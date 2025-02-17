<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $apiEndpoint = '/api/users';
        $workgroups = Workgroup::where('deleted', 0)->get();
        $roles = Role::all()->map(function ($role) {
            $role->name_readable = __('auth.roles.' . $role->name);
            return $role;
        });

        return view('content.pages.users', compact('apiEndpoint', 'workgroups', 'roles'));
    }

    public function indexByRole($roleName)
    {
        // Dynamically generate the API endpoint based on the role
        $apiEndpoint = "/api/users/role/$roleName";
        $workgroups = Workgroup::where('deleted', 0)->get();
        $roles = Role::all()->map(function ($role) {
            $role->name_readable = __('auth.roles.' . $role->name);
            return $role;
        });

        return view('content.pages.users', compact('apiEndpoint', 'workgroups', 'roles'));
    }

    public function getAllAndFeaturedUsers()
    {
        $users = User::withFeatured()
            ->where('featured', 1)
            ->get()
            ->map(function ($user) {
                return $this->formatUserData($user);
            });
        return response()->json(['data' => $users]);
    }

    public function getAllUsers()
    {
        $users = User::all()->map(function ($user) {
                return $this->formatUserData($user);
            });
        return response()->json(['data' => $users]);
    }

    public function getUsersByRole($roleName)
    {
        $role = Role::findByName($roleName);
        $users = $role->users()
            ->where('featured', 0)
            ->get()
            ->map(function ($user) {
                return $this->formatUserData($user);
            });

        return response()->json(['data' => $users]);
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->deleted = 1;
        $user->save();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function restore($id)
    {
        $user = User::find($id);
        $user->deleted = 0;
        $user->save();
        return response()->json(['message' => 'User restored successfully']);
    }

    public function update($id)
    {
        $validatedData = $this->validateRequest();

        $user = User::find($id);
        $user->fill($validatedData);
        $user->workgroup_id = request('workgroup_id');
        $user->syncRoles(request('roles'));
        $user->updated_by = Auth::id();
        $user->save();

        return response()->json(['message' => 'User updated successfully']);
    }

    public function create()
    {
        $validatedData = $this->validateRequest();

        $user = new User();
        $user->fill($validatedData);
        $user->syncRoles(request('roles'));
        $user->password = bcrypt('password');
        $user->created_by = Auth::id();
        $user->updated_by = Auth::id();
        $user->save();

        return response()->json(['message' => 'User created successfully']);
    }

    private function formatUserData($user)
    {
        if (!$user) {
            return null;
        }

        try {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'workgroup_id' => $user->workgroup_id,
                'workgroup_name' => $user->workgroup 
                    ? ($user->workgroup->workgroup_number . ' - ' . $user->workgroup->name)
                    : null,
                'role_names' => $user->roles ? $user->roles->map(function ($role) {
                    return $role->name;
                }) : [],
                'roles' => $user->roles ? $user->roles->map(function ($role) {
                    return __('auth.roles.' . $role->name);
                })->implode(', ') : '',
                'deleted' => $user->deleted,
                'featured' => $user->featured,
                'created_at' => $user->created_at,
                'created_by_name' => $user->createdBy ? $user->createdBy->name : null,
                'updated_at' => $user->updated_at,
                'updated_by_name' => $user->updatedBy ? $user->updatedBy->name : null,
            ];
        } catch (\Exception $e) {
            Log::error('Error formatting user data for user ID: ' . $user->id . '. Error: ' . $e->getMessage());
            return null;
        }
    }

    private function validateRequest()
    {
        return request()->validate([
            'name' => 'required|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('wf_user', 'email')->ignore(request()->input('userId')),
            ],
            'workgroup_id' => 'required|exists:wf_workgroup,id',
        ],
        [
            'name.required' => 'Név kötelező',
            'name.max' => 'Név maximum 255 karakter lehet',
            'email.required' => 'Email kötelező',
            'email.email' => 'Valós email címet adj meg',
            'email.unique' => 'Ez az email cím már foglalt',
            'email.max' => 'Email maximum 255 karakter lehet',
            'workgroup_id.required' => 'Csoport kötelező',
            'workgroup_id.exists' => 'A megadott csoport nem létezik',
        ]);
    }
}
