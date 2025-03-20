<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Validation\Rule;

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

    public function checkGroupLevelUnique()
    {
        $groupLevel = request()->input('group_level');
        $instituteId = request()->input('institute_id');
        
        $query = Institute::where('group_level', $groupLevel)
            ->where('deleted', 0);
        
        if ($instituteId) {
            $query->where('id', '!=', $instituteId);
        }
        
        $exists = $query->exists();
        
        return response()->json(['valid' => !$exists]);
    }

    public function checkNameUnique()
    {
        $name = request()->input('name');
        $instituteId = request()->input('institute_id');
        
        $query = Institute::where('name', $name)
            ->where('deleted', 0);
        
        if ($instituteId) {
            $query->where('id', '!=', $instituteId);
        }
        
        $exists = $query->exists();
        
        return response()->json(['valid' => !$exists]);
    }

    public function checkAbbreviationUnique()
    {
        $abbreviation = request()->input('abbreviation');
        $instituteId = request()->input('institute_id');
        
        $query = Institute::where('abbreviation', $abbreviation)
            ->where('deleted', 0);
        
        if ($instituteId) {
            $query->where('id', '!=', $instituteId);
        }
        
        $exists = $query->exists();
        
        return response()->json(['valid' => !$exists]);
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
        $existingGroupLevels = Institute::where('deleted', 0);
        if (request()->input('id')) {
            $existingGroupLevels = $existingGroupLevels->where('id', '!=', request()->input('id'));
        }
        $existingGroupLevels = $existingGroupLevels->pluck('group_level')->toArray();
        
        $existingNames = Institute::where('deleted', 0);
        if (request()->input('id')) {
            $existingNames = $existingNames->where('id', '!=', request()->input('id'));
        }
        $existingNames = $existingNames->pluck('name')->toArray();
        
        $existingAbbreviations = Institute::where('deleted', 0);
        if (request()->input('id')) {
            $existingAbbreviations = $existingAbbreviations->where('id', '!=', request()->input('id'));
        }
        $existingAbbreviations = $existingAbbreviations->pluck('abbreviation')->toArray();

        return request()->validate([
            'name' => [
                'required',
                'max:255',
                'regex:/^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ\s,-]+$/',
                function (string $attribute, mixed $value, Closure $fail) use ($existingNames) {
                    if (in_array($value, $existingNames)) {
                        $fail("Az intézet neve már foglalt");
                    }
                },
            ],
            'abbreviation' => [
                'required',
                'max:5',
                'regex:/^[A-ZÁÉÍÓÖŐÚÜŰ]+$/',
                function (string $attribute, mixed $value, Closure $fail) use ($existingAbbreviations) {
                    if (in_array($value, $existingAbbreviations)) {
                        $fail("Az intézet rövidítése már foglalt");
                    }
                },
            ],
            'group_level' => [
                'required',
                'integer',
                'min:1',
                'max:9',
                function (string $attribute, mixed $value, Closure $fail) use ($existingGroupLevels) {
                    if (in_array($value, $existingGroupLevels)) {
                        $fail("Az intézet száma már foglalt");
                    }
                },
            ],
        ], [
            'name.required' => 'Az intézet neve kötelező',
            'name.max' => 'Az intézet neve maximum 255 karakter lehet',
            'name.regex' => 'Az intézet neve csak betűket, szóközt, vesszőt és kötőjelet tartalmazhat',
            'abbreviation.required' => 'Az intézet rövidítése kötelező',
            'abbreviation.max' => 'Az intézet rövidítése maximum 5 karakter lehet',
            'abbreviation.regex' => 'Az intézet rövidítése csak nagybetűket tartalmazhat',
            'group_level.required' => 'Az intézet száma kötelező',
            'group_level.integer' => 'Az intézet száma csak egész szám lehet',
            'group_level.min' => 'Az intézet száma minimum 1 lehet',
            'group_level.max' => 'Az intézet száma maximum 9 lehet',
        ]);
    }
}
