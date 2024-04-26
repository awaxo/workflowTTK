<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());
        if (!$user->hasRole('adminisztrator')) {
            return view('content.pages.misc-not-authorized');
        }

        return view('content.pages.settings');
    }
}