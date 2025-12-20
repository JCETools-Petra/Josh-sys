# 🚀 Quick Start - PMS Hotel System

## Langkah Cepat untuk Memulai

### 1. Clear Cache Laravel
Jalankan file batch yang sudah dibuat:
```
clear-cache.bat
```

Atau jalankan manual di terminal:
```bash
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear
C:\xampp\php\php.exe artisan view:clear
```

### 2. Akses Front Office

**URL yang benar:**
```
http://127.0.0.1:8000/frontoffice
```

**BUKAN:**
```
http://127.0.0.1:8000/property/frontoffice  ❌ (Salah!)
```

### 3. Login dengan Role: pengguna_properti

Setelah login, di sidebar kiri akan muncul menu baru:
- **Front Office** - Check-in/Check-out
- **Restaurant** - POS & Menu Management
- **Housekeeping** - Room Status Management

### 4. Struktur Menu PMS

```
📋 Dashboard Properti
👥 Front Office ← BARU!
   ├─ Dashboard Front Office
   ├─ Room Grid
   ├─ Check-In
   └─ Guest Details

🍽️ Restaurant ← BARU!
   ├─ POS (Point of Sale)
   └─ Menu Management

🧹 Housekeeping ← BARU!
   ├─ Room Status Dashboard
   └─ Cleaning Assignment

📅 Kalender
📝 Reservasi
💰 Pendapatan
```

### 5. Fitur Utama

#### Front Office Dashboard (`/frontoffice`)
- Statistik kamar (Total, Terisi, Siap, Kotor, Perbaikan)
- Check-in hari ini
- Check-out hari ini
- Daftar tamu saat ini
- Tombol cepat: Check-In, Room Grid, Restaurant

#### Check-In Tamu (`/frontoffice/check-in`)
- Form check-in lengkap
- Data tamu otomatis tersimpan
- Room status otomatis update
- Support multiple sources: Walk-in, OTA, TA, Corporate, dll

#### Check-Out Tamu
- Klik tombol "Check-Out" di dashboard
- **Daily income OTOMATIS ter-update!**
- Room status otomatis berubah ke "Kotor"

#### Restaurant POS (`/restaurant/pos`)
- Buat order makanan/minuman
- Support: Dine-in, Room Service, Takeaway, Delivery
- Room service otomatis link ke kamar tamu
- **Daily income OTOMATIS ter-update saat order completed!**

#### Housekeeping (`/housekeeping/dashboard`)
- Lihat semua kamar dan statusnya
- Assign cleaning staff
- Mark room as clean
- Room otomatis siap untuk booking baru

### 6. Otomatis Update Daily Income

**TIDAK PERLU INPUT MANUAL LAGI!**

System akan otomatis update tabel `daily_incomes` saat:

✅ **Check-out tamu:**
- Walk-in → Update `offline_room_income`
- OTA → Update `online_room_income`
- Travel Agent → Update `ta_income`
- Corporate → Update `corp_income`
- dst...

✅ **F&B Order Completed:**
- Order jam 06:00-10:59 → Update `breakfast_income`
- Order jam 11:00-15:59 → Update `lunch_income`
- Order jam 16:00-05:59 → Update `dinner_income`

### 7. Troubleshooting

#### Masalah: 404 Not Found

**Solusi:**
1. Pastikan mengakses `/frontoffice` bukan `/property/frontoffice`
2. Clear cache dengan `clear-cache.bat`
3. Restart development server

#### Masalah: Menu tidak muncul di sidebar

**Solusi:**
1. Pastikan login sebagai `pengguna_properti`
2. Clear browser cache (Ctrl + F5)
3. Logout dan login ulang

#### Masalah: Auto-calculation tidak jalan

**Solusi:**
1. Pastikan Observer terdaftar di `app/Providers/AppServiceProvider.php`
2. Clear cache: `clear-cache.bat`
3. Cek log error di `storage/logs/laravel.log`

### 8. Testing Flow

#### Test Check-In & Check-Out:
1. Buka `/frontoffice`
2. Klik "Check-In"
3. Isi form check-in (minimal: nama, nomor HP, tanggal check-in/out, rate)
4. Pilih source: Walk-in
5. Klik "Check In"
6. Verifikasi: Room status berubah ke "Occupied"
7. Klik "Check-Out" untuk tamu tersebut
8. Verifikasi: `daily_incomes` otomatis ter-update

#### Test F&B Order:
1. Buka `/restaurant/pos`
2. Pilih order type: Room Service
3. Pilih nomor kamar (yang ada tamu)
4. Tambah menu items
5. Create order
6. Update status → Completed
7. Process payment
8. Verifikasi: `daily_incomes` otomatis ter-update

### 9. Data yang Sudah Ada

Sistem PMS ini **tidak menghapus** data lama:
- Data MICE tetap ada
- Data reservasi tetap ada
- Data daily_incomes lama tetap ada
- Hanya menambahkan fitur baru

### 10. Dokumentasi Lengkap

Baca panduan lengkap di: `PMS_SETUP_GUIDE.md`

---

**Selamat Menggunakan Sistem PMS Hotel! 🎉**
