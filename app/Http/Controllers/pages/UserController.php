<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return view('content.pages.users');
    }

    public function getAllUsers()
    {
        $users = User::all(['id', 'name', 'email', 'created_at']);

        return response()->json(['data' => $users]);
    }
}
