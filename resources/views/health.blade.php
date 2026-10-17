<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>System status · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <style>
        :root { color-scheme: light; --ink:#032b3a; --muted:#60747b; --line:#d7e2e5; --canvas:#f3f7f7; --surface:#fff; --brand:#073b4c; --ok:#16794b; --ok-soft:#e9f7ef; --bad:#b42318; --bad-soft:#fff0ee; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; background:var(--canvas); color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        .shell { width:min(680px,calc(100% - 32px)); margin:0 auto; padding:56px 0; }
        .brand { display:flex; align-items:center; gap:10px; margin-bottom:40px; color:var(--brand); font-weight:800; letter-spacing:-.02em; }
        .mark { display:grid; width:34px; height:34px; place-items:center; border-radius:7px; background:var(--brand); color:#fff; font-size:14px; }
        .summary { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; margin-bottom:28px; }
        h1 { margin:0 0 8px; font-size:clamp(28px,6vw,40px); letter-spacing:-.045em; line-height:1.05; }
        .lede { margin:0; color:var(--muted); line-height:1.55; }
        .badge { display:inline-flex; flex:none; align-items:center; gap:8px; border:1px solid {{ $status === 'operational' ? '#b9e4cc' : '#f2c3bd' }}; border-radius:999px; background:{{ $status === 'operational' ? 'var(--ok-soft)' : 'var(--bad-soft)' }}; padding:8px 12px; color:{{ $status === 'operational' ? 'var(--ok)' : 'var(--bad)' }}; font-size:13px; font-weight:750; }
        .dot { width:8px; height:8px; border-radius:50%; background:currentColor; box-shadow:0 0 0 4px color-mix(in srgb,currentColor 12%,transparent); }
        .panel { overflow:hidden; border:1px solid var(--line); border-radius:8px; background:var(--surface); }
        .check { display:grid; grid-template-columns:1fr auto; align-items:center; gap:16px; padding:20px 22px; }
        .check + .check { border-top:1px solid var(--line); }
        .check-title { display:flex; align-items:center; gap:11px; margin-bottom:4px; font-weight:750; }
        .check p { margin:0; color:var(--muted); font-size:14px; line-height:1.45; }
        .state { color:var(--ok); font-size:13px; font-weight:750; text-align:right; }
        .state.bad { color:var(--bad); }
        .timing { display:block; margin-top:3px; color:var(--muted); font-size:11px; font-weight:500; }
        footer { display:flex; justify-content:space-between; gap:20px; margin-top:18px; color:var(--muted); font-size:12px; }
        footer span:last-child { text-align:right; }
        @media (max-width:560px) { .shell{padding:32px 0}.summary{display:block}.badge{margin-top:18px}.check{padding:18px}.check p{max-width:220px}footer{display:block}footer span{display:block;margin-top:6px}footer span:last-child{text-align:left} }
    </style>
</head>
<body>
    <main class="shell">
        <div class="brand"><span class="mark">8K</span><span>{{ config('app.name') }}</span></div>

        <section class="summary" aria-labelledby="status-heading">
            <div>
                <h1 id="status-heading">System status</h1>
                <p class="lede">Live availability of the services that keep 8Kommerce running.</p>
            </div>
            <div class="badge" role="status">
                <span class="dot" aria-hidden="true"></span>
                {{ $status === 'operational' ? 'All systems operational' : 'Service disruption' }}
            </div>
        </section>

        <section class="panel" aria-label="Service checks">
            @foreach ($checks as $check)
                <article class="check">
                    <div>
                        <div class="check-title">{{ $check['name'] }}</div>
                        <p>{{ $check['description'] }}</p>
                    </div>
                    <div class="state {{ $check['status'] === 'operational' ? '' : 'bad' }}">
                        {{ ucfirst($check['status']) }}
                        <span class="timing">{{ number_format($check['response_time_ms'], 1) }} ms</span>
                    </div>
                </article>
            @endforeach
        </section>

        <footer>
            <span>Checked {{ \Illuminate\Support\Carbon::parse($checked_at)->diffForHumans() }}</span>
            <span>Version {{ $version }} · {{ number_format($response_time_ms, 1) }} ms total</span>
        </footer>
    </main>
</body>
</html>
