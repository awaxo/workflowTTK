<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
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

        event(new ModelChangedEvent($user, 'updated'));

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

        event(new ModelChangedEvent($user, 'created'));

        return response()->json(['message' => 'User created successfully']);
    }

    /**
     * Creates a new user or updates existing one from provided data
     * 
     * @param array $userData Array containing user data with the following keys:
     *                      - name
     *                      - email
     *                      - workgroup_id
     *                      - workflow_id
     *                      - social_security_number
     *                      - contract_expiration
     *                      - legal_relationship
     * @return User
     */
    public function createUserFromData(array $userData): User
    {
        try {
            // Check if user exists with the given social security number
            $user = User::where('social_security_number', $userData['social_security_number'])->first();
            $isNewUser = !$user;

            if (!$user) {
                $user = new User();
                // Set creation audit fields for new user
                $user->created_at = now();
                $user->created_by = 1;
            }
            
            // Map basic information
            $user->name = $userData['name'];
            $user->email = $userData['email'];
            $user->workgroup_id = $userData['workgroup_id'];
            
            // Only set password for new users
            if ($isNewUser) {
                $user->password = bcrypt('password');
            }
            
            $user->workflow_id = $userData['workflow_id'];
            $user->social_security_number = $userData['social_security_number'];
            $user->contract_expiration = $userData['contract_expiration'];
            $user->legal_relationship = $userData['legal_relationship'];
            
            // Update audit fields
            $user->updated_at = now();
            $user->updated_by = 1;
            
            $user->save();

            Log::info($isNewUser ? 'User created successfully' : 'User updated successfully', [
                'workflow_id' => $userData['workflow_id'],
                'user_id' => $user->id,
                'social_security_number' => $userData['social_security_number']
            ]);
            
            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to create/update user', [
                'workflow_id' => $userData['workflow_id'] ?? null,
                'social_security_number' => $userData['social_security_number'] ?? null,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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
