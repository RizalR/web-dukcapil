-- Portal Informasi Dukcapil Kabupaten Gorontalo
-- Target: database dukc_kabGTLO pada MySQL 8.0+ / MySQL 8.4 Laragon
-- Isi: struktur tabel, foreign key, indeks, dan riwayat migration.
-- Tidak memuat akun, kata sandi, atau data konten.

SET NAMES utf8mb4;
SET time_zone = '+08:00';

USE `dukc_kabGTLO`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(255) NOT NULL DEFAULT 'news_editor',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` BIGINT NOT NULL,
    PRIMARY KEY (`key`),
    KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key` VARCHAR(255) NOT NULL,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` BIGINT NOT NULL,
    PRIMARY KEY (`key`),
    KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` SMALLINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL,
    `cancelled_at` INT NULL DEFAULT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` VARCHAR(255) NOT NULL,
    `connection` VARCHAR(255) NOT NULL,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
    KEY `failed_jobs_connection_queue_failed_at_index` (`connection`, `queue`, `failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `homepage_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `background_image_path` VARCHAR(255) NULL DEFAULT NULL,
    `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `homepage_settings_updated_by_foreign` (`updated_by`),
    CONSTRAINT `homepage_settings_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(160) NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(320) NULL DEFAULT NULL,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `starts_at` TIMESTAMP NULL DEFAULT NULL,
    `ends_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `hero_slides_is_active_sort_order_index` (`is_active`, `sort_order`),
    KEY `hero_slides_updated_by_foreign` (`updated_by`),
    CONSTRAINT `hero_slides_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `news_articles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `author_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `excerpt` VARCHAR(320) NOT NULL,
    `body` TEXT NOT NULL,
    `status` VARCHAR(255) NOT NULL DEFAULT 'draft',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `source_url` VARCHAR(255) NULL DEFAULT NULL,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `news_articles_slug_unique` (`slug`),
    KEY `news_articles_author_id_foreign` (`author_id`),
    KEY `news_articles_status_published_at_index` (`status`, `published_at`),
    CONSTRAINT `news_articles_author_id_foreign`
        FOREIGN KEY (`author_id`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `population_statistics` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(120) NOT NULL,
    `category` VARCHAR(80) NOT NULL,
    `value` BIGINT UNSIGNED NOT NULL,
    `unit` VARCHAR(30) NOT NULL DEFAULT 'jiwa',
    `period` VARCHAR(80) NOT NULL,
    `area` VARCHAR(120) NOT NULL DEFAULT 'Kabupaten Gorontalo',
    `source` VARCHAR(180) NOT NULL,
    `source_url` VARCHAR(255) NULL DEFAULT NULL,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `population_statistic_unique` (`label`, `category`, `period`, `area`),
    KEY `population_statistics_period_category_index` (`period`, `category`),
    KEY `population_statistics_published_at_index` (`published_at`),
    KEY `population_statistics_updated_by_foreign` (`updated_by`),
    CONSTRAINT `population_statistics_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supply_statuses` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `location` VARCHAR(255) NOT NULL,
    `item` VARCHAR(255) NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `note` VARCHAR(500) NULL DEFAULT NULL,
    `source` VARCHAR(255) NULL DEFAULT NULL,
    `checked_at` TIMESTAMP NOT NULL,
    `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `supply_statuses_location_item_unique` (`location`, `item`),
    KEY `supply_statuses_updated_by_foreign` (`updated_by`),
    CONSTRAINT `supply_statuses_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `public_documents` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `category` VARCHAR(255) NOT NULL,
    `year` SMALLINT UNSIGNED NOT NULL,
    `description` VARCHAR(500) NULL DEFAULT NULL,
    `url` VARCHAR(255) NOT NULL,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `public_documents_slug_unique` (`slug`),
    KEY `public_documents_category_year_index` (`category`, `year`),
    KEY `public_documents_updated_by_foreign` (`updated_by`),
    CONSTRAINT `public_documents_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employees` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `position` VARCHAR(255) NOT NULL,
    `unit` VARCHAR(255) NULL DEFAULT NULL,
    `photo_url` VARCHAR(255) NULL DEFAULT NULL,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employees_is_published_sort_order_index` (`is_published`, `sort_order`),
    KEY `employees_updated_by_foreign` (`updated_by`),
    CONSTRAINT `employees_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `external_links` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(255) NOT NULL,
    `description` VARCHAR(500) NULL DEFAULT NULL,
    `url` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `last_status_code` SMALLINT UNSIGNED NULL DEFAULT NULL,
    `last_check_ok` TINYINT(1) NULL DEFAULT NULL,
    `last_checked_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `external_links_category_is_active_sort_order_index` (`category`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `action` VARCHAR(255) NOT NULL,
    `auditable_type` VARCHAR(255) NULL DEFAULT NULL,
    `auditable_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `description` VARCHAR(500) NOT NULL,
    `properties` JSON NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` VARCHAR(500) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `audit_logs_user_id_foreign` (`user_id`),
    KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`, `auditable_id`),
    KEY `audit_logs_action_created_at_index` (`action`, `created_at`),
    CONSTRAINT `audit_logs_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2026_08_28_151147_create_news_articles_table', 1),
    ('2026_08_28_151148_create_population_statistics_table', 1),
    ('2026_08_28_151149_create_supply_statuses_table', 1),
    ('2026_08_28_151150_create_public_documents_table', 1),
    ('2026_08_28_151151_create_employees_table', 1),
    ('2026_08_28_151152_create_external_links_table', 1),
    ('2026_08_28_151153_create_audit_logs_table', 1),
    ('2026_08_28_151323_add_role_and_active_to_users_table', 1),
    ('2026_08_29_014513_create_homepage_settings_table', 1),
    ('2026_08_29_014514_create_hero_slides_table', 1);
