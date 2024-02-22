<?php

namespace Modules\EmployeeRecruitment\App\Models;

use App\Models\User;
use App\Models\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\EmployeeRecruitment\Database\Factories\RecruitUserPositionFactory;

class RecruitUserPosition extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return RecruitUserPositionFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recruit_user_position';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = ['user_id', 'position_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'position_id',
        'created_by',
        'updated_by',
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
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Get the user associated with the user position.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the position associated with the user position.
     */
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /**
     * Get the user who created the group.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the group.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
