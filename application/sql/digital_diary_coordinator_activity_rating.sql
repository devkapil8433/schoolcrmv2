-- Coordinator rates students per teaching activity type (1–10 + comment).
-- Auto-created by Digital_diary_model::ensureCoordinatorActivityRatingTable().

CREATE TABLE IF NOT EXISTS `digital_diary_coordinator_activity_rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `student_id` int(11) NOT NULL,
  `teaching_activity_id` int(11) NOT NULL,
  `score` tinyint(4) NOT NULL DEFAULT 0,
  `comment` varchar(500) DEFAULT NULL,
  `rated_by` int(11) DEFAULT NULL,
  `rated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dd_car_unique` (`branch_id`,`session_id`,`work_date`,`class_id`,`section_id`,`student_id`,`teaching_activity_id`),
  KEY `dd_car_lookup` (`work_date`,`class_id`,`section_id`),
  KEY `dd_car_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
