<?php

namespace Database\Seeders;

use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $leaves = [
            [
                'user_id' => 2,
                'start_date' => '2026-04-21',
                'end_date' => '2026-04-22',
                'total_days' => 2,
                'type' => 'sakit',
                'reason' => 'Demam tinggi dan perlu istirahat total',
                'document_path' => 'documents/leave/surat_sakit_april_001.pdf',
                'status' => 'approved',
                'created_at' => Carbon::parse('2026-04-21 07:00:00'),
                'updated_at' => Carbon::parse('2026-04-21 08:15:00'),
            ],
            [
                'user_id' => 3,
                'start_date' => '2026-04-10',
                'end_date' => '2026-04-10',
                'total_days' => 1,
                'type' => 'izin',
                'reason' => 'Mengurus keperluan keluarga di rumah sakit',
                'document_path' => null,
                'status' => 'approved',
                'created_at' => Carbon::parse('2026-04-10 06:30:00'),
                'updated_at' => Carbon::parse('2026-04-10 07:45:00'),
            ],
            [
                'user_id' => 4,
                'start_date' => '2026-04-03',
                'end_date' => '2026-04-04',
                'total_days' => 2,
                'type' => 'cuti',
                'reason' => 'Cuti tahunan untuk acara keluarga',
                'document_path' => null,
                'status' => 'approved',
                'created_at' => Carbon::parse('2026-03-28 14:00:00'),
                'updated_at' => Carbon::parse('2026-03-29 09:30:00'),
            ],
            [
                'user_id' => 3,
                'start_date' => '2026-04-25',
                'end_date' => '2026-04-25',
                'total_days' => 1,
                'type' => 'sakit',
                'reason' => 'Sakit kepala dan mual',
                'document_path' => 'documents/leave/surat_sakit_april_002.pdf',
                'status' => 'pending',
                'created_at' => Carbon::parse('2026-04-25 06:00:00'),
                'updated_at' => Carbon::parse('2026-04-25 06:00:00'),
            ],
            [
                'user_id' => 4,
                'start_date' => '2026-04-28',
                'end_date' => '2026-04-29',
                'total_days' => 2,
                'type' => 'izin',
                'reason' => 'Menghadiri pernikahan saudara di luar kota',
                'document_path' => null,
                'status' => 'pending',
                'created_at' => Carbon::parse('2026-04-24 15:30:00'),
                'updated_at' => Carbon::parse('2026-04-24 15:30:00'),
            ],
        ];

        foreach ($leaves as $leave) {
            Leave::create($leave);
        }
    }
}
