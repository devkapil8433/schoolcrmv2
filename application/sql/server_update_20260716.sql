-- =============================================================================
-- EdSchool server update SQL (2026-07-16)
-- Run once in phpMyAdmin / MySQL on the LIVE database.
-- Safe to re-run: uses IF NOT EXISTS / NOT EXISTS checks where possible.
-- =============================================================================

SET NAMES utf8;

-- -----------------------------------------------------------------------------
-- 1) Online Exam: question type master + custom type column
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `onlineexam_question_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `question_type_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `marks` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `onlineexam_question_type_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Add question_type_id if missing (ignore error if already exists)
-- ALTER TABLE `onlineexam_question_type` ADD COLUMN `question_type_id` int(11) NOT NULL DEFAULT 0 AFTER `branch_id`;

-- Add custom_question_type on questions if missing (ignore error if already exists)
-- ALTER TABLE `questions` ADD COLUMN `custom_question_type` varchar(255) DEFAULT NULL AFTER `type`;

-- Prefer procedural-safe adds via information_schema:
SET @db := DATABASE();

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'onlineexam_question_type' AND COLUMN_NAME = 'question_type_id') = 0,
    'ALTER TABLE `onlineexam_question_type` ADD COLUMN `question_type_id` int(11) NOT NULL DEFAULT 0 AFTER `branch_id`',
    'SELECT 1'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'questions' AND COLUMN_NAME = 'custom_question_type') = 0,
    'ALTER TABLE `questions` ADD COLUMN `custom_question_type` varchar(255) DEFAULT NULL AFTER `type`',
    'SELECT 1'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Deactivate legacy custom rows + old Descriptive (type 4)
UPDATE `onlineexam_question_type`
SET `is_active` = 0, `updated_at` = NOW()
WHERE `question_type_id` IN (0, 4);

-- Seed / refresh descriptive types 8–11 for every branch that already has question types
-- (Also covers branches that only have older types 1–7)

INSERT INTO `onlineexam_question_type`
  (`branch_id`, `question_type_id`, `name`, `description`, `marks`, `is_active`, `created_at`, `updated_at`)
SELECT b.`branch_id`, 8, 'Fill in the blanks', 'Fill in the blank(s) with a short text answer.', 1, 1, NOW(), NOW()
FROM (SELECT DISTINCT `branch_id` FROM `onlineexam_question_type`) b
WHERE NOT EXISTS (
  SELECT 1 FROM `onlineexam_question_type` t
  WHERE t.`branch_id` = b.`branch_id` AND t.`question_type_id` = 8
);

INSERT INTO `onlineexam_question_type`
  (`branch_id`, `question_type_id`, `name`, `description`, `marks`, `is_active`, `created_at`, `updated_at`)
SELECT b.`branch_id`, 9, 'Very short', 'Very short descriptive answer.', 2, 1, NOW(), NOW()
FROM (SELECT DISTINCT `branch_id` FROM `onlineexam_question_type`) b
WHERE NOT EXISTS (
  SELECT 1 FROM `onlineexam_question_type` t
  WHERE t.`branch_id` = b.`branch_id` AND t.`question_type_id` = 9
);

INSERT INTO `onlineexam_question_type`
  (`branch_id`, `question_type_id`, `name`, `description`, `marks`, `is_active`, `created_at`, `updated_at`)
SELECT b.`branch_id`, 10, 'Short', 'Short descriptive answer.', 3, 1, NOW(), NOW()
FROM (SELECT DISTINCT `branch_id` FROM `onlineexam_question_type`) b
WHERE NOT EXISTS (
  SELECT 1 FROM `onlineexam_question_type` t
  WHERE t.`branch_id` = b.`branch_id` AND t.`question_type_id` = 10
);

INSERT INTO `onlineexam_question_type`
  (`branch_id`, `question_type_id`, `name`, `description`, `marks`, `is_active`, `created_at`, `updated_at`)
SELECT b.`branch_id`, 11, 'Long', 'Long descriptive answer.', 5, 1, NOW(), NOW()
FROM (SELECT DISTINCT `branch_id` FROM `onlineexam_question_type`) b
WHERE NOT EXISTS (
  SELECT 1 FROM `onlineexam_question_type` t
  WHERE t.`branch_id` = b.`branch_id` AND t.`question_type_id` = 11
);

-- Keep names in sync if rows already exist
UPDATE `onlineexam_question_type` SET `name` = 'Fill in the blanks', `is_active` = 1, `updated_at` = NOW() WHERE `question_type_id` = 8;
UPDATE `onlineexam_question_type` SET `name` = 'Very short', `is_active` = 1, `updated_at` = NOW() WHERE `question_type_id` = 9;
UPDATE `onlineexam_question_type` SET `name` = 'Short', `is_active` = 1, `updated_at` = NOW() WHERE `question_type_id` = 10;
UPDATE `onlineexam_question_type` SET `name` = 'Long', `is_active` = 1, `updated_at` = NOW() WHERE `question_type_id` = 11;

-- If a branch has NO question-type rows yet, seed from branch table (optional)
INSERT INTO `onlineexam_question_type`
  (`branch_id`, `question_type_id`, `name`, `description`, `marks`, `is_active`, `created_at`, `updated_at`)
SELECT br.`id`, v.`question_type_id`, v.`name`, v.`description`, v.`marks`, 1, NOW(), NOW()
FROM `branch` br
CROSS JOIN (
  SELECT 1 AS question_type_id, 'Single Choice' AS name, 'Single correct answer from multiple options.' AS description, 1 AS marks
  UNION ALL SELECT 2, 'Multiple Choice', 'Multiple correct answers from multiple options.', 1
  UNION ALL SELECT 3, 'True/False', 'True or False response.', 1
  UNION ALL SELECT 5, 'Match', 'Match items from left column to right column.', 1
  UNION ALL SELECT 6, 'Reading', 'Reading comprehension style answer.', 1
  UNION ALL SELECT 7, 'Writing', 'Writing/long-form answer.', 1
  UNION ALL SELECT 8, 'Fill in the blanks', 'Fill in the blank(s) with a short text answer.', 1
  UNION ALL SELECT 9, 'Very short', 'Very short descriptive answer.', 2
  UNION ALL SELECT 10, 'Short', 'Short descriptive answer.', 3
  UNION ALL SELECT 11, 'Long', 'Long descriptive answer.', 5
) v
WHERE NOT EXISTS (
  SELECT 1 FROM `onlineexam_question_type` t
  WHERE t.`branch_id` = br.`id` AND t.`question_type_id` = v.`question_type_id`
);

-- -----------------------------------------------------------------------------
-- 2) Digital Diary: Today's Work learning flow tables
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `digital_diary_today_work_assignment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `today_work_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `is_test` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dd_twa_unique` (`today_work_id`,`question_id`),
  KEY `dd_twa_tw` (`today_work_id`),
  KEY `dd_twa_q` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `digital_diary_student_submission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `today_work_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submit_type` varchar(20) NOT NULL COMMENT 'homework|learning',
  `photo` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'submitted' COMMENT 'submitted|verified|rejected',
  `marks` decimal(8,2) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dd_ss_unique` (`today_work_id`,`student_id`,`submit_type`),
  KEY `dd_ss_tw` (`today_work_id`),
  KEY `dd_ss_student` (`student_id`),
  KEY `dd_ss_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `digital_diary_coordinator_alert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `class_id` int(11) NOT NULL DEFAULT 0,
  `section_id` int(11) NOT NULL DEFAULT 0,
  `event_type` varchar(50) NOT NULL,
  `message` varchar(500) NOT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `dd_ca_branch` (`branch_id`),
  KEY `dd_ca_date` (`work_date`),
  KEY `dd_ca_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
