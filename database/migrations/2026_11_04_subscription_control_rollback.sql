-- Rollback for 2026_11_04_subscription_control.sql
-- Removes only objects created by that migration. Original school data is untouched.
-- Note: this drops the subscription/payment/audit data recorded after the migration.
-- Export those tables first if you need to keep them.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `platform_audit_log`;
DROP TABLE IF EXISTS `subscription_payment`;
DROP TABLE IF EXISTS `subscription_settings`;
DROP TABLE IF EXISTS `school_subscription`;
DROP TABLE IF EXISTS `school_admin`;

ALTER TABLE `branch` DROP FOREIGN KEY `fk_branch_school`;
ALTER TABLE `branch`
  DROP INDEX `idx_branch_school`,
  DROP INDEX `idx_branch_status`,
  DROP COLUMN `school_id`,
  DROP COLUMN `status_changed_at`,
  DROP COLUMN `status_changed_by`;

DROP TABLE IF EXISTS `school`;

SET FOREIGN_KEY_CHECKS = 1;
