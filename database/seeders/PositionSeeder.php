<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Wali Nagari', 'description' => 'Kepala Pemerintahan Nagari'],
            ['name' => 'Sekretaris Nagari', 'description' => 'Sekretaris Pemerintahan Nagari'],
            ['name' => 'Kaur Pemerintahan', 'description' => 'Kepala Urusan Pemerintahan'],
            ['name' => 'Kaur Pembangunan', 'description' => 'Kepala Urusan Pembangunan'],
            ['name' => 'Kaur Kesejahteraan', 'description' => 'Kepala Urusan Kesejahteraan'],
            ['name' => 'Kaur Keuangan', 'description' => 'Kepala Urusan Keuangan'],
            ['name' => 'Kaur Umum', 'description' => 'Kepala Urusan Umum'],
            ['name' => 'Staff Administrasi', 'description' => 'Staff Administrasi Umum'],
            ['name' => 'Staff Teknis', 'description' => 'Staff Teknis'],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
