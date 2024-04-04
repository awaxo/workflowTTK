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

Route::get('/uj-felveteli-kerelem', [EmployeeRecruitmentController::class, 'index'])->middleware(['auth'])->name('pages-new-process');
Route::get('/folyamat-jovahagyas/{id}', [EmployeeRecruitmentController::class, 'beforeApprove'])->middleware(['auth'])->name('pages-approve-process');

// API routes
Route::post('/employee-recruitment', [EmployeeRecruitmentController::class, 'store'])->middleware(['auth']);
Route::post('/file/upload', [FileUploadController::class, 'upload'])->name('file.upload');

Route::post('/employee-recruitment/{id}/approve', [EmployeeRecruitmentController::class, 'approve'])->middleware(['auth']);
