-- Assign existing teachers as Digital Diary Coordinator / Moderator (per class + section + session).
-- Also auto-created by Digital_diary_model::ensureRoleAllocationTable().

CREATE TABLE IF NOT EXISTS `digital_diary_role_allocation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_type` varchar(20) NOT NULL COMMENT 'coordinator|moderator',
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dd_ra_unique` (`role_type`,`class_id`,`section_id`,`session_id`,`branch_id`),
  KEY `dd_ra_teacher` (`teacher_id`),
  KEY `dd_ra_role` (`role_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
