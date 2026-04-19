<?php

namespace App\Traits;

use App\Models\UserAction;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait Actionable
{
    public static function bootActionable()
    {
        static::created(function ($model) {
            self::logAction('create', $model);
        });

        static::updated(function ($model) {
            self::logAction('update', $model, $model->getChanges());
        });

        static::deleted(function ($model) {
            self::logAction('delete', $model);
        });
    }

    protected static function logAction(string $action, $model, ?array $changes = null): void
    {
        try {
            $request = Request::instance();
            if (!$request->is('api/*')) {
                return;
            }

            UserAction::create([
                'user_id' => Auth::check() ? Auth::id() : null,
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'changes' => $changes ? json_encode($changes) : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Action tracking failed: ' . $e->getMessage());
        }
    }
}
