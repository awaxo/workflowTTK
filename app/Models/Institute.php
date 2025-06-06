<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\RoleService;
use Illuminate\Support\Facades\Log;

class Institute extends Model
{
    use HasFactory;

    protected $table = 'wf_institute';

    protected $fillable = [
        'group_level',
        'name',
        'abbreviation',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'group_level' => 'integer'
    ];

    protected $attributes = [
        'deleted' => 0,
    ];

    /**
     * Get the user who created the labor administrator.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the labor administrator.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the workgroups that belong to this institute.
     */
    public function workgroups()
    {
        return Workgroup::whereRaw('LEFT(workgroup_number, 1) = ?', [$this->group_level])->where('deleted', 0);
    }

    /**
     * Get the secretary role name for this institute
     */
    public function getSecretaryRoleName(): string
    {
        return "titkar_{$this->group_level}";
    }

    /**
     * Get the secretary role display name for this institute
     */
    public function getSecretaryRoleDisplayName(): string
    {
        return "Titkár - {$this->abbreviation}";
    }

    /**
     * Update secretary role when institute changes
     */
    protected static function booted()
    {
        // When institute is created, create the secretary role
        static::created(function ($institute) {
            $institute->updateSecretaryRole();
        });

        // When institute is updated, update the secretary role
        static::updated(function ($institute) {
            Log::info("Institute updated event triggered", [
        'institute_id' => $institute->id,
        'dirty_fields' => $institute->getDirty(),
        'original_values' => $institute->getOriginal(),
        'abbreviation_dirty' => $institute->isDirty('abbreviation'),
        'group_level_dirty' => $institute->isDirty('group_level'),
        'deleted_dirty' => $institute->isDirty('deleted')
    ]);
            // Check if abbreviation or group_level changed
            if ($institute->isDirty('abbreviation') || $institute->isDirty('group_level')) {
                Log::info("Calling updateSecretaryRole", [
            'old_group_level' => $institute->getOriginal('group_level')
        ]);
                $institute->updateSecretaryRole($institute->getOriginal('group_level'));
            }
        });

        // When institute is deleted (soft delete via deleted flag), handle secretary role
        static::updated(function ($institute) {
            if ($institute->isDirty('deleted') && $institute->deleted) {
                $institute->handleSecretaryRoleOnDelete();
            } elseif ($institute->isDirty('deleted') && !$institute->deleted) {
                // Restored - recreate secretary role
                $institute->updateSecretaryRole();
            }
        });
    }

    /**
     * Update or create secretary role for this institute
     */
    public function updateSecretaryRole(?int $oldGroupLevel = null): void
    {
        Log::info("updateSecretaryRole called", [
        'current_group_level' => $this->group_level,
        'old_group_level' => $oldGroupLevel,
        'condition_result' => ($oldGroupLevel && $oldGroupLevel !== $this->group_level)
    ]);
        try {
            // If group_level changed, handle it as delete + create
            if ($oldGroupLevel && $oldGroupLevel !== $this->group_level) {
                Log::info("DELETING role due to group_level change");
                $this->handleGroupLevelChange($oldGroupLevel);
            } else {
                Log::info("UPDATING role display name only");
                // Just update display name (abbreviation changed or new institute)
                RoleService::createOrUpdateRole(
                    $this->getSecretaryRoleName(),
                    $this->getSecretaryRoleDisplayName(),
                    'dynamic'
                );
                
                Log::info("Secretary role updated for institute", [
                    'institute_id' => $this->id,
                    'institute_name' => $this->name,
                    'role_name' => $this->getSecretaryRoleName(),
                    'display_name' => $this->getSecretaryRoleDisplayName()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update secretary role for institute", [
                'institute_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle secretary role when institute is deleted
     */
    public function handleSecretaryRoleOnDelete(): void
    {
        try {
            $roleName = $this->getSecretaryRoleName();
            $role = Role::where('name', $roleName)->first();
            
            if ($role) {
                // Remove role from all users
                $usersWithRole = $role->users()->count();
                if ($usersWithRole > 0) {
                    $role->users()->detach();
                    Log::info("Removed secretary role from users due to institute deletion", [
                        'institute_id' => $this->id,
                        'role_name' => $roleName,
                        'users_affected' => $usersWithRole
                    ]);
                }
                
                // Delete the role
                $role->delete();
                
                Log::info("Secretary role deleted due to institute deletion", [
                    'institute_id' => $this->id,
                    'institute_name' => $this->name,
                    'role_name' => $roleName
                ]);
            }

            // Clear role cache
            RoleService::clearCache();
            
        } catch (\Exception $e) {
            Log::error("Failed to delete secretary role for institute", [
                'institute_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle group_level change (delete old role, create new one)
     */
    private function handleGroupLevelChange(int $oldGroupLevel): void
    {
        try {
            $oldRoleName = "titkar_{$oldGroupLevel}";
            $oldRole = Role::where('name', $oldRoleName)->first();
            
            if ($oldRole) {
                // Remove role from all users
                $usersWithRole = $oldRole->users()->count();
                if ($usersWithRole > 0) {
                    $oldRole->users()->detach();
                    Log::info("Removed old secretary role from users due to group_level change", [
                        'institute_id' => $this->id,
                        'old_role_name' => $oldRoleName,
                        'users_affected' => $usersWithRole
                    ]);
                }
                
                // Delete the old role
                $oldRole->delete();
                
                Log::info("Old secretary role deleted due to group_level change", [
                    'institute_id' => $this->id,
                    'old_role_name' => $oldRoleName,
                    'old_group_level' => $oldGroupLevel,
                    'new_group_level' => $this->group_level
                ]);
            }
            
            // Create new role with new group_level
            RoleService::createOrUpdateRole(
                $this->getSecretaryRoleName(),
                $this->getSecretaryRoleDisplayName(),
                'dynamic'
            );
            
            Log::info("New secretary role created due to group_level change", [
                'institute_id' => $this->id,
                'new_role_name' => $this->getSecretaryRoleName(),
                'new_display_name' => $this->getSecretaryRoleDisplayName()
            ]);
            
            // Clear role cache
            RoleService::clearCache();
            
        } catch (\Exception $e) {
            Log::error("Failed to handle group_level change for institute", [
                'institute_id' => $this->id,
                'old_group_level' => $oldGroupLevel,
                'new_group_level' => $this->group_level,
                'error' => $e->getMessage()
            ]);
        }
    }
}