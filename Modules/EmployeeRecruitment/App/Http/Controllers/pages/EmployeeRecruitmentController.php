<?php

namespace Modules\EmployeeRecruitment\App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeRecruitmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('employeerecruitment::content.pages.new-employee-recruitment');
    }
}
