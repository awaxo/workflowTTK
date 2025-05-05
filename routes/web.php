<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\pages\CostCenterController;
use App\Http\Controllers\pages\CostCenterTypeController;
use App\Http\Controllers\pages\DashboardController;
use App\Http\Controllers\pages\ExternalAccessController;
use App\Http\Controllers\pages\ExternalPrivilegeController;
use App\Http\Controllers\pages\InstituteController;
use App\Http\Controllers\pages\PermissionController;
use App\Http\Controllers\pages\PositionController;
use App\Http\Controllers\pages\ProfileController;
use App\Http\Controllers\pages\RoleController;
use App\Http\Controllers\pages\SettingsController;
use App\Http\Controllers\pages\UserController;
use App\Http\Controllers\pages\WorkflowController;
use App\Http\Controllers\pages\WorkgroupController;
use Illuminate\Http\Request;

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

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth:dynamic'])->name('dashboard');
Route::get('/intezetek', [InstituteController::class, 'index'])->middleware(['auth:dynamic'])->name('pages-institutes');
Route::get('/segedadat/szerepkorok', [RoleController::class, 'index'])->middleware(['check.wg915'])->name('auxiliary-data-authorizations-roles');
Route::get('/segedadat/jogosultsagok', [PermissionController::class, 'index'])->middleware(['auth:dynamic'])->name('authorizations-permissions');
Route::get('/folyamatok', [WorkflowController::class, 'index'])->middleware(['auth:dynamic'])->name('workflows-all-open');
Route::get('/segedadat/intezetek', [InstituteController::class, 'manage'])->middleware(['check.wg912'])->name('auxiliary-data-institute');
Route::get('/segedadat/csoportok', [WorkgroupController::class, 'manage'])->middleware(['check.wg912'])->name('auxiliary-data-workgroup');
Route::get('/segedadat/hozzaferesi-jogosultsagok', [ExternalAccessController::class, 'manage'])->middleware(['check.wg915'])->name('auxiliary-data-external-access');
Route::get('/segedadat/koltseghelyek', [CostCenterController::class, 'manage'])->middleware(['check.costcenter.viewers'])->name('auxiliary-data-costcenter');
Route::get('/segedadat/koltseghely-tipusok', [CostCenterTypeController::class, 'manage'])->middleware(['check.wg910.wg911'])->name('auxiliary-data-costcenter-type');
Route::get('/segedadat/munkakorok', [PositionController::class, 'manage'])->middleware(['check.wg908'])->name('auxiliary-data-position');
Route::get('/segedadat/felhasznalok', [UserController::class, 'index'])->middleware(['check.wg915'])->name('auxiliary-data-pages-users');
Route::get('/segedadat/kulsos-jogok', [ExternalPrivilegeController::class, 'manage'])->middleware(['check.wg915'])->name('auxiliary-data-external-privilege');
Route::get('/felhasznalok/szerepkor/{role}', [UserController::class, 'indexByRole'])->middleware(['check.wg915'])->name('pages-users-role');
Route::get('/beallitasok', [SettingsController::class, 'index'])->middleware(['check.admin'])->name('settings');
Route::get('/profil', [ProfileController::class, 'index'])->middleware(['auth:dynamic'])->name('profile');

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

// Define the logout route for GET requests
Route::get('/logout', function (Request $request) {
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    app(LoginBasic::class)->logout($request);
    return redirect('/login');
})->name('logout');

// Define a route that returns a fresh CSRF token
Route::get('/refresh-csrf', function() {
    return response()->json(['csrf_token' => csrf_token()]);
});


// API routes
Route::prefix('api')->middleware(['auth:dynamic'])->group(function () {
    Route::get('/permissions', [PermissionController::class, 'getAllPermissions']);

    Route::get('/workflows', [WorkflowController::class, 'getAllWorkflows']);
    Route::get('/workflows/closed', [WorkflowController::class, 'getClosedWorkflows']);
    Route::get('/workflow/{configName}/states', [WorkflowController::class, 'getWorkflowStatesByConfigName']);

    Route::get('/delegations', [ProfileController::class, 'getAllDelegations']);
    Route::get('/delegates/{type}', [ProfileController::class, 'getDelegates']);
    Route::get('/delegations/delegations-to-me', [ProfileController::class, 'getDelegatedToMe']);
    Route::post('/delegation/create', [ProfileController::class, 'create']);
    Route::post('/delegation/{id}/delete', [ProfileController::class, 'delete']);
    Route::post('/delegation/{id}/accept', [ProfileController::class, 'acceptDelegation']);
    Route::post('/delegation/{id}/reject', [ProfileController::class, 'rejectDelegation']);
    Route::post('/notification-settings/update', [ProfileController::class, 'notificationUpdate']);
});

// admin API routes
Route::prefix('api')->middleware(['check.admin'])->group(function () {
    Route::post('/settings/update', [SettingsController::class, 'settingsUpdate']);
    Route::get('/settings/{configName}/state/{state}/deadline', [SettingsController::class, 'getWorkflowStateDeadline']);
    Route::post('/settings/update-deadline', [SettingsController::class, 'deadlineUpdate']);
});

// workgroup 908 API routes
Route::prefix('api')->middleware(['check.wg908'])->group(function () {
    Route::get('/positions', [PositionController::class, 'getAllPositions']);
    Route::post('/position/{id}/delete', [PositionController::class, 'delete']);
    Route::post('/position/{id}/restore', [PositionController::class, 'restore']);
    Route::post('/position/{id}/update', [PositionController::class, 'update']);
    Route::post('/position/create', [PositionController::class, 'create']);
});

// Cost center API routes
Route::prefix('api')->group(function () {
    // Viewers can access the list
    Route::get('/costcenters', [CostCenterController::class, 'getAllCostCenters'])
        ->middleware(['check.costcenter.viewers']);
    
    // Editors can perform modifications
    Route::middleware(['check.costcenter.editors'])->group(function () {
        Route::post('/costcenter/validate-cost-center-code', [CostCenterController::class, 'validateCostCenterCode']);
        Route::post('/costcenter/check-user-in-workgroup', [CostCenterController::class, 'checkUserInWorkgroup']);
        Route::post('/costcenter/check-project-coordinator', [CostCenterController::class, 'checkProjectCoordinator']);
        Route::post('/costcenter/{id}/delete', [CostCenterController::class, 'delete']);
        Route::post('/costcenter/{id}/restore', [CostCenterController::class, 'restore']);
        Route::post('/costcenter/{id}/update', [CostCenterController::class, 'update']);
        Route::post('/costcenter/create', [CostCenterController::class, 'create']);
        Route::post('/costcenter/import', [CostCenterController::class, 'import']);
    });
});

// workgroup 910 or 911 API routes
Route::prefix('api')->middleware(['check.wg910.wg911'])->group(function () {
    Route::get('/costcenter-types', [CostCenterTypeController::class, 'getAllCostCenterTypes']);
    Route::post('/costcenter-type/check-name-unique', [CostCenterTypeController::class, 'checkNameUnique']);
    Route::post('/costcenter-type/{id}/delete', [CostCenterTypeController::class, 'delete']);
    Route::post('/costcenter-type/{id}/restore', [CostCenterTypeController::class, 'restore']);
    Route::post('/costcenter-type/{id}/update', [CostCenterTypeController::class, 'update']);
    Route::post('/costcenter-type/create', [CostCenterTypeController::class, 'create']);
});

// workgroup 912 API routes
Route::prefix('api')->middleware(['check.wg912'])->group(function () {
    Route::get('/workgroups', [WorkgroupController::class, 'getAllWorkgroups']);
    Route::post('/workgroup/check-unique', [WorkgroupController::class, 'checkWorkgroupNumberUnique']);
    Route::post('/workgroup/check-name-unique', [WorkgroupController::class, 'checkWorkgroupNameUnique']);
    Route::post('/workgroup/{id}/delete', [WorkgroupController::class, 'delete']);
    Route::post('/workgroup/{id}/restore', [WorkgroupController::class, 'restore']);
    Route::post('/workgroup/{id}/update', [WorkgroupController::class, 'update']);
    Route::post('/workgroup/create', [WorkgroupController::class, 'create']);

    Route::get('/institutes', [InstituteController::class, 'getAllInstitutes']);
    Route::post('/institute/check-group-level-unique', [InstituteController::class, 'checkGroupLevelUnique']);
    Route::post('/institute/check-name-unique', [InstituteController::class, 'checkNameUnique']);
    Route::post('/institute/check-abbreviation-unique', [InstituteController::class, 'checkAbbreviationUnique']);
    Route::post('/institute/{id}/delete', [InstituteController::class, 'delete']);
    Route::post('/institute/{id}/restore', [InstituteController::class, 'restore']);
    Route::post('/institute/{id}/update', [InstituteController::class, 'update']);
    Route::post('/institute/create', [InstituteController::class, 'create']);
});

// workgroup 915 API routes
Route::prefix('api')->middleware(['check.wg915'])->group(function () {
    Route::get('/roles', [RoleController::class, 'getAllRoles']);

    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::get('/users/role/{roleName}', [UserController::class, 'getUsersByRole']);
    Route::post('/user/check-email-unique', [UserController::class, 'checkEmailUnique']);
    Route::post('/user/check-name-unique', [UserController::class, 'checkNameUnique']);
    Route::post('/user/{id}/delete', [UserController::class, 'delete']);
    Route::post('/user/{id}/restore', [UserController::class, 'restore']);
    Route::post('/user/{id}/update', [UserController::class, 'update']);
    Route::post('/user/create', [UserController::class, 'create']);

    Route::get('/external-access', [ExternalAccessController::class, 'getAllExternalAccess']);
    Route::post('/external-access/check-active-group', [ExternalAccessController::class, 'checkActiveGroup']);
    Route::post('/external-access/{id}/delete', [ExternalAccessController::class, 'delete']);
    Route::post('/external-access/{id}/restore', [ExternalAccessController::class, 'restore']);
    Route::post('/external-access/{id}/update', [ExternalAccessController::class, 'update']);
    Route::post('/external-access/create', [ExternalAccessController::class, 'create']);

    Route::get('/external-privileges', [ExternalPrivilegeController::class, 'getAllExternalPrivileges']);
    Route::post('/external-privilege/create', [ExternalPrivilegeController::class, 'create']);
    Route::post('/external-privilege/{id}/update', [ExternalPrivilegeController::class, 'update']);
    Route::post('/external-privilege/{id}/delete', [ExternalPrivilegeController::class, 'delete']);
});