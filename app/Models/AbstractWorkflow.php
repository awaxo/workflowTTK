<?php

namespace App\Models;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ZeroDaHero\LaravelWorkflow\Traits\WorkflowTrait;

abstract class AbstractWorkflow extends Model implements IGenericWorkflow
{
    use HasFactory;
    use WorkflowTrait;

    abstract protected static function newFactory();

    /**
     * Fetch active workflows.
     *
     * @return Collection|AbstractWorkflow[]
     */
    public static function fetchActive(): Collection
    {
        return static::where('state', '!=', 'completed')->with(['workflowType', 'initiatorWorkgroup', 'createdBy', 'updatedBy'])->get();
    }

    protected $fillable = [
        'workflow_type_id',
        'workflow_deadline',
        'state',
        'initiator_workgroup_id',
        'meta_key',
        'meta_value',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];

    protected $attributes = [
        'deleted' => 0,
    ];

    public function workflowType()
    {
        return $this->belongsTo(WorkflowType::class, 'workflow_type_id');
    }

    public function initiatorWorkgroup()
    {
        return $this->belongsTo(Workgroup::class, 'initiator_workgroup_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getDataAttribute($attribute)
    {
        return $this->$attribute ?? null;
    }

    public function getCurrentState(): string
    {
        return $this->state;
    }
}
