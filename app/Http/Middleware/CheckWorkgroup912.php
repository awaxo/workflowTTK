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
        $user = User::find(Auth::id());
        $workgroup912 = Workgroup::where('workgroup_number', 912)->first();

        if (!$workgroup912 || $workgroup912->leader_id !== $user->id) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}