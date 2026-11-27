-- Teaching Activity: store multiple notebook types (comma-separated IDs in TEXT)
-- Safe to run more than once: skips ADD if column already exists; upgrades VARCHAR to TEXT if needed.
-- Requires: digital_diary_teaching_activity table (and ideally digital_diary_notebook_type for FK semantics).

-- 1) Add column if missing
SET @dbname = DATABASE();
SET @tablename = 'digital_diary_teaching_activity';
SET @columnname = 'notebook_type_ids';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1 AS notebook_type_ids_column_already_present',
  'ALTER TABLE `digital_diary_teaching_activity` ADD COLUMN `notebook_type_ids` TEXT NULL COMMENT ''Comma-separated notebook type IDs'' AFTER `notebook_type_id`'
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) If column was added earlier as VARCHAR(500), widen to TEXT
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'digital_diary_teaching_activity'
      AND COLUMN_NAME = 'notebook_type_ids'
      AND DATA_TYPE = 'varchar'
  ) > 0,
  'ALTER TABLE `digital_diary_teaching_activity` MODIFY COLUMN `notebook_type_ids` TEXT NULL COMMENT ''Comma-separated notebook type IDs''',
  'SELECT 1 AS notebook_type_ids_already_text_or_missing'
));
PREPARE stmt2 FROM @preparedStatement;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- 3) Copy legacy single FK into CSV when multi column is still empty
UPDATE `digital_diary_teaching_activity`
SET `notebook_type_ids` = CAST(`notebook_type_id` AS CHAR)
WHERE `notebook_type_id` IS NOT NULL
  AND (`notebook_type_ids` IS NULL OR TRIM(`notebook_type_ids`) = '');
