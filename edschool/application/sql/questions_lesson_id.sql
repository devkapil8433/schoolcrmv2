-- Optional manual migration: link question bank rows to Digital Diary lessons (digital_diary_lesson.id).
-- The app also runs this via Onlineexam::ensureQuestionsLessonIdColumn() when saving questions.
-- Safe to run more than once: skips if lesson_id already exists (#1060 otherwise).

SET @db = DATABASE();
SET @sql = (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'questions' AND COLUMN_NAME = 'lesson_id'
    ),
    'SELECT ''column lesson_id already exists'' AS migration_status',
    'ALTER TABLE `questions` ADD COLUMN `lesson_id` int(11) DEFAULT NULL AFTER `subject_id`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
