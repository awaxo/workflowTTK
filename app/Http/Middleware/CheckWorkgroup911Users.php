<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckWorkgroup911Users
{
    public function handle($request, Closure $next)
    {
        $user = User::find(Auth::id());

        if (!$user || $user->workgroup->workgroup_number !== 911) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}