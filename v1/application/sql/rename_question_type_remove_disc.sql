-- Remove "Disc - " prefix from descriptive question type names (types 8–11).
-- Safe to run multiple times.

UPDATE `onlineexam_question_type`
SET `name` = 'Fill in the blanks', `updated_at` = NOW()
WHERE `question_type_id` = 8;

UPDATE `onlineexam_question_type`
SET `name` = 'Very short', `updated_at` = NOW()
WHERE `question_type_id` = 9;

UPDATE `onlineexam_question_type`
SET `name` = 'Short', `updated_at` = NOW()
WHERE `question_type_id` = 10;

UPDATE `onlineexam_question_type`
SET `name` = 'Long', `updated_at` = NOW()
WHERE `question_type_id` = 11;

-- Also fix labels already saved on existing questions
UPDATE `questions`
SET `custom_question_type` = 'Fill in the blanks'
WHERE `type` = 8 AND (`custom_question_type` LIKE 'Disc%Fill%' OR `custom_question_type` = 'Disc - Fill in the blanks');

UPDATE `questions`
SET `custom_question_type` = 'Very short'
WHERE `type` = 9 AND (`custom_question_type` LIKE 'Disc%Very%' OR `custom_question_type` = 'Disc - Very short');

UPDATE `questions`
SET `custom_question_type` = 'Short'
WHERE `type` = 10 AND (`custom_question_type` LIKE 'Disc%Short%' OR `custom_question_type` = 'Disc - Short');

UPDATE `questions`
SET `custom_question_type` = 'Long'
WHERE `type` = 11 AND (`custom_question_type` LIKE 'Disc%Long%' OR `custom_question_type` = 'Disc - Long');
