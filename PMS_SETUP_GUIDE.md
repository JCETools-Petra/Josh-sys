# 🏨 PANDUAN SETUP SISTEM PMS HOTEL

## 📋 Daftar Isi
1. [Pengenalan](#pengenalan)
2. [Fitur Utama](#fitur-utama)
3. [Cara Install](#cara-install)
4. [Struktur Database](#struktur-database)
5. [Cara Menggunakan](#cara-menggunakan)
6. [FAQ](#faq)

---

## 🎯 Pengenalan

Sistem PMS (Property Management System) ini adalah solusi lengkap untuk manajemen hotel yang **menghilangkan kebutuhan input manual** pada `daily_incomes`.

### ✨ Yang Berubah:

**SEBELUM (Input Manual):**
```
Pengguna harus input:
- Walk In Guest (Kamar): 5
- Walk In Guest (Pendapatan): Rp 500,000
- OTA (Kamar): 3
- OTA (Pendapatan): Rp 300,000
- Breakfast: Rp 150,000
... dan seterusnya
```

**SESUDAH (Otomatis):**
```
✅ Check-out tamu → Otomatis update daily_incomes
✅ F&B order selesai → Otomatis update daily_incomes
✅ Tidak perlu input manual lagi!
```

---

## 🚀 Fitur Utama

### 1. **Room Management** 🛏️
- 6 Status Kamar: Siap, Kotor, Terisi, Perbaikan, Rusak, Diblokir
- Tracking pembersihan terakhir
- Assignment housekeeping staff
- Floor & smoking preference

### 2. **Guest Management** 👤
- Database tamu lengkap (identitas, kontak, preferences)
- Guest type: Individual, Corporate, VIP, Group
- Statistics: Total stays, lifetime value
- Blacklist management

### 3. **Front Office (Check-in/Check-out)** ✅
- Check-in otomatis update room status
- Multi-source tracking (Walk-in, OTA, TA, Corporate, dll)
- BAR pricing tracking
- **AUTO-UPDATE `daily_incomes` saat checkout**

### 4. **Restaurant / F&B** 🍽️
- Menu management lengkap
- Order types: Dine-in, Room Service, Takeaway, Delivery
- Link ke room stay (jika room service)
- **AUTO-UPDATE `daily_incomes` saat order completed**

### 5. **Housekeeping** 🧹
- Room cleaning tracking
- Staff assignment
- Bulk operations (mark multiple rooms as clean)

### 6. **Auto-Calculation Daily Income** 💰
**TIDAK PERLU INPUT MANUAL LAGI!**

---

## 📦 Cara Install

### Step 1: Run Migrations

```bash
php artisan migrate
```

Migrations yang akan dijalankan:
1. `2025_12_19_100000_add_pms_columns_to_hotel_rooms_table.php` - Room status
2. `2025_12_19_100001_create_guests_table.php` - Guest data
3. `2025_12_19_100002_create_room_stays_table.php` - Check-in/out
4. `2025_12_19_100003_create_fnb_menu_items_table.php` - Menu
5. `2025_12_19_100004_create_fnb_orders_table.php` - Orders
6. `2025_12_19_100005_create_fnb_order_items_table.php` - Order details
7. `2025_12_19_100006_create_payment_transactions_table.php` - Payments

### Step 2: Update Existing Data (Optional)

Jika Anda sudah punya data `hotel_rooms`, perlu set status default:

```sql
UPDATE hotel_rooms SET status = 'vacant_clean' WHERE status IS NULL;
```

### Step 3: Seed Data Menu (Optional)

Buat beberapa menu item contoh untuk testing:

```bash
php artisan tinker
```

```php
$property = \App\Models\Property::first();

\App\Models\FnbMenuItem::create([
    'property_id' => $property->id,
    'name' => 'Nasi Goreng',
    'category' => 'lunch',
    'price' => 35000,
    'is_available' => true,
]);

\App\Models\FnbMenuItem::create([
    'property_id' => $property->id,
    'name' => 'Kopi',
    'category' => 'beverage',
    'price' => 15000,
    'is_available' => true,
]);
```

---

## 🗄️ Struktur Database

### Tabel Utama

#### 1. `guests` - Data Tamu
```
- id, first_name, last_name, email, phone
- id_number, id_type
- guest_type (individual, corporate, vip, group)
- total_stays, lifetime_value
```

#### 2. `room_stays` - Transaksi Kamar
```
- id, property_id, hotel_room_id, guest_id
- check_in_date, check_out_date
- source (walk_in, ota, ta, corporate, dll)
- room_rate_per_night, total_room_charge
- status (reserved, checked_in, checked_out)
```

#### 3. `hotel_rooms` - Status Kamar (Updated)
```
- id, property_id, room_number, room_type_id
- status (vacant_clean, vacant_dirty, occupied, maintenance, out_of_order, blocked)
- last_cleaned_at, assigned_hk_user_id
- floor, is_smoking
```

#### 4. `fnb_menu_items` - Menu Restoran
```
- id, property_id, name, category, price
- is_available, prep_time_minutes
```

#### 5. `fnb_orders` - Order F&B
```
- id, property_id, order_type
- room_stay_id, hotel_room_id (jika room service)
- subtotal, tax_amount, service_charge
- status, payment_status
```

---

## 📘 Cara Menggunakan

### A. Front Office - Check-in Tamu

**Route:** `/frontoffice/check-in`

**Langkah:**
1. Masuk ke halaman check-in
2. Pilih kamar yang tersedia
3. Isi data tamu:
   - Nama, email, telepon
   - Nomor identitas (KTP/Passport)
4. Pilih tanggal check-in & check-out
5. Pilih source (Walk-in, OTA, dll)
6. Klik "Check In"

**Yang Terjadi Otomatis:**
- ✅ Room status → `occupied`
- ✅ Data tamu tersimpan
- ✅ Daily occupancy updated
- ✅ Confirmation number generated

### B. Front Office - Check-out Tamu

**Route:** `/frontoffice` (klik tombol Check Out)

**Yang Terjadi Otomatis:**
- ✅ Room status → `vacant_dirty`
- ✅ Daily income OTOMATIS ter-update:
  - Jika source = walk_in → Update `offline_rooms` & `offline_room_income`
  - Jika source = ota → Update `online_rooms` & `online_room_income`
  - dst...
- ✅ Guest statistics updated

**Contoh:**
```
Tamu walk-in menginap 3 malam @ Rp 500,000/malam
Saat checkout:
→ daily_incomes untuk 3 tanggal akan otomatis ter-update:
  - offline_rooms: +1
  - offline_room_income: +500,000
```

### C. Restaurant - Buat Order

**Route:** `/restaurant/pos`

**Langkah:**
1. Pilih order type (Dine-in / Room Service / Takeaway)
2. Jika room service, pilih nomor kamar
3. Tambahkan menu items
4. Klik "Create Order"
5. Saat sudah siap, ubah status → "Completed"
6. Process payment

**Yang Terjadi Otomatis:**
- ✅ Daily income OTOMATIS ter-update:
  - Order jam 06:00-10:59 → Update `breakfast_income`
  - Order jam 11:00-15:59 → Update `lunch_income`
  - Order jam 16:00-05:59 → Update `dinner_income`

### D. Housekeeping - Update Room Status

**Route:** `/housekeeping/dashboard`

**Langkah:**
1. Lihat daftar kamar yang kotor
2. Assign housekeeping staff
3. Setelah bersih, klik "Mark as Clean"

**Yang Terjadi Otomatis:**
- ✅ Room status → `vacant_clean`
- ✅ `last_cleaned_at` updated
- ✅ Kamar tersedia untuk booking baru

---

## 🔄 Flow Lengkap

### Scenario: Tamu Walk-in Check-in

```
1. Receptionist: Buka /frontoffice/check-in
2. Pilih Room 101 (status: vacant_clean)
3. Input data tamu
4. Check-in date: 2025-12-19
5. Check-out date: 2025-12-21 (2 malam)
6. Rate: Rp 500,000/malam
7. Source: walk_in
8. Klik "Check In"

→ OTOMATIS:
  ✅ Room 101 status → occupied
  ✅ Tamu tersimpan di database
  ✅ RoomStay created (status: checked_in)
  ✅ Daily occupancy updated

9. (2 hari kemudian) Klik "Check Out"

→ OTOMATIS:
  ✅ Room 101 status → vacant_dirty
  ✅ RoomStay status → checked_out
  ✅ daily_incomes untuk 19 Des & 20 Des:
    - offline_rooms: +1
    - offline_room_income: +500,000
  ✅ Guest statistics updated
```

### Scenario: Room Service Order

```
1. Waiter: Buka /restaurant/pos
2. Order type: Room Service
3. Select Room: 101
4. Add items:
   - Nasi Goreng (Rp 35,000) x1
   - Kopi (Rp 15,000) x2
5. Klik "Create Order"

→ Order created (status: pending)

6. Kitchen: Update status → Preparing
7. Kitchen: Update status → Ready
8. Waiter: Deliver to room
9. Waiter: Update status → Completed
10. Process payment → Room Charge

→ OTOMATIS:
  ✅ fnb_orders created & linked to room_stay
  ✅ daily_incomes updated:
    - Jika order jam 12:00 → lunch_income: +65,000
  ✅ Total akan di-charge ke kamar saat check-out
```

---

## ❓ FAQ

### Q: Apakah data `daily_incomes` lama akan hilang?
**A:** TIDAK. Sistem baru ini hanya menambahkan data baru. Data lama tetap aman.

### Q: Bagaimana jika saya masih ingin input manual?
**A:** Anda masih bisa input manual melalui `/property/income/create`. Tapi dengan sistem PMS ini, seharusnya tidak perlu lagi.

### Q: Apakah MICE booking masih berfungsi?
**A:** YA. MICE booking tidak terpengaruh dan tetap berfungsi seperti biasa.

### Q: Bagaimana cara reset jika ada kesalahan?
**A:**
- Room status salah → Update manual via housekeeping dashboard
- Income salah → Edit via `/property/income/{id}/edit`
- Guest data salah → Bisa edit langsung di database

### Q: Apakah ada laporan untuk tracking ini?
**A:** Dashboard admin sudah otomatis menampilkan data dari `daily_incomes` yang sudah ter-update otomatis.

---

## 🛠️ Troubleshooting

### Issue: Auto-calculation tidak jalan

**Solusi:**
1. Pastikan Observer sudah terdaftar di `AppServiceProvider`:
```php
RoomStay::observe(RoomStayObserver::class);
FnbOrder::observe(FnbOrderObserver::class);
```

2. Clear cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: Room tidak bisa di-check-in

**Solusi:**
- Pastikan room status = `vacant_clean`
- Cek via housekeeping dashboard, ubah status jika perlu

### Issue: F&B order tidak update daily_incomes

**Solusi:**
- Pastikan order status = `completed`
- Pastikan order memiliki `property_id`

---

## 📞 Support

Jika ada pertanyaan atau issue:
1. Cek log error: `storage/logs/laravel.log`
2. Test di environment development dulu
3. Backup database sebelum production deployment

---

**Dibuat dengan ❤️ untuk Griya Hospitality Management**
