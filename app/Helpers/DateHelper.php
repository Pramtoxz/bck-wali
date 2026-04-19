<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Get current date for testing purposes
     * Checks if there's a test_date in session or query parameter
     */
    public static function now(): Carbon
    {
        // Check if we're in testing mode via query parameter or session
        if (request()->has('test_date')) {
            $testDate = request()->get('test_date');
            session(['test_date' => $testDate]);
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
     * Clear test date from session
     */
    public static function clearTestDate(): void
    {
        session()->forget('test_date');
    }

    /**
     * Check if we're in test mode
     */
    public static function isTestMode(): bool
    {
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

        $testDate = Carbon::parse(session('test_date'));
        return [
            'date' => $testDate->format('Y-m-d'),
            'formatted' => $testDate->locale('id')->isoFormat('dddd, D MMMM YYYY'),
            'is_weekend' => $testDate->isWeekend(),
        ];
    }
}
