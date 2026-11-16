<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PromoterProfile;
use App\Models\PromoterVisit;
use App\Services\PromoterAttributionService;
use App\Services\SmsOtpService;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromoterController extends Controller
{
    public const SESSION_AUTHENTICATED_PROFILE_ID = 'promoter_authenticated_profile_id';

    public function index(Request $request): View
    {
        $profile = $this->authenticatedProfile($request);

        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('status', [Product::STATUS_IN_STOCK, Product::STATUS_LOW_STOCK, Product::STATUS_PRE_ORDER])
            ->whereHas('user.sellerProfile', fn ($query) => $query->whereNotNull('public_slug'))
            ->with('user.sellerProfile')
            ->latest()
            ->take(12)
            ->get();

        $recentVisits = $profile
            ? $profile->visits()->with('product')->latest('visited_at')->take(10)->get()
            : collect();
        $recentConversions = $profile
            ? $profile->conversions()->latest('converted_at')->take(10)->get()
            : collect();
        $topProducts = $profile
            ? PromoterVisit::query()
                ->where('promoter_profile_id', $profile->id)
                ->whereNotNull('product_id')
                ->select('product_id')
                ->selectRaw('COUNT(*) as visits')
                ->groupBy('product_id')
                ->orderByDesc('visits')
                ->with('product')
                ->take(5)
                ->get()
            : collect();

        return view('promoters.index', compact(
            'profile', 'products', 'recentVisits', 'recentConversions', 'topProducts'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone_number' => ['required', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:120'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => [Rule::in(['whatsapp', 'instagram', 'facebook', 'tiktok', 'in_person'])],
            'note' => ['nullable', 'string', 'max:500'],
            'pin' => ['required', 'digits:4', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $phone = Phone::normalize($data['phone_number'], '+233');
        if (! $phone || ! Phone::isValidGh($phone)) {
            throw ValidationException::withMessages(['phone_number' => 'Enter a valid Ghana phone number.']);
        }

        $existing = PromoterProfile::query()->where('phone', $phone)->first();
        if ($existing && (! $request->user() || (int) $existing->user_id !== (int) $request->user()->id)) {
            return back()->with('status', 'promoter-existing');
        }

        $profile = $existing ?: PromoterProfile::create([
            'user_id' => $request->user()?->id,
            'public_code' => Str::upper(Str::random(10)),
            'name' => $data['name'],
            'phone' => $phone,
            'access_pin_hash' => Hash::make($data['pin']),
            'city' => $data['city'] ?? null,
            'channels' => $data['channels'],
            'note' => $data['note'] ?? null,
            'status' => PromoterProfile::STATUS_ACTIVE,
        ]);

        $request->session()->put(self::SESSION_AUTHENTICATED_PROFILE_ID, $profile->id);
        $request->session()->regenerate();

        return redirect()->route('promoters.index')->with('status', 'promoter-created');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone_number' => ['required', 'string', 'max:20'],
            'pin' => ['required', 'digits:4'],
        ]);

        $phone = Phone::normalize($data['phone_number'], '+233');
        $profile = $phone
            ? PromoterProfile::query()->where('phone', $phone)->where('status', PromoterProfile::STATUS_ACTIVE)->first()
            : null;

        if (! $profile || ! $profile->access_pin_hash || ! Hash::check($data['pin'], $profile->access_pin_hash)) {
            throw ValidationException::withMessages([
                'promoter_login' => 'The phone number or promoter PIN is incorrect.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_AUTHENTICATED_PROFILE_ID, $profile->id);
        $profile->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('promoters.index')->with('status', 'promoter-authenticated');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_AUTHENTICATED_PROFILE_ID);
        $request->session()->regenerateToken();

        return redirect()->route('promoters.index')->with('status', 'promoter-signed-out');
    }

    public function sendPinOtp(Request $request, SmsOtpService $smsOtp): RedirectResponse
    {
        $data = $request->validate(['phone_number' => ['required', 'string', 'max:20']]);
        $phone = Phone::normalize($data['phone_number'], '+233');
        if (! $phone || ! Phone::isValidGh($phone)) {
            throw ValidationException::withMessages(['promoter_pin_phone' => 'Enter a valid Ghana phone number.']);
        }

        $profile = PromoterProfile::query()->where('phone', $phone)->where('status', PromoterProfile::STATUS_ACTIVE)->first();
        if ($profile) {
            try {
                $sent = $smsOtp->sendOtp($phone);
                if (! $sent) {
                    throw new \RuntimeException('OTP delivery was not accepted.');
                }
            } catch (\Throwable $exception) {
                Log::warning('Promoter PIN OTP delivery failed', [
                    'promoter_id' => $profile->id,
                    'message' => $exception->getMessage(),
                ]);
                throw ValidationException::withMessages([
                    'promoter_pin_phone' => 'We could not send the verification code. Try again shortly.',
                ]);
            }
        }

        $request->session()->put('promoter_pin_pending_phone', $phone);

        return back()->with('promoter_pin_status', 'sent')->withInput();
    }

    public function completePinReset(Request $request, SmsOtpService $smsOtp): RedirectResponse
    {
        $data = $request->validate([
            'phone_number' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'min:4', 'max:8'],
            'pin' => ['required', 'digits:4', 'confirmed'],
        ]);
        $phone = Phone::normalize($data['phone_number'], '+233');
        $pendingPhone = $request->session()->get('promoter_pin_pending_phone');
        $profile = $phone && $phone === $pendingPhone
            ? PromoterProfile::query()->where('phone', $phone)->where('status', PromoterProfile::STATUS_ACTIVE)->first()
            : null;

        $approved = false;
        if ($profile) {
            try {
                $approved = $smsOtp->verifyOtp($phone, $data['otp']);
            } catch (\Throwable $exception) {
                Log::warning('Promoter PIN OTP verification failed', [
                    'promoter_id' => $profile->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if (! $profile || ! $approved) {
            throw ValidationException::withMessages([
                'promoter_pin_otp' => 'The verification code is invalid or has expired.',
            ]);
        }

        if (in_array($data['pin'], config('auth_phone.pin.weak_values', []), true)) {
            throw ValidationException::withMessages(['pin' => 'Choose a less predictable PIN.']);
        }

        $profile->forceFill([
            'access_pin_hash' => Hash::make($data['pin']),
            'last_login_at' => now(),
        ])->save();
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_AUTHENTICATED_PROFILE_ID, $profile->id);
        $request->session()->forget('promoter_pin_pending_phone');

        return redirect()->route('promoters.index')->with('status', 'promoter-pin-ready');
    }

    public function visit(Request $request, PromoterProfile $promoter, PromoterAttributionService $attribution): RedirectResponse
    {
        $attribution->rememberVisit($request, $promoter);

        return redirect()->route('marketplace.sellers', ['utm_source' => 'promoter', 'utm_campaign' => $promoter->public_code]);
    }

    public function product(
        Request $request,
        PromoterProfile $promoter,
        Product $product,
        PromoterAttributionService $attribution
    ): RedirectResponse {
        abort_unless($product->is_active && $product->isPayable(), 404);
        $attribution->rememberVisit($request, $promoter, $product);

        return redirect()->route('public.product', [
            'product_slug' => $product->slug,
            'utm_source' => 'promoter',
            'utm_medium' => 'referral',
            'utm_campaign' => $promoter->public_code,
        ]);
    }

    private function authenticatedProfile(Request $request): ?PromoterProfile
    {
        if ($request->user()) {
            $owned = PromoterProfile::query()
                ->where('user_id', $request->user()->id)
                ->where('status', PromoterProfile::STATUS_ACTIVE)
                ->first();
            if ($owned) {
                return $owned;
            }
        }

        $profileId = $request->session()->get(self::SESSION_AUTHENTICATED_PROFILE_ID);

        return is_string($profileId)
            ? PromoterProfile::query()->whereKey($profileId)->where('status', PromoterProfile::STATUS_ACTIVE)->first()
            : null;
    }
}
