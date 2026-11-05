<div id="orders-by-customer" class="mt-4 space-y-4">
    @forelse(($ordersByCustomer ?? collect()) as $group)
        <article class="lp-card overflow-hidden">
            <header class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">{{ mb_strtoupper(mb_substr($group['customer_name'], 0, 1)) }}</span>
                    <div class="min-w-0"><h3 class="truncate text-sm font-semibold text-slate-950">{{ $group['customer_name'] }}</h3><p class="truncate text-xs text-slate-500">{{ $group['customer_phone'] ?: 'No phone number' }} · {{ $group['orders_count'] }} {{ str('order')->plural($group['orders_count']) }}</p></div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge tone="neutral">{{ \App\Support\Money::format($group['group_total'], $currency) }} total</x-ui.badge>
                    @if($group['whatsapp_url'])<x-ui.button variant="secondary" size="sm" icon="whatsapp" :href="$group['whatsapp_url']" target="_blank" rel="noreferrer noopener">WhatsApp</x-ui.button>@endif
                    @if($group['call_url'])<x-ui.button variant="secondary" size="sm" icon="phone" :href="$group['call_url']">Call</x-ui.button>@endif
                </div>
            </header>

            <div class="divide-y divide-slate-100">
                @foreach($group['orders'] as $order)
                    @php
                        $statusTone = match($order->status) {
                            \App\Models\Order::STATUS_PAID => 'warning',
                            \App\Models\Order::STATUS_ACCEPTED,
                            \App\Models\Order::STATUS_PROCESSING,
                            \App\Models\Order::STATUS_READY,
                            \App\Models\Order::STATUS_SHIPPED,
                            \App\Models\Order::STATUS_DELIVERY_RESCHEDULED => 'info',
                            \App\Models\Order::STATUS_DELIVERED,
                            \App\Models\Order::STATUS_COMPLETED => 'success',
                            \App\Models\Order::STATUS_DELIVERY_ATTEMPTED => 'warning',
                            \App\Models\Order::STATUS_CANNOT_FULFILL,
                            \App\Models\Order::STATUS_RETURNED,
                            \App\Models\Order::STATUS_DISPUTED_PENDING_ADMIN,
                            \App\Models\Order::STATUS_REFUNDED => 'danger',
                            default => 'neutral',
                        };
                    @endphp
                    <details id="order-{{ $order->id }}" class="group/order scroll-mt-24">
                        <summary class="cursor-pointer list-none px-5 py-4 transition hover:bg-slate-50">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    @if($order->status === \App\Models\Order::STATUS_PAID)
                                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" form="bulk-accept-form" class="h-4 w-4 shrink-0 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" data-order-bulk-checkbox onclick="event.stopPropagation()" aria-label="Select order {{ $order->reference }}">
                                    @endif
                                    <span class="lp-icon-box h-9 w-9"><x-ui.icon name="package" size="sm" /></span>
                                    <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-900">Order {{ $order->reference }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $order->created_at->format('M j, Y · H:i') }} · {{ $order->delivery_required ? 'Delivery' : 'Pickup' }}</p></div>
                                </div>
                                <div class="flex items-center justify-between gap-3 sm:justify-end"><x-ui.badge :tone="$statusTone">{{ str($order->status)->replace('_', ' ')->title() }}</x-ui.badge><span class="text-sm font-semibold tabular-nums text-slate-950">{{ \App\Support\Money::format((string) $order->total, $currency) }}</span><x-ui.icon name="chevron-down" size="sm" class="text-slate-400 transition group-open/order:rotate-180" /></div>
                            </div>
                        </summary>

                        <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-5">
                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($order->items as $item)
                                    <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-3">
                                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-md bg-slate-100">@if($item->product?->image_path)<img src="{{ asset('storage/'.$item->product->image_path) }}" alt="" class="h-full w-full object-cover">@else<span class="flex h-full w-full items-center justify-center text-slate-400"><x-ui.icon name="package" size="sm" /></span>@endif</div>
                                        <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ $item->product_name }}</p><p class="mt-0.5 text-xs text-slate-500">Qty {{ $item->quantity }} · {{ \App\Support\Money::format((string) $item->line_total, $currency) }}</p></div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 grid gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-xs leading-5 text-slate-600 sm:grid-cols-2">
                                <div><strong class="text-slate-800">{{ $order->delivery_required ? 'Delivery' : 'Pickup' }}</strong><p>{{ $order->fulfillment_label ?: ($order->customer_location ?: 'Fulfilment details unavailable') }}</p>@if($order->fulfillment_details)<p class="text-slate-500">{{ $order->fulfillment_details }}</p>@endif</div>
                                <div><strong class="text-slate-800">Fee</strong><p>{{ (float) $order->fulfillment_fee > 0 ? \App\Support\Money::format((string) $order->fulfillment_fee, $currency) : 'Free' }}</p>@if($order->delivery_note)<p class="text-slate-500">Customer note: {{ $order->delivery_note }}</p>@endif</div>
                            </div>

                            @if($order->delivery_failure_reason)
                                <x-ui.alert tone="warning" title="Delivery exception" class="mt-4">{{ $order->delivery_failure_reason }}@if($order->rescheduled_for)<br>Rescheduled for {{ $order->rescheduled_for->format('M j, Y · g:i A') }}.@endif</x-ui.alert>
                            @endif

                            @if(in_array($order->status, [\App\Models\Order::STATUS_PAID, \App\Models\Order::STATUS_ACCEPTED, \App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_READY, \App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_DELIVERY_ATTEMPTED, \App\Models\Order::STATUS_DELIVERY_RESCHEDULED], true))
                                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                                    <span class="mr-auto text-xs font-semibold text-slate-500">Choose the next fulfilment step</span>
                                    @if($order->status === \App\Models\Order::STATUS_PAID)
                                        <form method="POST" action="{{ route('notifications.orders.reject', $order) }}" onsubmit="return confirm('Cancel this paid order and request a full refund?')">@csrf<x-ui.button type="submit" variant="danger" size="sm">Cancel & refund</x-ui.button></form>
                                        <form method="POST" action="{{ route('notifications.orders.accept', $order) }}">@csrf<x-ui.button type="submit" variant="success" icon="check" size="sm">Accept order</x-ui.button></form>
                                    @elseif($order->status === \App\Models\Order::STATUS_ACCEPTED)
                                        <form method="POST" action="{{ route('notifications.orders.processing', $order) }}">@csrf<x-ui.button type="submit" size="sm">Start processing</x-ui.button></form>
                                    @elseif($order->status === \App\Models\Order::STATUS_PROCESSING)
                                        <form method="POST" action="{{ route('notifications.orders.ready', $order) }}">@csrf<x-ui.button type="submit" size="sm">Mark ready</x-ui.button></form>
                                        @if($order->delivery_required)<form method="POST" action="{{ route('notifications.orders.shipped', $order) }}">@csrf<x-ui.button type="submit" variant="secondary" size="sm" icon="truck">Start delivery</x-ui.button></form>@endif
                                    @elseif($order->status === \App\Models\Order::STATUS_READY)
                                        @if($order->delivery_required)<form method="POST" action="{{ route('notifications.orders.shipped', $order) }}">@csrf<x-ui.button type="submit" size="sm" icon="truck">Start delivery</x-ui.button></form>@endif
                                        <form method="POST" action="{{ route('notifications.orders.delivered', $order) }}">@csrf<x-ui.button type="submit" variant="secondary" size="sm">Mark collected/delivered</x-ui.button></form>
                                    @elseif($order->status === \App\Models\Order::STATUS_DELIVERY_RESCHEDULED)
                                        <form method="POST" action="{{ route('notifications.orders.shipped', $order) }}">@csrf<x-ui.button type="submit" size="sm" icon="truck">Dispatch again</x-ui.button></form>
                                        <form method="POST" action="{{ route('notifications.orders.delivered', $order) }}">@csrf<x-ui.button type="submit" variant="success" icon="check" size="sm">Mark delivered</x-ui.button></form>
                                    @else
                                        <form method="POST" action="{{ route('notifications.orders.delivered', $order) }}">@csrf<x-ui.button type="submit" variant="success" icon="check" size="sm">Mark delivered</x-ui.button></form>
                                    @endif
                                </div>
                            @endif

                            @if(in_array($order->status, [\App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_DELIVERY_ATTEMPTED, \App\Models\Order::STATUS_DELIVERY_RESCHEDULED], true))
                                <details class="mt-4 rounded-lg border border-amber-200 bg-amber-50/60 p-4">
                                    <summary class="cursor-pointer text-sm font-semibold text-amber-900">Delivery problem or return</summary>
                                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                        <form method="POST" action="{{ route('notifications.orders.delivery-attempt', $order) }}" class="space-y-3 rounded-lg border border-amber-200 bg-white p-4">
                                            @csrf
                                            <div><label class="text-xs font-semibold text-slate-700">What happened?</label><select name="failure_reason" required class="mt-1 w-full rounded-lg border-slate-200 text-sm"><option value="customer_unavailable">Customer unavailable</option><option value="invalid_address">Address could not be located</option><option value="customer_requested">Customer requested another time</option><option value="seller_delay">Seller or courier delay</option><option value="other">Other</option></select></div>
                                            <div><label class="text-xs font-semibold text-slate-700">New delivery time <span class="font-normal text-slate-400">(optional)</span></label><input type="datetime-local" name="rescheduled_for" min="{{ now()->addMinutes(15)->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded-lg border-slate-200 text-sm"></div>
                                            <div><label class="text-xs font-semibold text-slate-700">Details <span class="font-normal text-slate-400">(optional)</span></label><textarea name="failure_details" rows="2" maxlength="500" class="mt-1 w-full rounded-lg border-slate-200 text-sm"></textarea></div>
                                            <x-ui.button type="submit" variant="warning" size="sm">Save delivery update</x-ui.button>
                                        </form>
                                        <form method="POST" action="{{ route('notifications.orders.returned', $order) }}" class="space-y-3 rounded-lg border border-rose-200 bg-white p-4" onsubmit="return confirm('Return this order and create a refund request?')">
                                            @csrf
                                            <div><label class="text-xs font-semibold text-slate-700">Return reason</label><select name="return_reason" required class="mt-1 w-full rounded-lg border-slate-200 text-sm"><option value="customer_unavailable">Customer unavailable</option><option value="invalid_address">Invalid address</option><option value="customer_refused">Customer refused delivery</option><option value="seller_fault">Seller or courier fault</option><option value="damaged">Item damaged in delivery</option></select></div>
                                            <div><label class="text-xs font-semibold text-slate-700">Return details <span class="font-normal text-slate-400">(optional)</span></label><textarea name="return_details" rows="2" maxlength="500" class="mt-1 w-full rounded-lg border-slate-200 text-sm"></textarea></div>
                                            <p class="text-xs leading-5 text-slate-500">Seller or damage faults include the delivery fee. Customer availability, refusal, or address issues refund the products only.</p>
                                            <x-ui.button type="submit" variant="danger" size="sm">Return and request refund</x-ui.button>
                                        </form>
                                    </div>
                                </details>
                            @endif

                            @if($order->refund_status)
                                <x-ui.alert tone="warning" title="Refund {{ str($order->refund_status)->replace('_', ' ')->lower() }}" class="mt-4">Requested amount: {{ $order->refund_requested_amount ? \App\Support\Money::format((string) $order->refund_requested_amount, $currency) : 'Pending calculation' }}. This remains subject to the refund review workflow.</x-ui.alert>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        </article>
    @empty
        <x-ui.empty-state title="No orders in this view" description="Try another filter, or share your storefront to start receiving orders." icon="shopping-bag"><x-slot:action><x-ui.button variant="secondary" :href="route('products.orders')">Show all orders</x-ui.button></x-slot:action></x-ui.empty-state>
    @endforelse
</div>
