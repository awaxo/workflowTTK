<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;

class InstituteController extends Controller
{
    public function index()
    {
        $institutes = Institute::where('deleted', 0)->get()->map(function ($institute) {
            $workgroupCount = Workgroup::where('workgroup_number', 'like', $institute->group_level . '%')->where('deleted', 0)->count();
            $institute->workgroup_count = $workgroupCount;

            return $institute;
        });

        return view('content.pages.institutes', compact('institutes'));
    }

    public function manage()
    {
        return view('content.pages.institutes-manage');
    }

    public function getAllInstitutes()
    {
        $institutes = Institute::all()->map(function ($institute) {
            return [
                'id' => $institute->id,
                'name' => $institute->name,
                'abbreviation' => $institute->abbreviation,
                'group_level' => $institute->group_level,
                'deleted' => $institute->deleted,
                'created_at' => $institute->created_at,
                'created_by_name' => optional($institute->createdBy)->name ?? 'Technikai felhasználó',
                'updated_at' => $institute->updated_at,
                'updated_by_name' => optional($institute->updatedBy)->name ?? 'Technikai felhasználó',
            ];
        });
        return response()->json(['data' => $institutes]);
    }

    public function delete($id)
    {
        $institute = Institute::find($id);
        $institute->deleted = 1;
        $institute->save();
        return response()->json(['message' => 'Institute deleted successfully']);
    }

    public function restore($id)
    {
        $institute = Institute::find($id);
        $institute->deleted = 0;
        $institute->save();
        return response()->json(['message' => 'Institute restored successfully']);
    }

    public function update($id)
    {
        $validatedData = $this->validateRequest();

        $institute = Institute::find($id);
        $institute->fill($validatedData);
        $institute->updated_by = Auth::id();
        $institute->save();

        return response()->json(['message' => 'Institute updated successfully']);
    }

    public function create()
    {
        $validatedData = $this->validateRequest();

        $institute = new Institute();
        $institute->fill($validatedData);
        $institute->created_by = Auth::id();
        $institute->updated_by = Auth::id();
        $institute->save();
        
        return response()->json(['message' => 'Institute created successfully']);
    }

    private function validateRequest()
    {
        return request()->validate([
            'name' => 'required|max:255',
            'abbreviation' => 'required|max:255',
            'group_level' => 'required',
        ], [
            'name.required' => 'Intézet név kötelező',
            'name.max' => 'Intézet név maximum 255 karakter lehet',
            'abbreviation.required' => 'Intézet rövidítés kötelező',
            'abbreviation.max' => 'Intézet rövidítés maximum 255 karakter lehet',
            'group_level.required' => 'Intézet szám kötelező',
        ]);
    }
}
