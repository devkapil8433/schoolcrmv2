-- Assign existing teachers as Digital Diary Coordinator / Moderator
-- per teaching activity type + class + section + session.
-- One teacher can be assigned to multiple classes for the same activity.
-- Also auto-created by Digital_diary_model::ensureRoleAllocationTable().

CREATE TABLE IF NOT EXISTS `digital_diary_role_allocation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_type` varchar(20) NOT NULL COMMENT 'coordinator|moderator',
  `teaching_activity_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK digital_diary_teaching_activity; 0 = all activities',
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dd_ra_unique` (`role_type`,`teaching_activity_id`,`class_id`,`section_id`,`session_id`,`branch_id`),
  KEY `dd_ra_teacher` (`teacher_id`),
  KEY `dd_ra_role` (`role_type`),
  KEY `dd_ra_activity` (`teaching_activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
