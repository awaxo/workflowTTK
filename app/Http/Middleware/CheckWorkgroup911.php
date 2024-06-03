<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workgroup;

class CheckWorkgroup911
{
    public function handle($request, Closure $next)
    {
        $user = User::find(Auth::id());
        $workgroup911 = Workgroup::where('workgroup_number', 911)->first();

        if (!$workgroup911 || $workgroup911->leader_id !== $user->id) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}