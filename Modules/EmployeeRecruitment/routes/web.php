<?php

use App\Http\Controllers\files\FileUploadController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Modules\EmployeeRecruitment\App\Http\Controllers\pages\EmployeeRecruitmentController;
use Modules\EmployeeRecruitment\App\Middleware\CheckSecretary;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Page routes
Route::get('/hr/felveteli-kerelem/uj', [EmployeeRecruitmentController::class, 'index'])->middleware([CheckSecretary::class])->name('workflows-employee-recruitment-new');
Route::get('/hr/felveteli-kerelem', [EmployeeRecruitmentController::class, 'opened'])->middleware(['auth:dynamic'])->name('workflows-employee-recruitment-opened');
Route::get('/folyamat/jovahagyas/{id}', [EmployeeRecruitmentController::class, 'beforeApprove'])->middleware(['auth:dynamic'])->name('pages-approve-process');
Route::get('/folyamat/visszaallitas/{id}', [EmployeeRecruitmentController::class, 'beforeRestore'])->middleware(['auth:dynamic'])->name('pages-restore-process');
Route::get('/folyamat/megtekintes/{id}', [EmployeeRecruitmentController::class, 'view'])->middleware(['auth:dynamic'])->name('pages-restore-process');

// API routes
Route::post('/employee-recruitment', [EmployeeRecruitmentController::class, 'store'])->middleware([CheckSecretary::class]);
Route::post('/employee-recruitment/{id}/approve', [EmployeeRecruitmentController::class, 'approve'])->middleware(['auth:dynamic']);
Route::post('/employee-recruitment/{id}/reject', [EmployeeRecruitmentController::class, 'reject'])->middleware(['auth:dynamic']);
Route::post('/employee-recruitment/{id}/suspend', [EmployeeRecruitmentController::class, 'suspend'])->middleware(['auth:dynamic']);
Route::post('/employee-recruitment/{id}/restore', [EmployeeRecruitmentController::class, 'restore'])->middleware(['auth:dynamic']);
Route::post('/employee-recruitment/{id}/delete', [EmployeeRecruitmentController::class, 'delete'])->middleware(['auth:dynamic']);

Route::get('/employee-recruitment/opened', [EmployeeRecruitmentController::class, 'getAll'])->middleware(['auth:dynamic']);
Route::get('/employee-recruitment/closed', [EmployeeRecruitmentController::class, 'getAllClosed'])->middleware(['auth:dynamic']);

Route::get('/generate-pdf/{id}', [EmployeeRecruitmentController::class, 'generatePDF'])->middleware(['auth:dynamic'])->name('generate.pdf');
Route::get('/generate-medical-pdf/{id}', [EmployeeRecruitmentController::class, 'generateMedicalPDF'])->middleware(['auth:dynamic'])->name('generateMedical.pdf');

Route::post('/file/upload', [FileUploadController::class, 'upload'])->name('file.upload');
Route::post('/file/delete', [FileUploadController::class, 'delete'])->name('file.delete');
Route::get('/dokumentumok/{filename}', function ($filename) {
    $path = storage_path('app/public/uploads/' . $filename);
    if (!File::exists($path)) {
        abort(404);
    }
    return response()->file($path);
});
