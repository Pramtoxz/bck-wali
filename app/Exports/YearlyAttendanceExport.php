<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class YearlyAttendanceExport implements WithMultipleSheets
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        $sheets[] = new YearlySummarySheet($this->year);
        
        for ($month = 1; $month <= 12; $month++) {
            $monthString = $this->year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
            $sheets[] = new AllEmployeesSummarySheet($monthString);
        }
        
        return $sheets;
    }
}
