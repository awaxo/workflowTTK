<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workgroup;

/**
 * Middleware to check if the authenticated user is a leader of workgroup 910.
 * If not, it redirects to a not authorized view.
 */
class CheckWorkgroup910
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
        $workgroup910 = Workgroup::where('workgroup_number', 910)->first();

        if (!$workgroup910 || $workgroup910->leader_id !== $user->id) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}