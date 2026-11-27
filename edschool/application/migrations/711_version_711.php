<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Reading/Writing subjective AI: question metadata + per-answer evaluation rows.
 * Foreign keys omitted here for broader MySQL compatibility; see application/sql/onlineexam_subjective_ai.sql if you want FKs.
 */
class Migration_Version_711 extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('onlineexam_question_subjective')) {
            $this->dbforge->add_field(array(
                'question_id' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'passage_context' => array('type' => 'LONGTEXT', 'null' => true),
                'key_points' => array('type' => 'LONGTEXT', 'null' => true),
                'evaluation_criteria' => array('type' => 'LONGTEXT', 'null' => true),
                'rubric_weights' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 512,
                    'default' => '{"accuracy":4,"relevance":2,"completeness":2,"clarity":1,"grammar":1}',
                ),
                'difficulty' => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 2),
                'ai_evaluation_enabled' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 1),
                'hybrid_keyword_boost' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
                'ai_custom_instructions' => array('type' => 'TEXT', 'null' => true),
                'student_show_obtained_marks' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 1),
                'student_show_feedback' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 1),
                'student_show_strengths' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 1),
                'student_show_weaknesses' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 1),
                'student_show_breakdown' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
                'evaluation_type' => array('type' => 'VARCHAR', 'constraint' => 64, 'default' => 'reading_writing'),
                'created_at' => array('type' => 'DATETIME', 'null' => true),
                'updated_at' => array('type' => 'DATETIME', 'null' => true),
            ));
            $this->dbforge->add_key('question_id', true);
            $this->dbforge->create_table('onlineexam_question_subjective', true, array('ENGINE' => 'InnoDB'));
        }

        if (!$this->db->table_exists('online_exam_answer_evaluation')) {
            $this->dbforge->add_field(array(
                'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true),
                'online_exam_answer_id' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'status' => array('type' => 'VARCHAR', 'constraint' => 32, 'default' => 'pending'),
                'total_score' => array('type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true),
                'max_score' => array('type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true),
                'percentage' => array('type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true),
                'breakdown_json' => array('type' => 'LONGTEXT', 'null' => true),
                'strengths_json' => array('type' => 'LONGTEXT', 'null' => true),
                'weaknesses_json' => array('type' => 'LONGTEXT', 'null' => true),
                'feedback_text' => array('type' => 'LONGTEXT', 'null' => true),
                'is_off_topic' => array('type' => 'TINYINT', 'constraint' => 1, 'null' => true),
                'confidence' => array('type' => 'DECIMAL', 'constraint' => '6,4', 'null' => true),
                'keyword_coverage_score' => array('type' => 'DECIMAL', 'constraint' => '6,4', 'null' => true),
                'hybrid_adjustment' => array('type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 0),
                'raw_request' => array('type' => 'LONGTEXT', 'null' => true),
                'raw_response' => array('type' => 'LONGTEXT', 'null' => true),
                'last_error' => array('type' => 'TEXT', 'null' => true),
                'retry_count' => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 0),
                'manual_override_marks' => array('type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true),
                'manual_override_by' => array('type' => 'INT', 'constraint' => 11, 'null' => true),
                'manual_override_at' => array('type' => 'DATETIME', 'null' => true),
                'evaluated_at' => array('type' => 'DATETIME', 'null' => true),
                'created_at' => array('type' => 'DATETIME', 'null' => true),
                'updated_at' => array('type' => 'DATETIME', 'null' => true),
            ));
            $this->dbforge->add_key('id', true);
            $this->dbforge->add_key('online_exam_answer_id', false, true);
            $this->dbforge->add_key('status');
            $this->dbforge->create_table('online_exam_answer_evaluation', true, array('ENGINE' => 'InnoDB'));
        }
    }
}
