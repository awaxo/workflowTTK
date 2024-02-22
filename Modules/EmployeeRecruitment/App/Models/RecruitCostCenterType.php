<?php

namespace Modules\EmployeeRecruitment\App\Models;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\EmployeeRecruitment\Database\Factories\RecruitCostCenterTypeFactory;

class RecruitCostCenterType extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return RecruitCostCenterTypeFactory::new();
    }

    protected $table = 'recruit_cost_center_type';
    protected $primaryKey = 'name';
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'tender',
        'financial_approver_role_id',
        'clause_template',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'deleted' => 0,
    ];

    protected $casts = [
        'tender' => 'boolean',
        'deleted' => 'boolean',
    ];

    public $incrementing = false;

    public function financialApproverRole()
    {
        return $this->belongsTo(Role::class, 'financial_approver_role_id');
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
