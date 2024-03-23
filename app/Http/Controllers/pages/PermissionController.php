<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Nwidart\Modules\Facades\Module;

class PermissionController extends Controller
{
    public function index()
    {
        return view('content.pages.permissions');
    }

    public function getAllPermissions()
    {
        // Load generic permissions
        $genericPermissions = include resource_path('lang/hu/auth.php');
        $mergedPermissions = $genericPermissions['permissions'] ?? [];

        // Dynamically load module-specific permissions and merge them
        foreach (Module::all() as $module) {
            $modulePermissionsPath = module_path($module->getName(), 'Resources/lang/hu/auth.php');
            
            if (file_exists($modulePermissionsPath)) {
                $modulePermissions = include $modulePermissionsPath;
                if (isset($modulePermissions['permissions'])) {
                    $mergedPermissions = array_merge_recursive($mergedPermissions, $modulePermissions['permissions']);
                }
            }
        }

        // Retrieve all permissions from the database
        $permissions = Permission::select(['id', 'name'])
        ->withCount('roles')
        ->get()
        ->map(function ($permission) use ($mergedPermissions) {
            // Localize the permission names based on merged permissions
            $localizedPermission = $mergedPermissions[$permission->name] ?? $permission->name;
            $permission->name = $localizedPermission;
            return $permission;
        });

        return response()->json(['data' => $permissions]);
    }
}
