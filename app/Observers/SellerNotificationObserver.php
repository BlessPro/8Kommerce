<?php

namespace App\Observers;

use App\Jobs\SendOutboundMessage;
use App\Models\SellerNotification;
use Illuminate\Support\Facades\Log;

class SellerNotificationObserver
{
    public function created(SellerNotification $notification): void
    {
        $user = $notification->user()->with('sellerProfile')->first();
        if (! $user) {
            return;
        }

        $phone = $user->phone ?: $user->sellerProfile?->phone;
        if (! $phone) {
            Log::warning('Seller notification skipped (no phone)', [
                'user_id' => $user->id,
                'type' => $notification->type,
            ]);

            return;
        }

        $content = trim($notification->title."\n".$notification->body);
        $content .= "\nOrders: ".route('products.index').'#orders-by-customer';
        $orderId = data_get($notification->data, 'order_id');
        if (is_scalar($orderId) && (string) $orderId !== '') {
            $content .= "\nOrder link: ".route('products.index').'#order-'.(string) $orderId;
        }

        SendOutboundMessage::dispatch(
            $phone,
            $content,
            [
                'user_id' => $user->id,
                'context_type' => 'seller_notification',
                'context_id' => $notification->type,
            ],
            true,
            null,
            'notifications',
        )->afterCommit();
    }
}
