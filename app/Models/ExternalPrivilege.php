<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ExternalPrivilege model represents a privilege that can be assigned to users outside the standard role-based permissions.
 * It includes attributes for the privilege name and description, and relationships to users who have this privilege.
 */
class ExternalPrivilege extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wf_external_privileges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the users that have this external privilege.
     *
     * @var array
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'wf_external_privilege_user', 'external_privilege_id', 'user_id')
                    ->withTimestamps();
    }

    /*
     * Get the user who created the external privilege.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the external privilege.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}