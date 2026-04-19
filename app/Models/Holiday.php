<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'type',
        'description',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Cek apakah tanggal tertentu adalah hari libur
     */
    public static function isHoliday($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        
        // Cek weekend (Sabtu = 6, Minggu = 0)
        if (in_array($date->dayOfWeek, [0, 6])) {
            return [
                'is_holiday' => true,
                'holiday_name' => $date->dayOfWeek === 0 ? 'Hari Minggu' : 'Hari Sabtu',
                'type' => 'weekend'
            ];
        }
        
        // Cek tanggal merah dari database
        $holiday = self::where('date', $date->format('Y-m-d'))
            ->where('is_active', true)
            ->first();
        
        if ($holiday) {
            return [
                'is_holiday' => true,
                'holiday_name' => $holiday->name,
                'type' => $holiday->type,
                'description' => $holiday->description
            ];
        }
        
        return [
            'is_holiday' => false,
            'holiday_name' => null,
            'type' => null
        ];
    }

    /**
     * Get all active holidays for a specific year
     */
    public static function getYearHolidays($year = null)
    {
        $year = $year ?? Carbon::now()->year;
        
        return self::whereYear('date', $year)
            ->where('is_active', true)
            ->orderBy('date')
            ->get();
    }
}
