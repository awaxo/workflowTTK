<?php

namespace Modules\EmployeeRecruitment\App\Models;

use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\EmployeeRecruitment\Database\Factories\RecruitmentCostCenterFactory;

class RecruitmentCostCenter extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return RecruitmentCostCenterFactory::new();
    }

    protected $table = 'recruitment_cost_center';

    protected $fillable = [
        'cost_center_code',
        'workgroup_id',
        'name',
        'type',
        'lead_user_id',
        'project_coordinator_user_id',
        'due_date',
        'minimal_order_limit',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'minimal_order_limit' => 'decimal:2',
        'deleted' => 'boolean',
    ];

    protected $attributes = [
        'deleted' => 0,
    ];

    public function workgroup()
    {
        return $this->belongsTo(Workgroup::class, 'workgroup_id');
    }

    public function leadUser()
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    public function projectCoordinatorUser()
    {
        return $this->belongsTo(User::class, 'project_coordinator_user_id');
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
