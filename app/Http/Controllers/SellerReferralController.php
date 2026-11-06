<?php

namespace App\Http\Controllers;

use App\Models\ReferralLink;
use App\Services\SellerReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerReferralController extends Controller
{
    public function index(Request $request, SellerReferralService $referrals): View
    {
        $user = $request->user();
        $link = $referrals->linkFor($user);
        $rows = $link->referrals()
            ->with(['referredUser.sellerProfile', 'referredUser.subscriptions'])
            ->latest('registered_at')
            ->get()
            ->map(function ($referral) {
                $referred = $referral->referredUser;
                $stage = $referred?->hasActivePlan()
                    ? 'subscribed'
                    : ($referred?->hasCompletedProfileOnboarding() ? 'setup' : 'joined');

                $phone = (string) ($referred?->phone ?? '');
                $suffix = preg_replace('/\D+/', '', $phone);
                $suffix = $suffix ? substr($suffix, -4) : null;
                $businessName = trim((string) ($referred?->sellerProfile?->business_name ?? ''));
                $defaultName = str_starts_with($businessName, 'Seller ');

                return [
                    'label' => $businessName !== '' && ! $defaultName
                        ? $businessName
                        : ($suffix ? 'Seller ending '.$suffix : 'New seller'),
                    'stage' => $stage,
                    'registered_at' => $referral->registered_at,
                ];
            });

        return view('dashboard.referrals.index', [
            'link' => $link,
            'inviteUrl' => route('referrals.accept', $link),
            'rows' => $rows,
            'stats' => [
                'visits' => $link->visits_count,
                'joined' => $rows->count(),
                'setup' => $rows->whereIn('stage', ['setup', 'subscribed'])->count(),
                'subscribed' => $rows->where('stage', 'subscribed')->count(),
            ],
        ]);
    }

    public function accept(
        Request $request,
        ReferralLink $referralLink,
        SellerReferralService $referrals
    ): RedirectResponse {
        if ($request->user()) {
            if ((int) $request->user()->id === (int) $referralLink->user_id) {
                return redirect()->route('referrals.index')
                    ->with('status', 'This is your seller invite link. Share it with someone starting a business.');
            }

            return redirect()->route('dashboard')
                ->with('status', 'Seller invite links are for new accounts. Your existing account was not changed.');
        }

        $referralLink->loadMissing('user.sellerProfile');
        $referrals->rememberInvite($request, $referralLink);

        return redirect()->route('register');
    }
}
