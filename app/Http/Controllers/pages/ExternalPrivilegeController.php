<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\ExternalPrivilege;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * ExternalPrivilegeController handles the management of external privileges,
 * including CRUD operations and DataTables integration.
 */
class ExternalPrivilegeController extends Controller
{
    /**
     * Display the external privileges management page.
     *
     * @return \Illuminate\View\View
     */
    public function manage()
    {
        return view('content.pages.external-privileges');
    }

    /**
     * Get all external privileges for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllExternalPrivileges()
    {
        $externalPrivileges = ExternalPrivilege::orderBy('name')->get()->map(function ($privilege) {
            return [
                'id' => $privilege->id,
                'name' => $privilege->name,
                'description' => $privilege->description,
                'created_at' => $privilege->created_at,
                'created_by_name' => optional($privilege->createdBy)->name ?? 'Technikai felhasználó',
                'updated_at' => $privilege->updated_at,
                'updated_by_name' => optional($privilege->updatedBy)->name ?? 'Technikai felhasználó',
                'user_count' => $privilege->users()->count(),
            ];
        });
        
        return response()->json(['data' => $externalPrivileges]);
    }

    /**
     * Delete an external privilege.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $privilege = ExternalPrivilege::findOrFail($id);
        
        // Store a copy for the event
        $deletedPrivilege = clone $privilege;
        
        // Delete the privilege - this will automatically remove it from users
        // because of the foreign key constraint with cascade delete
        $privilege->delete();

        event(new ModelChangedEvent($deletedPrivilege, 'deleted'));

        return response()->json(['message' => 'Külsős jog sikeresen törölve']);
    }

    /**
     * Update an existing external privilege.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $validatedData = $this->validateRequest();

        $privilege = ExternalPrivilege::findOrFail($id);
        $privilege->fill($validatedData);
        $privilege->updated_by = Auth::id();
        $privilege->save();

        event(new ModelChangedEvent($privilege, 'updated'));

        return response()->json(['message' => 'Külsős jog sikeresen frissítve']);
    }

    /**
     * Create a new external privilege.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $validatedData = $this->validateRequest();

        $privilege = new ExternalPrivilege();
        $privilege->fill($validatedData);
        $privilege->created_by = Auth::id();
        $privilege->updated_by = Auth::id();
        $privilege->save();

        event(new ModelChangedEvent($privilege, 'created'));

        return response()->json(['message' => 'Külsős jog sikeresen létrehozva']);
    }

    /**
     * Validate the request data.
     *
     * @return array
     */
    private function validateRequest()
    {
        return request()->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
        ],
        [
            'name.required' => 'A név mező kitöltése kötelező',
            'name.max' => 'A név maximum 255 karakter lehet',
            'description.max' => 'A leírás maximum 1000 karakter lehet',
        ]);
    }
}