<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workgroup;

class CheckWorkgroup912
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = User::find(Auth::id());
        $workgroup912 = Workgroup::where('workgroup_number', 912)->first();

        if ($user && $user->hasRole('adminisztrator')) {
            return $next($request);
        }
        if ($workgroup912 && $workgroup912->leader_id === $user->id) {
            return $next($request);
        }

        return response()->view('content.pages.misc-not-authorized');
    }
}