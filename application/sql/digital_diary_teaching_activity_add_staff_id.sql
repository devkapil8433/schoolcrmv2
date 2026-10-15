-- Deprecated: activity types are defined without teachers here; use digital_diary_teaching_activity_drop_staff_id.sql to remove if applied.
-- Link Teaching Activity to a teacher (staff). Run on existing databases after digital_diary_teaching_activity.sql

ALTER TABLE `digital_diary_teaching_activity`
  ADD COLUMN `staff_id` int(11) DEFAULT NULL COMMENT 'FK staff (teacher)' AFTER `branch_id`,
  ADD KEY `dd_teaching_activity_staff` (`staff_id`);
