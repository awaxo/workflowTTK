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
        $originalDelegations = $service->getAllDelegations(Auth::user());
        
        // Group similar delegation types
        $delegations = $this->groupSimilarDelegations($originalDelegations);
        
        $approval_notification = json_decode(Auth::user()->notification_preferences)->email?->recruitment->approval_notification;

        return view('content.pages.profile', compact('delegations', 'approval_notification'));
    }
    
    /**
     * Group similar delegation types into single items
     * 
     * @param array $delegations The original delegations array
     * @return array The modified delegations array with similar types grouped
     */
    private function groupSimilarDelegations($delegations)
    {
        $result = [];
        $laborAdmins = [];
        $projectCoordinators = [];
        
        foreach ($delegations as $key => $delegation) {
            if (isset($delegation['type'])) {
                // Check for labor administrators
                if (preg_match('/^draft_contract_labor_administrator_\d+$/', $delegation['type'])) {
                    $laborAdmins[] = $delegation;
                } 
                // Check for project coordinators
                else if (preg_match('/^project_coordinator_workgroup_\d+$/', $delegation['type'])) {
                    $projectCoordinators[] = $delegation;
                }
                // Other delegation types remain unchanged
                else {
                    $result[] = $delegation;
                }
            } else {
                // Handle array delegations (nested)
                $result[] = $delegation;
            }
        }
        
        // Add grouped labor admins if any
        if (!empty($laborAdmins)) {
            $types = array_column($laborAdmins, 'type');
            $result[] = [
                'type' => implode(',', $types),
                'readable_name' => 'Munkaügyi ügyintéző',
                'original_delegations' => $laborAdmins
            ];
        }
        
        // Add grouped project coordinators if any
        if (!empty($projectCoordinators)) {
            $types = array_column($projectCoordinators, 'type');
            $result[] = [
                'type' => implode(',', $types),
                'readable_name' => 'Projektkoordinátor',
                'original_delegations' => $projectCoordinators
            ];
        }
        
        return $result;
    }

    public function getAllDelegations()
    {
        $delegations = Delegation::where('original_user_id', Auth::id())
                        ->where('deleted', 0)
                        ->whereDate('end_date', '>=', now())
                        ->with('delegateUser')
                        ->get();
        
        // Group delegations by delegate, type pattern, dates
        $groupedDelegations = [];
        $laborAdminPattern = '/^draft_contract_labor_administrator_\d+$/';
        $projectCoordinatorPattern = '/^project_coordinator_workgroup_\d+$/';
        
        foreach ($delegations as $delegation) {
            $delegateId = $delegation->delegate_user_id;
            $startDate = $delegation->start_date;
            $endDate = $delegation->end_date;
            
            // Determine the group type and readable name
            $groupType = $delegation->type;
            $readable_type = $delegation->type;
            
            // Get readable type from service
            $service = new DelegationService();
            $allDelegations = $service->getAllDelegations(Auth::user());

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
            
            // Determine group key based on type patterns
            if (preg_match($laborAdminPattern, $delegation->type)) {
                $groupType = 'labor_admin';
                $readable_type = 'Munkaügyi ügyintéző';
            } elseif (preg_match($projectCoordinatorPattern, $delegation->type)) {
                $groupType = 'project_coordinator';
                $readable_type = 'Projektkoordinátor';
            }
            
            // Create a unique key for the group
            $groupKey = $delegateId . '|' . $groupType . '|' . $startDate . '|' . $endDate;
            
            if (!isset($groupedDelegations[$groupKey])) {
                $groupedDelegations[$groupKey] = [
                    'id' => $delegation->id, // Use the first delegation's ID for the group
                    'readable_type' => $readable_type,
                    'delegate_name' => $delegation->delegateUser->name,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'delegate_user_id' => $delegateId,
                    'delegations' => []
                ];
            }
            
            // Add this delegation to the group
            $groupedDelegations[$groupKey]['delegations'][] = $delegation->id;
            
            // If this ID is smaller, use it as the group's ID (for consistency)
            if ($delegation->id < $groupedDelegations[$groupKey]['id']) {
                $groupedDelegations[$groupKey]['id'] = $delegation->id;
            }
        }
        
        // Convert to indexed array for response
        $result = array_values($groupedDelegations);
        
        return response()->json(['data' => $result]);
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
        
        $types = explode(',', $validatedData['type']);
        $successCount = 0;
        
        foreach ($types as $type) {
            // Check for existing record
            $exists = Delegation::where('original_user_id', Auth::id())
                                ->where('delegate_user_id', $validatedData['delegate_user_id'])
                                ->where('type', $type)
                                ->where('start_date', $validatedData['start_date'])
                                ->where('end_date', $validatedData['end_date'])
                                ->where('deleted', 0)
                                ->exists();

            if (!$exists) {
                $delegation = new Delegation();
                $delegation->fill([
                    'delegate_user_id' => $validatedData['delegate_user_id'],
                    'type' => $type,
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                ]);
                $delegation->original_user_id = Auth::id();
                $delegation->created_by = Auth::id();
                $delegation->updated_by = Auth::id();

                try {
                    $delegation->save();
                    $successCount++;
                } catch (QueryException $e) {
                    // Continue to next item if one fails
                    continue;
                }
            }
        }
        
        if ($successCount === 0) {
            return response()->json(['message' => 'A similar delegation record already exists.'], 409);
        }

        return response()->json(['message' => 'Delegation added successfully']);
    }

    public function delete($id)
    {
        $delegation = Delegation::find($id);
        
        if (!$delegation) {
            return response()->json(['message' => 'Delegation not found'], 404);
        }
        
        // Check if this is part of a grouped delegation (labor admin or project coordinator)
        $isLaborAdmin = preg_match('/^draft_contract_labor_administrator_\d+$/', $delegation->type);
        $isProjectCoordinator = preg_match('/^project_coordinator_workgroup_\d+$/', $delegation->type);
        
        // If this is a single delegation (not part of a group), just delete it
        if (!$isLaborAdmin && !$isProjectCoordinator) {
            $delegation->deleted = 1;
            $delegation->save();
            return response()->json(['message' => 'Delegation deleted successfully']);
        }
        
        // For grouped delegations, we'll let the client handle the deletion of all related entries
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
        // Check if we have multiple types
        if (strpos($type, ',') !== false) {
            $types = explode(',', $type);
            // Just use the first type for fetching delegates
            // This assumes delegates are the same for all grouped types
            $type = $types[0];
        }
        
        $delegates = User::nonAdmin()->find(Auth::id())->getDelegates($type);
        
        // If the result is an array, remove duplicates based on user ID
        if (is_array($delegates)) {
            // Use a temporary array to track unique IDs
            $uniqueDelegates = [];
            $uniqueIds = [];
            
            foreach ($delegates as $delegate) {
                if (!in_array($delegate['id'], $uniqueIds)) {
                    $uniqueIds[] = $delegate['id'];
                    $uniqueDelegates[] = $delegate;
                }
            }
            
            return $uniqueDelegates;
        }
        
        // If it's a single user or another format, return as is
        return $delegates;
    }
}