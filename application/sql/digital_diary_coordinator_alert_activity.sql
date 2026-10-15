-- Split coordinator tasks by teaching activity type.
-- Auto-applied by Digital_diary_model::ensureCoordinatorAlertActivityColumn() which also
-- backfills existing alerts into one task per activity used that day.

ALTER TABLE `digital_diary_coordinator_alert`
  ADD COLUMN `teaching_activity_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK digital_diary_teaching_activity; 0 = all/legacy' AFTER `section_id`;

ALTER TABLE `digital_diary_coordinator_alert`
  ADD KEY `dd_ca_activity` (`teaching_activity_id`);
