<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAuthentication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error('Username atau password salah', null, 401);
        }

        // Blokir admin dari login ke API
        if ($user->hasRole('admin')) {
            return ApiResponse::error('Admin tidak memiliki akses ke Aplikasi , Silahkan Login Menggunakan Akun Pegawai', null, 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        // Track login
        $this->trackAuthentication($request, $user, 'login');

        return ApiResponse::success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip,
                'position' => $user->position,
                'department' => $user->department,
                'avatar' => $user->avatar ?? asset('images/logo-transparan.png'),
                'role' => $user->getRoleNames()->first(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ], 'Login berhasil');
    }

    public function logout(Request $request): JsonResponse
    {
        // Track logout
        $this->trackAuthentication($request, $request->user(), 'logout');

        $request->user()->currentAccessToken()->delete();
        
        return ApiResponse::success(null, 'Logout berhasil');
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return ApiResponse::success([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'nip' => $user->nip,
            'position' => $user->position,
            'department' => $user->department,
            'avatar' => $user->avatar ?? asset('images/logo-transparan.png'),
            'role' => $user->getRoleNames()->first(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    private function trackAuthentication(Request $request, User $user, string $action): void
    {
        try {
            $agent = new Agent();
            $agent->setUserAgent($request->userAgent());

            UserAuthentication::create([
                'user_id' => $user->id,
                'action' => $action,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device' => $this->getDevice($agent),
                'platform' => $agent->platform(),
                'browser' => $agent->browser(),
                'authenticated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Authentication tracking failed: ' . $e->getMessage());
        }
    }

    private function getDevice(Agent $agent): string
    {
        if ($agent->isDesktop()) {
            return 'Desktop';
        } elseif ($agent->isTablet()) {
            return 'Tablet';
        } elseif ($agent->isMobile()) {
            return 'Mobile';
        }
        return 'Unknown';
    }
}
