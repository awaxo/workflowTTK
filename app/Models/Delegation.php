<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Delegation model represents a delegation of tasks or responsibilities from one user to another.
 * It includes attributes for the original user, delegate user, type of delegation, start and end dates, and status.
 */
class Delegation extends Model
{
    use HasFactory;

    protected $table = 'wf_delegation';

    protected $fillable = [
        'original_user_id',
        'delegate_user_id',
        'type',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deleted' => 'boolean',
    ];

    protected $attributes = [
        'deleted' => 0,
    ];

    /**
     * Get the original user who delegated the tasks.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function originalUser()
    {
        return $this->belongsTo(User::class, 'original_user_id');
    }

    /**
     * Get the user to whom the tasks are delegated.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delegateUser()
    {
        return $this->belongsTo(User::class, 'delegate_user_id');
    }

    /**
     * Get the user who created the delegation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the delegation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}