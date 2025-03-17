<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\CostCenterType;
use Illuminate\Support\Facades\Auth;

class CostCenterTypeController extends Controller
{
    public function manage()
    {
        return view('content.pages.costcenter-types');
    }

    public function getAllCostCenterTypes()
    {
        $costcenterTypes = CostCenterType::all()->map(function ($costcenterType) {
            return [
                'id' => $costcenterType->id,
                'name' => $costcenterType->name,
                'tender' => $costcenterType->tender,
                'financial_countersign' => $costcenterType->financial_countersign,
                'clause_template' => $costcenterType->clause_template,
                'deleted' => $costcenterType->deleted,
                'created_at' => $costcenterType->created_at,
                'created_by_name' => optional($costcenterType->createdBy)->name ?? 'Technikai felhasználó',
                'updated_at' => $costcenterType->updated_at,
                'updated_by_name' => optional($costcenterType->updatedBy)->name ?? 'Technikai felhasználó',
            ];
        });
        return response()->json(['data' => $costcenterTypes]);
    }

    public function delete($id)
    {
        $costcenterType = CostCenterType::find($id);
        $costcenterType->deleted = 1;
        $costcenterType->save();
        return response()->json(['message' => 'Cost center type deleted successfully']);
    }

    public function restore($id)
    {
        $costcenterType = CostCenterType::find($id);
        $costcenterType->deleted = 0;
        $costcenterType->save();
        return response()->json(['message' => 'Cost center type restored successfully']);
    }

    public function update($id)
    {
        $validatedData = $this->validateRequest();

        $costcenterType = CostCenterType::find($id);
        $costcenterType->fill($validatedData);
        $costcenterType->tender = request('tender') == 'true' ? 1 : 0;
        $costcenterType->clause_template = request('clause_template') ?? '';
        $costcenterType->updated_by = Auth::id();
        $costcenterType->save();

        event(new ModelChangedEvent($costcenterType, 'updated'));

        return response()->json(['message' => 'Cost center type updated successfully']);
    }

    public function create()
    {
        $validatedData = $this->validateRequest();

        $costcenterType = new CostCenterType();
        $costcenterType->fill($validatedData);
        $costcenterType->tender = request('tender') == 'true' ? 1 : 0;
        $costcenterType->clause_template = request('clause_template') ?? '';
        $costcenterType->created_by = Auth::id();
        $costcenterType->updated_by = Auth::id();
        $costcenterType->save();

        event(new ModelChangedEvent($costcenterType, 'created'));

        return response()->json(['message' => 'Cost center type created successfully']);
    }

    private function validateRequest()
    {
        return request()->validate([
            'name' => 'required|max:255',
            'financial_countersign' => 'required|in:pénzügyi osztályvezető,projektkooridinációs osztályvezető',
        ],
        [
            'name.required' => 'Költséghely típus név kötelező',
            'name.max' => 'Költséghely típus név maximum 255 karakter lehet',
            'financial_countersign.required' => 'Pénzügyi ellenjegyző kötelező',
            'financial_countersign.in' => 'Pénzügyi ellenjegyző értéke nem megfelelő',
        ]);
    }
}