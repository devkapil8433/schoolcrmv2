<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Metadata for Reading/Writing (types 6–7) and future subjective types.
 */
class Onlineexam_subjective_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function table_ready()
    {
        return $this->db->table_exists('onlineexam_question_subjective');
    }

    public function get_by_question_id($questionId)
    {
        if (!$this->table_ready()) {
            return null;
        }
        $row = $this->db->where('question_id', (int) $questionId)->get('onlineexam_question_subjective')->row_array();
        return $row ?: null;
    }

    /**
     * Persist subjective configuration from POST (types 6 and 7 only).
     */
    public function sync_from_post($questionId, $questionType)
    {
        if (!$this->table_ready() || !in_array((int) $questionType, array(6, 7), true)) {
            return;
        }
        $questionId = (int) $questionId;
        if ($questionId < 1) {
            return;
        }

        $weights = $this->normalize_rubric_weights($this->input->post('rubric_weights_json', true));
        $row = array(
            'passage_context' => $this->input->post('passage_context', false),
            'key_points' => $this->input->post('key_points', false),
            'evaluation_criteria' => $this->input->post('evaluation_criteria', false),
            'rubric_weights' => json_encode($weights),
            'difficulty' => max(1, min(5, (int) ($this->input->post('subjective_difficulty') ?: 2))),
            'ai_evaluation_enabled' => $this->input->post('ai_evaluation_enabled') ? 1 : 0,
            'hybrid_keyword_boost' => $this->input->post('hybrid_keyword_boost') ? 1 : 0,
            'ai_custom_instructions' => $this->input->post('ai_custom_instructions', false),
            'student_show_obtained_marks' => $this->input->post('student_show_obtained_marks') ? 1 : 0,
            'student_show_feedback' => $this->input->post('student_show_feedback') ? 1 : 0,
            'student_show_strengths' => $this->input->post('student_show_strengths') ? 1 : 0,
            'student_show_weaknesses' => $this->input->post('student_show_weaknesses') ? 1 : 0,
            'student_show_breakdown' => $this->input->post('student_show_breakdown') ? 1 : 0,
            'evaluation_type' => 'reading_writing',
            'updated_at' => date('Y-m-d H:i:s'),
        );

        $exists = $this->db->where('question_id', $questionId)->get('onlineexam_question_subjective')->num_rows() > 0;
        if ($exists) {
            $this->db->where('question_id', $questionId)->update('onlineexam_question_subjective', $row);
        } else {
            $row['question_id'] = $questionId;
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('onlineexam_question_subjective', $row);
        }
    }

    public function default_rubric_weights()
    {
        return array(
            'accuracy' => 4,
            'relevance' => 2,
            'completeness' => 2,
            'clarity' => 1,
            'grammar' => 1,
        );
    }

    public function normalize_rubric_weights($jsonOrNull)
    {
        $defaults = $this->default_rubric_weights();
        if (is_string($jsonOrNull) && $jsonOrNull !== '') {
            $decoded = json_decode($jsonOrNull, true);
            if (is_array($decoded)) {
                foreach ($defaults as $k => $v) {
                    if (isset($decoded[$k]) && is_numeric($decoded[$k])) {
                        $defaults[$k] = max(0, (float) $decoded[$k]);
                    }
                }
            }
        }
        return $defaults;
    }

    public function get_evaluation_by_answer_id($answerId)
    {
        if (!$this->db->table_exists('online_exam_answer_evaluation')) {
            return null;
        }
        return $this->db->where('online_exam_answer_id', (int) $answerId)->get('online_exam_answer_evaluation')->row_array();
    }

    public function ensure_evaluation_row($answerId, $status = 'pending')
    {
        if (!$this->db->table_exists('online_exam_answer_evaluation')) {
            return null;
        }
        $answerId = (int) $answerId;
        $existing = $this->db->where('online_exam_answer_id', $answerId)->get('online_exam_answer_evaluation')->row_array();
        if ($existing) {
            return (int) $existing['id'];
        }
        $this->db->insert('online_exam_answer_evaluation', array(
            'online_exam_answer_id' => $answerId,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return (int) $this->db->insert_id();
    }

    public function update_evaluation($answerId, array $data)
    {
        if (!$this->db->table_exists('online_exam_answer_evaluation')) {
            return;
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('online_exam_answer_id', (int) $answerId)->update('online_exam_answer_evaluation', $data);
    }
}
