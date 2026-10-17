<?php

namespace App\Http\Controllers;

use App\Models\PromotionSubmission;
use Illuminate\Http\RedirectResponse;

class PromotionRedirectController extends Controller
{
    public function __invoke(string $code): RedirectResponse
    {
        $submission = PromotionSubmission::query()->with('product')->where('tracking_code', $code)->firstOrFail();
        abort_unless($submission->product?->slug, 404);

        return redirect()->route('public.product', [
            'product_slug' => $submission->product->slug,
            'utm_source' => $submission->channel ?: '8kommerce',
            'utm_medium' => 'editorial',
            'utm_campaign' => $submission->tracking_code,
        ]);
    }
}
