<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $apiEndpoint = "/api/roles";
        return view('content.pages.roles', compact('apiEndpoint'));
    }

    public function getAllRoles()
    {
        $roles = Role::select(['id', 'name'])
            ->withCount('users')
            ->get()
            ->map(function ($role) {
                $role->name_readable = __('auth.roles.' . $role->name);
                return $role;
            });

        return response()->json(['data' => $roles]);
    }

    public function indexByPermission($permissionName)
    {
        // Dynamically generate the API endpoint based on the permission
        $apiEndpoint = "/api/roles/permission/$permissionName";
        return view('content.pages.roles', compact('apiEndpoint'));
    }

    public function getRolesByPermission($permissionName)
    {
        $roles = Role::whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })
        ->withCount('users')
        ->get()
        ->map(function ($role) {
            $role->name_readable = __('auth.roles.' . $role->name);
            return $role;
        });

        return response()->json(['data' => $roles]);        
    }
}
