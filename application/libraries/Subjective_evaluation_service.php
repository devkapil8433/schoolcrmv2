<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Orchestrates subjective AI evaluation: keyword hint, API call, parse, persist.
 */
class Subjective_evaluation_service
{
    /** @var CI_Controller */
    protected $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('onlineexam_subjective_model');
        $this->ci->load->library('subjective_ai_client');
        $this->ci->load->library('subjective_ai_prompt_builder');
    }

    /**
     * Run evaluation for one online_exam_answer row (sync).
     */
    public function evaluate_answer_by_id($answerId)
    {
        if (!$this->ci->db->table_exists('online_exam_answer_evaluation')) {
            return array('ok' => false, 'error' => 'Evaluation table missing');
        }
        $answerId = (int) $answerId;
        $row = $this->ci->db->where('id', $answerId)->get('online_exam_answer')->row_array();
        if (!$row) {
            return array('ok' => false, 'error' => 'Answer not found');
        }
        $question = $this->ci->db->where('id', (int) $row['question_id'])->get('questions')->row_array();
        if (!$question || !in_array((int) $question['type'], array(6, 7), true)) {
            return array('ok' => false, 'error' => 'Not a reading/writing question');
        }
        $sub = $this->ci->onlineexam_subjective_model->get_by_question_id($question['id']);
        if (!$sub || empty($sub['ai_evaluation_enabled'])) {
            return array('ok' => false, 'error' => 'AI evaluation disabled for this question');
        }

        $this->ci->config->load('subjective_ai', true);
        if (empty($this->ci->config->item('subjective_ai_api_key', 'subjective_ai'))) {
            $this->mark_manual_review($answerId, 'AI API key not configured (set SUBJECTIVE_AI_API_KEY on server)');
            return array('ok' => false, 'error' => 'AI API key not configured');
        }

        $qm = $this->ci->db
            ->where('onlineexam_id', (int) $row['online_exam_id'])
            ->where('question_id', (int) $row['question_id'])
            ->get('questions_manage')
            ->row_array();
        $questionMarks = $qm && isset($qm['marks']) ? (float) $qm['marks'] : (float) $question['mark'];

        $weights = $this->ci->onlineexam_subjective_model->normalize_rubric_weights($sub['rubric_weights']);
        $maxRubric = 0;
        foreach ($weights as $w) {
            $maxRubric += (float) $w;
        }
        if ($maxRubric <= 0) {
            $maxRubric = 10;
        }

        $keyLines = $this->key_points_to_lines($sub['key_points'] ?? '');
        $coverage = $this->keyword_coverage_score($keyLines, $row['answer'] ?? '');
        $keywordHint = 'Approximate key-point string coverage: ' . round($coverage * 100, 1)
            . '% (lines matched / total key lines).';

        $ctx = array(
            'question_text' => $question['question'],
            'passage' => $sub['passage_context'] ?? '',
            'reference_answer' => $question['answer'] ?? '',
            'key_points_lines' => $keyLines,
            'evaluation_criteria' => $sub['evaluation_criteria'] ?? '',
            'rubric_weights' => $weights,
            'max_score' => $maxRubric,
            'question_marks' => $questionMarks,
            'difficulty' => (int) $sub['difficulty'],
            'custom_instructions' => $sub['ai_custom_instructions'] ?? '',
            'student_answer' => $row['answer'] ?? '',
            'keyword_hint' => $keywordHint,
        );

        $messages = $this->ci->subjective_ai_prompt_builder->build_messages($ctx);
        $rawRequest = json_encode($messages);

        $this->ci->onlineexam_subjective_model->ensure_evaluation_row($answerId, 'pending');
        $this->ci->onlineexam_subjective_model->update_evaluation($answerId, array(
            'status' => 'processing',
            'keyword_coverage_score' => $coverage,
            'raw_request' => $rawRequest,
            'last_error' => null,
        ));

        $this->ci->config->load('subjective_ai', true);
        $maxRetries = (int) $this->ci->config->item('subjective_ai_max_retries', 'subjective_ai');
        $attempt = 0;
        $lastErr = '';
        $content = '';
        $rawApi = '';

        while ($attempt <= $maxRetries) {
            $attempt++;
            $res = $this->ci->subjective_ai_client->chat($messages);
            $rawApi = $res['raw'];
            if (!$res['ok']) {
                $lastErr = $res['error'];
                continue;
            }
            $parsed = $this->parse_evaluation_json($res['content']);
            if ($parsed['ok']) {
                $content = $res['content'];
                $data = $parsed['data'];
                $hybridAdj = $this->compute_hybrid_adjustment(
                    (float) $data['total_score'],
                    (float) $data['max_score'],
                    $questionMarks,
                    $coverage,
                    !empty($sub['hybrid_keyword_boost'])
                );
                $scaled = $this->scale_to_question_marks((float) $data['total_score'], (float) $data['max_score'], $questionMarks);
                $finalMarks = min($questionMarks, max(0, $scaled + $hybridAdj));

                $this->ci->onlineexam_subjective_model->update_evaluation($answerId, array(
                    'status' => 'completed',
                    'total_score' => $data['total_score'],
                    'max_score' => $data['max_score'],
                    'percentage' => $data['percentage'],
                    'breakdown_json' => json_encode($data['breakdown']),
                    'strengths_json' => json_encode($data['strengths']),
                    'weaknesses_json' => json_encode($data['weaknesses']),
                    'feedback_text' => $data['feedback'],
                    'is_off_topic' => $data['is_off_topic'] ? 1 : 0,
                    'confidence' => $data['confidence'],
                    'hybrid_adjustment' => $hybridAdj,
                    'raw_response' => $content,
                    'last_error' => null,
                    'retry_count' => $attempt - 1,
                    'evaluated_at' => date('Y-m-d H:i:s'),
                ));

                return array(
                    'ok' => true,
                    'scaled_marks' => $finalMarks,
                    'parsed' => $data,
                    'hybrid_adjustment' => $hybridAdj,
                );
            }
            $lastErr = $parsed['error'];
        }

        $this->ci->onlineexam_subjective_model->update_evaluation($answerId, array(
            'status' => 'failed',
            'raw_response' => $content ?: null,
            'last_error' => $lastErr,
            'retry_count' => $maxRetries,
            'evaluated_at' => date('Y-m-d H:i:s'),
        ));

        return array('ok' => false, 'error' => $lastErr ?: 'Parse failed');
    }

    public function mark_manual_review($answerId, $reason = null)
    {
        $this->ci->onlineexam_subjective_model->ensure_evaluation_row($answerId, 'manual_review');
        $this->ci->onlineexam_subjective_model->update_evaluation($answerId, array(
            'status' => 'manual_review',
            'last_error' => $reason,
            'evaluated_at' => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * @return float 0..1
     */
    public function keyword_coverage_score(array $keyLines, $studentAnswer)
    {
        $student = $this->normalize_text($studentAnswer);
        if ($student === '') {
            return 0.0;
        }
        $lines = array_filter(array_map('trim', $keyLines));
        if (count($lines) === 0) {
            return 0.5;
        }
        $hit = 0;
        foreach ($lines as $line) {
            $n = $this->normalize_text($line);
            if ($n === '') {
                continue;
            }
            if (mb_stripos($student, $n, 0, 'UTF-8') !== false) {
                $hit++;
            } elseif (mb_strlen($n, 'UTF-8') > 40) {
                // Long line: require substantial token overlap
                $tok = preg_split('/\s+/', $n, -1, PREG_SPLIT_NO_EMPTY);
                $need = min(4, count($tok));
                $got = 0;
                foreach (array_slice($tok, 0, 12) as $t) {
                    if (mb_strlen($t, 'UTF-8') > 3 && mb_stripos($student, $t, 0, 'UTF-8') !== false) {
                        $got++;
                    }
                }
                if ($got >= $need) {
                    $hit++;
                }
            }
        }
        return $hit / max(1, count($lines));
    }

    public function scale_to_question_marks($totalScore, $maxScore, $questionMarks)
    {
        if ($maxScore <= 0) {
            return 0.0;
        }
        return ((float) $totalScore / (float) $maxScore) * (float) $questionMarks;
    }

    /**
     * Small optional bump when keyword coverage is strong (does not replace AI).
     */
    public function compute_hybrid_adjustment($aiTotal, $aiMax, $questionMarks, $coverage, $enabled)
    {
        if (!$enabled || $questionMarks <= 0 || $aiMax <= 0) {
            return 0.0;
        }
        if ($coverage < 0.55) {
            return 0.0;
        }
        $cap = 0.08 * (float) $questionMarks;
        $bonus = ($coverage - 0.5) * 0.25 * (float) $questionMarks;
        return round(min($cap, max(0, $bonus)), 4);
    }

    /**
     * @return array{ok:bool,data?:array,error?:string}
     */
    public function parse_evaluation_json($rawContent)
    {
        $json = $this->extract_json_string($rawContent);
        if ($json === null) {
            return array('ok' => false, 'error' => 'No JSON found');
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return array('ok' => false, 'error' => 'Invalid JSON');
        }
        $req = array('total_score', 'max_score', 'percentage', 'breakdown', 'strengths', 'weaknesses', 'feedback', 'is_off_topic', 'confidence');
        foreach ($req as $k) {
            if (!array_key_exists($k, $data)) {
                return array('ok' => false, 'error' => 'Missing key: ' . $k);
            }
        }
        if (!is_array($data['breakdown'])) {
            return array('ok' => false, 'error' => 'breakdown must be object');
        }
        foreach (array('accuracy', 'relevance', 'completeness', 'clarity', 'grammar') as $bk) {
            if (!isset($data['breakdown'][$bk])) {
                return array('ok' => false, 'error' => 'breakdown.' . $bk);
            }
        }
        if (!is_array($data['strengths'])) {
            $data['strengths'] = array();
        }
        if (!is_array($data['weaknesses'])) {
            $data['weaknesses'] = array();
        }
        $data['total_score'] = (float) $data['total_score'];
        $data['max_score'] = (float) $data['max_score'];
        $data['percentage'] = (float) $data['percentage'];
        $data['confidence'] = (float) $data['confidence'];
        $data['is_off_topic'] = !empty($data['is_off_topic']);
        $data['feedback'] = (string) $data['feedback'];

        $sumBreak = 0;
        foreach ($data['breakdown'] as $v) {
            $sumBreak += (float) $v;
        }
        if (abs($sumBreak - $data['total_score']) > 0.05) {
            // Allow AI small drift; normalize total_score
            $data['total_score'] = round($sumBreak, 2);
        }
        if ($data['max_score'] > 0) {
            $data['percentage'] = round(($data['total_score'] / $data['max_score']) * 100, 2);
        }

        return array('ok' => true, 'data' => $data);
    }

    private function extract_json_string($text)
    {
        $t = trim((string) $text);
        if ($t === '') {
            return null;
        }
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/u', $t, $m)) {
            $t = trim($m[1]);
        }
        $start = mb_strpos($t, '{', 0, 'UTF-8');
        $end = mb_strrpos($t, '}', 0, 'UTF-8');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        return mb_substr($t, $start, $end - $start + 1, 'UTF-8');
    }

    private function key_points_to_lines($text)
    {
        $lines = preg_split('/\R/u', (string) $text);
        return is_array($lines) ? $lines : array();
    }

    private function normalize_text($s)
    {
        $s = strip_tags((string) $s);
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($s)), 'UTF-8');
    }
}
