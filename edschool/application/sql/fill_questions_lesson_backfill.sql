-- One-shot: set questions.lesson_id for rows where lesson_id IS NULL, using existing
-- digital_diary_lesson rows that match branch_id, class_id, section_id, subject_id.
--
-- Picks ONE lesson per group: MIN(digital_diary_lesson.id). If you have several lessons
-- per subject, prefer the in-app backfill (round-robin) instead:
--   /onlineexam/questions_lesson_backfill
--
-- Prerequisites: questions.lesson_id exists; digital_diary_lesson has data.

UPDATE `questions` `q`
INNER JOIN (
  SELECT `branch_id`, `class_id`, `section_id`, `subject_id`, MIN(`id`) AS `pick_lesson_id`
  FROM `digital_diary_lesson`
  GROUP BY `branch_id`, `class_id`, `section_id`, `subject_id`
) `x` ON `x`.`branch_id` = `q`.`branch_id`
  AND `x`.`class_id` = `q`.`class_id`
  AND `x`.`section_id` = `q`.`section_id`
  AND `x`.`subject_id` = `q`.`subject_id`
SET `q`.`lesson_id` = `x`.`pick_lesson_id`
WHERE (`q`.`lesson_id` IS NULL OR `q`.`lesson_id` = 0);
