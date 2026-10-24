@php
    $title = 'Storefront';
    $themeOptions = [
        'classic' => ['name' => 'Classic', 'tag' => 'Versatile', 'description' => 'Balanced cards and familiar shopping patterns for almost any small business.'],
        'perfume' => ['name' => 'Perfume', 'tag' => 'Fragrance & beauty', 'description' => 'An editorial fragrance shop with a campaign cover, refined type and focused product grid.'],
        'market' => ['name' => 'Marketplace', 'tag' => 'Large catalogues', 'description' => 'A compact, quick-add catalogue for electronics, groceries and general retail.'],
        'fashion' => ['name' => 'Fashion', 'tag' => 'Apparel & accessories', 'description' => 'Campaign-led boutique with editorial categories, quick view and a polished shopping bag.'],
    ];
    $storefrontSettings = $profile->resolvedStorefrontSettings();
    $accentLabels = ['forest' => 'Forest', 'ocean' => 'Ocean', 'terracotta' => 'Terracotta', 'charcoal' => 'Charcoal', 'plum' => 'Plum', 'amber' => 'Amber', 'rose' => 'Rose', 'teal' => 'Teal', 'ink' => 'Ink'];
    $socialUsername = function (string $network) use ($storefrontSettings): string {
        $stored = trim((string) ($storefrontSettings[$network.'_username'] ?? ''));
        if ($stored !== '') return ltrim($stored, '@');
        $path = parse_url((string) ($storefrontSettings[$network.'_url'] ?? ''), PHP_URL_PATH);
        return ltrim(trim((string) $path, '/'), '@');
    };
    $storefrontUrl = route('public.listing', $profile->public_slug);
@endphp
@extends('layouts.dashboard')

@section('content')
    <div class="storefront-editor-workspace">
        <section class="storefront-editor-panel lp-card p-5 sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-2xl">
                    <div class="flex items-center gap-2">
                        <p class="lp-eyebrow">Design studio</p>
                        <span class="storefront-editor-live"><i></i>Live canvas</span>
                        <span class="storefront-editor-progress" data-design-progress>Checking setup…</span>
                    </div>
                    <h2 class="mt-2 text-xl font-semibold tracking-tight text-slate-950">Shape your customer experience</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Work through one focused section at a time. Every change appears instantly in the fixed preview.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('public.listing', $profile->public_slug) }}" target="_blank" rel="noopener" class="lp-button-secondary">View live store</a>
                    <button type="submit" form="storefront-settings-form" class="lp-button-brand">Save and publish</button>
                </div>
            </div>

            <aside class="storefront-editor-guide" data-storefront-guide>
                <div class="storefront-editor-guide-head">
                    <div><span>Quick start</span><h3>Make the shop yours</h3></div>
                    <button type="button" data-storefront-guide-dismiss aria-label="Dismiss quick-start guide">Got it</button>
                </div>
                <ol>
                    <li><span>1</span><p><strong>Add your identity</strong><small>Upload a square logo and a wide cover image.</small></p></li>
                    <li><span>2</span><p><strong>Choose the look</strong><small>Pick a template, colour and type style while the preview updates.</small></p></li>
                    <li><span>3</span><p><strong>Review and publish</strong><small>Save the design, then open the complete customer view.</small></p></li>
                </ol>
                <a href="{{ route('onboarding.index') }}">More setup help <span aria-hidden="true">→</span></a>
            </aside>

            <div class="storefront-editor-scroll">
            @if(session('status') === 'storefront-updated')
                <div class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">Your storefront settings are now live.</div>
            @elseif(session('status') === 'storefront-reset')
                <div class="mt-5 rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700" role="status">Storefront customisations were reset.</div>
            @endif

            @if($errors->any())
                <div class="mt-5 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                    <p class="font-semibold">Please correct these settings:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <details class="storefront-editor-section mt-5" data-editor-section open>
                <summary class="storefront-editor-summary">
                    <span class="storefront-editor-step">01</span>
                    <span class="storefront-editor-summary-copy"><strong>Storefront template</strong><small>Choose the visual foundation</small></span>
                    <span class="storefront-editor-value">{{ $themeOptions[$storefrontTheme]['name'] }}</span>
                    <span class="storefront-editor-chevron" aria-hidden="true"></span>
                </summary>
                <div class="storefront-editor-section-body">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach($themeOptions as $key => $option)
                        <a href="{{ route('public.preview', ['theme' => $key]) }}" class="storefront-choice storefront-choice-{{ $key }} block overflow-hidden rounded-lg border bg-white transition-colors {{ $storefrontTheme === $key ? 'border-emerald-600 ring-2 ring-emerald-600 ring-offset-2' : 'border-slate-200 hover:border-slate-400' }}" aria-current="{{ $storefrontTheme === $key ? 'true' : 'false' }}">
                            <div class="storefront-choice-preview" aria-hidden="true">
                                <span class="storefront-choice-bar"></span>
                                <span class="storefront-choice-title"></span>
                                <span class="storefront-choice-grid"><i></i><i></i><i></i><i></i></span>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-slate-950">{{ $option['name'] }}</span>
                                    @if($profile->resolvedStorefrontTheme() === $key)<span class="rounded bg-emerald-50 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Live</span>@endif
                                </div>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $option['tag'] }}</p>
                                <p class="mt-2 text-xs leading-5 text-slate-600">{{ $option['description'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                </div>
            </details>

            <form id="storefront-settings-form" method="POST" action="{{ route('public.preview.update') }}" enctype="multipart/form-data" class="mt-3 space-y-3" data-storefront-editor-form data-form-draft="storefront-design-{{ auth()->id() }}" data-form-clear-on-load="{{ session('status') === 'storefront-updated' ? '1' : '0' }}" data-has-logo="{{ $storefrontSettings['logo_path'] ? '1' : '0' }}" data-has-cover="{{ $storefrontSettings['hero_image_path'] ? '1' : '0' }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="storefront_theme" value="{{ $storefrontTheme }}">

                <details class="storefront-editor-section" data-editor-section>
                    <summary class="storefront-editor-summary">
                        <span class="storefront-editor-step">02</span>
                        <span class="storefront-editor-summary-copy"><strong>Brand essentials</strong><small>Logo, colour and store message</small></span>
                        <span class="storefront-editor-value">{{ $accentLabels[$storefrontSettings['accent']] ?? 'Custom' }}</span>
                        <span class="storefront-editor-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="storefront-editor-section-body">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="storefront_logo" class="block text-sm font-medium text-slate-700">Business logo</label>
                            <label class="storefront-asset-drop storefront-asset-drop-logo mt-2" for="storefront_logo">
                                <input id="storefront_logo" name="storefront_logo" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only">
                                <span class="storefront-asset-icon" aria-hidden="true">+</span>
                                <strong>{{ $storefrontSettings['logo_path'] ? 'Replace logo' : 'Add logo' }}</strong>
                                <small>Square image</small>
                            </label>
                            <p class="mt-1 text-xs text-slate-500">PNG, JPG or WebP. Maximum 2 MB.</p>
                            @if($storefrontSettings['logo_path'])
                                <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                                    <input type="hidden" name="remove_logo" value="0">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                                    Remove current logo
                                </label>
                            @endif
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-700">Colour palette</span>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach(\App\Models\SellerProfile::STOREFRONT_ACCENTS as $key => $palette)
                                    <label class="storefront-colour-choice" title="{{ $accentLabels[$key] }}">
                                        <input type="radio" name="storefront_accent" value="{{ $key }}" class="sr-only" @checked(old('storefront_accent', $storefrontSettings['accent']) === $key)>
                                        <span class="storefront-palette-swatch" aria-hidden="true"><i style="background: {{ $palette['accent'] }}"></i><i style="background: {{ $palette['secondary'] }}"></i><i style="background: {{ $palette['surface'] }}"></i><i style="background: {{ $palette['ink'] }}"></i></span>
                                        <small>{{ $accentLabels[$key] }}</small>
                                    </label>
                                @endforeach
                                <label class="storefront-colour-choice storefront-colour-custom" title="Custom colour">
                                    <input type="radio" name="storefront_accent" value="custom" class="sr-only" @checked(old('storefront_accent', $storefrontSettings['accent']) === 'custom')>
                                    <input type="color" name="custom_accent" value="{{ old('custom_accent', $storefrontSettings['custom_accent']) }}" aria-label="Custom brand colour">
                                    <small>Custom</small>
                                </label>
                            </div>
                            <p class="storefront-contrast-note mt-3" data-contrast-note>Text colours are adjusted automatically for readability.</p>
                        </div>
                    </div>
                    <div class="storefront-editor-subheading"><span>Store message</span><small>What customers see first</small></div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="storefront_tagline" class="block text-sm font-medium text-slate-700">Short tagline</label>
                            <input id="storefront_tagline" name="storefront_tagline" maxlength="80" value="{{ old('storefront_tagline', $storefrontSettings['tagline']) }}" placeholder="Quality goods, made for everyday life" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>
                        <div class="md:row-span-2">
                            <label for="storefront_description" class="block text-sm font-medium text-slate-700">Store description</label>
                            <textarea id="storefront_description" name="storefront_description" rows="5" maxlength="240" placeholder="Tell customers what makes this business useful or different." class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('storefront_description', $storefrontSettings['description']) }}</textarea>
                        </div>
                        <div>
                            <label for="announcement_text" class="block text-sm font-medium text-slate-700">Announcement bar</label>
                            <input id="announcement_text" name="announcement_text" maxlength="100" value="{{ old('announcement_text', $storefrontSettings['announcement_text']) }}" placeholder="Free delivery this weekend" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>
                        <div class="md:col-span-2">
                            <label for="announcement_url" class="block text-sm font-medium text-slate-700">Announcement link <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="announcement_url" name="announcement_url" type="url" value="{{ old('announcement_url', $storefrontSettings['announcement_url']) }}" placeholder="https://example.com/offer" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>
                    </div>
                    </div>
                </details>

                <details class="storefront-editor-section" data-editor-section>
                    <summary class="storefront-editor-summary">
                        <span class="storefront-editor-step">03</span>
                        <span class="storefront-editor-summary-copy"><strong>Catalogue and merchandising</strong><small>Products, cover and discovery</small></span>
                        <span class="storefront-editor-value">{{ ucfirst($storefrontSettings['grid_density']) }}</span>
                        <span class="storefront-editor-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="storefront-editor-section-body">
                    <div class="grid gap-5 md:grid-cols-3">
                        <div>
                            <label for="image_ratio" class="block text-sm font-medium text-slate-700">Image shape</label>
                            <select id="image_ratio" name="image_ratio" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="portrait" @selected(old('image_ratio', $storefrontSettings['image_ratio']) === 'portrait')>Portrait (4:5)</option>
                                <option value="square" @selected(old('image_ratio', $storefrontSettings['image_ratio']) === 'square')>Square (1:1)</option>
                                <option value="landscape" @selected(old('image_ratio', $storefrontSettings['image_ratio']) === 'landscape')>Landscape (4:3)</option>
                                <option value="natural" @selected(old('image_ratio', $storefrontSettings['image_ratio']) === 'natural')>Original image</option>
                            </select>
                        </div>
                        <div>
                            <label for="grid_density" class="block text-sm font-medium text-slate-700">Catalogue spacing</label>
                            <select id="grid_density" name="grid_density" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="compact" @selected(old('grid_density', $storefrontSettings['grid_density']) === 'compact')>Compact</option>
                                <option value="comfortable" @selected(old('grid_density', $storefrontSettings['grid_density']) === 'comfortable')>Comfortable</option>
                                <option value="spacious" @selected(old('grid_density', $storefrontSettings['grid_density']) === 'spacious')>Spacious</option>
                            </select>
                        </div>
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-slate-700">Default sorting</label>
                            <select id="sort_order" name="sort_order" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="newest" @selected(old('sort_order', $storefrontSettings['sort_order']) === 'newest')>Newest first</option>
                                <option value="price_asc" @selected(old('sort_order', $storefrontSettings['sort_order']) === 'price_asc')>Price: low to high</option>
                                <option value="price_desc" @selected(old('sort_order', $storefrontSettings['sort_order']) === 'price_desc')>Price: high to low</option>
                                <option value="name" @selected(old('sort_order', $storefrontSettings['sort_order']) === 'name')>Name: A to Z</option>
                                <option value="availability" @selected(old('sort_order', $storefrontSettings['sort_order']) === 'availability')>Availability</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach([
                            'show_description' => ['Product descriptions', 'Help customers understand each item.'],
                            'show_stock' => ['Stock labels', 'Show in-stock, low-stock and pre-order states.'],
                            'show_quick_add' => ['Quick add', 'Let customers add items without opening details.'],
                            'show_whatsapp' => ['WhatsApp action', 'Let customers ask about a product directly.'],
                        ] as $key => [$label, $help])
                            <label class="storefront-toggle-row">
                                <span><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input type="checkbox" name="{{ $key }}" value="1" @checked((bool) old($key, $storefrontSettings[$key]))>
                            </label>
                        @endforeach
                    </div>
                    <div class="storefront-editor-subheading"><span>Cover presentation</span><small>Wide imagery at the top of the shop</small></div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="storefront_hero_image" class="block text-sm font-medium text-slate-700">Hero image</label>
                            <label class="storefront-asset-drop storefront-asset-drop-cover mt-2" for="storefront_hero_image">
                                <input id="storefront_hero_image" name="storefront_hero_image" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only">
                                <span class="storefront-asset-icon" aria-hidden="true">+</span>
                                <strong>{{ $storefrontSettings['hero_image_path'] ? 'Replace cover photo' : 'Add a cover photo' }}</strong>
                                <small>Recommended 2000 × 800</small>
                            </label>
                            <p class="mt-1 text-xs text-slate-500">Use a wide, uncluttered image. Maximum 4 MB.</p>
                            @if($storefrontSettings['hero_image_path'])
                                <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                                    <input type="hidden" name="remove_hero_image" value="0">
                                    <input type="checkbox" name="remove_hero_image" value="1" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                                    Remove hero image
                                </label>
                            @endif
                        </div>
                        <div>
                            <label for="hero_layout" class="block text-sm font-medium text-slate-700">Hero layout</label>
                            <select id="hero_layout" name="hero_layout" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="compact" @selected(old('hero_layout', $storefrontSettings['hero_layout']) === 'compact')>Compact header</option>
                                <option value="split" @selected(old('hero_layout', $storefrontSettings['hero_layout']) === 'split')>Split image and text</option>
                                <option value="banner" @selected(old('hero_layout', $storefrontSettings['hero_layout']) === 'banner')>Wide image banner</option>
                            </select>
                        </div>
                    </div>
                    <div class="storefront-editor-subheading"><span>Featured products</span><small>Guide customers towards key items</small></div>
                    <input type="hidden" name="featured_product_ids_present" value="1">
                    <div class="mt-4">
                        <div class="flex items-end justify-between gap-3">
                            <div><p class="text-sm font-medium text-slate-700">Featured products</p><p class="mt-1 text-xs text-slate-500">Choose up to six products. They appear first in the catalogue.</p></div>
                            <span class="text-xs font-semibold text-slate-500" data-featured-count>0/6 selected</span>
                        </div>
                        <div class="mt-3 grid max-h-56 gap-2 overflow-y-auto rounded-md border border-slate-200 p-2 sm:grid-cols-2">
                            @forelse($products as $product)
                                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-transparent px-3 py-2 hover:border-slate-200 hover:bg-slate-50">
                                    <input type="checkbox" name="featured_product_ids[]" value="{{ $product->id }}" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" data-featured-product @checked(in_array((int) $product->id, array_map('intval', old('featured_product_ids', $storefrontSettings['featured_product_ids'])), true))>
                                    <span class="min-w-0"><strong class="block truncate text-sm font-medium text-slate-800">{{ $product->name }}</strong><small class="text-xs text-slate-500">{{ \App\Support\Money::format($product->price, $currency) }}</small></span>
                                </label>
                            @empty
                                <p class="p-3 text-sm text-slate-500 sm:col-span-2">Add active products before choosing featured items.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach([
                            'show_search' => ['Product search', 'Help customers find an item quickly.'],
                            'show_trust_bar' => ['Trust information', 'Show payment, delivery and support details.'],
                            'show_social_links' => ['Social links', 'Display configured social profiles in the footer.'],
                        ] as $key => [$label, $help])
                            <label class="storefront-toggle-row">
                                <span><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input type="checkbox" name="{{ $key }}" value="1" @checked((bool) old($key, $storefrontSettings[$key]))>
                            </label>
                        @endforeach
                    </div>
                    </div>
                </details>

                <details class="storefront-editor-section" data-editor-section>
                    <summary class="storefront-editor-summary">
                        <span class="storefront-editor-step">04</span>
                        <span class="storefront-editor-summary-copy"><strong>Customer confidence</strong><small>Delivery, returns and social links</small></span>
                        <span class="storefront-editor-value">Trust details</span>
                        <span class="storefront-editor-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="storefront-editor-section-body">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="delivery_message" class="block text-sm font-medium text-slate-700">Delivery message</label>
                            <input id="delivery_message" name="delivery_message" maxlength="80" value="{{ old('delivery_message', $storefrontSettings['delivery_message']) }}" placeholder="Delivery within Accra in 1–2 days" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label for="returns_message" class="block text-sm font-medium text-slate-700">Returns message</label>
                            <input id="returns_message" name="returns_message" maxlength="80" value="{{ old('returns_message', $storefrontSettings['returns_message']) }}" placeholder="Returns accepted within 7 days" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>
                        @foreach(['instagram' => 'Instagram', 'tiktok' => 'TikTok', 'facebook' => 'Facebook'] as $network => $label)
                            <div>
                                <label for="{{ $network }}_username" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
                                <div class="storefront-username-field mt-2"><span>@</span><input id="{{ $network }}_username" name="{{ $network }}_username" value="{{ old($network.'_username', $socialUsername($network)) }}" placeholder="username" autocomplete="off"></div>
                            </div>
                        @endforeach
                    </div>
                    </div>
                </details>

                <details class="storefront-editor-section" data-editor-section>
                    <summary class="storefront-editor-summary">
                        <span class="storefront-editor-step">05</span>
                        <span class="storefront-editor-summary-copy"><strong>Finishing style</strong><small>Typography, cards and buttons</small></span>
                        <span class="storefront-editor-value">{{ ucfirst($storefrontSettings['font_preset']) }}</span>
                        <span class="storefront-editor-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="storefront-editor-section-body">
                    <div class="space-y-5">
                        <div><p class="text-sm font-medium text-slate-700">Font style</p><div class="mt-2 grid gap-2 sm:grid-cols-3">
                            @foreach(['clean' => ['Modern', 'Clean and confident'], 'editorial' => ['Classic', 'Elegant serif headings'], 'rounded' => ['Friendly', 'Round and approachable']] as $key => [$label, $help])
                                <label class="storefront-option-card storefront-font-option-{{ $key }}"><input type="radio" name="font_preset" value="{{ $key }}" class="sr-only" @checked(old('font_preset', $storefrontSettings['font_preset']) === $key)><strong>{{ $label }}</strong><small>{{ $help }}</small></label>
                            @endforeach
                        </div></div>
                        <div><p class="text-sm font-medium text-slate-700">Product card treatment</p><div class="mt-2 grid gap-2 sm:grid-cols-3">
                            @foreach(['bordered' => ['Bordered', 'Clear definition'], 'flat' => ['Flat', 'Minimal catalogue'], 'elevated' => ['Elevated', 'More separation']] as $key => [$label, $help])
                                <label class="storefront-option-card storefront-card-option-{{ $key }}"><input type="radio" name="card_style" value="{{ $key }}" class="sr-only" @checked(old('card_style', $storefrontSettings['card_style']) === $key)><span class="storefront-option-demo"></span><strong>{{ $label }}</strong><small>{{ $help }}</small></label>
                            @endforeach
                        </div></div>
                        <div class="grid gap-5 sm:grid-cols-3">
                            <div><p class="text-sm font-medium text-slate-700">Card corners</p><div class="mt-2 flex gap-2">@foreach(['rounded' => 'Rounded', 'square' => 'Square'] as $key => $label)<label class="storefront-segment"><input type="radio" name="card_corner" value="{{ $key }}" class="sr-only" @checked(old('card_corner', $storefrontSettings['card_corner']) === $key)><span>{{ $label }}</span></label>@endforeach</div></div>
                            <div><p class="text-sm font-medium text-slate-700">Header</p><div class="mt-2 flex gap-2">@foreach(['left' => 'Left', 'center' => 'Centred'] as $key => $label)<label class="storefront-segment"><input type="radio" name="header_alignment" value="{{ $key }}" class="sr-only" @checked(old('header_alignment', $storefrontSettings['header_alignment']) === $key)><span>{{ $label }}</span></label>@endforeach</div></div>
                            <div><p class="text-sm font-medium text-slate-700">Buttons</p><div class="mt-2 flex gap-2">@foreach(['solid' => 'Solid', 'outline' => 'Outline'] as $key => $label)<label class="storefront-segment"><input type="radio" name="button_style" value="{{ $key }}" class="sr-only" @checked(old('button_style', $storefrontSettings['button_style']) === $key)><span>{{ $label }}</span></label>@endforeach</div></div>
                        </div>
                    </div>
                    </div>
                </details>

                <details class="storefront-editor-section" data-editor-section>
                    <summary class="storefront-editor-summary">
                        <span class="storefront-editor-step">06</span>
                        <span class="storefront-editor-summary-copy"><strong>Business and sharing</strong><small>Contact details and search preview</small></span>
                        <span class="storefront-editor-value">{{ $storefrontSettings['seo_title'] ? 'SEO ready' : 'Optional' }}</span>
                        <span class="storefront-editor-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="storefront-editor-section-body">
                    <div>
                        <label class="storefront-toggle-row">
                            <span><strong>Business information</strong><small>Show location, opening hours and contact details near the footer.</small></span>
                            <input type="hidden" name="show_business_info" value="0">
                            <input type="checkbox" name="show_business_info" value="1" @checked((bool) old('show_business_info', $storefrontSettings['show_business_info']))>
                        </label>
                    </div>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="business_address" class="block text-sm font-medium text-slate-700">Business location</label>
                            <input id="business_address" name="business_address" maxlength="160" value="{{ old('business_address', $storefrontSettings['business_address']) }}" placeholder="Osu, Accra" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label for="business_hours" class="block text-sm font-medium text-slate-700">Opening hours</label>
                            <input id="business_hours" name="business_hours" maxlength="120" value="{{ old('business_hours', $storefrontSettings['business_hours']) }}" placeholder="Mon–Sat, 9:00–18:00" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label for="seo_title" class="block text-sm font-medium text-slate-700">Share and search title</label>
                            <input id="seo_title" name="seo_title" maxlength="60" value="{{ old('seo_title', $storefrontSettings['seo_title']) }}" placeholder="{{ $profile->business_name }}" class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                            <p class="mt-1 text-xs text-slate-500">Maximum 60 characters.</p>
                        </div>
                        <div>
                            <label for="seo_description" class="block text-sm font-medium text-slate-700">Share and search description</label>
                            <textarea id="seo_description" name="seo_description" rows="3" maxlength="160" placeholder="A clear summary shown when your store link is shared." class="mt-2 w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('seo_description', $storefrontSettings['seo_description']) }}</textarea>
                            <p class="mt-1 text-xs text-slate-500">Maximum 160 characters.</p>
                        </div>
                    </div>
                    <div class="storefront-qr-panel mt-5">
                        <div><p class="text-sm font-semibold text-slate-900">Store QR code</p><p class="mt-1 text-xs leading-5 text-slate-500">Print it on flyers, packaging or your stall so customers can scan directly to your shop.</p><p class="mt-2 truncate text-xs font-medium text-emerald-800">{{ $storefrontUrl }}</p></div>
                        <canvas width="144" height="144" data-storefront-qr aria-label="QR code for {{ $storefrontUrl }}"></canvas>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="lp-button-secondary" data-qr-download="png">Download PNG</button>
                            <button type="button" class="lp-button-secondary" data-qr-download="svg">Download SVG</button>
                            <button type="button" class="lp-button-secondary" data-copy-store-link>Copy link</button>
                        </div>
                    </div>
                    </div>
                </details>

                <div class="storefront-editor-actions">
                    <span class="text-xs font-medium text-amber-700" data-unsaved-indicator hidden>Unsaved changes</span>
                    <button type="submit" class="lp-button-brand">Save and publish</button>
                </div>
            </form>

            <form method="POST" action="{{ route('public.preview.reset') }}" class="mt-4 border-t border-slate-200 pt-4" onsubmit="return confirm('Reset all storefront customisations? Your products and orders will not be affected.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-rose-700 hover:text-rose-800">Reset customisations</button>
            </form>
            </div>
        </section>

        <aside class="storefront-phone-column" aria-label="Phone storefront preview">
            <div class="storefront-phone-heading">
                <div><p class="lp-eyebrow">Customer view</p><h2 class="mt-1 text-base font-semibold text-slate-950">Phone preview</h2></div>
                <div class="text-right"><span class="block rounded bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">iPhone 14 · 390 × 844</span><a href="{{ $storefrontUrl }}?preview=1" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-semibold text-emerald-800 hover:underline">Open full preview ↗</a></div>
            </div>
            <div class="storefront-phone-device">
                <span class="storefront-phone-side storefront-phone-side-silent" aria-hidden="true"></span>
                <span class="storefront-phone-side storefront-phone-side-volume-up" aria-hidden="true"></span>
                <span class="storefront-phone-side storefront-phone-side-volume-down" aria-hidden="true"></span>
                <span class="storefront-phone-side storefront-phone-side-power" aria-hidden="true"></span>
                <div class="storefront-phone-notch" aria-hidden="true"><span></span></div>
                <div class="storefront-phone-status" aria-hidden="true"><span>9:41</span><span>● ◒ △</span></div>
                <div class="storefront-phone-screen">
                    <div class="storefront-phone-viewport" data-phone-preview-scroll tabindex="0" aria-label="Scrollable iPhone 14 storefront preview">
                        <div class="storefront-phone-browser-bar"><span class="storefront-phone-lock">◆</span><span class="truncate">8kommerce · {{ $profile->public_slug }}</span></div>
                        <div class="storefront-phone-content">@include('public.partials.listing-content')</div>
                    </div>
                </div>
                <div class="storefront-phone-home" aria-hidden="true"></div>
            </div>
            <p class="mt-3 text-center text-xs leading-5 text-slate-500">Changes appear here before you publish.</p>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('[data-storefront-editor-form]');
            const preview = document.querySelector('.storefront-phone-content .storefront');
            const unsaved = document.querySelector('[data-unsaved-indicator]');
            const palettes = @json(\App\Models\SellerProfile::STOREFRONT_ACCENTS);
            const editorSections = Array.from(document.querySelectorAll('[data-editor-section]'));
            const storefrontUrl = @json($storefrontUrl);
            const businessName = @json($profile->business_name);
            let dirty = false;
            if (!form || !preview) return;

            const guide = document.querySelector('[data-storefront-guide]');
            const guideKey = 'storefront_guide_dismissed_{{ $profile->id }}';
            try { if (localStorage.getItem(guideKey) === '1') guide?.setAttribute('hidden', ''); } catch (_) {}
            document.querySelector('[data-storefront-guide-dismiss]')?.addEventListener('click', function () {
                guide?.setAttribute('hidden', '');
                try { localStorage.setItem(guideKey, '1'); } catch (_) {}
            });

            let changingSection = false;
            editorSections.forEach((section, index) => {
                section.addEventListener('toggle', function () {
                    if (changingSection || !section.open) return;
                    changingSection = true;
                    editorSections.forEach((other) => { if (other !== section) other.open = false; });
                    changingSection = false;
                    try { sessionStorage.setItem('storefront_editor_section', String(index)); } catch (_) {}
                });
            });
            try {
                const savedSection = Number(sessionStorage.getItem('storefront_editor_section'));
                if (Number.isInteger(savedSection) && editorSections[savedSection]) {
                    editorSections.forEach((section, index) => { section.open = index === savedSection; });
                }
            } catch (_) {}

            function field(name) { return form.elements.namedItem(name); }
            function checked(name) {
                return Array.from(form.querySelectorAll('[name="' + name + '"]')).some((control) => control.type === 'checkbox' && control.checked);
            }
            function setDirty() { dirty = true; if (unsaved) unsaved.hidden = false; }
            function replaceClass(prefix, value) {
                Array.from(preview.classList).filter((name) => name.startsWith(prefix)).forEach((name) => preview.classList.remove(name));
                preview.classList.add(prefix + value);
            }
            function mixHex(first, second, weight) {
                const a = first.replace('#', '');
                const b = second.replace('#', '');
                const output = [0, 2, 4].map((offset) => {
                    const start = parseInt(a.slice(offset, offset + 2), 16);
                    const end = parseInt(b.slice(offset, offset + 2), 16);
                    return Math.round(start + ((end - start) * weight)).toString(16).padStart(2, '0');
                });
                return '#' + output.join('');
            }
            function customPalette(hex) {
                return {
                    accent: hex,
                    dark: mixHex(hex, '#000000', 0.24),
                    tint: mixHex(hex, '#ffffff', 0.9),
                    secondary: mixHex(hex, '#d08b52', 0.5),
                    surface: mixHex(hex, '#ffffff', 0.94),
                    ink: mixHex(hex, '#111827', 0.78),
                };
            }
            function updateCompletion() {
                const essentials = [
                    form.dataset.hasLogo === '1' || Boolean(field('storefront_logo')?.files?.length),
                    form.dataset.hasCover === '1' || Boolean(field('storefront_hero_image')?.files?.length),
                    Boolean(form.querySelector('[name="storefront_accent"]:checked')),
                    String(field('storefront_tagline')?.value || '').trim() !== '',
                    form.querySelectorAll('[data-featured-product]:checked').length > 0,
                ];
                const completed = essentials.filter(Boolean).length;
                const progress = document.querySelector('[data-design-progress]');
                if (progress) progress.textContent = completed + '/5 essentials';
            }
            function syncFeaturedProducts(changedControl) {
                const controls = Array.from(form.querySelectorAll('[data-featured-product]'));
                let selected = controls.filter((control) => control.checked);
                if (selected.length > 6 && changedControl) {
                    changedControl.checked = false;
                    selected = controls.filter((control) => control.checked);
                }
                controls.forEach((control) => { control.disabled = selected.length >= 6 && !control.checked; });
                const count = document.querySelector('[data-featured-count]');
                if (count) count.textContent = selected.length + '/6 selected';
                const selectedIds = selected.map((control) => control.value);
                preview.querySelectorAll('[data-product-id]').forEach((card) => {
                    let badge = card.querySelector('.storefront-featured-badge');
                    const isSelected = selectedIds.includes(card.dataset.productId);
                    if (isSelected && !badge) {
                        badge = document.createElement('span');
                        badge.className = 'storefront-featured-badge';
                        badge.textContent = 'Featured';
                        card.querySelector('.storefront-product-media')?.appendChild(badge);
                    } else if (!isSelected && badge) {
                        badge.remove();
                    }
                });
            }
            function syncPreview(event) {
                if (event?.target?.name === 'custom_accent') {
                    const customChoice = form.querySelector('[name="storefront_accent"][value="custom"]');
                    if (customChoice) customChoice.checked = true;
                }
                const accent = form.querySelector('[name="storefront_accent"]:checked')?.value || 'forest';
                const customAccent = String(field('custom_accent')?.value || '#073B4C');
                const palette = accent === 'custom' ? customPalette(customAccent) : (palettes[accent] || palettes.forest);
                preview.style.setProperty('--store-accent', palette.accent);
                preview.style.setProperty('--store-accent-dark', palette.dark);
                preview.style.setProperty('--store-tint', palette.tint);
                preview.style.setProperty('--store-secondary', palette.secondary);
                preview.style.setProperty('--store-surface', palette.surface);
                preview.style.setProperty('--store-ink', palette.ink);
                const contrastNote = document.querySelector('[data-contrast-note]');
                if (contrastNote) {
                    const raw = palette.accent.replace('#', '');
                    const luminance = ((parseInt(raw.slice(0, 2), 16) * 299) + (parseInt(raw.slice(2, 4), 16) * 587) + (parseInt(raw.slice(4, 6), 16) * 114)) / 255000;
                    contrastNote.textContent = luminance > 0.66
                        ? 'This colour is light, so a darker shade is used for text and links.'
                        : 'This colour has sufficient contrast for primary actions.';
                }

                const tagline = String(field('storefront_tagline')?.value || '').trim();
                const description = String(field('storefront_description')?.value || '').trim();
                const taglineNode = preview.querySelector('.storefront-tagline');
                const perfumeTitle = preview.querySelector('.storefront-perfume-title');
                const marketTitle = preview.querySelector('.storefront-market-title');
                const descriptionNode = preview.querySelector('.storefront-intro');
                if (taglineNode) taglineNode.textContent = tagline || taglineNode.dataset.storefrontDefault;
                if (perfumeTitle) perfumeTitle.textContent = tagline || perfumeTitle.dataset.storefrontDefault;
                if (marketTitle) marketTitle.textContent = tagline || marketTitle.dataset.storefrontDefault;
                if (descriptionNode) descriptionNode.textContent = description || descriptionNode.dataset.storefrontDefault;

                const announcementText = String(field('announcement_text')?.value || '').trim();
                const announcementUrl = String(field('announcement_url')?.value || '').trim();
                const safeAnnouncementUrl = /^https?:\/\//i.test(announcementUrl) ? announcementUrl : '';
                const announcement = preview.querySelector('.storefront-announcement');
                if (announcement) {
                    announcement.hidden = announcementText === '';
                    announcement.replaceChildren();
                    const copy = document.createElement(safeAnnouncementUrl ? 'a' : 'span');
                    copy.textContent = announcementText;
                    if (safeAnnouncementUrl) { copy.href = safeAnnouncementUrl; copy.target = '_blank'; copy.rel = 'noopener noreferrer'; }
                    announcement.appendChild(copy);
                }

                replaceClass('storefront-image-', field('image_ratio')?.value || 'portrait');
                replaceClass('storefront-density-', field('grid_density')?.value || 'comfortable');
                replaceClass('storefront-hero-layout-', field('hero_layout')?.value || 'compact');
                replaceClass('storefront-font-', field('font_preset')?.value || 'clean');
                replaceClass('storefront-cards-', field('card_style')?.value || 'bordered');
                replaceClass('storefront-card-corners-', field('card_corner')?.value || 'rounded');
                replaceClass('storefront-header-', field('header_alignment')?.value || 'left');
                replaceClass('storefront-buttons-', field('button_style')?.value || 'solid');
                preview.classList.toggle('storefront-hide-description', !checked('show_description'));
                preview.classList.toggle('storefront-hide-stock', !checked('show_stock'));
                preview.classList.toggle('storefront-hide-quick-add', !checked('show_quick_add'));
                preview.classList.toggle('storefront-hide-whatsapp', !checked('show_whatsapp'));

                const search = preview.querySelector('.storefront-search');
                if (search) search.hidden = !checked('show_search');
                const trust = preview.querySelector('[data-storefront-trust]');
                if (trust) trust.hidden = !checked('show_trust_bar');
                [['delivery_message', '[data-storefront-delivery]'], ['returns_message', '[data-storefront-returns]']].forEach(([name, selector]) => {
                    const value = String(field(name)?.value || '').trim();
                    const item = preview.querySelector(selector);
                    if (item) { item.hidden = value === ''; const copy = item.querySelector('small'); if (copy) copy.textContent = value; }
                });
                const businessInfo = preview.querySelector('[data-storefront-business]');
                if (businessInfo) businessInfo.hidden = !checked('show_business_info');
                [['business_address', '[data-storefront-address]'], ['business_hours', '[data-storefront-hours]']].forEach(([name, selector]) => {
                    const value = String(field(name)?.value || '').trim();
                    const item = preview.querySelector(selector);
                    if (item) { item.hidden = value === ''; const copy = item.querySelector('dd'); if (copy) copy.textContent = value; }
                });

                const logoInput = field('storefront_logo');
                const logo = preview.querySelector('.storefront-logo');
                const perfumeBrand = preview.querySelector('.storefront-perfume-brand, .storefront-market-brand, .storefront-fashion-brand');
                if (logo) logo.hidden = checked('remove_logo');
                if (event?.target === logoInput && logoInput.files?.[0] && logo) {
                    const reader = new FileReader();
                    reader.onload = function () {
                        let image = logo.querySelector('img');
                        if (!image) { image = document.createElement('img'); image.alt = 'Logo preview'; logo.appendChild(image); }
                        image.src = reader.result;
                        logo.hidden = false;
                    };
                    reader.readAsDataURL(logoInput.files[0]);
                }
                if (perfumeBrand) {
                    const brandImage = perfumeBrand.querySelector('img');
                    let textBrand = perfumeBrand.querySelector('span');
                    if (!textBrand) {
                        textBrand = document.createElement('span');
                        perfumeBrand.appendChild(textBrand);
                    }
                    textBrand.textContent = businessName;
                    if (brandImage) brandImage.hidden = checked('remove_logo');
                    textBrand.hidden = Boolean(brandImage) && !checked('remove_logo');
                    if (event?.target === logoInput && logoInput.files?.[0]) {
                        const reader = new FileReader();
                        reader.onload = function () {
                            perfumeBrand.replaceChildren();
                            const image = document.createElement('img');
                            image.alt = businessName + ' logo';
                            image.src = reader.result;
                            perfumeBrand.appendChild(image);
                        };
                        reader.readAsDataURL(logoInput.files[0]);
                    }
                }

                const heroInput = field('storefront_hero_image');
                const heroSide = preview.querySelector('.storefront-hero-side');
                let heroMedia = preview.querySelector('.storefront-hero-media');
                const perfumeCover = preview.querySelector('.storefront-perfume-cover, .storefront-market-cover, .storefront-fashion-cover');
                if (heroMedia) heroMedia.hidden = checked('remove_hero_image');
                if (event?.target === heroInput && heroInput.files?.[0] && heroSide) {
                    const reader = new FileReader();
                    reader.onload = function () {
                        if (!heroMedia) {
                            heroMedia = document.createElement('div');
                            heroMedia.className = 'storefront-hero-media';
                            heroMedia.innerHTML = '<img alt="">';
                            heroSide.prepend(heroMedia);
                        }
                        heroMedia.querySelector('img').src = reader.result;
                        heroMedia.hidden = false;
                    };
                    reader.readAsDataURL(heroInput.files[0]);
                }
                if (perfumeCover) {
                    const coverImage = perfumeCover.querySelector('img');
                    let coverPlaceholder = perfumeCover.querySelector('span');
                    if (!coverPlaceholder) {
                        coverPlaceholder = document.createElement('span');
                        coverPlaceholder.textContent = 'Add a cover image in the design editor';
                        perfumeCover.appendChild(coverPlaceholder);
                    }
                    if (coverImage) coverImage.hidden = checked('remove_hero_image');
                    coverPlaceholder.hidden = Boolean(coverImage) && !checked('remove_hero_image');
                    perfumeCover.classList.toggle('is-empty', !coverImage || checked('remove_hero_image'));
                    if (event?.target === heroInput && heroInput.files?.[0]) {
                        const reader = new FileReader();
                        reader.onload = function () {
                            perfumeCover.classList.remove('is-empty');
                            perfumeCover.replaceChildren();
                            const image = document.createElement('img');
                            image.alt = businessName + ' collection';
                            image.src = reader.result;
                            perfumeCover.appendChild(image);
                        };
                        reader.readAsDataURL(heroInput.files[0]);
                    }
                }

                const social = preview.querySelector('[data-storefront-social]');
                if (social) {
                    const socialLinks = [
                        ['instagram_username', 'Instagram', 'https://instagram.com/'],
                        ['facebook_username', 'Facebook', 'https://facebook.com/'],
                        ['tiktok_username', 'TikTok', 'https://tiktok.com/@'],
                    ].map(([name, label, base]) => [String(field(name)?.value || '').trim().replace(/^@/, ''), label, base])
                        .filter(([username]) => username !== '');
                    social.hidden = !checked('show_social_links') || socialLinks.length === 0;
                    const nav = social.querySelector('nav');
                    if (nav) {
                        nav.replaceChildren();
                        socialLinks.forEach(([username, label, base]) => {
                            const link = document.createElement('a');
                            link.href = base + username;
                            link.target = '_blank';
                            link.rel = 'noopener noreferrer';
                            link.textContent = label;
                            nav.appendChild(link);
                        });
                    }
                }

                syncFeaturedProducts(event?.target?.matches?.('[data-featured-product]') ? event.target : null);
                updateCompletion();
            }

            document.querySelectorAll('.storefront-asset-drop').forEach((drop) => {
                const input = drop.querySelector('input[type="file"]');
                input?.addEventListener('change', () => {
                    const filename = input.files?.[0]?.name;
                    const helper = drop.querySelector('small');
                    if (filename && helper) {
                        helper.textContent = filename;
                        helper.title = filename;
                    }
                });
                ['dragenter', 'dragover'].forEach((name) => drop.addEventListener(name, (event) => { event.preventDefault(); drop.classList.add('is-dragging'); }));
                ['dragleave', 'drop'].forEach((name) => drop.addEventListener(name, (event) => { event.preventDefault(); drop.classList.remove('is-dragging'); }));
                drop.addEventListener('drop', (event) => {
                    if (!input || !event.dataTransfer?.files?.length) return;
                    input.files = event.dataTransfer.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });

            const qrCanvas = document.querySelector('[data-storefront-qr]');
            if (qrCanvas && window.QRCode) {
                window.QRCode.toCanvas(qrCanvas, storefrontUrl, { width: 144, margin: 1, color: { dark: '#171714', light: '#ffffff' } });
                document.querySelectorAll('[data-qr-download]').forEach((button) => {
                    button.addEventListener('click', async function () {
                        const type = button.dataset.qrDownload;
                        const link = document.createElement('a');
                        link.download = '{{ $profile->public_slug }}-store-qr.' + type;
                        if (type === 'svg') {
                            const svg = await window.QRCode.toString(storefrontUrl, { type: 'svg', margin: 1 });
                            link.href = URL.createObjectURL(new Blob([svg], { type: 'image/svg+xml' }));
                        } else {
                            link.href = qrCanvas.toDataURL('image/png');
                        }
                        link.click();
                        if (type === 'svg') URL.revokeObjectURL(link.href);
                    });
                });
            }
            document.querySelector('[data-copy-store-link]')?.addEventListener('click', async function () {
                try { await navigator.clipboard.writeText(storefrontUrl); this.textContent = 'Copied'; }
                catch (_) { this.textContent = 'Copy failed'; }
                window.setTimeout(() => { this.textContent = 'Copy link'; }, 1800);
            });

            form.addEventListener('input', function (event) { setDirty(); syncPreview(event); });
            form.addEventListener('change', function (event) { setDirty(); syncPreview(event); });
            form.addEventListener('submit', function () { dirty = false; });
            window.addEventListener('beforeunload', function (event) {
                if (!dirty) return;
                event.preventDefault();
                event.returnValue = '';
            });
            syncFeaturedProducts();
            syncPreview();
        });
    </script>
@endsection
