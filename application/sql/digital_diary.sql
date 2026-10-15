-- Digital Diary module table
-- Run this SQL in your database to create the digital_diary table

CREATE TABLE IF NOT EXISTS `digital_diary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL COMMENT 'NULL = class/section level entry',
  `session_id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=draft, 1=published',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `digital_diary_branch` (`branch_id`),
  KEY `digital_diary_class` (`class_id`),
  KEY `digital_diary_section` (`section_id`),
  KEY `digital_diary_student` (`student_id`),
  KEY `digital_diary_session` (`session_id`),
  KEY `digital_diary_entry_date` (`entry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Lesson table (Digital Diary - Lesson)
CREATE TABLE IF NOT EXISTS `digital_diary_lesson` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `lesson_no` varchar(50) NOT NULL,
  `lesson_name` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `dd_lesson_sort` (`sort_order`),
  KEY `dd_lesson_branch` (`branch_id`),
  KEY `dd_lesson_session` (`session_id`),
  KEY `dd_lesson_class` (`class_id`),
  KEY `dd_lesson_section` (`section_id`),
  KEY `dd_lesson_subject` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
