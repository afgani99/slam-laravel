<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('profile.edit');
        }

        $users = User::all();
        $roles = User::ROLES;
        return view('settings.index', compact('users', 'roles'));
    }
}