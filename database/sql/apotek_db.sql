-- ============================================================================
--  Database contoh: APOTEK
--  Dibuat dari migrasi & seeder Laravel (php artisan migrate --seed).
--  File ini disediakan untuk impor cepat lewat phpMyAdmin / MySQL Workbench.
--
--  Impor CLI:  mysql -u root -p < database/sql/apotek_db.sql
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE IF NOT EXISTS `apotek_db`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `apotek_db`;

DROP TABLE IF EXISTS `detail_penjualan`;
DROP TABLE IF EXISTS `penjualan`;
DROP TABLE IF EXISTS `obat`;
DROP TABLE IF EXISTS `supplier`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `migrations`;
DROP TABLE IF EXISTS `users`;

-- ------------------------------------------------------------------- users
CREATE TABLE `users` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(255) NOT NULL,
    `email`             VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL,
    `password`          VARCHAR(255) NOT NULL,
    `role`              ENUM('admin','kasir') NOT NULL DEFAULT 'kasir',
    `aktif`             TINYINT(1) NOT NULL DEFAULT 1,
    `terakhir_login`    TIMESTAMP NULL,
    `remember_token`    VARCHAR(100) NULL,
    `created_at`        TIMESTAMP NULL,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------- tabel bawaan Laravel
-- sessions & cache WAJIB ada: login memakai SESSION_DRIVER=database dan
-- batas percobaan login memakai CACHE_STORE=database.
CREATE TABLE `password_reset_tokens` (
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
    `id`            VARCHAR(255) NOT NULL,
    `user_id`       BIGINT UNSIGNED NULL,
    `ip_address`    VARCHAR(45) NULL,
    `user_agent`    TEXT NULL,
    `payload`       LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
    `key`        VARCHAR(255) NOT NULL,
    `value`      MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`),
    KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
    `key`        VARCHAR(255) NOT NULL,
    `owner`      VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`),
    KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue`        VARCHAR(255) NOT NULL,
    `payload`      LONGTEXT NOT NULL,
    `attempts`     TINYINT UNSIGNED NOT NULL,
    `reserved_at`  INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at`   INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
    `id`             VARCHAR(255) NOT NULL,
    `name`           VARCHAR(255) NOT NULL,
    `total_jobs`     INT NOT NULL,
    `pending_jobs`   INT NOT NULL,
    `failed_jobs`    INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options`        MEDIUMTEXT NULL,
    `cancelled_at`   INT NULL,
    `created_at`     INT NOT NULL,
    `finished_at`    INT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`       VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue`      TEXT NOT NULL,
    `payload`    LONGTEXT NOT NULL,
    `exception`  LONGTEXT NOT NULL,
    `failed_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catatan migrasi, supaya `php artisan migrate` setelah impor tidak
-- mencoba membuat ulang tabel yang sudah ada.
CREATE TABLE `migrations` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch`     INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- kategori
CREATE TABLE `kategori` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`       VARCHAR(100) NOT NULL,
    `slug`       VARCHAR(120) NOT NULL,
    `deskripsi`  TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `kategori_nama_unique` (`nama`),
    UNIQUE KEY `kategori_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- supplier
CREATE TABLE `supplier` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`        VARCHAR(150) NOT NULL,
    `telepon`     VARCHAR(30) NULL,
    `email`       VARCHAR(150) NULL,
    `alamat`      VARCHAR(255) NULL,
    `nama_kontak` VARCHAR(100) NULL,
    `aktif`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `supplier_nama_index` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------- obat
CREATE TABLE `obat` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `kode_obat`          VARCHAR(30) NOT NULL,
    `nama`               VARCHAR(150) NOT NULL,
    `kategori_id`        BIGINT UNSIGNED NOT NULL,
    `supplier_id`        BIGINT UNSIGNED NULL,
    `golongan`           ENUM('bebas','bebas_terbatas','keras','narkotika','psikotropika','herbal') NOT NULL DEFAULT 'bebas',
    `bentuk_sediaan`     VARCHAR(50) NULL,
    `satuan`             VARCHAR(20) NOT NULL DEFAULT 'strip',
    `kandungan`          VARCHAR(191) NULL,
    `produsen`           VARCHAR(150) NULL,
    `harga_beli`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `harga_jual`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `stok`               INT UNSIGNED NOT NULL DEFAULT 0,
    `stok_minimum`       INT UNSIGNED NOT NULL DEFAULT 10,
    `tanggal_kadaluarsa` DATE NULL,
    `deskripsi`          TEXT NULL,
    `aktif`              TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`         TIMESTAMP NULL,
    `updated_at`         TIMESTAMP NULL,
    `deleted_at`         TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `obat_kode_obat_unique` (`kode_obat`),
    KEY `obat_nama_index` (`nama`),
    KEY `obat_golongan_index` (`golongan`),
    KEY `obat_tanggal_kadaluarsa_index` (`tanggal_kadaluarsa`),
    KEY `obat_kategori_id_foreign` (`kategori_id`),
    KEY `obat_supplier_id_foreign` (`supplier_id`),
    CONSTRAINT `obat_kategori_id_foreign` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `obat_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------- penjualan
CREATE TABLE `penjualan` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`        BIGINT UNSIGNED NULL,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `tanggal`        DATETIME NOT NULL,
    `nama_pelanggan` VARCHAR(150) NULL,
    `total`          DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `bayar`          DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `kembalian`      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `metode_bayar`   ENUM('tunai','debit','kredit','qris','transfer') NOT NULL DEFAULT 'tunai',
    `catatan`        VARCHAR(255) NULL,
    `created_at`     TIMESTAMP NULL,
    `updated_at`     TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `penjualan_kode_transaksi_unique` (`kode_transaksi`),
    KEY `penjualan_tanggal_index` (`tanggal`),
    KEY `penjualan_user_id_foreign` (`user_id`),
    CONSTRAINT `penjualan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------- detail_penjualan
CREATE TABLE `detail_penjualan` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `penjualan_id` BIGINT UNSIGNED NOT NULL,
    `obat_id`      BIGINT UNSIGNED NOT NULL,
    `nama_obat`    VARCHAR(150) NOT NULL,
    `jumlah`       INT UNSIGNED NOT NULL,
    `harga_satuan` DECIMAL(12,2) NOT NULL,
    `subtotal`     DECIMAL(14,2) NOT NULL,
    `created_at`   TIMESTAMP NULL,
    `updated_at`   TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `detail_penjualan_penjualan_id_foreign` (`penjualan_id`),
    KEY `detail_penjualan_obat_id_foreign` (`obat_id`),
    CONSTRAINT `detail_penjualan_penjualan_id_foreign` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE,
    CONSTRAINT `detail_penjualan_obat_id_foreign` FOREIGN KEY (`obat_id`) REFERENCES `obat` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================= DATA MIGRASI
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_01_000100_create_kategori_table', 1),
(5, '2026_01_01_000200_create_supplier_table', 1),
(6, '2026_01_01_000300_create_obat_table', 1),
(7, '2026_01_01_000400_create_penjualan_table', 1),
(8, '2026_01_01_000500_create_detail_penjualan_table', 1),
(9, '2026_09_18_150000_add_role_to_users_table', 1),
(10, '2026_09_18_150100_add_user_id_to_penjualan_table', 1);

-- ============================================================ DATA USER
-- Password awal: admin@apotek.test = Admin12345, kasir1/kasir2@apotek.test = Kasir12345
-- GANTI setelah login pertama lewat menu Ganti Password.
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `aktif`, `terakhir_login`, `created_at`, `updated_at`) VALUES
(1, 'Admin Apotek', 'admin@apotek.test', '2026-09-21 14:16:25', '$2y$12$x1OohxIOdUDvXds7BCJHwuRP1V8wV8JkZhDC0QxsQ5XjvX6h2.1lG', 'admin', 1, NULL, '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(2, 'Kasir Pagi', 'kasir1@apotek.test', '2026-09-21 14:16:25', '$2y$12$prqi50BYLJSzn3PHWsIRmu3EjlzxWKlyuRV2WuphtnbXB4y3YMZuq', 'kasir', 1, NULL, '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(3, 'Kasir Sore', 'kasir2@apotek.test', '2026-09-21 14:16:25', '$2y$12$HJgKhZOy0JjIhTLngGY6PeiPur5Oovu97cwZo2Lq1vTEaL3avZVrG', 'kasir', 1, NULL, '2026-09-21 14:16:25', '2026-09-21 14:16:25');

-- ======================================================== DATA KATEGORI
INSERT INTO `kategori` (`id`, `nama`, `slug`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'Analgesik & Antipiretik', 'analgesik-antipiretik', 'Obat pereda nyeri dan penurun demam.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(2, 'Antibiotik', 'antibiotik', 'Obat keras untuk infeksi bakteri, wajib dengan resep dokter.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(3, 'Vitamin & Suplemen', 'vitamin-suplemen', 'Multivitamin, mineral, dan suplemen daya tahan tubuh.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(4, 'Obat Batuk & Flu', 'obat-batuk-flu', 'Sirup dan tablet untuk batuk, pilek, serta gejala flu.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(5, 'Obat Pencernaan', 'obat-pencernaan', 'Obat maag, diare, sembelit, dan gangguan lambung.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(6, 'Antihistamin & Alergi', 'antihistamin-alergi', 'Obat untuk reaksi alergi, gatal, dan biduran.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(7, 'Obat Luar & Topikal', 'obat-luar-topikal', 'Salep, krim, antiseptik, dan obat pemakaian luar.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(8, 'Kardiovaskular', 'kardiovaskular', 'Obat hipertensi, kolesterol, dan jantung.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(9, 'Antidiabetes', 'antidiabetes', 'Obat penurun gula darah untuk penderita diabetes.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(10, 'Obat Herbal', 'obat-herbal', 'Jamu dan obat tradisional terstandar.', '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(11, 'Alat Kesehatan', 'alat-kesehatan', 'Masker, perban, termometer, dan perlengkapan medis.', '2026-09-21 14:16:25', '2026-09-21 14:16:25');

-- ======================================================== DATA SUPPLIER
INSERT INTO `supplier` (`id`, `nama`, `telepon`, `email`, `alamat`, `nama_kontak`, `aktif`, `created_at`, `updated_at`) VALUES
(1, 'PT Kimia Farma Trading & Distribution', '021-4805436', 'cs@kftd.co.id', 'Jl. Budi Utomo No. 1, Jakarta Pusat', 'Bpk. Hendra', 1, '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(2, 'PT Anugrah Pharmindo Lestari', '021-8378 8888', 'order@apl-pharma.co.id', 'Jl. Raya Bekasi KM 21, Jakarta Timur', 'Ibu Ratna', 1, '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(3, 'PT Enseval Putera Megatrading', '021-4410222', 'sales@enseval.com', 'Jl. Pulo Lentut No. 10, Kawasan Industri Pulogadung', 'Bpk. Yusuf', 1, '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(4, 'PT Bina San Prima', '022-6035432', 'info@binasanprima.co.id', 'Jl. Purnawarman No. 47, Bandung', 'Ibu Sinta', 1, '2026-09-21 14:16:25', '2026-09-21 14:16:25'),
(5, 'PT Merapi Utama Pharma', '0274-563421', 'yogya@merapipharma.co.id', 'Jl. Magelang KM 6, Sleman, Yogyakarta', 'Bpk. Bagas', 1, '2026-09-21 14:16:25', '2026-09-21 14:16:25');

-- ============================================================ DATA OBAT
INSERT INTO `obat` (`id`, `kode_obat`, `nama`, `kategori_id`, `supplier_id`, `golongan`, `bentuk_sediaan`, `satuan`, `kandungan`, `produsen`, `harga_beli`, `harga_jual`, `stok`, `stok_minimum`, `tanggal_kadaluarsa`, `deskripsi`, `aktif`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'OBT-0001', 'Paracetamol 500 mg', 1, 1, 'bebas', 'Tablet', 'strip', 'Paracetamol 500 mg', 'Kimia Farma', 2800, 4500, 348, 50, '2028-11-21', 'Meredakan demam dan nyeri ringan sampai sedang. Isi 10 tablet per strip.', 1, '2026-09-21 14:16:25', '2026-09-21 14:16:26', NULL),
(2, 'OBT-0002', 'Panadol Extra', 1, 3, 'bebas', 'Kaplet', 'strip', 'Paracetamol 500 mg, Kafein 65 mg', 'Sterling Products', 9500, 13500, 120, 24, '2028-07-21', 'Pereda nyeri kepala dengan tambahan kafein.', 1, '2026-09-21 14:16:25', '2026-09-21 14:16:25', NULL),
(3, 'OBT-0003', 'Bodrex Migra', 1, 4, 'bebas', 'Tablet', 'strip', 'Paracetamol 350 mg, Propifenazon 150 mg, Kafein 50 mg', 'Tempo Scan Pacific', 5200, 7500, 95, 20, '2028-04-21', 'Untuk sakit kepala sebelah (migrain).', 1, '2026-09-21 14:16:25', '2026-09-21 14:16:25', NULL),
(4, 'OBT-0004', 'Ibuprofen 400 mg', 1, 1, 'bebas_terbatas', 'Tablet salut selaput', 'strip', 'Ibuprofen 400 mg', 'Hexpharm Jaya', 4200, 6500, 175, 40, '2028-09-21', 'Antiinflamasi non-steroid untuk nyeri dan radang. Diminum setelah makan.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(5, 'OBT-0005', 'Asam Mefenamat 500 mg', 1, 2, 'keras', 'Kaplet', 'strip', 'Asam Mefenamat 500 mg', 'Dexa Medica', 6800, 9500, 140, 30, '2028-05-21', 'Nyeri gigi dan nyeri haid. Harus dengan resep dokter.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(6, 'OBT-0006', 'Natrium Diklofenak 50 mg', 1, 2, 'keras', 'Tablet salut enterik', 'strip', 'Natrium Diklofenak 50 mg', 'Novell Pharmaceutical', 8500, 12000, 57, 20, '2027-12-21', 'Antinyeri dan antiradang untuk keluhan sendi.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(7, 'OBT-0101', 'Amoxicillin 500 mg', 2, 1, 'keras', 'Kapsul', 'strip', 'Amoxicillin trihydrate 500 mg', 'Kimia Farma', 9200, 13000, 205, 40, '2028-03-21', 'Antibiotik golongan penisilin. Wajib dihabiskan sesuai anjuran dokter.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(8, 'OBT-0102', 'Cefadroxil 500 mg', 2, 2, 'keras', 'Kapsul', 'strip', 'Cefadroxil monohydrate 500 mg', 'Sanbe Farma', 18500, 25000, 84, 25, '2028-01-21', 'Antibiotik sefalosporin generasi pertama.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(9, 'OBT-0103', 'Ciprofloxacin 500 mg', 2, 3, 'keras', 'Tablet salut selaput', 'strip', 'Ciprofloxacin HCl 500 mg', 'Indofarma', 14000, 19500, 18, 25, '2027-08-21', 'Antibiotik kuinolon untuk infeksi saluran kemih dan pencernaan.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(10, 'OBT-0104', 'Amoxicillin Sirup Kering 125 mg/5 ml', 2, 1, 'keras', 'Sirup kering', 'botol', 'Amoxicillin 125 mg per 5 ml, 60 ml', 'Hexpharm Jaya', 12500, 17000, 45, 15, '2027-11-21', 'Antibiotik anak. Larutkan dengan air matang sebelum digunakan.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(11, 'OBT-0201', 'Vitamin C IPI 50 mg', 3, 4, 'bebas', 'Tablet', 'botol', 'Asam askorbat 50 mg, isi 45 tablet', 'Supra Ferbindo Farma', 5500, 8000, 240, 40, '2029-01-21', 'Suplemen vitamin C harian.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(12, 'OBT-0202', 'Redoxon Effervescent 1000 mg', 3, 3, 'bebas', 'Tablet effervescent', 'tube', 'Vitamin C 1000 mg, isi 10 tablet', 'Bayer Indonesia', 32000, 42000, 60, 15, '2028-06-21', 'Vitamin C dosis tinggi, larut dalam air.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(13, 'OBT-0203', 'Enervon-C Multivitamin', 3, 3, 'bebas', 'Tablet salut gula', 'strip', 'Vitamin C 500 mg, Vitamin B kompleks', 'Darya-Varia', 11000, 15500, 130, 30, '2028-10-21', 'Multivitamin untuk menjaga stamina.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(14, 'OBT-0204', 'Imboost Force Tablet', 3, 2, 'bebas', 'Tablet', 'strip', 'Echinacea purpurea 250 mg, Black elderberry 400 mg, Zinc 10 mg', 'Soho Industri Pharmasi', 28000, 37500, 70, 20, '2028-02-21', 'Suplemen peningkat daya tahan tubuh.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(15, 'OBT-0205', 'Blackmores Vitamin D3 1000 IU', 3, 5, 'bebas', 'Kapsul lunak', 'botol', 'Cholecalciferol 1000 IU, isi 60 kapsul', 'Blackmores', 96000, 125000, 22, 10, '2028-08-21', 'Suplemen vitamin D untuk kesehatan tulang.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(16, 'OBT-0301', 'OBH Combi Batuk Flu 100 ml', 4, 4, 'bebas_terbatas', 'Sirup', 'botol', 'Paracetamol, Ephedrine HCl, Chlorpheniramine maleate', 'Combiphar', 14500, 19000, 88, 20, '2028-05-21', 'Meredakan batuk berdahak disertai gejala flu.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(17, 'OBT-0302', 'Woods Peppermint Antitussive 60 ml', 4, 3, 'bebas_terbatas', 'Sirup', 'botol', 'Dextromethorphan HBr, Diphenhydramine HCl', 'Kalbe Farma', 21000, 27500, 53, 15, '2028-03-21', 'Untuk batuk kering tidak berdahak.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(18, 'OBT-0303', 'Actifed Plus Expectorant 60 ml', 4, 3, 'bebas_terbatas', 'Sirup', 'botol', 'Triprolidine HCl, Pseudoephedrine HCl, Guaifenesin', 'Glaxo Wellcome', 24000, 31000, 12, 15, '2027-06-21', 'Batuk berdahak disertai hidung tersumbat.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(19, 'OBT-0304', 'Ambroxol 30 mg', 4, 1, 'keras', 'Tablet', 'strip', 'Ambroxol HCl 30 mg', 'Dexa Medica', 5800, 8500, 160, 30, '2028-07-21', 'Pengencer dahak (mukolitik).', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(20, 'OBT-0401', 'Promag Tablet', 5, 4, 'bebas', 'Tablet kunyah', 'strip', 'Hydrotalcite, Mg(OH)2, Simethicone', 'Kalbe Farma', 8200, 11500, 198, 40, '2028-09-21', 'Meredakan gejala maag dan kembung.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(21, 'OBT-0402', 'Mylanta Cair 150 ml', 5, 3, 'bebas', 'Suspensi', 'botol', 'Al(OH)3, Mg(OH)2, Simethicone', 'Pfizer Indonesia', 26000, 34000, 39, 12, '2027-12-21', 'Antasida cair untuk nyeri lambung.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(22, 'OBT-0403', 'Omeprazole 20 mg', 5, 2, 'keras', 'Kapsul', 'strip', 'Omeprazole 20 mg', 'Hexpharm Jaya', 12500, 17500, 90, 25, '2028-04-21', 'Penghambat pompa proton untuk asam lambung berlebih.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(23, 'OBT-0404', 'Oralit Serbuk 200 ml', 5, 1, 'bebas', 'Serbuk', 'sachet', 'NaCl, KCl, Natrium sitrat, Glukosa anhidrat', 'Kimia Farma', 1200, 2500, 400, 100, '2029-03-21', 'Mencegah dehidrasi akibat diare.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(24, 'OBT-0405', 'Antimo Tablet', 5, 4, 'bebas_terbatas', 'Tablet', 'strip', 'Dimenhydrinate 50 mg', 'Phapros', 4800, 7000, 110, 25, '2028-06-21', 'Mencegah mabuk perjalanan, mual, dan muntah.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(25, 'OBT-0501', 'Cetirizine 10 mg', 6, 2, 'keras', 'Tablet salut selaput', 'strip', 'Cetirizine dihydrochloride 10 mg', 'Novell Pharmaceutical', 6500, 9500, 150, 30, '2028-08-21', 'Antihistamin generasi kedua untuk alergi dan gatal.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(26, 'OBT-0502', 'Loratadine 10 mg', 6, 2, 'keras', 'Tablet', 'strip', 'Loratadine 10 mg', 'Dexa Medica', 7200, 10500, 73, 20, '2028-02-21', 'Antialergi non-sedatif, tidak menyebabkan kantuk berat.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(27, 'OBT-0503', 'CTM 4 mg', 6, 1, 'bebas_terbatas', 'Tablet', 'strip', 'Chlorpheniramine maleate 4 mg', 'Indofarma', 1800, 3500, 260, 50, '2028-11-21', 'Antihistamin klasik. Dapat menyebabkan kantuk.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(28, 'OBT-0601', 'Betadine Antiseptik 60 ml', 7, 3, 'bebas', 'Larutan', 'botol', 'Povidone iodine 10%', 'Mahakam Beta Farma', 22000, 29000, 62, 15, '2028-12-21', 'Antiseptik untuk luka luar.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(29, 'OBT-0602', 'Counterpain Cream 30 g', 7, 4, 'bebas', 'Krim', 'tube', 'Metil salisilat, Eugenol, Mentol', 'Taisho Pharmaceutical', 28000, 36000, 48, 12, '2028-05-21', 'Meredakan nyeri otot dan pegal linu.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(30, 'OBT-0603', 'Daktarin Krim 10 g', 7, 3, 'bebas_terbatas', 'Krim', 'tube', 'Miconazole nitrate 2%', 'Janssen Pharmaceutica', 38000, 48000, 30, 10, '2028-01-21', 'Antijamur kulit (panu, kadas, kurap).', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(31, 'OBT-0604', 'Alkohol 70% 100 ml', 7, 1, 'bebas', 'Larutan', 'botol', 'Etanol 70%', 'OneMed', 6000, 9000, 138, 30, '2029-02-21', 'Antiseptik pembersih kulit sebelum tindakan.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(32, 'OBT-0701', 'Amlodipine 10 mg', 8, 2, 'keras', 'Tablet', 'strip', 'Amlodipine besylate 10 mg', 'Dexa Medica', 9500, 13500, 175, 40, '2028-06-21', 'Obat darah tinggi golongan CCB. Wajib resep dokter.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(33, 'OBT-0702', 'Captopril 25 mg', 8, 1, 'keras', 'Tablet', 'strip', 'Captopril 25 mg', 'Kimia Farma', 5500, 8000, 95, 25, '2027-10-21', 'Antihipertensi golongan ACE inhibitor.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(34, 'OBT-0703', 'Simvastatin 20 mg', 8, 2, 'keras', 'Tablet salut selaput', 'strip', 'Simvastatin 20 mg', 'Hexpharm Jaya', 11000, 15000, 20, 25, '2027-09-21', 'Penurun kolesterol. Diminum malam hari.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(35, 'OBT-0801', 'Metformin 500 mg', 9, 1, 'keras', 'Tablet salut selaput', 'strip', 'Metformin HCl 500 mg', 'Indofarma', 6800, 9800, 165, 35, '2028-05-21', 'Antidiabetes oral lini pertama. Diminum bersama makan.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(36, 'OBT-0802', 'Glibenclamide 5 mg', 9, 5, 'keras', 'Tablet', 'strip', 'Glibenclamide 5 mg', 'Phapros', 7500, 10500, 57, 20, '2027-11-21', 'Sulfonilurea penurun gula darah.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(37, 'OBT-0901', 'Tolak Angin Cair 15 ml', 10, 4, 'herbal', 'Cairan obat dalam', 'sachet', 'Ekstrak jahe, daun mint, madu', 'Sido Muncul', 3200, 5000, 318, 60, '2028-07-21', 'Jamu untuk masuk angin dan perut kembung.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(38, 'OBT-0902', 'Antangin JRG 15 ml', 10, 4, 'herbal', 'Cairan obat dalam', 'sachet', 'Jahe, Royal jelly, Ginseng', 'Deltomed Laboratories', 3000, 4800, 280, 60, '2028-04-21', 'Membantu meredakan gejala masuk angin.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(39, 'OBT-0903', 'Diapet Kapsul', 10, 5, 'herbal', 'Kapsul', 'strip', 'Ekstrak daun jambu biji, kunyit, buah mojokeling', 'Soho Industri Pharmasi', 9800, 13500, 66, 20, '2028-03-21', 'Membantu mengurangi frekuensi buang air besar.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(40, 'ALK-0001', 'Masker Medis 3 Ply (Box isi 50)', 11, 5, 'bebas', NULL, 'box', NULL, 'OneMed', 24000, 35000, 51, 15, '2029-07-21', 'Masker bedah sekali pakai 3 lapis.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(41, 'ALK-0002', 'Hansaplast Plester (Box isi 10)', 11, 3, 'bebas', NULL, 'box', NULL, 'Beiersdorf Indonesia', 8500, 12000, 90, 20, '2029-05-21', 'Plester luka tahan air.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(42, 'ALK-0003', 'Termometer Digital', 11, 5, 'bebas', NULL, 'pcs', NULL, 'OneMed', 32000, 45000, 8, 10, '2030-07-21', 'Termometer digital ketiak, hasil cepat 60 detik.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL),
(43, 'ALK-0004', 'Kasa Steril 16x16 cm', 11, 5, 'bebas', NULL, 'pcs', NULL, 'Husada', 2500, 4000, 174, 40, '2028-10-21', 'Kasa steril untuk menutup luka.', 1, '2026-09-21 14:16:26', '2026-09-21 14:16:26', NULL);

-- =============================================== DATA PENJUALAN CONTOH
INSERT INTO `penjualan` (`id`, `user_id`, `kode_transaksi`, `tanggal`, `nama_pelanggan`, `total`, `bayar`, `kembalian`, `metode_bayar`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 2, 'TRX-20260914-0001', '2026-09-14 09:19:00', 'Umum', 71000, 75000, 4000, 'tunai', NULL, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(2, 3, 'TRX-20260914-0002', '2026-09-14 10:49:00', 'Ibu Wulan', 40500, 45000, 4500, 'tunai', NULL, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(3, 2, 'TRX-20260915-0001', '2026-09-15 11:35:00', 'Bpk. Andi', 50500, 55000, 4500, 'qris', NULL, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(4, 3, 'TRX-20260915-0002', '2026-09-15 12:29:00', 'Klinik Sehat Bersama', 100500, 105000, 4500, 'debit', NULL, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(5, 2, 'TRX-20260916-0001', '2026-09-16 13:42:00', 'Ibu Sri Rahayu', 131000, 135000, 4000, 'tunai', NULL, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(6, 3, 'TRX-20260916-0002', '2026-09-16 14:38:00', 'Umum', 385500, 390000, 4500, 'qris', NULL, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(7, 2, 'TRX-20260917-0001', '2026-09-17 15:05:00', 'Bpk. Joko', 36000, 40000, 4000, 'transfer', NULL, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(8, 3, 'TRX-20260917-0002', '2026-09-17 16:10:00', 'Ibu Nur Aini', 27500, 30000, 2500, 'tunai', NULL, '2026-09-21 14:16:26', '2026-09-21 14:16:26');

INSERT INTO `detail_penjualan` (`id`, `penjualan_id`, `obat_id`, `nama_obat`, `jumlah`, `harga_satuan`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 20, 'Promag Tablet', 2, 11500, 23000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(2, 1, 43, 'Kasa Steril 16x16 cm', 1, 4000, 4000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(3, 1, 7, 'Amoxicillin 500 mg', 3, 13000, 39000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(4, 1, 37, 'Tolak Angin Cair 15 ml', 1, 5000, 5000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(5, 2, 4, 'Ibuprofen 400 mg', 1, 6500, 6500, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(6, 2, 21, 'Mylanta Cair 150 ml', 1, 34000, 34000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(7, 3, 26, 'Loratadine 10 mg', 2, 10500, 21000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(8, 3, 31, 'Alkohol 70% 100 ml', 2, 9000, 18000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(9, 3, 4, 'Ibuprofen 400 mg', 1, 6500, 6500, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(10, 3, 37, 'Tolak Angin Cair 15 ml', 1, 5000, 5000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(11, 4, 8, 'Cefadroxil 500 mg', 1, 25000, 25000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(12, 4, 34, 'Simvastatin 20 mg', 2, 15000, 30000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(13, 4, 7, 'Amoxicillin 500 mg', 2, 13000, 26000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(14, 4, 4, 'Ibuprofen 400 mg', 3, 6500, 19500, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(15, 5, 1, 'Paracetamol 500 mg', 2, 4500, 9000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(16, 5, 28, 'Betadine Antiseptik 60 ml', 3, 29000, 87000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(17, 5, 40, 'Masker Medis 3 Ply (Box isi 50)', 1, 35000, 35000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(18, 6, 36, 'Glibenclamide 5 mg', 1, 10500, 10500, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(19, 6, 15, 'Blackmores Vitamin D3 1000 IU', 3, 125000, 375000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(20, 7, 6, 'Natrium Diklofenak 50 mg', 3, 12000, 36000, '2026-09-21 14:16:26', '2026-09-21 14:16:26'),
(21, 8, 17, 'Woods Peppermint Antitussive 60 ml', 1, 27500, 27500, '2026-09-21 14:16:26', '2026-09-21 14:16:26');

SET FOREIGN_KEY_CHECKS = 1;
