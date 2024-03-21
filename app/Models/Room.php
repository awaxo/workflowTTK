<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'wf_room';

    public $timestamps = false;

    protected $fillable = [
        'workgroup_number',
        'room_number',
    ];
}
