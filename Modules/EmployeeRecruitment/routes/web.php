<?php

use App\Http\Controllers\files\FileUploadController;
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
// TODO: routok átnevezése, pl: /felveteli-kerelem, /felveteli-kerelem/jovahagyas/{id}, /felveteli-kerelem/visszaallitas/{id}
Route::get('/uj-felveteli-kerelem', [EmployeeRecruitmentController::class, 'index'])->middleware(['auth'])->name('pages-new-process');
Route::get('/folyamat-jovahagyas/{id}', [EmployeeRecruitmentController::class, 'beforeApprove'])->middleware(['auth'])->name('pages-approve-process');
Route::get('/folyamat-visszaallitas/{id}', [EmployeeRecruitmentController::class, 'beforeRestore'])->middleware(['auth'])->name('pages-restore-process');

// API routes
Route::post('/employee-recruitment', [EmployeeRecruitmentController::class, 'store'])->middleware(['auth']);
Route::post('/employee-recruitment/{id}/approve', [EmployeeRecruitmentController::class, 'approve'])->middleware(['auth']);
Route::post('/employee-recruitment/{id}/reject', [EmployeeRecruitmentController::class, 'reject'])->middleware(['auth']);
Route::post('/employee-recruitment/{id}/suspend', [EmployeeRecruitmentController::class, 'suspend'])->middleware(['auth']);
Route::post('/employee-recruitment/{id}/restore', [EmployeeRecruitmentController::class, 'restore'])->middleware(['auth']);

Route::get('/generate-pdf/{id}', [EmployeeRecruitmentController::class, 'generatePDF'])->middleware(['auth'])->name('generate.pdf');

Route::post('/file/upload', [FileUploadController::class, 'upload'])->name('file.upload');
