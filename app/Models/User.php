<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Specify custom table name
     */
    protected $table = 'wf_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'workgroup_number',
        'email_verified_at',
        'password',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'deleted' => 'boolean',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'deleted' => 0,
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the workgroup associated with the user.
     */
    public function workgroup()
    {
        return $this->belongsTo(Workgroup::class);
    }

    /**
     * Get the user who created this user.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this user.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Determine if the user has the given ability.
     *
     * @param  string|array  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function can($ability, $arguments = [])
    {
        // First, check if the user has the ability directly.
        if (parent::can($ability, $arguments)) {
            return true;
        }

        // Next, check for the ability through substitutions.
        return $this->hasPermissionThroughSubstitution($ability, $arguments);
    }

    /**
     * Check if the user has the given permission through substitution.
     *
     * @param  string  $permission
     * @param  array  $arguments
     * @return bool
     */
    protected function hasPermissionThroughSubstitution($permission, $arguments = [])
    {
        // Check for active substitutions
        $substitutes = RoleSubstitute::where('substitute_user_id', $this->id)
                                       ->whereDate('start_date', '<=', now())
                                       ->whereDate('end_date', '>=', now())
                                       ->get();

        foreach ($substitutes as $substitute) {
            if ($substitute->role->hasPermissionTo($permission)) {
                return true;
            }
        }
    
        return false;
    }
}
