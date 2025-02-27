<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\CostCenterType;
use App\Models\User;
use App\Services\Import\CostCenterImporter;
use App\Services\Import\ImportManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CostCenterController extends Controller
{
    protected $importManager;

    public function __construct(ImportManager $importManager)
    {
        $this->importManager = $importManager;
        $this->importManager->registerImporter('costcenter', new CostCenterImporter());
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);
    
        $file = $request->file('csv_file');
    
        $errors = $this->importManager->import('costcenter', $file);
    
        if (!empty($errors)) {
            // Ensure the error messages are properly encoded in UTF-8
            $encodedErrors = array_map(function($errorArray) {
                return array_map(function($errorMessage) {
                    return mb_convert_encoding($errorMessage, 'UTF-8', 'UTF-8');
                }, $errorArray);
            }, $errors);
    
            return response()->json(['errors' => $encodedErrors], 422);
        }
    
        return response()->json(['message' => 'Import successful!'], 200);
    }

    public function manage()
    {
        $costcenterTypes = CostCenterType::where('deleted', 0)->get();
        $users = User::nonAdmin()->where('deleted', 0)->get();
        $projectCoordinators = User::nonAdmin()->where('deleted', 0)
            ->whereHas('workgroup', function ($query) {
                $query->whereIn('workgroup_number', [910, 911]);
            })
            ->get();

        return view('content.pages.costcenters', compact('users', 'projectCoordinators', 'costcenterTypes'));
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
                'minimal_order_limit' => number_format($costCenter->minimal_order_limit, 0, '.', ' '),
                'valid_employee_recruitment' => $costCenter->valid_employee_recruitment,
                'valid_procurement' => $costCenter->valid_procurement,
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
        $validatedData = $this->validateRequest($id, request('cost_center_code'));

        $costCenter = CostCenter::find($id);
        $costCenter->fill($validatedData);
        $costCenter->valid_employee_recruitment = request('valid_employee_recruitment') == 'true' ? 1 : 0;
        $costCenter->valid_procurement = request('valid_procurement') == 'true' ? 1 : 0;
        $costCenter->updated_by = Auth::id();
        $costCenter->save();

        event(new ModelChangedEvent($costCenter, 'updated'));
        
        return response()->json(['message' => 'Cost center updated successfully']);
    }

    public function create()
    {
        $validatedData = $this->validateRequest();

        $costCenter = new CostCenter();
        $costCenter->fill($validatedData);
        $costCenter->valid_employee_recruitment = request('valid_employee_recruitment') == 'true' ? 1 : 0;
        $costCenter->valid_procurement = request('valid_procurement') == 'true' ? 1 : 0;
        $costCenter->created_by = Auth::id();
        $costCenter->updated_by = Auth::id();
        $costCenter->save();

        event(new ModelChangedEvent($costCenter, 'created'));

        return response()->json(['message' => 'Cost center created successfully']);
    }

    private function validateRequest($id = null, $costCenterCode = null)
    {
        $input = request()->all();
        if (isset($input['minimal_order_limit'])) {
            $input['minimal_order_limit'] = str_replace(' ', '', $input['minimal_order_limit']);
            request()->replace($input);
        }

        $checkUnique = true;
        if ($id && $costCenterCode) {
            $costCenter = CostCenter::find($id);
            if ($costCenter->cost_center_code == $costCenterCode) {
                $checkUnique = false;
            }
        }

        return request()->validate([
            'cost_center_code' => $checkUnique ? 'required|max:50|unique:wf_cost_center,cost_center_code' : 'required|max:50',
            'name' => 'required|max:255',
            'type_id' => 'required|exists:wf_cost_center_type,id',
            'lead_user_id' => 'required|exists:wf_user,id',
            'project_coordinator_user_id' => 'required|exists:wf_user,id',
            'due_date' => $input['due_date'] != '' ? 'date_format:Y.m.d' : '',
            'minimal_order_limit' => 'required|numeric',
        ],
        [
            'cost_center_code.required' => 'Költséghely kód kötelező',
            'cost_center_code.max' => 'Költséghely kód maximum 50 karakter lehet',
            'cost_center_code.unique' => 'A megadott költséghely (' . $input['cost_center_code'] . ') már létezik',
            'name.required' => 'Megnevezés kötelező',
            'name.max' => 'Megnevezés maximum 255 karakter lehet',
            'type_id.required' => 'Típus kötelező',
            'type_id.exists' => 'Típus nem létezik',
            'lead_user_id.required' => 'Témavezető kötelező',
            'lead_user_id.exists' => 'Témavezető nem létezik',
            'project_coordinator_user_id.required' => 'Projektkoordinátor kötelező',
            'project_coordinator_user_id.exists' => 'Projektkoordinátor nem létezik',
            'due_date.date' => 'Kérjük, valós formában add meg a dátumot: YYYY.MM.DD',
            'minimal_order_limit.required' => 'Minimális rendelési limit kötelező',
            'minimal_order_limit.numeric' => 'Minimum rendelési limit csak szám lehet',
        ]);
    }
}