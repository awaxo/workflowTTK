<?php

namespace Modules\EmployeeRecruitment\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class FileController extends Controller
{
    public function delete(Request $request)
    {
        $recruitmentId = $request->input('recruitment_id');
        $type = $request->input('type');

        $recruitment = RecruitmentWorkflow::find($recruitmentId);
        $recruitment->$type = '';
        $recruitment->save();

        $filename = $request->input('filename');
        if (!$filename) {
            return response()->json(['error' => 'Filename is required'], 400);
        }

        $path = storage_path('app/public/uploads/' . $filename);
        if (file_exists($path)) {
            unlink($path);
            return response()->json(['message' => 'File deleted'], 200);
        }

        return response()->json(['error' => 'File not found'], 404);
    }
}
