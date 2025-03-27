<?php

namespace App\Http\Controllers\pages;

use App\Events\DelegationAcceptedEvent;
use App\Events\DelegationRejectedEvent;
use App\Http\Controllers\Controller;
use App\Models\Delegation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class ProfileController extends Controller
{
    protected $statusTranslation = [
        'waiting_to_accept' => 'Elfogadásra vár',
        'valid' => 'Érvényes',
        'invalid' => 'Érvénytelen'
    ];

    protected function translateStatus($dbStatus)
    {
        return $this->statusTranslation[$dbStatus] ?? $dbStatus;
    }

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
        $showDeleted = request('show_deleted', false) === 'true';
        
        $query = Delegation::where('original_user_id', Auth::id())
                        ->whereDate('end_date', '>=', now())
                        ->with('delegateUser');
        
        // Include deleted delegations if requested
        if (!$showDeleted) {
            $query->where('deleted', 0);
        }
        
        $delegations = $query->get();
        
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
            
            $dbStatus = $delegation->deleted == 1 ? 'invalid' : ($delegation->status ?: 'waiting_to_accept');
            $displayStatus = $this->translateStatus($dbStatus);
            
            if (!isset($groupedDelegations[$groupKey])) {
                $groupedDelegations[$groupKey] = [
                    'id' => $delegation->id, // Use the first delegation's ID for the group
                    'readable_type' => $readable_type,
                    'delegate_name' => $delegation->delegateUser->name,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'delegate_user_id' => $delegateId,
                    'delegations' => [],
                    'status' => $displayStatus,
                    'db_status' => $dbStatus,
                    'deleted' => $delegation->deleted,
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

    public function getDelegatedToMe()
    {
        $showDeleted = request('show_deleted', false) === 'true';
        
        $query = Delegation::where('delegate_user_id', Auth::id())
                        ->with('originalUser');
        
        // Include deleted delegations if requested
        if (!$showDeleted) {
            $query->where('deleted', 0);
        }
        
        // Get delegations where end date is today or in the future
        $delegations = $query->whereDate('end_date', '>=', now())
                            ->get();
        
        $service = new DelegationService();
        $result = [];
        
        foreach ($delegations as $delegation) {
            // Get readable type from service
            $readableType = $delegation->type;
            $allDelegations = $service->getAllDelegations($delegation->originalUser);
            
            foreach ($allDelegations as $delegationGroup) {
                if (isset($delegationGroup['type']) && $delegationGroup['type'] === $delegation->type) {
                    $readableType = $delegationGroup['readable_name'];
                    break;
                } elseif (is_array($delegationGroup)) {
                    foreach ($delegationGroup as $delegationItem) {
                        if (isset($delegationItem['type']) && $delegationItem['type'] === $delegation->type) {
                            $readableType = $delegationItem['readable_name'];
                            break 2;
                        }
                    }
                }
            }
            
            // Determine group type and readable name
            if (preg_match('/^draft_contract_labor_administrator_\d+$/', $delegation->type)) {
                $readableType = 'Munkaügyi ügyintéző';
            } elseif (preg_match('/^project_coordinator_workgroup_\d+$/', $delegation->type)) {
                $readableType = 'Projektkoordinátor';
            }
            
            $dbStatus = $delegation->deleted == 1 ? 'invalid' : ($delegation->status ?: 'waiting_to_accept');
            $displayStatus = $this->translateStatus($dbStatus);
            
            $result[] = [
                'id' => $delegation->id,
                'original_user_name' => $delegation->originalUser->name,
                'original_user_id' => $delegation->original_user_id,
                'readable_type' => $readableType,
                'start_date' => $delegation->start_date,
                'end_date' => $delegation->end_date,
                'status' => $displayStatus,
                'db_status' => $dbStatus,
                'deleted' => $delegation->deleted
            ];
        }
        
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
        $startDate = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['start_date'])));
        $endDate = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['end_date'])));
        
        $validatedData['start_date'] = $startDate;
        $validatedData['end_date'] = $endDate;
        
        // Check if end date is not more than 2 months after start date
        $maxEndDate = date('Y-m-d', strtotime($startDate . ' + 2 months'));
        if ($endDate > $maxEndDate) {
            return response()->json([
                'message' => 'A helyettesítés vége nem lehet 2 hónapnál későbbi a kezdő dátumtól',
                'errors' => [
                    'end_date' => ['A helyettesítés vége nem lehet 2 hónapnál későbbi a kezdő dátumtól']
                ]
            ], 422);
        }
        
        $types = explode(',', $validatedData['type']);
        $successCount = 0;
        $overlappingDelegations = [];
        $savedDelegations = [];
        
        foreach ($types as $type) {
            // Check for overlapping time periods with the same type and delegate
            $exists = Delegation::where('original_user_id', Auth::id())
                            ->where('delegate_user_id', $validatedData['delegate_user_id'])
                            ->where('type', $type)
                            ->where('deleted', 0)
                            ->where(function ($query) use ($validatedData) {
                                // Overlapping date ranges check:
                                // (start1 <= end2) AND (end1 >= start2)
                                $query->where(function ($q) use ($validatedData) {
                                    $q->where('start_date', '<=', $validatedData['end_date'])
                                    ->where('end_date', '>=', $validatedData['start_date']);
                                });
                            })
                            ->first();

            if ($exists) {
                // Keep track of overlapping delegations
                $overlappingDelegations[] = [
                    'type' => $type,
                    'start_date' => $exists->start_date,
                    'end_date' => $exists->end_date
                ];
                continue; // Skip creating this delegation
            }
            
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
                $savedDelegations[] = $delegation;
            } catch (QueryException $e) {
                // Continue to next item if one fails
                continue;
            }
        }
        
        // Send notification for each successfully created delegation
        if ($successCount > 0) {
            foreach ($savedDelegations as $delegation) {
                event(new \App\Events\DelegationCreatedEvent($delegation));
            }
        }
        
        if ($successCount === 0) {
            if (!empty($overlappingDelegations)) {
                return response()->json([
                    'message' => 'Overlapping delegation record already exists for the same function and delegate.', 
                    'overlapping' => $overlappingDelegations
                ], 409);
            }
            return response()->json(['message' => 'Failed to create delegation records.'], 400);
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

    public function acceptDelegation($id)
    {
        $delegation = Delegation::find($id);
        
        if (!$delegation) {
            return response()->json(['message' => 'Delegation not found'], 404);
        }
        
        // Check if user is the delegate of this delegation
        if ($delegation->delegate_user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }
        
        $delegation->status = 'valid'; // Angol név az adatbázisban
        $delegation->save();
        
        event(new DelegationAcceptedEvent($delegation));
        
        return response()->json(['message' => 'Delegation accepted successfully']);
    }

    public function rejectDelegation($id)
    {
        $delegation = Delegation::find($id);
        
        if (!$delegation) {
            return response()->json(['message' => 'Delegation not found'], 404);
        }
        
        // Check if user is the delegate of this delegation
        if ($delegation->delegate_user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }
        
        $delegation->deleted = 1;
        $delegation->status = 'invalid'; // Angol név az adatbázisban
        $delegation->save();
        
        event(new DelegationRejectedEvent($delegation));
        
        return response()->json(['message' => 'Delegation rejected successfully']);
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