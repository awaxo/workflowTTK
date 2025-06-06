<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class RoleService
{
    /**
     * Check if user has secretary role
     *
     * @param User $user
     * @return bool
     */
    public static function isSecretary(User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        // Get all secretary role names
        $secretaryRoles = self::getAllSecretaryRoles();
        
        // Check user roles directly
        foreach ($user->roles as $role) {
            if (in_array($role->name, $secretaryRoles)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get all secretary role names
     *
     * @return string[]
     */
    public static function getAllSecretaryRoles(): array
    {
        // Cache for 10 minutes
        return Cache::remember('titkar_roles', now()->addMinutes(10), function () {
            return Role::where('name', 'like', 'titkar\_%')
                       ->pluck('name')
                       ->toArray();
        });
    }

    /**
     * Get role display name
     *
     * @param string $roleName
     * @return string
     */
    public static function getDisplayName(string $roleName): string
    {
        return Role::getDisplayName($roleName);
    }

    /**
     * Get all roles with their display names
     *
     * @return array
     */
    public static function getAllRolesWithDisplayNames(): array
    {
        return Role::getAllWithDisplayNames();
    }

    /**
     * Get all roles formatted for select options
     *
     * @return array
     */
    public static function getRolesForSelect(): array
    {
        $roles = self::getAllRolesWithDisplayNames();
        
        // Sort by display name
        asort($roles);
        
        return $roles;
    }

    /**
     * Create or update role with display name
     *
     * @param string $roleName
     * @param string $displayName
     * @param string $guardName
     * @return Role
     */
    public static function createOrUpdateRole(string $roleName, string $displayName, string $guardName = 'web'): Role
    {
        $role = Role::updateOrCreate(
            ['name' => $roleName, 'guard_name' => $guardName],
            ['display_name' => $displayName]
        );

        // Clear cache
        self::clearCache();

        return $role;
    }

    /**
     * Get secretary roles with their display names
     *
     * @return array
     */
    public static function getSecretaryRolesWithDisplayNames(): array
    {
        $cacheKey = 'secretary_roles_with_display_names';
        
        return Cache::remember($cacheKey, 3600, function () {
            return Role::where('name', 'like', 'titkar\_%')
                      ->whereNotNull('display_name')
                      ->where('display_name', '!=', '')
                      ->pluck('display_name', 'name')
                      ->toArray();
        });
    }

    /**
     * Check if role exists
     *
     * @param string $roleName
     * @return bool
     */
    public static function roleExists(string $roleName): bool
    {
        return Role::where('name', $roleName)->exists();
    }

    /**
     * Get user's role display names
     *
     * @param User $user
     * @return array
     */
    public static function getUserRoleDisplayNames(User $user): array
    {
        if (!$user) {
            return [];
        }

        $displayNames = [];
        
        foreach ($user->roles as $role) {
            $displayNames[] = $role->display_name ?: $role->name;
        }

        return $displayNames;
    }

    /**
     * Clear all role-related cache
     */
    public static function clearCache(): void
    {
        Cache::forget('titkar_roles');
        Cache::forget('all_roles_with_display_names');
        Cache::forget('secretary_roles_with_display_names');
        
        // Clear individual role display name caches
        $roles = Role::pluck('name');
        foreach ($roles as $roleName) {
            Cache::forget("role_display_name_{$roleName}");
        }
    }
}