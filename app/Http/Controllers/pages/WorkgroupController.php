<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;

class WorkgroupController extends Controller
{
    public function manage()
    {
        $users = User::where('deleted', 0)->get();

        return view('content.pages.workgroups', compact('users'));
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
        $workgroup = Workgroup::find($id);
        $workgroup->name = request('name');
        $workgroup->workgroup_number = request('workgroup_number');
        $workgroup->leader_id = request('leader_id');
        $workgroup->labor_administrator = request('labor_administrator');
        $workgroup->updated_by = Auth::id();
        $workgroup->save();
        return response()->json(['success' => 'Workgroup updated successfully']);
    }

    public function create()
    {
        $workgroup = new Workgroup();
        $workgroup->name = request('name');
        $workgroup->workgroup_number = request('workgroup_number');
        $workgroup->leader_id = request('leader_id');
        $workgroup->labor_administrator = request('labor_administrator');
        $workgroup->created_by = Auth::id();
        $workgroup->updated_by = Auth::id();
        $workgroup->save();
        return response()->json(['success' => 'Workgroup created successfully']);
    }
}