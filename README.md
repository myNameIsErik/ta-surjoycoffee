# STAR JAYA — Aplikasi Stockist Distribusi

Aplikasi web manajemen stok & akuntansi sederhana untuk usaha **stockist / distributor**.
Dibangun dengan **Laravel 13** + **MySQL** + **Bootstrap 5**.

Fitur utama:

- **Master Barang** dengan kategori, stok, harga beli/jual, dan mapping akun otomatis.
- **Transaksi Stok** — Pembelian (stok masuk dari supplier), Penjualan (stok keluar ke reseller), Koreksi.
- **Auto-Journal** — setiap transaksi stok otomatis membuat jurnal akuntansi double-entry yang seimbang.
- **Bagan Akun (Chart of Accounts)** — sudah berisi 25+ akun siap pakai khusus stockist (Persediaan Barang Dagang, HPP, Penjualan, Beban operasional, dll).
- **Jurnal Umum** — input transaksi multi-baris, validasi otomatis debit = kredit.
- **Buku Besar** per akun dengan saldo berjalan.
- **Neraca Saldo** (Trial Balance).
- **Laporan Laba Rugi** (Income Statement).
- **Neraca / Laporan Posisi Keuangan** (Balance Sheet).
- **Dashboard** dengan ringkasan pendapatan, beban, laba, saldo kas, dan grafik tren 6 bulan.
- **Login user** dengan auth Laravel default.
- Fitur **cetak / print friendly** untuk semua laporan.

## Persyaratan

- PHP **8.2+** (sudah terverifikasi di PHP 8.3)
- Composer 2.x
- MySQL 5.7+ / MariaDB 10.4+ (XAMPP / Laragon juga jalan)
- Browser modern

## Cara Instalasi

```bash
# 1. Masuk ke direktori proyek
cd cafe-accounting

# 2. Install dependency PHP
composer install

# 3. Salin .env (sudah disiapkan)
#    Pastikan database, user, password di .env sudah benar:
#    DB_DATABASE=star_jaya
#    DB_USERNAME=root
#    DB_PASSWORD=
#
#    Jika ingin generate ulang APP_KEY:
php artisan key:generate

# 4. Buat database (di MySQL):
mysql -u root -e "CREATE DATABASE star_jaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Jalankan migrasi + seeder
php artisan migrate:fresh --seed

# 6. Jalankan server
php artisan serve
```

Aplikasi dapat diakses di **http://127.0.0.1:8000**.

### Akun Login Default

| Email             | Password   |
|-------------------|------------|
| `admin@starjaya.test` | `password` (Admin) |
| `gudang@starjaya.test` | `password` (Staff Gudang) |

## Struktur Aplikasi

```
app/
  Http/Controllers/
    DashboardController.php   # Ringkasan keuangan
    AccountController.php     # CRUD Bagan Akun
    JournalController.php     # CRUD Jurnal Umum (double-entry)
    ReportController.php      # 4 laporan keuangan
    Auth/LoginController.php  # Login / logout
  Models/
    Account.php               # Model akun + perhitungan saldo
    Journal.php               # Header transaksi jurnal
    JournalEntry.php          # Baris debit/kredit
database/
  migrations/                 # accounts, journals, journal_entries
  seeders/
    AccountSeeder.php         # 28 akun standar stockist
    CategorySeeder.php        # Kategori barang dagang default
resources/views/
  layouts/app.blade.php       # Layout utama (Bootstrap 5)
  dashboard.blade.php         # Dashboard + chart
  accounts/                   # Bagan akun
  journals/                   # Form jurnal multi-baris
  reports/                    # 4 laporan keuangan
  auth/login.blade.php        # Form login
routes/web.php                # Semua rute aplikasi
```

## Konsep Akuntansi yang Dipakai

Tiap transaksi disimpan sebagai 1 **jurnal** dengan minimal 2 **entry** (debit & kredit).
Setiap baris hanya boleh diisi debit ATAU kredit (bukan keduanya), dan total debit
harus sama persis dengan total kredit — sistem akan menolak jurnal yang tidak seimbang.

| Tipe Akun  | Saldo Normal | Contoh                                            |
|------------|--------------|---------------------------------------------------|
| Aset       | Debit        | Kas, Bank, Persediaan Barang Dagang, Peralatan Gudang, Kendaraan |
| Kewajiban  | Kredit       | Utang Usaha (Supplier), Utang Bank                |
| Modal      | Kredit       | Modal Pemilik, Laba Ditahan                       |
| Pendapatan | Kredit       | Penjualan Barang Dagang, Pendapatan Lain          |
| Beban      | Debit        | HPP, Beban Gaji, Sewa Gudang, BBM, Pengiriman     |

### Contoh: Penjualan Barang Dagang Tunai Rp 100.000 (HPP Rp 70.000)

| Akun                          | Debit    | Kredit   |
|-------------------------------|---------:|---------:|
| Kas                           | 100.000  |          |
| Penjualan Barang Dagang       |          | 100.000  |
| Harga Pokok Penjualan         |  70.000  |          |
| Persediaan Barang Dagang      |          |  70.000  |

## Fitur Tambahan

- **Filter periode** pada Jurnal & semua laporan (tanggal dari/sampai).
- **Cetak laporan** — tombol "Cetak" pada setiap laporan menyembunyikan navbar & tombol aksi.
- **No. referensi otomatis** untuk setiap jurnal (`JU-YYYYMMDD-NNNN`).
- **Locale Indonesia** — tanggal & nama bulan tampil dalam Bahasa Indonesia.
- **Validasi saldo seimbang** secara real-time di form jurnal (JavaScript) DAN di server.

## Catatan Pengembangan

- Locale tanggal diset ke `id` di [config/app.php] / `.env` (`APP_LOCALE=id`).
- Currency format menggunakan Blade directive custom `@rupiah(...)` (lihat
  `app/Providers/AppServiceProvider.php`).
- Untuk integrasi dengan **XAMPP / Laragon**, cukup pastikan service MySQL berjalan
  dan kredensial di `.env` sesuai.

## Lisensi

MIT — silakan modifikasi sesuai kebutuhan distribusi Anda.
