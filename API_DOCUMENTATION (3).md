# API Documentation - Wali Nagari Digital Attendance

> Dokumentasi API untuk Tim Backend  
> Project: SIWALI - Sistem Informasi Wali Nagari  
> Frontend: React Native CLI 0.85.1  
> Backend: Laravel 12  
> Last Updated: 2026-04-18

---

## ATURAN KERJA
- **PENGGUNAAN CODINGAN** WAJIB TANYA KE CONTEXT7 TANPA ASUSMSI SENDIRI
- **TECH**  LARAVEL 12 INERTIA JS STARTERKIT REACT SUDAH DEFAULT SHADCN UI DI TAHUN 2026
- **STANDART PROJECT** PROJECT HARUS STANDART INDUSTRI
- **STRUKTUR** WAJIB DI PISAH DAN TIDAK BOLEH MENUMPUK DALAM 1 FILE ATAU FOLDER
- **KEBERSIHAN** TIDAK BOLEH MEMBUAT FILE MD DAN COMENTAR // DI CODINGAN

## 📋 Status Implementasi

### ✅ READY - Sudah Implementasi di Mobile (Siap Connect API)
Fitur-fitur berikut sudah lengkap di mobile dan siap untuk integrasi API:

1. **Authentication**
   - Login (username/password)
   - Logout
   - Get User Profile
   - Token Management (AsyncStorage)

2. **Field Duty (Dinas Luar)**
   - Submit Request (dengan file upload)
   - Get List (3 terbaru)
   - **Detail Modal (view full submission details)**

3. **Leave (Izin & Cuti)**
   - Submit Request (dengan file upload opsional)
   - Get List (3 terbaru)
   - **Detail Modal (view full submission details)**

4. **File Upload**
   - PDF/JPG/PNG support
   - Max 5MB validation

5. **Attendance (Check In/Out)**
   - **Camera integration (react-native-vision-camera)**
   - **GPS location tracking (@react-native-community/geolocation)**
   - **Real-time location validation (radius check)**
   - **Photo capture for attendance**
   - **Check In/Out API integration**
   - **Today attendance status**

6. **UI Components**
   - CustomAlert (4 types: alert, confirm, success, error)
   - **DetailModal (slide up modal with full details)**
   - **CameraView (front camera with face frame)**
   - **LocationStatusCard (GPS status with accuracy)**
   - Pull-to-refresh on all screens
   - Loading states & error handling

### 🔄 FUTURE - Akan Dikembangkan Bersama
Fitur-fitur berikut akan dikembangkan bersama antara mobile dan backend:

- **Rekap Attendance (Calendar & History)** - ⏳ IN PROGRESS
- Detail & Cancel untuk Field Duty/Leave
- Profile Update
- Admin Approval System

---

## 📋 Table of Contents

1. [Base Configuration](#base-configuration)
2. [Authentication](#authentication)
3. [Field Duty API](#field-duty-api)
4. [Leave API](#leave-api)
5. [Attendance API](#attendance-api)
6. [Attendance Recap API](#attendance-recap-api)
7. [File Upload](#file-upload)
8. [Response Format](#response-format)
9. [Error Handling](#error-handling)
10. [Laravel 12 Implementation Guide](#laravel-12-implementation-guide)

---

## Base Configuration

### Base URL
```
Production: https://api.walinagari.id/api
Development: http://localhost:8000/api
```

### Headers
```json
{
  "Content-Type": "application/json",
  "Authorization": "Bearer {access_token}",
  "Accept": "application/json"
}
```

### File Upload Headers
```json
{
  "Content-Type": "multipart/form-data",
  "Authorization": "Bearer {access_token}",
  "Accept": "application/json"
}
```

---

## Authentication

### Laravel Sanctum Configuration
Backend menggunakan Laravel Sanctum untuk API authentication:
- Token-based authentication
- Token disimpan di AsyncStorage mobile
- Token tidak expire (revoke manual via logout)

### Mobile Implementation
- **File:** `src/services/authService.ts`
- **Storage:** AsyncStorage (`@auth_token`, `@user_data`)
- **Auto-check:** App.tsx check token on startup

### 1. Login
**Endpoint:** `POST /auth/login`

**Mobile File:** `src/screens/Auth/LoginScreen.tsx`

**Request Body:**
```json
{
  "username": "ahmad.rizki",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123def456...",
    "user": {
      "id": 1,
      "username": "ahmad.rizki",
      "name": "Ahmad Rizki",
      "email": "ahmad.rizki@walinagari.id",
      "nip": "199001012020011001",
      "position": "Staff Administrasi",
      "department": "Pemerintahan",
      "avatar": "https://api.walinagari.id/storage/avatars/default.jpg"
    }
  },
  "message": "Login berhasil"
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Username atau password salah"
}
```

**Mobile Behavior:**
- Save token to AsyncStorage (`@auth_token`)
- Save user data to AsyncStorage (`@user_data`)
- Navigate to MainTabNavigator
- Show error Alert if failed

---

### 2. Logout
**Endpoint:** `POST /auth/logout`

**Mobile File:** `src/services/authService.ts`

**Headers:** Authorization required

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

**Mobile Behavior:**
- Clear AsyncStorage (`@auth_token`, `@user_data`)
- Navigate to LoginScreen
- Revoke token on backend

---

### 3. Get Authenticated User
**Endpoint:** `GET /auth/user`

**Mobile File:** `src/services/authService.ts`

**Headers:** Authorization required

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "ahmad.rizki",
    "name": "Ahmad Rizki",
    "email": "ahmad.rizki@walinagari.id",
    "nip": "199001012020011001",
    "position": "Staff Administrasi",
    "department": "Pemerintahan",
    "avatar": "https://api.walinagari.id/storage/avatars/default.jpg"
  }
}
```

**Mobile Usage:**
- ProfileHeader component (all screens)
- App startup (check if token valid)

---

## Field Duty API

### Mobile Implementation
- **Screen:** `src/screens/Dinas/DinasScreen.tsx`
- **Service:** `src/services/fieldDutyService.ts`
- **Components:** DateRangePicker, DocumentUpload, DutyStatusItem, **DetailModal**

### 1. Submit Field Duty Request
**Endpoint:** `POST /field-duty`

**Mobile Screen:** `src/screens/Dinas/DinasScreen.tsx`

**Request (multipart/form-data):**
```
start_date: 2024-10-25
end_date: 2024-10-27
destination: Padang
purpose: Rapat koordinasi dengan Pemda
document: [File PDF/JPG, max 5MB]
```

**Mobile Form:**
- **Periode Dinas:** DateRangePicker (min: tomorrow)
- **Tujuan Dinas:** TextInput
- **Maksud & Tujuan:** TextArea (multiline)
- **Dokumen Surat Tugas:** File picker (required)

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "start_date": "2024-10-25",
    "end_date": "2024-10-27",
    "total_days": 3,
    "destination": "Padang",
    "purpose": "Rapat koordinasi dengan Pemda",
    "document_url": "https://api.walinagari.id/storage/documents/duty_123.pdf",
    "status": "pending",
    "submitted_at": "2024-10-24T10:30:00+07:00"
  },
  "message": "Pengajuan dinas berhasil dikirim"
}
```

**Validation Rules (Laravel 12 Format):**
```php
$request->validate([
    'start_date' => 'required|date|after:today',
    'end_date' => 'required|date|after_or_equal:start_date',
    'destination' => 'required|string|max:255',
    'purpose' => 'required|string',
    'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
]);
```

**Alternative Fluent Validation (Recommended for Files):**
```php
use Illuminate\Validation\Rules\File;

$request->validate([
    'start_date' => 'required|date|after:today',
    'end_date' => 'required|date|after_or_equal:start_date',
    'destination' => 'required|string|max:255',
    'purpose' => 'required|string',
    'document' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(5 * 1024)],
]);
```

**Mobile Behavior After Success:**
- Show success Alert
- Clear form
- Refresh status list
- Auto-calculate: "Absensi otomatis nonaktif selama 3 hari"

---

### 2. Get Field Duty List
**Endpoint:** `GET /field-duty?limit=3`

**Mobile Screen:** `src/screens/Dinas/DinasScreen.tsx`

**Query Parameters:**
- `limit`: 3 (fixed - untuk status section)

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "date": "24 Okt 2024",
      "start_date": "2024-10-25",
      "end_date": "2024-10-27",
      "total_days": 3,
      "destination": "Padang",
      "purpose": "Rapat koordinasi...",
      "status": "pending",
      "submitted_at": "2024-10-24T10:30:00+07:00"
    },
    {
      "id": 2,
      "date": "20 Okt 2024",
      "start_date": "2024-10-21",
      "end_date": "2024-10-22",
      "total_days": 2,
      "destination": "Bukittinggi",
      "purpose": "Pelatihan...",
      "status": "approved",
      "submitted_at": "2024-10-20T08:15:00+07:00"
    }
  ]
}
```

**Status Types:**
- `pending`: Menunggu (yellow badge)
- `approved`: Disetujui (green badge)
- `rejected`: Ditolak (red badge)

**Mobile Display:**
- Component: DutyStatusItem
- Show 3 most recent
- Format date: "DD MMM YYYY"
- Truncate purpose if too long
- **Tap to open DetailModal with full information**

**DetailModal Features:**
- Slide up animation from bottom
- Display all submission details:
  - Status badge with icon
  - Start date, end date, total days
  - Destination and purpose
  - Submission date and ID
  - Download document button (if available)
- Scrollable content
- Close button

---

## Leave API

### Mobile Implementation
- **Screen:** `src/screens/Izin/IzinScreen.tsx`
- **Service:** `src/services/leaveService.ts`
- **Components:** DateRangePicker, PickerModal, DocumentUpload, **DetailModal**

### 1. Submit Leave Request
**Endpoint:** `POST /leave`

**Mobile Screen:** `src/screens/Izin/IzinScreen.tsx`

**Request (multipart/form-data):**
```
start_date: 2024-10-24
end_date: 2024-10-25
type: sakit
reason: Demam tinggi
document: [File PDF/JPG, optional]
```

**Mobile Form:**
- **Periode Izin/Cuti:** DateRangePicker (min: today - bisa pilih hari ini)
- **Jenis Izin/Cuti:** Dropdown (sakit/izin/cuti)
- **Alasan/Keterangan:** TextArea (multiline)
- **Dokumen Pendukung:** File picker (optional)

**Leave Types:**
- `sakit`: Sakit
- `izin`: Izin
- `cuti`: Cuti

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "start_date": "2024-10-24",
    "end_date": "2024-10-25",
    "total_days": 2,
    "type": "sakit",
    "reason": "Demam tinggi",
    "document_url": "https://api.walinagari.id/storage/documents/leave_123.pdf",
    "status": "pending",
    "submitted_at": "2024-10-24T08:00:00+07:00"
  },
  "message": "Pengajuan izin/cuti berhasil dikirim"
}
```

**Validation Rules (Laravel 12 Format):**
```php
use Illuminate\Validation\Rules\File;

$request->validate([
    'start_date' => 'required|date',
    'end_date' => 'required|date|after_or_equal:start_date',
    'type' => 'required|in:sakit,izin,cuti',
    'reason' => 'required|string',
    'document' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(5 * 1024)],
]);
```

**Mobile Behavior After Success:**
- Show success Alert
- Clear form
- Refresh status list
- Auto-calculate: "Absensi otomatis nonaktif selama 2 hari"

---

### 2. Get Leave History
**Endpoint:** `GET /leave?limit=3`

**Mobile Screen:** `src/screens/Izin/IzinScreen.tsx`

**Query Parameters:**
- `limit`: 3 (fixed - untuk status section)

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "date": "24 Okt 2024",
      "start_date": "2024-10-24",
      "end_date": "2024-10-25",
      "total_days": 2,
      "type": "sakit",
      "reason": "Demam tinggi",
      "status": "pending",
      "submitted_at": "2024-10-24T08:00:00+07:00"
    },
    {
      "id": 2,
      "date": "15 Okt 2024",
      "start_date": "2024-10-16",
      "end_date": "2024-10-16",
      "total_days": 1,
      "type": "izin",
      "reason": "Keperluan keluarga",
      "status": "approved",
      "submitted_at": "2024-10-15T14:20:00+07:00"
    }
  ]
}
```

**Mobile Display:**
- Component: DutyStatusItem (reused)
- Title: type (Sakit/Izin/Cuti)
- Description: reason (truncated)
- Show 3 most recent
- **Tap to open DetailModal with full information**

**DetailModal Features:**
- Slide up animation from bottom
- Display all submission details:
  - Status badge with icon
  - Start date, end date, total days
  - Leave type (Sakit/Izin/Cuti) and reason
  - Submission date and ID
  - Download document button (if available)
- Scrollable content
- Close button

**Important Response Fields for DetailModal:**
- `id`: Unique identifier (displayed as #ID)
- `date`: Formatted submission date (e.g., "24 Okt 2024")
- `start_date`: ISO date format (e.g., "2024-10-24")
- `end_date`: ISO date format
- `total_days`: Calculated days
- `type`: Leave type (sakit/izin/cuti)
- `reason`: Full reason text
- `status`: pending/approved/rejected
- `document_url`: Optional document URL
- `submitted_at`: ISO timestamp

---

## Attendance API

### Mobile Implementation
- **Screen:** `src/screens/Absen/AbsenScreen.tsx`
- **Service:** `src/services/attendanceService.ts`
- **Components:** CameraView, LocationStatusCard
- **Hooks:** useCamera, useLocation
- **Libraries:** react-native-vision-camera, @react-native-community/geolocation

### 1. Get Office Location
**Endpoint:** `GET /office/location`

**Headers:** Authorization required
```json
{
  "Content-Type": "application/json",
  "Authorization": "Bearer {access_token}",
  "Accept": "application/json"
}
```

**Mobile Usage:** Auto-fetch saat app start dan refresh location

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "latitude": -0.9492,
    "longitude": 100.3543,
    "radius": 50,
    "name": "Kantor Wali Nagari Padang Panjang"
  },
  "message": "Lokasi kantor berhasil dimuat"
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Unauthenticated. Please login via POST /api/auth/login to get a token, then set Authorization: Bearer {token} header."
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Gagal mendapatkan lokasi kantor"
}
```

**Mobile Behavior:**
- Cache lokasi kantor di locationService
- Gunakan untuk validasi radius real-time
- Refresh otomatis saat pull-to-refresh
- Tampilkan nama kantor di LocationStatusCard
- **Requires Bearer token authentication**

**Business Rules:**
- Admin bisa update lokasi kantor kapan saja
- Radius bisa berbeda per kantor (contoh: 20m, 50m, 100m)
- Nama kantor ditampilkan di UI
- Mobile cache lokasi untuk performa
- **TIDAK BOLEH HARDCODE radius di mobile - harus dari backend**
- **Endpoint protected - harus login dulu**

---

### 2. Check In
**Endpoint:** `POST /attendance/check-in`

**Mobile Screen:** `src/screens/Absen/AbsenScreen.tsx`

**Headers:** Authorization required
```json
{
  "Content-Type": "multipart/form-data",
  "Authorization": "Bearer {access_token}",
  "Accept": "application/json"
}
```

**Request (multipart/form-data):**
```
latitude: -0.9492
longitude: 100.3543
photo: [File JPEG from camera]
```

**Mobile Features:**
- **Dynamic Office Location:** Get dari API `/office/location`
- **Real-time Radius Validation:** Cek jarak dari lokasi kantor
- **Camera Integration:** Front camera dengan frame lingkaran
- **GPS Validation:** Auto-refresh lokasi dengan akurasi
- **Photo Capture:** Ambil foto selfie untuk verifikasi
- **Permission Handling:** Request camera & location permissions

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "check_in_time": "08:15:30",
    "check_in_photo": "https://api.walinagari.id/storage/attendance/checkin_123.jpg",
    "check_in_latitude": -0.9492,
    "check_in_longitude": 100.3543,
    "date": "2024-10-24",
    "created_at": "2024-10-24T08:15:30+07:00"
  },
  "message": "Check in berhasil"
}
```

**Validation Rules (Laravel 12 Format):**
```php
use Illuminate\Validation\Rules\File;

$request->validate([
    'latitude' => 'required|numeric|between:-90,90',
    'longitude' => 'required|numeric|between:-180,180',
    'photo' => ['required', File::image()->max(5 * 1024)],
]);
```

**Additional Backend Validation:**
- Validate distance from office location (radius check)
- Validate check-in time window (06:00 - 10:00 WIB)
- Validate user hasn't checked in today
- Validate photo is valid JPEG format

**Business Rules:**
- **Radius validation dari backend (bukan hardcode mobile)**
- User harus dalam radius yang ditentukan backend
- Hanya bisa check in sekali per hari
- Waktu check in: 06:00 - 10:00 WIB
- Photo wajib (selfie verification)

**Mobile Behavior:**
- Get office location dari API
- Validasi lokasi real-time dengan office coordinates
- Tampilkan jarak dan radius di UI
- Ambil foto dengan camera front
- Show loading saat submit
- Update status absensi hari ini
- Refresh data setelah berhasil

---

### 3. Check Out
**Endpoint:** `POST /attendance/check-out`

**Mobile Screen:** `src/screens/Absen/AbsenScreen.tsx`

**Request (multipart/form-data):**
```
latitude: -0.9492
longitude: 100.3543
photo: [File JPEG from camera]
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "check_in_time": "08:15:30",
    "check_out_time": "17:30:45",
    "check_out_photo": "https://api.walinagari.id/storage/attendance/checkout_123.jpg",
    "check_out_latitude": -0.9492,
    "check_out_longitude": 100.3543,
    "working_hours": "09:15:15",
    "date": "2024-10-24",
    "updated_at": "2024-10-24T17:30:45+07:00"
  },
  "message": "Check out berhasil"
}
```

### 3. Check Out
**Endpoint:** `POST /attendance/check-out`

**Mobile Screen:** `src/screens/Absen/AbsenScreen.tsx`

**Request (multipart/form-data):**
```
latitude: -0.9492
longitude: 100.3543
photo: [File JPEG from camera]
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "check_in_time": "08:15:30",
    "check_out_time": "17:30:45",
    "check_out_photo": "https://api.walinagari.id/storage/attendance/checkout_123.jpg",
    "check_out_latitude": -0.9492,
    "check_out_longitude": 100.3543,
    "working_hours": "09:15:15",
    "date": "2024-10-24",
    "updated_at": "2024-10-24T17:30:45+07:00"
  },
  "message": "Check out berhasil"
}
```

**Business Rules:**
- User harus sudah check in
- Hanya bisa check out sekali per hari
- Waktu check out: 15:00 - 20:00 WIB
- **Harus dalam radius office location (dari backend)**
- Auto-calculate working hours

**Mobile Behavior:**
- Button berubah dari "Absen Masuk" ke "Absen Keluar"
- Validasi sudah check in
- Same validation: lokasi + foto dengan office coordinates
- Show working hours setelah check out

---

### 4. Get Today Status
**Endpoint:** `GET /attendance/today`

**Mobile Screen:** `src/screens/Absen/AbsenScreen.tsx`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "date": "2024-10-24",
    "check_in_time": "08:15:30",
    "check_out_time": null,
    "check_in_photo": "https://api.walinagari.id/storage/attendance/checkin_123.jpg",
    "check_out_photo": null,
    "working_hours": null,
    "status": "checked_in"
  }
}
```

**Status Values:**
- `not_checked_in`: Belum absen masuk
- `checked_in`: Sudah absen masuk, belum keluar
- `checked_out`: Sudah absen masuk dan keluar (selesai)

**Mobile Usage:**
- Load saat app start dan refresh
- Tentukan button state (Masuk/Keluar/Selesai)
- Show status card dengan waktu masuk/keluar
- Disable button jika sudah selesai

**Mobile Display:**
```
Status Absensi Hari Ini
Masuk: 08:15:30
Keluar: -
```

---

## Attendance Recap API

### Mobile Implementation
- **Screen:** `src/screens/Rekap/RekapScreen.tsx`
- **Service:** `src/services/attendanceService.ts` (akan ditambahkan method baru)
- **Components:** AttendanceCalendar, AttendanceListItem
- **Library:** react-native-calendars

### 1. Get Monthly Attendance Recap
**Endpoint:** `GET /attendance/recap`

**Mobile Screen:** `src/screens/Rekap/RekapScreen.tsx`

**Headers:** Authorization required
```json
{
  "Content-Type": "application/json",
  "Authorization": "Bearer {access_token}",
  "Accept": "application/json"
}
```

**Query Parameters:**
- `month`: YYYY-MM format (e.g., "2024-10") - required
- `year`: YYYY format (e.g., "2024") - optional (extracted from month if not provided)

**Success Response (200):**
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
        "status": "present",
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
        "date": "2024-10-01",
        "day_name": "Selasa",
        "status": "present",
        "check_in": "08:00:00",
        "check_out": "17:00:00",
        "working_hours": "09:00:00",
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
        "date": "2024-10-03",
        "day_name": "Kamis",
        "status": "field_duty",
        "description": "Kunjungan lapangan kecamatan",
        "check_in": null,
        "check_out": null
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
        "date": "2024-10-05",
        "day_name": "Sabtu",
        "status": "absent",
        "check_in": null,
        "check_out": null
      }
    ]
  },
  "message": "Rekapitulasi absensi berhasil dimuat"
}
```

**Status Types:**
- `present`: Hadir (hijau) - check in & out normal, tidak terlambat
- `late`: Terlambat (merah) - check in setelah jam 08:00
- `absent`: Tidak hadir (abu-abu) - tidak ada check in
- `field_duty`: Dinas luar (kuning) - sedang dinas
- `leave`: Izin/Cuti (biru) - sedang izin/cuti
- `weekend`: Akhir pekan (tidak ditampilkan di list)
- `holiday`: Hari libur (tidak ditampilkan di list)

**PENTING - Struktur Data untuk Mobile:**

Mobile mengakses data dengan struktur berikut:
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

// Details List
recapData.details[].date               // Format: YYYY-MM-DD
recapData.details[].day_name           // Nama hari (Senin, Selasa, dst)
recapData.details[].status             // Status: present/late/field_duty/leave/absent
recapData.details[].check_in           // Waktu check in atau null
recapData.details[].check_out          // Waktu check out atau null
recapData.details[].working_hours      // Total jam kerja (jika ada)
recapData.details[].is_late            // Boolean
recapData.details[].description        // Deskripsi (untuk field_duty/leave)

// Month Info
recapData.month_name                   // Nama bulan (Oktober, November, dst)
recapData.year                         // Tahun (2024)
```

**CRITICAL - Backend Implementation Notes:**

1. **Status Logic:**
   - Jika check_in > 08:00:00 → status = "late" (bukan "present")
   - Jika check_in <= 08:00:00 → status = "present"
   - Jika ada field_duty approved → status = "field_duty"
   - Jika ada leave approved → status = "leave"
   - Jika tidak ada data → status = "absent"

2. **Summary Calculation:**
   - `present`: Count status "present" only (exclude "late")
   - `late`: Count status "late" only
   - `field_duty`: Count status "field_duty"
   - `leave`: Count status "leave"
   - `absent`: Count status "absent"

3. **Details Array:**
   - Harus sorted by date DESC (terbaru di atas)
   - Mobile hanya tampilkan 10 data pertama: `details.slice(0, 10)`
   - Jangan include weekend/holiday di details array

4. **Calendar Object:**
   - Key harus format YYYY-MM-DD
   - Jangan include weekend/holiday
   - Setiap entry harus punya minimal field `status`

**Mobile Display:**
- **Calendar View:** Tampilkan dots dengan warna sesuai status
  - Present: Primary color (hijau)
  - Late: Tertiary color (merah)
  - Field Duty: Secondary container (kuning)
  - Leave: Primary fixed (biru muda)
  - Absent: Surface container high (abu-abu)
- **List View:** Tampilkan detail per hari dengan AttendanceListItem
- **Summary Card:** Tampilkan ringkasan statistik bulan ini

**Mobile Behavior:**
- Load data saat screen mount
- Refresh saat pull-to-refresh
- Filter by month dengan month picker
- Cache data untuk performa
- Show loading state saat fetch data

**Business Rules:**
- Hanya tampilkan working days (Senin-Jumat)
- Weekend dan holiday tidak dihitung sebagai absent
- Late jika check in > 08:00 WIB
- Field duty dan leave otomatis dari approval
- Data hanya untuk user yang login

---

### 2. Get Attendance Detail by Date
**Endpoint:** `GET /attendance/detail/{date}`

**Mobile Screen:** `src/screens/Rekap/RekapScreen.tsx` (untuk detail modal)

**Headers:** Authorization required

**Path Parameters:**
- `date`: YYYY-MM-DD format (e.g., "2024-10-24")

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "date": "2024-10-24",
    "day_name": "Kamis",
    "status": "present",
    "check_in_time": "08:00:00",
    "check_out_time": "17:00:00",
    "check_in_photo": "https://api.walinagari.id/storage/attendance/checkin_123.jpg",
    "check_out_photo": "https://api.walinagari.id/storage/attendance/checkout_123.jpg",
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

**Mobile Usage:**
- Tap pada AttendanceListItem untuk lihat detail
- Show modal dengan foto check in/out
- Tampilkan lokasi dan waktu lengkap
- Button untuk view photo full screen

---

### 3. Get Attendance Statistics
**Endpoint:** `GET /attendance/statistics`

**Mobile Screen:** `src/screens/Rekap/RekapScreen.tsx` (untuk summary card)

**Headers:** Authorization required

**Query Parameters:**
- `month`: YYYY-MM format (optional, default: current month)
- `year`: YYYY format (optional, default: current year)

**Success Response (200):**
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

**Mobile Display:**
- Card dengan statistik ringkasan
- Progress bar untuk attendance rate
- Comparison dengan bulan sebelumnya
- Icon dan warna sesuai status

---

## File Upload

### Upload Document
**Endpoint:** `POST /upload`

**Mobile Implementation:**
- Library: `@react-native-documents/picker`
- Used in: DinasScreen, IzinScreen

**Request (multipart/form-data):**
```
file: [File]
type: field_duty
```

**File Types:**
- `field_duty`: Untuk dokumen surat tugas
- `leave`: Untuk dokumen pendukung izin

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "url": "https://api.walinagari.id/storage/documents/abc123.pdf",
    "filename": "surat_tugas.pdf",
    "size": 2048576,
    "mime_type": "application/pdf",
    "uploaded_at": "2024-10-24T10:30:00+07:00"
  }
}
```

**Validation:**
- Max size: 5MB (5120 KB)
- Allowed types: PDF, JPG, JPEG, PNG
- Validated di mobile sebelum upload

**Mobile File Picker:**
```typescript
import DocumentPicker from '@react-native-documents/picker';

const result = await DocumentPicker.pick({
  type: [DocumentPicker.types.pdf, DocumentPicker.types.images],
});
```

**Alternative Implementation:**
File bisa langsung di-upload bersamaan dengan form submit di endpoint `/field-duty` atau `/leave` (tidak perlu endpoint terpisah).

---

## Response Format

### Success Response
```json
{
   "success": true,
  "data": {
    // response data
  },
  "message": "Optional success message"
}
```

### Error Response
```json
{
  "message": "Error message",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

**Note:** Laravel 12 default validation error format uses `message` and `errors` keys (without `success` wrapper). For consistency, backend should wrap this in a custom response format or frontend should handle both formats.

---

## Error Handling

### HTTP Status Codes
- `200` - OK (Success)
- `201` - Created (Resource created)
- `400` - Bad Request
- `401` - Unauthorized (Invalid/missing token)
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

### Validation Error Example (422 Unprocessable Entity)

**Laravel 12 Default Format:**
```json
{
  "message": "The start date field is required. (and 2 more errors)",
  "errors": {
    "start_date": ["The start date field is required."],
    "end_date": ["The end date must be a date after start date."],
    "document": ["The document must not be greater than 5120 kilobytes."]
  }
}
```

**Custom Wrapped Format (Recommended for Consistency):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "start_date": ["The start date field is required."],
    "end_date": ["The end date must be a date after start date."],
    "document": ["The document must not be greater than 5120 kilobytes."]
  }
}
```

**Important:** Backend team should decide whether to use Laravel's default format or wrap it with `success: false` for consistency with success responses.

### Mobile Error Handling
```typescript
try {
  const response = await api.post('/field-duty', data);
  Alert.alert('Berhasil', response.data.message);
} catch (error) {
  if (error.response?.status === 422) {
    // Validation error
    const errors = error.response.data.errors;
    Alert.alert('Validasi Gagal', Object.values(errors).flat().join('\n'));
  } else if (error.response?.status === 401) {
    // Unauthorized - logout
    await authService.logout();
  } else {
    // General error
    Alert.alert('Error', error.response?.data?.message || 'Terjadi kesalahan');
  }
}
```

---

## Laravel 12 Implementation Guide

### 1. Routes Structure
```php
// routes/api.php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FieldDutyController;
use App\Http\Controllers\Api\LeaveController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);
    
    Route::get('office/location', [OfficeController::class, 'getLocation']);
    
    Route::post('field-duty', [FieldDutyController::class, 'store']);
    Route::get('field-duty', [FieldDutyController::class, 'index']);
    
    Route::post('leave', [LeaveController::class, 'store']);
    Route::get('leave', [LeaveController::class, 'index']);
    
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('attendance/today', [AttendanceController::class, 'getTodayStatus']);
    
    Route::post('upload', [UploadController::class, 'store']);
});
```

### 2. Sanctum Configuration
```php
// config/sanctum.php
return [
    'expiration' => null, // Token tidak expire
    'token_prefix' => '',
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
        'validate_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    ],
];
```

### 3. Response Helper (Custom Wrapper)

**IMPORTANT:** Laravel 12 validation errors return format:
```json
{
  "message": "The field is required. (and X more errors)",
  "errors": { "field": ["error message"] }
}
```

To maintain consistency with success responses, create a custom response helper:

```php
// app/Helpers/ApiResponse.php
namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Success response with data
     */
    public static function success($data = null, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    /**
     * Error response (wraps Laravel validation format)
     */
    public static function error(string $message, $errors = null, int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if ($errors) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $code);
    }
    
    /**
     * Validation error response (422)
     * Wraps Laravel's default validation error format
     */
    public static function validationError(\Illuminate\Validation\ValidationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $exception->errors(),
        ], 422);
    }
}
```

**Exception Handler (app/Exceptions/Handler.php):**
```php
use App\Helpers\ApiResponse;
use Illuminate\Validation\ValidationException;

public function render($request, Throwable $exception)
{
    // Wrap validation errors for API requests
    if ($exception instanceof ValidationException && $request->expectsJson()) {
        return ApiResponse::validationError($exception);
    }
    
    return parent::render($request, $exception);
}
```

### 4. Auth Controller Example
```php
// app/Http/Controllers/Api/AuthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            return ApiResponse::error('Username atau password salah', null, 401);
        }

        $user = Auth::user();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip,
                'position' => $user->position,
                'department' => $user->department,
                'avatar' => $user->avatar_url,
            ],
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::success(null, 'Logout berhasil');
    }

    public function user(Request $request)
    {
        return ApiResponse::success($request->user());
    }
}
```

### 5. Field Duty Controller Example (Laravel 12)
```php
// app/Http/Controllers/Api/FieldDutyController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\FieldDuty;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Carbon\Carbon;

class FieldDutyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string',
            'document' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(5 * 1024)],
        ]);

        // Upload file with custom name
        $file = $request->file('document');
        $filename = time() . '_' . $file->getClientOriginalName();
        $documentPath = $file->storeAs('documents/field-duty', $filename, 'public');
        
        // Calculate total days
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Create field duty
        $fieldDuty = FieldDuty::create([
            'user_id' => $request->user()->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'destination' => $validated['destination'],
            'purpose' => $validated['purpose'],
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        return ApiResponse::success([
            'id' => $fieldDuty->id,
            'start_date' => $fieldDuty->start_date,
            'end_date' => $fieldDuty->end_date,
            'total_days' => $fieldDuty->total_days,
            'destination' => $fieldDuty->destination,
            'purpose' => $fieldDuty->purpose,
            'document_url' => asset('storage/' . $fieldDuty->document_path),
            'status' => $fieldDuty->status,
            'submitted_at' => $fieldDuty->created_at->toIso8601String(),
        ], 'Pengajuan dinas berhasil dikirim', 201);
    }

    public function index(Request $request)
    {
        $limit = $request->query('limit', 3);
        
        $duties = FieldDuty::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($duty) {
                return [
                    'id' => $duty->id,
                    'date' => Carbon::parse($duty->created_at)->format('d M Y'),
                    'start_date' => $duty->start_date,
                    'end_date' => $duty->end_date,
                    'total_days' => $duty->total_days,
                    'destination' => $duty->destination,
                    'purpose' => $duty->purpose,
                    'status' => $duty->status,
                    'submitted_at' => $duty->created_at->toIso8601String(),
                ];
            });

        return ApiResponse::success($duties);
    }
}
```

### 6. Database Migration Example
```php
// database/migrations/xxxx_create_field_duties_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('field_duties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->string('destination');
            $table->text('purpose');
            $table->string('document_path');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('field_duties');
    }
};
```

### 7. Attendance Recap Controller Example (Laravel 12)
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Attendance;
use App\Models\FieldDuty;
use App\Models\Leave;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceRecapController extends Controller
{
    public function getMonthlyRecap(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $userId = $request->user()->id;
        $month = $validated['month'];
        
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        // Get all attendances for the month
        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $startDate->year)
            ->whereMonth('date', $startDate->month)
            ->get()
            ->keyBy('date');

        // Get approved field duties
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

        // Get approved leaves
        $leaves = Leave::where('user_id', $userId)
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

        $calendar = [];
        $details = [];
        $summary = [
            'total_days' => $endDate->day,
            'working_days' => 0,
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'field_duty' => 0,
            'leave' => 0,
        ];

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateString = $current->format('Y-m-d');
            $dayName = $current->locale('id')->dayName;
            
            // Skip weekends
            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $summary['working_days']++;

            // Check if date is in field duty period
            $isFieldDuty = $fieldDuties->first(function ($duty) use ($current) {
                return $current->between(
                    Carbon::parse($duty->start_date),
                    Carbon::parse($duty->end_date)
                );
            });

            // Check if date is in leave period
            $isLeave = $leaves->first(function ($leave) use ($current) {
                return $current->between(
                    Carbon::parse($leave->start_date),
                    Carbon::parse($leave->end_date)
                );
            });

            // Determine status and build response
            if ($isFieldDuty) {
                $calendar[$dateString] = [
                    'status' => 'field_duty',
                    'description' => $isFieldDuty->purpose,
                ];
                $details[] = [
                    'date' => $dateString,
                    'day_name' => $dayName,
                    'status' => 'field_duty',
                    'description' => $isFieldDuty->purpose,
                    'check_in' => null,
                    'check_out' => null,
                    'working_hours' => null,
                    'is_late' => false,
                ];
                $summary['field_duty']++;
            } elseif ($isLeave) {
                $calendar[$dateString] = [
                    'status' => 'leave',
                    'description' => $isLeave->reason,
                ];
                $details[] = [
                    'date' => $dateString,
                    'day_name' => $dayName,
                    'status' => 'leave',
                    'description' => $isLeave->reason,
                    'check_in' => null,
                    'check_out' => null,
                    'working_hours' => null,
                    'is_late' => false,
                ];
                $summary['leave']++;
            } elseif (isset($attendances[$dateString])) {
                $attendance = $attendances[$dateString];
                
                // CRITICAL: Determine if late (check_in > 08:00:00)
                $checkInTime = Carbon::parse($attendance->check_in_time);
                $isLate = $checkInTime->format('H:i:s') > '08:00:00';
                
                // Status is "late" if late, otherwise "present"
                $status = $isLate ? 'late' : 'present';
                
                $calendar[$dateString] = [
                    'status' => $status,
                    'check_in' => $attendance->check_in_time,
                    'check_out' => $attendance->check_out_time,
                    'is_late' => $isLate,
                ];
                
                $details[] = [
                    'date' => $dateString,
                    'day_name' => $dayName,
                    'status' => $status,
                    'check_in' => $attendance->check_in_time,
                    'check_out' => $attendance->check_out_time,
                    'working_hours' => $attendance->working_hours,
                    'is_late' => $isLate,
                ];
                
                // CRITICAL: Count separately for present and late
                if ($isLate) {
                    $summary['late']++;
                } else {
                    $summary['present']++;
                }
            } else {
                // No attendance record = absent
                $calendar[$dateString] = [
                    'status' => 'absent',
                ];
                $details[] = [
                    'date' => $dateString,
                    'day_name' => $dayName,
                    'status' => 'absent',
                    'check_in' => null,
                    'check_out' => null,
                    'working_hours' => null,
                    'is_late' => false,
                ];
                $summary['absent']++;
            }

            $current->addDay();
        }

        // CRITICAL: Sort details by date DESC (newest first)
        usort($details, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return ApiResponse::success([
            'month' => $month,
            'month_name' => $startDate->locale('id')->monthName,
            'year' => $startDate->year,
            'summary' => $summary,
            'calendar' => $calendar,
            'details' => $details, // Mobile will slice(0, 10)
        ], 'Rekapitulasi absensi berhasil dimuat');
    }

    public function getAttendanceDetail(Request $request, $date)
    {
        $userId = $request->user()->id;
        
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            return ApiResponse::error('Data absensi tidak ditemukan', null, 404);
        }

        $carbonDate = Carbon::parse($date);
        $checkInTime = Carbon::parse($attendance->check_in_time);
        $isLate = $checkInTime->format('H:i:s') > '08:00:00';
        $status = $isLate ? 'late' : 'present';

        return ApiResponse::success([
            'date' => $date,
            'day_name' => $carbonDate->locale('id')->dayName,
            'status' => $status,
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $attendance->check_out_time,
            'check_in_photo' => asset('storage/' . $attendance->check_in_photo),
            'check_out_photo' => $attendance->check_out_photo ? asset('storage/' . $attendance->check_out_photo) : null,
            'check_in_location' => [
                'latitude' => $attendance->check_in_latitude,
                'longitude' => $attendance->check_in_longitude,
                'address' => 'Kantor Wali Nagari',
            ],
            'check_out_location' => $attendance->check_out_latitude ? [
                'latitude' => $attendance->check_out_latitude,
                'longitude' => $attendance->check_out_longitude,
                'address' => 'Kantor Wali Nagari',
            ] : null,
            'working_hours' => $attendance->working_hours,
            'is_late' => $isLate,
            'notes' => null,
        ], 'Detail absensi berhasil dimuat');
    }

    public function getStatistics(Request $request)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $userId = $request->user()->id;
        
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        // Count working days (exclude weekends)
        $workingDays = 0;
        $current = $startDate->copy();
        while ($current <= $endDate) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $startDate->year)
            ->whereMonth('date', $startDate->month)
            ->get();

        $presentDays = 0;
        $lateDays = 0;
        
        foreach ($attendances as $attendance) {
            $checkInTime = Carbon::parse($attendance->check_in_time);
            if ($checkInTime->format('H:i:s') > '08:00:00') {
                $lateDays++;
            } else {
                $presentDays++;
            }
        }

        $fieldDutyDays = FieldDuty::where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->sum('total_days');

        $leaveDays = Leave::where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->sum('total_days');

        $absentDays = $workingDays - $presentDays - $lateDays - $fieldDutyDays - $leaveDays;
        $totalPresent = $presentDays + $lateDays;
        $attendanceRate = $workingDays > 0 ? round(($totalPresent / $workingDays) * 100, 2) : 0;
        $punctualityRate = $totalPresent > 0 ? round(($presentDays / $totalPresent) * 100, 2) : 0;

        return ApiResponse::success([
            'period' => [
                'month' => $startDate->locale('id')->monthName,
                'year' => $startDate->year,
                'month_number' => $startDate->month,
            ],
            'statistics' => [
                'total_working_days' => $workingDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => max(0, $absentDays),
                'field_duty_days' => $fieldDutyDays,
                'leave_days' => $leaveDays,
                'attendance_rate' => $attendanceRate,
                'punctuality_rate' => $punctualityRate,
            ],
        ], 'Statistik absensi berhasil dimuat');
    }
}
```

### 8. Routes for Attendance Recap
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('attendance/recap', [AttendanceRecapController::class, 'getMonthlyRecap']);
    Route::get('attendance/detail/{date}', [AttendanceRecapController::class, 'getAttendanceDetail']);
    Route::get('attendance/statistics', [AttendanceRecapController::class, 'getStatistics']);
});
```

---

## Mobile Integration Checklist

### Backend Tasks
- [ ] Setup Laravel 12 project
- [ ] Install & configure Sanctum
- [ ] Create database migrations (users, field_duties, leaves, attendances)
- [ ] Create models (User, FieldDuty, Leave, Attendance)
- [ ] Implement Auth endpoints (login, logout, user)
- [ ] Implement Field Duty endpoints (store, index)
- [ ] Implement Leave endpoints (store, index)
- [ ] Implement Attendance endpoints (check-in, check-out, today, office/location)
- [ ] Implement Attendance Recap endpoints (recap, detail, statistics)
- [ ] Setup file storage (public disk)
- [ ] Configure CORS for mobile app
- [ ] Configure timezone to Asia/Jakarta (WIB)
- [ ] Seed test data
- [ ] Test all endpoints with Postman

### Frontend Tasks
- [ ] Update `API_BASE_URL` in `src/services/api.config.ts`
- [ ] Change `USE_MOCK: false` in `src/services/api.config.ts`
- [ ] Test login flow
- [ ] Test field duty submission
- [ ] Test leave submission
- [ ] Test attendance check-in/out
- [ ] Test attendance recap calendar
- [ ] Test file upload
- [ ] Handle all error cases
- [ ] Test token expiration handling

### CRITICAL - Backend Implementation Notes for Recap API

**1. Status Logic (MUST FOLLOW):**
```php
// Determine status based on check_in time
$checkInTime = Carbon::parse($attendance->check_in_time);
$isLate = $checkInTime->format('H:i:s') > '08:00:00';
$status = $isLate ? 'late' : 'present'; // NOT both 'present'!
```

**2. Summary Counting (MUST BE SEPARATE):**
```php
// Count present and late separately
if ($isLate) {
    $summary['late']++;  // Increment late counter
} else {
    $summary['present']++;  // Increment present counter
}
// DO NOT count late as present!
```

**3. Details Array Sorting:**
```php
// Sort by date DESC (newest first)
usort($details, function($a, $b) {
    return strcmp($b['date'], $a['date']);
});
```

**4. Mobile Expects These Exact Fields:**
- `summary.present` - Only non-late attendance
- `summary.late` - Only late attendance
- `summary.field_duty` - Approved field duties
- `summary.absent` - No attendance record
- `calendar[date].status` - Must be one of: present/late/field_duty/leave/absent
- `details[].status` - Same as calendar status
- `details[].description` - For field_duty and leave only

**5. Common Mistakes to Avoid:**
- ❌ Counting late as present in summary
- ❌ Not sorting details by date DESC
- ❌ Including weekends in calendar/details
- ❌ Wrong status logic (using 'present' for late attendance)
- ❌ Missing `working_hours` field in details
- ❌ Missing `is_late` boolean field

---

## Important Notes

### Date Format
- **Request:** `YYYY-MM-DD` (e.g., "2024-10-24")
- **Response:** `YYYY-MM-DD` or `ISO 8601` (e.g., "2024-10-24T10:30:00+07:00")
- **Display:** Mobile akan format ke bahasa Indonesia

### Timezone Configuration

**Laravel 12 Configuration:**
```php
// config/app.php
'timezone' => 'Asia/Jakarta', // WIB (UTC+7)
```

**Response Format:**
- Semua timestamp menggunakan **WIB (UTC+7)**
- Format ISO 8601 dengan timezone: `2024-10-24T10:30:00+07:00`
- Use Carbon: `$date->toIso8601String()` or `$date->format('c')`

**Important:** Set timezone in `config/app.php` to ensure all timestamps are in WIB.

### File Upload
- Max size: **5MB** (5120 KB)
- Allowed types: **PDF, JPG, JPEG, PNG**
- Validated di mobile sebelum upload
- Response harus return URL yang bisa diakses

### Status Values
- `pending`: Menunggu persetujuan
- `approved`: Disetujui
- `rejected`: Ditolak

### Business Rules
- Field Duty: start_date harus **after today** (tidak bisa pilih hari ini/kemarin)
- Leave: start_date bisa **today** (untuk sakit mendadak)
- Document: **required** untuk Field Duty, **optional** untuk Leave
- List: Selalu return **3 data terbaru** (limit=3)

---

## Testing

### Test Accounts
Buat test accounts di seeder:
```php
// database/seeders/UserSeeder.php
User::create([
    'username' => 'ahmad.rizki',
    'password' => Hash::make('password123'),
    'name' => 'Ahmad Rizki',
    'email' => 'ahmad.rizki@walinagari.id',
    'nip' => '199001012020011001',
    'position' => 'Staff Administrasi',
    'department' => 'Pemerintahan',
]);
```

### Postman Collection
Request Postman collection untuk testing:
- All endpoints documented
- Example requests & responses
- Environment variables (dev, production)

---

## Contact & Support

**Frontend Team:**
- Mobile App: React Native CLI 0.85.1
- Service Layer: `src/services/`
- Ready to integrate

**Backend Team:**
- Framework: Laravel 12
- Authentication: Sanctum
- Need to implement endpoints

**Next Steps:**
1. Backend implement endpoints sesuai dokumentasi ini
2. Share API base URL & test credentials
3. Frontend switch `USE_MOCK: false`
4. Integration testing
5. Bug fixing & optimization

---

---

## Laravel 12 Best Practices & Considerations

### 1. Validation Error Response Format

**CRITICAL:** Laravel 12 default validation error format differs from custom success responses:

**Laravel Default (422):**
```json
{
  "message": "The field is required. (and X more errors)",
  "errors": { "field": ["error message"] }
}
```

**Custom Success Format:**
```json
{
  "success": true,
  "data": {},
  "message": "Success message"
}
```

**Solution Options:**

**Option A: Wrap All Responses (Recommended)**
- Modify `app/Exceptions/Handler.php` to wrap validation errors
- Ensures consistent `success` field across all responses
- Frontend can always check `success: true/false`

**Option B: Frontend Handles Both Formats**
- Check for `success` field first
- If missing, check for `errors` field (validation error)
- More flexible but requires frontend logic

**Recommendation:** Use Option A for consistency.

---

### 2. File Upload Best Practices

**Use Laravel 12 Fluent File Validation:**
```php
use Illuminate\Validation\Rules\File;

// Modern approach (Laravel 12)
'document' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(5 * 1024)]

// Old approach (still works)
'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
```

**File Storage:**
```php
// Store with auto-generated name
$path = $request->file('document')->store('documents/field-duty', 'public');

// Store with custom name (recommended for tracking)
$filename = time() . '_' . $file->getClientOriginalName();
$path = $file->storeAs('documents/field-duty', $filename, 'public');
```

**Important:** Run `php artisan storage:link` to create symbolic link for public storage.

---

### 3. Timezone Configuration

**Set in config/app.php:**
```php
'timezone' => 'Asia/Jakarta', // WIB (UTC+7)
```

**Use Carbon for Date Formatting:**
```php
// ISO 8601 format with timezone
$date->toIso8601String(); // "2024-10-24T10:30:00+07:00"

// Or use format()
$date->format('c'); // Same as ISO 8601
```

**Important:** All timestamps will automatically use Asia/Jakarta timezone after configuration.

---

### 4. Sanctum Token Authentication

**No Expiration by Default:**
```php
// config/sanctum.php
'expiration' => null, // Token never expires
```

**Token Creation:**
```php
$token = $user->createToken('mobile-app')->plainTextToken;
```

**Token Revocation (Logout):**
```php
$request->user()->currentAccessToken()->delete();
```

**Middleware:**
```php
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes
});
```

---

### 5. CORS Configuration

**For React Native Mobile App:**

```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Or specific origins in production
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

**Important:** In production, restrict `allowed_origins` to specific domains.

---

### 6. API Response Consistency Checklist

- [ ] All success responses have `success: true`
- [ ] All error responses have `success: false`
- [ ] Validation errors wrapped with `success: false` (via Exception Handler)
- [ ] All timestamps use ISO 8601 format with timezone
- [ ] All file URLs use `asset('storage/...')` helper
- [ ] All dates formatted consistently (YYYY-MM-DD for API, localized for display)
- [ ] HTTP status codes used correctly (200, 201, 400, 401, 422, 500)

---

### 7. Database Considerations

**Use Proper Column Types:**
```php
// Dates
$table->date('start_date');
$table->date('end_date');

// Times
$table->time('check_in_time');
$table->time('check_out_time');

// Coordinates (decimal with precision)
$table->decimal('latitude', 10, 8);
$table->decimal('longitude', 11, 8);

// Enums for status
$table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

// File paths
$table->string('document_path');
```

**Indexes for Performance:**
```php
$table->index('user_id');
$table->index('date');
$table->index(['user_id', 'date']); // Composite index for attendance queries
```

---

### 8. Error Messages in Bahasa Indonesia

**Custom Validation Messages:**
```php
$request->validate([
    'start_date' => 'required|date|after:today',
], [
    'start_date.required' => 'Tanggal mulai wajib diisi',
    'start_date.after' => 'Tanggal mulai harus setelah hari ini',
]);
```

**Or use Language Files:**
```php
// resources/lang/id/validation.php
return [
    'required' => ':attribute wajib diisi.',
    'after' => ':attribute harus setelah :date.',
    // ... more translations
];
```

---

### 9. Testing Endpoints

**Use Laravel HTTP Tests:**
```php
public function test_user_can_submit_field_duty()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/field-duty', [
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(4)->format('Y-m-d'),
            'destination' => 'Padang',
            'purpose' => 'Rapat koordinasi',
            'document' => UploadedFile::fake()->create('document.pdf', 1024),
        ]);
    
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Pengajuan dinas berhasil dikirim',
        ]);
}
```

---

### 10. Performance Optimization

**Eager Loading for Relationships:**
```php
// Avoid N+1 queries
$duties = FieldDuty::with('user')
    ->where('user_id', $userId)
    ->get();
```

**Query Optimization:**
```php
// Use select() to limit columns
$duties = FieldDuty::select(['id', 'start_date', 'end_date', 'status'])
    ->where('user_id', $userId)
    ->limit(3)
    ->get();
```

**Caching for Office Location:**
```php
use Illuminate\Support\Facades\Cache;

$officeLocation = Cache::remember('office_location', 3600, function () {
    return OfficeSetting::first();
});
```

---

**Last Updated:** 2026-04-18  
**Version:** 2.1.0  
**Status:** Ready for Backend Implementation  
**New Features:** DetailModal for viewing full submission details
