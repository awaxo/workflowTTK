<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'wf_room';

    protected $fillable = [
        'workgroup_id',
        'room_number',
        'created_by',
        'updated_by',
    ];

    public function workgroup()
    {
        return $this->belongsTo(Workgroup::class, 'workgroup_id');
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
