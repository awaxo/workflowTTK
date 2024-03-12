<?php

namespace App\Models;

use App\Models\User;
use App\Models\Role;
use App\Models\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\RolePositionFactory;

class RolePosition extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return RolePositionFactory::new();
    }

    protected $table = 'wf_role_position';
    protected $primaryKey = ['role_id', 'position_id'];

    protected $fillable = [
        'role_id',
        'position_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];

    protected $attributes = [
        'deleted' => 0,
    ];

    public $incrementing = false;

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
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
