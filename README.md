# API Showroom Motor Bekas - Andara Motor 

Backend RESTful API untuk Sistem Informasi Showroom Motor Bekas **Andara Motor** berbasis Laravel, dibangun sesuai spesifikasi Laporan Progress 3 UAS Mata Kuliah Framework Pemrograman Web (Fakultas Ilmu Komputer, Universitas Singaperbangsa Karawang).

---

##  Daftar Fitur & Spesifikasi

Sistem memiliki 2 aktor utama:
1. **Admin (Owner Showroom)**: Mengelola katalog motor, memantau dashboard penjualan & keuntungan, mencatat transaksi penjualan, melihat rekapitulasi finansial, dan mengunduh laporan Excel.
2. **Pelanggan (Pengunjung Website)**: Melihat katalog motor bekas, mencari & memfilter motor (tahun, harga, merk, tipe), melihat spesifikasi & kelengkapan unit, serta menghubungi showroom via WhatsApp dengan pesan otomatis.

---

##  Panduan Menjalankan API

### 1. Prasyarat
- PHP >= 8.2 (disarankan PHP 8.3)
- Composer

### 2. Instalasi & Menjalankan Database
```bash
# Masuk ke direktori project
cd andara-motor

# Salin file environment jika belum ada
copy .env.example .env

# Generate application key
php artisan key:generate

# Jalankan migrasi dan seeder data realistis
php artisan migrate:fresh --seed
```

### 3. Jalankan Server API
```bash
php artisan serve
```
Server akan aktif di: `http://127.0.0.1:8000` (atau port yang ditentukan).

---

##  Akun Default Admin

| Field | Nilai |
|---|---|
| **Email** | `admin@andaramotor.com` |
| **Username** | `admin` |
| **Password** | `admin123` |
| **Role** | `admin` |

*Catatan: Admin dapat login menggunakan **email** maupun **username**.*

---

## 📖 Dokumentasi Lengkap Endpoint API

Base URL: `http://127.0.0.1:8000/api`

### 1. Autentikasi Admin
| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `POST` | `/login` | Login admin (mendukung email atau username) | Publik |
| `GET` | `/me` | Mendapatkan profil admin yang sedang login | `Bearer Token` |
| `POST` | `/logout` | Revoke token dan logout | `Bearer Token` |

#### Contoh Payload Login:
```json
{
  "login": "admin@andaramotor.com", // atau "admin"
  "password": "admin123"
}
```

---

### 2. Katalog Motor & Pelanggan (Publik)
*Keamanan Data: Field `harga_beli` tidak akan pernah dikembalikan di endpoint publik.*

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `GET` | `/motor` | Katalog motor tersedia dengan filter, sort, dan pagination | Publik |
| `GET` | `/motor/{id}` | Detail lengkap motor + galeri foto + link generator WhatsApp | Publik |
| `GET` | `/motor/meta/filters` | Mendapatkan list merk, tipe, rentang harga & tahun untuk filter UI | Publik |
| `GET` | `/kontak` | Profil showroom, alamat, jam operasional, dan nomor WhatsApp | Publik |

#### Parameter Filter Katalog (`GET /motor`):
- `search`: Kata kunci pencarian (nama motor, merk, tipe, kondisi)
- `merk`: Filter merk (contoh: `Honda`, `Yamaha`, `Kawasaki`, `Vespa`, `Suzuki`)
- `tipe_motor`: Filter tipe motor (contoh: `Matic`, `Bebek Sport`, `Sport`, `Trail`)
- `tahun_min` & `tahun_max`: Filter rentang tahun
- `harga_min` & `harga_max`: Filter rentang harga jual
- `sort_by`: `harga_jual`, `tahun`, `jarak_tempuh`, `created_at` (default: `created_at`)
- `sort_order`: `asc` atau `desc` (default: `desc`)
- `page` & `per_page`: Pagination (default 12 per halaman)

---

### 3. Kelola Data Motor (Admin CRUD)
*Endpoint ini memerlukan header `Authorization: Bearer <token>`.*

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `GET` | `/admin/motor` | List semua motor (termasuk harga beli & potensi laba) | Admin |
| `POST` | `/admin/motor` | Tambah unit motor baru (mendukung upload foto) | Admin |
| `GET` | `/admin/motor/{id}` | Detail unit motor untuk admin | Admin |
| `PUT` / `POST` | `/admin/motor/{id}` | Update informasi motor atau ganti foto | Admin |
| `DELETE` | `/admin/motor/{id}` | Hapus data motor dari sistem | Admin |

#### Field Data Motor:
- `nama_motor`: (string, wajib) contoh: "Honda Vario 160 CBS"
- `merk`: (string, wajib) contoh: "Honda"
- `tipe_motor`: (string, wajib) contoh: "Matic"
- `tahun`: (integer, wajib) contoh: 2023
- `harga_beli`: (integer, wajib) Modal showroom, contoh: 18000000
- `harga_jual`: (integer, wajib) Harga tawar/jual, contoh: 21500000
- `kondisi`: (string, wajib) contoh: "Mulus 98%, mesin halus standar"
- `jarak_tempuh`: (integer, wajib) Odometer dalam km, contoh: 12000
- `surat_surat`: (string, wajib) contoh: "Lengkap (BPKB, STNK, Faktur)"
- `pajak`: (string, wajib) contoh: "Hidup (s/d Agustus 2026)"
- `garansi`: (string, opsional) contoh: "Garansi Mesin 1 Bulan"
- `status`: (string: `tersedia`, `terjual`, `booking`)
- `foto_utama`: (file image, opsional, max 2MB)
- `photos[]`: (array of image files, opsional, foto galeri tambahan)
- `deskripsi`: (text, opsional)

---

### 4. Pencatatan & Rekapitulasi Transaksi Penjualan
*Endpoint ini memerlukan header `Authorization: Bearer <token>`.*

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `GET` | `/admin/transaksi` | Riwayat transaksi penjualan (filter tanggal, pencarian) | Admin |
| `POST` | `/admin/transaksi` | Catat transaksi penjualan baru (status motor otomatis 'terjual') | Admin |
| `GET` | `/admin/transaksi/{id}` | Detail satu transaksi penjualan | Admin |
| `GET` | `/admin/transaksi/rekap` | Rekapitulasi omset, modal, keuntungan bersih & breakdown bulanan | Admin |

#### Logika Pencatatan Transaksi:
- Ketika `POST /admin/transaksi` dipanggil, sistem secara otomatis:
  1. Menghasilkan kode transaksi unik (format: `TRX-YYYYMMDD-XXXX`).
  2. Mengunci motor dan memvalidasi bahwa motor belum berstatus `terjual`.
  3. Mengambil modal beli motor (`harga_beli`).
  4. Menghitung keuntungan bersih server-side (`keuntungan = harga_jual - harga_beli`).
  5. Mengubah status unit motor terkait menjadi `terjual`.

---

### 5. Ekspor Laporan Excel
*Endpoint ini mengunduh file laporan transaksi yang siap dicetak/diarsipkan.*

| Method | Endpoint | Format | Deskripsi |
|---|---|---|---|
| `GET` | `/admin/transaksi/export/excel` | `.xlsx` (Excel) | Download laporan rapi berformat Excel lengkap dengan header, border, dan formula total SUM |
| `GET` | `/admin/transaksi/export/csv` | `.csv` (CSV) | Download laporan dalam format CSV (UTF-8 BOM) |

*Parameter opsional untuk filter periode laporan:*
- `?tanggal_mulai=YYYY-MM-DD`
- `?tanggal_selesai=YYYY-MM-DD`

---

### 6. Dashboard Admin (Ringkasan & Statistik)
| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `GET` | `/admin/dashboard` | Statistik unit motor, performa omset, laba, dan tren penjualan | Admin |

#### Data yang Disediakan Dashboard:
- **Ringkasan Unit**: Total unit motor, unit tersedia, unit terjual, unit dalam status booking.
- **Ringkasan Keuangan**: Total omset penjualan, total modal terjual, total laba bersih showroom, total nilai modal aset motor yang masih tersedia di showroom, estimasi potensi laba berjalan.
- **Performa Bulan Ini**: Unit terjual bulan ini, omset bulan ini, laba bulan ini.
- **Transaksi Terbaru**: 5 transaksi penjualan terkini.
- **Motor Baru Masuk**: 5 unit motor bekas terbaru yang masuk ke showroom.
- **Tren Penjualan**: Grafik histori penjualan 6 bulan terakhir (jumlah unit, omset, dan laba per bulan).

---

### 7. Pengaturan Profil & Kontak Showroom
| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `GET` | `/admin/pengaturan` | Lihat pengaturan profil showroom dan nomor WhatsApp | Admin |
| `PUT` | `/admin/pengaturan` | Update profil showroom, alamat, jam buka, nomor WhatsApp | Admin |

---

##  Pengujian Otomatis (Automated Tests)

Project ini dilengkapi dengan **32 Automated Tests** yang mencakup seluruh skenario validasi, relasi database, alur transaksi, ekspor Excel, filter katalog, dan keamanan data:

```bash
php artisan test
```

Hasil:
```text
PASS Tests\Unit\ExampleTest
PASS Tests\Feature\AdminMotorApiTest
PASS Tests\Feature\AuthApiTest
PASS Tests\Feature\DashboardApiTest
PASS Tests\Feature\ExampleTest
PASS Tests\Feature\ExcelExportApiTest
PASS Tests\Feature\ModelMigrationTest
PASS Tests\Feature\PublicMotorApiTest
PASS Tests\Feature\SalesTransactionApiTest
PASS Tests\Feature\ShowroomSettingApiTest

Tests: 32 passed (142 assertions)
Duration: 1.3s
```
