-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 26 Mar 2026 pada 10.07
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `appdokumen`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Invoice', 'Billing and payment documents', '2026-03-25 09:10:42', '2026-03-25 09:10:42'),
(2, 'PKS', 'Perjanjian Kerja Sama (Contracts)', '2026-03-25 09:10:42', '2026-03-25 09:10:42'),
(3, 'Foto', 'Progress photos and attachments', '2026-03-25 09:10:42', '2026-03-25 09:10:42'),
(4, 'Legal', 'Permits and legal documents', '2026-03-25 09:10:42', '2026-03-25 09:10:42'),
(5, 'Berita Aktivasi', 'Dokumen Berita Aktivasi', '2026-03-25 19:57:54', '2026-03-25 19:57:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `folder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `documents`
--

INSERT INTO `documents` (`id`, `title`, `file_path`, `file_type`, `category_id`, `folder_id`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(13, 'SURAT_PERNYATAAN_UNAUDITED,_KEBENARAN,_JAMINAN_September_2025', 'documents/pks/1774515996_SURAT_PERNYATAAN_UNAUDITED,_KEBENARAN,_JAMINAN_September_2025.pdf', 'application/pdf', 2, NULL, 1, '2026-03-26 02:06:36', '2026-03-26 02:06:36'),
(14, 'SURAT_PERNYATAAN_UNAUDITED,_KEBENARAN,_JAMINAN_September_2025 Page 1', 'documents/pks/1774515996_SURAT_PERNYATAAN_UNAUDITED,_KEBENARAN,_JAMINAN_September_2025 Page 1.pdf', 'application/pdf', 2, NULL, 1, '2026-03-26 02:06:36', '2026-03-26 02:06:36'),
(15, 'SURAT_PERNYATAAN_UNAUDITED,_KEBENARAN,_JAMINAN_September_2025 Page 2', 'documents/pks/1774515996_SURAT_PERNYATAAN_UNAUDITED,_KEBENARAN,_JAMINAN_September_2025 Page 2.pdf', 'application/pdf', 2, NULL, 1, '2026-03-26 02:06:36', '2026-03-26 02:06:36'),
(16, 'SURAT_PERNYATAAN_UNAUDITED,_KEBENARAN,_JAMINAN_September_2025 Page 3', 'documents/pks/1774515996_SURAT_PERNYATAAN_UNAUDITED,_KEBENARAN,_JAMINAN_September_2025 Page 3.pdf', 'application/pdf', 2, NULL, 1, '2026-03-26 02:06:36', '2026-03-26 02:06:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `download_tokens`
--

CREATE TABLE `download_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `expired_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `file_shares`
--

CREATE TABLE `file_shares` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `file_user_shares`
--

CREATE TABLE `file_user_shares` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `shared_by` bigint(20) UNSIGNED NOT NULL,
  `shared_to` bigint(20) UNSIGNED NOT NULL,
  `permission` enum('view','download') NOT NULL DEFAULT 'view',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `folders`
--

CREATE TABLE `folders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `menus`
--

CREATE TABLE `menus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `route` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `position` varchar(255) NOT NULL DEFAULT 'sidebar',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `menus`
--

INSERT INTO `menus` (`id`, `name`, `route`, `icon`, `position`, `parent_id`, `order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Dashboard', 'dashboard', 'layout-grid', 'sidebar', NULL, 1, 1, '2026-03-25 09:10:44', '2026-03-25 09:10:44'),
(2, 'Documents', NULL, 'folder', 'sidebar', NULL, 2, 1, '2026-03-25 09:10:45', '2026-03-25 21:00:13'),
(3, 'All Documents', 'documents.index', 'file-text', 'sidebar', 2, 1, 1, '2026-03-25 09:10:45', '2026-03-25 21:04:54'),
(4, 'Categories', 'categories.index', 'layers', 'sidebar', NULL, 3, 1, '2026-03-25 09:10:45', '2026-03-25 09:10:45'),
(5, 'Users Management', 'users.index', 'users', 'sidebar', 6, 4, 1, '2026-03-25 09:10:45', '2026-03-25 10:42:49'),
(6, 'Settings', NULL, 'settings', 'sidebar', NULL, 50, 1, '2026-03-25 09:10:45', '2026-03-25 21:02:25'),
(7, 'Menu Management', 'menus.index', 'menu', 'sidebar', 6, 1, 1, '2026-03-25 09:10:45', '2026-03-25 09:10:45'),
(10, 'Tickets', 'tickets.index', 'ticket', 'sidebar', NULL, 8, 1, '2026-03-25 09:10:45', '2026-03-25 09:10:45'),
(11, 'Shared with Me', 'shared.index', 'share-2', 'sidebar', NULL, 6, 1, '2026-03-25 20:02:08', '2026-03-25 21:02:37'),
(12, 'My Drive', 'drive.index', 'hard-drive', 'sidebar', NULL, 5, 1, '2026-03-25 20:16:45', '2026-03-25 20:16:45'),
(13, 'Company Settings', 'settings.company', 'settings', 'sidebar', 6, 5, 1, '2026-03-26 01:39:42', '2026-03-26 01:43:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_25_140000_create_categories_table', 1),
(5, '2026_03_25_150000_create_documents_table', 1),
(6, '2026_03_25_153703_create_menus_table', 1),
(7, '2026_03_25_153703_create_roles_table', 1),
(8, '2026_03_25_153704_create_menu_role_table', 1),
(9, '2026_03_25_153705_add_role_to_users_table', 1),
(10, '2026_03_25_171359_add_position_to_menus_table', 2),
(11, '2026_03_26_025636_create_file_shares_table', 3),
(12, '2026_03_26_025637_create_download_tokens_table', 3),
(13, '2026_03_26_025637_create_file_user_shares_table', 3),
(14, '2026_03_26_031424_create_folders_table', 4),
(15, '2026_03_26_031425_add_folder_id_to_documents_table', 4),
(16, '2026_03_26_034537_create_positions_table', 5),
(17, '2026_03_26_034538_add_position_id_to_users_table', 6),
(18, '2026_03_26_041113_add_disk_quota_to_users_table', 7),
(19, '2026_03_26_083754_create_settings_table', 8);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `positions`
--

CREATE TABLE `positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `positions`
--

INSERT INTO `positions` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Manager', '2026-03-25 20:49:32', '2026-03-25 20:49:32'),
(2, 'NOC', '2026-03-25 20:49:32', '2026-03-25 20:49:32'),
(3, 'CS', '2026-03-25 20:49:32', '2026-03-25 20:49:32'),
(4, 'Teknisi', '2026-03-25 20:49:32', '2026-03-25 20:49:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'root', '2026-03-25 09:10:42', '2026-03-25 09:10:42'),
(2, 'admin', '2026-03-25 09:10:42', '2026-03-25 20:49:32'),
(3, 'member', '2026-03-25 09:10:42', '2026-03-25 20:49:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_menu`
--

CREATE TABLE `role_menu` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `menu_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role_menu`
--

INSERT INTO `role_menu` (`role_id`, `menu_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(2, 1),
(2, 4),
(2, 11),
(2, 12),
(2, 13),
(3, 1),
(3, 11),
(3, 12);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('CrVMfzjz85GmUFgOedU4Z7gBRip28fNvDLqBUxeW', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibHE0VkF1MFk0OUVVWlY0bkliblNmWjFJWnNRcWxLTXd5VmI2cklsQiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2RvY3VtZW50cyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1774512588),
('SklzKJklWVZKgYbownpw43dMdeXSQg9GyHKus8UJ', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicHNrQllWQ0cyWXp1TVN1YnFMSEcxYXlDZE1VRlpab1RiN1BONWtrbCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1774515773),
('zmtNd6r2GmEBJO2KwWbxt7mWMqJdv55wCAylDGL0', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoia2FWQjJSRnhoS2tkV0ZqemVjUjhmaUxqSGNEOFBDdWdsTEg0SHBQMSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9kb2N1bWVudHMiO3M6NToicm91dGUiO3M6MTU6ImRvY3VtZW50cy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1774515996);

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL DEFAULT 'PT. Puskomedia Indonesia Kreatif',
  `company_subtitle` text DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `company_name`, `company_subtitle`, `company_logo`, `created_at`, `updated_at`) VALUES
(1, 'PT. Puskomedia', 'Internet Service Provider & Pengembangan Aplikasi\r\nTeknologi untuk masa depan bisnis Indonesia', 'settings/uUEIEN7Qapntz0NLLcZleZj3cMhAsdcb1u4s0Jhh.png', '2026-03-26 01:38:26', '2026-03-26 01:54:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `position_id` bigint(20) UNSIGNED DEFAULT NULL,
  `disk_quota` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `role_id`, `position_id`, `disk_quota`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'root User', 'root@example.com', NULL, '$2y$12$gSTsDmxpWd6O4E5cY92gY.fvYkPcElXpEcx/Piz.onuqdbXSnBKV.', NULL, '2026-03-25 09:10:43', '2026-03-25 09:10:43'),
(2, 2, 1, NULL, 'Manager User', 'manager@example.com', NULL, '$2y$12$.0lhEDbg08OMTCh0u4Xq/ejez38dB3WXtRhmc0fRnvIt2YwWseXr2', NULL, '2026-03-25 09:10:43', '2026-03-25 20:49:32'),
(3, 3, 2, 3145728, 'NOC User', 'noc@example.com', NULL, '$2y$12$b7pGTpm2JdMc66vZrTTlve1wuOeQSsmn9Rt6CSIDX3DRomfpQxyd2', NULL, '2026-03-25 09:10:44', '2026-03-25 21:16:37'),
(4, 3, 2, NULL, 'CS User', 'cs@example.com', NULL, '$2y$12$IC7DaMUVcEqx6Mn7jkpvsuPPOvZL4/RcGKbxtVrHaEWGeJUjot4uS', NULL, '2026-03-25 09:10:44', '2026-03-25 20:50:06'),
(5, 3, 2, NULL, 'Teknisi User', 'teknisi@example.com', NULL, '$2y$12$XdIxJLJPpnfkpRX6D.h73OCmrhZnDEalPnWWqMNU1IuB80z3lLBZm', NULL, '2026-03-25 09:10:44', '2026-03-25 20:50:06');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_category_id_foreign` (`category_id`),
  ADD KEY `documents_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `documents_folder_id_foreign` (`folder_id`);

--
-- Indeks untuk tabel `download_tokens`
--
ALTER TABLE `download_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `download_tokens_token_unique` (`token`),
  ADD KEY `download_tokens_document_id_foreign` (`document_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `file_shares`
--
ALTER TABLE `file_shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_shares_token_unique` (`token`),
  ADD KEY `file_shares_document_id_foreign` (`document_id`),
  ADD KEY `file_shares_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `file_user_shares`
--
ALTER TABLE `file_user_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_user_shares_document_id_foreign` (`document_id`),
  ADD KEY `file_user_shares_shared_by_foreign` (`shared_by`),
  ADD KEY `file_user_shares_shared_to_foreign` (`shared_to`);

--
-- Indeks untuk tabel `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folders_user_id_foreign` (`user_id`),
  ADD KEY `folders_parent_id_foreign` (`parent_id`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menus_parent_id_foreign` (`parent_id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `positions_name_unique` (`name`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indeks untuk tabel `role_menu`
--
ALTER TABLE `role_menu`
  ADD PRIMARY KEY (`role_id`,`menu_id`),
  ADD KEY `role_menu_menu_id_foreign` (`menu_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`),
  ADD KEY `users_position_id_foreign` (`position_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `download_tokens`
--
ALTER TABLE `download_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `file_shares`
--
ALTER TABLE `file_shares`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `file_user_shares`
--
ALTER TABLE `file_user_shares`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `folders`
--
ALTER TABLE `folders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `positions`
--
ALTER TABLE `positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `download_tokens`
--
ALTER TABLE `download_tokens`
  ADD CONSTRAINT `download_tokens_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `file_shares`
--
ALTER TABLE `file_shares`
  ADD CONSTRAINT `file_shares_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_shares_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `file_user_shares`
--
ALTER TABLE `file_user_shares`
  ADD CONSTRAINT `file_user_shares_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_user_shares_shared_by_foreign` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_user_shares_shared_to_foreign` FOREIGN KEY (`shared_to`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `folders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `role_menu`
--
ALTER TABLE `role_menu`
  ADD CONSTRAINT `role_menu_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_menu_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
