<?php

use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\EnsureActiveAccess;
use App\Http\Middleware\EnsurePaymentsPlan;
use App\Http\Middleware\EnsurePhoneAuthEnabled;
use App\Http\Middleware\EnsureProfileOnboardingComplete;
use App\Http\Middleware\EnsurePromotionAccess;
use App\Http\Middleware\SetWebCacheHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Configure the exact proxy CIDRs in production. A wildcard remains
        // available for platforms where the proxy addresses are not stable.
        $trustedProxies = array_values(array_filter(array_map(
            static fn (string $proxy): string => trim($proxy),
            explode(',', (string) env('TRUSTED_PROXIES', ''))
        )));
        if ($trustedProxies !== []) {
            $middleware->trustProxies(at: $trustedProxies === ['*'] ? '*' : $trustedProxies);
        }

        $middleware->validateCsrfTokens(except: [
            'webhooks/paystack',
            'webhooks/twilio/status',
        ]);

        $middleware->alias([
            'admin' => AdminOnly::class,
            'active_access' => EnsureActiveAccess::class,
            'payments_plan' => EnsurePaymentsPlan::class,
            'promotion_access' => EnsurePromotionAccess::class,
            'profile_onboarding_complete' => EnsureProfileOnboardingComplete::class,
            'phone_auth_enabled' => EnsurePhoneAuthEnabled::class,
        ]);

        $middleware->web(append: [
            SetWebCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $exception, $request) {
            $contentLength = (int) ($request->server('CONTENT_LENGTH') ?? 0);
            $userId = optional($request->user())->id;

            Log::warning('Upload rejected: request exceeds server body size limit', [
                'path' => $request->path(),
                'method' => $request->method(),
                'content_length_bytes' => $contentLength,
                'user_id' => $userId,
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Upload too large. Reduce image sizes or upload fewer files at once.',
                ], 413);
            }

            return back()->withErrors([
                'upload' => 'Upload is too large for the server limit. Try fewer files or smaller images.',
            ]);
        });

        $exceptions->report(function (Throwable $exception): void {
            if (! config('monitoring.error_tracking')) {
                return;
            }

            $provider = strtolower((string) config('monitoring.provider', 'sentry'));
            Log::error('Unhandled exception captured for monitoring', [
                'provider' => $provider,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            if ($provider === 'sentry' && app()->bound('sentry')) {
                app('sentry')->captureException($exception);

                return;
            }

            if ($provider === 'bugsnag' && app()->bound('bugsnag')) {
                app('bugsnag')->notifyException($exception);

                return;
            }

            Log::warning('Monitoring provider not bound; exception logged only', [
                'provider' => $provider,
            ]);
        });
    })->create();
