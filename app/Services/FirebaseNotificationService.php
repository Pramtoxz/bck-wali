<?php

namespace App\Services;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Send notification to single device
     */
    public function sendToDevice(string $token, string $title, string $body, array $data = [])
    {
        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data)
                ->toToken($token);

            $result = $this->messaging->send($message);
            
            Log::info('Firebase notification sent', [
                'token' => $token,
                'title' => $title,
                'result' => $result
            ]);

            return [
                'success' => true,
                'result' => $result
            ];
        } catch (\Exception $e) {
            Log::error('Firebase notification failed', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to multiple devices
     */
    public function sendToMultipleDevices(array $tokens, string $title, string $body, array $data = [])
    {
        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $result = $this->messaging->sendMulticast($message, $tokens);
            
            Log::info('Firebase multicast notification sent', [
                'tokens_count' => count($tokens),
                'success_count' => $result->successes()->count(),
                'failure_count' => $result->failures()->count()
            ]);

            return [
                'success' => true,
                'success_count' => $result->successes()->count(),
                'failure_count' => $result->failures()->count(),
                'failures' => $result->failures()
            ];
        } catch (\Exception $e) {
            Log::error('Firebase multicast notification failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send data-only message (no notification)
     */
    public function sendDataMessage(string $token, array $data)
    {
        try {
            $message = CloudMessage::new()
                ->withData($data)
                ->toToken($token);

            $result = $this->messaging->send($message);
            
            return [
                'success' => true,
                'result' => $result
            ];
        } catch (\Exception $e) {
            Log::error('Firebase data message failed', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate FCM token
     */
    public function validateToken(string $token): bool
    {
        try {
            $message = CloudMessage::new()
                ->withData(['test' => 'validation'])
                ->toToken($token);

            $this->messaging->validate($message);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
