<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    public function manage()
    {
        return view('content.pages.positions');
    }

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

    public function delete($id)
    {
        $position = Position::find($id);
        $position->deleted = 1;
        $position->save();

        event(new ModelChangedEvent($position, 'deleted'));

        return response()->json(['message' => 'Position deleted successfully']);
    }

    public function restore($id)
    {
        $position = Position::find($id);
        $position->deleted = 0;
        $position->save();

        event(new ModelChangedEvent($position, 'restored'));

        return response()->json(['message' => 'Position restored successfully']);
    }

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