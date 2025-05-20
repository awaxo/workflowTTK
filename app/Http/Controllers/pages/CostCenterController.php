<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Exports\CostCenterExport;
use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\CostCenterType;
use App\Models\User;
use App\Models\Workgroup;
use App\Services\Import\CostCenterImporter;
use App\Services\Import\ImportManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

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

    /**
     * Export cost centers to Excel
     * 
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // Költséghelyek lekérdezése az összes kapcsolódó adattal
        $costCenters = CostCenter::with(['type', 'leadUser', 'projectCoordinatorUser', 'createdBy', 'updatedBy'])->get();
        
        // Adatok előkészítése az exporthoz
        $data = $costCenters->map(function ($costCenter) {
            return [
                'cost_center_code' => $costCenter->cost_center_code,
                'name' => $costCenter->name,
                'type_name' => $costCenter->type->name,
                'lead_user_name' => $costCenter->leadUser->name,
                'project_coordinator_user_name' => $costCenter->projectCoordinatorUser->name,
                'due_date' => $costCenter->due_date ? date('Y.m.d', strtotime($costCenter->due_date)) : '',
                'minimal_order_limit' => number_format($costCenter->minimal_order_limit, 0, '.', ' '),
                'valid_employee_recruitment' => $costCenter->valid_employee_recruitment ? 'Igen' : 'Nem',
                'valid_procurement' => $costCenter->valid_procurement ? 'Igen' : 'Nem',
                'active' => $costCenter->deleted ? 'Nem' : 'Igen',
                'updated_by_name' => optional($costCenter->updatedBy)->name ?? 'Technikai felhasználó',
                'updated_at' => date('Y.m.d H:i:s', strtotime($costCenter->updated_at)),
                'created_by_name' => optional($costCenter->createdBy)->name ?? 'Technikai felhasználó',
                'created_at' => date('Y.m.d H:i:s', strtotime($costCenter->created_at)),
            ];
        });
        
        // Fejlécek meghatározása
        $headers = [
            'Költséghely',
            'Megnevezés',
            'Típus',
            'Témavezető',
            'Projektkoordinátor',
            'Lejárat',
            'Minimális rendelési limit',
            'Érvényes felvételi kérelem',
            'Érvényes beszerzés',
            'Aktív',
            'Utolsó módosító',
            'Utolsó módosítás',
            'Létrehozó',
            'Létrehozás',
        ];
        
        // Fájlnév generálása az aktuális dátummal és idővel
        $filename = 'koltseghelyek_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Excel fájl letöltése
        return Excel::download(new CostCenterExport($data, $headers), $filename);
    }

    public function manage()
    {
        $costcenterTypes = CostCenterType::where('deleted', 0)->get();
        $users = User::nonAdmin()->where('deleted', 0)
            ->whereHas('roles')
            ->get();
        $projectCoordinators = User::nonAdmin()->where('deleted', 0)
            ->whereHas('roles')
            ->whereHas('workgroup', function ($query) {
                $query->whereIn('workgroup_number', [910, 911])
                    ->where('deleted', 0);
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
                'created_by_name' => optional($costCenter->createdBy)->name ?? 'Technikai felhasználó',
                'updated_at' => $costCenter->updated_at,
                'updated_by_name' => optional($costCenter->updatedBy)->name ?? 'Technikai felhasználó',
            ];
        });

        return response()->json(['data' => $costCenters]);
    }

    /**
     * Egy endpoint a költséghely kód teljes validációjára:
     * - Egyediség ellenőrzése
     * - Aktív csoport ellenőrzése
     */
    public function validateCostCenterCode(Request $request)
    {
        $costCenterCode = $request->input('cost_center_code');
        $workgroupNumber = $request->input('workgroup_number');
        $costCenterId = $request->input('costcenter_id');
        
        // Formátum ellenőrzése (bár ezt már a regexp validátor kezeli a kliens oldalon)
        if (!preg_match('/^\d{4}-\d{2}\s\d{3}$/', $costCenterCode)) {
            return response()->json([
                'valid' => false,
                'message' => 'A költséghely formátuma nem megfelelő.'
            ]);
        }
        
        // Egyediség ellenőrzése
        $query = CostCenter::where('cost_center_code', $costCenterCode)
            ->where('deleted', 0);
        
        if ($costCenterId) {
            $query->where('id', '!=', $costCenterId);
        }
        
        $exists = $query->exists();
        
        if ($exists) {
            return response()->json([
                'valid' => false,
                'message' => 'Ez a költséghely már használatban van.'
            ]);
        }
        
        // Aktív csoport ellenőrzése
        $workgroupExists = Workgroup::where('workgroup_number', $workgroupNumber)
            ->where('deleted', 0)
            ->exists();
        
        if (!$workgroupExists) {
            return response()->json([
                'valid' => false,
                'message' => 'A költséghely utolsó 3 számjegyének meg kell egyeznie egy létező és aktív csoportszámmal.'
            ]);
        }
        
        return response()->json(['valid' => true]);
    }

    public function checkUserInWorkgroup(Request $request)
    {
        $userId = $request->input('user_id');
        $workgroupNumber = $request->input('workgroup_number');
        
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['valid' => false]);
        }
        
        $inWorkgroup = $user->workgroup && 
                      $user->workgroup->workgroup_number == $workgroupNumber && 
                      $user->workgroup->deleted == 0 &&
                      $user->roles->count() > 0;
        
        return response()->json(['valid' => $inWorkgroup]);
    }

    public function checkProjectCoordinator(Request $request)
    {
        $userId = $request->input('user_id');
        
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['valid' => false]);
        }
        
        $isProjectCoordinator = $user->workgroup && 
                               in_array($user->workgroup->workgroup_number, [910, 911]) && 
                               $user->workgroup->deleted == 0 &&
                               $user->roles->count() > 0;
        
        return response()->json(['valid' => $isProjectCoordinator]);
    }

    public function delete($id)
    {
        $costCenter = CostCenter::find($id);
        $costCenter->deleted = 1;
        $costCenter->save();

        event(new ModelChangedEvent($costCenter, 'deleted'));

        return response()->json(['message' => 'Cost center deleted successfully']);
    }

    public function restore($id)
    {
        $costCenter = CostCenter::find($id);
        $costCenter->deleted = 0;
        $costCenter->save();

        event(new ModelChangedEvent($costCenter, 'restored'));

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

    private function validateRequest()
    {
        $input = request()->all();
        if (isset($input['minimal_order_limit'])) {
            $input['minimal_order_limit'] = str_replace(' ', '', $input['minimal_order_limit']);
            request()->replace($input);
        }

        // Költséghely ellenőrzése egyediségre
        $costCenterId = request('costcenter_id');
        $costCenterCodeRule = 'required|max:50|regex:/^\d{4}-\d{2}\s\d{3}$/';
        
        if (!$costCenterId) {
            // Új létrehozásnál ellenőrizzük az egyediséget
            $existingCostCenter = CostCenter::where('cost_center_code', request('cost_center_code'))
                ->where('deleted', 0)
                ->first();
            
            if ($existingCostCenter) {
                throw ValidationException::withMessages([
                    'cost_center_code' => ['A megadott költséghely (' . request('cost_center_code') . ') már létezik'],
                ]);
            }
        } else {
            // Módosításnál csak akkor ellenőrizzük az egyediséget, ha változott a kód
            $existingCostCenter = CostCenter::find($costCenterId);
            if ($existingCostCenter && $existingCostCenter->cost_center_code != request('cost_center_code')) {
                $duplicateCostCenter = CostCenter::where('cost_center_code', request('cost_center_code'))
                    ->where('deleted', 0)
                    ->where('id', '!=', $costCenterId)
                    ->first();
                
                if ($duplicateCostCenter) {
                    throw ValidationException::withMessages([
                        'cost_center_code' => ['A megadott költséghely (' . request('cost_center_code') . ') már létezik'],
                    ]);
                }
            }
        }
        
        // Ellenőrizzük, hogy a költséghely utolsó 3 számjegye egy aktív csoportszám
        if (preg_match('/^\d{4}-\d{2}\s(\d{3})$/', request('cost_center_code'), $matches)) {
            $workgroupNumber = $matches[1];
            $workgroupExists = Workgroup::where('workgroup_number', $workgroupNumber)
                ->where('deleted', 0)
                ->exists();
            
            if (!$workgroupExists) {
                throw ValidationException::withMessages([
                    'cost_center_code' => ['A költséghely utolsó 3 számjegye (' . $workgroupNumber . ') nem létező vagy inaktív csoportszám'],
                ]);
            }
            
            // Témavezető ellenőrzése, hogy a költséghely csoportszámához tartozik-e
            $leadUser = User::find(request('lead_user_id'));
            if ($leadUser) {
                $isInWorkgroup = $leadUser->workgroup && 
                                $leadUser->workgroup->workgroup_number == $workgroupNumber && 
                                $leadUser->workgroup->deleted == 0 &&
                                $leadUser->roles->count() > 0;
                
                if (!$isInWorkgroup) {
                    throw ValidationException::withMessages([
                        'lead_user_id' => ['A témavezetőnek a költséghely csoportszámához (' . $workgroupNumber . ') tartozó aktív felhasználónak kell lennie'],
                    ]);
                }
            }
        }
        
        // Projektkoordinátor ellenőrzése, hogy 910 vagy 911-es csoportba tartozik-e
        $projectCoordinatorUser = User::find(request('project_coordinator_user_id'));
        if ($projectCoordinatorUser) {
            $isProjectCoordinator = $projectCoordinatorUser->workgroup && 
                                  in_array($projectCoordinatorUser->workgroup->workgroup_number, [910, 911]) && 
                                  $projectCoordinatorUser->workgroup->deleted == 0 &&
                                  $projectCoordinatorUser->roles->count() > 0;
            
            if (!$isProjectCoordinator) {
                throw ValidationException::withMessages([
                    'project_coordinator_user_id' => ['A projektkoordinátornak a 910 vagy 911-es csoportba tartozó aktív felhasználónak kell lennie'],
                ]);
            }
        }

        // Aktív felhasználók, akiknek van legalább egy szerepkörük
        $activeUserIds = User::where('deleted', 0)
            ->whereHas('roles')
            ->pluck('id')
            ->toArray();

        return request()->validate([
            'cost_center_code' => $costCenterCodeRule,
            'name' => 'required|max:255',
            'type_id' => 'required|exists:wf_cost_center_type,id',
            'lead_user_id' => [
                'required',
                Rule::in($activeUserIds)
            ],
            'project_coordinator_user_id' => [
                'required',
                Rule::in($activeUserIds)
            ],
            'due_date' => $input['due_date'] != '' ? 'date_format:Y.m.d' : '',
            'minimal_order_limit' => 'required|numeric',
        ],
        [
            'cost_center_code.required' => 'Költséghely kód kötelező',
            'cost_center_code.max' => 'Költséghely kód maximum 50 karakter lehet',
            'cost_center_code.regex' => 'A költséghely kód formátuma nem megfelelő. Elvárt formátum: 4 számjegy, kötőjel, 2 számjegy, szóköz, 3 számjegy (pl. 0004-24 908)',
            'name.required' => 'Megnevezés kötelező',
            'name.max' => 'Megnevezés maximum 255 karakter lehet',
            'type_id.required' => 'Típus kötelező',
            'type_id.exists' => 'Típus nem létezik vagy inaktív',
            'lead_user_id.required' => 'Témavezető kötelező',
            'lead_user_id.in' => 'A témavezetőnek aktív, szerepkörrel rendelkező felhasználónak kell lennie',
            'project_coordinator_user_id.required' => 'Projektkoordinátor kötelező',
            'project_coordinator_user_id.in' => 'A projektkoordinátornak aktív, szerepkörrel rendelkező felhasználónak kell lennie',
            'due_date.date_format' => 'Kérjük, valós formában add meg a dátumot: YYYY.MM.DD',
            'minimal_order_limit.required' => 'Minimális rendelési limit kötelező',
            'minimal_order_limit.numeric' => 'Minimum rendelési limit csak szám lehet',
        ]);
    }
}