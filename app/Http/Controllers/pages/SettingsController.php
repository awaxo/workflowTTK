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
        $options = Option::get()->pluck('option_value', 'option_name');

        $workflow_configs = config('workflow');
        $workflows = [];
        foreach ($workflow_configs as $key => $value) {
            $workflows[$key] = __('workflows.' . $key);

            $places = [];
            foreach ($value['places'] as $place) {
                $places[$place] = __('states.' . $place);
            }
        }

        return view('content.pages.settings', compact('options', 'workflows', 'places'));
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

    public function getWorkflowStateDeadline($configName, $state)
    {
        $deadline = Option::where('option_name', $configName . '_' . $state . '_deadline')->first()?->option_value;
        return response()->json(['data' => $deadline]);
    }

    public function deadlineUpdate()
    {
        $data = request()->all();

        Option::updateOrCreate(
            ['option_name' => $data['workflow'] . '_' . $data['state'] . '_deadline'],
            ['option_value' => $data['deadline']]
        );

        return response()->json(['message' => 'Deadline updated']);
    }
}