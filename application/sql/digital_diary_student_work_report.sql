-- Student Work Report (per student, per date, per class/section/subject)
-- Run after core tables (enroll, student, subject) exist.

CREATE TABLE IF NOT EXISTS `digital_diary_student_work_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL COMMENT 'Last saved by',
  `work_date` date NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `discipline_dress` tinyint(1) NOT NULL DEFAULT 0,
  `discipline_conduct` tinyint(1) NOT NULL DEFAULT 0,
  `fair_copy` tinyint(1) NOT NULL DEFAULT 0,
  `writing_work` tinyint(1) NOT NULL DEFAULT 0,
  `learning_work` tinyint(1) NOT NULL DEFAULT 0,
  `remarks` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dd_swr_unique` (`branch_id`,`session_id`,`work_date`,`class_id`,`section_id`,`subject_id`,`student_id`),
  KEY `dd_swr_lookup` (`work_date`,`class_id`,`section_id`,`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `digital_diary_timetable_period_conducted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `timetable_class_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `conducted_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dd_tpc_unique` (`branch_id`,`session_id`,`work_date`,`timetable_class_id`),
  KEY `dd_tpc_staff_date` (`staff_id`,`work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
