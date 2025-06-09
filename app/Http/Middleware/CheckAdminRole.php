<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * Middleware to check if the authenticated user has the 'adminisztrator' role.
 * If not, it redirects to the login page or returns a not authorized view.
 */
class CheckAdminRole
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
        if (!$user || !$user->hasRole('adminisztrator')) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}