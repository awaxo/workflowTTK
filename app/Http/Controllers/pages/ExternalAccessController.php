<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\ExternalAccessRight;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;

class ExternalAccessController extends Controller
{
    public function manage()
    {
        $workgroups = Workgroup::where('deleted', 0)->get();

        return view('content.pages.external-access-manage', compact('workgroups'));
    }

    public function getAllExternalAccess()
    {
        $externalAcceses = ExternalAccessRight::all()->map(function ($externalAccess) {
            return [
                'id' => $externalAccess->id,
                'external_system' => $externalAccess->external_system,
                'admin_group_number' => $externalAccess->workgroup->id,
                'admin_group_name' => $externalAccess->workgroup->workgroup_number . ' - ' . $externalAccess->workgroup->name,
                'deleted' => $externalAccess->deleted,
                'created_at' => $externalAccess->created_at,
                'created_by_name' => $externalAccess->createdBy->name,
                'updated_at' => $externalAccess->updated_at,
                'updated_by_name' => $externalAccess->updatedBy->name,
            ];
        });
        return response()->json(['data' => $externalAcceses]);
    }

    public function delete($id)
    {
        $externalAccess = ExternalAccessRight::find($id);
        $externalAccess->deleted = 1;
        $externalAccess->save();
        return response()->json(['success' => 'External access right deleted successfully']);
    }

    public function restore($id)
    {
        $externalAccess = ExternalAccessRight::find($id);
        $externalAccess->deleted = 0;
        $externalAccess->save();
        return response()->json(['success' => 'External access right restored successfully']);
    }

    public function update($id)
    {
        $externalAccess = ExternalAccessRight::find($id);
        $externalAccess->external_system = request('external_system');
        $externalAccess->admin_group_number = request('admin_group_number');
        $externalAccess->save();
        return response()->json(['success' => 'External access right updated successfully']);
    }

    public function create()
    {
        $externalAccess = new ExternalAccessRight();
        $externalAccess->external_system = request('external_system');
        $externalAccess->admin_group_number = request('admin_group_number');
        $externalAccess->created_by = Auth::id();
        $externalAccess->updated_by = Auth::id();
        $externalAccess->save();
        return response()->json(['success' => 'External access right created successfully']);
    }
}