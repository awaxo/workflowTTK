<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return view('content.pages.roles');
    }

    public function getAllRoles()
    {
        $roles = Role::select(['id', 'name'])
            ->withCount('users')
            ->get()
            ->map(function ($role) {
                $role->name = __('auth.roles.' . $role->name);
                return $role;
            });

        return response()->json(['data' => $roles]);
    }
}
