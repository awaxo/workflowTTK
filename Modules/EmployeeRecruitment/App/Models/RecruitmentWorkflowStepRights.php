<?php

namespace Modules\EmployeeRecruitment\App\Models;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\EmployeeRecruitment\Database\Factories\RecruitmentWorkflowStepRightFactory;

class RecruitmentWorkflowStepRights extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return RecruitmentWorkflowStepRightFactory::new();
    }

    protected $table = 'recruitment_workflow_step_rights';

    protected $fillable = [
        'workflow_step_id',
        'role_id',
        'custom_approval_rules',
        'created_by',
        'updated_by',
    ];

    /*public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }*/

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
