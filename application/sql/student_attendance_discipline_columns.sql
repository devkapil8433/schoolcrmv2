-- Add discipline fields to attendance entry (used by Student Work Report)
ALTER TABLE `student_attendance`
  ADD COLUMN `discipline_dress` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status`,
  ADD COLUMN `discipline_conduct` TINYINT(1) NOT NULL DEFAULT 1 AFTER `discipline_dress`;

