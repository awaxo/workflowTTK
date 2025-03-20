<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\ExternalAccessRight;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
                'created_by_name' => optional($externalAccess->createdBy)->name ?? 'Technikai felhasználó',
                'updated_at' => $externalAccess->updated_at,
                'updated_by_name' => optional($externalAccess->updatedBy)->name ?? 'Technikai felhasználó',
            ];
        });
        return response()->json(['data' => $externalAcceses]);
    }

    public function checkActiveGroup()
    {
        $adminGroupId = request()->input('admin_group_number');
        
        $isActive = Workgroup::where('id', $adminGroupId)
            ->where('deleted', 0)
            ->exists();
        
        return response()->json(['valid' => $isActive]);
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
        $validatedData = $this->validateRequest();

        $externalAccess = ExternalAccessRight::find($id);
        $externalAccess->fill($validatedData);
        $externalAccess->save();
        return response()->json(['success' => 'External access right updated successfully']);
    }

    public function create()
    {
        $validatedData = $this->validateRequest();
    
        $externalAccess = new ExternalAccessRight();
        $externalAccess->fill($validatedData);
        $externalAccess->created_by = Auth::id();
        $externalAccess->updated_by = Auth::id();
        $externalAccess->save();

        return response()->json(['success' => 'External access right created successfully']);
    }

    private function validateRequest()
    {
        // Az aktív workgroup-ok ID-jait kérjük le
        $activeWorkgroupIds = Workgroup::where('deleted', 0)
            ->pluck('id')
            ->toArray();

        return request()->validate([
            'external_system' => 'required|max:255',
            'admin_group_number' => [
                'required',
                'numeric',
                Rule::in($activeWorkgroupIds),
            ],
        ], [
            'external_system.required' => 'Külső rendszer név kötelező',
            'external_system.max' => 'Külső rendszer név maximum 255 karakter lehet',
            'admin_group_number.required' => 'Admin csoport kötelező',
            'admin_group_number.numeric' => 'Admin csoport id csak szám lehet',
            'admin_group_number.in' => 'Csak aktív csoport választható',
        ]);
    }
}