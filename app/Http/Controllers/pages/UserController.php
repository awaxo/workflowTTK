<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index()
    {
        return view('content.pages.list-users');
    }

    public function getAllUsers()
    {
        $users = User::all(['id', 'name', 'email', 'created_at']);

        return response()->json(['data' => $users]);
    }
}
