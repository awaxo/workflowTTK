<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Delegation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class ProfileController extends Controller
{
    public function index()
    {
        $service = new DelegationService();
        $delegations = $service->getAllDelegations(Auth::user());
        $approval_notification = json_decode(Auth::user()->notification_preferences)->email?->recruitment->approval_notification;

        return view('content.pages.profile', compact('delegations', 'approval_notification'));
    }

    public function getAllDelegations()
    {
        $delegations = Delegation::where('original_user_id', Auth::id())
                        ->where('deleted', 0)
                        ->whereDate('end_date', '>=', now())
                        ->with('delegateUser')
                        ->get()
                        ->map(function ($delegation) {
                            $service = new DelegationService();
                            $allDelegations = $service->getAllDelegations(Auth::user());

                            $readable_type = $delegation->type; // Default to delegation type
                            foreach ($allDelegations as $delegationGroup) {
                                // Check if delegationGroup is associative or indexed
                                if (isset($delegationGroup['type']) && $delegationGroup['type'] === $delegation->type) {
                                    $readable_type = $delegationGroup['readable_name'];
                                    break; // Found the matching type, break the loop
                                } elseif (is_array($delegationGroup)) {
                                    foreach ($delegationGroup as $delegationItem) {
                                        if (isset($delegationItem['type']) && $delegationItem['type'] === $delegation->type) {
                                            $readable_type = $delegationItem['readable_name'];
                                            break 2; // Found the matching type, break both loops
                                        }
                                    }
                                }
                            }

                            return [
                                'id' => $delegation->id,
                                'readable_type' => $readable_type,
                                'delegate_name' => $delegation->delegateUser->name,
                                'start_date' => $delegation->start_date,
                                'end_date' => $delegation->end_date,
                            ];
                        });

        return response()->json(['data' => $delegations]);
    }

    public function create() 
    {
        $validatedData = request()->validate([
            'type' => 'required',
            'delegate_user_id' => 'required',
            'start_date' => 'required|date_format:Y.m.d|before_or_equal:end_date',
            'end_date' => 'required|date_format:Y.m.d|after_or_equal:start_date',
        ], [
            'type.required' => 'Kérjük válassz helyettesített funkciót',
            'delegate_user_id.required' => 'Kérjük válassz helyettesítőt',
            'start_date.required' => 'Kérjük add meg a helyettesítés kezdetét',
            'start_date.date' => 'Kérjük, valós formában add meg a dátumot: YYYY.MM.DD',
            'start_date.before_or_equal' => 'The start date must be earlier than the end date',
            'end_date.required' => 'Kérjük add meg a helyettesítés végét',
            'end_date.date' => 'Kérjük, valós formában add meg a dátumot: YYYY.MM.DD',
            'end_date.after_or_equal' => 'A helyettesítés vége nem lehet korábban a helyettesítés kezdténél',
        ]);

        // Convert the dates to the 'Y-m-d' format
        $validatedData['start_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['start_date'])));
        $validatedData['end_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['end_date'])));

        // Check for existing record
        $exists = Delegation::where('original_user_id', Auth::id())
                            ->where('delegate_user_id', $validatedData['delegate_user_id'])
                            ->where('type', $validatedData['type'])
                            ->where('start_date', $validatedData['start_date'])
                            ->where('end_date', $validatedData['end_date'])
                            ->where('deleted', 0)
                            ->exists();

        if ($exists) {
            return response()->json(['message' => 'A similar delegation record already exists.'], 409);
        }

        $delegation = new Delegation();
        $delegation->fill($validatedData);
        $delegation->original_user_id = Auth::id();
        $delegation->start_date = $validatedData['start_date'];
        $delegation->end_date = $validatedData['end_date'];
        $delegation->created_by = Auth::id();
        $delegation->updated_by = Auth::id();

        try {
            $delegation->save();
        } catch (QueryException $e) {
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }

        return response()->json(['message' => 'Delegation added successfully']);
    }

    public function delete($id)
    {
        $delegation = Delegation::find($id);
        $delegation->deleted = 1;
        $delegation->save();
        return response()->json(['message' => 'Delegation deleted successfully']);
    }

    public function notificationUpdate()
    {
        $notification_preferences = json_decode(Auth::user()?->notification_preferences);
        if ($notification_preferences === null) {
            $notification_preferences = (object) [
                'email' => (object) [
                    'recruitment' => (object) [
                        'approval_notification' => null
                    ]
                ]
            ];
        }
        $notification_preferences->email->recruitment->approval_notification = request('approval_notification');
        $user = User::find(Auth::id());
        $user->notification_preferences = json_encode($notification_preferences);
        $user->save();

        return response()->json(['message' => 'Notification settings updated successfully']);
    }

    public function getDelegates($type)
    {
        return User::find(Auth::id())->getDelegates($type);
    }
}