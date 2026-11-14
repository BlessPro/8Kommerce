<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PromoterConversion;
use App\Models\PromoterProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoterAttributionService
{
    public const SESSION_PROMOTER_ID = 'promoter_profile_id';

    public function rememberVisit(Request $request, PromoterProfile $promoter): void
    {
        abort_unless($promoter->status === PromoterProfile::STATUS_ACTIVE, 404);

        $visitKey = 'promoter_visit_'.$promoter->id;
        if (! $request->session()->has($visitKey)) {
            PromoterProfile::query()->whereKey($promoter->id)->increment('visits_count', 1, [
                'last_visited_at' => now(),
                'updated_at' => now(),
            ]);
            $request->session()->put($visitKey, true);
        }

        $request->session()->put(self::SESSION_PROMOTER_ID, $promoter->id);
    }

    public function currentId(Request $request): ?string
    {
        $id = $request->session()->get(self::SESSION_PROMOTER_ID);

        return is_string($id) && PromoterProfile::query()->whereKey($id)->where('status', PromoterProfile::STATUS_ACTIVE)->exists()
            ? $id
            : null;
    }

    public function recordConversion(Payment $payment): void
    {
        $promoterId = data_get($payment->raw_payload, 'promoter_id');
        if (! is_string($promoterId) || $promoterId === '') {
            return;
        }

        DB::transaction(function () use ($payment, $promoterId): void {
            $promoter = PromoterProfile::query()->lockForUpdate()->find($promoterId);
            if (! $promoter || $promoter->status !== PromoterProfile::STATUS_ACTIVE) {
                return;
            }

            $conversion = PromoterConversion::query()->firstOrCreate(
                ['payment_id' => $payment->id],
                [
                    'promoter_profile_id' => $promoter->id,
                    'order_id' => $payment->order_id,
                    'amount' => $payment->amount,
                    'currency' => config('services.paystack.currency', 'GHS'),
                    'converted_at' => $payment->paid_at ?: now(),
                ]
            );

            if ($conversion->wasRecentlyCreated) {
                $promoter->increment('conversions_count');
                $promoter->increment('conversion_amount', (float) $payment->amount);
            }
        }, 3);
    }
}
