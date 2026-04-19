# Implementasi Holiday/Weekend di Backend Laravel

## 1. Database Migration - Tabel Holidays

Buat migration untuk tabel holidays:

```bash
php artisan make:migration create_holidays_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name');
            $table->enum('type', ['weekend', 'national', 'company'])->default('national');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('holidays');
    }
};
```

## 2. Model Holiday

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'type',
        'description',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Cek apakah tanggal tertentu adalah hari libur
     */
    public static function isHoliday($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        
        // Cek weekend (Sabtu = 6, Minggu = 0)
        if (in_array($date->dayOfWeek, [0, 6])) {
            return [
                'is_holiday' => true,
                'holiday_name' => $date->dayOfWeek === 0 ? 'Hari Minggu' : 'Hari Sabtu',
                'type' => 'weekend'
            ];
        }
        
        // Cek tanggal merah dari database
        $holiday = self::where('date', $date->format('Y-m-d'))
            ->where('is_active', true)
            ->first();
        
        if ($holiday) {
            return [
                'is_holiday' => true,
                'holiday_name' => $holiday->name,
                'type' => $holiday->type,
                'description' => $holiday->description
            ];
        }
        
        return [
            'is_holiday' => false,
            'holiday_name' => null,
            'type' => null
        ];
    }
}
```

## 3. Update AttendanceController

### Method getTodayStatus()

```php
public function getTodayStatus(Request $request)
{
    try {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');
        
        // Cek holiday/weekend
        $holidayCheck = Holiday::isHoliday($today);
        
        // Cek status dinas luar
        $fieldDuty = FieldDuty::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();
        
        // Cek status cuti/izin
        $leave = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();
        
        // Cek attendance hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
        
        // Tentukan status
        $status = 'available'; // default
        $description = null;
        
        if ($fieldDuty) {
            $status = 'field_duty';
            $description = $fieldDuty->purpose;
        } elseif ($leave) {
            $status = 'leave';
            $description = $leave->reason;
        } elseif ($attendance) {
            $status = 'present';
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'date' => $today,
                'status' => $status,
                'description' => $description,
                'is_holiday' => $holidayCheck['is_holiday'],
                'holiday_name' => $holidayCheck['holiday_name'],
                'holiday_type' => $holidayCheck['type'],
                'check_in_time' => $attendance?->check_in_time,
                'check_out_time' => $attendance?->check_out_time,
                'is_late' => $attendance?->is_late ?? false,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mendapatkan status absensi: ' . $e->getMessage()
        ], 500);
    }
}
```

### Method checkIn() - Tambahkan validasi holiday

```php
public function checkIn(Request $request)
{
    try {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');
        
        // Validasi holiday/weekend
        $holidayCheck = Holiday::isHoliday($today);
        if ($holidayCheck['is_holiday']) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat melakukan absensi pada hari libur',
                'data' => [
                    'is_holiday' => true,
                    'holiday_name' => $holidayCheck['holiday_name']
                ]
            ], 400);
        }
        
        // Validasi sudah check-in
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
        
        if ($existingAttendance && $existingAttendance->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk hari ini'
            ], 400);
        }
        
        // ... lanjutkan proses check-in normal
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal melakukan check-in: ' . $e->getMessage()
        ], 500);
    }
}
```

### Method checkOut() - Tambahkan validasi holiday

```php
public function checkOut(Request $request)
{
    try {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');
        
        // Validasi holiday/weekend
        $holidayCheck = Holiday::isHoliday($today);
        if ($holidayCheck['is_holiday']) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat melakukan absensi pada hari libur',
                'data' => [
                    'is_holiday' => true,
                    'holiday_name' => $holidayCheck['holiday_name']
                ]
            ], 400);
        }
        
        // ... lanjutkan proses check-out normal
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal melakukan check-out: ' . $e->getMessage()
        ], 500);
    }
}
```

## 4. Seeder untuk Data Holiday 2024-2025

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Carbon\Carbon;

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
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }
    }
}
```

## 5. Routes (api.php)

```php
// Holiday Management (Admin only)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('holidays', [HolidayController::class, 'index']);
    Route::post('holidays', [HolidayController::class, 'store']);
    Route::put('holidays/{id}', [HolidayController::class, 'update']);
    Route::delete('holidays/{id}', [HolidayController::class, 'destroy']);
});

// Check holiday (untuk user)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('holidays/check', [HolidayController::class, 'checkToday']);
});
```

## 6. Testing

```bash
# Jalankan migration
php artisan migrate

# Jalankan seeder
php artisan db:seed --class=HolidaySeeder

# Test API
curl -X GET "http://localhost:8000/api/attendance/today-status" \
  -H "Authorization: Bearer {token}"
```

## Response Format yang Diharapkan Frontend

```json
{
  "success": true,
  "data": {
    "date": "2024-12-25",
    "status": "available",
    "description": null,
    "is_holiday": true,
    "holiday_name": "Hari Raya Natal",
    "holiday_type": "national",
    "check_in_time": null,
    "check_out_time": null,
    "is_late": false
  }
}
```

## Catatan Penting

1. **Weekend Detection**: Otomatis mendeteksi Sabtu (6) dan Minggu (0)
2. **Database Holidays**: Untuk tanggal merah nasional dan cuti bersama
3. **Priority**: Holiday check dilakukan SEBELUM validasi lainnya
4. **Admin Panel**: Perlu dibuat untuk manage holidays (tambah/edit/hapus)
5. **Cuti Bersama**: Bisa ditambahkan dengan type 'company'
