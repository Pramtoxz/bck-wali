<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    public function run()
    {
        $holidays = [
            // 2024
            ['date' => '2024-01-01', 'name' => 'Tahun Baru 2024', 'type' => 'national'],
            ['date' => '2024-02-08', 'name' => 'Isra Mikraj Nabi Muhammad SAW', 'type' => 'national'],
            ['date' => '2024-02-10', 'name' => 'Tahun Baru Imlek 2575', 'type' => 'national'],
            ['date' => '2024-03-11', 'name' => 'Hari Suci Nyepi', 'type' => 'national'],
            ['date' => '2024-03-29', 'name' => 'Wafat Isa Al Masih', 'type' => 'national'],
            ['date' => '2024-04-10', 'name' => 'Hari Raya Idul Fitri 1445 H', 'type' => 'national'],
            ['date' => '2024-04-11', 'name' => 'Hari Raya Idul Fitri 1445 H', 'type' => 'national'],
            ['date' => '2024-05-01', 'name' => 'Hari Buruh Internasional', 'type' => 'national'],
            ['date' => '2024-05-09', 'name' => 'Kenaikan Isa Al Masih', 'type' => 'national'],
            ['date' => '2024-05-23', 'name' => 'Hari Raya Waisak 2568', 'type' => 'national'],
            ['date' => '2024-06-01', 'name' => 'Hari Lahir Pancasila', 'type' => 'national'],
            ['date' => '2024-06-17', 'name' => 'Hari Raya Idul Adha 1445 H', 'type' => 'national'],
            ['date' => '2024-07-07', 'name' => 'Tahun Baru Islam 1446 H', 'type' => 'national'],
            ['date' => '2024-08-17', 'name' => 'Hari Kemerdekaan RI', 'type' => 'national'],
            ['date' => '2024-09-16', 'name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national'],
            ['date' => '2024-12-25', 'name' => 'Hari Raya Natal', 'type' => 'national'],
            
            // 2025
            ['date' => '2025-01-01', 'name' => 'Tahun Baru 2025', 'type' => 'national'],
            ['date' => '2025-01-29', 'name' => 'Tahun Baru Imlek 2576', 'type' => 'national'],
            ['date' => '2025-03-29', 'name' => 'Hari Suci Nyepi', 'type' => 'national'],
            ['date' => '2025-03-31', 'name' => 'Hari Raya Idul Fitri 1446 H', 'type' => 'national'],
            ['date' => '2025-04-01', 'name' => 'Hari Raya Idul Fitri 1446 H', 'type' => 'national'],
            ['date' => '2025-04-18', 'name' => 'Wafat Isa Al Masih', 'type' => 'national'],
            ['date' => '2025-05-01', 'name' => 'Hari Buruh Internasional', 'type' => 'national'],
            ['date' => '2025-05-12', 'name' => 'Hari Raya Waisak 2569', 'type' => 'national'],
            ['date' => '2025-05-29', 'name' => 'Kenaikan Isa Al Masih', 'type' => 'national'],
            ['date' => '2025-06-01', 'name' => 'Hari Lahir Pancasila', 'type' => 'national'],
            ['date' => '2025-06-07', 'name' => 'Hari Raya Idul Adha 1446 H', 'type' => 'national'],
            ['date' => '2025-06-27', 'name' => 'Tahun Baru Islam 1447 H', 'type' => 'national'],
            ['date' => '2025-08-17', 'name' => 'Hari Kemerdekaan RI', 'type' => 'national'],
            ['date' => '2025-09-05', 'name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national'],
            ['date' => '2025-12-25', 'name' => 'Hari Raya Natal', 'type' => 'national'],
            
            // 2026
            ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026', 'type' => 'national'],
            ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577', 'type' => 'national'],
            ['date' => '2026-03-20', 'name' => 'Hari Raya Idul Fitri 1447 H', 'type' => 'national'],
            ['date' => '2026-03-21', 'name' => 'Hari Raya Idul Fitri 1447 H', 'type' => 'national'],
            ['date' => '2026-04-03', 'name' => 'Wafat Isa Al Masih', 'type' => 'national'],
            ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional', 'type' => 'national'],
            ['date' => '2026-05-14', 'name' => 'Kenaikan Isa Al Masih', 'type' => 'national'],
            ['date' => '2026-05-27', 'name' => 'Hari Raya Idul Adha 1447 H', 'type' => 'national'],
            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila', 'type' => 'national'],
            ['date' => '2026-06-16', 'name' => 'Tahun Baru Islam 1448 H', 'type' => 'national'],
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan RI', 'type' => 'national'],
            ['date' => '2026-08-26', 'name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national'],
            ['date' => '2026-12-25', 'name' => 'Hari Raya Natal', 'type' => 'national'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date']],
                $holiday
            );
        }
    }
}
