-- Today's Work (Digital Diary) — daily class/subject work per lesson
-- Run after digital_diary.sql (digital_diary_lesson exists).

CREATE TABLE IF NOT EXISTS `digital_diary_today_work` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL COMMENT 'Staff who saved the row',
  `work_date` date NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL COMMENT 'FK digital_diary_lesson',
  `class_work_items` varchar(500) DEFAULT NULL COMMENT 'comma-separated keys: reading,explanation,...',
  `home_work_items` varchar(500) DEFAULT NULL,
  `home_work_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dd_tw_unique` (`branch_id`,`session_id`,`work_date`,`class_id`,`section_id`,`subject_id`),
  KEY `dd_tw_date` (`work_date`),
  KEY `dd_tw_lesson` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `digital_diary_today_work_day` (
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `is_final` tinyint(1) NOT NULL DEFAULT 0,
  `staff_id` int(11) DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `review_deadline` date DEFAULT NULL COMMENT 'Coordinator review due (7 working days after final submit)',
  PRIMARY KEY (`branch_id`,`session_id`,`work_date`,`class_id`,`section_id`),
  KEY `dd_twd_date` (`work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
