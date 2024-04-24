<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $apiEndpoint = '/api/users';
        $workgroups = Workgroup::where('deleted', 0)->get();

        return view('content.pages.users', compact('apiEndpoint', 'workgroups'));
    }

    public function indexByRole($roleName)
    {
        // Dynamically generate the API endpoint based on the role
        $apiEndpoint = "/api/users/role/$roleName";
        return view('content.pages.users', compact('apiEndpoint'));
    }

    public function getAllUsers()
    {
        $users = User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'workgroup_id' => $user->workgroup_id,
                'workgroup_name' => ($user->workgroup->workgroup_number . ' - ' . $user->workgroup->name),
                'deleted' => $user->deleted,
                'created_at' => $user->created_at,
                'created_by_name' => $user->createdBy->name,
                'updated_at' => $user->updated_at,
                'updated_by_name' => $user->updatedBy->name,
            ];
        });
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
