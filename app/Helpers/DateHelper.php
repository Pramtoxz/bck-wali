<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DateHelper
{
    /**
     * Get current date for testing purposes
     * Checks if there's a test_date in cache (for API) or session (for web)
     */
    public static function now(): Carbon
    {
        // Check if we're in testing mode
        if (!config('app.dev_mode', false)) {
            return Carbon::now();
        }

        // For API: Check cache with user ID
        if (auth('sanctum')->check()) {
            $userId = auth('sanctum')->id();
            $testDate = Cache::get("test_date_user_{$userId}");
            if ($testDate) {
                return Carbon::parse($testDate);
            }
        }

        // For Web: Check query parameter or session
        if (request()->has('test_date')) {
            $testDate = request()->get('test_date');
            
            // Save to session for web
            session(['test_date' => $testDate]);
            
            // Save to cache for API if authenticated
            if (auth()->check()) {
                $userId = auth()->id();
                Cache::put("test_date_user_{$userId}", $testDate, now()->addDays(7));
            }
            
            return Carbon::parse($testDate);
        }

        if (session()->has('test_date')) {
            return Carbon::parse(session('test_date'));
        }

        return Carbon::now();
    }

    /**
     * Get today's date string (Y-m-d format)
     */
    public static function today(): string
    {
        return self::now()->format('Y-m-d');
    }

    /**
     * Clear test date from session and cache
     */
    public static function clearTestDate(): void
    {
        session()->forget('test_date');
        
        // Clear from cache if authenticated
        if (auth()->check()) {
            $userId = auth()->id();
            Cache::forget("test_date_user_{$userId}");
        }
        
        if (auth('sanctum')->check()) {
            $userId = auth('sanctum')->id();
            Cache::forget("test_date_user_{$userId}");
        }
    }

    /**
     * Set test date for current user
     */
    public static function setTestDate(string $date): void
    {
        // Save to session for web
        session(['test_date' => $date]);
        
        // Save to cache for API
        if (auth()->check()) {
            $userId = auth()->id();
            Cache::put("test_date_user_{$userId}", $date, now()->addDays(7));
        }
        
        if (auth('sanctum')->check()) {
            $userId = auth('sanctum')->id();
            Cache::put("test_date_user_{$userId}", $date, now()->addDays(7));
        }
    }

    /**
     * Check if we're in test mode
     */
    public static function isTestMode(): bool
    {
        if (!config('app.dev_mode', false)) {
            return false;
        }

        // Check API cache
        if (auth('sanctum')->check()) {
            $userId = auth('sanctum')->id();
            if (Cache::has("test_date_user_{$userId}")) {
                return true;
            }
        }

        // Check web session
        return session()->has('test_date');
    }

    /**
     * Get test date info
     */
    public static function getTestDateInfo(): ?array
    {
        if (!self::isTestMode()) {
            return null;
        }

        $testDate = Carbon::parse(self::today());
        return [
            'date' => $testDate->format('Y-m-d'),
            'formatted' => $testDate->locale('id')->isoFormat('dddd, D MMMM YYYY'),
            'is_weekend' => $testDate->isWeekend(),
        ];
    }
}
