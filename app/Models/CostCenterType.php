<?php

namespace App\Models;

use App\Models\User;
use App\Models\WfRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\CostCenterTypeFactory;

/**
 * CostCenterType model represents a type of cost center in the system.
 * It includes attributes for the name, tender flag, financial countersign,
 * clause template, and user references for creation and updates.
 */
class CostCenterType extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return CostCenterTypeFactory::new();
    }

    protected $table = 'wf_cost_center_type';

    protected $fillable = [
        'name',
        'tender',
        'financial_countersign',
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

    /**
     * Get the user who created this cost center type.
     *
     * @return string
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this cost center type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the costcenters associated with this cost center type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function costCenters()
    {
        return $this->hasMany(CostCenter::class, 'type_id')->where('deleted', 0);
    }
}
