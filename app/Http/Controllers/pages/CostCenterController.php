<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\CostCenterType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CostCenterController extends Controller
{
    public function manage()
    {
        $costcenterTypes = CostCenterType::where('deleted', 0)->get();
        $users = User::where('deleted', 0)->get();

        return view('content.pages.costcenters', compact('users', 'costcenterTypes'));
    }

    public function getAllCostCenters()
    {
        $costCenters = CostCenter::all()->map(function ($costCenter) {
            return [
                'id' => $costCenter->id,
                'cost_center_code' => $costCenter->cost_center_code,
                'name' => $costCenter->name,
                'type_id' => $costCenter->type_id,
                'type_name' => $costCenter->type->name,
                'lead_user_id' => $costCenter->lead_user_id,
                'lead_user_name' => $costCenter->leadUser->name,
                'project_coordinator_user_id' => $costCenter->project_coordinator_user_id,
                'project_coordinator_user_name' => $costCenter->projectCoordinatorUser->name,
                'due_date' => $costCenter->due_date,
                'minimal_order_limit' => number_format($costCenter->minimal_order_limit, 0),
                'valid_employee_recruitment' => $costCenter->valid_employee_recruitment,
                'deleted' => $costCenter->deleted,
                'created_at' => $costCenter->created_at,
                'created_by_name' => $costCenter->createdBy->name,
                'updated_at' => $costCenter->updated_at,
                'updated_by_name' => $costCenter->updatedBy->name,
            ];
        });
        return response()->json(['data' => $costCenters]);
    }

    public function delete($id)
    {
        $costCenter = CostCenter::find($id);
        $costCenter->deleted = 1;
        $costCenter->save();
        return response()->json(['message' => 'Cost center deleted successfully']);
    }

    public function restore($id)
    {
        $costCenter = CostCenter::find($id);
        $costCenter->deleted = 0;
        $costCenter->save();
        return response()->json(['message' => 'Cost center restored successfully']);
    }

    public function update($id)
    {
        $validatedData = request()->validate([
            'cost_center_code' => 'required',
            'name' => 'required',
            'type_id' => 'required',
            'lead_user_id' => 'required',
            'project_coordinator_user_id' => 'required',
            'due_date' => 'required|date_format:Y.m.d|after:yesterday',
            'minimal_order_limit' => 'required|numeric',
        ],
        [
            'cost_center_code.required' => 'Költséghely kód kötelező',
            'name.required' => 'Megnevezés kötelező',
            'type_id.required' => 'Típus kötelező',
            'lead_user_id.required' => 'Témavezető kötelező',
            'project_coordinator_user_id.required' => 'Projektkoordinátor kötelező',
            'due_date.required' => 'Lejárati dátum kötelező',
            'due_date.date' => 'Kérjük, valós formában add meg a dátumot: YYYY.MM.DD',
            'minimal_order_limit.required' => 'Minimális rendelési limit kötelező',
            'minimal_order_limit.numeric' => 'Minimum rendelési limit csak szám lehet',
        ]);

        $costCenter = CostCenter::find($id);
        $costCenter->cost_center_code = $validatedData['cost_center_code'];
        $costCenter->name = $validatedData['name'];
        $costCenter->type_id = $validatedData['type_id'];
        $costCenter->lead_user_id = $validatedData['lead_user_id'];
        $costCenter->project_coordinator_user_id = $validatedData['project_coordinator_user_id'];
        $costCenter->due_date = $validatedData['due_date'];
        $costCenter->minimal_order_limit = $validatedData['minimal_order_limit'];
        $costCenter->valid_employee_recruitment = request('valid_employee_recruitment') == 'true' ? 1 : 0;
        $costCenter->updated_by = Auth::id();
        $costCenter->save();
        
        return response()->json(['message' => 'Cost center updated successfully']);
    }

    public function create()
    {
        $validatedData = request()->validate([
            'cost_center_code' => 'required',
            'name' => 'required',
            'type_id' => 'required',
            'lead_user_id' => 'required',
            'project_coordinator_user_id' => 'required',
            'due_date' => 'required|date_format:Y.m.d|after:yesterday',
            'minimal_order_limit' => 'required|numeric',
        ],
        [
            'cost_center_code.required' => 'Költséghely kód kötelező',
            'name.required' => 'Megnevezés kötelező',
            'type_id.required' => 'Típus kötelező',
            'lead_user_id.required' => 'Témavezető kötelező',
            'project_coordinator_user_id.required' => 'Projektkoordinátor kötelező',
            'due_date.required' => 'Lejárati dátum kötelező',
            'due_date.date' => 'Kérjük, valós formában add meg a dátumot: YYYY.MM.DD',
            'minimal_order_limit.required' => 'Minimális rendelési limit kötelező',
            'minimal_order_limit.numeric' => 'Minimum rendelési limit csak szám lehet',
        ]);

        $costCenter = new CostCenter();
        $costCenter->cost_center_code = $validatedData['cost_center_code'];
        $costCenter->name = $validatedData['name'];
        $costCenter->type_id = $validatedData['type_id'];
        $costCenter->lead_user_id = $validatedData['lead_user_id'];
        $costCenter->project_coordinator_user_id = $validatedData['project_coordinator_user_id'];
        $costCenter->due_date = $validatedData['due_date'];
        $costCenter->minimal_order_limit = $validatedData['minimal_order_limit'];
        $costCenter->valid_employee_recruitment = request('valid_employee_recruitment') == 'true' ? 1 : 0;
        $costCenter->created_by = Auth::id();
        $costCenter->updated_by = Auth::id();
        $costCenter->save();

        return response()->json(['message' => 'Cost center created successfully']);
    }
}