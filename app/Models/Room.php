<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'wf_room';

    public $timestamps = false;

    /**
     * Nincs elsődleges kulcs a táblában, ez a beállítás megakadályozza,
     * hogy a Laravel automatikusan keressen egyet
     */
    public $incrementing = false;

    /**
     * Jelezzük, hogy nincs elsődleges kulcs
     */
    protected $primaryKey = null;

    protected $fillable = [
        'workgroup_number',
        'room_number',
    ];
}