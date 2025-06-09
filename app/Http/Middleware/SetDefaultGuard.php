<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/*
 * Middleware to set the default authentication guard.
 * If the 'dynamic' guard is active, it will use that; otherwise, it will use the default guard.
 */
class SetDefaultGuard
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('dynamic')->check()) {
            Auth::shouldUse('dynamic');
        } else {
            Auth::shouldUse(config('auth.defaults.guard'));
        }

        return $next($request);
    }
}
