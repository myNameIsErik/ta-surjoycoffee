# STRUKTUR TABEL DAN GLOSARIUM — STAR JAYA

Dokumen ini berisi struktur seluruh tabel yang digunakan dalam aplikasi **STAR JAYA (Stockist Application)** beserta glosarium istilah teknis. Format tabel mengikuti standar dokumentasi Tugas Akhir.

---

## STRUKTUR TABEL

Berikut adalah rincian struktur setiap tabel dalam basis data `star_jaya`.

### a. Tabel Users

Tabel `users` adalah tabel yang menyimpan data pengguna sistem (admin dan staff gudang). Pada tabel ini `id` sebagai *primary key*. Berikut adalah struktur tabel `users`:

| #  | Fields              | Type          | Null | Default             | Extra           |
|----|---------------------|---------------|------|---------------------|-----------------|
| 1  | `id` (PK)           | bigint(20)    | No   | None                | AUTO_INCREMENT  |
| 2  | `name`              | varchar(255)  | No   | None                |                 |
| 3  | `email`             | varchar(255)  | No   | None                |                 |
| 4  | `email_verified_at` | timestamp     | Yes  | NULL                |                 |
| 5  | `password`          | varchar(255)  | No   | None                |                 |
| 6  | `role`              | varchar(30)   | No   | `staff_gudang`      |                 |
| 7  | `is_active`         | tinyint(1)    | No   | 1                   |                 |
| 8  | `remember_token`    | varchar(100)  | Yes  | NULL                |                 |
| 9  | `created_at`        | timestamp     | Yes  | NULL                |                 |
| 10 | `updated_at`        | timestamp     | Yes  | NULL                |                 |

*Tabel IV.1 Struktur Tabel Users*

---

### b. Tabel Categories

Tabel `categories` adalah tabel yang menyimpan data kategori barang dagang. Pada tabel ini `id` sebagai *primary key*. Berikut adalah struktur tabel `categories`:

| # | Fields        | Type          | Null | Default | Extra           |
|---|---------------|---------------|------|---------|-----------------|
| 1 | `id` (PK)     | bigint(20)    | No   | None    | AUTO_INCREMENT  |
| 2 | `name`        | varchar(255)  | No   | None    |                 |
| 3 | `description` | text          | Yes  | NULL    |                 |
| 4 | `is_active`   | tinyint(1)    | No   | 1       |                 |
| 5 | `created_at`  | timestamp     | Yes  | NULL    |                 |
| 6 | `updated_at`  | timestamp     | Yes  | NULL    |                 |

*Tabel IV.2 Struktur Tabel Categories*

---

### c. Tabel Products

Tabel `products` adalah tabel yang menyimpan data master barang dagang. Pada tabel ini `id` sebagai *primary key* dan `category_id` sebagai *foreign key* yang mereferensi tabel `categories`. Berikut adalah struktur tabel `products`:

| #  | Fields              | Type          | Null | Default | Extra           |
|----|---------------------|---------------|------|---------|-----------------|
| 1  | `id` (PK)           | bigint(20)    | No   | None    | AUTO_INCREMENT  |
| 2  | `code`              | varchar(30)   | No   | None    |                 |
| 3  | `name`              | varchar(255)  | No   | None    |                 |
| 4  | `category_id` (FK)  | bigint(20)    | Yes  | NULL    |                 |
| 5  | `unit`              | varchar(20)   | No   | `pcs`   |                 |
| 6  | `stock`             | decimal(18,2) | No   | 0       |                 |
| 7  | `min_stock`         | decimal(18,2) | No   | 0       |                 |
| 8  | `is_active`         | tinyint(1)    | No   | 1       |                 |
| 9  | `created_at`        | timestamp     | Yes  | NULL    |                 |
| 10 | `updated_at`        | timestamp     | Yes  | NULL    |                 |

*Tabel IV.3 Struktur Tabel Products*

---

### d. Tabel Stock Movements

Tabel `stock_movements` adalah tabel yang menyimpan data transaksi stok (pembelian, penjualan, koreksi tambah, dan koreksi kurang). Pada tabel ini `id` sebagai *primary key*, `product_id` sebagai *foreign key* yang mereferensi tabel `products`, dan `user_id` sebagai *foreign key* yang mereferensi tabel `users`. Berikut adalah struktur tabel `stock_movements`:

| #  | Fields          | Type                                                         | Null | Default | Extra           |
|----|-----------------|--------------------------------------------------------------|------|---------|-----------------|
| 1  | `id` (PK)       | bigint(20)                                                   | No   | None    | AUTO_INCREMENT  |
| 2  | `reference`     | varchar(50)                                                  | No   | None    |                 |
| 3  | `date`          | date                                                         | No   | None    |                 |
| 4  | `type`          | enum('purchase','sale','adjustment_in','adjustment_out')     | No   | None    |                 |
| 5  | `product_id` (FK) | bigint(20)                                                 | No   | None    |                 |
| 6  | `quantity`      | decimal(18,2)                                                | No   | None    |                 |
| 7  | `user_id` (FK)  | bigint(20)                                                   | Yes  | NULL    |                 |
| 8  | `note`          | text                                                         | Yes  | NULL    |                 |
| 9  | `created_at`    | timestamp                                                    | Yes  | NULL    |                 |
| 10 | `updated_at`    | timestamp                                                    | Yes  | NULL    |                 |

*Tabel IV.4 Struktur Tabel Stock Movements*

---

### e. Tabel Consignees

Tabel `consignees` adalah tabel yang menyimpan data master penerima konsinyasi (toko/reseller yang menerima barang titipan). Pada tabel ini `id` sebagai *primary key*. Berikut adalah struktur tabel `consignees`:

| # | Fields       | Type          | Null | Default | Extra           |
|---|--------------|---------------|------|---------|-----------------|
| 1 | `id` (PK)    | bigint(20)    | No   | None    | AUTO_INCREMENT  |
| 2 | `name`       | varchar(255)  | No   | None    |                 |
| 3 | `phone`      | varchar(30)   | Yes  | NULL    |                 |
| 4 | `address`    | text          | Yes  | NULL    |                 |
| 5 | `notes`      | text          | Yes  | NULL    |                 |
| 6 | `is_active`  | tinyint(1)    | No   | 1       |                 |
| 7 | `created_at` | timestamp     | Yes  | NULL    |                 |
| 8 | `updated_at` | timestamp     | Yes  | NULL    |                 |

*Tabel IV.5 Struktur Tabel Consignees*

---

### f. Tabel Consignments

Tabel `consignments` adalah tabel yang menyimpan data transaksi konsinyasi (kirim titipan dan lapor terjual). Pada tabel ini `id` sebagai *primary key*, `consignee_id` sebagai *foreign key* yang mereferensi tabel `consignees`, `product_id` sebagai *foreign key* yang mereferensi tabel `products`, dan `user_id` sebagai *foreign key* yang mereferensi tabel `users`. Berikut adalah struktur tabel `consignments`:

| #  | Fields              | Type                    | Null | Default | Extra           |
|----|---------------------|-------------------------|------|---------|-----------------|
| 1  | `id` (PK)           | bigint(20)              | No   | None    | AUTO_INCREMENT  |
| 2  | `reference`         | varchar(50)             | No   | None    |                 |
| 3  | `date`              | date                    | No   | None    |                 |
| 4  | `type`              | enum('send','sold')     | No   | None    |                 |
| 5  | `consignee_id` (FK) | bigint(20)              | No   | None    |                 |
| 6  | `product_id` (FK)   | bigint(20)              | No   | None    |                 |
| 7  | `quantity`          | decimal(18,2)           | No   | None    |                 |
| 8  | `user_id` (FK)      | bigint(20)              | Yes  | NULL    |                 |
| 9  | `note`              | text                    | Yes  | NULL    |                 |
| 10 | `created_at`        | timestamp               | Yes  | NULL    |                 |
| 11 | `updated_at`        | timestamp               | Yes  | NULL    |                 |

*Tabel IV.6 Struktur Tabel Consignments*

---

## RELASI ANTAR TABEL

Berikut adalah *foreign key* relationship yang menghubungkan tabel-tabel di atas:

| Tabel Sumber        | Field                | Tabel Tujuan  | Field | On Delete   |
|---------------------|----------------------|---------------|-------|-------------|
| `products`          | `category_id`        | `categories`  | `id`  | SET NULL    |
| `stock_movements`   | `product_id`         | `products`    | `id`  | RESTRICT    |
| `stock_movements`   | `user_id`            | `users`       | `id`  | SET NULL    |
| `consignments`      | `consignee_id`       | `consignees`  | `id`  | RESTRICT    |
| `consignments`      | `product_id`         | `products`    | `id`  | RESTRICT    |
| `consignments`      | `user_id`            | `users`       | `id`  | SET NULL    |

*Tabel IV.7 Relasi Foreign Key Antar Tabel*

---

# GLOSARIUM

Berikut adalah daftar istilah teknis dan teknologi utama yang digunakan dalam pembangunan aplikasi STAR JAYA.

## A. Teknologi Utama

| Istilah          | Definisi |
|------------------|----------|
| **HTML**         | *HyperText Markup Language* — bahasa dasar untuk membangun struktur halaman web (menyusun teks, form, tabel, tombol, dan sebagainya). Digunakan pada seluruh halaman antarmuka aplikasi STAR JAYA. |
| **CSS**          | *Cascading Style Sheets* — bahasa untuk mengatur tampilan halaman web seperti warna, jarak, ukuran huruf, dan tata letak. Pada STAR JAYA, sebagian besar CSS berasal dari *framework* Bootstrap ditambah gaya khusus untuk warna dan tema aplikasi. |
| **JavaScript**   | Bahasa pemrograman yang berjalan di sisi *browser* (client-side). Digunakan pada STAR JAYA untuk kalkulasi otomatis di form transaksi (mis. hitung stok setelah transaksi) serta menampilkan grafik di Dashboard. |
| **PHP**          | Bahasa pemrograman yang berjalan di sisi server. Berfungsi memproses data, berkomunikasi dengan database, dan menghasilkan halaman web yang dikirim ke pengguna. |
| **Laravel**      | *Framework* PHP yang menjadi kerangka utama pembangunan aplikasi. Menyediakan struktur siap pakai untuk halaman, form, database, dan login. |
| **MySQL**        | Sistem manajemen basis data relasional (*Relational Database Management System* / RDBMS) yang digunakan untuk menyimpan seluruh data aplikasi seperti barang, transaksi, dan pengguna. Menggunakan bahasa SQL untuk mengelola data. |
| **Bootstrap**    | *Framework* CSS siap pakai yang mempercepat pembuatan tampilan aplikasi. Menyediakan komponen seperti *navbar*, *card*, tombol, form, dan tabel yang responsif di berbagai ukuran layar. |
| **Chart.js**     | Pustaka JavaScript untuk menampilkan grafik/diagram (batang, garis, lingkaran). Digunakan di halaman Dashboard untuk menampilkan tren pembelian dan penjualan. |

## B. Database

| Istilah              | Definisi |
|----------------------|----------|
| **Database (Basis Data)** | Kumpulan data yang tersusun secara terstruktur dan disimpan pada media penyimpanan komputer, sehingga dapat diakses, dikelola, dan diperbarui dengan mudah. |
| **Basis Data Relasional (RDBMS)** | Jenis database yang menyimpan data dalam bentuk tabel-tabel yang saling berhubungan (berelasi) melalui kolom kunci. STAR JAYA menggunakan jenis database ini melalui MySQL. |
| **SQL**              | *Structured Query Language* — bahasa standar untuk mengelola data pada basis data relasional (seperti menambah, membaca, mengubah, dan menghapus data). |
| **Tabel**            | Struktur penyimpanan data pada database, terdiri dari baris (*record*) dan kolom (*field*). Contoh: tabel `products` menyimpan seluruh data barang. |
| **Field / Kolom**    | Bagian tabel yang mendefinisikan jenis data yang disimpan (contoh: `name`, `stock`, `unit`). |
| **Record / Baris**   | Satu entri data pada tabel (contoh: satu baris di tabel `products` mewakili satu barang). |
| **Primary Key (PK)** | Kolom yang menjadi identitas unik setiap baris pada sebuah tabel. Setiap tabel memiliki satu PK. |
| **Foreign Key (FK)** | Kolom yang menghubungkan satu tabel ke tabel lain, sehingga terbentuk relasi antar tabel. |
| **AUTO_INCREMENT**   | Fitur database yang otomatis membuat nomor urut untuk kolom PK setiap kali data baru ditambahkan. |
| **phpMyAdmin**       | Aplikasi berbasis web untuk mengelola database MySQL melalui antarmuka grafis, seperti membuat tabel, menambah data, dan melihat relasi antar tabel. |

## C. Perangkat Bantu (Tools)

| Istilah      | Definisi |
|--------------|----------|
| **XAMPP**    | Paket perangkat lunak untuk menjalankan aplikasi di komputer lokal. Sudah termasuk Apache (server web), MySQL, PHP, dan phpMyAdmin. |
| **Composer** | Perangkat bantu untuk memasang dan mengelola pustaka PHP yang dibutuhkan Laravel. |
| **Git**      | Sistem yang mencatat setiap perubahan kode program, sehingga versi lama dapat dilihat kembali kapan saja. |
| **GitHub**   | Layanan penyimpanan kode program berbasis internet. Digunakan sebagai tempat menyimpan kode sumber aplikasi STAR JAYA. |

## D. Hosting & Domain

| Istilah        | Definisi |
|----------------|----------|
| **Railway**    | Layanan *hosting* berbasis awan (*cloud*) tempat aplikasi STAR JAYA dijalankan agar bisa diakses melalui internet. |
| **Domain**     | Nama alamat website (contoh: `app.surjoy-caffe.my.id`) yang digunakan pengguna untuk membuka aplikasi di browser. |
| **DNS**        | Sistem yang menerjemahkan nama domain menjadi alamat server, sehingga browser tahu kemana harus mengambil halaman. |
| **HTTPS / SSL**| Protokol keamanan yang memastikan data antara pengguna dan aplikasi terenkripsi. Ditandai dengan ikon gembok di browser. |

## E. Istilah Aplikasi

| Istilah          | Definisi |
|------------------|----------|
| **Login**        | Proses masuk ke aplikasi menggunakan email dan kata sandi. |
| **Role**         | Peran pengguna dalam aplikasi. STAR JAYA memiliki dua role: **Admin** (akses penuh) dan **Staff Gudang** (akses terbatas ke stok). |
| **Dashboard**    | Halaman utama yang menampilkan ringkasan angka penting (jumlah pembelian, penjualan, stok rendah, dll). |
| **CRUD**         | Empat operasi dasar pada data: *Create* (tambah), *Read* (baca), *Update* (ubah), *Delete* (hapus). |

## F. Istilah Bisnis Stockist

| Istilah                    | Definisi |
|----------------------------|----------|
| **Stockist**               | Pihak yang menyimpan dan mendistribusikan barang, biasanya sebagai perantara antara produsen dengan pengecer. |
| **Master Barang**          | Kumpulan data referensi seluruh barang yang dijual (kode, nama, satuan, kategori, dan stok minimum). |
| **Stok Masuk**             | Transaksi pertambahan stok di gudang, umumnya karena pembelian dari supplier. |
| **Stok Keluar**            | Transaksi pengurangan stok di gudang, umumnya karena penjualan ke pelanggan. |
| **Adjustment (Koreksi)**   | Penyesuaian stok manual yang dilakukan ketika terjadi kehilangan, kerusakan, atau setelah opname stok. |
| **Kartu Stok**             | Laporan yang menampilkan seluruh riwayat pergerakan stok satu barang secara kronologis, lengkap dengan saldo berjalan. |
| **Reference Number**       | Nomor unik yang dibuat otomatis untuk setiap transaksi (contoh: `PB-20260622-0001` untuk pembelian, `PJ-...` untuk penjualan). |
| **Konsinyasi**             | Sistem penjualan dimana STAR JAYA menitipkan barang ke toko lain untuk dijual. Barang tetap milik STAR JAYA hingga terjual. |
| **Kirim Titipan**          | Aksi mengeluarkan barang dari gudang untuk dititipkan ke toko penerima konsinyasi. |
| **Lapor Terjual**          | Aksi mencatat bahwa sebagian barang titipan telah terjual oleh toko penerima. |
| **Outstanding**            | Jumlah sisa barang titipan yang masih ada di toko penerima dan belum dilaporkan terjual. |

---

## REFERENSI SUMBER TEKNOLOGI

Berikut adalah sumber resmi dari teknologi utama yang digunakan:

| Teknologi     | Sumber                              |
|---------------|-------------------------------------|
| PHP           | https://www.php.net                 |
| Laravel       | https://laravel.com                 |
| MySQL         | https://www.mysql.com               |
| Bootstrap     | https://getbootstrap.com            |
| Chart.js      | https://www.chartjs.org             |
| XAMPP         | https://www.apachefriends.org       |
| phpMyAdmin    | https://www.phpmyadmin.net          |
| Git & GitHub  | https://git-scm.com , https://github.com |
| Railway       | https://railway.app                 |

---

*Dokumen ini dibuat untuk kebutuhan dokumentasi Tugas Akhir aplikasi STAR JAYA — Stockist Application.*
