@php($title = $article->seo_title ?: $article->title)
@extends('layouts.public')
@section('og_title', $article->seo_title ?: $article->title)
@section('og_description', $article->seo_description ?: $article->excerpt)
@section('content')
<article class="mx-auto max-w-4xl">
    <a href="{{ route('discover.index') }}" class="text-sm font-semibold text-emerald-700">← Discover</a>
    <header class="mt-7 border-b border-slate-200 pb-8"><p class="lp-eyebrow text-emerald-700">{{ \App\Models\EditorialArticle::TYPES[$article->type] ?? 'Discover' }}</p><h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">{{ $article->title }}</h1><p class="mt-4 text-lg leading-8 text-slate-600">{{ $article->excerpt }}</p><div class="mt-5 flex flex-wrap items-center gap-3 text-sm text-slate-500"><span>{{ $article->published_at->format('F j, Y') }}</span>@if($article->seller?->sellerProfile)<span>·</span><a class="font-semibold text-slate-800" href="{{ route('public.listing', $article->seller->sellerProfile->public_slug) }}">{{ $article->seller->sellerProfile->business_name }}</a>@endif</div></header>
    @if($article->resolvedCoverImagePath())<img src="{{ asset('storage/'.$article->resolvedCoverImagePath()) }}" alt="" class="mt-8 aspect-[16/9] w-full rounded-lg object-cover">@endif
    <div class="prose prose-slate mt-8 max-w-none whitespace-pre-line text-base leading-8 text-slate-700">{{ $article->body }}</div>
    @if($products->isNotEmpty())<section class="mt-12 border-t border-slate-200 pt-8"><h2 class="text-2xl font-semibold text-slate-950">Products in this story</h2><div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3">@foreach($products as $product)<a href="{{ route('public.product', $product->slug) }}"><div class="aspect-square overflow-hidden rounded-lg bg-slate-100">@if($product->image_path)<img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">@endif</div><strong class="mt-2 block text-sm text-slate-900">{{ $product->name }}</strong><span class="text-xs text-slate-500">GHS {{ number_format((float) $product->price, 2) }}</span></a>@endforeach</div></section>@endif
</article>
@endsection
