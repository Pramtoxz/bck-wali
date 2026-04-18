<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Pemerintahan', 'description' => 'Departemen Pemerintahan dan Administrasi'],
            ['name' => 'Pembangunan', 'description' => 'Departemen Pembangunan dan Infrastruktur'],
            ['name' => 'Kesejahteraan', 'description' => 'Departemen Kesejahteraan Masyarakat'],
            ['name' => 'Keuangan', 'description' => 'Departemen Keuangan dan Aset'],
            ['name' => 'Umum', 'description' => 'Departemen Umum dan Pelayanan'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
