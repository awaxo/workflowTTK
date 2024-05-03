<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\pages\CostCenterController;
use App\Http\Controllers\pages\CostCenterTypeController;
use App\Http\Controllers\pages\DashboardController;
use App\Http\Controllers\pages\ExternalAccessController;
use App\Http\Controllers\pages\InstituteController;
use App\Http\Controllers\pages\PermissionController;
use App\Http\Controllers\pages\PositionController;
use App\Http\Controllers\pages\ProfileController;
use App\Http\Controllers\pages\RoleController;
use App\Http\Controllers\pages\SettingsController;
use App\Http\Controllers\pages\UserController;
use App\Http\Controllers\pages\WorkflowController;
use App\Http\Controllers\pages\WorkgroupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::get('/intezetek', [InstituteController::class, 'index'])->middleware(['auth'])->name('pages-institutes');
Route::get('/szerepkorok', [RoleController::class, 'index'])->middleware(['auth'])->name('authorizations-roles');
Route::get('/jogosultsagok', [PermissionController::class, 'index'])->middleware(['auth'])->name('authorizations-permissions');
Route::get('/folyamatok/nyitott', [WorkflowController::class, 'index'])->middleware(['auth'])->name('workflows-all-open');
Route::get('/folyamatok/lezart', [WorkflowController::class, 'closed'])->middleware(['auth'])->name('workflows-all-closed');
Route::get('/segedadat/intezetek', [InstituteController::class, 'manage'])->middleware(['auth'])->name('auxiliary-data-institute');
Route::get('/segedadat/csoportok', [WorkgroupController::class, 'manage'])->middleware(['auth'])->name('auxiliary-data-workgroup');
Route::get('/segedadat/hozzaferesi-jogosultsagok', [ExternalAccessController::class, 'manage'])->middleware(['auth'])->name('auxiliary-data-external-access');
Route::get('/segedadat/koltseghelyek', [CostCenterController::class, 'manage'])->middleware(['auth'])->name('auxiliary-data-costcenter');
Route::get('/segedadat/koltseghely-tipusok', [CostCenterTypeController::class, 'manage'])->middleware(['auth'])->name('auxiliary-data-costcenter-type');
Route::get('/segedadat/munkakorok', [PositionController::class, 'manage'])->middleware(['auth'])->name('auxiliary-data-position');
Route::get('/felhasznalok', [UserController::class, 'index'])->middleware(['auth'])->name('pages-users');
Route::get('/felhasznalok/szerepkor/{role}', [UserController::class, 'indexByRole'])->middleware(['auth'])->name('pages-users-role');
Route::get('/beallitasok', [SettingsController::class, 'index'])->middleware(['auth'])->name('settings');
Route::get('/profil', [ProfileController::class, 'index'])->middleware(['auth'])->name('profile');

// locale
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// pages
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

Route::get('/szerepkorok/jogosultsag/{permission}', [RoleController::class, 'indexByPermission'])
    ->middleware(['auth'])
    ->name('pages-roles-permission');

// Display the login form
Route::get('/login', [LoginBasic::class, 'index'])->name('login');

// Handle authentication
Route::post('/login', [LoginBasic::class, 'authenticate']);

// Define the logout route
Route::post('/logout', [LoginBasic::class, 'logout'])->name('logout');


// API routes
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/roles', [RoleController::class, 'getAllRoles']);

    Route::get('/permissions', [PermissionController::class, 'getAllPermissions']);

    Route::get('/workflows', [WorkflowController::class, 'getAllWorkflows']);
    Route::get('/workflows/closed', [WorkflowController::class, 'getClosedWorkflows']);

    Route::get('/institutes', [InstituteController::class, 'getAllInstitutes']);
    Route::post('/institute/{id}/delete', [InstituteController::class, 'delete']);
    Route::post('/institute/{id}/restore', [InstituteController::class, 'restore']);
    Route::post('/institute/{id}/update', [InstituteController::class, 'update']);
    Route::post('/institute/create', [InstituteController::class, 'create']);

    Route::get('/workgroups', [WorkgroupController::class, 'getAllWorkgroups']);
    Route::post('/workgroup/{id}/delete', [WorkgroupController::class, 'delete']);
    Route::post('/workgroup/{id}/restore', [WorkgroupController::class, 'restore']);
    Route::post('/workgroup/{id}/update', [WorkgroupController::class, 'update']);
    Route::post('/workgroup/create', [WorkgroupController::class, 'create']);

    Route::get('/external-access', [ExternalAccessController::class, 'getAllExternalAccess']);
    Route::post('/external-access/{id}/delete', [ExternalAccessController::class, 'delete']);
    Route::post('/external-access/{id}/restore', [ExternalAccessController::class, 'restore']);
    Route::post('/external-access/{id}/update', [ExternalAccessController::class, 'update']);
    Route::post('/external-access/create', [ExternalAccessController::class, 'create']);

    Route::get('/costcenters', [CostCenterController::class, 'getAllCostCenters']);
    Route::post('/costcenter/{id}/delete', [CostCenterController::class, 'delete']);
    Route::post('/costcenter/{id}/restore', [CostCenterController::class, 'restore']);
    Route::post('/costcenter/{id}/update', [CostCenterController::class, 'update']);
    Route::post('/costcenter/create', [CostCenterController::class, 'create']);

    Route::get('/costcenter-types', [CostCenterTypeController::class, 'getAllCostCenterTypes']);
    Route::post('/costcenter-type/{id}/delete', [CostCenterTypeController::class, 'delete']);
    Route::post('/costcenter-type/{id}/restore', [CostCenterTypeController::class, 'restore']);
    Route::post('/costcenter-type/{id}/update', [CostCenterTypeController::class, 'update']);
    Route::post('/costcenter-type/create', [CostCenterTypeController::class, 'create']);

    Route::get('/positions', [PositionController::class, 'getAllPositions']);
    Route::post('/position/{id}/delete', [PositionController::class, 'delete']);
    Route::post('/position/{id}/restore', [PositionController::class, 'restore']);
    Route::post('/position/{id}/update', [PositionController::class, 'update']);
    Route::post('/position/create', [PositionController::class, 'create']);

    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::get('/users/role/{roleName}', [UserController::class, 'getUsersByRole']);
    Route::post('/user/{id}/delete', [UserController::class, 'delete']);
    Route::post('/user/{id}/restore', [UserController::class, 'restore']);
    Route::post('/user/{id}/update', [UserController::class, 'update']);
    Route::post('/user/create', [UserController::class, 'create']);

    Route::get('/delegations', [ProfileController::class, 'getAllDelegations']);
    Route::post('/delegation/create', [ProfileController::class, 'createDelegation']);
});
