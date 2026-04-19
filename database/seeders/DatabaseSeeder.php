<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            PositionSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            OfficeLocationSeeder::class,
            // AttendanceSeeder::class,
            // FieldDutySeeder::class,
            // LeaveSeeder::class,
        ]);
    }
}
