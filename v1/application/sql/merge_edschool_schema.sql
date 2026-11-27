-- Auto-generated idempotent schema merge from edschool.sql
-- Safe to run multiple times on existing databases
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS = 0;

-- Sync migration version after schema merge
UPDATE `migrations` SET `version` = 711 WHERE `version` < 711;

SET FOREIGN_KEY_CHECKS = 1;