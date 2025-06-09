<?php

namespace Modules\EmployeeRecruitment\App\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\RoleService;

/*
 * CheckSecretary is a middleware that checks if the authenticated user has any of the secretary roles.
 * If the user is not authenticated or does not have the required roles, it redirects to the login page
 * or returns a not authorized view.
 */
class CheckSecretary
{
    /*
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
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