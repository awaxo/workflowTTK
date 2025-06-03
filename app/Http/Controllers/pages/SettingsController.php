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

    /**
     * Get all deadlines for a specific workflow
     * 
     * @param string $configName
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWorkflowDeadlines($configName)
    {
        // Get workflow configuration
        $workflow_config = config('workflow.' . $configName);
        if (!$workflow_config) {
            return response()->json(['error' => 'Workflow not found'], 404);
        }

        $deadlines = [];
        
        // Get all states for this workflow
        foreach ($workflow_config['places'] as $state) {
            // Skip states that cannot have deadlines
            if (in_array($state, ['new_request', 'completed', 'rejected', 'suspended'])) {
                continue;
            }

            $optionName = $configName . '_' . $state . '_deadline';
            $deadline = Option::where('option_name', $optionName)->first();
            
            $deadlines[] = [
                'state' => $state,
                'state_name' => __('states.' . $state),
                'deadline' => $deadline ? $deadline->option_value : null,
                'option_name' => $optionName
            ];
        }

        return response()->json(['data' => $deadlines]);
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