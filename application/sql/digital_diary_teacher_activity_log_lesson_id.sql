-- Link teacher activity log rows to a syllabus lesson (Today's Work completion prompt)
-- Run after digital_diary_teacher_activity_log.sql

SET @dbname = DATABASE();
SET @tablename = 'digital_diary_teacher_activity_log';
SET @columnname = 'lesson_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `digital_diary_teacher_activity_log` ADD COLUMN `lesson_id` int(11) DEFAULT NULL COMMENT ''FK digital_diary_lesson'' AFTER `subject_id`, ADD KEY `dd_tal_lesson` (`lesson_id`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
