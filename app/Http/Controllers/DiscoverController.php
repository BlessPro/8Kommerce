<?php

namespace App\Http\Controllers;

use App\Models\EditorialArticle;
use App\Models\FeaturedPlacement;
use App\Models\SellerRecognition;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    public function index(): View
    {
        return view('discover.index', [
            'articles' => EditorialArticle::query()->where('status', 'published')->where('published_at', '<=', now())->with('seller.sellerProfile')->latest('published_at')->paginate(12),
            'featuredProducts' => FeaturedPlacement::query()->active()->where('placement', 'marketplace_home')->with('product.user.sellerProfile')->latest()->take(8)->get(),
            'recognition' => SellerRecognition::query()->with(['user.sellerProfile', 'article'])->latest('period_start')->first(),
        ]);
    }

    public function show(EditorialArticle $article): View
    {
        abort_unless($article->status === 'published' && $article->published_at?->lte(now()), 404);
        $article->load('seller.sellerProfile');

        return view('discover.show', ['article' => $article, 'products' => $article->products()]);
    }
}
