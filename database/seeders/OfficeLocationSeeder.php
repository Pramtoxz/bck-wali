<?php

namespace Database\Seeders;

use App\Models\OfficeLocation;
use Illuminate\Database\Seeder;

class OfficeLocationSeeder extends Seeder
{
    public function run(): void
    {
        OfficeLocation::create([
            'name' => 'Kantor Wali Nagari Padang Panjang',
            'latitude' => -0.9492,
            'longitude' => 100.3543,
            'radius' => 20,
            'is_active' => true,
        ]);
    }
}
