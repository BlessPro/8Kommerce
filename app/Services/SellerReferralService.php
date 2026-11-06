<?php

namespace App\Services;

use App\Models\ReferralLink;
use App\Models\SellerReferral;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class SellerReferralService
{
    public const SESSION_LINK_ID = 'seller_referral_link_id';
    public const SESSION_INVITER_NAME = 'seller_referral_inviter_name';

    public function linkFor(User $user): ReferralLink
    {
        $existing = $user->referralLink()->first();
        if ($existing) {
            return $existing;
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return ReferralLink::create([
                    'user_id' => $user->id,
                    'code' => $this->generateCode(),
                ]);
            } catch (QueryException $exception) {
                $existing = $user->referralLink()->first();
                if ($existing) {
                    return $existing;
                }

                if ($attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Unable to create referral link.');
    }

    public function rememberInvite(Request $request, ReferralLink $link): void
    {
        $visitKey = 'seller_referral_visit_'.$link->id;
        if (! $request->session()->has($visitKey)) {
            $link->newQuery()->whereKey($link->id)->increment('visits_count', 1, [
                'last_visited_at' => now(),
                'updated_at' => now(),
            ]);
            $request->session()->put($visitKey, true);
        }

        $request->session()->put([
            self::SESSION_LINK_ID => $link->id,
            self::SESSION_INVITER_NAME => $link->user?->sellerProfile?->business_name
                ?: $link->user?->name
                ?: 'an 8Kommerce seller',
        ]);
    }

    public function attributeRegistration(Request $request, User $newUser): ?SellerReferral
    {
        $linkId = $request->session()->pull(self::SESSION_LINK_ID);
        $request->session()->forget(self::SESSION_INVITER_NAME);

        if (! $linkId) {
            return null;
        }

        $link = ReferralLink::query()->find($linkId);
        if (! $link || (int) $link->user_id === (int) $newUser->id) {
            return null;
        }

        return SellerReferral::query()->firstOrCreate(
            ['referred_user_id' => $newUser->id],
            [
                'referral_link_id' => $link->id,
                'referrer_id' => $link->user_id,
                'status' => SellerReferral::STATUS_JOINED,
                'registered_at' => now(),
            ]
        );
    }

    private function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        for ($index = 0; $index < 8; $index++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }
}
