<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\CostCenterFactory;

class CostCenter extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return CostCenterFactory::new();
    }

    protected $table = 'wf_cost_center';

    protected $fillable = [
        'cost_center_code',
        'name',
        'type_id',
        'lead_user_id',
        'project_coordinator_user_id',
        'due_date',
        'minimal_order_limit',
        'valid_employee_recruitment',
        'valid_procurement',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'minimal_order_limit' => 'decimal:2',
        'valid_employee_recruitment' => 'boolean',
        'deleted' => 'boolean',
    ];

    protected $attributes = [
        'deleted' => 0,
        'valid_employee_recruitment' => 1,
        'valid_procurement' => 1,
    ];

    public function type()
    {
        return $this->belongsTo(CostCenterType::class, 'type_id');
    }

    public function leadUser()
    {
        return $this->belongsTo(User::class, 'lead_user_id')->withoutGlobalScopes();
    }

    public function projectCoordinatorUser()
    {
        return $this->belongsTo(User::class, 'project_coordinator_user_id')->withoutGlobalScopes();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScopes();
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by')->withoutGlobalScopes();
    }
}
