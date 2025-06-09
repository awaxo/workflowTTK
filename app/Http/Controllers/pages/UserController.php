<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\ExternalPrivilege;
use App\Models\Role;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Closure;

/*
 * UserController handles the user management page and related functionality.
 *
 * This controller is responsible for displaying the user management page,
 * fetching users, checking unique constraints, and creating/updating users.
 */
class UserController extends Controller
{
    /**
     * Display the user management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $apiEndpoint = '/api/users';
        $workgroups = Workgroup::where('deleted', 0)->get();
        $roles = Role::all()->map(function ($role) {
            $role->name_readable = __('auth.roles.' . $role->name);
            return $role;
        });

        $externalPrivileges = ExternalPrivilege::all();

        return view('content.pages.users', compact('apiEndpoint', 'workgroups', 'roles', 'externalPrivileges'));
    }

    /**
     * Display the user management page filtered by role.
     *
     * @param string $roleName
     * @return \Illuminate\View\View
     */
    public function indexByRole($roleName)
    {
        // Dynamically generate the API endpoint based on the role
        $apiEndpoint = "/api/users/role/$roleName";
        $workgroups = Workgroup::where('deleted', 0)->get();
        $roles = Role::all()->map(function ($role) {
            $role->name_readable = __('auth.roles.' . $role->name);
            return $role;
        });

        // get readable role name from $roles by $roleName
        $role = $roles->firstWhere('name', $roleName);
        if (!$role) {
            abort(404, 'Role not found');
        }
        $roleNameReadable = $role->name_readable;

        $externalPrivileges = ExternalPrivilege::all();

        return view('content.pages.users', compact('apiEndpoint', 'workgroups', 'roles', 'roleNameReadable', 'externalPrivileges'));
    }

    /**
     * Display the user management page filtered by permission.
     *
     * @param string $permissionName
     * @return \Illuminate\View\View
     */
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

    /**
     * Fetch all users, including their details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::all()->map(function ($user) {
                return $this->formatUserData($user);
            });
        return response()->json(['data' => $users]);
    }

    /**
     * Fetch users by role, excluding featured users.
     *
     * @param string $roleName
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Fetch users by permission, excluding featured users.
     *
     * @param string $permissionName
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmailUnique()
    {
        $email = request()->input('email');
        $userId = request()->input('user_id');
        
        $query = User::where('email', $email)
            ->where('deleted', 0);
        
        if ($userId) {
            $query->where('id', '!=', $userId);
        }
        
        $exists = $query->exists();
        
        return response()->json(['valid' => !$exists]);
    }

    /**
     * Check if a user name is unique, excluding the current user if editing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkNameUnique()
    {
        $name = request()->input('name');
        $userId = request()->input('user_id');
        
        $query = User::where('name', $name)
            ->where('deleted', 0);
        
        if ($userId) {
            $query->where('id', '!=', $userId);
        }
        
        $exists = $query->exists();
        
        return response()->json(['valid' => !$exists]);
    }

    /**
     * Delete a user by setting its deleted flag.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $user = User::find($id);
        $user->deleted = 1;
        $user->save();

        event(new ModelChangedEvent($user, 'deleted'));

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $user = User::find($id);
        $user->deleted = 0;
        $user->save();

        event(new ModelChangedEvent($user, 'restored'));

        return response()->json(['message' => 'User restored successfully']);
    }

    /**
     * Update an existing user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $validatedData = $this->validateRequest();

        $user = User::find($id);
        $user->fill($validatedData);
        $user->workgroup_id = request('workgroup_id');
        $user->syncRoles(request('roles'));

        if (method_exists($user, 'externalPrivileges')) {
            $user->externalPrivileges()->sync(request('external_privileges', []));
        }

        $user->updated_by = Auth::id();
        $user->save();

        event(new ModelChangedEvent($user, 'updated'));

        return response()->json(['message' => 'User updated successfully']);
    }

    /**
     * Create a new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

        if (method_exists($user, 'externalPrivileges')) {
            $user->externalPrivileges()->sync(request('external_privileges', []));
        }

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

    /**
     * Format user data for API response.
     *
     * @param User $user
     * @return array|null
     */
    private function formatUserData($user)
    {
        if (!$user) {
            return null;
        }

        try {
            $userData = [
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
                'external_privileges' => '',
                'external_privilege_ids' => [],
                'deleted' => $user->deleted,
                'featured' => $user->featured,
                'created_at' => $user->created_at,
                'created_by_name' => $user->createdBy ? $user->createdBy->name : null,
                'updated_at' => $user->updated_at,
                'updated_by_name' => $user->updatedBy ? $user->updatedBy->name : null,
            ];

            if (method_exists($user, 'externalPrivileges') && $user->externalPrivileges) {
                $userData['external_privileges'] = $user->externalPrivileges->pluck('name')->implode(', ');
                $userData['external_privilege_ids'] = $user->externalPrivileges->pluck('id')->toArray();
            }
            
            return $userData;
        } catch (\Exception $e) {
            Log::error('Error formatting user data for user ID: ' . $user->id . '. Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate the incoming request data for user creation or update.
     *
     * @return array
     */
    private function validateRequest()
    {
        $userId = request()->input('userId');
        
        $activeWorkgroups = Workgroup::where('deleted', 0)
            ->pluck('id');
        
        return request()->validate([
            'name' => [
                'required',
                'max:255',
                'regex:/^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ\s\-\.]+$/',
                Rule::unique('wf_user')->where(function ($query) {
                    return $query->where('deleted', 0);
                })->ignore(request()->input('userId')),
            ],
            'email' => [
                'required',
                'max:255',
                'regex:/^[_a-zA-Z0-9\-]+([_a-zA-Z0-9.\-]+)*@ttk.hu$/',
                Rule::unique('wf_user')->where(function ($query) {
                    return $query->where('deleted', 0);
                })->ignore(request()->input('userId')),
            ],
            'workgroup_id' => [
                'required',
                Rule::in($activeWorkgroups->toArray()),
            ],
        ],
        [
            'name.required' => 'Név kötelező',
            'name.max' => 'Név maximum 255 karakter lehet',
            'name.regex' => 'A név csak betűket, szóközt, kötőjelet és pontot tartalmazhat',
            'name.unique' => 'Ez a név már foglalt',
            'email.required' => 'Email kötelező',
            'email.regex' => 'Az email címnek ttk.hu végződésűnek kell lennie és csak megengedett karaktereket tartalmazhat',
            'email.max' => 'Email maximum 255 karakter lehet',
            'email.unique' => 'Ez az email cím már foglalt',
            'workgroup_id.required' => 'Csoport kötelező',
            'workgroup_id.in' => 'Csak aktív csoport választható',
        ]);
    }
}
