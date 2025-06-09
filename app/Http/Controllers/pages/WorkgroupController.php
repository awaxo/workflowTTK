<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workgroup;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * WorkgroupController handles the management of workgroups,
 * including creating, updating, deleting, and restoring workgroups.
 *
 * This controller is responsible for rendering the workgroups page,
 * fetching all workgroups, checking uniqueness of workgroup numbers and names,
 * and managing workgroup states.
 */
class WorkgroupController extends Controller
{
    /**
     * Display the workgroups management page.
     *
     * @return \Illuminate\View\View
     */
    public function manage()
    {
        $users = User::nonAdmin()
            ->where('deleted', 0)
            ->whereHas('roles')
            ->get();
        $labor_administrators = User::nonAdmin()
            ->where('deleted', 0)
            ->whereHas('roles')
            ->whereHas('workgroup', function ($query) {
            $query->where('workgroup_number', 908);
            })
            ->get();

        return view('content.pages.workgroups', compact('users', 'labor_administrators'));
    }

    /**
     * Get all workgroups for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllWorkgroups()
    {
        $workgroups = Workgroup::all()->map(function ($workgroup) {
            return [
                'id' => $workgroup->id,
                'name' => $workgroup->name,
                'workgroup_number' => $workgroup->workgroup_number,
                'leader_id' => $workgroup->leader_id,
                'leader_name' => $workgroup->leader->name,
                'labor_administrator' => $workgroup->labor_administrator,
                'labor_administrator_name' => optional($workgroup->laborAdministrator)->name ?? '',
                'deleted' => $workgroup->deleted,
                'created_at' => $workgroup->created_at,
                'created_by_name' => optional($workgroup->createdBy)->name ?? 'Technikai felhasználó',
                'updated_at' => $workgroup->updated_at,
                'updated_by_name' => optional($workgroup->updatedBy)->name ?? 'Technikai felhasználó'
            ];
        });
        return response()->json(['data' => $workgroups]);
    }

    /**
     * Check if the workgroup number is unique.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkWorkgroupNumberUnique()
    {
        $workgroupNumber = request()->input('workgroup_number');
        $workgroupId = request()->input('workgroup_id');
        
        $query = Workgroup::where('workgroup_number', $workgroupNumber)
            ->where('deleted', 0);
        
        if ($workgroupId) {
            $query->where('id', '!=', $workgroupId);
        }
        
        $exists = $query->exists();
        
        return response()->json(['valid' => !$exists]);
    }
    
    /**
     * Check if the workgroup name is unique.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkWorkgroupNameUnique()
    {
        $name = request()->input('name');
        $workgroupId = request()->input('workgroup_id');
        
        $query = Workgroup::where('name', $name)
            ->where('deleted', 0);
        
        if ($workgroupId) {
            $query->where('id', '!=', $workgroupId);
        }
        
        $exists = $query->exists();
        
        return response()->json(['valid' => !$exists]);
    }

    /**
     * Delete a workgroup by setting its deleted flag.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $workgroup = Workgroup::find($id);
        $workgroup->deleted = 1;
        $workgroup->save();

        event(new ModelChangedEvent($workgroup, 'deleted'));

        return response()->json(['success' => 'Workgroup deleted successfully']);
    }

    /**
     * Restore a deleted workgroup by resetting its deleted flag.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $workgroup = Workgroup::find($id);
        $workgroup->deleted = 0;
        $workgroup->save();

        event(new ModelChangedEvent($workgroup, 'restored'));

        return response()->json(['success' => 'Workgroup restored successfully']);
    }

    /**
     * Update an existing workgroup.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $validatedData = $this->validateRequest();
        
        $workgroup = Workgroup::find($id);
        $workgroup->fill($validatedData);
        $workgroup->updated_by = Auth::id();
        $workgroup->save();

        event(new ModelChangedEvent($workgroup, 'updated'));

        return response()->json(['success' => 'Workgroup updated successfully']);
    }

    /**
     * Create a new workgroup.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $validatedData = $this->validateRequest();

        $workgroup = new Workgroup();
        $workgroup->fill($validatedData);
        $workgroup->created_by = Auth::id();
        $workgroup->updated_by = Auth::id();
        $workgroup->save();

        event(new ModelChangedEvent($workgroup, 'created'));

        return response()->json(['success' => 'Workgroup created successfully']);
    }

    /**
     * Validate the request data for creating or updating a workgroup.
     *
     * @return array
     */
    private function validateRequest()
    {
        // Get active users with at least one role
        $activeUsers = User::where('deleted', 0)
            ->whereHas('roles') // Users with at least one role
            ->get()
            ->pluck('id');
        
        // Get existing active workgroup numbers for unique validation
        $existingNumbers = Workgroup::where('deleted', 0);
        if (request()->input('workgroupId')) {
            $existingNumbers = $existingNumbers->where('id', '!=', request()->input('workgroupId'));
        }
        $existingNumbers = $existingNumbers->pluck('workgroup_number')->toArray();
        
        // Get existing active workgroup names for unique validation
        $existingNames = Workgroup::where('deleted', 0);
        if (request()->input('workgroupId')) {
            $existingNames = $existingNames->where('id', '!=', request()->input('workgroupId'));
        }
        $existingNames = $existingNames->pluck('name')->toArray();

        return request()->validate([
            'name' => [
                'required',
                'max:255',
                'regex:/^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ\s,-]+$/',
                function (string $attribute, mixed $value, Closure $fail) use ($existingNames) {
                    if (in_array($value, $existingNames)) {
                        $fail("A csoport neve már foglalt");
                    }
                },
            ],
            'workgroup_number' => [
                'required',
                'numeric',
                'min:100',
                'max:999',
                function (string $attribute, mixed $value, Closure $fail) use ($existingNumbers) {
                    if (in_array($value, $existingNumbers)) {
                        $fail("A csoportszám már foglalt");
                    }
                },
            ],
            'leader_id' => [
                'required',
                'numeric',
                Rule::in($activeUsers->toArray()),
            ],
            'labor_administrator' => [
                'required',
                'numeric',
                Rule::in($activeUsers->toArray()),
            ],
        ],
        [
            'name.required' => 'A név kötelező',
            'name.max' => 'A név maximum 255 karakter lehet',
            'name.regex' => 'A név csak betűket, szóközt, vesszőt és kötőjelet tartalmazhat',
            'workgroup_number.required' => 'A csoportszám kötelező',
            'workgroup_number.numeric' => 'A csoportszám csak szám lehet',
            'workgroup_number.min' => 'A csoportszám minimum 100 lehet',
            'workgroup_number.max' => 'A csoportszám maximum 999 lehet',
            'leader_id.required' => 'A csoportvezető kötelező',
            'leader_id.numeric' => 'A csoportvezető id csak szám lehet',
            'leader_id.in' => 'A csoportvezető csak aktív, szerepkörrel rendelkező felhasználó lehet',
            'labor_administrator.required' => 'A munkaügyi ügyintéző kötelező',
            'labor_administrator.numeric' => 'A munkaügyi ügyintéző id csak szám lehet',
            'labor_administrator.in' => 'A munkaügyi ügyintéző csak aktív, szerepkörrel rendelkező felhasználó lehet',
        ]);
    }
}