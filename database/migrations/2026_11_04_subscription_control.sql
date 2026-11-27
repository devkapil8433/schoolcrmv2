-- School Pilot 360 - Subscription & branch access control
-- Migration: 2026_11_04_subscription_control
-- Run once.  Rollback: 2026_11_04_subscription_control_rollback.sql
-- BACK UP THE DATABASE FIRST (see SUBSCRIPTION_CONTROL.md).
-- Nothing in this migration deletes or rewrites existing school data.

START TRANSACTION;

-- 1. School (tenant) level above branch ---------------------------------------
CREATE TABLE IF NOT EXISTS `school` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobileno` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_school_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `branch`
  ADD COLUMN `school_id` int(11) DEFAULT NULL AFTER `id`,
  ADD COLUMN `status_changed_at` datetime DEFAULT NULL,
  ADD COLUMN `status_changed_by` int(11) DEFAULT NULL,
  ADD KEY `idx_branch_school` (`school_id`),
  ADD KEY `idx_branch_status` (`status`);

ALTER TABLE `branch`
  ADD CONSTRAINT `fk_branch_school` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Staff with role 2 listed here are School Admins (all branches of that school).
-- Role 2 staff that are not listed stay Branch Admins (own branch only).
CREATE TABLE IF NOT EXISTS `school_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_school_admin` (`school_id`,`staff_id`),
  KEY `idx_school_admin_staff` (`staff_id`),
  CONSTRAINT `fk_school_admin_school` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_school_admin_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2. Subscriptions ---------------------------------------------------------------
-- admin_status is what the Super Admin controls.  The effective state
-- (active / grace / expired / suspended) is computed from dates + grace period.
CREATE TABLE IF NOT EXISTS `school_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `grace_days` tinyint(3) unsigned DEFAULT NULL COMMENT 'NULL = use platform default',
  `admin_status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `payment_status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_subscription_school` (`school_id`),
  KEY `idx_subscription_expiry` (`expiry_date`),
  CONSTRAINT `fk_subscription_school` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Manual payment records. Rows are never deleted; a correction voids the row.
CREATE TABLE IF NOT EXISTS `subscription_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `method` varchar(50) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `recorded_by` int(11) NOT NULL COMMENT 'login_credential.id',
  `status` enum('recorded','void') NOT NULL DEFAULT 'recorded',
  `void_reason` varchar(255) DEFAULT NULL,
  `voided_by` int(11) DEFAULT NULL,
  `voided_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_payment_school` (`school_id`),
  KEY `idx_payment_date` (`payment_date`),
  CONSTRAINT `fk_payment_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `school_subscription` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_payment_school` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Platform wide policy (single row, id = 1)
CREATE TABLE IF NOT EXISTS `subscription_settings` (
  `id` tinyint(4) NOT NULL,
  `default_grace_days` tinyint(3) unsigned NOT NULL DEFAULT 7,
  `warning_days` tinyint(3) unsigned NOT NULL DEFAULT 15 COMMENT 'show expiry warning this many days before expiry',
  `support_message` varchar(500) NOT NULL DEFAULT 'Please contact the platform administrator to renew the subscription.',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 3. Audit log -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `platform_audit_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `actor_login_id` int(11) DEFAULT NULL,
  `actor_name` varchar(150) DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `entity_type` varchar(40) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_school` (`school_id`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 4. Seed (no existing data is changed except the new branch.school_id column) --------
INSERT INTO `subscription_settings` (`id`, `default_grace_days`, `warning_days`) VALUES (1, 15, 15);

-- One school per distinct existing school_name; existing branches are linked to it.
INSERT INTO `school` (`name`, `code`, `email`, `mobileno`)
SELECT b.`school_name`, CONCAT('SCH', LPAD(MIN(b.`id`), 4, '0')), MIN(b.`email`), MIN(b.`mobileno`)
FROM `branch` b GROUP BY b.`school_name`;

UPDATE `branch` b INNER JOIN `school` s ON s.`name` = b.`school_name` SET b.`school_id` = s.`id`;

-- Baseline subscription so nobody is locked out on day one (edit in the Super Admin panel).
INSERT INTO `school_subscription` (`school_id`, `plan_name`, `amount`, `start_date`, `expiry_date`, `admin_status`, `payment_status`, `notes`)
SELECT s.`id`, 'Baseline (migration)', 0.00, CURDATE(), '2027-11-04', 'active', 'unpaid', 'Created by migration so existing schools keep access.'
FROM `school` s;

COMMIT;
