<?php

namespace Database\Seeders;

use App\Models\FieldDuty;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FieldDutySeeder extends Seeder
{
    public function run(): void
    {
        $fieldDuties = [
            [
                'user_id' => 2,
                'start_date' => '2026-04-07',
                'end_date' => '2026-04-09',
                'total_days' => 3,
                'destination' => 'Padang',
                'purpose' => 'Rapat koordinasi dengan Pemda Sumatera Barat mengenai program pembangunan infrastruktur nagari',
                'document_path' => 'documents/field-duty/surat_tugas_april_001.pdf',
                'status' => 'approved',
                'created_at' => Carbon::parse('2026-04-04 09:00:00'),
                'updated_at' => Carbon::parse('2026-04-04 14:30:00'),
            ],
            [
                'user_id' => 3,
                'start_date' => '2026-04-14',
                'end_date' => '2026-04-15',
                'total_days' => 2,
                'destination' => 'Bukittinggi',
                'purpose' => 'Pelatihan manajemen keuangan desa untuk bendahara nagari se-Sumatera Barat',
                'document_path' => 'documents/field-duty/surat_tugas_april_002.pdf',
                'status' => 'approved',
                'created_at' => Carbon::parse('2026-04-10 10:15:00'),
                'updated_at' => Carbon::parse('2026-04-10 15:20:00'),
            ],
            [
                'user_id' => 4,
                'start_date' => '2026-04-21',
                'end_date' => '2026-04-23',
                'total_days' => 3,
                'destination' => 'Payakumbuh',
                'purpose' => 'Monitoring dan evaluasi proyek pembangunan jalan nagari',
                'document_path' => 'documents/field-duty/surat_tugas_april_003.pdf',
                'status' => 'pending',
                'created_at' => Carbon::parse('2026-04-17 08:30:00'),
                'updated_at' => Carbon::parse('2026-04-17 08:30:00'),
            ],
            [
                'user_id' => 2,
                'start_date' => '2026-04-28',
                'end_date' => '2026-04-30',
                'total_days' => 3,
                'destination' => 'Solok',
                'purpose' => 'Sosialisasi program digitalisasi administrasi nagari',
                'document_path' => 'documents/field-duty/surat_tugas_april_004.pdf',
                'status' => 'pending',
                'created_at' => Carbon::parse('2026-04-24 11:00:00'),
                'updated_at' => Carbon::parse('2026-04-24 11:00:00'),
            ],
        ];

        foreach ($fieldDuties as $duty) {
            FieldDuty::create($duty);
        }
    }
}
