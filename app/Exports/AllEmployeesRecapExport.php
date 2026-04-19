<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllEmployeesRecapExport implements WithMultipleSheets
{
    protected $month;

    public function __construct($month)
    {
        $this->month = $month;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        $sheets[] = new AllEmployeesSummarySheet($this->month);
        
        $users = User::orderBy('name')->get();
        
        foreach ($users as $user) {
            $sheets[] = new AttendanceRecapExport($user->id, $this->month);
        }
        
        return $sheets;
    }
}
