<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Current availability, incidents and service help for 8Kommerce.">
    <meta name="robots" content="noindex, follow">
    <meta name="theme-color" content="#073b4c">
    <title>Service status · {{ config('app.name', '8Kommerce') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <style>
        :root {
            color-scheme: light;
            --ink:#092f3d; --heading:#062633; --muted:#61767d; --quiet:#829198;
            --line:#d9e3e5; --line-strong:#c5d3d6; --canvas:#f3f7f7; --surface:#fff;
            --brand:#073b4c; --brand-soft:#e7f0f1; --ok:#16794b; --ok-soft:#eaf7ef;
            --warn:#9a6700; --warn-soft:#fff7db; --bad:#b42318; --bad-soft:#fff0ee;
        }
        * { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; min-height:100vh; overflow-x:hidden; background:var(--canvas); color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        a { color:inherit; }
        button,summary { font:inherit; }
        .shell { width:min(920px,calc(100% - 32px)); margin:0 auto; padding:34px 0 64px; }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:44px; }
        .brand { display:flex; align-items:center; gap:11px; color:var(--brand); font-weight:800; letter-spacing:-.02em; text-decoration:none; }
        .mark { display:grid; width:36px; height:36px; place-items:center; border-radius:7px; background:var(--brand); color:#fff; font-size:14px; }
        .toplink { color:var(--muted); font-size:13px; font-weight:700; text-decoration:none; }
        .toplink:hover { color:var(--brand); }
        .summary { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:start; gap:28px; margin-bottom:26px; }
        .eyebrow { margin:0 0 9px; color:var(--ok); font-size:12px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
        h1 { margin:0 0 10px; color:var(--heading); font-size:clamp(32px,6vw,48px); letter-spacing:-.05em; line-height:1.02; }
        h2 { margin:0; color:var(--heading); font-size:clamp(22px,4vw,28px); letter-spacing:-.035em; line-height:1.15; }
        h3 { margin:0; color:var(--heading); font-size:16px; letter-spacing:-.015em; }
        .lede { max-width:620px; margin:0; color:var(--muted); line-height:1.65; }
        .badge { display:inline-flex; flex:none; align-items:center; gap:9px; border:1px solid {{ $status === 'operational' ? '#b9e4cc' : '#efc679' }}; border-radius:7px; background:{{ $status === 'operational' ? 'var(--ok-soft)' : 'var(--warn-soft)' }}; padding:10px 13px; color:{{ $status === 'operational' ? 'var(--ok)' : 'var(--warn)' }}; font-size:13px; font-weight:800; }
        .dot { width:8px; height:8px; border-radius:50%; background:currentColor; box-shadow:0 0 0 4px color-mix(in srgb,currentColor 13%,transparent); }
        .incident { margin:0 0 22px; border:1px solid #efc679; border-radius:8px; background:var(--warn-soft); padding:19px 20px; }
        .incident-kicker { margin:0 0 5px; color:var(--warn); font-size:11px; font-weight:850; letter-spacing:.1em; text-transform:uppercase; }
        .incident p { margin:7px 0 0; color:#785b1d; line-height:1.55; }
        .incident time { display:block; margin-top:10px; color:#8b713a; font-size:12px; }
        .panel { overflow:hidden; border:1px solid var(--line); border-radius:8px; background:var(--surface); }
        .service { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:center; gap:18px; min-height:84px; padding:18px 21px; }
        .service + .service { border-top:1px solid var(--line); }
        .service p { margin:5px 0 0; color:var(--muted); font-size:13px; line-height:1.45; }
        .state { display:inline-flex; align-items:center; gap:7px; color:var(--ok); font-size:12px; font-weight:800; white-space:nowrap; }
        .state::before { width:7px; height:7px; border-radius:50%; background:currentColor; content:""; }
        .state.bad { color:var(--bad); }
        .meta { display:flex; justify-content:space-between; gap:20px; margin:14px 2px 0; color:var(--quiet); font-size:12px; }
        .section { padding-top:58px; }
        .section-heading { display:flex; align-items:end; justify-content:space-between; gap:20px; margin-bottom:19px; }
        .section-copy { max-width:560px; margin:7px 0 0; color:var(--muted); font-size:14px; line-height:1.55; }
        .help-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .help-card { border:1px solid var(--line); border-radius:8px; background:var(--surface); padding:20px; }
        .help-index { display:grid; width:30px; height:30px; place-items:center; margin-bottom:24px; border-radius:6px; background:var(--brand-soft); color:var(--brand); font-size:12px; font-weight:850; }
        .help-card p { margin:8px 0 0; color:var(--muted); font-size:14px; line-height:1.55; }
        .faq { border-top:1px solid var(--line); }
        details { border-bottom:1px solid var(--line); background:transparent; }
        summary { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:19px 2px; color:var(--heading); font-weight:750; cursor:pointer; list-style:none; }
        summary::-webkit-details-marker { display:none; }
        summary::after { display:grid; width:26px; height:26px; flex:none; place-items:center; border:1px solid var(--line-strong); border-radius:6px; color:var(--brand); content:"+"; font-size:18px; font-weight:500; }
        details[open] summary::after { content:"−"; }
        .answer { max-width:730px; padding:0 40px 20px 2px; color:var(--muted); font-size:14px; line-height:1.65; }
        .support { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:center; gap:24px; margin-top:58px; border:1px solid #bcd0d4; border-radius:8px; background:var(--brand); padding:25px; color:#fff; }
        .support h2 { color:#fff; }
        .support p { margin:8px 0 0; color:#c8d9dd; font-size:14px; line-height:1.55; }
        .actions { display:flex; flex-wrap:wrap; gap:9px; }
        .button { display:inline-flex; min-height:42px; align-items:center; justify-content:center; border:1px solid transparent; border-radius:6px; padding:9px 14px; font-size:13px; font-weight:800; text-decoration:none; }
        .button-primary { background:#fff; color:var(--brand); }
        .button-secondary { border-color:#52737c; color:#fff; }
        .technical { margin-top:54px; }
        .technical details { border:1px solid var(--line); border-radius:8px; background:var(--surface); }
        .technical details summary { padding:17px 19px; }
        .technical details summary::after { margin-left:auto; }
        .checks { border-top:1px solid var(--line); }
        .check { display:grid; grid-template-columns:1fr auto; gap:16px; padding:14px 19px; font-size:13px; }
        .check + .check { border-top:1px solid var(--line); }
        .timing { color:var(--quiet); font-variant-numeric:tabular-nums; }
        footer { display:flex; justify-content:space-between; gap:20px; margin-top:28px; color:var(--quiet); font-size:12px; }
        @media (max-width:680px) {
            .shell { padding-top:24px; }
            .topbar { margin-bottom:32px; }
            .summary { grid-template-columns:1fr; gap:17px; }
            .badge { justify-self:start; }
            .help-grid { grid-template-columns:1fr; }
            .section { padding-top:46px; }
            .support { grid-template-columns:1fr; }
            .actions { width:100%; }
            .button { flex:1; }
        }
        @media (max-width:460px) {
            .shell { width:min(100% - 24px,920px); }
            .service { grid-template-columns:1fr; align-items:start; gap:10px; padding:17px; }
            .state { justify-self:start; }
            .meta,footer { display:block; }
            .meta span,footer span { display:block; margin-top:6px; }
        }
    </style>
</head>
<body>
    <main class="shell">
        <nav class="topbar" aria-label="Status navigation">
            <a class="brand" href="{{ url('/') }}">
                <span class="mark" aria-hidden="true">8K</span>
                <span>{{ config('app.name', '8Kommerce') }}</span>
            </a>
            <a class="toplink" href="{{ route('customer.orders.login') }}">Track an order →</a>
        </nav>

        <section class="summary" aria-labelledby="status-heading">
            <div>
                <p class="eyebrow">Live service status</p>
                <h1 id="status-heading">{{ $status === 'operational' ? 'Everything is running normally.' : 'Some services need attention.' }}</h1>
                <p class="lede">Current availability of the services customers and sellers use across 8Kommerce.</p>
            </div>
            <div class="badge" role="status">
                <span class="dot" aria-hidden="true"></span>
                {{ $status === 'operational' ? 'All systems operational' : 'Service disruption' }}
            </div>
        </section>

        @if ($incident['active'])
            <section class="incident" aria-labelledby="incident-title">
                <p class="incident-kicker">{{ $incident['severity'] === 'maintenance' ? 'Scheduled maintenance' : 'Active incident' }}</p>
                <h3 id="incident-title">{{ $incident['title'] ?: 'We are investigating a service issue.' }}</h3>
                @if ($incident['message'])
                    <p>{{ $incident['message'] }}</p>
                @endif
                @if ($incident['updated_at'])
                    <time datetime="{{ $incident['updated_at'] }}">Updated {{ $incident['updated_at'] }}</time>
                @endif
            </section>
        @endif

        <section class="panel" aria-label="8Kommerce services">
            @foreach ($services as $service)
                <article class="service">
                    <div>
                        <h3>{{ $service['name'] }}</h3>
                        <p>{{ $service['description'] }}</p>
                    </div>
                    <div class="state {{ $service['status'] === 'operational' ? '' : 'bad' }}">
                        {{ $service['status'] === 'operational' ? 'Operational' : 'Degraded' }}
                    </div>
                </article>
            @endforeach
        </section>

        <div class="meta">
            <span>Checked {{ \Illuminate\Support\Carbon::parse($checked_at)->diffForHumans() }}</span>
            <span>Automatic checks run whenever this page is requested.</span>
        </div>

        <section class="section" aria-labelledby="service-help-heading">
            <div class="section-heading">
                <div>
                    <h2 id="service-help-heading">What each service covers</h2>
                    <p class="section-copy">A quick guide to what may be affected when a component reports a problem.</p>
                </div>
            </div>

            <div class="help-grid">
                <article class="help-card">
                    <span class="help-index" aria-hidden="true">01</span>
                    <h3>Orders and checkout</h3>
                    <p>Includes carts, delivery or pickup choices, order creation and checkout. If confirmation is delayed, avoid submitting the same payment twice.</p>
                </article>
                <article class="help-card">
                    <span class="help-index" aria-hidden="true">02</span>
                    <h3>Payment processing</h3>
                    <p>Includes payment verification and settlement workflows. A successful payment may occasionally take a moment to appear on an order.</p>
                </article>
                <article class="help-card">
                    <span class="help-index" aria-hidden="true">03</span>
                    <h3>SMS notifications</h3>
                    <p>Includes login codes and order updates. A delayed SMS does not automatically mean your account, order or payment has failed.</p>
                </article>
                <article class="help-card">
                    <span class="help-index" aria-hidden="true">04</span>
                    <h3>Stores and seller tools</h3>
                    <p>Includes seller storefronts, products, stock, customer records, reporting and the business dashboard.</p>
                </article>
            </div>
        </section>

        <section class="section" aria-labelledby="faq-heading">
            <div class="section-heading">
                <div>
                    <h2 id="faq-heading">Service questions</h2>
                    <p class="section-copy">What to do when something is delayed or temporarily unavailable.</p>
                </div>
            </div>

            <div class="faq">
                <details>
                    <summary>My payment succeeded but my order still says pending. What should I do?</summary>
                    <div class="answer">Wait a few minutes and check the order tracker again. Payment confirmation can arrive shortly after the payment itself. Do not make a second payment unless the first attempt is confirmed as failed.</div>
                </details>
                <details>
                    <summary>Why have I not received my OTP or order SMS?</summary>
                    <div class="answer">Network delivery can be delayed. Confirm that the phone number is correct, wait for the resend timer, then request one new code. Only the newest code should be used.</div>
                </details>
                <details>
                    <summary>Are my existing orders safe during an outage?</summary>
                    <div class="answer">A temporary page or notification outage does not delete an order. Once service returns, use the order tracker to confirm the latest recorded stage before taking another action.</div>
                </details>
                <details>
                    <summary>What does degraded performance mean?</summary>
                    <div class="answer">The service is available, but some actions may be slower or intermittently unavailable. The status will return to operational after checks confirm normal service.</div>
                </details>
                <details>
                    <summary>Why can one seller's storefront be unavailable?</summary>
                    <div class="answer">The seller may have paused the store or a specific product, or the wider storefront service may be experiencing a problem. Check this page to distinguish a platform incident from store availability.</div>
                </details>
                <details>
                    <summary>What happens during scheduled maintenance?</summary>
                    <div class="answer">We may temporarily limit selected actions while applying an update. Any active notice above will explain what is affected and when the next update is expected.</div>
                </details>
            </div>
        </section>

        <section class="support" aria-labelledby="support-heading">
            <div>
                <h2 id="support-heading">Still experiencing a problem?</h2>
                <p>Check your order first, or return to 8Kommerce and try the action again after a few minutes.</p>
            </div>
            <div class="actions">
                <a class="button button-primary" href="{{ route('customer.orders.login') }}">Track an order</a>
                <a class="button button-secondary" href="{{ url('/') }}">Return home</a>
            </div>
        </section>

        <section class="technical" aria-label="Technical checks">
            <details>
                <summary>Technical check details</summary>
                <div class="checks">
                    @foreach ($checks as $check)
                        <div class="check">
                            <span>{{ $check['name'] }} · {{ ucfirst($check['status']) }}</span>
                            <span class="timing">{{ number_format($check['response_time_ms'], 1) }} ms</span>
                        </div>
                    @endforeach
                </div>
            </details>
        </section>

        <footer>
            <span>Version {{ $version }} · {{ number_format($response_time_ms, 1) }} ms total</span>
            <span>© {{ date('Y') }} 8Kommerce</span>
        </footer>
    </main>
</body>
</html>
