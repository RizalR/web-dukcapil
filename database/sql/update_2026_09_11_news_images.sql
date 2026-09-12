-- Gambar sampul berita: MySQL 8.0+ / MySQL 8.4.
-- Pilih database aplikasi di phpMyAdmin sebelum mengimpor file ini.
-- Dapat dijalankan ulang. Data berita yang sudah ada tetap dipertahankan.
-- Tabel news_articles dan migrations harus sudah tersedia.

SET NAMES utf8mb4;
SET @news_image_database := DATABASE();

SET @news_image_ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @news_image_database
          AND TABLE_NAME = 'news_articles'
          AND COLUMN_NAME = 'image_path'
    ),
    'SELECT 1',
    'ALTER TABLE `news_articles` ADD COLUMN `image_path` VARCHAR(255) NULL'
);
PREPARE news_image_statement FROM @news_image_ddl;
EXECUTE news_image_statement;
DEALLOCATE PREPARE news_image_statement;

SET @news_image_ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @news_image_database
          AND TABLE_NAME = 'news_articles'
          AND COLUMN_NAME = 'image_alt'
    ),
    'SELECT 1',
    'ALTER TABLE `news_articles` ADD COLUMN `image_alt` VARCHAR(255) NULL'
);
PREPARE news_image_statement FROM @news_image_ddl;
EXECUTE news_image_statement;
DEALLOCATE PREPARE news_image_statement;

SET @news_image_ddl := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @news_image_database
          AND TABLE_NAME = 'news_articles'
          AND COLUMN_NAME = 'image_caption'
    ),
    'SELECT 1',
    'ALTER TABLE `news_articles` ADD COLUMN `image_caption` VARCHAR(320) NULL'
);
PREPARE news_image_statement FROM @news_image_ddl;
EXECUTE news_image_statement;
DEALLOCATE PREPARE news_image_statement;

-- Catat migrasi agar Artisan tidak menambahkan kolom yang sama kembali.
SET @news_image_batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_11_213228_add_cover_image_to_news_articles_table', @news_image_batch
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_09_11_213228_add_cover_image_to_news_articles_table'
)
AND (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @news_image_database
      AND TABLE_NAME = 'news_articles'
      AND COLUMN_NAME IN ('image_path', 'image_alt', 'image_caption')
      AND DATA_TYPE = 'varchar'
      AND IS_NULLABLE = 'YES'
      AND CHARACTER_MAXIMUM_LENGTH = CASE WHEN COLUMN_NAME = 'image_caption' THEN 320 ELSE 255 END
) = 3;
