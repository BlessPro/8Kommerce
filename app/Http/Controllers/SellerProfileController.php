<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\SellerNotification;
use App\Models\SellerProfile;
use App\Services\OgImageService;
use App\Services\PaystackService;
use App\Services\SellerNotifier;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SellerProfileController extends Controller
{
    public function update(UpdateProfileRequest $request, PaystackService $paystack)
    {
        $user = $request->user();
        $profile = $user->sellerProfile;

        if (! $profile) {
            $profile = $user->sellerProfile()->create([
                'business_name' => $user->name,
                'public_slug' => SellerProfile::generateUniqueSlug($user->name),
            ]);
        }

        $payoutChanged = collect(['payout_method', 'settlement_bank_code', 'account_number'])
            ->contains(fn (string $field): bool => (string) $request->input($field, '') !== (string) ($profile->{$field} ?? ''));

        $resolvedAccountName = $request->input('account_name');
        $resolvedAccountNumber = $request->input('account_number');
        if ($payoutChanged && $request->filled('settlement_bank_code') && $request->filled('account_number')) {
            try {
                $type = $request->input('payout_method', 'MOMO') === 'BANK' ? 'ghipss' : 'mobile_money';
                $supported = collect($paystack->listBanks(config('services.paystack.currency', 'GHS'), $type));
                if (! $supported->contains(fn (array $provider): bool => (string) ($provider['code'] ?? '') === (string) $request->input('settlement_bank_code'))) {
                    throw new \RuntimeException('Unsupported payout provider.');
                }
                $resolved = $paystack->resolveAccount(
                    (string) $request->input('account_number'),
                    (string) $request->input('settlement_bank_code')
                );
                $resolvedAccountName = trim((string) ($resolved['account_name'] ?? ''));
                $resolvedAccountNumber = (string) ($resolved['account_number'] ?? preg_replace('/\D+/', '', (string) $request->input('account_number')));
                if ($resolvedAccountName === '') {
                    throw new \RuntimeException('Account name was not returned.');
                }
            } catch (\Throwable $exception) {
                Log::warning('Paystack payout account resolution failed.', [
                    'user_id' => $user->id,
                    'bank_code' => $request->input('settlement_bank_code'),
                    'exception' => $exception::class,
                ]);

                return back()
                    ->withErrors(['account_number' => 'We could not verify this payout account. Check the provider or bank and number, then try again.'])
                    ->withInput();
            }
        }

        $profile->fill([
            'business_name' => $request->input('business_name'),
            'payout_method' => $request->input('payout_method', $profile->payout_method ?: 'MOMO'),
            'settlement_bank_code' => $request->input('settlement_bank_code'),
            'account_number' => $resolvedAccountNumber,
            'account_name' => $resolvedAccountName,
        ]);

        $options = collect($request->validated('fulfillment_options', []))
            ->map(fn (array $option) => [
                'type' => $option['type'],
                'label' => trim($option['label']),
                'fee' => number_format((float) $option['fee'], 2, '.', ''),
                'details' => filled($option['details'] ?? null) ? trim((string) $option['details']) : null,
            ])
            ->values()
            ->all();
        $settings = $profile->resolvedStorefrontSettings();
        $settings['fulfillment_options'] = $options;
        $profile->storefront_settings = $settings;

        $phoneNumber = $request->input('phone_number');
        $phoneCountry = $request->input('phone_country', '+233');
        $profile->phone = $phoneNumber
            ? Phone::normalize($phoneNumber, $phoneCountry)
            : null;

        $profile->save();

        // Pre-render a large OG image for WhatsApp previews.
        try {
            app(OgImageService::class)->generateSeller($profile);
        } catch (\Throwable $e) {
            // Ignore OG failures.
        }

        if ($profile->phone) {
            if ($user->phone !== $profile->phone) {
                $user->phone_verified_at = null;
            }
            $user->phone = $profile->phone;
        } else {
            $user->phone = null;
            $user->phone_verified_at = null;
        }

        if ($request->has('name')) {
            $user->name = $request->string('name')->trim()->toString();
        }
        if ($request->has('email')) {
            $email = strtolower($request->string('email')->trim()->toString());
            if ($email !== $user->email) {
                $user->email = $email;
                $user->email_verified_at = null;
            }
        }
        if ($request->filled('password')) {
            $user->password = Hash::make((string) $request->input('password'));
        }
        $user->save();

        $shouldConnectPaystack = ($payoutChanged || ! $profile->paystack_subaccount_code)
            && $request->filled('settlement_bank_code')
            && $request->filled('account_number')
            && $request->filled('account_name');

        if ($shouldConnectPaystack) {
            try {
                $response = $paystack->createOrUpdateSubaccount($profile);
                $profile->paystack_subaccount_code = $response['subaccount_code'] ?? $profile->paystack_subaccount_code;
                $profile->percent_charge = $response['percentage_charge'] ?? $profile->percent_charge;
                $profile->save();

                app(SellerNotifier::class)->notify(
                    $user,
                    SellerNotification::TYPE_PAYSTACK_CONNECTED,
                    'Paystack connected',
                    'Your payout details are connected to Paystack.',
                    ['subaccount_code' => $profile->paystack_subaccount_code]
                );
            } catch (\Throwable $exception) {
                return back()
                    ->withErrors(['paystack' => 'Unable to connect Paystack: '.$exception->getMessage()])
                    ->withInput();
            }
        }

        return back()->with('status', 'profile-updated');
    }

    public function resolveAccount(Request $request, PaystackService $paystack)
    {
        $validated = $request->validate([
            'payout_method' => ['required', 'string', 'in:MOMO,BANK'],
            'settlement_bank_code' => ['required', 'string', 'max:20'],
            'account_number' => ['required', 'string', 'max:20'],
        ]);

        try {
            $type = $validated['payout_method'] === 'MOMO' ? 'mobile_money' : 'ghipss';
            $supported = collect($paystack->listBanks(config('services.paystack.currency', 'GHS'), $type));
            if (! $supported->contains(fn (array $provider): bool => (string) ($provider['code'] ?? '') === $validated['settlement_bank_code'])) {
                return response()->json(['message' => 'Select a supported payout provider.'], 422);
            }

            $resolved = $paystack->resolveAccount($validated['account_number'], $validated['settlement_bank_code']);
            $accountName = trim((string) ($resolved['account_name'] ?? ''));
            if ($accountName === '') {
                return response()->json(['message' => 'We could not verify this account.'], 422);
            }

            return response()->json([
                'account_name' => $accountName,
                'account_number' => $resolved['account_number'] ?? preg_replace('/\D+/', '', $validated['account_number']),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Paystack account lookup failed.', [
                'user_id' => $request->user()?->id,
                'bank_code' => $validated['settlement_bank_code'],
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'Account verification is temporarily unavailable. Check the details and try again.',
            ], 422);
        }
    }

    public function testConnection(Request $request, PaystackService $paystack)
    {
        $user = $request->user();
        $profile = $user->sellerProfile;

        if (! $profile) {
            return back()->withErrors(['paystack' => 'No seller profile found.']);
        }

        if (! $profile->settlement_bank_code || ! $profile->account_number || ! $profile->account_name) {
            return back()->withErrors(['paystack' => 'Please fill in payout details before testing.']);
        }

        try {
            $response = $paystack->createOrUpdateSubaccount($profile);
            $profile->paystack_subaccount_code = $response['subaccount_code'] ?? $profile->paystack_subaccount_code;
            $profile->percent_charge = $response['percentage_charge'] ?? $profile->percent_charge;
            $profile->save();

            return back()->with('paystack_status', 'Paystack connection successful.');
        } catch (\Throwable $exception) {
            return back()->withErrors(['paystack' => 'Paystack connection failed: '.$exception->getMessage()]);
        }
    }
}
