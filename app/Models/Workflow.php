<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wf_workflow';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'workflow_type_id',
        'workflow_deadline',
        'status',
        'meta_key',
        'meta_value',
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
     * Get the workflow type associated with the workflow.
     */
    public function workflowType()
    {
        return $this->belongsTo(WorkflowType::class, 'workflow_type_id');
    }

    /**
     * Get the user who created the workflow.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the workflow.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}