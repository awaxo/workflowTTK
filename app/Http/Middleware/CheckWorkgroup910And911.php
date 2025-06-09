<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workgroup;

/**
 * Middleware to check if the authenticated user is a leader of workgroup 910 or 911,
 * or has the 'adminisztrator' role.
 * If not, it redirects to a not authorized view.
 */
class CheckWorkgroup910And911
{
    /* * Handle an incoming request.
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
        $workgroup910 = Workgroup::where('workgroup_number', 910)->first();
        $workgroup911 = Workgroup::where('workgroup_number', 911)->first();

        if ($user && $user->hasRole('adminisztrator')) {
            return $next($request);
        }
        if ($workgroup911 && $workgroup911->leader_id === $user->id) {
            return $next($request);
        }
        if ($workgroup910 && $workgroup910->leader_id === $user->id) {
            return $next($request);
        }

        return response()->view('content.pages.misc-not-authorized');
    }
}