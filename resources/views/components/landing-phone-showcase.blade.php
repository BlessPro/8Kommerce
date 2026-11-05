@props(['products' => collect(), 'currency' => 'GHS'])

@php
    $previewProducts = collect($products)->take(2)->values();
@endphp

<div class="landing-device-showcase" aria-label="8Kommerce mobile storefront, seller workspace, and order tracking previews">
    <span class="landing-ai-event landing-ai-event--order" data-ai-float><i>+</i><span><strong>New order</strong><small>GHS 185.00</small></span></span>
    <span class="landing-ai-event landing-ai-event--payment" data-ai-float><i>✓</i><span><strong>Payment confirmed</strong><small>Just now</small></span></span>
    <span class="landing-ai-event landing-ai-event--stock" data-ai-float><i>!</i><span><strong>Low stock</strong><small>3 units remaining</small></span></span>
    <div class="landing-device landing-device--orders" aria-hidden="true">
        <div class="landing-device__speaker"></div>
        <div class="landing-device__screen">
            <div class="landing-mobile-status"><span>9:41</span><span>● ◒</span></div>
            <div class="landing-orders-head">
                <span class="landing-mini-logo">8K</span>
                <span class="landing-avatar">BA</span>
            </div>
            <p class="landing-mobile-kicker">Today</p>
            <h3>Orders to prepare</h3>
            <div class="landing-metric-row">
                <div><strong>12</strong><span>New orders</span></div>
                <div><strong>GHS 1.8k</strong><span>Sales</span></div>
            </div>
            <div class="landing-order-list">
                @foreach([['#1048', 'Ama Mensah', 'Ready'], ['#1047', 'Kojo Frimpong', 'Packing'], ['#1046', 'Naa Dedei', 'Paid']] as $order)
                    <div class="landing-order-item">
                        <span class="landing-order-icon">{{ mb_substr($order[1], 0, 1) }}</span>
                        <span><strong>{{ $order[1] }}</strong><small>{{ $order[0] }} · 2 items</small></span>
                        <em>{{ $order[2] }}</em>
                    </div>
                @endforeach
            </div>
            <div class="landing-mobile-nav"><span class="is-current">⌂<small>Home</small></span><span>▤<small>Orders</small></span><span>□<small>Stock</small></span><span>⋯<small>More</small></span></div>
        </div>
    </div>

    <div class="landing-device landing-device--store">
        <div class="landing-device__speaker"></div>
        <div class="landing-device__screen">
            <div class="landing-mobile-status"><span>9:41</span><span>● ◒</span></div>
            <div class="landing-store-offer">Free delivery in Accra over GHS 250</div>
            <div class="landing-store-head"><strong>ATELIER 8</strong><span>⌕　<span class="landing-bag">▢<b>2</b></span></span></div>
            <div class="landing-store-hero">
                <span>NEW COLLECTION</span>
                <strong>Made for<br>your everyday.</strong>
                <button type="button" tabindex="-1">Shop the edit</button>
            </div>
            <div class="landing-store-tabs"><strong>New in</strong><span>Clothing</span><span>Accessories</span></div>
            <div class="landing-product-grid">
                @forelse($previewProducts as $index => $product)
                    <div class="landing-product-card">
                        <div class="landing-product-image landing-product-image--{{ $index + 1 }}">
                            @if($product['image_path'] ?? null)
                                <img src="{{ asset('storage/'.$product['image_path']) }}" alt="">
                            @else
                                <span class="landing-product-shape"></span>
                            @endif
                            <span class="landing-heart">♡</span>
                        </div>
                        <strong>{{ $product['name'] ?? ($index === 0 ? 'Everyday tote' : 'Linen set') }}</strong>
                        <span>{{ \App\Support\Money::format((string) ($product['price'] ?? ($index === 0 ? '185' : '240')), $currency) }}</span>
                    </div>
                @empty
                    @foreach([['Everyday tote', 'GHS 185'], ['Linen set', 'GHS 240']] as $index => $product)
                        <div class="landing-product-card"><div class="landing-product-image landing-product-image--{{ $index + 1 }}"><span class="landing-product-shape"></span><span class="landing-heart">♡</span></div><strong>{{ $product[0] }}</strong><span>{{ $product[1] }}</span></div>
                    @endforeach
                @endforelse
            </div>
            <div class="landing-store-bottom"><span class="is-current">⌂<small>Home</small></span><span>▦<small>Browse</small></span><span>⌕<small>Search</small></span><span>▢<small>Cart</small></span></div>
        </div>
    </div>

    <div class="landing-device landing-device--tracking" aria-hidden="true">
        <div class="landing-device__speaker"></div>
        <div class="landing-device__screen">
            <div class="landing-mobile-status"><span>9:41</span><span>● ◒</span></div>
            <div class="landing-track-head"><span>←</span><strong>Track order</strong><span>⋯</span></div>
            <div class="landing-track-check">✓</div>
            <p class="landing-mobile-kicker">ORDER #1048</p>
            <h3>Your order is ready</h3>
            <p class="landing-track-copy">The seller has prepared your items for delivery.</p>
            <div class="landing-track-card">
                @foreach([['Order confirmed', 'Paid successfully', true], ['Preparing order', 'Completed today', true], ['Out for delivery', 'Next step', false], ['Delivered', 'Confirm when received', false]] as $step)
                    <div class="landing-track-step {{ $step[2] ? 'is-complete' : '' }}"><i>{{ $step[2] ? '✓' : '' }}</i><span><strong>{{ $step[0] }}</strong><small>{{ $step[1] }}</small></span></div>
                @endforeach
            </div>
            <div class="landing-track-seller"><span>BA</span><span><small>Sold by</small><strong>Bloom & Ash</strong></span><b>Message</b></div>
            <button class="landing-track-button" type="button" tabindex="-1">View order details</button>
        </div>
    </div>

    <span class="landing-showcase-note landing-showcase-note--store">Customer storefront</span>
    <span class="landing-showcase-note landing-showcase-note--orders">Seller workspace</span>
    <span class="landing-showcase-note landing-showcase-note--track">Order tracking</span>
</div>
