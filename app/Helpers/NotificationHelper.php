<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseNotificationService;

class NotificationHelper
{
    public static function sendFieldDutyStatusNotification($fieldDuty, $status)
    {
        $user = $fieldDuty->user;
        
        $statusText = $status === 'approved' ? 'disetujui' : 'ditolak';
        $title = 'Status Dinas Luar';
        $body = "Pengajuan dinas luar Anda ke {$fieldDuty->destination} telah {$statusText}";
        
        $data = [
            'type' => 'field_duty_status',
            'field_duty_id' => (string) $fieldDuty->id,
            'status' => $status,
            'destination' => $fieldDuty->destination,
        ];

        Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => 'field_duty',
            'data' => $data,
        ]);

        if ($user->fcm_token) {
            $firebaseService = app(FirebaseNotificationService::class);
            $firebaseService->sendToDevice($user->fcm_token, $title, $body, $data);
        }
    }

    public static function sendLeaveStatusNotification($leave, $status)
    {
        $user = $leave->user;
        
        $statusText = $status === 'approved' ? 'disetujui' : 'ditolak';
        $title = 'Status Izin/Cuti';
        $body = "Pengajuan {$leave->type} Anda telah {$statusText}";
        
        $data = [
            'type' => 'leave_status',
            'leave_id' => (string) $leave->id,
            'status' => $status,
            'leave_type' => $leave->type,
        ];

        Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => 'leave',
            'data' => $data,
        ]);

        if ($user->fcm_token) {
            $firebaseService = app(FirebaseNotificationService::class);
            $firebaseService->sendToDevice($user->fcm_token, $title, $body, $data);
        }
    }

    public static function sendCheckInReminder(User $user)
    {
        $title = 'Reminder Absen Masuk';
        $body = 'Jangan lupa untuk melakukan absen masuk hari ini';
        
        $data = [
            'type' => 'check_in_reminder',
            'timestamp' => now()->toIso8601String(),
        ];

        Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => 'attendance',
            'data' => $data,
        ]);

        if ($user->fcm_token) {
            $firebaseService = app(FirebaseNotificationService::class);
            $firebaseService->sendToDevice($user->fcm_token, $title, $body, $data);
        }
    }

    public static function sendCheckOutReminder(User $user)
    {
        $title = 'Reminder Absen Keluar';
        $body = 'Jangan lupa untuk melakukan absen keluar sebelum pulang';
        
        $data = [
            'type' => 'check_out_reminder',
            'timestamp' => now()->toIso8601String(),
        ];

        Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => 'attendance',
            'data' => $data,
        ]);

        if ($user->fcm_token) {
            $firebaseService = app(FirebaseNotificationService::class);
            $firebaseService->sendToDevice($user->fcm_token, $title, $body, $data);
        }
    }

    public static function sendBroadcast($title, $body, $data = [])
    {
        $users = User::whereNotNull('fcm_token')->get();
        $tokens = $users->pluck('fcm_token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $firebaseService = app(FirebaseNotificationService::class);
        $firebaseService->sendToMultipleDevices($tokens, $title, $body, $data);
    }
}
