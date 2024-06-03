<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckWorkgroup910And911Users
{
    public function handle($request, Closure $next)
    {
        $user = User::find(Auth::id());

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user && $user->hasRole('adminisztrator')) {
            return $next($request);
        }
        if ($user && $user->workgroup->workgroup_number == 910) {
            return $next($request);
        }
        if ($user && $user->workgroup->workgroup_number == 911) {
            return $next($request);
        }

        return response()->view('content.pages.misc-not-authorized');
    }
}