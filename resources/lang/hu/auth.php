<?php

use App\Services\RoleService;

return [
    'roles' => RoleService::getAllRolesWithDisplayNames(),
];
