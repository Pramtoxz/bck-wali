<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function checkToday(Request $request): JsonResponse
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $holidayCheck = Holiday::isHoliday($date);

        return ApiResponse::success([
            'date' => $date,
            'is_holiday' => $holidayCheck['is_holiday'],
            'holiday_name' => $holidayCheck['holiday_name'],
            'type' => $holidayCheck['type'],
            'description' => $holidayCheck['description'] ?? null,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $year = $request->query('year', Carbon::now()->year);
        
        $holidays = Holiday::whereYear('date', $year)
            ->where('is_active', true)
            ->orderBy('date')
            ->get();

        return ApiResponse::success($holidays);
    }
}
