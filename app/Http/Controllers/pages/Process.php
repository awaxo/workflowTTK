<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Process extends Controller
{
    public function index()
    {
        return view('content.pages.new-process');
    }
}
