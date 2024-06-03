<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workgroup;

class CheckWorkgroup908
{
    public function handle($request, Closure $next)
    {
        $user = User::find(Auth::id());
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user && $user->hasRole('adminisztrator')) {
            return $next($request);
        }
        if ($workgroup908 && $workgroup908->leader_id === $user->id) {
            return $next($request);
        }

        return response()->view('content.pages.misc-not-authorized');
    }
}