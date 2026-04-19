<?php

namespace App\Http\Controllers;

use App\Models\UserVisit;
use App\Models\UserAction;
use App\Models\UserAuthentication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    public function visits(Request $request)
    {
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 15);
        $dateFrom = $request->query('date_from', \App\Helpers\DateHelper::today());
        $dateTo = $request->query('date_to', \App\Helpers\DateHelper::today());

        $visits = UserVisit::with('user:id,name,nip,avatar')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('url', 'ilike', "%{$search}%")
                        ->orWhere('ip_address', 'ilike', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'ilike', "%{$search}%")
                                ->orWhere('nip', 'ilike', "%{$search}%");
                        });
                });
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate($perPage);

        $summary = [
            'total_visits' => UserVisit::count(),
            'today_visits' => UserVisit::whereDate('created_at', today())->count(),
            'unique_users' => UserVisit::distinct('user_id')->whereNotNull('user_id')->count('user_id'),
            'guest_visits' => UserVisit::whereNull('user_id')->count(),
        ];

        return Inertia::render('monitoring/visits', [
            'visits' => $visits,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function actions(Request $request)
    {
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 15);
        $action = $request->query('action');
        $dateFrom = $request->query('date_from', \App\Helpers\DateHelper::today());
        $dateTo = $request->query('date_to', \App\Helpers\DateHelper::today());

        $actions = UserAction::with('user:id,name,nip,avatar')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('model_type', 'ilike', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'ilike', "%{$search}%")
                                ->orWhere('nip', 'ilike', "%{$search}%");
                        });
                });
            })
            ->when($action, function ($query) use ($action) {
                $query->where('action', $action);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate($perPage);

        $summary = [
            'total_actions' => UserAction::count(),
            'today_actions' => UserAction::whereDate('created_at', today())->count(),
            'create_actions' => UserAction::where('action', 'create')->count(),
            'update_actions' => UserAction::where('action', 'update')->count(),
            'delete_actions' => UserAction::where('action', 'delete')->count(),
        ];

        return Inertia::render('monitoring/actions', [
            'actions' => $actions,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'action' => $action,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function authentications(Request $request)
    {
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 15);
        $action = $request->query('action');
        $dateFrom = $request->query('date_from', \App\Helpers\DateHelper::today());
        $dateTo = $request->query('date_to', \App\Helpers\DateHelper::today());

        $authentications = UserAuthentication::with('user:id,name,nip,avatar')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'ilike', "%{$search}%")
                        ->orWhere('nip', 'ilike', "%{$search}%");
                });
            })
            ->when($action, function ($query) use ($action) {
                $query->where('action', $action);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate($perPage);

        $summary = [
            'total_authentications' => UserAuthentication::count(),
            'today_logins' => UserAuthentication::where('action', 'login')->whereDate('created_at', today())->count(),
            'total_logins' => UserAuthentication::where('action', 'login')->count(),
            'total_logouts' => UserAuthentication::where('action', 'logout')->count(),
        ];

        return Inertia::render('monitoring/authentications', [
            'authentications' => $authentications,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'action' => $action,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
