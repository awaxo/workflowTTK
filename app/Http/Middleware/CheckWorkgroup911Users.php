<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/*
 * Middleware to check if the authenticated user is a member of workgroup 911.
 * If not, it redirects to a not authorized view.
 */
class CheckWorkgroup911Users
{
    /*
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

        if (!$user || $user->workgroup->workgroup_number !== 911) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}