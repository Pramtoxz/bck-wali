<?php

namespace Database\Seeders;

use App\Models\OfficeLocation;
use Illuminate\Database\Seeder;

class OfficeLocationSeeder extends Seeder
{
    public function run(): void
    {
        OfficeLocation::create([
            'name' => 'Kantor Wali Nagari Barung Barung Balantai Selatan',
            'latitude' => -0.9441743,
            'longitude' => 100.3531209,
            'map_iframe'=>'<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7258.434848338198!2d100.49624750053651!3d-1.1647771614408464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2fd4a9b95f9a7dff%3A0x2fb15bad221c75a0!2sKantor%20Wali%20Nagari%20Barung2%20Balantai%20Selatan!5e0!3m2!1sid!2sid!4v1776621430276!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
            'radius' => 20,
            'is_active' => true,
        ]);
    }
}
