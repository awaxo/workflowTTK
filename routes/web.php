<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\pages\Dashboard;
use App\Http\Controllers\pages\Process;
use App\Http\Controllers\pages\UserController;

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

// Main Page Route
//Route::get('/', [HomePage::class, 'index'])->name('pages-home');

// Dashboard
Route::get('/dashboard', [Dashboard::class, 'index'])->middleware(['auth'])->name('dashboard');

// locale
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// pages
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/felhasznalok', [UserController::class, 'index'])->middleware(['auth'])->name('pages-list-users');

// Display the login form
Route::get('/login', [LoginBasic::class, 'index'])->name('login');

// Handle login submission
Route::post('/login', [LoginBasic::class, 'authenticate']);

// Define the logout route
Route::post('/logout', [LoginBasic::class, 'logout'])->name('logout');

//Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
