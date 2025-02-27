<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workgroup;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class WorkgroupController extends Controller
{
    public function manage()
    {
        $users = User::nonAdmin()->where('deleted', 0)->get();
        $labor_administrators = User::nonAdmin()->where('deleted', 0)
            ->whereHas('workgroup', function ($query) {
                $query->where('workgroup_number', 908);
            })
            ->get();

        return view('content.pages.workgroups', compact('users', 'labor_administrators'));
    }

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
                'created_by_name' => $workgroup->createdBy->name,
                'updated_at' => $workgroup->updated_at,
                'updated_by_name' => $workgroup->updatedBy->name
            ];
        });
        return response()->json(['data' => $workgroups]);
    }

    public function delete($id)
    {
        $workgroup = Workgroup::find($id);
        $workgroup->deleted = 1;
        $workgroup->save();

        return response()->json(['success' => 'Workgroup deleted successfully']);
    }

    public function restore($id)
    {
        $workgroup = Workgroup::find($id);
        $workgroup->deleted = 0;
        $workgroup->save();

        return response()->json(['success' => 'Workgroup restored successfully']);
    }

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

    private function validateRequest()
    {
        $labor_administrators = User::where('deleted', 0)
            ->whereHas('workgroup', function ($query) {
                $query->where('workgroup_number', 908);
            })
            ->get()
            ->pluck('id');

        return request()->validate([
            'name' => 'required|max:255',
            'workgroup_number' => [
                'required',
                'numeric',
                Rule::unique('wf_workgroup', 'workgroup_number')->ignore(request()->input('workgroupId')),
            ],
            'leader_id' => 'required|numeric|exists:wf_user,id',
            'labor_administrator' => [
                'required',
                'numeric',
                Rule::exists('wf_user', 'id')->whereIn('id', $labor_administrators->toArray()),
                function (string $attribute, mixed $value, Closure $fail) use ($labor_administrators) {
                    if (!in_array($value, $labor_administrators->toArray())) {
                        $fail("Munkaügyi ügyintéző csak ilyen jogú felhasználó lehet");
                    }
                }
            ],
        ],
        [
            'name.required' => 'A név kötelező',
            'name.max' => 'A név maximum 255 karakter lehet',
            'workgroup_number.required' => 'A csoportszám kötelező',
            'workgroup_number.numeric' => 'A csoportszám csak szám lehet',
            'workgroup_number.unique' => 'A csoportszám már foglalt',
            'leader_id.required' => 'A csoportvezető kötelező',
            'leader_id.numeric' => 'A csoportvezető id csak szám lehet',
            'leader_id.exists' => 'A csoportvezető nem létezik',
            'labor_administrator.required' => 'A munkaügyi ügyintéző kötelező',
            'labor_administrator.numeric' => 'A munkaügyi ügyintéző id csak szám lehet',
            'labor_administrator.exists' => 'A munkaügyi ügyintéző nem létezik',
        ]);
    }
}