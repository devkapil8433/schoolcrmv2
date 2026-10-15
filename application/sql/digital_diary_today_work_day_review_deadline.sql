-- Coordinator review window: 7 working days after class-teacher Final Submit.
-- Safe to re-run. Also auto-migrated + backfilled by
-- Digital_diary_model::ensureTodayWorkDayReviewDeadlineColumn().

SET @dbname = DATABASE();

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = 'digital_diary_today_work_day'
      AND COLUMN_NAME = 'review_deadline'
  ) > 0,
  'SELECT 1 AS review_deadline_present',
  'ALTER TABLE `digital_diary_today_work_day` ADD COLUMN `review_deadline` date DEFAULT NULL COMMENT ''Coordinator review due (7 working days after final submit)'' AFTER `finalized_at`'
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Backfill existing final days that have no deadline yet.
-- Uses calendar approximation (+9 days ≈ 7 weekdays); PHP auto-migrate
-- recalculates exact Mon–Fri working days on next page load.
UPDATE `digital_diary_today_work_day`
SET `review_deadline` = DATE(DATE_ADD(`finalized_at`, INTERVAL 9 DAY))
WHERE `is_final` = 1
  AND `finalized_at` IS NOT NULL
  AND (`review_deadline` IS NULL OR `review_deadline` = '0000-00-00');
