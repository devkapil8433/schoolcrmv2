-- Optional: remove teacher column from teaching activity master (types only; assign teachers elsewhere later).
-- Run only if digital_diary_teaching_activity_add_staff_id.sql was applied.

ALTER TABLE `digital_diary_teaching_activity` DROP INDEX `dd_teaching_activity_staff`;
ALTER TABLE `digital_diary_teaching_activity` DROP COLUMN `staff_id`;
