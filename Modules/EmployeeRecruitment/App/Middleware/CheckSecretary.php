<?php

namespace Modules\EmployeeRecruitment\App\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\RoleService;

class CheckSecretary
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $roles = RoleService::getAllSecretaryRoles();
        $user = User::find(Auth::id());
        
        if (!$user || !$user->hasAnyRole($roles)) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}