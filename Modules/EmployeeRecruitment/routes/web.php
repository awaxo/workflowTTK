<?php

use Illuminate\Support\Facades\Route;
use Modules\EmployeeRecruitment\App\Http\Controllers\EmployeeRecruitmentController;

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

Route::group([], function () {
    Route::resource('employeerecruitment', EmployeeRecruitmentController::class)->names('employeerecruitment');
});
