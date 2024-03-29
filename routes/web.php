<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\pages\DashboardController;
use App\Http\Controllers\pages\InstituteController;
use App\Http\Controllers\pages\PermissionController;
use App\Http\Controllers\pages\RoleController;
use App\Http\Controllers\pages\UserController;
use App\Http\Controllers\pages\WorkflowController;

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
Route::get('/folyamatok', [WorkflowController::class, 'index'])->middleware(['auth'])->name('pages-workflows');

// locale
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// pages
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/felhasznalok', [UserController::class, 'index'])->middleware(['auth'])->name('pages-users');
Route::get('/felhasznalok/szerepkor/{role}', [UserController::class, 'indexByRole'])
    ->middleware(['auth'])
    ->name('pages-users-role');

Route::get('/szerepkorok/jogosultsag/{permission}', [RoleController::class, 'indexByPermission'])
    ->middleware(['auth'])
    ->name('pages-roles-permission');

// Display the login form
Route::get('/login', [LoginBasic::class, 'index'])->name('login');

// Handle login submission
Route::post('/login', [LoginBasic::class, 'authenticate']);

// Define the logout route
Route::post('/logout', [LoginBasic::class, 'logout'])->name('logout');

//Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
