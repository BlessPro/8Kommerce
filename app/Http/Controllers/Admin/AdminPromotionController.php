<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EditorialArticle;
use App\Models\FeaturedPlacement;
use App\Models\Product;
use App\Models\PromotionSubmission;
use App\Models\SellerRecognition;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPromotionController extends Controller
{
    public function index(): View
    {
        return view('admin.promotions.index', [
            'submissions' => PromotionSubmission::query()->with(['user.sellerProfile', 'product'])->latest()->paginate(20),
            'articles' => EditorialArticle::query()->with('seller.sellerProfile')->latest()->get(),
            'placements' => FeaturedPlacement::query()->with(['user.sellerProfile', 'product'])->latest()->get(),
            'recognitions' => SellerRecognition::query()->with(['user.sellerProfile', 'article'])->latest('period_start')->get(),
            'sellers' => User::query()->whereHas('sellerProfile')->with('sellerProfile')->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->with('user.sellerProfile')->latest()->get(),
        ]);
    }

    public function updateSubmission(Request $request, PromotionSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(PromotionSubmission::STATUSES))],
            'channel' => ['nullable', Rule::in(array_keys(PromotionSubmission::CHANNELS))],
            'scheduled_at' => ['nullable', 'date'],
            'published_url' => ['nullable', 'url:http,https', 'max:2048'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['published_at'] = $data['status'] === 'published' ? ($submission->published_at ?: now()) : $submission->published_at;
        $submission->update($data);

        return back()->with('status', 'promotion-updated');
    }

    public function storePlacement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'placement' => ['required', Rule::in(['marketplace_home', 'category', 'popular', 'search'])],
            'source' => ['required', Rule::in(['editorial', 'package'])],
            'label' => ['nullable', 'string', 'max:80'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);
        $product = Product::findOrFail($data['product_id']);
        FeaturedPlacement::create([...$data, 'user_id' => $product->user_id]);

        return back()->with('status', 'placement-created');
    }

    public function destroyPlacement(FeaturedPlacement $placement): RedirectResponse
    {
        $placement->delete();
        return back()->with('status', 'placement-removed');
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $data = $this->articleData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['created_by_admin_id'] = $request->user()->id;
        $data['published_at'] = $data['status'] === 'published' ? now() : null;
        EditorialArticle::create($data);

        return back()->with('status', 'article-created');
    }

    public function updateArticle(Request $request, EditorialArticle $article): RedirectResponse
    {
        $data = $this->articleData($request, $article);
        if ($data['title'] !== $article->title) $data['slug'] = $this->uniqueSlug($data['title'], $article->id);
        $data['published_at'] = $data['status'] === 'published' ? ($article->published_at ?: now()) : null;
        $article->update($data);

        return back()->with('status', 'article-updated');
    }

    public function storeRecognition(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'article_id' => ['nullable', 'exists:editorial_articles,id'],
            'period_start' => ['required', 'date_format:Y-m'],
            'summary' => ['nullable', 'string', 'max:500'],
        ]);
        SellerRecognition::updateOrCreate(
            ['type' => 'seller_of_month', 'period_start' => $data['period_start'].'-01'],
            [...$data, 'period_start' => $data['period_start'].'-01', 'title' => 'Seller of the Month']
        );

        return back()->with('status', 'recognition-saved');
    }

    private function articleData(Request $request, ?EditorialArticle $article = null): array
    {
        return $request->validate([
            'seller_user_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', Rule::in(array_keys(EditorialArticle::TYPES))],
            'title' => ['required', 'string', 'max:160'],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:30000'],
            'product_ids' => ['nullable', 'array', 'max:12'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published'])],
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 2;
        while (EditorialArticle::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }
        return $slug;
    }
}
