<?php

namespace Modules\EmployeeRecruitment\App\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckSecretary
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $roles = ['titkar_9_fi','titkar_9_gi','titkar_1','titkar_3','titkar_4','titkar_5','titkar_6','titkar_7','titkar_8'];
        $user = User::find(Auth::id());
        
        if (!$user || !$user->hasAnyRole($roles)) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}