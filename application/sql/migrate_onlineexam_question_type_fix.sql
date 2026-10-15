-- One-shot migration: fixed 7 question types per branch + questions.custom_question_type
-- Safe to run multiple times (ignores duplicate column errors when using mysql --force)

SET NAMES utf8;

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

ALTER TABLE `onlineexam_question_type` ADD COLUMN `question_type_id` int(11) NOT NULL DEFAULT 0 AFTER `branch_id`;

ALTER TABLE `questions` ADD COLUMN `custom_question_type` varchar(255) DEFAULT NULL AFTER `type`;

-- Deactivate legacy custom rows (no fixed type id)
UPDATE `onlineexam_question_type` SET `is_active` = 0, `updated_at` = NOW() WHERE `question_type_id` = 0;
