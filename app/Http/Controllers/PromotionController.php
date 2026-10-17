<?php

namespace App\Http\Controllers;

use App\Models\PromotionSubmission;
use App\Services\SellerRatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    public function index(Request $request, SellerRatingService $ratings): View
    {
        $user = $request->user();

        return view('dashboard.promotions.index', [
            'products' => $user->products()->where('is_active', true)->whereNotIn('status', ['unavailable', 'trashed'])->latest()->get(),
            'submissions' => $user->promotionSubmissions()->with('product')->latest()->get(),
            'monthlyLimit' => $user->promotionSubmissionLimit(),
            'monthlyUsed' => $user->promotionSubmissions()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'ratingSummary' => $ratings->summary($user->id),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $used = $user->promotionSubmissions()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        abort_if($used >= $user->promotionSubmissionLimit(), 422, 'Your promotion nominations for this month have been used.');

        $data = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where(fn ($query) => $query->where('user_id', $user->id)->where('is_active', true))],
            'purpose' => ['required', Rule::in(array_keys(PromotionSubmission::PURPOSES))],
            'seller_note' => ['nullable', 'string', 'max:600'],
        ]);

        $user->promotionSubmissions()->create([
            ...$data,
            'status' => 'submitted',
            'tracking_code' => Str::upper(Str::random(12)),
        ]);

        return back()->with('status', 'promotion-submitted');
    }
}
