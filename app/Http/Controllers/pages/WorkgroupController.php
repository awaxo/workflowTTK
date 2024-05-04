<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workgroup;
use Closure;
use Illuminate\Support\Facades\Auth;

class WorkgroupController extends Controller
{
    public function manage()
    {
        $users = User::where('deleted', 0)->get();
        $labor_administrators = User::where('deleted', 0)
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
                'updated_by_name' => $workgroup->updatedBy->name,
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
        $labor_administrators = User::where('deleted', 0)
            ->whereHas('workgroup', function ($query) {
                $query->where('workgroup_number', 908);
            })
            ->get()
            ->pluck('id');

        $validatedData = request()->validate([
            'name' => 'required',
            'workgroup_number' => 'required|numeric',
            'leader_id' => 'required|numeric',
            'labor_administrator' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail) use ($labor_administrators) {
                    if (!in_array($value, $labor_administrators->toArray())) {
                        $fail("Munkaügyi ügyintéző csak ilyen jogú felhasználó lehet");
                    }
                }],
        ], [
            'name.required' => 'A név kötelező',
            'workgroup_number.required' => 'A csoportszám kötelező',
            'workgroup_number.numeric' => 'A csoportszám csak szám érték lehet',
            'leader_id.required' => 'A csoportvezető kötelező',
            'leader_id.numeric' => 'A csoportvezető id megadása kötelező',
            'labor_administrator.required' => 'A munkaügyi ügyintéző kötelező',
        ]);

        $workgroup = Workgroup::find($id);
        $workgroup->name = $validatedData['name'];
        $workgroup->workgroup_number = $validatedData['workgroup_number'];
        $workgroup->leader_id = $validatedData['leader_id'];
        $workgroup->labor_administrator = $validatedData['labor_administrator'];
        $workgroup->updated_by = Auth::id();
        $workgroup->save();

        return response()->json(['success' => 'Workgroup updated successfully']);
    }

    public function create()
    {
        $labor_administrators = User::where('deleted', 0)
            ->whereHas('workgroup', function ($query) {
                $query->where('workgroup_number', 908);
            })
            ->get()
            ->pluck('id');

        $validatedData = request()->validate([
            'name' => 'required',
            'workgroup_number' => 'required|numeric',
            'leader_id' => 'required|numeric',
            'labor_administrator' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail) use ($labor_administrators) {
                    if (!in_array($value, $labor_administrators->toArray())) {
                        $fail("Munkaügyi ügyintéző csak ilyen jogú felhasználó lehet");
                    }
                }],
        ],
        [
            'name.required' => 'A név kötelező',
            'workgroup_number.required' => 'A csoportszám kötelező',
            'workgroup_number.numeric' => 'A csoportszám id csak szám lehet',
            'leader_id.required' => 'A csoportvezető kötelező',
            'leader_id.numeric' => 'A csoportvezető id csak szám lehet',
            'labor_administrator.required' => 'A munkaügyi ügyintéző kötelező',
        ]);

        $workgroup = new Workgroup();
        $workgroup->name = $validatedData['name'];
        $workgroup->workgroup_number = $validatedData['workgroup_number'];
        $workgroup->leader_id = $validatedData['leader_id'];
        $workgroup->labor_administrator = $validatedData['labor_administrator'];
        $workgroup->created_by = Auth::id();
        $workgroup->updated_by = Auth::id();
        $workgroup->save();

        return response()->json(['success' => 'Workgroup created successfully']);
    }
}