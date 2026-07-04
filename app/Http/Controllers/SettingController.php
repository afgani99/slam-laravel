<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $users = User::all();
        $roles = User::ROLES;
        return view('settings.index', compact('users', 'roles'));
    }
}