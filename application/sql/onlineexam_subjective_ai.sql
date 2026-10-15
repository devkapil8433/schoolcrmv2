-- Subjective / Reading-Writing AI evaluation (CodeIgniter / MySQL 5.7+)
-- Safe to run multiple times: check table existence in your tool or use IF NOT EXISTS.

SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `onlineexam_question_subjective` (
  `question_id` int(11) NOT NULL,
  `passage_context` longtext DEFAULT NULL COMMENT 'Optional reading passage or context',
  `key_points` longtext DEFAULT NULL COMMENT 'One key point per line',
  `evaluation_criteria` longtext DEFAULT NULL COMMENT 'Human-readable rubric / criteria text',
  `rubric_weights` varchar(512) NOT NULL DEFAULT '{"accuracy":4,"relevance":2,"completeness":2,"clarity":1,"grammar":1}' COMMENT 'JSON: max points per criterion (AI must respect sum as scale)',
  `difficulty` tinyint(4) NOT NULL DEFAULT 2 COMMENT '1=easy .. 5=hard',
  `ai_evaluation_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `hybrid_keyword_boost` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Optional small boost from key-point string overlap',
  `ai_custom_instructions` text DEFAULT NULL,
  `student_show_obtained_marks` tinyint(1) NOT NULL DEFAULT 1,
  `student_show_feedback` tinyint(1) NOT NULL DEFAULT 1,
  `student_show_strengths` tinyint(1) NOT NULL DEFAULT 1,
  `student_show_weaknesses` tinyint(1) NOT NULL DEFAULT 1,
  `student_show_breakdown` tinyint(1) NOT NULL DEFAULT 0,
  `evaluation_type` varchar(64) NOT NULL DEFAULT 'reading_writing' COMMENT 'Extensible for future subjective types',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`question_id`),
  CONSTRAINT `fk_subjective_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `online_exam_answer_evaluation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_exam_answer_id` int(11) NOT NULL,
  `status` enum('pending','processing','completed','failed','manual_review') NOT NULL DEFAULT 'pending',
  `total_score` decimal(10,2) DEFAULT NULL,
  `max_score` decimal(10,2) DEFAULT NULL,
  `percentage` decimal(6,2) DEFAULT NULL,
  `breakdown_json` longtext DEFAULT NULL,
  `strengths_json` longtext DEFAULT NULL,
  `weaknesses_json` longtext DEFAULT NULL,
  `feedback_text` longtext DEFAULT NULL,
  `is_off_topic` tinyint(1) DEFAULT NULL,
  `confidence` decimal(6,4) DEFAULT NULL,
  `keyword_coverage_score` decimal(6,4) DEFAULT NULL COMMENT '0-1 ratio from backend key-point check',
  `hybrid_adjustment` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Marks added after AI (scaled to question marks)',
  `raw_request` longtext DEFAULT NULL COMMENT 'Audit: messages or payload sent to AI',
  `raw_response` longtext DEFAULT NULL COMMENT 'Audit: raw model output',
  `last_error` text DEFAULT NULL,
  `retry_count` tinyint(4) NOT NULL DEFAULT 0,
  `manual_override_marks` decimal(10,2) DEFAULT NULL,
  `manual_override_by` int(11) DEFAULT NULL,
  `manual_override_at` datetime DEFAULT NULL,
  `evaluated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_answer_eval` (`online_exam_answer_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_eval_answer` FOREIGN KEY (`online_exam_answer_id`) REFERENCES `online_exam_answer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
