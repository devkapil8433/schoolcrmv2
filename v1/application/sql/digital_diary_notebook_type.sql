-- Note Book Type master table (Digital Diary - from UA spec page 4)
-- Run this SQL to create the table

CREATE TABLE IF NOT EXISTS `digital_diary_notebook_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Notebook type name e.g. Class Work, Home Work, Practical',
  `code` varchar(50) DEFAULT NULL COMMENT 'Optional short code',
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `dd_notebook_type_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
