<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * Middleware to check if the authenticated user has the 'koltseghely_adatkarbantarto' or 'adminisztrator' role,
 * or belongs to specific workgroups (900, 901, 903, 908, 910, 911, 912).
 * This middleware allows access to users with these roles or workgroups and redirects others to a not authorized view.
 */
class CheckCostCenterViewers
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