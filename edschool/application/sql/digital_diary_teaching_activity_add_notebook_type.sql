-- Add Note Book Type to Teaching Activity (run if table already exists)
-- Run after digital_diary_teaching_activity.sql and digital_diary_notebook_type.sql

ALTER TABLE `digital_diary_teaching_activity`
  ADD COLUMN `notebook_type_id` int(11) DEFAULT NULL COMMENT 'FK to digital_diary_notebook_type' AFTER `branch_id`,
  ADD KEY `dd_teaching_activity_notebook_type` (`notebook_type_id`);
