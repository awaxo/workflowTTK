<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = ['name', 'display_name', 'guard_name'];

    /**
     * Get role display name by role name
     */
    public static function getDisplayName(string $roleName): string
    {
        $cacheKey = "role_display_name_{$roleName}";
        
        return Cache::remember($cacheKey, 3600, function () use ($roleName) {
            $role = self::where('name', $roleName)->first();
            
            return $role && $role->display_name ? $role->display_name : $roleName;
        });
    }

    /**
     * Get all roles with their display names
     */
    public static function getAllWithDisplayNames(): array
    {
        $cacheKey = "all_roles_with_display_names";
        
        return Cache::remember($cacheKey, 3600, function () {
            return self::whereNotNull('display_name')
                      ->where('display_name', '!=', '')
                      ->pluck('display_name', 'name')
                      ->toArray();
        });
    }

    /**
     * Clear role-related cache when model is updated
     */
    protected static function booted()
    {
        static::saved(function ($role) {
            Cache::forget("role_display_name_{$role->name}");
            Cache::forget("all_roles_with_display_names");
            Cache::forget('titkar_roles');
        });

        static::deleted(function ($role) {
            Cache::forget("role_display_name_{$role->name}");
            Cache::forget("all_roles_with_display_names");
            Cache::forget('titkar_roles');
        });
    }
}
