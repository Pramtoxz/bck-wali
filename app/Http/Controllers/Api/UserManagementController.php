<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search', '');

        $query = User::with('roles');

        if ($search) {
            $query->whereLike('name', "%{$search}%")
                  ->orWhereLike('username', "%{$search}%")
                  ->orWhereLike('email', "%{$search}%")
                  ->orWhereLike('nip', "%{$search}%");
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($perPage)
                       ->withQueryString();

        return ApiResponse::success([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
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

        return ApiResponse::success([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'nip' => $user->nip,
            'position' => $user->position,
            'department' => $user->department,
            'role' => $user->getRoleNames()->first(),
        ], 'User berhasil ditambahkan', 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::with('roles')->findOrFail($id);

        return ApiResponse::success([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'nip' => $user->nip,
            'position' => $user->position,
            'department' => $user->department,
            'avatar' => $user->avatar ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTzZUspXcWeUL8UQz6YcSWiW6RCzLJf8JvrTA&s',
            'role' => $user->getRoleNames()->first(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
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

        return ApiResponse::success([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'nip' => $user->nip,
            'position' => $user->position,
            'department' => $user->department,
            'role' => $user->getRoleNames()->first(),
        ], 'User berhasil diupdate');
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return ApiResponse::error('Tidak dapat menghapus user yang sedang login', null, 400);
        }

        $user->delete();

        return ApiResponse::success(null, 'User berhasil dihapus');
    }
}
