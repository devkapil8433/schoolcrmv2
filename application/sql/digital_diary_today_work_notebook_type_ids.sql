-- Today's Work: multiple notebook types for class work and home work (comma-separated IDs, TEXT)
-- Prerequisite: digital_diary_today_work_multilesson_notebook.sql (columns cw_notebook_type_id, hw_notebook_type_id)

SET @dbname = DATABASE();

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'digital_diary_today_work' AND COLUMN_NAME = 'cw_notebook_type_ids'
  ) > 0,
  'SELECT 1 AS cw_notebook_type_ids_present',
  'ALTER TABLE `digital_diary_today_work` ADD COLUMN `cw_notebook_type_ids` TEXT NULL COMMENT ''Comma-separated notebook type IDs (class work)'' AFTER `hw_notebook_type_id`'
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'digital_diary_today_work' AND COLUMN_NAME = 'hw_notebook_type_ids'
  ) > 0,
  'SELECT 1 AS hw_notebook_type_ids_present',
  'ALTER TABLE `digital_diary_today_work` ADD COLUMN `hw_notebook_type_ids` TEXT NULL COMMENT ''Comma-separated notebook type IDs (home work)'' AFTER `cw_notebook_type_ids`'
));
PREPARE stmt2 FROM @preparedStatement;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

UPDATE `digital_diary_today_work`
SET `cw_notebook_type_ids` = CAST(`cw_notebook_type_id` AS CHAR)
WHERE `cw_notebook_type_id` IS NOT NULL
  AND (`cw_notebook_type_ids` IS NULL OR TRIM(`cw_notebook_type_ids`) = '');

UPDATE `digital_diary_today_work`
SET `hw_notebook_type_ids` = CAST(`hw_notebook_type_id` AS CHAR)
WHERE `hw_notebook_type_id` IS NOT NULL
  AND (`hw_notebook_type_ids` IS NULL OR TRIM(`hw_notebook_type_ids`) = '');
