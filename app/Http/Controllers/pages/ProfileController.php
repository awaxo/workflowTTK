<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class ProfileController extends Controller
{
    public function index()
    {
        $service = new DelegationService();
        $delegations = $service->getAllDelegations(Auth::user());

        return view('content.pages.profile', compact('delegations'));
    }
}