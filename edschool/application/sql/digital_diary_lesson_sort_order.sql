-- Add sort_order column for lesson sequence (run once on existing database in phpMyAdmin)
ALTER TABLE `digital_diary_lesson` ADD COLUMN `sort_order` INT(11) NOT NULL DEFAULT 0 AFTER `lesson_name`;
ALTER TABLE `digital_diary_lesson` ADD KEY `dd_lesson_sort` (`sort_order`);

-- Set initial order so existing rows keep current order (run after ALTER):
-- UPDATE digital_diary_lesson SET sort_order = id;
