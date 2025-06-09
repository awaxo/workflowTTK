<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Support\Facades\Auth;

/**
 * PositionController handles the management of positions,
 * including CRUD operations and validation.
 */
class PositionController extends Controller
{
    /**
     * Display the positions management page.
     *
     * @return \Illuminate\View\View
     */
    public function manage()
    {
        return view('content.pages.positions');
    }

    /*
     * Get all positions, including their details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPositions()
    {
        $positions = Position::all()->map(function ($position) {
            return [
                'id' => $position->id,
                'name' => $position->name,
                'type' => $position->type,
                'deleted' => $position->deleted,
                'created_at' => $position->created_at,
                'created_by_name' => optional($position->createdBy)->name ?? 'Technikai felhasználó',
                'updated_at' => $position->updated_at,
                'updated_by_name' => optional($position->updatedBy)->name ?? 'Technikai felhasználó',
            ];
        });
        return response()->json(['data' => $positions]);
    }

    /*
     * Delete a position by setting its deleted flag.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $position = Position::find($id);
        $position->deleted = 1;
        $position->save();

        event(new ModelChangedEvent($position, 'deleted'));

        return response()->json(['message' => 'Position deleted successfully']);
    }

    /**
     * Restore a position by resetting its deleted flag.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $position = Position::find($id);
        $position->deleted = 0;
        $position->save();

        event(new ModelChangedEvent($position, 'restored'));

        return response()->json(['message' => 'Position restored successfully']);
    }

    /*
     * Update an existing position.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $validatedData = $this->validateRequest();

        $position = Position::find($id);
        $position->fill($validatedData);
        $position->type = request('type');
        $position->updated_by = Auth::id();
        $position->save();

        event(new ModelChangedEvent($position, 'updated'));

        return response()->json(['message' => 'Position updated successfully']);
    }

    /*
     * Create a new position.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $validatedData = $this->validateRequest();

        $position = new Position();
        $position->fill($validatedData);
        $position->type = request('type');
        $position->created_by = Auth::id();
        $position->updated_by = Auth::id();
        $position->save();

        event(new ModelChangedEvent($position, 'created'));

        return response()->json(['message' => 'Position created successfully']);
    }

    /**
     * Validate the incoming request data for position creation or update.
     *
     * @return array
     */
    private function validateRequest()
    {
        return request()->validate([
            'name' => 'required|max:255',
        ],
        [
            'name.required' => 'Munkakör név kötelező',
            'name.max' => 'Munkakör név maximum 255 karakter lehet',
        ]);
    }
}