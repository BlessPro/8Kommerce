<?php

namespace App\Providers;

use App\Models\SellerNotification;
use App\Observers\SellerNotificationObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        SellerNotification::observe(SellerNotificationObserver::class);
        $this->configureRateLimiting();

        $forceHttps = (bool) env('APP_FORCE_HTTPS', app()->environment('production'));
        if ($forceHttps) {
            URL::forceScheme('https');
        }
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('public-pay', function (Request $request): array {
            $phone = preg_replace('/\D+/', '', (string) $request->input('phone_number', 'na')) ?: 'na';

            return [
                Limit::perMinute(20)->by('public-pay-ip:'.$request->ip()),
                Limit::perMinute(6)->by('public-pay-phone:'.$phone),
            ];
        });

        RateLimiter::for('public-checkout', function (Request $request): array {
            $phone = preg_replace('/\D+/', '', (string) $request->input('phone_number', 'na')) ?: 'na';

            return [
                Limit::perMinute(16)->by('public-checkout-ip:'.$request->ip()),
                Limit::perMinute(5)->by('public-checkout-phone:'.$phone),
            ];
        });

        RateLimiter::for('auth-phone-send', function (Request $request): array {
            $phone = preg_replace('/\D+/', '', (string) ($request->input('phone') ?: $request->input('phone_number') ?: 'na')) ?: 'na';

            return [
                Limit::perMinute(8)->by('auth-phone-send-ip:'.$request->ip()),
                Limit::perMinute(3)->by('auth-phone-send-phone:'.$phone),
            ];
        });

        RateLimiter::for('auth-phone-verify', function (Request $request): array {
            $phone = preg_replace('/\D+/', '', (string) ($request->input('phone') ?: $request->input('phone_number') ?: 'na')) ?: 'na';

            return [
                Limit::perMinute(15)->by('auth-phone-verify-ip:'.$request->ip()),
                Limit::perMinute(6)->by('auth-phone-verify-phone:'.$phone),
            ];
        });
    }
}
