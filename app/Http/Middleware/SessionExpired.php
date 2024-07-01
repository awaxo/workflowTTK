<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SessionExpired
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login'); // Redirect to the login page if the user is not authenticated
        }

        return $next($request);
    }
}
