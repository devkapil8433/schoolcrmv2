-- Today's Work learning flow: Question Bank assignments, student submissions, coordinator alerts
-- Run after digital_diary_today_work.sql

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
