-- Today's Work: multiple lessons + notebook types (run after digital_diary_today_work.sql)

ALTER TABLE `digital_diary_today_work`
  ADD COLUMN `lesson_ids` VARCHAR(500) DEFAULT NULL COMMENT 'comma-separated digital_diary_lesson.id' AFTER `lesson_id`,
  ADD COLUMN `cw_notebook_type_id` INT(11) DEFAULT NULL COMMENT 'FK digital_diary_notebook_type' AFTER `home_work_note`,
  ADD COLUMN `hw_notebook_type_id` INT(11) DEFAULT NULL COMMENT 'FK digital_diary_notebook_type' AFTER `cw_notebook_type_id`;
