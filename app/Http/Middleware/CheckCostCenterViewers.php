<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckCostCenterViewers
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
        
        if ($user && $user->workgroup && in_array($user->workgroup->workgroup_number, [900, 901, 903, 908, 910, 911, 912])) {
            return $next($request);
        }

        return response()->view('content.pages.misc-not-authorized');
    }
}