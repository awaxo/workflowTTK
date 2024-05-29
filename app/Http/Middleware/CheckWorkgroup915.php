<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workgroup;

class CheckWorkgroup915
{
    public function handle($request, Closure $next)
    {
        $user = User::find(Auth::id());
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();

        if (!$workgroup915 || $workgroup915->leader_id !== $user->id) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}