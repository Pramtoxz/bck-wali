<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        $query = User::with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereLike('name', "%{$search}%")
                  ->orWhereLike('username', "%{$search}%")
                  ->orWhereLike('email', "%{$search}%")
                  ->orWhereLike('nip', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($perPage)
                       ->withQueryString()
                       ->through(fn ($user) => [
                           'id' => $user->id,
                           'username' => $user->username,
                           'name' => $user->name,
                           'email' => $user->email,
                           'nip' => $user->nip,
                           'position' => $user->position,
                           'department' => $user->department,
                           'role' => $user->getRoleNames()->first(),
                           'created_at' => $user->created_at->format('d M Y'),
                       ]);

        return Inertia::render('users/index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        $positions = \App\Models\Position::orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();

        return Inertia::render('users/create', [
            'positions' => $positions,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'nip' => 'required|string|unique:users,nip|max:255',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nip' => $validated['nip'],
            'position' => $validated['position'],
            'department' => $validated['department'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function edit(string $id): Response
    {
        $user = User::with('roles')->findOrFail($id);
        $positions = \App\Models\Position::orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();

        return Inertia::render('users/edit', [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip,
                'position' => $user->position,
                'department' => $user->department,
                'role' => $user->getRoleNames()->first(),
            ],
            'positions' => $positions,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nip' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,user',
        ]);

        $user->update([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nip' => $validated['nip'],
            'position' => $validated['position'],
            'department' => $validated['department'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate');
    }

    public function destroy(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus user yang sedang login');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}
