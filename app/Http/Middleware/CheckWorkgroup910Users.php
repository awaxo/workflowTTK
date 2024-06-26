<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckWorkgroup910Users
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = User::find(Auth::id());

        if (!$user || $user->workgroup->workgroup_number !== 910) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}