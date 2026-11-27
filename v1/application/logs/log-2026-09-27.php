<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-09-27 13:19:12 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`schoolpi_adminnna`.`fee_payment_history`, CONSTRAINT `fee_payment_history_rms_1` FOREIGN KEY (`allocation_id`) REFERENCES `fee_allocation` (`id`) ON DELETE CASCADE) - Invalid query: INSERT INTO `fee_payment_history` (`allocation_id`, `type_id`, `collect_by`, `amount`, `discount`, `fine`, `pay_via`, `remarks`, `date`) VALUES ('32', '3', '1', 630, '0', '30.00', '1', '', '2026-09-27')
