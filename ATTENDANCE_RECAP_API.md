# Attendance Recap API Documentation

## Overview
API untuk menampilkan rekapitulasi absensi bulanan dengan kalender, detail harian, dan statistik kehadiran.

**Base URL:** `http://localhost:8000/api`

**Authentication:** Bearer Token (Laravel Sanctum)

---

## Table of Contents
1. [Get Monthly Attendance Recap](#1-get-monthly-attendance-recap)
2. [Get Attendance Detail by Date](#2-get-attendance-detail-by-date)
3. [Get Attendance Statistics](#3-get-attendance-statistics)
4. [Status Types](#status-types)
5. [Business Rules](#business-rules)
6. [Testing Guide](#testing-guide)
7. [Implementation Notes](#implementation-notes)

---

## 1. Get Monthly Attendance Recap

Mendapatkan rekapitulasi absensi bulanan dengan data kalender dan detail harian.

### Endpoint
```
GET /attendance/recap
```

### Headers
```json
{
  "Authorization": "Bearer {access_token}",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

### Query Parameters
| Parameter | Type   | Required | Description                    | Example    |
|-----------|--------|----------|--------------------------------|------------|
| month     | string | Yes      | Format YYYY-MM                 | 2024-10    |

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "month": "2024-10",
    "month_name": "Oktober",
    "year": 2024,
    "summary": {
      "total_days": 31,
      "working_days": 22,
      "present": 18,
      "late": 2,
      "absent": 2,
      "field_duty": 1,
      "leave": 1
    },
    "calendar": {
      "2024-10-01": {
        "status": "present",
        "check_in": "08:00:00",
        "check_out": "17:00:00",
        "is_late": false
      },
      "2024-10-02": {
        "status": "present",
        "check_in": "07:45:00",
        "check_out": "16:30:00",
        "is_late": false
      },
      "2024-10-03": {
        "status": "field_duty",
        "description": "Kunjungan lapangan kecamatan"
      },
      "2024-10-04": {
        "status": "late",
        "check_in": "08:15:00",
        "check_out": "17:10:00",
        "is_late": true
      },
      "2024-10-05": {
        "status": "absent"
      }
    },
    "details": [
      {
        "date": "2024-10-05",
        "day_name": "Sabtu",
        "status": "absent",
        "check_in": null,
        "check_out": null,
        "working_hours": null,
        "is_late": false
      },
      {
        "date": "2024-10-04",
        "day_name": "Jumat",
        "status": "late",
        "check_in": "08:15:00",
        "check_out": "17:10:00",
        "working_hours": "08:55:00",
        "is_late": true
      },
      {
        "date": "2024-10-03",
        "day_name": "Kamis",
        "status": "field_duty",
        "description": "Kunjungan lapangan kecamatan",
        "check_in": null,
        "check_out": null,
        "working_hours": null,
        "is_late": false
      },
      {
        "date": "2024-10-02",
        "day_name": "Rabu",
        "status": "present",
        "check_in": "07:45:00",
        "check_out": "16:30:00",
        "working_hours": "08:45:00",
        "is_late": false
      },
      {
        "date": "2024-10-01",
        "day_name": "Selasa",
        "status": "present",
        "check_in": "08:00:00",
        "check_out": "17:00:00",
        "working_hours": "09:00:00",
        "is_late": false
      }
    ]
  },
  "message": "Rekapitulasi absensi berhasil dimuat"
}
```

### Error Responses

**401 Unauthorized**
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

**422 Validation Error**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "month": ["The month field is required."]
  }
}
```

### Response Structure Explanation

#### summary
Ringkasan statistik kehadiran bulan ini:
- `total_days`: Total hari dalam bulan (28-31)
- `working_days`: Total hari kerja (exclude weekend)
- `present`: Total hadir tepat waktu (check_in <= 08:00)
- `late`: Total terlambat (check_in > 08:00)
- `absent`: Total tidak hadir
- `field_duty`: Total hari dinas luar
- `leave`: Total hari izin/cuti

#### calendar
Object dengan key tanggal (YYYY-MM-DD) dan value status:
- Key: Format YYYY-MM-DD
- Hanya working days (tidak ada weekend)
- Setiap entry minimal punya field `status`

#### details
Array detail per hari, sorted by date DESC (terbaru di atas):
- Mobile akan slice(0, 10) untuk tampilkan 10 data terbaru
- Hanya working days
- Include semua field untuk detail view

---

## 2. Get Attendance Detail by Date

Mendapatkan detail lengkap absensi untuk tanggal tertentu.

### Endpoint
```
GET /attendance/detail/{date}
```

### Headers
```json
{
  "Authorization": "Bearer {access_token}",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

### Path Parameters
| Parameter | Type   | Required | Description           | Example    |
|-----------|--------|----------|-----------------------|------------|
| date      | string | Yes      | Format YYYY-MM-DD     | 2024-10-24 |

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "date": "2024-10-24",
    "day_name": "Kamis",
    "status": "present",
    "check_in_time": "08:00:00",
    "check_out_time": "17:00:00",
    "check_in_photo": "http://localhost:8000/storage/attendance/checkin_123.jpg",
    "check_out_photo": "http://localhost:8000/storage/attendance/checkout_123.jpg",
    "check_in_location": {
      "latitude": -0.9492,
      "longitude": 100.3543,
      "address": "Kantor Wali Nagari Padang Panjang"
    },
    "check_out_location": {
      "latitude": -0.9492,
      "longitude": 100.3543,
      "address": "Kantor Wali Nagari Padang Panjang"
    },
    "working_hours": "09:00:00",
    "is_late": false,
    "notes": null
  },
  "message": "Detail absensi berhasil dimuat"
}
```

### Error Responses

**404 Not Found**
```json
{
  "success": false,
  "message": "Data absensi tidak ditemukan"
}
```

---

## 3. Get Attendance Statistics

Mendapatkan statistik kehadiran dengan perbandingan bulan sebelumnya.

### Endpoint
```
GET /attendance/statistics
```

### Headers
```json
{
  "Authorization": "Bearer {access_token}",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

### Query Parameters
| Parameter | Type   | Required | Description                    | Example    |
|-----------|--------|----------|--------------------------------|------------|
| month     | string | No       | Format YYYY-MM (default: now)  | 2024-10    |
| year      | string | No       | Format YYYY (default: now)     | 2024       |

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "period": {
      "month": "Oktober",
      "year": 2024,
      "month_number": 10
    },
    "statistics": {
      "total_working_days": 22,
      "present_days": 18,
      "late_days": 2,
      "absent_days": 2,
      "field_duty_days": 1,
      "leave_days": 1,
      "attendance_rate": 81.82,
      "punctuality_rate": 90.00
    },
    "comparison": {
      "previous_month": {
        "attendance_rate": 85.00,
        "change": -3.18
      }
    }
  },
  "message": "Statistik absensi berhasil dimuat"
}
```

### Calculation Formula

**Attendance Rate:**
```
attendance_rate = ((present_days + late_days) / total_working_days) * 100
```

**Punctuality Rate:**
```
punctuality_rate = (present_days / (present_days + late_days)) * 100
```

---

## Status Types

| Status      | Description                          | Color  | Condition                           |
|-------------|--------------------------------------|--------|-------------------------------------|
| present     | Hadir tepat waktu                    | Hijau  | check_in <= 08:00:00                |
| late        | Terlambat                            | Merah  | check_in > 08:00:00                 |
| absent      | Tidak hadir                          | Abu    | Tidak ada data attendance           |
| field_duty  | Dinas luar                           | Kuning | Ada field_duty approved             |
| leave       | Izin/Cuti                            | Biru   | Ada leave approved                  |

### Status Priority
Jika ada multiple conditions, gunakan priority berikut:
1. field_duty (highest priority)
2. leave
3. late (if check_in > 08:00:00)
4. present (if check_in <= 08:00:00)
5. absent (if no data)

---

## Business Rules

### Working Days
- Senin - Jumat (exclude weekend)
- Weekend tidak dihitung sebagai absent
- Holiday tidak dihitung (jika ada table holidays)

### Late Determination
- Check in <= 08:00:00 → Status: present
- Check in > 08:00:00 → Status: late
- Gunakan format H:i:s untuk comparison

### Summary Calculation
- `present` dan `late` harus dihitung terpisah
- Total working days = present + late + absent + field_duty + leave
- Jangan count late sebagai present

### Calendar Object
- Key harus format YYYY-MM-DD
- Hanya include working days
- Setiap entry minimal punya field `status`

### Details Array
- Sorted by date DESC (terbaru di atas)
- Mobile akan slice(0, 10)
- Include semua field untuk detail modal

---

## Testing Guide

### Postman Setup

**Environment Variables:**
```
base_url: http://localhost:8000/api
token: {your_token_here}
```

### Test Case 1: Get Monthly Recap

**Request:**
```
GET {{base_url}}/attendance/recap?month=2024-10
Authorization: Bearer {{token}}
```

**Verify:**
- ✅ Response status 200
- ✅ `summary.present` dan `summary.late` terpisah
- ✅ `calendar` keys format YYYY-MM-DD
- ✅ `details` sorted by date DESC
- ✅ Tidak ada weekend di calendar/details

### Test Case 2: Get Detail by Date

**Request:**
```
GET {{base_url}}/attendance/detail/2024-10-24
Authorization: Bearer {{token}}
```

**Verify:**
- ✅ Response status 200
- ✅ Ada check_in_photo dan check_out_photo URL
- ✅ Ada location coordinates
- ✅ `is_late` sesuai dengan check_in_time

### Test Case 3: Get Statistics

**Request:**
```
GET {{base_url}}/attendance/statistics?month=2024-10
Authorization: Bearer {{token}}
```

**Verify:**
- ✅ Response status 200
- ✅ `attendance_rate` dan `punctuality_rate` calculated correctly
- ✅ Ada comparison dengan bulan sebelumnya

### Test Case 4: Unauthorized

**Request:**
```
GET {{base_url}}/attendance/recap?month=2024-10
(No Authorization header)
```

**Expected:**
- ❌ Response status 401
- ❌ Message: "Unauthenticated."

### Test Case 5: Invalid Month Format

**Request:**
```
GET {{base_url}}/attendance/recap?month=10-2024
Authorization: Bearer {{token}}
```

**Expected:**
- ❌ Response status 422
- ❌ Validation error for month field

### Test Case 6: Empty Data

**Request:**
```
GET {{base_url}}/attendance/recap?month=2025-12
Authorization: Bearer {{token}}
```

**Verify:**
- ✅ Response status 200
- ✅ `calendar` empty object {}
- ✅ `details` empty array []
- ✅ `summary` tetap ada dengan nilai 0

---

## Implementation Notes

### CRITICAL - Status Logic
```php
// Determine status based on check_in time
$checkInTime = Carbon::parse($attendance->check_in_time);
$isLate = $checkInTime->format('H:i:s') > '08:00:00';
$status = $isLate ? 'late' : 'present'; // NOT both 'present'!
```

### CRITICAL - Summary Counting
```php
// Count present and late separately
if ($isLate) {
    $summary['late']++;  // Increment late counter
} else {
    $summary['present']++;  // Increment present counter
}
// DO NOT count late as present!
```

### CRITICAL - Details Sorting
```php
// Sort by date DESC (newest first)
usort($details, function($a, $b) {
    return strcmp($b['date'], $a['date']);
});
```

### Date Iteration
```php
use Carbon\Carbon;

$current = $startDate->copy();
while ($current <= $endDate) {
    $dateString = $current->format('Y-m-d');
    
    // Skip weekends
    if ($current->isWeekend()) {
        $current->addDay();
        continue;
    }
    
    // Process working day
    // ...
    
    $current->addDay();
}
```

### Query Optimization
```php
// Use whereYear and whereMonth for better performance
$attendances = Attendance::where('user_id', $userId)
    ->whereYear('date', $startDate->year)
    ->whereMonth('date', $startDate->month)
    ->get()
    ->keyBy('date'); // Key by date for O(1) lookup
```

### Field Duty & Leave Query
```php
// Query approved field duties/leaves that overlap with month
$fieldDuties = FieldDuty::where('user_id', $userId)
    ->where('status', 'approved')
    ->where(function ($query) use ($startDate, $endDate) {
        $query->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate])
            ->orWhere(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $startDate)
                  ->where('end_date', '>=', $endDate);
            });
    })
    ->get();
```

---

## Mobile Integration

### Data Structure Access
```typescript
// Summary Card
recapData.summary.present      // Total hadir (tidak termasuk terlambat)
recapData.summary.late         // Total terlambat
recapData.summary.field_duty   // Total dinas
recapData.summary.absent       // Total tidak hadir

// Calendar
recapData.calendar[date].status        // Status: present/late/field_duty/leave/absent
recapData.calendar[date].check_in      // Waktu check in (jika ada)
recapData.calendar[date].check_out     // Waktu check out (jika ada)
recapData.calendar[date].is_late       // Boolean
recapData.calendar[date].description   // Deskripsi (untuk field_duty/leave)

// Details List (10 terbaru)
recapData.details.slice(0, 10).map(item => {
  item.date               // Format: YYYY-MM-DD
  item.day_name           // Nama hari (Senin, Selasa, dst)
  item.status             // Status: present/late/field_duty/leave/absent
  item.check_in           // Waktu check in atau null
  item.check_out          // Waktu check out atau null
  item.working_hours      // Total jam kerja (jika ada)
  item.is_late            // Boolean
  item.description        // Deskripsi (untuk field_duty/leave)
})

// Month Info
recapData.month_name                   // Nama bulan (Oktober, November, dst)
recapData.year                         // Tahun (2024)
```

### Calendar Display
```typescript
// Calendar dots color mapping
const statusColors = {
  present: '#2e7d32',      // Primary (hijau)
  late: '#c62828',         // Tertiary (merah)
  field_duty: '#ffd600',   // Secondary (kuning)
  leave: '#1976d2',        // Primary fixed (biru)
  absent: '#9e9e9e'        // Surface container (abu)
};
```

---

## Common Mistakes to Avoid

❌ **Counting late as present in summary**
```php
// WRONG
if ($attendance) {
    $summary['present']++; // This counts late as present!
}

// CORRECT
if ($isLate) {
    $summary['late']++;
} else {
    $summary['present']++;
}
```

❌ **Not sorting details by date DESC**
```php
// WRONG
return $details; // Random order

// CORRECT
usort($details, function($a, $b) {
    return strcmp($b['date'], $a['date']);
});
```

❌ **Including weekends in calendar/details**
```php
// WRONG
$calendar[$dateString] = [...]; // Includes Saturday/Sunday

// CORRECT
if ($current->isWeekend()) {
    $current->addDay();
    continue;
}
```

❌ **Wrong status logic**
```php
// WRONG
$status = 'present'; // Always present even if late

// CORRECT
$status = $isLate ? 'late' : 'present';
```

---

## Postman Collection JSON

```json
{
  "info": {
    "name": "Attendance Recap API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get Monthly Recap",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/attendance/recap?month=2024-10",
          "host": ["{{base_url}}"],
          "path": ["attendance", "recap"],
          "query": [
            {
              "key": "month",
              "value": "2024-10"
            }
          ]
        }
      }
    },
    {
      "name": "Get Attendance Detail",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/attendance/detail/2024-10-24",
          "host": ["{{base_url}}"],
          "path": ["attendance", "detail", "2024-10-24"]
        }
      }
    },
    {
      "name": "Get Statistics",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/attendance/statistics?month=2024-10",
          "host": ["{{base_url}}"],
          "path": ["attendance", "statistics"],
          "query": [
            {
              "key": "month",
              "value": "2024-10"
            }
          ]
        }
      }
    }
  ]
}
```

---

## Support

**Last Updated:** 2026-04-19  
**Version:** 1.0.0  
**Status:** Ready for Implementation

Untuk pertanyaan atau issue, silakan hubungi tim backend.
