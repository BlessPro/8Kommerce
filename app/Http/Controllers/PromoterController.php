<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PromoterProfile;
use App\Services\PromoterAttributionService;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromoterController extends Controller
{
    public function index(Request $request): View
    {
        $profile = null;
        $profileId = $request->session()->get(PromoterAttributionService::SESSION_PROMOTER_ID);
        if ($request->user()) {
            $profile = PromoterProfile::query()->where('user_id', $request->user()->id)->first();
        }
        if (! $profile && is_string($profileId)) {
            $profile = PromoterProfile::query()->find($profileId);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('status', [Product::STATUS_IN_STOCK, Product::STATUS_LOW_STOCK, Product::STATUS_PRE_ORDER])
            ->whereHas('user.sellerProfile', fn ($query) => $query->whereNotNull('public_slug'))
            ->with('user.sellerProfile')
            ->latest()
            ->take(8)
            ->get();

        return view('promoters.index', compact('profile', 'products'));
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
            'city' => $data['city'] ?? null,
            'channels' => $data['channels'],
            'note' => $data['note'] ?? null,
            'status' => PromoterProfile::STATUS_ACTIVE,
        ]);

        $request->session()->put(PromoterAttributionService::SESSION_PROMOTER_ID, $profile->id);

        return redirect()->route('promoters.index')->with('status', 'promoter-created');
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
        $attribution->rememberVisit($request, $promoter);

        return redirect()->route('public.product', [
            'product_slug' => $product->slug,
            'utm_source' => 'promoter',
            'utm_medium' => 'referral',
            'utm_campaign' => $promoter->public_code,
        ]);
    }
}
