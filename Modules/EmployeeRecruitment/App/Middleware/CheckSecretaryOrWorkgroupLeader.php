<?php

namespace Modules\EmployeeRecruitment\App\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Workgroup;
use App\Services\SecretaryRoleService;

class CheckSecretaryOrWorkgroupLeader
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $roles = SecretaryRoleService::getAll();
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