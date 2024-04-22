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
        $costCenter = CostCenter::find($id);
        $costCenter->cost_center_code = request('cost_center_code');
        $costCenter->name = request('name');
        $costCenter->type_id = request('type_id');
        $costCenter->lead_user_id = request('lead_user_id');
        $costCenter->project_coordinator_user_id = request('project_coordinator_user_id');
        $costCenter->due_date = request('due_date');
        $costCenter->minimal_order_limit = request('minimal_order_limit');
        $costCenter->valid_employee_recruitment = request('valid_employee_recruitment') == 'true' ? 1 : 0;
        $costCenter->updated_by = Auth::id();
        $costCenter->save();
        return response()->json(['message' => 'Cost center updated successfully']);
    }

    public function create()
    {
        $costCenter = new CostCenter();
        $costCenter->cost_center_code = request('cost_center_code');
        $costCenter->name = request('name');
        $costCenter->type_id = request('type_id');
        $costCenter->lead_user_id = request('lead_user_id');
        $costCenter->project_coordinator_user_id = request('project_coordinator_user_id');
        $costCenter->due_date = request('due_date');
        $costCenter->minimal_order_limit = request('minimal_order_limit');
        $costCenter->valid_employee_recruitment = request('valid_employee_recruitment') == 'true' ? 1 : 0;
        $costCenter->created_by = Auth::id();
        $costCenter->updated_by = Auth::id();
        $costCenter->save();
        return response()->json(['message' => 'Cost center created successfully']);
    }
}