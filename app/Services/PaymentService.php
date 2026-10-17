<?php

namespace App\Services;

use App\Jobs\SendOutboundMessage;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PublicCartSession;
use App\Models\SellerNotification;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function markSuccess(Payment $payment, array $verifiedData): void
    {
        $lock = Cache::lock('payment:mark-success:'.$payment->id, 15);
        if (! $lock->get()) {
            return;
        }

        try {
            DB::transaction(function () use ($payment, $verifiedData): void {
                $payment = Payment::query()->lockForUpdate()->with(['invoice.user.sellerProfile', 'product', 'order.items.product', 'order.user.sellerProfile'])->find($payment->id);
                if (! $payment || $payment->status === Payment::STATUS_SUCCESS) {
                    return;
                }

                if (data_get($verifiedData, 'channel') !== 'admin_manual_confirmation') {
                    $verificationError = $this->providerVerificationError($payment, $verifiedData);
                    if ($verificationError !== null) {
                        throw new \RuntimeException('Payment verification mismatch: '.$verificationError);
                    }
                }

                $payment->status = Payment::STATUS_SUCCESS;
                $payment->channel = $verifiedData['channel'] ?? null;

                $paidAt = $verifiedData['paid_at'] ?? null;
                if ($paidAt) {
                    $payment->paid_at = Carbon::parse($paidAt);
                } elseif (! $payment->paid_at) {
                    $payment->paid_at = now();
                }
                $payment->verified_at = now();

                $existingPayload = $payment->raw_payload ?? [];
                $payment->raw_payload = array_replace_recursive(
                    $existingPayload,
                    $this->sanitizedVerificationData($verifiedData)
                );
                $commissionPercent = $payment->user?->commissionPercent() ?? (string) config('plans.payments.commission_percent', '0.01');
                $payment->commission_amount = Money::percent((string) $payment->amount, $commissionPercent);
                $payment->transaction_fee = $this->amountFromMinor(data_get($verifiedData, 'transaction_charge'));
                $payment->tax_amount = $this->resolveTaxAmount($verifiedData);
                $payment->receiving_account = $this->resolveReceivingAccount($payment, $verifiedData);
                $payment->transaction_code = $this->resolveTransactionCode($payment, $verifiedData);
                $payment->transaction_id = $this->resolveTransactionId($payment, $verifiedData);
                $payment->save();

                $user = $payment->user()->with('sellerProfile')->first();
                if ($user) {
                    $customerName = (string) (data_get($payment->raw_payload, 'customer.name') ?? data_get($verifiedData, 'metadata.customer.name') ?? 'Customer');
                    $customerPhone = (string) (data_get($payment->raw_payload, 'customer.phone') ?? data_get($verifiedData, 'metadata.customer.phone') ?? '');
                    $customerLocation = (string) (data_get($payment->raw_payload, 'customer.location') ?? data_get($verifiedData, 'metadata.customer.location') ?? '');
                    $customerNote = (string) (data_get($payment->raw_payload, 'customer.note') ?? data_get($verifiedData, 'metadata.customer.note') ?? '');
                    $itemLabel = $payment->invoice?->title ?? $payment->product?->name ?? 'payment';
                    $amountLabel = Money::format((string) $payment->amount, config('services.paystack.currency', 'GHS'));

                    $leadMatch = $this->findMatchingLead($payment, $customerPhone);
                    $leadPaidBody = $leadMatch
                        ? "Lead paid: {$customerName} completed payment for {$itemLabel} ({$amountLabel})."
                        : null;

                    // Order payments get one richer notification below. Keeping this notification
                    // for invoices/direct payments avoids sending a seller duplicate messages.
                    if (! $payment->order_id) {
                        app(SellerNotifier::class)->notify(
                            $user,
                            SellerNotification::TYPE_PAYMENT_RECEIVED,
                            'Payment received',
                            'Payment '.$payment->reference.' was completed by '.$customerName.'.',
                            [
                                'payment_id' => $payment->id,
                                'reference' => $payment->reference,
                                'item' => $itemLabel,
                                'amount' => (string) $payment->amount,
                                'customer_name' => $customerName,
                                'customer_phone' => $customerPhone,
                                'customer_location' => $customerLocation,
                                'customer_note' => $customerNote,
                                'matched_lead_id' => $leadMatch?->id,
                            ]
                        );
                    }

                    if ($leadMatch && $leadPaidBody) {
                        app(SellerNotifier::class)->notify(
                            $user,
                            SellerNotification::TYPE_LEAD_CAPTURED,
                            'Lead converted to payment',
                            $leadPaidBody,
                            [
                                'lead_id' => $leadMatch->id,
                                'payment_id' => $payment->id,
                                'reference' => $payment->reference,
                                'customer_phone' => $customerPhone,
                            ],
                            sendEmail: false
                        );
                    }
                }

                if ($payment->invoice_id) {
                    $invoice = Invoice::find($payment->invoice_id);
                    if ($invoice) {
                        $beforeStatus = $invoice->status;
                        $invoice->refreshPaymentStatus();

                        if ($invoice->status === Invoice::STATUS_PARTIAL && $beforeStatus !== Invoice::STATUS_PARTIAL) {
                            if ($user) {
                                app(SellerNotifier::class)->notify(
                                    $user,
                                    SellerNotification::TYPE_INVOICE_PARTIAL,
                                    'Invoice partially paid',
                                    'Invoice "'.$invoice->title.'" has a new payment.',
                                    ['invoice_id' => $invoice->id]
                                );
                            }
                        }

                        if ($invoice->status === Invoice::STATUS_PAID && $beforeStatus !== Invoice::STATUS_PAID) {
                            if ($user) {
                                app(SellerNotifier::class)->notify(
                                    $user,
                                    SellerNotification::TYPE_INVOICE_PAID,
                                    'Invoice fully paid',
                                    'Invoice "'.$invoice->title.'" is fully paid.',
                                    ['invoice_id' => $invoice->id]
                                );
                            }
                        }
                    }
                }

                if ($payment->product_id && ! $payment->order_id) {
                    $product = Product::find($payment->product_id);
                    if ($product) {
                        app(InventoryService::class)->decrementForDirectProductPayment($product, 1, $user);
                    }
                }

                if ($payment->order_id) {
                    $order = Order::with('items')->find($payment->order_id);
                    if ($order) {
                        $order->status = Order::STATUS_PAID;
                        $order->payment_status = Payment::STATUS_SUCCESS;
                        if (! $order->paid_at) {
                            $order->paid_at = $payment->paid_at ?: now();
                        }
                        if (! $order->seller_response_due_at) {
                            $order->seller_response_due_at = now()->addHours((int) config('orders.seller_response_hours', 24));
                        }
                        $order->save();

                        if ($user) {
                            $itemsSummary = $order->items->map(function ($item) {
                                return $item->product_name.' x'.$item->quantity;
                            })->implode(', ');

                            app(SellerNotifier::class)->notify(
                                $user,
                                SellerNotification::TYPE_PAYMENT_RECEIVED,
                                'New paid order',
                                'Order '.$order->reference.' paid. '.$itemsSummary,
                                [
                                    'order_id' => $order->id,
                                    'reference' => $order->reference,
                                    'amount' => (string) $order->total,
                                    'customer_name' => $order->customer_name,
                                    'customer_phone' => $order->customer_phone,
                                    'customer_location' => $order->customer_location,
                                    'delivery_required' => $order->delivery_required ? 'yes' : 'no',
                                    'delivery_note' => $order->delivery_note,
                                ]
                            );

                        }

                        app(InventoryService::class)->decrementForOrder($order, $user);

                        if (! $payment->product_id) {
                            $publicSlug = (string) data_get($payment->raw_payload, 'order.public_slug', '');
                            $cartToken = (string) data_get($payment->raw_payload, 'order.cart_session_token', '');
                            if ($publicSlug !== '' && $cartToken !== '') {
                                PublicCartSession::query()
                                    ->where('public_slug', $publicSlug)
                                    ->where('session_token', $cartToken)
                                    ->delete();
                                Cache::put(
                                    'public_cart_paid:'.sha1($publicSlug.'|'.$cartToken),
                                    true,
                                    now()->addDays(30)
                                );
                            }
                        }

                        if ($order->coupon_code) {
                            $coupon = Coupon::query()
                                ->where('user_id', $order->user_id)
                                ->where('code', $order->coupon_code)
                                ->first();

                            if ($coupon) {
                                $customerPhone = (string) ($order->customer_phone ?? data_get($payment->raw_payload, 'customer.phone', ''));
                                if ($customerPhone !== '') {
                                    CouponRedemption::query()->firstOrCreate(
                                        [
                                            'coupon_id' => $coupon->id,
                                            'customer_fingerprint' => Coupon::customerFingerprint($customerPhone),
                                        ],
                                        [
                                            'seller_id' => $order->user_id,
                                            'order_id' => $order->id,
                                            'payment_id' => $payment->id,
                                            'ip_address' => data_get($payment->raw_payload, 'customer.ip_address'),
                                            'used_at' => $payment->paid_at ?? now(),
                                        ]
                                    );
                                }

                                $coupon->increment('used_count');
                            }
                        }
                    }
                }

                $customerPhone = data_get($payment->raw_payload, 'customer.phone')
                    ?? data_get($verifiedData, 'metadata.customer.phone')
                    ?? data_get($verifiedData, 'customer.phone');

                if ($customerPhone) {
                    $amount = Money::format((string) $payment->amount, config('services.paystack.currency', 'GHS'));
                    $sellerName = substr((string) ($user?->sellerProfile?->business_name ?? $user?->name ?? 'LinkPay seller'), 0, 30);
                    $trackingLine = $payment->order_id ? ' Track: '.route('customer.orders.login') : '';
                    $message = "Paid {$amount} to {$sellerName}. Ref: {$payment->reference}.{$trackingLine}";
                    SendOutboundMessage::dispatch(
                        $customerPhone,
                        $message,
                        [
                            'user_id' => $payment->user_id,
                            'payment_id' => $payment->id,
                            'context_type' => 'payment_customer_success_sms',
                            'context_id' => $payment->id,
                        ],
                        true,
                        null,
                        'notifications',
                    )->afterCommit();
                } else {
                    Log::warning('Customer WhatsApp notify skipped (no phone)', [
                        'payment_id' => $payment->id,
                    ]);
                }
            }, 3);
        } finally {
            optional($lock)->release();
        }
    }

    public function providerVerificationError(Payment $payment, array $verifiedData): ?string
    {
        if (data_get($verifiedData, 'status') !== 'success') {
            return 'transaction status';
        }

        if (! hash_equals((string) $payment->reference, (string) data_get($verifiedData, 'reference', ''))) {
            return 'reference';
        }

        $verifiedAmount = data_get($verifiedData, 'amount');
        if (! is_numeric($verifiedAmount) || (int) $verifiedAmount !== Money::toMinor((string) $payment->amount)) {
            return 'amount';
        }

        $expectedCurrency = strtoupper((string) config('services.paystack.currency', 'GHS'));
        if (strtoupper((string) data_get($verifiedData, 'currency', '')) !== $expectedCurrency) {
            return 'currency';
        }

        $expectedSubaccount = $payment->user()->with('sellerProfile')->first()?->sellerProfile?->paystack_subaccount_code;
        if ($expectedSubaccount) {
            $verifiedSubaccount = data_get($verifiedData, 'subaccount.subaccount_code');
            if (! is_string($verifiedSubaccount) || ! hash_equals($expectedSubaccount, $verifiedSubaccount)) {
                return 'seller account';
            }
        }

        return null;
    }

    private function amountFromMinor($value): string
    {
        if ($value === null) {
            return '0.00';
        }

        return Money::fromMinor($value);
    }

    /**
     * Keep the payment evidence needed for support and reconciliation without
     * retaining the provider's entire customer and authorization response.
     */
    private function sanitizedVerificationData(array $verifiedData): array
    {
        $safe = Arr::only($verifiedData, [
            'id',
            'status',
            'reference',
            'amount',
            'currency',
            'channel',
            'paid_at',
            'transaction_date',
            'transaction_charge',
            'fees',
        ]);

        $authorization = data_get($verifiedData, 'authorization');
        if (is_array($authorization)) {
            $safe['authorization'] = Arr::only($authorization, [
                'authorization_code',
                'channel',
                'card_type',
                'bank',
                'last4',
                'exp_month',
                'exp_year',
            ]);
        }

        $subaccount = data_get($verifiedData, 'subaccount');
        if (is_array($subaccount)) {
            $safe['subaccount'] = Arr::only($subaccount, ['id', 'subaccount_code']);
        }

        $metadata = data_get($verifiedData, 'metadata');
        if (is_array($metadata)) {
            $safe['metadata'] = Arr::only($metadata, [
                'payment_id',
                'order_id',
                'transaction_code',
                'transaction_id',
                'subaccount',
                'tax',
            ]);
        }

        return $safe;
    }

    private function resolveTaxAmount(array $verifiedData): string
    {
        $tax = data_get($verifiedData, 'metadata.tax')
            ?? data_get($verifiedData, 'tax')
            ?? 0;

        if (! is_numeric($tax)) {
            return '0.00';
        }

        return (string) number_format((float) $tax, 2, '.', '');
    }

    private function resolveReceivingAccount(Payment $payment, array $verifiedData): ?string
    {
        $candidate = data_get($verifiedData, 'subaccount')
            ?? data_get($verifiedData, 'metadata.subaccount')
            ?? $payment->receiving_account;

        if (is_string($candidate) || $candidate === null) {
            return $candidate;
        }

        if (is_array($candidate)) {
            $code = $candidate['subaccount_code'] ?? $candidate['code'] ?? null;
            if (is_string($code)) {
                return $code;
            }

            return isset($candidate['id']) ? (string) $candidate['id'] : null;
        }

        if (is_object($candidate)) {
            return method_exists($candidate, '__toString') ? (string) $candidate : null;
        }

        return (string) $candidate;
    }

    private function resolveTransactionCode(Payment $payment, array $verifiedData): ?string
    {
        return data_get($verifiedData, 'authorization.authorization_code')
            ?? data_get($verifiedData, 'metadata.transaction_code')
            ?? data_get($payment->raw_payload, 'authorization.authorization_code');
    }

    private function resolveTransactionId(Payment $payment, array $verifiedData): ?string
    {
        return data_get($verifiedData, 'id')
            ?? data_get($verifiedData, 'metadata.transaction_id')
            ?? $payment->transaction_id;
    }

    private function findMatchingLead(Payment $payment, ?string $customerPhone): ?Lead
    {
        if (! $customerPhone || ! $payment->user_id) {
            return null;
        }

        $query = Lead::query()
            ->where('user_id', $payment->user_id)
            ->whereJsonContains('phones', $customerPhone);

        if ($payment->product_id) {
            $query->where('product_id', $payment->product_id);
        }

        return $query->latest()->first();
    }
}
