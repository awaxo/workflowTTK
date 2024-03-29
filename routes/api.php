<?php

use App\Http\Controllers\pages\PermissionController;
use App\Http\Controllers\pages\RoleController;
use App\Http\Controllers\pages\UserController;
use App\Http\Controllers\pages\WorkflowController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// TODO: authentikációt beállítani!!!
Route::get('/users', [UserController::class, 'getAllUsers']);
Route::get('/users/role/{roleName}', [UserController::class, 'getUsersByRole']);
Route::get('/roles/permission/{permissionName}', [RoleController::class, 'getRolesByPermission']);

Route::get('/roles', [RoleController::class, 'getAllRoles']);

Route::get('/permissions', [PermissionController::class, 'getAllPermissions']);

Route::get('/workflows', [WorkflowController::class, 'getAllWorkflows']);