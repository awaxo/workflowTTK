<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());
        if (!$user->hasRole('adminisztrator')) {
            return view('content.pages.misc-not-authorized');
        }

        $options = Option::get()->pluck('option_value', 'option_name');
        return view('content.pages.settings', compact('options'));
    }

    public function settingsUpdate()
    {
        $user = User::find(Auth::id());
        if (!$user->hasRole('adminisztrator')) {
            return view('content.pages.misc-not-authorized');
        }

        $options = request()->all();

        foreach ($options['settings'] as $key => $value) {
            Option::updateOrCreate(
                ['option_name' => $key],
                ['option_value' => $value]
            );
        }

        return response()->json(['message' => 'Settings updated']);
    }
}