-- Tracks which syllabus lessons are marked completed by teacher (Today's Work confirmation)
-- One row per (session, branch, class, section, subject, lesson); updated when teacher confirms again

CREATE TABLE IF NOT EXISTS `digital_diary_lesson_completion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL COMMENT 'Teacher who confirmed',
  `work_date` date NOT NULL COMMENT 'Date of confirmation',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_scope_lesson` (`session_id`,`branch_id`,`class_id`,`section_id`,`subject_id`,`lesson_id`),
  KEY `idx_branch_class_section` (`branch_id`,`class_id`,`section_id`,`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
