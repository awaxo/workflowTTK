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
        return static::where('state', '!=', 'completed')
            ->where('deleted', 0)
            ->with(['workflowType', 'initiatorInstitute', 'createdBy', 'updatedBy'])
            ->get();
    }

    /**
     * Fetch closed workflows.
     *
     * @return Collection|AbstractWorkflow[]
     */
    public static function fetchClosed(): Collection
    {
        return static::whereIn('state', ['completed', 'rejected'])
            ->orWhere('deleted', 1)
            ->with(['workflowType', 'initiatorInstitute', 'createdBy', 'updatedBy'])
            ->get();
    }

    protected $fillable = [
        'workflow_type_id',
        'workflow_deadline',
        'state',
        'initiator_institute_id',
        'meta_data',
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

    public function initiatorInstitute()
    {
        return $this->belongsTo(Institute::class, 'initiator_institute_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getCurrentState(): string
    {
        return $this->state;
    }

    public function isApprovedBy(User $user): bool
    {
        $metaData = json_decode($this->meta_data, true);
        if (isset($metaData['approvals'][$this->state]['approval_user_ids']) && 
            in_array($user->id, $metaData['approvals'][$this->state]['approval_user_ids'])) {
                return true;
        }
        return false;
    }
}
