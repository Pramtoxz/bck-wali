<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'name' => 'Administrator',
            'email' => 'admin@walinagari.id',
            'nip' => '199001012020011000',
            'position' => 'Administrator',
            'department' => 'IT',
        ])->assignRole('admin');

        User::create([
            'username' => 'ahmad.rizki',
            'password' => Hash::make('password123'),
            'name' => 'Ahmad Rizki',
            'email' => 'ahmad.rizki@walinagari.id',
            'nip' => '199001012020011001',
            'position' => 'Staff Administrasi',
            'department' => 'Pemerintahan',
        ])->assignRole('user');

        User::create([
            'username' => 'siti.nurhaliza',
            'password' => Hash::make('password123'),
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@walinagari.id',
            'nip' => '199205152020012002',
            'position' => 'Staff Keuangan',
            'department' => 'Keuangan',
        ])->assignRole('user');

        User::create([
            'username' => 'budi.santoso',
            'password' => Hash::make('password123'),
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@walinagari.id',
            'nip' => '198803102019011003',
            'position' => 'Kepala Seksi',
            'department' => 'Pembangunan',
        ])->assignRole('user');
    }
}
