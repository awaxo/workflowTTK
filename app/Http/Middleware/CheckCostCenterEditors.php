<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckCostCenterEditors
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = User::find(Auth::id());

        if ($user && $user->hasRole('adminisztrator')) {
            return $next($request);
        }
        
        if ($user && $user->hasRole('koltseghely_adatkarbantarto')) {
            return $next($request);
        }
        
        return response()->view('content.pages.misc-not-authorized');
    }
}