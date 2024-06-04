<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workgroup;

class CheckWorkgroup910And911
{
    public function handle($request, Closure $next)
    {
        $user = User::find(Auth::id());
        $workgroup910 = Workgroup::where('workgroup_number', 910)->first();
        $workgroup911 = Workgroup::where('workgroup_number', 911)->first();

        if (!$user) {
            return redirect()->route('login');
        }

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