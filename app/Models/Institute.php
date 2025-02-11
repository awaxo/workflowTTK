<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    use HasFactory;

    protected $table = 'wf_institute';

    protected $fillable = [
        'group_level',
        'name',
        'abbreviation',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];

    protected $attributes = [
        'deleted' => 0,
    ];

    /**
     * Get the user who created the labor administrator.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScopes();
    }

    /**
     * Get the user who last updated the labor administrator.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by')->withoutGlobalScopes();
    }

    // Additional methods related to this model can be added here
}
