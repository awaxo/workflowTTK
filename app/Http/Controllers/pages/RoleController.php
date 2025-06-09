<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Role;

/**
 * RoleController handles the roles management page.
 * 
 * This controller is responsible for rendering the roles view and providing
 * APIs to fetch roles and roles by permission.
 */
class RoleController extends Controller
{
    /**
     * Display the roles management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $apiEndpoint = "/api/roles";
        return view('content.pages.roles', compact('apiEndpoint'));
    }

    /**
     * Fetch all roles with their user count and readable names.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Display the roles management page filtered by permission.
     *
     * @param string $permissionName
     * @return \Illuminate\View\View
     */
    public function indexByPermission($permissionName)
    {
        // Dynamically generate the API endpoint based on the permission
        $apiEndpoint = "/api/roles/permission/$permissionName";
        return view('content.pages.roles', compact('apiEndpoint'));
    }

    /**
     * Fetch roles that have a specific permission.
     *
     * @param string $permissionName
     * @return \Illuminate\Http\JsonResponse
     */
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
