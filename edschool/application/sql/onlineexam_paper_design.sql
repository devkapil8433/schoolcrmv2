-- Question paper design templates (Design Question Paper screen).
-- The application also auto-creates this table on first use (ensurePaperDesignTable).

CREATE TABLE IF NOT EXISTS `onlineexam_paper_design` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `config` longtext NOT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `oe_paper_design_br` (`branch_id`,`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
