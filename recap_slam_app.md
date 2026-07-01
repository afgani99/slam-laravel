# Rekap Aplikasi SLAM / SLA Monitoring

Dibuat: 2026-06-28  
Lokasi proyek: `/Applications/XAMPP/xamppfiles/htdocs/slam-laravel`

File ini dibuat agar AI/developer berikutnya bisa memahami aplikasi tanpa harus membaca ulang seluruh kode terlebih dahulu.

Aplikasi ini awalnya adalah aplikasi **PHP native** sederhana. File ini mencatat **struktur asli aplikasi lama** dan juga **perkembangan rebuild ke Laravel** mulai dari Phase 1 sampai Phase 4.

---

## 1. Gambaran Umum

**Nama/tampilan aplikasi:** SLA Monitoring System / SLAM  
**Jenis aplikasi:** Web app Laravel 12 MVC.  
**Framework backend:** Laravel 12 (PHP 8.2).  
**Database:** MySQL/MariaDB via Eloquent ORM.  
**Target runtime:** `php artisan serve` atau XAMPP (`http://localhost/slam-laravel/public`).  
**UI:** Breeze Blade (Tailwind CSS default).  
**Auth:** Laravel Breeze.

Aplikasi berfungsi untuk:

1. Menyimpan data CID/link pelanggan di tabel `cids`.
2. Membuat laporan gangguan/tiket di tabel `tickets`.
3. Mengubah status tiket `open` / `pending` / `closed`.
4. Pending tiket dengan multiple interval di tabel `ticket_pending_intervals`.
5. Menghitung SLA bulanan khusus untuk kasus `Link Down`.
6. Menampilkan daftar CID yang perlu restitusi jika SLA tercapai lebih kecil dari target SLA CID.

---

## 2. Perbandingan Struktur: Lama vs Laravel

### 2.1 Struktur Aplikasi Lama (PHP Native)

Root proyek:

```text
closed_ticket.php
daftar_cid.php
dashboard.php
detail_cid.php
edit_cid.php
edit_laporan.php
index.php
input_laporan.php
layout.php
layout_footer.php
sidebar.php
sla_bulanan.php
sla_rekap.php
tambah_cid.php
ticket_list.php
update_status.php
```

Tidak ada struktur framework, dependency manager, `.env`, migration, composer, npm, atau file SQL dump.

### 2.2 Struktur Aplikasi Baru (Laravel 12)

Proyek: `/Applications/XAMPP/xamppfiles/htdocs/slam-laravel`

```text
app/
  Http/
    Controllers/
      CidController.php          [Phase 3]
      DashboardController.php    (belum dibuat)
      TicketController.php       [Phase 4]
      TicketStatusController.php [Phase 4]
      SlaMonthlyController.php   (belum dibuat)
      SlaRestitutionController.php (belum dibuat)
      ProfileController.php
      Auth/                      (Breeze default)
    Requests/
      StoreCidRequest.php        [Phase 3]
      UpdateCidRequest.php       [Phase 3]
      StoreTicketRequest.php     [Phase 4]
      UpdateTicketRequest.php    [Phase 4]
      PendingTicketRequest.php   [Phase 4]
      ResumeTicketRequest.php    [Phase 4]
      CloseTicketRequest.php     [Phase 4]
  Models/
    Cid.php                      [Phase 2]
    Ticket.php                   [Phase 2]
    TicketPendingInterval.php    [Phase 2]
    User.php
  Services/
    TicketNumberService.php      [Phase 4]
    SlaService.php               (belum dibuat)

database/
  migrations/
    0001_01_01_000000_create_users_table.php           (default Laravel)
    0001_01_01_000001_create_cache_table.php           (default Laravel)
    0001_01_01_000002_create_jobs_table.php            (default Laravel)
    2026_06_28_035436_add_role_to_users_table.php      [Phase 2]
    2026_06_28_035436_create_cids_table.php            [Phase 2]
    2026_06_28_035436_create_tickets_table.php         [Phase 2]
    2026_06_28_035437_create_ticket_pending_intervals_table.php [Phase 2]
  seeders/
    DatabaseSeeder.php
    AdminUserSeeder.php           [Phase 2]

resources/
  views/
    layouts/
      app.blade.php              (Breeze default)
      navigation.blade.php       (Breeze default, ditambah menu Master CID di Phase 3)
    cids/                        [Phase 3]
      index.blade.php
      create.blade.php
      edit.blade.php
      show.blade.php
      _form.blade.php
    tickets/                     [Phase 4]
      index.blade.php
      create.blade.php
      edit.blade.php
      show.blade.php
      _form.blade.php
      _status-badge.blade.php
    dashboard.blade.php          (ditulis ulang Phase 1)

routes/
  web.php                        (ditambah route CID di Phase 3)
  auth.php                       (Breeze default)
```

---

## 3. Konfigurasi Database

### 3.1 Aplikasi Lama

```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sla_db";
$conn = new mysqli($host, $user, $pass, $db);
```

Koneksi ditulis berulang di hampir semua halaman. Database: **`sla_db`**.

### 3.2 Aplikasi Laravel

File `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sla_db
DB_USERNAME=root
DB_PASSWORD=
```

Semua koneksi diatur di `.env` oleh Laravel. Database: **`sla_db`**.

---

## 4. Tabel Database: Perbandingan Lama vs Laravel

### 4.1 CID / Link Data

| Aplikasi Lama (`cid_data`) | Laravel (`cids`) |
|---|---|
| `cid` | `cid` (unique) |
| `nama_vendor` | `vendor_name` |
| `nama_pelanggan` | `customer_name` |
| `service` | `service` |
| `sla_persen` | `sla_percentage` (decimal 5,2) |
| `created_at` | `created_at` + `updated_at` |
| — | `id` (auto increment) |

### 4.2 Ticket

Aplikasi lama memisahkan tiket terbuka (`opened_ticket`) dan tiket tertutup (`closed_ticket`).

Laravel menyimpan semua tiket di satu tabel (`tickets`) dengan kolom `status`:

| Laravel (`tickets`) | Keterangan |
|---|---|
| `id` | Auto increment |
| `ticket_number` | Unique, format `nusa-ddmmyyXXX` |
| `cid_id` | FK ke `cids.id` |
| `vendor_ticket_number` | Ticket ID dari vendor |
| `case_type` | `Link Down`, `Link High Latency`, dll |
| `started_at` | Waktu mulai gangguan |
| `finished_at` | Waktu selesai (nullable) |
| `rfo_action` | Root cause / action |
| `status` | `open`, `pending`, `closed`, `cancelled` |
| `closed_at` | Timestamp saat ditutup |
| `created_at` / `updated_at` | Default Laravel |

### 4.3 Pending Interval

Aplikasi lama hanya memiliki satu `pending_start` dan `pending_end` di tiket.

Laravel menyimpan interval pending di tabel terpisah (`ticket_pending_intervals`) — mendukung multiple pending per tiket.

| Kolom | Keterangan |
|---|---|
| `id` | Auto increment |
| `ticket_id` | FK ke `tickets.id` |
| `started_at` | Waktu mulai pending |
| `ended_at` | Waktu selesai pending (nullable) |
| `note` | Catatan pending |

### 4.4 Users

| Aplikasi Lama | Laravel |
|---|---|
| Tidak ada | `users` (Laravel default + `role`) |

Role:

```text
admin
operator
viewer
```

---

## 5. Entry Point dan Navigasi

### 5.1 Aplikasi Lama

- `index.php` redirect ke `dashboard.php`.
- `layout.php` sebagai template dengan Bootstrap + sidebar.
- `layout_footer.php` penutup.
- `sidebar.php` tidak terpakai.

### 5.2 Aplikasi Laravel

- `/` redirect ke `/dashboard`.
- Route di `routes/web.php` dan `routes/auth.php`.
- Layout Blade di `resources/views/layouts/app.blade.php`.
- Navigation di `resources/views/layouts/navigation.blade.php`.

Menu yang sudah aktif:

```text
Dashboard      -> /dashboard
Master CID     -> /cids                         [Phase 3]
Opened Ticket  -> /tickets?status=open          [Phase 4]
Pending Ticket -> /tickets?status=pending       [Phase 4]
Closed Ticket  -> /tickets?status=closed        [Phase 4]
```

---

## 6. Halaman dan Fungsi: Perbandingan

### 6.1 Dashboard

| Aplikasi Lama | Laravel |
|---|---|
| `dashboard.php` | `dashboard.blade.php` |
| 3 kartu: Opened, Closed, Restitusi | 3 kartu (masih placeholder) |
| Tombol Laporan Baru ke CID | — (belum dibuat) |

Status Phase 1: Halaman dashboard sudah ada, masih menampilkan angka 0 karena data belum terisi.

### 6.2 Daftar CID

| Aplikasi Lama | Laravel |
|---|---|
| `daftar_cid.php` | `cids.index` (index.blade.php) |
| Search: cid, vendor, pelanggan, service | Sama |
| Pagination: 10/25/50 | Sama |
| Sort: created_at DESC | Sort: latest() |
| Buat laporan → `input_laporan.php?cid=...` | Belum dibuat (akan di Phase 4) |
| Detail → `detail_cid.php?cid=...` | `cids.show` |
| Update → `edit_cid.php?cid=...` | `cids.edit` |
| Tambah CID → `tambah_cid.php` | `cids.create` |
| Prepared statement | Eloquent ORM |

Status Phase 3: Sudah selesai.

### 6.3 Tambah CID

| Aplikasi Lama | Laravel |
|---|---|
| `tambah_cid.php` | `cids.create` |
| Validasi: semua wajib, sla numeric | Form Request: required, numeric, min 0, max 100, unique |
| Insert langsung | Eloquent `Cid::create()` |
| Error: CID sudah ada | Validation message |
| CID bisa diedit | CID masih bisa diedit |

Status Phase 3: Sudah selesai.

### 6.4 Edit CID

| Aplikasi Lama | Laravel |
|---|---|
| `edit_cid.php?cid=...` | `cids.edit` / `cids.update` |
| CID disabled | CID masih bisa diubah |
| Update: vendor, pelanggan, service, sla | Sama + validasi unique |

Status Phase 3: Sudah selesai.

### 6.5 Detail CID

| Aplikasi Lama | Laravel |
|---|---|
| `detail_cid.php?cid=...` | `cids.show` |
| 3 tiket terakhir | 5 tiket terakhir |
| Grafik SLA 6 bulan Chart.js | Belum dibuat |
| Tabel tiket (closed only) | Semua status tiket |

Status Phase 3: Info CID + ringkasan tiket sudah selesai. Grafik SLA 6 bulan belum dibuat (akan di phase SLA).

### 6.6 Input Laporan / Ticket

| Aplikasi Lama | Laravel |
|---|---|
| `input_laporan.php?cid=...` | `tickets.create` / `tickets.store` |
| Generate ticket_id: `nusa-ddmmyyXXX` | `TicketNumberService` |
| Kasus: 4 pilihan | Sama (model `Ticket::CASE_TYPES`) |
| Insert ke `opened_ticket` | Insert ke `tickets` dengan status `open` |

Status Phase 4: Sudah selesai.

### 6.7 Daftar Tiket Open

| Aplikasi Lama | Laravel |
|---|---|
| `ticket_list.php` | `tickets.index?status=open` |
| Status badge: aktif (hijau), pending (kuning) | Badge `open` hijau, `pending` kuning, `closed` biru |
| Aksi: detail, edit, hapus, pending, lanjut | Detail/edit/hapus/status tersedia dari halaman detail |

Status Phase 4: Sudah selesai.

### 6.8 Update Status / Pending

| Aplikasi Lama | Laravel |
|---|---|
| `update_status.php` | `TicketStatusController` |
| Satu interval pending saja | `ticket_pending_intervals` mendukung multiple pending intervals |

Status Phase 4: Sudah selesai.

### 6.9 Edit / Tutup Tiket

| Aplikasi Lama | Laravel |
|---|---|
| `edit_laporan.php?ticket_id=...` | `tickets.edit` / `tickets.update` / `tickets.close` |
| Bug: close ticket pakai data lama | Diperbaiki: close membaca `finished_at` dan `rfo_action` dari request |
| Insert ke `closed_ticket`, delete dari `opened_ticket` | Update `status = closed` + `closed_at` saja — tidak pindah tabel |

Status Phase 4: Sudah selesai.

### 6.10 Daftar Tiket Closed

| Aplikasi Lama | Laravel |
|---|---|
| `closed_ticket.php` | `tickets.index?status=closed` |
| Modal detail tiket | Detail tersedia di `tickets.show` |

Status Phase 4: Sudah selesai.

### 6.11 SLA Bulanan

| Aplikasi Lama | Laravel |
|---|---|
| `sla_bulanan.php` | Belum dibuat |
| Search masih `real_escape_string` | Akan pakai Eloquent |
| Group by CID + bulan | Sama |

Status Phase 3: Belum dibuat (akan di phase SLA).

### 6.12 SLA Restitusi

| Aplikasi Lama | Laravel |
|---|---|
| `sla_rekap.php` | Belum dibuat |
| Hanya bulan berjalan | Akan dibuat + filter bulan |

Status Phase 3: Belum dibuat (akan di phase SLA).

---

## 7. Alur Bisnis: Perubahan yang Dipilih Saat Rebuild

### 7.1 Struktur Tabel Tiket

| Aplikasi Lama | Laravel |
|---|---|
| `opened_ticket` + `closed_ticket` | `tickets` (satu tabel, pakai status) |
| Pending: satu interval di tiket | `ticket_pending_intervals` (multiple) |
| Close: pindah data antar tabel | Update `status = closed` + `closed_at` |

### 7.2 Auth

| Aplikasi Lama | Laravel |
|---|---|
| Tidak ada | Login / register / logout (Breeze) |

### 7.3 Validasi

| Aplikasi Lama | Laravel |
|---|---|
| Validasi inline di tiap file | Form Request (StoreCidRequest, UpdateCidRequest) |

### 7.4 Delete Tiket

| Aplikasi Lama | Laravel |
|---|---|
| GET `ticket_list.php?hapus=...` | Method DELETE + CSRF di `tickets.destroy` |

### 7.5 Koneksi Database

| Aplikasi Lama | Laravel |
|---|---|
| `mysqli` berulang di tiap file | `.env` + Eloquent ORM |

### 7.6 Rumus SLA

Rumus tetap sama:

```text
downtime_menit = (waktu_selesai - waktu_mulai) - total_pending
total_menit_bulan = jumlah_hari_bulan * 24 * 60
sla_tercapai = ((total_menit_bulan - downtime_menit) / total_menit_bulan) * 100
```

Hanya kasus `Link Down` yang masuk perhitungan SLA.

Di Laravel, rumus ini akan dipusatkan di:

```text
app/Services/SlaService.php (belum dibuat)
```

---

## 8. Dependency & Package Terinstall

### 8.1 Composer (Production)

- `laravel/framework` ^12.0
- `laravel/tinker` ^2.10.1

### 8.2 Composer (Dev)

- `laravel/breeze` ^2.4 — auth scaffolding
- `fakerphp/faker` ^1.23
- `laravel/pail` ^1.2.2
- `laravel/pint` ^1.24
- `laravel/sail` ^1.41
- `mockery/mockery` ^1.6
- `nunomaduro/collision` ^8.6
- `phpunit/phpunit` ^11.5.50

### 8.3 NPM / Frontend

- Tailwind CSS v4 (Vite)
- Alpine.js (via Breeze Blade)

Auth scaffold sudah di-build ke `/public/build/`.

---

## 9. Status Rebuild per Phase

### ✅ Phase 1 — Setup Laravel

Status: Selesai.

- Laravel 12 project.
- `.env` ke MySQL `sla_db`.
- Breeze Blade auth scaffolding.
- Login / register / logout / profile.
- Dashboard awal.
- 25 test passed.

### ✅ Phase 2 — Database Structure

Status: Selesai.

- Migration `cids`.
- Migration `tickets`.
- Migration `ticket_pending_intervals`.
- Migration `add_role_to_users_table`.
- Model `Cid`, `Ticket`, `TicketPendingInterval` + relasi.
- Seeder admin: `admin@slam.local` / `password`.
- User model dengan role: `admin`, `operator`, `viewer`.

### ✅ Phase 3 — Master CID

Status: Selesai.

- `CidController` (resource, kecuali destroy).
- Route resource `cids` di dalam auth.
- `StoreCidRequest` / `UpdateCidRequest`.
- Views: `index`, `create`, `edit`, `show`, `_form`.
- Search + pagination (10/25/50).
- Navigation menu "Master CID".

### ✅ Phase 4 — Ticket Management

Status: Selesai.

- `TicketController`.
- `TicketStatusController`.
- `TicketNumberService`.
- `StoreTicketRequest`, `UpdateTicketRequest`, `PendingTicketRequest`, `ResumeTicketRequest`, `CloseTicketRequest`.
- Route resource `tickets`.
- Route status: `tickets.pending`, `tickets.resume`, `tickets.close`.
- Buat tiket baru dengan nomor otomatis `nusa-ddmmyyXXX`.
- List ticket berdasarkan status `open`, `pending`, `closed`.
- Edit ticket sebelum closed.
- Pending / resume dengan tabel `ticket_pending_intervals`.
- Close ticket dengan update status, tanpa pindah tabel.
- Delete ticket non-closed memakai method DELETE + CSRF.
- Feature test `TicketManagementTest`.

### ✅ Phase 5 — SLA Calculation dan Dashboard Real Data

Status: Selesai.

- `app/Services/SlaService.php`
- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard.blade.php`
- `database/factories/CidFactory.php`
- `database/factories/TicketFactory.php`
- `tests/Feature/SlaServiceTest.php`
- `routes/web.php`

Yang sudah aktif di Phase 5:

- Perhitungan downtime efektif per ticket.
- Hanya ticket `closed` dengan `case_type = Link Down` yang dihitung.
- Pending interval tanpa `ended_at` tidak dihitung.
- Downtime efektif di-clamp minimal 0.
- Perhitungan SLA bulanan per CID.
- Daftar CID yang perlu restitusi.
- Dashboard memakai data real dari database.

Catatan implementasi:

- Dashboard sekarang membaca statistik dari `SlaService`.
- Statistik dashboard: opened, pending, closed, total CID, dan restitution count.
- Test suite ditambah untuk memverifikasi SLA calculation.

### ✅ Phase 6 — SLA Bulanan, Restitusi, dan Grafik

Status: Selesai.

- `app/Http/Controllers/SlaMonthlyController.php`
- `app/Http/Controllers/SlaRestitutionController.php`
- `resources/views/sla/monthly.blade.php`
- `resources/views/sla/restitution.blade.php`
- `resources/views/cids/show.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `routes/web.php`
- `tests/Feature/SlaMonthlyControllerTest.php`
- `tests/Feature/SlaRestitutionControllerTest.php`
- `tests/Feature/CidSlaGraphTest.php`

Yang sudah aktif di Phase 6:

1. Halaman **SLA Bulanan** (`/sla/monthly`) dengan filter bulan dan tahun.
2. Halaman **Restitusi** (`/sla/restitution`) — hanya CID dengan SLA di bawah target.
3. Navigasi sidebar: **SLA Bulanan** dan **Restitusi**.
4. **Grafik SLA 6 bulan** di halaman detail CID (progress bar per bulan).
5. Tampilan ringkasan per CID (CID, vendor, pelanggan, service, target, downtime, SLA, status).
6. Semua perhitungan tetap menggunakan `SlaService`.
7. Test kontroller untuk memastikan halaman server-side bekerja dengan benar.
8. Semua checklist pada aplikasi lama sudah terpenuhi (Phase 4-6 selesai).

### ✅ Phase 7 — Export/Import Data

Status: Selesai.

- `app/Console/Commands/ExportSlaData.php`
- `app/Console/Commands/ImportLegacySlaData.php`
- `tests/Feature/LegacyImportExportTest.php`

Fitur yang diimplementasikan:

1. **Command `slam:export`** — Export data ke CSV:
   - Membuat file `cids_{timestamp}.csv`, `tickets_{timestamp}.csv`, dan `pending_intervals_{timestamp}.csv`
   - Disimpan di folder `storage/app/private/exports/`.
   - Format kolom sudah disesuaikan untuk konsumsi manusia (CID menggunakan kode CID dari relasi).
   - Dapat digunakan: `php artisan slam:export --path=exports`.

2. **Command `slam:import-legacy`** — Import data dari legacy app:
   - Membaca file `sla_db.sql` dan memparse tabel `cid_data`, `opened_ticket`, dan `closed_ticket`.
   - Memetakan kolom bahasa Indonesia ke struktur Laravel baru.
   - Menangani format tanggal yang tidak valid (`0000-00-00 00:00:00`).
   - Menangani berbagai kasus edge: null value, dash placeholder.
   - Opsi `--truncate` untuk hapus data baru sebelum import.
   - Menambahkan `ticket_pending_intervals` untuk ticket closed yang punya data pending.
   - Dapat digunakan: `php artisan slam:import-legacy --file=sla_db.sql --truncate`.

3. **Test migrasi data:**
   - `tests/Feature/LegacyImportExportTest.php` — 2 test.
   - Verifikasi export menghasilkan file CSV.
   - Verifikasi import membaca data legacy dengan benar.

Catatan implementasi:

- Kedua command menggunakan `updateOrCreate` supaya safe jika dijalankan ulang.
- Import tidak meng-hardcode ID (menggunakan `cid` string sebagai unique identifier).
- Semua perhitungan SLA tetap melalui `SlaService` — export/import hanya pengelolaan data.

Target utama Phase 6:

1. Buat halaman SLA bulanan untuk rekap per CID per bulan.
2. Buat halaman/daftar restitusi yang hanya menampilkan CID dengan SLA di bawah target.
3. Tambahkan filter bulan dan tahun untuk rekap SLA.
4. Tambahkan tampilan ringkasan per CID:
   - CID
   - vendor
   - pelanggan
   - service
   - target SLA
   - SLA tercapai
   - total downtime
   - total pending
   - status Aman / Perlu Restitusi
5. Buat grafik SLA 6 bulan pada detail CID atau dashboard.
6. Pastikan perhitungan tetap memakai `SlaService`, bukan duplikasi rumus di view/controller.
7. Tambahkan test untuk memastikan halaman SLA bulanan dan restitusi konsisten dengan service.

File yang kemungkinan dibuat/diubah di Phase 6:

```text
app/Http/Controllers/SlaMonthlyController.php
app/Http/Controllers/SlaRestitutionController.php
resources/views/sla/monthly.blade.php
resources/views/sla/restitution.blade.php
resources/views/cids/show.blade.php
resources/views/dashboard.blade.php
routes/web.php
tests/Feature/SlaMonthlyTest.php
tests/Feature/SlaRestitutionTest.php
```

Urutan pengerjaan yang disarankan:

1. Buat controller SLA bulanan.
2. Buat controller restitusi.
3. Buat view rekap bulanan.
4. Buat view restitusi.
5. Tambahkan grafik SLA 6 bulan.
6. Tambahkan test dan jalankan seluruh suite.

---

## 10. Akun untuk Development

```text
Email    : admin@slam.local
Password : password
Role     : admin
```

---

## 11. Catatan Menjalankan Aplikasi

|```bash
# Masuk ke folder
cd /Applications/XAMPP/xamppfiles/htdocs/slam-laravel

# Jalankan dev server
php artisan serve

# Jalankan test
php artisan test

# Seed ulang database
php artisan migrate:fresh --seed

# Import data legacy
php artisan slam:import-legacy --file=sla_db.sql

# Hapus tabel legacy (setelah yakin)
php artisan slam:cleanup-legacy

# Export data ke CSV
php artisan slam:export --path=export-hari-ini
```

Akses:

```text
http://127.0.0.1:8000
```

Login dengan:

```text
admin@slam.local / password
```

---

## 12. Apa yang Belum Selesai dari Aplikasi Lama

Belum dibuat di rebuild:

- [x] Buat tiket baru (Phase 4)
- [x] Opened ticket list (Phase 4)
- [x] Close ticket (Phase 4)
- [x] Pending / resume ticket (Phase 4)
- [x] Dashboard dengan data real
- [x] SLA Bulanan
- [x] Restitusi
- [x] Grafik SLA 6 bulan
- [x] Export data (Phase 7)
- [x] Import data dari aplikasi lama (Phase 7)

---

## 13. Catatan Perbaikan Bug

### Bug: `started_at` berubah saat close ticket (28 Jun 2026)

Penyebab:
- Kolom `tickets.started_at` dibuat sebagai `TIMESTAMP` di MySQL.
- MySQL dapat mengubah nilai TIMESTAMP secara otomatis saat row di-update.
- Saat ticket ditutup, `started_at` loncat ke waktu update.

Perbaikan:
- Migration `2026_06_28_050000_alter_ticket_datetime_columns`:
  - `tickets.started_at`, `finished_at`, `closed_at` → `DATETIME`
  - `ticket_pending_intervals.started_at`, `ended_at` → `DATETIME`
- `closed_at` sekarang diisi dari `finished_at`, bukan dari `now()`.
- Tidak ada tabel lain yang berpotensi efek serupa.
