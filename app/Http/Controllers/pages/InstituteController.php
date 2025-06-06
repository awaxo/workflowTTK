<?php

namespace App\Http\Controllers\pages;

use App\Events\ModelChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Role;
use App\Models\Workgroup;
use App\Services\RoleService;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Support\Facades\Log;
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
        $groupLevel = request()->input('cleaned_group_level');
        $instituteId = request()->input('institute_id');
        
        // If we're checking the same institute's current group_level, it's valid
        if ($instituteId) {
            $currentInstitute = Institute::find($instituteId);
            if ($currentInstitute && $currentInstitute->group_level == $groupLevel) {
                return response()->json(['valid' => true]);
            }
        }
        
        $exists = Institute::where('group_level', $groupLevel)
                        ->where('deleted', 0)
                        ->when($instituteId, function ($query) use ($instituteId) {
                            return $query->where('id', '!=', $instituteId);
                        })
                        ->exists();
        
        return response()->json(['valid' => !$exists]);
    }

    public function checkNameUnique()
    {
        $name = request()->input('name');
        $instituteId = request()->input('institute_id');
        
        // If we're checking the same institute's current name, it's valid
        if ($instituteId) {
            $currentInstitute = Institute::find($instituteId);
            if ($currentInstitute && $currentInstitute->name === $name) {
                return response()->json(['valid' => true]);
            }
        }
        
        $exists = Institute::where('name', $name)
                        ->where('deleted', 0)
                        ->when($instituteId, function ($query) use ($instituteId) {
                            return $query->where('id', '!=', $instituteId);
                        })
                        ->exists();
        
        return response()->json(['valid' => !$exists]);
    }

    public function checkAbbreviationUnique()
    {
        $abbreviation = trim(request()->input('abbreviation'));
        $instituteId = request()->input('institute_id');

        if (mb_strlen($abbreviation) > 5) {
            return response()->json(['valid' => false]);
        }

        // If we're checking the same institute's current abbreviation, it's valid
        if ($instituteId) {
            $currentInstitute = Institute::find($instituteId);
            if ($currentInstitute && $currentInstitute->abbreviation === $abbreviation) {
                return response()->json(['valid' => true]);
            }
        }

        $exists = Institute::where('abbreviation', $abbreviation)
                        ->where('deleted', 0)
                        ->when($instituteId, function ($query) use ($instituteId) {
                            return $query->where('id', '!=', $instituteId);
                        })
                        ->exists();

        return response()->json(['valid' => !$exists]);
    }

    public function delete($id)
    {
        $institute = Institute::findOrFail($id);
    
        // Check if group_level is 9 - cannot be deleted
        if ($institute->group_level === 9) {
            return response()->json([
                'message' => 'Ez az intézet nem törölhető.'
            ], 403);
        }
        
        $institute->deleted = 1;
        $institute->save();

        event(new ModelChangedEvent($institute, 'deleted'));

        return response()->json(['message' => 'Institute deleted successfully']);
    }

    public function restore($id)
    {
        $institute = Institute::findOrFail($id);
        
        // Check if group_level is 9 - cannot be restored (though it shouldn't be deleted in first place)
        if ($institute->group_level === 9) {
            return response()->json([
                'message' => 'Ez az intézet nem állítható vissza.'
            ], 403);
        }

        $conflictExists = Institute::where('deleted', 0)
            ->where('id', '!=', $institute->id)
            ->where(function ($q) use ($institute) {
                $q->where('name', $institute->name)
                ->orWhere('abbreviation', $institute->abbreviation)
                ->orWhere('group_level', $institute->group_level);
            })
            ->exists();

        if ($conflictExists) {
            return response()->json([
                'message' => 'Nem állítható vissza, mert ütközés van egy meglévő intézettel (azonos név, rövidítés vagy számszint).'
            ], 422);
        }

        $institute->deleted = 0;
        $institute->save();

        event(new ModelChangedEvent($institute, 'restored'));

        return response()->json(['message' => 'Intézet sikeresen visszaállítva']);
    }

    public function update($id)
    {
        $institute = Institute::findOrFail($id);
        
        // Check if group_level is 9 - cannot be modified
        if ($institute->group_level === 9) {
            return response()->json([
                'message' => 'Ez az intézet nem módosítható.'
            ], 403);
        }
        
        $validatedData = $this->validateRequest();

        $institute->fill($validatedData);
        $institute->updated_by = Auth::id();
        $institute->save();

        event(new ModelChangedEvent($institute, 'updated'));

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

        event(new ModelChangedEvent($institute, 'created'));
        
        return response()->json(['message' => 'Institute created successfully']);
    }

    private function validateRequest()
    {
        return request()->validate([
            'name' => [
                'required',
                'max:255',
                'regex:/^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ\s,-]+$/',
                Rule::unique('wf_institute', 'name')
                    ->where('deleted', 0)
                    ->ignore(request()->input('id')),
            ],
            'abbreviation' => [
                'required',
                'max:5',
                'regex:/^[A-ZÁÉÍÓÖŐÚÜŰ]+$/',
                Rule::unique('wf_institute', 'abbreviation')
                    ->where('deleted', 0)
                    ->ignore(request()->input('id')),
            ],
            'group_level' => [
                'required',
                'integer',
                'min:1',
                'max:9',
                Rule::unique('wf_institute', 'group_level')
                    ->where('deleted', 0)
                    ->ignore(request()->input('id')),
            ],
        ], [
            'name.required' => 'Az intézet neve kötelező',
            'name.max' => 'Az intézet neve maximum 255 karakter lehet',
            'name.regex' => 'Az intézet neve csak betűket, szóközt, vesszőt és kötőjelet tartalmazhat',
            'name.unique' => 'Az intézet neve már foglalt',
            'abbreviation.required' => 'Az intézet rövidítése kötelező',
            'abbreviation.max' => 'Az intézet rövidítése maximum 5 karakter lehet',
            'abbreviation.regex' => 'Az intézet rövidítése csak nagybetűket tartalmazhat',
            'abbreviation.unique' => 'Az intézet rövidítése már foglalt',
            'group_level.required' => 'Az intézet száma kötelező',
            'group_level.integer' => 'Az intézet száma csak egész szám lehet',
            'group_level.min' => 'Az intézet száma minimum 1 lehet',
            'group_level.max' => 'Az intézet száma maximum 9 lehet',
            'group_level.unique' => 'Az intézet száma már foglalt',
        ]);
    }
}
