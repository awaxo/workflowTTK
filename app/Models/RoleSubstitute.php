<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleSubstitute extends Model
{
    use HasFactory;

    protected $table = 'wf_role_substitute';

    protected $fillable = [
        'original_user_id',
        'substitute_user_id',
        'role_id',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function originalUser()
    {
        return $this->belongsTo(User::class, 'original_user_id');
    }

    public function substituteUser()
    {
        return $this->belongsTo(User::class, 'substitute_user_id');
    }

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