<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index(Request $request)
    {
        $perPage = min($request->query('per_page', 20), 30);
        $type = $request->query('type');

        $notifications = Notification::where('user_id', $request->user()->id)
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->latest()
            ->take(30)
            ->paginate($perPage);

        return ApiResponse::success($notifications);
    }

    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return ApiResponse::success(['unread_count' => $count]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return ApiResponse::success($notification, 'Notifikasi ditandai sudah dibaca');
    }

    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return ApiResponse::success(null, 'Semua notifikasi ditandai sudah dibaca');
    }

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

