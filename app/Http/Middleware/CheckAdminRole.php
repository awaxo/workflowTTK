<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckAdminRole
{
    public function handle($request, Closure $next)
    {
        $user = User::find(Auth::id());
        if (!$user || !$user->hasRole('adminisztrator')) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}