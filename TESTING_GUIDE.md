# Testing Guide - Date Override System

## Overview
Sistem ini memungkinkan developer untuk testing berbagai skenario tanggal tanpa harus mengubah tanggal sistem atau menunggu hari tertentu.

## Fitur
- ✅ Override tanggal sistem untuk testing
- ✅ Quick select untuk hari-hari dalam minggu
- ✅ Visual indicator saat test mode aktif
- ✅ Hanya aktif di environment `local`
- ✅ Mudah di-clear untuk kembali ke tanggal real

## Cara Menggunakan

### 1. Via UI Panel (Recommended)
1. Login ke aplikasi web
2. Lihat tombol kuning "Dev: Date Testing" di pojok kanan bawah
3. Klik tombol tersebut untuk membuka panel
4. Pilih tanggal atau gunakan quick select
5. Klik "Set Test Date"
6. Semua fitur akan menggunakan tanggal testing
7. Klik "Clear" untuk kembali ke tanggal real

### 2. Via URL (Manual)
```
# Set test date
http://localhost/dev/set-date/2026-04-21

# Clear test date
http://localhost/dev/clear-date

# Check current date info
http://localhost/dev/date-info
```

## Skenario Testing

### Testing Hari Kerja Normal (Senin-Jumat)
```
Set date: 2026-04-20 (Senin)
Set date: 2026-04-21 (Selasa)
Set date: 2026-04-22 (Rabu)
Set date: 2026-04-23 (Kamis)
Set date: 2026-04-24 (Jumat)
```

### Testing Weekend
```
Set date: 2026-04-25 (Sabtu)
Set date: 2026-04-26 (Minggu)
```

### Testing Hari Libur
1. Buat hari libur di menu "Hari Libur"
2. Set test date ke tanggal hari libur tersebut
3. Cek tampilan di halaman absensi

### Testing Flow Lengkap
1. **Senin (Normal)**: Set date 2026-04-20
   - Test check-in tepat waktu
   - Test check-in terlambat
   - Test check-out

2. **Selasa (Dinas)**: Set date 2026-04-21
   - Buat pengajuan dinas luar
   - Approve dinas
   - Cek status di absensi

3. **Rabu (Izin)**: Set date 2026-04-22
   - Buat pengajuan izin/cuti
   - Approve izin
   - Cek status di absensi

4. **Kamis (Terlambat)**: Set date 2026-04-23
   - Test check-in setelah jam 08:00
   - Cek badge "Terlambat"

5. **Sabtu (Weekend)**: Set date 2026-04-25
   - Cek badge "Akhir Pekan"
   - Test check-in di weekend (optional)

6. **Hari Libur**: Set date ke tanggal libur
   - Cek badge "Hari Libur"
   - Cek nama hari libur muncul

## API Testing

### Update API Controller untuk Testing
Jika ingin API juga menggunakan test date, update controller:

```php
use App\Helpers\DateHelper;

// Ganti
$date = now()->format('Y-m-d');

// Dengan
$date = DateHelper::today();
```

### Testing via Postman/Insomnia
```
# Set test date via query parameter
GET /api/attendance/today?test_date=2026-04-21

# Test date akan disimpan di session
```

## Tips Testing

1. **Rekap Bulanan**: Set date ke berbagai tanggal dalam bulan yang sama untuk melihat rekap
2. **Export**: Test export dengan berbagai range tanggal
3. **Dashboard**: Cek statistik dengan berbagai tanggal
4. **Notifikasi**: Test reminder dengan set date ke hari kerja

## Troubleshooting

### Panel tidak muncul?
- Pastikan environment adalah `local` (check `.env` file)
- Clear cache browser
- Refresh halaman

### Test date tidak berubah?
- Pastikan klik "Set Test Date"
- Check session dengan mengakses `/dev/date-info`
- Clear session dan set ulang

### Ingin disable fitur ini?
- Fitur otomatis disabled di production
- Atau hapus `<DevDatePanel />` dari layout

## Security Notes

⚠️ **PENTING**: 
- Fitur ini HANYA aktif di environment `local`
- Otomatis disabled di production
- Tidak ada risk di production environment
- Session-based, tidak mengubah database

## Contoh Use Case

### Testing Absensi Bulanan
```bash
# Set ke awal bulan
/dev/set-date/2026-04-01

# Buat beberapa data absensi
# ...

# Set ke pertengahan bulan
/dev/set-date/2026-04-15

# Buat lebih banyak data
# ...

# Set ke akhir bulan
/dev/set-date/2026-04-30

# Cek rekap bulanan
```

### Testing Laporan Tahunan
```bash
# Test berbagai bulan
/dev/set-date/2026-01-15
/dev/set-date/2026-06-15
/dev/set-date/2026-12-15

# Export yearly report
```

## Developer Notes

### Extend DateHelper
Jika perlu fungsi tambahan, edit `app/Helpers/DateHelper.php`:

```php
public static function setTestDate(string $date): void
{
    session(['test_date' => $date]);
}

public static function addDays(int $days): Carbon
{
    return self::now()->addDays($days);
}
```

### Update Controller
Ganti semua `now()` dengan `DateHelper::now()`:

```php
// Before
$today = now()->format('Y-m-d');

// After
$today = DateHelper::today();
```

---

**Happy Testing! 🚀**
