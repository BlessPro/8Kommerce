<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#073B4C">
    <title>@yield('code') · @yield('title') · {{ config('app.name', '8Kommerce') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#f4f7f7;color:#032b3a;font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.shell{display:grid;min-height:100vh;grid-template-rows:auto 1fr auto;width:min(1120px,calc(100% - 32px));margin:auto;padding:24px 0}.brand{display:inline-flex;width:max-content;align-items:center;gap:10px;color:#073b4c;font-weight:800;text-decoration:none}.mark{display:grid;width:36px;height:36px;place-items:center;border-radius:7px;background:#073b4c;color:#fff;font-size:13px}.content{display:grid;align-items:center;gap:54px;padding:56px 0}.code{margin:0;color:#7a1f3d;font-size:13px;font-weight:800;letter-spacing:.16em;text-transform:uppercase}.title{max-width:680px;margin:14px 0 0;font-size:clamp(38px,8vw,70px);line-height:1;letter-spacing:-.055em}.message{max-width:590px;margin:22px 0 0;color:#536b74;font-size:17px;line-height:1.7}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:30px}.button{display:inline-flex;min-height:44px;align-items:center;justify-content:center;border:1px solid #073b4c;border-radius:6px;padding:10px 18px;color:#073b4c;font-size:14px;font-weight:750;text-decoration:none}.button.primary{background:#073b4c;color:#fff}.visual{position:relative;display:grid;min-height:240px;place-items:center;overflow:hidden;border:1px solid #d5e1e4;border-radius:8px;background:#fff}.visual:before,.visual:after{position:absolute;content:"";border-radius:50%;background:#eaf1f3}.visual:before{width:230px;height:230px;top:-110px;right:-45px}.visual:after{width:150px;height:150px;bottom:-75px;left:-25px}.window{position:relative;z-index:1;width:min(330px,75%);border:1px solid #b7cbd0;border-radius:7px;background:#f9fbfb;padding:14px}.window-top{display:flex;gap:5px;border-bottom:1px solid #d5e1e4;padding-bottom:11px}.window-top i{width:7px;height:7px;border-radius:50%;background:#b7cbd0}.line{display:block;height:10px;margin-top:14px;border-radius:3px;background:#dce7e9}.line.short{width:58%}.footer{display:flex;justify-content:space-between;gap:20px;border-top:1px solid #d5e1e4;padding-top:18px;color:#72878e;font-size:12px}.footer a{color:#536b74;text-decoration:none}@media(min-width:800px){.content{grid-template-columns:minmax(0,1.15fr) minmax(310px,.85fr)}}@media(max-width:540px){.shell{padding:18px 0}.content{padding:42px 0}.visual{min-height:190px}.footer{display:block}.footer span{display:block;margin-top:5px}}
    </style>
</head>
<body>
    <main class="shell">
        <a class="brand" href="{{ url('/') }}" aria-label="8Kommerce home"><span class="mark">8K</span><span>8Kommerce</span></a>
        <section class="content">
            <div>
                <p class="code">Error @yield('code')</p>
                <h1 class="title">@yield('title')</h1>
                <p class="message">@yield('message')</p>
                <div class="actions">
                    <a class="button primary" href="{{ url('/') }}">Go to homepage</a>
                    @hasSection('secondary_url')
                        <a class="button" href="@yield('secondary_url')">@yield('secondary_label')</a>
                    @else
                        <a class="button" href="{{ url()->previous() }}">Go back</a>
                    @endif
                </div>
            </div>
            <div class="visual" aria-hidden="true">
                <div class="window"><div class="window-top"><i></i><i></i><i></i></div><span class="line"></span><span class="line short"></span><span class="line"></span></div>
            </div>
        </section>
        <footer class="footer"><span>© {{ now()->year }} 8Kommerce</span><span>Need help? Return to a safe page and try again.</span></footer>
    </main>
</body>
</html>
