-- Pembaruan Portal Informasi Dukcapil Kabupaten Gorontalo
-- Target: MySQL 8.0+ / MySQL 8.4
-- Jalankan dari tab Import phpMyAdmin. Database dukc_kabGTLO harus sudah tersedia.
-- Skrip ini dapat dijalankan ulang dan tidak memuat akun atau kata sandi.

SET NAMES utf8mb4;
SET time_zone = '+08:00';

USE `dukc_kabGTLO`;
SET @application_database := 'dukc_kabGTLO';

-- 1. Hierarki wilayah statistik penduduk.
SET @ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @application_database
          AND TABLE_NAME = 'population_statistics'
          AND COLUMN_NAME = 'area_code'
    ),
    'SELECT 1',
    'ALTER TABLE `population_statistics` ADD COLUMN `area_code` VARCHAR(32) NULL AFTER `area`'
);
PREPARE update_statement FROM @ddl;
EXECUTE update_statement;
DEALLOCATE PREPARE update_statement;

SET @ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @application_database
          AND TABLE_NAME = 'population_statistics'
          AND COLUMN_NAME = 'area_level'
    ),
    'SELECT 1',
    'ALTER TABLE `population_statistics` ADD COLUMN `area_level` VARCHAR(30) NULL AFTER `area_code`'
);
PREPARE update_statement FROM @ddl;
EXECUTE update_statement;
DEALLOCATE PREPARE update_statement;

SET @ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @application_database
          AND TABLE_NAME = 'population_statistics'
          AND COLUMN_NAME = 'parent_area_code'
    ),
    'SELECT 1',
    'ALTER TABLE `population_statistics` ADD COLUMN `parent_area_code` VARCHAR(32) NULL AFTER `area_level`'
);
PREPARE update_statement FROM @ddl;
EXECUTE update_statement;
DEALLOCATE PREPARE update_statement;

UPDATE `population_statistics`
SET
    `area_code` = CONCAT('legacy-', `id`),
    `area_level` = 'legacy'
WHERE `area_code` IS NULL;

SET @ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = @application_database
          AND TABLE_NAME = 'population_statistics'
          AND INDEX_NAME = 'population_statistic_unique'
    ),
    'ALTER TABLE `population_statistics` DROP INDEX `population_statistic_unique`',
    'SELECT 1'
);
PREPARE update_statement FROM @ddl;
EXECUTE update_statement;
DEALLOCATE PREPARE update_statement;

SET @ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = @application_database
          AND TABLE_NAME = 'population_statistics'
          AND INDEX_NAME = 'population_statistic_area_unique'
    ),
    'SELECT 1',
    'ALTER TABLE `population_statistics` ADD UNIQUE INDEX `population_statistic_area_unique` (`label`, `category`, `period`, `area_code`)'
);
PREPARE update_statement FROM @ddl;
EXECUTE update_statement;
DEALLOCATE PREPARE update_statement;

SET @ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = @application_database
          AND TABLE_NAME = 'population_statistics'
          AND INDEX_NAME = 'population_statistics_browse_index'
    ),
    'SELECT 1',
    'ALTER TABLE `population_statistics` ADD INDEX `population_statistics_browse_index` (`period`, `area_level`, `parent_area_code`)'
);
PREPARE update_statement FROM @ddl;
EXECUTE update_statement;
DEALLOCATE PREPARE update_statement;

-- 2. Status ketersediaan pencetakan dokumen.
CREATE TABLE IF NOT EXISTS `printing_statuses` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `location` VARCHAR(255) NOT NULL,
    `document` VARCHAR(255) NOT NULL,
    `sort_order` SMALLINT UNSIGNED NOT NULL,
    `is_available` TINYINT(1) NOT NULL DEFAULT 0,
    `note` VARCHAR(500) NULL DEFAULT NULL,
    `source` VARCHAR(255) NULL DEFAULT NULL,
    `checked_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `printing_statuses_location_document_unique` (`location`, `document`),
    KEY `printing_statuses_updated_by_foreign` (`updated_by`),
    CONSTRAINT `printing_statuses_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `printing_statuses`
    (`location`, `document`, `sort_order`, `is_available`, `created_at`, `updated_at`)
VALUES
    ('Kantor Dukcapil Limboto', 'ktp_el', 10, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('Kantor Dukcapil Limboto', 'kia', 20, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- 3. Profil pimpinan yang dapat diperbarui super admin.
CREATE TABLE IF NOT EXISTS `leadership_profiles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role` VARCHAR(255) NOT NULL,
    `name` VARCHAR(160) NOT NULL,
    `photo_path` VARCHAR(255) NOT NULL,
    `sort_order` TINYINT UNSIGNED NOT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `leadership_profiles_role_unique` (`role`),
    KEY `leadership_profiles_is_published_sort_order_index` (`is_published`, `sort_order`),
    KEY `leadership_profiles_updated_by_foreign` (`updated_by`),
    CONSTRAINT `leadership_profiles_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Dataset dan observasi statistik tematik.
CREATE TABLE IF NOT EXISTS `thematic_statistic_datasets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(80) NOT NULL,
    `period` VARCHAR(80) NOT NULL,
    `source` VARCHAR(180) NOT NULL,
    `source_url` TEXT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `file_hash` CHAR(64) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `row_count` INT UNSIGNED NOT NULL,
    `observation_count` INT UNSIGNED NOT NULL,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `imported_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `thematic_dataset_file_unique` (`type`, `period`, `file_hash`),
    KEY `thematic_dataset_public_index` (`period`, `type`, `status`, `published_at`),
    KEY `thematic_statistic_datasets_imported_by_foreign` (`imported_by`),
    CONSTRAINT `thematic_statistic_datasets_imported_by_foreign`
        FOREIGN KEY (`imported_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `thematic_statistic_observations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `dataset_id` BIGINT UNSIGNED NOT NULL,
    `area_code` VARCHAR(32) NOT NULL,
    `area` VARCHAR(120) NOT NULL,
    `area_level` VARCHAR(30) NOT NULL,
    `parent_area_code` VARCHAR(32) NULL DEFAULT NULL,
    `dimension_one` VARCHAR(40) NOT NULL DEFAULT '',
    `dimension_one_value` VARCHAR(120) NOT NULL DEFAULT '',
    `dimension_two` VARCHAR(40) NOT NULL DEFAULT '',
    `dimension_two_value` VARCHAR(120) NOT NULL DEFAULT '',
    `metric` VARCHAR(60) NOT NULL,
    `sex` VARCHAR(10) NOT NULL,
    `snapshot` VARCHAR(20) NOT NULL DEFAULT 'dynamic',
    `value` DECIMAL(18, 4) NOT NULL,
    `unit` VARCHAR(30) NOT NULL,
    `observation_key` CHAR(40) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `thematic_observation_unique` (`dataset_id`, `observation_key`),
    KEY `thematic_observation_area_index` (`dataset_id`, `area_code`, `metric`),
    KEY `thematic_observation_dimension_index` (`dataset_id`, `dimension_one`, `dimension_one_value`),
    CONSTRAINT `thematic_statistic_observations_dataset_id_foreign`
        FOREIGN KEY (`dataset_id`) REFERENCES `dukc_kabGTLO`.`thematic_statistic_datasets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Kolom autentikasi multi-faktor (MFA) Laravel Fortify.
SET @ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @application_database
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'two_factor_secret'
    ),
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `two_factor_secret` TEXT NULL AFTER `password`'
);
PREPARE update_statement FROM @ddl;
EXECUTE update_statement;
DEALLOCATE PREPARE update_statement;

SET @ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @application_database
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'two_factor_recovery_codes'
    ),
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `two_factor_recovery_codes` TEXT NULL AFTER `two_factor_secret`'
);
PREPARE update_statement FROM @ddl;
EXECUTE update_statement;
DEALLOCATE PREPARE update_statement;

SET @ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @application_database
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'two_factor_confirmed_at'
    ),
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `two_factor_confirmed_at` TIMESTAMP NULL DEFAULT NULL AFTER `two_factor_recovery_codes`'
);
PREPARE update_statement FROM @ddl;
EXECUTE update_statement;
DEALLOCATE PREPARE update_statement;

-- 6. Bangun ulang foreign key agar instalasi lama tidak lagi merujuk web_dukcapil.
-- Pemeriksaan data yatim sebaiknya dilakukan sebelum menjalankan bagian ini pada
-- database yang sudah berisi data produksi.
ALTER TABLE `homepage_settings`
    DROP FOREIGN KEY `homepage_settings_updated_by_foreign`;
ALTER TABLE `hero_slides`
    DROP FOREIGN KEY `hero_slides_updated_by_foreign`;
ALTER TABLE `news_articles`
    DROP FOREIGN KEY `news_articles_author_id_foreign`;
ALTER TABLE `population_statistics`
    DROP FOREIGN KEY `population_statistics_updated_by_foreign`;
ALTER TABLE `supply_statuses`
    DROP FOREIGN KEY `supply_statuses_updated_by_foreign`;
ALTER TABLE `public_documents`
    DROP FOREIGN KEY `public_documents_updated_by_foreign`;
ALTER TABLE `employees`
    DROP FOREIGN KEY `employees_updated_by_foreign`;
ALTER TABLE `audit_logs`
    DROP FOREIGN KEY `audit_logs_user_id_foreign`;
ALTER TABLE `printing_statuses`
    DROP FOREIGN KEY `printing_statuses_updated_by_foreign`;
ALTER TABLE `leadership_profiles`
    DROP FOREIGN KEY `leadership_profiles_updated_by_foreign`;
ALTER TABLE `thematic_statistic_datasets`
    DROP FOREIGN KEY `thematic_statistic_datasets_imported_by_foreign`;
ALTER TABLE `thematic_statistic_observations`
    DROP FOREIGN KEY `thematic_statistic_observations_dataset_id_foreign`;

ALTER TABLE `homepage_settings`
    ADD CONSTRAINT `homepage_settings_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `hero_slides`
    ADD CONSTRAINT `hero_slides_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `news_articles`
    ADD CONSTRAINT `news_articles_author_id_foreign`
        FOREIGN KEY (`author_id`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE RESTRICT;
ALTER TABLE `population_statistics`
    ADD CONSTRAINT `population_statistics_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `supply_statuses`
    ADD CONSTRAINT `supply_statuses_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `public_documents`
    ADD CONSTRAINT `public_documents_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `employees`
    ADD CONSTRAINT `employees_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `audit_logs`
    ADD CONSTRAINT `audit_logs_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `printing_statuses`
    ADD CONSTRAINT `printing_statuses_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `leadership_profiles`
    ADD CONSTRAINT `leadership_profiles_updated_by_foreign`
        FOREIGN KEY (`updated_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `thematic_statistic_datasets`
    ADD CONSTRAINT `thematic_statistic_datasets_imported_by_foreign`
        FOREIGN KEY (`imported_by`) REFERENCES `dukc_kabGTLO`.`users` (`id`) ON DELETE SET NULL;
ALTER TABLE `thematic_statistic_observations`
    ADD CONSTRAINT `thematic_statistic_observations_dataset_id_foreign`
        FOREIGN KEY (`dataset_id`) REFERENCES `dukc_kabGTLO`.`thematic_statistic_datasets` (`id`) ON DELETE CASCADE;

-- Catat migration hanya setelah seluruh perubahan skema berhasil.
SET @migration_batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_29_140055_add_area_hierarchy_to_population_statistics_table', @migration_batch
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_08_29_140055_add_area_hierarchy_to_population_statistics_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_30_001051_create_printing_statuses_table', @migration_batch
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_08_30_001051_create_printing_statuses_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_30_183934_create_leadership_profiles_table', @migration_batch
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_08_30_183934_create_leadership_profiles_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_31_000001_create_thematic_statistic_datasets_table', @migration_batch
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_08_31_000001_create_thematic_statistic_datasets_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_31_000002_create_thematic_statistic_observations_table', @migration_batch
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_08_31_000002_create_thematic_statistic_observations_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_31_221738_add_two_factor_columns_to_users_table', @migration_batch
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_08_31_221738_add_two_factor_columns_to_users_table'
);

SELECT
    'Pembaruan database selesai.' AS `status`,
    @application_database AS `database`;
