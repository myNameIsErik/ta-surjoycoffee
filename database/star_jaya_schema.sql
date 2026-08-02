-- =====================================================================
-- STAR JAYA — Stockist App Database Schema
-- Untuk MySQL / MariaDB — cocok untuk XAMPP, Laragon, phpMyAdmin
--
-- Cara pakai:
--   1. Buka phpMyAdmin (http://localhost/phpmyadmin)
--   2. Klik tab "SQL" atau "Import" di root (bukan di dalam database)
--   3. Paste isi file ini, atau upload file .sql-nya
--   4. Klik "Go" / "Import"
--   5. Refresh sidebar → database "star_jaya" muncul
--   6. Buka database → klik tab "Designer" untuk lihat diagram relasi
--
-- Note:
--   • Password default admin & staff = "password" (hash bcrypt sudah bawaan)
--   • Semua tabel InnoDB, utf8mb4 — support emoji & FK constraint
--   • Timezone WIB (+07:00)
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- ---------------------------------------------------------------------
-- Database
-- ---------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `star_jaya`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `star_jaya`;

-- Drop kalau sudah ada (biar clean import)
DROP TABLE IF EXISTS `consignments`;
DROP TABLE IF EXISTS `consignees`;
DROP TABLE IF EXISTS `stock_movements`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- =====================================================================
-- 1. USERS — Pengguna sistem (admin & staff gudang)
-- =====================================================================
CREATE TABLE `users` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(255) NOT NULL,
    `email`             VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password`          VARCHAR(255) NOT NULL,
    `role`              VARCHAR(30) NOT NULL DEFAULT 'staff_gudang'
                        COMMENT 'admin | staff_gudang',
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `remember_token`    VARCHAR(100) DEFAULT NULL,
    `created_at`        TIMESTAMP NULL DEFAULT NULL,
    `updated_at`        TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Pengguna sistem STAR JAYA';

-- =====================================================================
-- 2. CATEGORIES — Kategori barang dagang
-- =====================================================================
CREATE TABLE `categories` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Kategori barang';

-- =====================================================================
-- 3. PRODUCTS — Master barang
-- FK: category_id → categories.id
-- =====================================================================
CREATE TABLE `products` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`        VARCHAR(30) NOT NULL,
    `name`        VARCHAR(255) NOT NULL,
    `category_id` BIGINT UNSIGNED DEFAULT NULL,
    `unit`        VARCHAR(20) NOT NULL DEFAULT 'pcs',
    `stock`       DECIMAL(18,2) NOT NULL DEFAULT 0,
    `min_stock`   DECIMAL(18,2) NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `products_code_unique` (`code`),
    KEY `products_category_id_foreign` (`category_id`),
    CONSTRAINT `products_category_id_foreign`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Master barang dagang';

-- =====================================================================
-- 4. STOCK_MOVEMENTS — Transaksi stok (pembelian, penjualan, koreksi)
-- FK: product_id → products.id
-- FK: user_id    → users.id
-- =====================================================================
CREATE TABLE `stock_movements` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`  VARCHAR(50) NOT NULL,
    `date`       DATE NOT NULL,
    `type`       ENUM('purchase','sale','adjustment_in','adjustment_out') NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `quantity`   DECIMAL(18,2) NOT NULL,
    `user_id`    BIGINT UNSIGNED DEFAULT NULL,
    `note`       TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `stock_movements_reference_unique` (`reference`),
    KEY `stock_movements_product_id_date_index` (`product_id`,`date`),
    KEY `stock_movements_type_index` (`type`),
    KEY `stock_movements_user_id_foreign` (`user_id`),
    CONSTRAINT `stock_movements_product_id_foreign`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ON DELETE RESTRICT,
    CONSTRAINT `stock_movements_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Transaksi stok masuk/keluar';

-- =====================================================================
-- 5. CONSIGNEES — Master penerima konsinyasi (toko/reseller)
-- =====================================================================
CREATE TABLE `consignees` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255) NOT NULL,
    `phone`      VARCHAR(30) DEFAULT NULL,
    `address`    TEXT DEFAULT NULL,
    `notes`      TEXT DEFAULT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `consignees_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Master penerima konsinyasi';

-- =====================================================================
-- 6. CONSIGNMENTS — Transaksi konsinyasi (kirim titipan & lapor terjual)
-- FK: consignee_id → consignees.id
-- FK: product_id   → products.id
-- FK: user_id      → users.id
-- =====================================================================
CREATE TABLE `consignments` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`    VARCHAR(50) NOT NULL,
    `date`         DATE NOT NULL,
    `type`         ENUM('send','sold') NOT NULL
                   COMMENT 'send = kirim titipan, sold = lapor terjual',
    `consignee_id` BIGINT UNSIGNED NOT NULL,
    `product_id`   BIGINT UNSIGNED NOT NULL,
    `quantity`     DECIMAL(18,2) NOT NULL,
    `user_id`      BIGINT UNSIGNED DEFAULT NULL,
    `note`         TEXT DEFAULT NULL,
    `created_at`   TIMESTAMP NULL DEFAULT NULL,
    `updated_at`   TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `consignments_reference_unique` (`reference`),
    KEY `consignments_consignee_id_date_index` (`consignee_id`,`date`),
    KEY `consignments_product_id_date_index` (`product_id`,`date`),
    KEY `consignments_type_index` (`type`),
    KEY `consignments_user_id_foreign` (`user_id`),
    CONSTRAINT `consignments_consignee_id_foreign`
        FOREIGN KEY (`consignee_id`) REFERENCES `consignees` (`id`)
        ON DELETE RESTRICT,
    CONSTRAINT `consignments_product_id_foreign`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ON DELETE RESTRICT,
    CONSTRAINT `consignments_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Transaksi konsinyasi keluar';

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- SEED DATA — User default & kategori standar stockist
-- =====================================================================

-- Users default (password bcrypt = "password")
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
('Admin STAR JAYA',     'admin@starjaya.test',  '$2y$12$HSbweJzxdeBGb84MCqQ.bOHQmH/CO7QbL12wvBxjrYXZBO/4qweRi', 'admin',        1, NOW(), NOW()),
('Staff Gudang Demo',   'gudang@starjaya.test', '$2y$12$HSbweJzxdeBGb84MCqQ.bOHQmH/CO7QbL12wvBxjrYXZBO/4qweRi', 'staff_gudang', 1, NOW(), NOW());

-- Kategori default
INSERT INTO `categories` (`name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
('Sembako',                'Beras, gula, minyak, tepung, dan bahan pokok lainnya',            1, NOW(), NOW()),
('Minuman Kemasan',        'Air mineral, teh kotak, susu UHT, soft drink, dll',               1, NOW(), NOW()),
('Snack & Makanan Ringan', 'Biskuit, kerupuk, kacang, permen, dll',                           1, NOW(), NOW()),
('Mie & Bumbu Dapur',      'Mie instan, kecap, saus, bumbu masak',                            1, NOW(), NOW()),
('Personal Care',          'Sabun, shampoo, pasta gigi, deterjen',                            1, NOW(), NOW()),
('Rokok',                  'Produk rokok & tembakau',                                         1, NOW(), NOW()),
('Lain-lain',              'Kategori umum untuk produk yang belum tergolong',                 1, NOW(), NOW());

-- =====================================================================
-- SELESAI. Login credentials:
--   admin@starjaya.test  / password  (role: admin)
--   gudang@starjaya.test / password  (role: staff_gudang)
-- =====================================================================
