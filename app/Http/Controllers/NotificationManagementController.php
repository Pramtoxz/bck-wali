<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class NotificationManagementController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 15);
        $type = $request->query('type');

        $notifications = Notification::with('user:id,name,nip')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ilike', "%{$search}%")
                        ->orWhere('body', 'ilike', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'ilike', "%{$search}%");
                        });
                });
            })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->latest()
            ->paginate($perPage);

        $summary = [
            'total' => Notification::count(),
            'broadcast' => Notification::where('is_broadcast', true)->count(),
            'read' => Notification::where('is_read', true)->count(),
            'unread' => Notification::where('is_read', false)->count(),
        ];

        return Inertia::render('notifications/index', [
            'notifications' => $notifications,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'type' => $type,
            ],
        ]);
    }

    public function create()
    {
        $users = User::role('user')
            ->select('id', 'name', 'nip')
            ->orderBy('name')
            ->get();

        return Inertia::render('notifications/create', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string',
                'type' => 'required|in:general,attendance,leave,field_duty,announcement',
                'send_to' => 'required|in:all,specific',
                'user_ids' => 'required_if:send_to,specific|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            $users = $validated['send_to'] === 'all'
                ? User::role('user')->get()
                : User::whereIn('id', $validated['user_ids'])->get();

            if ($users->isEmpty()) {
                return redirect()->back()
                    ->withErrors(['user_ids' => 'Tidak ada user yang dipilih atau user tidak memiliki role user'])
                    ->withInput();
            }

            $successCount = 0;
            $failCount = 0;

            foreach ($users as $user) {
                try {
                    Notification::create([
                        'user_id' => $user->id,
                        'title' => $validated['title'],
                        'body' => $validated['body'],
                        'type' => $validated['type'],
                        'is_broadcast' => $validated['send_to'] === 'all',
                    ]);

                    if ($user->fcm_token) {
                        $result = $this->firebaseService->sendToDevice(
                            $user->fcm_token,
                            $validated['title'],
                            $validated['body'],
                            [
                                'type' => $validated['type'],
                                'timestamp' => now()->toIso8601String()
                            ]
                        );

                        if ($result['success']) {
                            $successCount++;
                        } else {
                            $failCount++;
                        }
                    } else {
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error creating notification for user ' . $user->id . ': ' . $e->getMessage());
                    $failCount++;
                }
            }

            return redirect()->route('notifications.index')
                ->with('success', "Notifikasi berhasil dikirim ke {$successCount} user. Gagal: {$failCount}");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error in notification store: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'Notifikasi berhasil dihapus');
    }
}
