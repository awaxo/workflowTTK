<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delegation extends Model
{
    use HasFactory;

    protected $table = 'wf_delegation';

    protected $fillable = [
        'original_user_id',
        'delegate_user_id',
        'type',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deleted' => 'boolean',
    ];

    protected $attributes = [
        'deleted' => 0,
    ];

    public function originalUser()
    {
        return $this->belongsTo(User::class, 'original_user_id')->withoutGlobalScopes();
    }

    public function delegateUser()
    {
        return $this->belongsTo(User::class, 'delegate_user_id')->withoutGlobalScopes();
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