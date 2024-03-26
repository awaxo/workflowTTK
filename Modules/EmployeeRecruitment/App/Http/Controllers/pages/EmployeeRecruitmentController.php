<?php

namespace Modules\EmployeeRecruitment\App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Workgroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeRecruitmentController extends Controller
{
    public function index()
    {
        $workgroups1 = Workgroup::all();
        $workgroups2 = Workgroup::where('workgroup_number', '!=', 800)->get();
        
        return view('employeerecruitment::content.pages.new-employee-recruitment', [
            'workgroups1' => $workgroups1,
            'workgroups2' => $workgroups2
        ]);
    }
}
