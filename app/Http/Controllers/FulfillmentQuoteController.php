<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryUnavailableException;
use App\Jobs\SendOutboundMessage;
use App\Models\FulfillmentQuote;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\PaystackService;
use App\Support\Email;
use App\Support\Money;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FulfillmentQuoteController extends Controller
{
    public function show(string $token)
    {
        $quote = FulfillmentQuote::where('public_token', $token)->with('user.sellerProfile')->firstOrFail();
        if ($quote->status === FulfillmentQuote::STATUS_QUOTED && $quote->expires_at?->isPast()) {
            $quote->update(['status' => FulfillmentQuote::STATUS_EXPIRED]);
        }

        return view('public.fulfillment-quote', compact('quote'));
    }

    public function update(Request $request, FulfillmentQuote $quote)
    {
        abort_unless($quote->user_id === $request->user()->id, 403);
        abort_unless($quote->status === FulfillmentQuote::STATUS_REQUESTED, 409);
        $validated = $request->validate([
            'delivery_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'seller_note' => ['nullable', 'string', 'max:500'],
            'valid_hours' => ['nullable', 'integer', 'min:1', 'max:72'],
        ]);
        $fee = number_format((float) $validated['delivery_fee'], 2, '.', '');
        $quote->update([
            'delivery_fee' => $fee,
            'total' => Money::add((string) $quote->subtotal, $fee),
            'seller_note' => $validated['seller_note'] ?? null,
            'status' => FulfillmentQuote::STATUS_QUOTED,
            'quoted_at' => now(),
            'expires_at' => now()->addHours((int) ($validated['valid_hours'] ?? 24)),
        ]);

        SendOutboundMessage::dispatch(
            $quote->customer_phone,
            'Your delivery price is ready. Review and pay securely: '.route('fulfillment-quotes.show', $quote->public_token),
            ['user_id' => $quote->user_id, 'context_type' => 'delivery_quote_ready', 'context_id' => $quote->id],
            true,
            null,
            'notifications'
        );

        return back()->with('status', 'delivery-quote-sent');
    }

    public function reject(Request $request, FulfillmentQuote $quote)
    {
        abort_unless($quote->user_id === $request->user()->id, 403);
        abort_unless($quote->status === FulfillmentQuote::STATUS_REQUESTED, 409);
        $quote->update(['status' => FulfillmentQuote::STATUS_REJECTED]);

        return back()->with('status', 'delivery-quote-rejected');
    }

    public function pay(string $token, PaystackService $paystack, InventoryService $inventory)
    {
        $quote = FulfillmentQuote::where('public_token', $token)->with('user.sellerProfile')->firstOrFail();
        if (! in_array($quote->status, [FulfillmentQuote::STATUS_QUOTED, FulfillmentQuote::STATUS_PAYMENT_PENDING], true) || ! $quote->total || $quote->expires_at?->isPast()) {
            return back()->withErrors(['quote' => 'This delivery quote is not ready or has expired. Ask the seller for a new price.']);
        }
        if (! $quote->user?->sellerProfile?->paystack_subaccount_code) {
            return back()->withErrors(['quote' => 'The seller cannot accept online payment right now.']);
        }

        $order = null;
        $payment = null;
        try {
            if ($quote->status === FulfillmentQuote::STATUS_PAYMENT_PENDING && $quote->order_id) {
                $order = Order::findOrFail($quote->order_id);
                $payment = $order->payments()->where('status', Payment::STATUS_PENDING)->latest()->firstOrFail();
            } else {
                [$order, $payment] = DB::transaction(function () use ($quote): array {
                    $locked = FulfillmentQuote::query()->lockForUpdate()->findOrFail($quote->id);
                    abort_unless($locked->status === FulfillmentQuote::STATUS_QUOTED && ! $locked->expires_at?->isPast(), 409);
                    $reference = (string) Str::uuid();
                    $order = Order::create([
                        'user_id' => $locked->user_id,
                        'reference' => $reference,
                        'status' => Order::STATUS_PENDING_PAYMENT,
                        'payment_status' => Payment::STATUS_PENDING,
                        'customer_name' => $locked->customer_name,
                        'customer_phone' => $locked->customer_phone,
                        'customer_location' => $locked->location,
                        'delivery_required' => true,
                        'fulfillment_type' => Order::FULFILLMENT_DELIVERY,
                        'fulfillment_method' => $locked->fulfillment_method,
                        'fulfillment_label' => $locked->fulfillment_label,
                        'fulfillment_fee' => $locked->delivery_fee,
                        'fulfillment_details' => $locked->fulfillment_details,
                        'fulfillment_meta' => array_filter(['landmark' => $locked->landmark, 'gps_location' => $locked->gps_location, 'popular_stop' => $locked->popular_stop, 'quote_id' => $locked->id]),
                        'delivery_note' => $locked->customer_note,
                        'subtotal' => $locked->subtotal,
                        'discount_amount' => '0.00',
                        'total' => $locked->total,
                        'currency' => $locked->currency,
                    ]);
                    foreach ($locked->items as $item) {
                        $product = Product::where('user_id', $locked->user_id)->findOrFail($item['product_id']);
                        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name, 'unit_price' => $item['unit_price'], 'quantity' => $item['quantity'], 'line_total' => $item['line_total']]);
                    }
                    $payment = Payment::create([
                        'user_id' => $locked->user_id,
                        'order_id' => $order->id,
                        'reference' => $reference,
                        'amount' => $locked->total,
                        'status' => Payment::STATUS_PENDING,
                        'raw_payload' => ['fulfillment_quote_id' => $locked->id, 'customer' => ['email' => Email::placeholder($reference), 'phone' => $locked->customer_phone, 'location' => $locked->location, 'note' => $locked->customer_note], 'order' => ['id' => $order->id, 'items' => $locked->items]],
                    ]);
                    $locked->update(['status' => FulfillmentQuote::STATUS_PAYMENT_PENDING, 'order_id' => $order->id]);

                    return [$order, $payment];
                });
                $inventory->reserveForOrder($order, $payment);
            }
            $fee = $paystack->platformChargeFor((string) $quote->total, $quote->user);
            $data = $paystack->initializeTransaction((string) $quote->total, Email::placeholder($payment->reference), ['reference' => $payment->reference, 'payment_id' => $payment->id, 'order_id' => $order->id, 'purpose' => 'delivery_quote', 'platform_fee' => $fee], $quote->user->sellerProfile->paystack_subaccount_code, $fee);

            return redirect()->away($data['authorization_url']);
        } catch (InventoryUnavailableException $exception) {
            if ($order && $payment) {
                $payment->update(['status' => Payment::STATUS_FAILED]);
                $order->update(['status' => Order::STATUS_CANNOT_FULFILL, 'payment_status' => Payment::STATUS_FAILED]);
                $quote->update(['status' => FulfillmentQuote::STATUS_QUOTED, 'order_id' => null]);
            }

            return back()->withErrors(['quote' => $exception->getMessage().' You have not been charged.']);
        } catch (RequestException $exception) {
            if ($order && $payment) {
                $inventory->releaseForOrder($order);
                $payment->update(['status' => Payment::STATUS_FAILED]);
                $order->update(['status' => Order::STATUS_CANNOT_FULFILL, 'payment_status' => Payment::STATUS_FAILED]);
                $quote->update(['status' => FulfillmentQuote::STATUS_QUOTED, 'order_id' => null]);
            }

            return back()->withErrors(['quote' => 'Payment could not be started. Please try again shortly.']);
        }
    }
}
