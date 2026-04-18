# API Documentation - Wali Nagari Digital Attendance

> Dokumentasi API untuk Tim Backend  
> Project: SIWALI - Sistem Informasi Wali Nagari  
> Frontend: React Native CLI 0.85.1  
> Backend: Laravel 12  
> Last Updated: 2026-04-18

---

## ATURAN KERJA
- **PENGGUNAAN CODINGAN** WAJIB TANYA KE CONTEXT7 TANPA ASUSMSI SENDIRI
- **TECH**  LARAVEL 12 INERTIA JS STARTERKIT REACT SUDAH DEFAULT SHADCN UI DI TAHUN 2026 dan POSTGRESQL 14 SCHEMA PUBLIC
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

3. **Leave (Izin & Cuti)**
   - Submit Request (dengan file upload opsional)
   - Get List (3 terbaru)

4. **File Upload**
   - PDF/JPG/PNG support
   - Max 5MB validation

### 🔄 FUTURE - Akan Dikembangkan Bersama
Fitur-fitur berikut akan dikembangkan bersama antara mobile dan backend:

- Attendance (Check In/Out dengan camera & GPS)
- Rekap Attendance (Calendar & History)
- Detail & Cancel untuk Field Duty/Leave
- Profile Update
- Admin Approval System

---

## 📋 Table of Contents

1. [Base Configuration](#base-configuration)
2. [Authentication](#authentication)
3. [Field Duty API](#field-duty-api)
4. [Leave API](#leave-api)
5. [File Upload](#file-upload)
6. [Response Format](#response-format)
7. [Error Handling](#error-handling)
8. [Laravel 12 Implementation Guide](#laravel-12-implementation-guide)

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
- **Components:** DateRangePicker, DocumentUpload, DutyStatusItem

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

**Validation Rules:**
- start_date: required, date, after:today
- end_date: required, date, after_or_equal:start_date
- destination: required, string, max:255
- purpose: required, string
- document: required, file, mimes:pdf,jpg,jpeg,png, max:5120 (5MB)

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

---

## Leave API

### Mobile Implementation
- **Screen:** `src/screens/Izin/IzinScreen.tsx`
- **Service:** `src/services/leaveService.ts`
- **Components:** DateRangePicker, PickerModal, DocumentUpload

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

**Validation Rules:**
- start_date: required, date
- end_date: required, date, after_or_equal:start_date
- type: required, in:sakit,izin,cuti
- reason: required, string
- document: nullable, file, mimes:pdf,jpg,jpeg,png, max:5120 (5MB)

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
  "success": false,
  "message": "Error message",
  "errors": {
    // validation errors (optional)
  }
}
```

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

### Validation Error Example
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "start_date": ["Start date is required"],
    "end_date": ["End date must be after start date"],
    "document": ["File size exceeds 5MB"]
  }
}
```

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
    
    Route::post('field-duty', [FieldDutyController::class, 'store']);
    Route::get('field-duty', [FieldDutyController::class, 'index']);
    
    Route::post('leave', [LeaveController::class, 'store']);
    Route::get('leave', [LeaveController::class, 'index']);
    
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

### 3. Response Helper
```php
// app/Helpers/ApiResponse.php
namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public static function error($message, $errors = null, $code = 400)
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

### 5. Field Duty Controller Example
```php
// app/Http/Controllers/Api/FieldDutyController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\FieldDuty;
use Illuminate\Http\Request;
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
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Upload file
        $documentPath = $request->file('document')->store('documents/field-duty', 'public');
        
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

---

## Mobile Integration Checklist

### Backend Tasks
- [ ] Setup Laravel 12 project
- [ ] Install & configure Sanctum
- [ ] Create database migrations (users, field_duties, leaves)
- [ ] Create models (User, FieldDuty, Leave)
- [ ] Implement Auth endpoints (login, logout, user)
- [ ] Implement Field Duty endpoints (store, index)
- [ ] Implement Leave endpoints (store, index)
- [ ] Setup file storage (public disk)
- [ ] Configure CORS for mobile app
- [ ] Seed test data
- [ ] Test all endpoints with Postman

### Frontend Tasks
- [ ] Update `API_BASE_URL` in `src/services/api.config.ts`
- [ ] Change `USE_MOCK: false` in `src/services/api.config.ts`
- [ ] Test login flow
- [ ] Test field duty submission
- [ ] Test leave submission
- [ ] Test file upload
- [ ] Handle all error cases
- [ ] Test token expiration handling

---

## Important Notes

### Date Format
- **Request:** `YYYY-MM-DD` (e.g., "2024-10-24")
- **Response:** `YYYY-MM-DD` or `ISO 8601` (e.g., "2024-10-24T10:30:00+07:00")
- **Display:** Mobile akan format ke bahasa Indonesia

### Timezone
- Semua timestamp menggunakan **WIB (UTC+7)**
- Format ISO 8601 dengan timezone: `2024-10-24T10:30:00+07:00`

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

**Last Updated:** 2026-04-18  
**Version:** 2.0.0  
**Status:** Ready for Backend Implementation
