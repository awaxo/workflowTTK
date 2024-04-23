<?php

namespace App\Http\Controllers\pages;

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
                'created_by_name' => $position->createdBy->name,
                'updated_at' => $position->updated_at,
                'updated_by_name' => $position->updatedBy->name,
            ];
        });
        return response()->json(['data' => $positions]);
    }

    public function delete($id)
    {
        $position = Position::find($id);
        $position->deleted = 1;
        $position->save();
        return response()->json(['message' => 'Position deleted successfully']);
    }

    public function restore($id)
    {
        $position = Position::find($id);
        $position->deleted = 0;
        $position->save();
        return response()->json(['message' => 'Position restored successfully']);
    }

    public function update($id)
    {
        $position = Position::find($id);
        $position->name = request('name');
        $position->type = request('type');
        $position->updated_by = Auth::id();
        $position->save();
        return response()->json(['message' => 'Position updated successfully']);
    }

    public function create()
    {
        $position = new Position();
        $position->name = request('name');
        $position->type = request('type');
        $position->created_by = Auth::id();
        $position->updated_by = Auth::id();
        $position->save();
        return response()->json(['message' => 'Position created successfully']);
    }
}