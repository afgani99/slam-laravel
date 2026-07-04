<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    use LogsActivity;

    public function create(): View|RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('profile.edit');
        }

        $roles = User::ROLES;
        return view('settings.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('profile.edit');
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,operator,viewer'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $this->logActivity('create', 'Menambahkan user '.$user->name, $user);

        return redirect()->route('settings.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View|RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('profile.edit');
        }

        $roles = User::ROLES;
        return view('settings.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('profile.edit');
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,operator,viewer'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $this->logActivity('update', 'Memperbarui user '.$user->name, $user);

        return redirect()->route('settings.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('profile.edit');
        }
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $userName = $user->name;
        $this->logActivity('delete', 'Menghapus user '.$userName, $user);

        $user->delete();
        return redirect()->route('settings.index')->with('success', 'User berhasil dihapus.');
    }
}
