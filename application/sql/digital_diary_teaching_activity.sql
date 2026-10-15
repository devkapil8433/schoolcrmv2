-- Teaching Activity master table (Digital Diary)
-- Run this SQL to create the table

CREATE TABLE IF NOT EXISTS `digital_diary_teaching_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `notebook_type_id` int(11) DEFAULT NULL COMMENT 'FK to digital_diary_notebook_type (legacy; first id may mirror notebook_type_ids)',
  `notebook_type_ids` text DEFAULT NULL COMMENT 'Comma-separated notebook type IDs',
  `name` varchar(255) NOT NULL COMMENT 'Activity name e.g. Lecture, Discussion, Practical',
  `code` varchar(50) DEFAULT NULL COMMENT 'Optional short code',
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `enable_coordinator` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Coordinator enabled for this activity type',
  `enable_moderator` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Moderator enabled for this activity type',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `dd_teaching_activity_branch` (`branch_id`),
  KEY `dd_teaching_activity_notebook_type` (`notebook_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
