<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApproverSubstitute extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wf_approver_substitute';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'original_approver_role_group_id',
        'substitute_role_group_id',
        'start_date',
        'end_date',
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
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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
     * Get the original approver role group associated with the approver substitute.
     */
    public function originalApproverRoleGroup()
    {
        return $this->belongsTo(RoleGroup::class, 'original_approver_role_group_id');
    }

    /**
     * Get the substitute role group associated with the approver substitute.
     */
    public function substituteRoleGroup()
    {
        return $this->belongsTo(RoleGroup::class, 'substitute_role_group_id');
    }

    /**
     * Get the user who created the approver substitute.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the approver substitute.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}