<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetDefaultGuard
{
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
