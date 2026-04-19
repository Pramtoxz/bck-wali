<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Update FCM token for authenticated user
     */
    public function updateToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->fcm_token = $validated['fcm_token'];
        $user->save();

        return ApiResponse::success(null, 'FCM token berhasil diperbarui');
    }

    /**
     * Send test notification
     */
    public function sendTest(Request $request)
    {
        $user = $request->user();

        if (!$user->fcm_token) {
            return ApiResponse::error('FCM token tidak ditemukan', null, 400);
        }

        $result = $this->firebaseService->sendToDevice(
            $user->fcm_token,
            'Test Notification',
            'Ini adalah test notification dari sistem absensi',
            [
                'type' => 'test',
                'timestamp' => now()->toIso8601String()
            ]
        );

        if ($result['success']) {
            return ApiResponse::success($result, 'Notifikasi test berhasil dikirim');
        }

        return ApiResponse::error('Gagal mengirim notifikasi', $result, 500);
    }
}
