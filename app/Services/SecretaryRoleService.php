<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class SecretaryRoleService
{
    /**
     * Ellenőrzi, hogy a felhasználó titkár szerepkörrel rendelkezik-e.
     *
     * @param User $user
     * @return bool
     */
    public static function isSecretary(User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        // Az összes titkár szerepkör neve
        $secretaryRoles = self::getAll();
        
        // Közvetlenül ellenőrizzük a felhasználó szerepeit
        foreach ($user->roles as $role) {
            if (in_array($role->name, $secretaryRoles)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Visszaadja az összes titkár szerepkör nevét.
     *
     * @return string[]
     */
    public static function getAll(): array
    {
        // opcionális cache: 10 percig tarolja
        return Cache::remember('titkar_roles', now()->addMinutes(10), function () {
            return Role::where('name', 'like', 'titkar\_%')
                       ->pluck('name')
                       ->toArray();
        });
    }
}