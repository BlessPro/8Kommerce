<?php

namespace App\Http\Controllers;

use App\Models\WaitlistEntry;
use App\Services\SellerReferralService;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WaitlistController extends Controller
{
    public function index(Request $request): View
    {
        return view('waitlist.index', [
            'source' => (string) $request->query(
                'source',
                $request->session()->has(SellerReferralService::SESSION_LINK_ID) ? 'seller-referral' : 'public'
            ),
            'inviterName' => $request->session()->get(SellerReferralService::SESSION_INVITER_NAME),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone_number' => ['required', 'string', 'max:20'],
            'source' => ['nullable', 'string', 'max:80'],
            'consent' => ['accepted'],
        ]);

        $phone = Phone::normalize($data['phone_number'], '+233');
        if (! $phone || ! Phone::isValidGh($phone)) {
            throw ValidationException::withMessages([
                'phone_number' => 'Enter a valid Ghana phone number.',
            ]);
        }

        WaitlistEntry::query()->firstOrCreate(
            ['phone' => $phone],
            [
                'status' => WaitlistEntry::STATUS_WAITING,
                'source' => $data['source'] ?? 'public',
                'joined_at' => now(),
            ]
        );

        $request->session()->forget([
            SellerReferralService::SESSION_LINK_ID,
            SellerReferralService::SESSION_INVITER_NAME,
        ]);

        return redirect()->route('waitlist.show')->with('status', 'waitlist-joined');
    }
}
