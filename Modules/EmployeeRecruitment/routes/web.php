<?php

use App\Http\Controllers\files\FileUploadController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Modules\EmployeeRecruitment\App\Http\Controllers\pages\EmployeeRecruitmentController;

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
Route::get('/felveteli-kerelem/uj', [EmployeeRecruitmentController::class, 'index'])->middleware(['auth'])->name('workflows-employee-recruitment-new');
Route::get('/felveteli-kerelem/nyitott', [EmployeeRecruitmentController::class, 'opened'])->middleware(['auth'])->name('workflows-employee-recruitment-opened');
Route::get('/felveteli-kerelem/lezart', [EmployeeRecruitmentController::class, 'closed'])->middleware(['auth'])->name('workflows-employee-recruitment-closed');
Route::get('/folyamat/jovahagyas/{id}', [EmployeeRecruitmentController::class, 'beforeApprove'])->middleware(['auth'])->name('pages-approve-process');
Route::get('/folyamat/visszaallitas/{id}', [EmployeeRecruitmentController::class, 'beforeRestore'])->middleware(['auth'])->name('pages-restore-process');
Route::get('/folyamat/megtekintes/{id}', [EmployeeRecruitmentController::class, 'view'])->middleware(['auth'])->name('pages-restore-process');

// API routes
Route::post('/employee-recruitment', [EmployeeRecruitmentController::class, 'store'])->middleware(['auth']);
Route::post('/employee-recruitment/{id}/approve', [EmployeeRecruitmentController::class, 'approve'])->middleware(['auth']);
Route::post('/employee-recruitment/{id}/reject', [EmployeeRecruitmentController::class, 'reject'])->middleware(['auth']);
Route::post('/employee-recruitment/{id}/suspend', [EmployeeRecruitmentController::class, 'suspend'])->middleware(['auth']);
Route::post('/employee-recruitment/{id}/restore', [EmployeeRecruitmentController::class, 'restore'])->middleware(['auth']);

Route::get('/employee-recruitment/opened', [EmployeeRecruitmentController::class, 'getAllOpened'])->middleware(['auth']);
Route::get('/employee-recruitment/closed', [EmployeeRecruitmentController::class, 'getAllClosed'])->middleware(['auth']);

Route::get('/generate-pdf/{id}', [EmployeeRecruitmentController::class, 'generatePDF'])->middleware(['auth'])->name('generate.pdf');

Route::post('/file/upload', [FileUploadController::class, 'upload'])->name('file.upload');
Route::get('/dokumentumok/{filename}', function ($filename) {
    $path = storage_path('app/public/uploads/' . $filename);
    if (!File::exists($path)) {
        abort(404);
    }
    return response()->file($path);
});
