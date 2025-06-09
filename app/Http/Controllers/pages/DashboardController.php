<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * DashboardController handles the dashboard page.
 * 
 * This controller is responsible for rendering the dashboard view.
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('content.pages.dashboard');
    }
}
