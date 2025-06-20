<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\CostCenterFactory;

/**
 * CostCenter model represents a cost center in the system.
 * It includes attributes for cost center code, name, type, lead user, project coordinator,
 * due date, minimal order limit, and flags for valid employee recruitment and procurement.
 */
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
        'valid_procurement' => 'boolean',
        'deleted' => 'boolean',
    ];

    protected $attributes = [
        'deleted' => 0,
        'valid_employee_recruitment' => 1,
        'valid_procurement' => 1,
    ];

    /**
     * Get the cost center type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(CostCenterType::class, 'type_id');
    }

    /**
     * Get the user who is the lead for the cost center.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadUser()
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    /**
     * Get the project coordinator user for the cost center.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function projectCoordinatorUser()
    {
        return $this->belongsTo(User::class, 'project_coordinator_user_id');
    }

    /**
     * Get the user who created the cost center.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the cost center.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
