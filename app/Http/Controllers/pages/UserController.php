<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $apiEndpoint = '/api/users';
        return view('content.pages.users', compact('apiEndpoint'));
    }

    public function indexByRole($roleName)
    {
        // Dynamically generate the API endpoint based on the role
        $apiEndpoint = "/api/users/role/$roleName";
        return view('content.pages.users', compact('apiEndpoint'));
    }

    public function getAllUsers()
    {
        $users = User::all(['id', 'name', 'email', 'created_at']);

        return response()->json(['data' => $users]);
    }

    public function getUsersByRole($roleName)
    {
        $role = Role::findByName($roleName);
        $users = $role->users->map(function ($user) {
            return $user->only(['id', 'name', 'email', 'created_at']);
        });

        return response()->json(['data' => $users]);
    }
}
