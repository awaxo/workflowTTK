<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Permission model represents a permission in the system.
 * It extends the Spatie Permission model to include additional functionality if needed.
 */
class Permission extends SpatiePermission
{
    use HasFactory;
}
