<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\CostCenterType;
use Illuminate\Support\Facades\Auth;
use Closure;

/**
 * CostCenterTypeController handles the management of cost center types,
 * including CRUD operations and validation.
 */
class CostCenterTypeController extends Controller
{
    /**
     * Display the cost center types management page.
     *
     * @return \Illuminate\View\View
     */
    public function manage()
    {
        return view('content.pages.costcenter-types');
    }

    /**
     * Get all cost center types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

    /*
     * Check if the cost center type name is unique.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkNameUnique()
    {
        $name = request()->input('name');
        $costcenterTypeId = request()->input('costcenter_type_id');
        
        $query = CostCenterType::where('name', $name)
            ->where('deleted', 0);
        
        if ($costcenterTypeId) {
            $query->where('id', '!=', $costcenterTypeId);
        }
        
        $exists = $query->exists();
        
        return response()->json(['valid' => !$exists]);
    }

    /**
     * Delete a cost center type (soft delete).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $costcenterType = CostCenterType::find($id);
        $costcenterType->deleted = 1;
        $costcenterType->save();

        event(new ModelChangedEvent($costcenterType, 'deleted'));

        return response()->json(['message' => 'Cost center type deleted successfully']);
    }

    /**
     * Restore a soft-deleted cost center type.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $costcenterType = CostCenterType::find($id);
        $costcenterType->deleted = 0;
        $costcenterType->save();

        event(new ModelChangedEvent($costcenterType, 'restored'));
        
        return response()->json(['message' => 'Cost center type restored successfully']);
    }

    /**
     * Update an existing cost center type.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Create a new cost center type.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

    /*
     * Validate the request data for creating or updating a cost center type.
     *
     * @return array
     */
    private function validateRequest()
    {
        $existingNames = CostCenterType::where('deleted', 0);
        $id = request('id');
        
        if ($id) {
            $existingNames = $existingNames->where('id', '!=', $id);
        }
        
        $existingNames = $existingNames->pluck('name')->toArray();

        return request()->validate([
            'name' => [
                'required',
                'max:255',
                'regex:/^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ\s,-]+$/',
                function (string $attribute, mixed $value, Closure $fail) use ($existingNames) {
                    if (in_array($value, $existingNames)) {
                        $fail("A költséghely típus neve már foglalt");
                    }
                },
            ],
            'financial_countersign' => 'required|in:pénzügyi osztályvezető,projektkooridinációs osztályvezető',
        ],
        [
            'name.required' => 'Költséghely típus név kötelező',
            'name.max' => 'Költséghely típus név maximum 255 karakter lehet',
            'name.regex' => 'A költséghely típus neve csak betűket, szóközt, vesszőt és kötőjelet tartalmazhat',
            'financial_countersign.required' => 'Pénzügyi ellenjegyző kötelező',
            'financial_countersign.in' => 'Pénzügyi ellenjegyző értéke nem megfelelő',
        ]);
    }
}