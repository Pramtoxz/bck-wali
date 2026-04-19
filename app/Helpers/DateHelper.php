<?php

namespace App\Helpers;

use App\Models\TestDate;
use Carbon\Carbon;

class DateHelper
{
    public static function now(): Carbon
    {
        if (!config('app.dev_mode', false)) {
            return Carbon::now();
        }

        $testDate = TestDate::first();
        if ($testDate) {
            return Carbon::parse($testDate->test_date);
        }

        return Carbon::now();
    }

    public static function today(): string
    {
        return self::now()->format('Y-m-d');
    }

    public static function setTestDate(string $date): void
    {
        TestDate::truncate();
        TestDate::create(['test_date' => $date]);
    }

    public static function clearTestDate(): void
    {
        TestDate::truncate();
    }

    public static function isTestMode(): bool
    {
        if (!config('app.dev_mode', false)) {
            return false;
        }

        return TestDate::exists();
    }

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
