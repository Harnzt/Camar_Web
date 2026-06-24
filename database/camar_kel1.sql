-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2026 at 09:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `camar_kel1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(255) DEFAULT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_id`, `action`, `target_type`, `target_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 15, 'document.reviewed', 'App\\Models\\DocumentVerification', 9, 'Dokumen npwp milik raihangusriihidayat@gmail.com ditinjau.', '{\"status\":\"pending\",\"reviewed_by\":null,\"reviewed_at\":null,\"rejection_reason\":null,\"notes\":null}', '{\"status\":\"approved\",\"reviewed_by\":15,\"reviewed_at\":\"2026-06-21T15:33:39.000000Z\",\"rejection_reason\":null,\"notes\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 08:33:39', '2026-06-21 08:33:39'),
(2, 15, 'admin.created', 'App\\Models\\User', 16, 'Akun admin admin@gmail.com dibuat.', NULL, '{\"name\":\"Admin\",\"email\":\"admin@gmail.com\",\"role\":\"admin\",\"status\":\"verified\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 08:35:33', '2026-06-21 08:35:33'),
(3, 16, 'document.reviewed', 'App\\Models\\DocumentVerification', 9, 'Dokumen npwp milik raihangusriihidayat@gmail.com ditinjau.', '{\"status\":\"approved\",\"reviewed_by\":15,\"reviewed_at\":\"2026-06-21T15:33:39.000000Z\",\"rejection_reason\":null,\"notes\":null}', '{\"status\":\"approved\",\"reviewed_by\":16,\"reviewed_at\":\"2026-06-21T15:36:46.000000Z\",\"rejection_reason\":null,\"notes\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 08:36:46', '2026-06-21 08:36:46'),
(4, 16, 'user.status.updated', 'App\\Models\\User', 13, 'Status akun raihangusriihidayat@gmail.com diubah menjadi verified.', '{\"status\":\"pending\",\"verified_by\":null,\"verified_at\":null,\"rejection_reason\":null,\"suspended_at\":null,\"suspension_reason\":null}', '{\"status\":\"verified\",\"verified_by\":16,\"verified_at\":\"2026-06-21T15:38:48.000000Z\",\"rejection_reason\":null,\"suspended_at\":null,\"suspension_reason\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 08:38:48', '2026-06-21 08:38:48');

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_logs`
--

CREATE TABLE `admin_login_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `logged_in_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logged_out_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_login_logs`
--

INSERT INTO `admin_login_logs` (`id`, `admin_id`, `session_id`, `ip_address`, `user_agent`, `logged_in_at`, `logged_out_at`, `created_at`, `updated_at`) VALUES
(1, 16, 'CKEBEluEE2eZ6VYRFCLvdHsuYp3YsB2MrZ0Kwtuw', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-22 12:01:27', '2026-06-22 05:01:27', '2026-06-22 05:01:14', '2026-06-22 05:01:27'),
(2, 16, 'YnEIozPpJqEzMyz3AIT2V8WK95KaQldjcWaTsUu4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-22 12:28:08', '2026-06-22 05:28:08', '2026-06-22 05:22:41', '2026-06-22 05:28:08'),
(3, 15, 'RYNZpaOkDABdS12QHW8aMju16zTBto8J6pxURuzz', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-22 13:40:30', '2026-06-22 06:40:30', '2026-06-22 05:36:50', '2026-06-22 06:40:30'),
(4, 16, 'Ad2S2h9BiuoqB4FSzGlnu7iljsRpnAHPaqZ1UQ2y', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:15:30', '2026-06-22 07:15:30', '2026-06-22 07:14:03', '2026-06-22 07:15:30'),
(5, 15, 'SHma6rYfudD575VVth7Ld6AzCbn7k9rdwt0rt65c', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:16:23', '2026-06-22 07:16:23', '2026-06-22 07:16:02', '2026-06-22 07:16:23');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-57490977fa0a838417365f7549bd78b85855052b', 'i:1;', 1782286061),
('laravel-cache-57490977fa0a838417365f7549bd78b85855052b:timer', 'i:1782286061;', 1782286061),
('laravel-cache-a133d5a005c87a7c5695935ccf17d358503b82df', 'i:1;', 1782145783),
('laravel-cache-a133d5a005c87a7c5695935ccf17d358503b82df:timer', 'i:1782145783;', 1782145783),
('laravel-cache-boost.roster.scan', 'a:2:{s:6:\"roster\";O:21:\"Laravel\\Roster\\Roster\":3:{s:13:\"\0*\0approaches\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:9:{i:0;O:22:\"Laravel\\Roster\\Package\":6:{s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^12.0\";s:10:\"\0*\0package\";E:37:\"Laravel\\Roster\\Enums\\Packages:LARAVEL\";s:14:\"\0*\0packageName\";s:17:\"laravel/framework\";s:10:\"\0*\0version\";s:7:\"12.44.0\";s:6:\"\0*\0dev\";b:0;}i:1;O:22:\"Laravel\\Roster\\Package\":6:{s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:6:\"v0.3.8\";s:10:\"\0*\0package\";E:37:\"Laravel\\Roster\\Enums\\Packages:PROMPTS\";s:14:\"\0*\0packageName\";s:15:\"laravel/prompts\";s:10:\"\0*\0version\";s:5:\"0.3.8\";s:6:\"\0*\0dev\";b:0;}i:2;O:22:\"Laravel\\Roster\\Package\":6:{s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:3:\"4.1\";s:10:\"\0*\0package\";E:37:\"Laravel\\Roster\\Enums\\Packages:SANCTUM\";s:14:\"\0*\0packageName\";s:15:\"laravel/sanctum\";s:10:\"\0*\0version\";s:5:\"4.1.0\";s:6:\"\0*\0dev\";b:0;}i:3;O:22:\"Laravel\\Roster\\Package\":6:{s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:6:\"v0.5.1\";s:10:\"\0*\0package\";E:33:\"Laravel\\Roster\\Enums\\Packages:MCP\";s:14:\"\0*\0packageName\";s:11:\"laravel/mcp\";s:10:\"\0*\0version\";s:5:\"0.5.1\";s:6:\"\0*\0dev\";b:1;}i:4;O:22:\"Laravel\\Roster\\Package\":6:{s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.24\";s:10:\"\0*\0package\";E:34:\"Laravel\\Roster\\Enums\\Packages:PINT\";s:14:\"\0*\0packageName\";s:12:\"laravel/pint\";s:10:\"\0*\0version\";s:6:\"1.26.0\";s:6:\"\0*\0dev\";b:1;}i:5;O:22:\"Laravel\\Roster\\Package\":6:{s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.41\";s:10:\"\0*\0package\";E:34:\"Laravel\\Roster\\Enums\\Packages:SAIL\";s:14:\"\0*\0packageName\";s:12:\"laravel/sail\";s:10:\"\0*\0version\";s:6:\"1.51.0\";s:6:\"\0*\0dev\";b:1;}i:6;O:22:\"Laravel\\Roster\\Package\":6:{s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.2\";s:10:\"\0*\0package\";E:34:\"Laravel\\Roster\\Enums\\Packages:PEST\";s:14:\"\0*\0packageName\";s:12:\"pestphp/pest\";s:10:\"\0*\0version\";s:5:\"4.2.0\";s:6:\"\0*\0dev\";b:1;}i:7;O:22:\"Laravel\\Roster\\Package\":6:{s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:6:\"12.5.3\";s:10:\"\0*\0package\";E:37:\"Laravel\\Roster\\Enums\\Packages:PHPUNIT\";s:14:\"\0*\0packageName\";s:15:\"phpunit/phpunit\";s:10:\"\0*\0version\";s:6:\"12.5.3\";s:6:\"\0*\0dev\";b:1;}i:8;O:22:\"Laravel\\Roster\\Package\":6:{s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:10:\"\0*\0package\";E:41:\"Laravel\\Roster\\Enums\\Packages:TAILWINDCSS\";s:14:\"\0*\0packageName\";s:11:\"tailwindcss\";s:10:\"\0*\0version\";s:5:\"4.2.4\";s:6:\"\0*\0dev\";b:1;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:21:\"\0*\0nodePackageManager\";E:43:\"Laravel\\Roster\\Enums\\NodePackageManager:NPM\";}s:9:\"timestamp\";i:1782143821;}', 1782230221);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_verifications`
--

CREATE TABLE `document_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `document_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected','revision_required') NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_verifications`
--

INSERT INTO `document_verifications` (`id`, `user_id`, `document_type`, `document_path`, `status`, `reviewed_by`, `reviewed_at`, `rejection_reason`, `notes`, `created_at`, `updated_at`) VALUES
(1, 9, 'nib', 'documents/9/nib_9_1779621722.png', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(2, 9, 'akta', 'documents/9/akta_9_1779621722.png', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(3, 9, 'npwp', 'documents/9/npwp_9_1779621721.png', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(4, 10, 'vcs', 'documents/10/vcs_10_1780480635.pdf', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(5, 10, 'npwp', 'documents/10/npwp_10_1780480634.pdf', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(6, 10, 'gold_standard', 'documents/10/gold_standard_10_1780480635.pdf', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(7, 11, 'npwp', 'documents/11/npwp_11_1780480876.pdf', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(8, 12, 'npwp', 'documents/12/npwp_12_1780501022.pdf', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(9, 13, 'npwp', 'documents/13/npwp_13_1782040175.jpg', 'approved', 16, '2026-06-21 08:36:46', NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:36:46'),
(10, 14, 'npwp', 'documents/14/npwp_14_1782040935.jpg', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(11, 14, 'akta', 'documents/14/akta_14_1782040935.jpg', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(12, 14, 'nib', 'documents/14/nib_14_1782040935.jpg', 'pending', NULL, NULL, NULL, NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `emission_calculations`
--

CREATE TABLE `emission_calculations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `calculation_mode` varchar(20) DEFAULT NULL,
  `scope1_kg` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `scope2_kg` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `scope3_kg` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `scope_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scope_details`)),
  `total_kg` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `total_ton` decimal(15,6) NOT NULL DEFAULT 0.000000,
  `fuel_consumption` decimal(15,4) DEFAULT NULL,
  `fuel_factor` decimal(10,4) DEFAULT NULL,
  `electricity_consumption` decimal(15,4) DEFAULT NULL,
  `electricity_factor` decimal(10,4) DEFAULT NULL,
  `transport_distance` decimal(15,4) DEFAULT NULL,
  `transport_factor` decimal(10,4) DEFAULT NULL,
  `waste_amount` decimal(15,4) DEFAULT NULL,
  `waste_factor` decimal(10,4) DEFAULT NULL,
  `estimated_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `price_per_ton` decimal(10,2) NOT NULL DEFAULT 50000.00,
  `is_offset` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `emission_calculations`
--

INSERT INTO `emission_calculations` (`id`, `user_id`, `calculation_mode`, `scope1_kg`, `scope2_kg`, `scope3_kg`, `scope_details`, `total_kg`, `total_ton`, `fuel_consumption`, `fuel_factor`, `electricity_consumption`, `electricity_factor`, `transport_distance`, `transport_factor`, `waste_amount`, `waste_factor`, `estimated_cost`, `price_per_ton`, `is_offset`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 0.0000, 0.0000, 0.0000, NULL, 246.7000, 0.246700, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 12335.00, 50000.00, 0, '2026-05-01 11:13:06', '2026-05-01 11:13:06'),
(2, 1, NULL, 0.0000, 0.0000, 0.0000, NULL, 1452.7000, 1.452700, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 72635.00, 50000.00, 0, '2026-05-14 23:00:34', '2026-05-14 23:00:34'),
(3, 9, NULL, 0.0000, 0.0000, 0.0000, NULL, 194511.5610, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 50000.00, 0, '2026-06-03 10:13:34', '2026-06-03 10:13:34'),
(4, 9, NULL, 0.0000, 0.0000, 0.0000, NULL, 20164.9360, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3024740.40, 50000.00, 0, '2026-06-03 10:23:11', '2026-06-03 10:23:11'),
(5, 11, NULL, 0.0000, 0.0000, 0.0000, NULL, 334.2641, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50139.61, 50000.00, 0, '2026-06-05 01:52:25', '2026-06-05 01:52:25'),
(6, 11, NULL, 0.0000, 0.0000, 0.0000, NULL, 178.9800, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 26847.00, 50000.00, 0, '2026-06-10 00:13:51', '2026-06-10 00:13:51'),
(7, 11, NULL, 0.0000, 0.0000, 0.0000, NULL, 140.1840, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21027.60, 50000.00, 0, '2026-06-10 00:19:33', '2026-06-10 00:19:33'),
(8, 12, NULL, 0.0000, 0.0000, 0.0000, NULL, 189.0000, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 28350.00, 50000.00, 0, '2026-06-10 00:29:59', '2026-06-10 00:29:59'),
(9, 12, NULL, 0.0000, 0.0000, 0.0000, NULL, 3531.0200, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 529653.00, 50000.00, 0, '2026-06-16 21:41:59', '2026-06-16 21:41:59'),
(10, 12, NULL, 0.0000, 0.0000, 0.0000, NULL, 25809.0000, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3871350.00, 50000.00, 0, '2026-06-16 21:43:01', '2026-06-16 21:43:01'),
(11, 12, NULL, 0.0000, 0.0000, 0.0000, NULL, 25396.9560, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3809543.40, 50000.00, 0, '2026-06-16 21:47:48', '2026-06-16 21:47:48'),
(12, 12, NULL, 0.0000, 0.0000, 0.0000, NULL, 20608.2710, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3091240.65, 50000.00, 0, '2026-06-17 05:32:00', '2026-06-17 05:32:00'),
(13, 12, NULL, 0.0000, 0.0000, 0.0000, NULL, 26416.6880, 0.000000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3962503.20, 50000.00, 0, '2026-06-17 05:35:30', '2026-06-17 05:35:30'),
(14, 14, NULL, 195770.0000, 135961.9620, 240.7760, NULL, 331972.7380, 331.972738, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 49795910.70, 150000.00, 0, '2026-06-21 05:01:15', '2026-06-21 05:01:15'),
(15, 14, NULL, 12385.0000, 8995.6860, 3990.0000, NULL, 25370.6860, 25.370686, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3805602.90, 150000.00, 0, '2026-06-22 06:46:05', '2026-06-22 06:46:05'),
(16, 14, 'company', 10065.0000, 8790.0000, 7272.4400, '{\"scope1\":[{\"label\":\"Pembakaran stasioner\",\"value_kg\":3150},{\"label\":\"Kendaraan operasional\",\"value_kg\":6915.000000000001}],\"scope2\":[{\"label\":\"Konsumsi listrik\",\"value_kg\":8790}],\"scope3\":[{\"label\":\"Perjalanan pesawat\",\"value_kg\":7272.4400000000005},{\"label\":\"Akomodasi hotel\",\"value_kg\":0},{\"label\":\"Perjalanan kereta\",\"value_kg\":0}]}', 26127.4400, 26.127440, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3919116.00, 150000.00, 0, '2026-06-22 06:55:30', '2026-06-22 06:55:30'),
(17, 13, 'personal', 6372.0000, 971.8800, 15138.0000, '{\"scope1\":[{\"label\":\"Energi rumah tangga\",\"value_kg\":3780},{\"label\":\"Kendaraan pribadi\",\"value_kg\":2592}],\"scope2\":[{\"label\":\"Konsumsi listrik\",\"value_kg\":971.88}],\"scope3\":[{\"label\":\"Transportasi umum\",\"value_kg\":1530},{\"label\":\"Konsumsi makanan\",\"value_kg\":3240},{\"label\":\"Penggunaan air\",\"value_kg\":4128},{\"label\":\"Pengelolaan limbah\",\"value_kg\":6240}]}', 22481.8800, 22.481880, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3372282.00, 150000.00, 0, '2026-06-22 07:32:55', '2026-06-22 07:32:55'),
(18, 13, 'personal', 4609.4400, 2915.6400, 4531.2990, '{\"scope1\":[{\"label\":\"Energi rumah tangga\",\"value_kg\":3780},{\"label\":\"Kendaraan pribadi\",\"value_kg\":829.4399999999999}],\"scope2\":[{\"label\":\"Konsumsi listrik\",\"value_kg\":2915.64}],\"scope3\":[{\"label\":\"Transportasi umum\",\"value_kg\":12.099},{\"label\":\"Konsumsi makanan\",\"value_kg\":1620},{\"label\":\"Penggunaan air\",\"value_kg\":1651.1999999999998},{\"label\":\"Pengelolaan limbah\",\"value_kg\":1248}]}', 12056.3790, 12.056379, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1808456.85, 150000.00, 0, '2026-06-22 08:42:07', '2026-06-22 08:42:07');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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
-- Table structure for table `jobs`
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
-- Table structure for table `job_batches`
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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_29_154129_create_emissions_table', 1),
(5, '2026_05_01_174451_create_sessions_table', 1),
(6, '2026_05_02_123946_add_seller_id_to_projects_table', 2),
(7, '2026_05_04_045126_create_orders_table', 3),
(8, '2026_06_03_110611_create_cart_items_table', 4),
(9, '2026_06_21_180000_create_admin_management_tables', 4),
(10, '2026_06_21_180100_ensure_transactions_table_exists', 5),
(11, '2026_06_22_190000_create_admin_login_logs_table', 6),
(12, '2026_06_22_210000_add_scope_details_to_emission_calculations_table', 7),
(13, '2026_06_22_155727_create_personal_access_tokens_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(15,2) NOT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `buyer_name` varchar(255) DEFAULT NULL,
  `buyer_email` varchar(255) DEFAULT NULL,
  `buyer_phone` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `status_updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `project_id`, `order_number`, `quantity`, `subtotal`, `tax`, `total_price`, `payment_method`, `buyer_name`, `buyer_email`, `buyer_phone`, `status`, `status_updated_by`, `status_updated_at`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 12, 7, 'ORD-7FCPGALI', 1, 275000.00, 30250.00, 305250.00, 'midtrans', 'EL', 'elbuyer@gmail.com', '67343456576', 'paid', NULL, NULL, NULL, '2026-06-03 09:30:37', '2026-06-03 09:30:37'),
(2, 9, 7, 'ORD-RQOFVCPO', 1, 275000.00, 30250.00, 305250.00, 'midtrans', 'PIC', 'buyer@gmail.com', '0812345678', 'paid', NULL, NULL, NULL, '2026-06-03 09:32:00', '2026-06-03 09:32:00'),
(3, 9, 6, 'ORD-QIIOWVYQ', 1, 195000.00, 21450.00, 216450.00, 'midtrans', 'PIC', 'buyer@gmail.com', '0812345678', 'paid', NULL, NULL, NULL, '2026-06-03 09:56:52', '2026-06-03 09:56:52'),
(4, 11, 5, 'ORD-GWRBM651', 2, 420000.00, 46200.00, 466200.00, 'midtrans', 'elviraa2', 'elviraa@gmail.com', '+62 813727456', 'paid', NULL, NULL, NULL, '2026-06-05 01:51:10', '2026-06-05 01:51:10'),
(5, 10, 5, 'ORD-J9YBFOSF', 1, 210000.00, 23100.00, 233100.00, 'midtrans', 'elvira', 'elvira@gmail.com', '+62 813727456', 'paid', NULL, NULL, NULL, '2026-06-08 00:35:09', '2026-06-08 00:35:09'),
(6, 10, 2, 'ORD-3VA4QEWM', 2, 360000.00, 39600.00, 399600.00, 'midtrans', 'elvira', 'elvira@gmail.com', '+62 813727456', 'paid', NULL, NULL, NULL, '2026-06-08 00:36:43', '2026-06-08 00:36:43'),
(7, 10, 4, 'ORD-W04IZOIH', 2, 580000.00, 63800.00, 643800.00, 'midtrans', 'elvira', 'elvira@gmail.com', NULL, 'paid', NULL, NULL, NULL, '2026-06-08 00:39:43', '2026-06-08 00:39:43'),
(8, 11, 10, 'ORD-8KPRY5DB', 2, 480000.00, 52800.00, 532800.00, 'midtrans', 'elviraa2', 'elviraa@gmail.com', NULL, 'paid', NULL, NULL, NULL, '2026-06-09 20:54:52', '2026-06-09 20:54:52'),
(9, 12, 3, 'ORD-Y1UQADUS', 2, 640000.00, 70400.00, 710400.00, 'midtrans', 'EL', 'elbuyer@gmail.com', NULL, 'paid', NULL, NULL, NULL, '2026-06-09 21:52:35', '2026-06-09 21:52:35'),
(10, 9, 14, 'ORDER-1781073289-1-NIZ', 1, 300000.00, 33000.00, 333000.00, 'midtrans', 'PIC', 'buyer@gmail.com', '0812345678', 'paid', NULL, NULL, NULL, '2026-06-09 23:34:49', '2026-06-09 23:35:57'),
(11, 9, 16, 'ORDER-1781073522-1-L2K', 1, 270000.00, 29700.00, 299700.00, 'midtrans', 'PIC', 'buyer@gmail.com', '0812345678', 'paid', NULL, NULL, NULL, '2026-06-09 23:38:42', '2026-06-09 23:38:56'),
(12, 1, 13, 'ORDER-1781074027-1-XP3', 1, 285000.00, 31350.00, 316350.00, 'midtrans', 'Yonanda', '1234@gmail.com', '0812345678', 'paid', NULL, NULL, NULL, '2026-06-09 23:47:07', '2026-06-09 23:47:16'),
(13, 12, 10, 'ORDER-1781669868-1-JO4', 1, 240000.00, 26400.00, 266400.00, 'midtrans', 'EL', 'elbuyer@gmail.com', '67343456576', 'pending', NULL, NULL, NULL, '2026-06-16 21:17:48', '2026-06-16 21:17:48'),
(14, 14, 5, 'ORDER-1782043390-1-HYQ', 1, 210000.00, 23100.00, 233100.00, 'midtrans', 'Raihan', 'jadigasih@gmail.com', '+6282281345689', 'pending', NULL, NULL, NULL, '2026-06-21 05:03:10', '2026-06-21 05:03:10'),
(15, 14, 5, 'ORDER-1782046701-1-NFO', 7, 1470000.00, 161700.00, 1631700.00, 'midtrans', 'Raihan', 'jadigasih@gmail.com', '+6282281345689', 'paid', NULL, NULL, NULL, '2026-06-21 05:58:21', '2026-06-21 05:58:37'),
(22, 13, 21, 'ORDER-1782230287-1-TGMV', 1, 209997.00, 23100.00, 233097.00, 'qris', 'Raihan Gusri Hidayat', 'raihangusriihidayat@gmail.com', '081234567890', 'paid', NULL, NULL, NULL, '2026-06-23 08:58:07', '2026-06-23 09:00:34');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Lihat Dashboard Admin', 'admin.dashboard', NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(2, 'Verifikasi Akun', 'users.verify', NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(3, 'Verifikasi Dokumen', 'documents.verify', NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(4, 'Verifikasi Proyek', 'projects.verify', NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(5, 'Kelola Status Transaksi', 'transactions.manage', NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(6, 'Kelola Admin', 'admins.manage', NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(7, 'Kelola Role dan Permission', 'permissions.manage', NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(8, 'Lihat Audit Log', 'audit.view', NULL, '2026-06-21 08:25:47', '2026-06-21 08:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(4, 'App\\Models\\User', 13, 'camar-flutter', '73ff6969260285b43297ae6ebaa5739cf229ef54bd258b78f13b8a9887f0c13f', '[\"role:buyer\"]', NULL, '2026-07-23 07:44:54', '2026-06-23 07:44:54', '2026-06-23 07:44:54'),
(7, 'App\\Models\\User', 13, 'camar-flutter', '35c910b216cad1e281edae1aaa1bfb660b7531920511ea129cf5d2ff2fb1faab', '[\"role:buyer\"]', NULL, '2026-07-23 08:31:45', '2026-06-23 08:31:45', '2026-06-23 08:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` bigint(20) UNSIGNED DEFAULT NULL,
  `verification_status` enum('pending','approved','rejected','revision_required') NOT NULL DEFAULT 'approved',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `standard` varchar(255) DEFAULT NULL,
  `duration_months` int(11) NOT NULL DEFAULT 12,
  `price_per_ton` decimal(15,2) NOT NULL,
  `stock_available` int(11) NOT NULL DEFAULT 0,
  `area_ha` bigint(20) DEFAULT NULL,
  `co2_per_year` bigint(20) DEFAULT NULL,
  `families_impacted` int(11) DEFAULT NULL,
  `verified_year` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `methodology` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `seller_id`, `verification_status`, `reviewed_by`, `reviewed_at`, `rejection_reason`, `admin_notes`, `submitted_at`, `name`, `company_name`, `category`, `location`, `standard`, `duration_months`, `price_per_ton`, `stock_available`, `area_ha`, `co2_per_year`, `families_impacted`, `verified_year`, `description`, `methodology`, `image`, `created_at`, `updated_at`) VALUES
(1, 2, 'approved', NULL, NULL, NULL, NULL, '2026-04-29 00:57:34', 'Rehabilitasi Mangrove Pesisir Jawa', 'PT Konservasi Hijau', 'Blue Carbon', 'Jawa Barat, Indonesia', 'Verra VCS', 24, 250000.00, 1468, 500, 12000, 85, 2024, 'Restorasi ekosistem mangrove seluas 500 hektar di pesisir utara Jawa untuk mitigasi abrasi dan penyerapan karbon.', 'VM0007', 'mangrove5.jpg', '2026-04-29 00:57:34', '2026-05-09 03:13:58'),
(2, 5, 'approved', NULL, NULL, NULL, NULL, '2026-04-29 00:57:34', 'Konservasi Hutan Kalimantan', 'Yayasan Rimba Lestari', 'Forestry & Nature-Based', 'Kalimantan, Indonesia', NULL, 36, 180000.00, 150, 1000, 12000, 72, 2026, 'Perlindungan dan pemulihan hutan hujan tropis Kalimantan seluas 1000 hektar untuk habitat orangutan dan penyimpanan karbon.', 'Identifikasi Spasial (SIG/GIS)', 'hutan.jpg', '2026-04-29 00:57:34', '2026-05-09 03:27:31'),
(3, 8, 'approved', NULL, NULL, NULL, NULL, '2026-03-16 00:57:34', 'Pembangkit Listrik Tenaga Surya', 'Indonesia Green Energy', 'Renewable Energy', 'Indonesia', NULL, 60, 320000.00, 4998, NULL, 3200, 43, 2024, 'Instalasi sistem PLTS skala komunitas dengan kapasitas 5MW untuk mengurangi emisi dari pembangkit fosil.', 'Photovoltaic', 'PLTS.jpg', '2026-03-16 00:57:34', '2026-06-09 21:52:35'),
(4, 8, 'approved', NULL, NULL, NULL, NULL, '2026-03-31 13:43:30', 'Restorasi Lahan Gambut Sumatra', 'Gambut Lestari Foundation', 'Forestry & Nature-Based', 'Sumatra, Indonesia.', NULL, 48, 290000.00, 793, 1000, 12000, 43, 2026, 'Pemulihan dan rewetting lahan gambut terdegradasi seluas 800 hektar untuk mencegah kebakaran.', NULL, 'gambut.jpg', '2026-03-31 13:43:30', '2026-06-08 00:39:43'),
(5, 1, 'approved', NULL, NULL, NULL, NULL, '2026-05-03 23:14:10', 'Sistem Biogas Desa Mandiri', 'Bio Energy Indonesia', 'Renewable Energy', 'Bogor, Indonesia', NULL, 30, 210000.00, 490, NULL, 12000, 26, 2026, 'Pembangunan 500 unit biogas rumah tangga dari limbah ternak untuk mengurangi penggunaan LPG.', NULL, 'biogas.jpg', '2026-05-03 23:14:10', '2026-06-21 05:58:37'),
(6, 2, 'approved', NULL, NULL, NULL, NULL, '2026-04-30 23:18:47', 'Agroforestri Kopi Berkelanjutan', 'Petani Hijau Nusantara', 'Agriculture & Land Use', NULL, NULL, 36, 195000.00, 210, 300, 3200, 63, 2024, 'Sistem agroforestri terpadu dengan tanaman kopi dan pohon pelindung di 300 hektar lahan.', NULL, 'agroforestri.jpg', '2026-04-30 23:18:47', '2026-05-09 03:13:58'),
(7, 1, 'approved', NULL, NULL, NULL, NULL, '2026-03-01 23:45:50', 'Konservasi Terumbu Karang Bali', 'Ocean Conservation ID', 'Blue Carbon', 'Bali, Indonesia', NULL, 24, 275000.00, 192, 300, 12000, NULL, 2025, 'Rehabilitasi terumbu karang dan blue carbon di perairan Bali untuk ekosistem laut yang sehat.', 'Artificial Patch Reefs', 'coral.jpg', '2026-03-01 23:45:50', '2026-05-09 03:35:43'),
(8, 2, 'approved', NULL, NULL, NULL, NULL, '2023-03-13 02:27:38', 'Energi Angin Offshore Sulawesi', 'Wind Power Indonesia', 'Renewable Energy', 'Sulawesi, Indonesia.', 'IEC 61400', 60, 350000.00, 16, 10000, 6100, 34, 2023, 'Pembangunan wind farm offshore dengan kapasitas 10MW untuk energi bersih Sulawesi.', 'Floating Offshore Wind Turbine (FOWT)', 'offshore.jpg', '2023-03-13 02:27:38', NULL),
(9, 1, 'approved', NULL, NULL, NULL, NULL, '2016-05-10 02:34:15', 'Reforestasi Gunung Merapi', 'Green Mountain Initiative', 'orestry & Nature-Based', 'Yogyakarta, Indonesia.', 'Restorasi Aktif', 48, 220000.00, 23, 600, 360, 353, 2015, 'Penanaman 100,000 pohon endemik di lereng Gunung Merapi untuk konservasi tanah dan air.', 'Enrichment Planting', 'merapi.jpg', '2016-05-10 02:34:15', NULL),
(10, 1, 'approved', NULL, NULL, NULL, NULL, '2026-05-15 02:42:21', 'Pengelolaan Limbah Organik Kota', 'Urban Waste Solutions', 'Waste Management', 'Jakarta, Indoensia.', 'SNI', 36, 240000.00, 44, 750, 4779091, 72, 2024, 'Sistem pengolahan limbah organik menjadi kompos dan biogas untuk 50,000 rumah tangga.', 'Composting', 'waste.jpg', '2026-05-15 02:42:21', '2026-06-09 20:54:52'),
(11, 2, 'approved', NULL, NULL, NULL, NULL, '2025-04-01 02:48:59', 'Konservasi Savana Nusa Tenggara', 'Savana Conservation Trust', 'Forestry & Nature-Based', 'Nusa Tenggara, Indonesia.', NULL, 36, 265000.00, 40, 400, 440662, 27, 2025, 'Perlindungan ekosistem savana dan habitat komodo di NTT seluas 400 hektar.', 'Manajemen Partisipatif & Patroli', 'savana.jpg', '2025-04-01 02:48:59', NULL),
(12, 1, 'approved', NULL, NULL, NULL, NULL, '2025-02-01 03:06:17', 'PLTA Mikro Hidro Papua', 'Hydro Power Papua', 'Renewable Energy', 'Papua, Indonesia.', 'Mekano-Elektrikal', 48, 310000.00, 56, 3000, 21693, 31, 2025, 'Pembangunan 10 unit PLTA mikro hidro untuk desa terpencil di Papua tanpa listrik.', 'Feasibility Study', 'PLTA.jpg', '2025-02-01 03:06:17', NULL),
(13, 2, 'approved', NULL, NULL, NULL, NULL, '2024-05-06 03:10:30', 'Restorasi Danau Toba', 'Lake Toba Foundation', 'Forestry & Nature-Based', 'Sumatera Utara, Indoensia.', NULL, 60, 285000.00, 11, 1500, 30, 51, 2024, 'Program pemulihan kualitas air Danau Toba melalui reboisasi catchment area 1500 hektar.', 'Ekohidrologi dan Zonasi', 'lake.jpg', '2024-05-06 03:10:30', '2026-06-09 23:47:16'),
(14, 1, 'approved', NULL, NULL, NULL, NULL, '2020-05-04 03:10:30', 'Urban Forest Jakarta', 'Jakarta Green City', 'Forestry & Nature-Based', 'Jakarta, Indonesia.', NULL, 24, 300000.00, 249, 50, 50049, 36, 2020, 'Pembangunan hutan kota seluas 50 hektar di Jakarta untuk carbon sink dan udara bersih.', NULL, 'urban.jpg', '2020-05-04 03:10:30', '2026-06-09 23:35:57'),
(15, 2, 'approved', NULL, NULL, NULL, NULL, '2023-01-02 03:17:12', 'Konservasi Satwa Langka Kalimantan', 'Wildlife Conservation Society', 'Forestry & Nature-Based', 'Kalimantan, Indonesia.', 'PermenLHK', 60, 330000.00, 2000, 45, 172, 75, 2023, 'Perlindungan habitat orangutan, gajah pygmy, dan badak sumatera di Kalimantan Timur.', 'Konservasi In-Situ', 'atwa.jpg', '2023-01-02 03:17:12', NULL),
(16, 1, 'approved', NULL, NULL, NULL, NULL, '2020-04-06 03:17:12', 'Solar Panel Sekolah Nasional', 'Solar Education ID', 'Renewable Energy', 'Indonesia', 'SNI', 36, 270000.00, 999, 10, 45, NULL, 2020, 'Instalasi panel surya di 200 sekolah untuk mengurangi biaya listrik dan edukasi energi bersih.', NULL, 'school.jpg', '2020-04-06 03:17:12', '2026-06-09 23:38:56'),
(17, 2, 'approved', NULL, NULL, NULL, NULL, '2016-03-07 03:24:07', 'Reklamasi Tambang Berkelanjutan', 'Mine Restoration Corp', 'Forestry & Nature-Based', 'Indonesia', 'UU No. 4 Tahun 2009', 48, 295000.00, 60, 600, 250, NULL, 2015, 'Reklamasi dan reforestasi lahan bekas tambang seluas 600 hektar di Kalimantan Selatan.', 'Bioengineering', 'mine.jpg', '2016-03-07 03:24:07', NULL),
(18, 1, 'approved', NULL, NULL, NULL, NULL, '2020-05-04 03:24:07', 'Pertanian Organik Ramah Lingkungan', 'Organic Farming Alliance', 'Agriculture & Land Use', 'Indonesia', 'SNI & Internasional', 36, 175000.00, 55, 500, 63, NULL, 2020, 'Konversi 500 hektar sawah konvensional menjadi pertanian organik dengan emisi rendah.', NULL, 'organic.jpg', '2020-05-04 03:24:07', NULL),
(19, 2, 'approved', NULL, NULL, NULL, NULL, '2018-02-05 03:30:32', 'Efisiensi Energi Gedung Perkantoran', 'Green Building Solutions', 'Energy Efficiency', 'Jakarta, Indonesia.', 'ISO 50001:2018', 36, 340000.00, 50, 4000, 36, 50, 2018, 'Retrofit 50 gedung perkantoran dengan sistem HVAC efisien dan LED untuk hemat energi 40%.', 'Komprehensif ', 'building.jpg', '2018-02-05 03:30:32', NULL),
(20, 1, 'approved', NULL, NULL, NULL, NULL, '2019-05-13 03:30:32', 'Mangrove Blue Carbon Riau', 'Riau Mangrove Project', 'Blue Carbon', 'Riau, Indonesia.', 'VCS', 48, 255000.00, 70, 700, 2000, NULL, 2019, 'Penanaman dan perlindungan mangrove seluas 700 hektar di pesisir Riau untuk blue carbon.', NULL, 'riau.jpg', '2019-05-13 03:30:32', NULL),
(21, 10, 'approved', NULL, NULL, NULL, NULL, '2026-06-10 00:44:47', 'Co-Firing Biomassa di PLTU Batu Bara', 'Mitra CAMAR', 'wind', 'Indonesia', 'Verra VCS', 12, 209997.00, 45, NULL, 71, 6, NULL, 'Pembangkit Listrik Tenaga Uap (PLTU) mencampur batu bara dengan bahan bakar biomassa (seperti pelet kayu, sekam padi, atau cangkang sawit) hingga porsi tertentu (misal 5-10%). Kredit karbon dihitung dari selisih penurunan volume batu bara yang dibakar, karena porsi energi tersebut digantikan oleh karbon netral dari biomassa.', 'ACM0003', '1781077487.jpg', '2026-06-10 00:44:47', '2026-06-23 09:00:34');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'Buyer', 'buyer', 'Pembeli kredit karbon', 1, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(2, 'Seller', 'seller', 'Penyedia proyek karbon', 1, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(3, 'Admin', 'admin', 'Petugas operasional dan verifikasi', 1, '2026-06-21 08:25:47', '2026-06-21 08:25:47'),
(4, 'Super Admin', 'super_admin', 'Pengelola administrator dan seluruh sistem', 1, '2026-06-21 08:25:47', '2026-06-21 08:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(4, 7),
(4, 8);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
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
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0I0xDmFpPU1AblUMag7SSRqUSMpdvKyL2NbaEOur', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoielNVa0dwa3I1YXVsT051dHRoaUg4RjZINGtpNjNiZVlsSGlMTkEzNSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9rYWxrdWxhdG9yIjtzOjU6InJvdXRlIjtzOjEwOiJjYWxjdWxhdG9yIjt9fQ==', 1782142962),
('JxjtsCELKu4ZM0S76bZSIOpsKnIq8g1S2IvHAJV7', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYzBDWWtaRWRwWDQ3VjdjaURINGJiNmdzdW9jYWl2bUNBNWlnOUJxdyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9fQ==', 1782137786),
('WX26O6Sivao8ANM3ga0HdYAXra6mRgcfh866dPj9', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiODFJekFPUEhmTGV0UGN4NThaMFhBbkR2eG9WclNLbUNiNUQ1eVE4USI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9', 1782287448);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_code` varchar(255) NOT NULL,
  `buyer_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `emission_calculation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_ton` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `offset_ton` decimal(10,4) NOT NULL,
  `status` enum('pending','paid','verified','completed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `certificate_number` varchar(255) DEFAULT NULL,
  `certificate_issued_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `role` enum('buyer','seller','admin','super_admin') NOT NULL DEFAULT 'buyer',
  `account_category` enum('company','personal') NOT NULL DEFAULT 'personal',
  `status` enum('pending','verified','rejected','suspended') NOT NULL DEFAULT 'pending',
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `profile_photo` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `company_name`, `industry`, `position`, `job_title`, `role`, `account_category`, `status`, `verified_by`, `verified_at`, `rejection_reason`, `suspended_at`, `suspension_reason`, `documents`, `profile_photo`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Yonanda', '1234@gmail.com', '$2y$12$1ZNpFnt6ItbTs5w2712PGu16zSalxeGobiosfyl.kqdkmfHpuerNS', '0812345678', 'Jakarta', NULL, NULL, NULL, 'Lainnya', 'buyer', 'personal', 'pending', NULL, NULL, NULL, NULL, NULL, '\"{\\\"npwp\\\":\\\"documents\\\\/1\\\\/npwp_1_1777657819.jpg\\\"}\"', NULL, NULL, '2026-05-01 10:50:19', '2026-05-01 10:50:19', NULL),
(2, 'Y', 'camar@gmail.com', '$2y$12$Aj0y/QXQWhxt49oxZLjpbuK9xb10fTe/7fuk8K/Fdv3bjXE4bw4jK', '0812345678', 'Jakarta', NULL, NULL, NULL, 'Lainnya', 'seller', 'personal', 'pending', NULL, NULL, NULL, NULL, NULL, '\"{\\\"npwp\\\":\\\"documents\\\\/2\\\\/npwp_2_1777725129.jpg\\\",\\\"gold_standard\\\":\\\"documents\\\\/2\\\\/gold_standard_2_1777725130.png\\\"}\"', NULL, NULL, '2026-05-02 05:32:09', '2026-05-02 05:32:10', NULL),
(4, 'Yonanda Rianita', 'yonaanda06@gmail.com', '$2y$12$Oh1vUrBPVaFLMsQGRQj0NuAIM5kIeK6MA9gvEcsOBPLdo1kGxj7aW', '0812345678', 'Jakarta', NULL, NULL, NULL, 'Lainnya', 'buyer', 'personal', 'pending', NULL, NULL, NULL, NULL, NULL, '\"{\\\"npwp\\\":\\\"documents\\\\/4\\\\/npwp_4_1777868413.png\\\"}\"', NULL, NULL, '2026-05-03 21:20:13', '2026-05-03 21:20:13', NULL),
(5, 'Yonanda', 'carbon@camar.com', '$2y$12$9haNeNsMP5KodU8B5p01RuikaHV1uwtQpSejS3BYrbb.M.ciFs0kW', '0812345678', 'Jakarta', 'PT. Camar', 'transport', 'Manager', NULL, 'seller', 'company', 'pending', NULL, NULL, NULL, NULL, NULL, '\"{\\\"npwp\\\":\\\"documents\\\\/5\\\\/npwp_5_1777870462.png\\\",\\\"akta\\\":\\\"documents\\\\/5\\\\/akta_5_1777870462.png\\\",\\\"nib\\\":\\\"documents\\\\/5\\\\/nib_5_1777870462.png\\\",\\\"gold_standard\\\":\\\"documents\\\\/5\\\\/gold_standard_5_1777870462.png\\\"}\"', NULL, NULL, '2026-05-03 21:54:22', '2026-05-03 21:54:22', NULL),
(6, 'Adam Malik', 'adamalik@gmail.com', '$2y$12$ky4D0Ayx0ih99..dJQHbaezFeBGO1lRM8kKsq81YiDJAtwknD/pz6', '0812345678', 'Jakarta', NULL, NULL, NULL, 'Lainnya', 'seller', 'personal', 'pending', NULL, NULL, NULL, NULL, NULL, '\"{\\\"npwp\\\":\\\"documents\\\\/6\\\\/npwp_6_1779219591.png\\\",\\\"gold_standard\\\":\\\"documents\\\\/6\\\\/gold_standard_6_1779219591.png\\\"}\"', NULL, NULL, '2026-05-19 12:39:51', '2026-05-19 12:39:51', NULL),
(7, 'PIC 1', 'coba1@gmail.com', '$2y$12$xGeqg4dHKwMFlvjzh2FOAu4VPeVLmGyhbAU19ZgyJ4n01Sx9/4Xfq', '0812345678', 'Jkt', 'PT Coba 1', 'retail', 'Manager', NULL, 'buyer', 'company', 'pending', NULL, NULL, NULL, NULL, NULL, '\"{\\\"npwp\\\":\\\"documents\\\\/7\\\\/npwp_7_1779296270.jpg\\\",\\\"akta\\\":\\\"documents\\\\/7\\\\/akta_7_1779296270.png\\\",\\\"nib\\\":\\\"documents\\\\/7\\\\/nib_7_1779296270.png\\\"}\"', NULL, NULL, '2026-05-20 09:57:50', '2026-05-20 09:57:50', NULL),
(8, 'Seller', 'seller@gmail.com', '$2y$12$98g9pAWQqQ9lMoyUrFLFM.bG8BxgjUGu2cD0Q2ic7h1MU3vFokRTW', '0812345678', 'Jkt', 'PT. Seller', 'retail', 'Sales', NULL, 'seller', 'company', 'pending', NULL, NULL, NULL, NULL, NULL, '\"{\\\"npwp\\\":\\\"documents\\\\/8\\\\/npwp_8_1779306056.png\\\",\\\"akta\\\":\\\"documents\\\\/8\\\\/akta_8_1779306056.png\\\",\\\"nib\\\":\\\"documents\\\\/8\\\\/nib_8_1779306056.jpg\\\",\\\"gold_standard\\\":\\\"documents\\\\/8\\\\/gold_standard_8_1779306056.png\\\"}\"', NULL, NULL, '2026-05-20 12:40:56', '2026-05-20 12:40:56', NULL),
(9, 'PIC', 'buyer@gmail.com', '$2y$12$cE94XV0qOWBtq2Zh467nz.hEAYe2vjOGRVH4PerQPGOlFDkMFdtku', '0812345678', 'Jkt', 'PT. Buyer', 'agriculture', 'PIC', NULL, 'buyer', 'company', 'pending', NULL, NULL, NULL, NULL, NULL, '{\"nib\": \"documents/9/nib_9_1779621722.png\", \"akta\": \"documents/9/akta_9_1779621722.png\", \"npwp\": \"documents/9/npwp_9_1779621721.png\"}', NULL, NULL, '2026-05-24 04:22:01', '2026-05-24 04:22:03', NULL),
(10, 'elvira', 'elvira@gmail.com', '$2y$12$yJoMANEJ55npBMiHf0bDmuFmSg3wuT6/jD6Rc85rcbl.vwyiXB9x6', '+62 813727456', 'Jakarta', NULL, NULL, NULL, 'PNS', 'seller', 'personal', 'pending', NULL, NULL, NULL, NULL, NULL, '{\"vcs\": \"documents/10/vcs_10_1780480635.pdf\", \"npwp\": \"documents/10/npwp_10_1780480634.pdf\", \"gold_standard\": \"documents/10/gold_standard_10_1780480635.pdf\"}', NULL, NULL, '2026-06-03 02:57:14', '2026-06-03 02:57:15', NULL),
(11, 'elviraa2', 'elviraa@gmail.com', '$2y$12$giFAtbMWHPi1jpRoICtIcuBvbmFwoIRKd4rT46qwxm/EPzR533yTS', '+62 813727456', 'Jakarta', NULL, NULL, NULL, 'CEO', 'buyer', 'personal', 'pending', NULL, NULL, NULL, NULL, NULL, '{\"npwp\": \"documents/11/npwp_11_1780480876.pdf\"}', NULL, NULL, '2026-06-03 03:01:16', '2026-06-03 03:01:16', NULL),
(12, 'EL', 'elbuyer@gmail.com', '$2y$12$.EA.JHZtfrabUuo3YmCywu38gYkkGzm7eO1mP5XtoZBbOG9iltCcG', '67343456576', 'Depok', NULL, NULL, NULL, 'PNS', 'buyer', 'personal', 'pending', NULL, NULL, NULL, NULL, NULL, '{\"npwp\": \"documents/12/npwp_12_1780501022.pdf\"}', NULL, NULL, '2026-06-03 08:37:02', '2026-06-03 08:37:02', NULL),
(13, 'Raihan Gusri Hidayat', 'raihangusriihidayat@gmail.com', '$2y$12$ZYGF/rcdMo5AOd.36J7e0u/55/0sNxHbVfSM4/j459RYAfI6Yuwpm', '082243567890', 'Jakarta', NULL, NULL, NULL, 'Mahasiswa', 'buyer', 'personal', 'verified', 16, '2026-06-21 08:38:48', NULL, NULL, NULL, '{\"npwp\":\"documents\\/13\\/npwp_13_1782040175.jpg\"}', 'profile_photos/photo_6a37c66ee8d195.19827505.jpg', NULL, '2026-06-21 04:09:35', '2026-06-21 08:38:48', NULL),
(14, 'Raihan', 'jadigasih@gmail.com', '$2y$12$U/tjoX0OfOIHg6W7k8iqV.dnj8pYt.ugS/AcMm78/ZiimpkmGgqYO', '+6282281345689', 'Jakarta', 'PT Jadi Ga Sih', 'energy', 'HRD', NULL, 'buyer', 'company', 'pending', NULL, NULL, NULL, NULL, NULL, '{\"npwp\":\"documents\\/14\\/npwp_14_1782040935.jpg\",\"akta\":\"documents\\/14\\/akta_14_1782040935.jpg\",\"nib\":\"documents\\/14\\/nib_14_1782040935.jpg\"}', 'profile_photos/photo_6a37c967368ed0.70023141.jpg', NULL, '2026-06-21 04:22:15', '2026-06-21 04:22:15', NULL),
(15, 'Super Admin CAMAR', 'superadmin@camar.id', '$2y$12$KN0tMei0vtTARlDhzXjGP.28AKtMOEusuSV/YCeoCBP0FO.mXAbzy', NULL, NULL, NULL, NULL, NULL, NULL, 'super_admin', 'personal', 'verified', NULL, '2026-06-21 08:25:49', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-21 08:25:49', '2026-06-21 08:25:49', NULL),
(16, 'Admin', 'admin@gmail.com', '$2y$12$lMFd89gKMPs.z30/hoNG6uEAujtK.RhgFhVN7S1uQ8hrX69uUSsay', NULL, NULL, NULL, NULL, NULL, NULL, 'admin', 'personal', 'verified', 15, '2026-06-21 08:35:33', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-21 08:35:33', '2026-06-21 08:35:33', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_activity_logs_admin_id_foreign` (`admin_id`),
  ADD KEY `admin_activity_logs_target_type_target_id_index` (`target_type`,`target_id`),
  ADD KEY `admin_activity_logs_action_created_at_index` (`action`,`created_at`);

--
-- Indexes for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_login_logs_admin_id_logged_in_at_index` (`admin_id`,`logged_in_at`),
  ADD KEY `admin_login_logs_session_id_index` (`session_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_user_id_project_id_unique` (`user_id`,`project_id`),
  ADD KEY `cart_items_project_id_foreign` (`project_id`);

--
-- Indexes for table `document_verifications`
--
ALTER TABLE `document_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_verifications_user_id_document_type_unique` (`user_id`,`document_type`),
  ADD KEY `document_verifications_reviewed_by_foreign` (`reviewed_by`);

--
-- Indexes for table `emission_calculations`
--
ALTER TABLE `emission_calculations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emission_calculations_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_project_id_foreign` (`project_id`),
  ADD KEY `orders_status_updated_by_foreign` (`status_updated_by`),
  ADD KEY `orders_status_index` (`status`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projects_seller_id_foreign` (`seller_id`),
  ADD KEY `projects_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `projects_verification_status_index` (`verification_status`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_transaction_code_unique` (`transaction_code`),
  ADD KEY `transactions_buyer_id_foreign` (`buyer_id`),
  ADD KEY `transactions_project_id_foreign` (`project_id`),
  ADD KEY `transactions_emission_calculation_id_foreign` (`emission_calculation_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_verified_by_foreign` (`verified_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_verifications`
--
ALTER TABLE `document_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `emission_calculations`
--
ALTER TABLE `emission_calculations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD CONSTRAINT `admin_activity_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD CONSTRAINT `admin_login_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_verifications`
--
ALTER TABLE `document_verifications`
  ADD CONSTRAINT `document_verifications_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `document_verifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `emission_calculations`
--
ALTER TABLE `emission_calculations`
  ADD CONSTRAINT `emission_calculations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_status_updated_by_foreign` FOREIGN KEY (`status_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_emission_calculation_id_foreign` FOREIGN KEY (`emission_calculation_id`) REFERENCES `emission_calculations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
