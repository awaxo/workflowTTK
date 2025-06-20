<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ExternalAccessRight model represents the rights granted to external systems
 * for accessing certain functionalities within the application.
 * It includes attributes for the external system, admin group number, and user references for creation and updates.
 */
class ExternalAccessRight extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wf_external_access_rights';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'external_system',
        'admin_group_number',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted' => 'boolean',
    ];

    /**
     * The attributes that should have default values.
     *
     * @var array
     */
    protected $attributes = [
        'deleted' => 0,
    ];

    /**
     * Get the external system associated with this access right.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workgroup()
    {
        return $this->belongsTo(Workgroup::class, 'admin_group_number');
    }

    /**
     * Get the user who created this external access right.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
     * Get the user who last updated this external access right.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}