<?php

namespace Modules\EmployeeRecruitment\App\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Workgroup;
use App\Services\RoleService;

/**
 * CheckSecretaryOrWorkgroupLeader is a middleware that checks if the authenticated user
 * has any of the secretary roles or is a workgroup leader.
 * If not, it redirects to the login page or returns a not authorized view.
 */
class CheckSecretaryOrWorkgroupLeader
{
    /**
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
        
        $isWorkgroupLeader = Workgroup::whereHas('leader', function($query) {
                                $query->where('id', Auth::user()->id);
                             })->exists();
        
        if ((!$user || !$user->hasAnyRole($roles)) && !$isWorkgroupLeader) {
            return response()->view('content.pages.misc-not-authorized');
        }

        return $next($request);
    }
}