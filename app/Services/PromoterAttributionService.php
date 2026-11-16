<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PromoterConversion;
use App\Models\PromoterProfile;
use App\Models\PromoterVisit;
use App\Models\Product;
use App\Support\Privacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoterAttributionService
{
    public const SESSION_PROMOTER_ID = 'promoter_profile_id';

    public function rememberVisit(Request $request, PromoterProfile $promoter, ?Product $product = null): void
    {
        abort_unless($promoter->status === PromoterProfile::STATUS_ACTIVE, 404);

        $destination = $product ? 'product' : 'marketplace';
        $visitKey = 'promoter_visit_'.$promoter->id.'_'.($product?->id ?? 'marketplace');
        if (! $request->session()->has($visitKey)) {
            DB::transaction(function () use ($request, $promoter, $product, $destination): void {
                PromoterProfile::query()->whereKey($promoter->id)->increment('visits_count', 1, [
                    'last_visited_at' => now(),
                    'updated_at' => now(),
                ]);

                PromoterVisit::query()->create([
                    'promoter_profile_id' => $promoter->id,
                    'product_id' => $product?->id,
                    'destination' => $destination,
                    'visitor_hash' => Privacy::contactHash(implode('|', [
                        $request->session()->getId(),
                        $promoter->id,
                        $product?->id ?? 'marketplace',
                    ])),
                    'referrer_host' => $this->referrerHost($request),
                    'visited_at' => now(),
                ]);
            }, 3);
            $request->session()->put($visitKey, true);
        }

        $request->session()->put(self::SESSION_PROMOTER_ID, $promoter->id);
    }

    private function referrerHost(Request $request): ?string
    {
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        return is_string($host) ? mb_substr(strtolower($host), 0, 180) : null;
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
