<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs(
            'onboarding.*',
            'profile.*',
            'logout',
            'verification.*',
            'billing.*',
            'legal.data-deletion.store'
        )) {
            return $next($request);
        }

        // Onboarding is guidance, not an access gate. Incomplete sellers can keep
        // working on every device and return to the checklist when they are ready.
        return $next($request);
    }
}
