-- Teacher log: which master teaching activity was done (Digital Diary)
-- Run after digital_diary_teaching_activity.sql exists.

CREATE TABLE IF NOT EXISTS `digital_diary_teacher_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL COMMENT 'FK staff who logged the activity',
  `teaching_activity_id` int(11) NOT NULL COMMENT 'FK digital_diary_teaching_activity',
  `activity_date` date NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `dd_tal_branch_session` (`branch_id`, `session_id`),
  KEY `dd_tal_staff` (`staff_id`),
  KEY `dd_tal_master` (`teaching_activity_id`),
  KEY `dd_tal_activity_date` (`activity_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
