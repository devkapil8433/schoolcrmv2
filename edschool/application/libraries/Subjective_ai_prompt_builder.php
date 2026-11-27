<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Builds system + user messages for strict JSON subjective evaluation.
 */
class Subjective_ai_prompt_builder
{
    const SYSTEM_PROMPT = 'You are a strict exam grader for reading/writing tasks. '
        . 'You must output a single valid JSON object only — no markdown, no code fences, no commentary before or after. '
        . 'Be conservative: do not award full marks unless the answer fully meets the rubric. '
        . 'Penalize off-topic, vague, copied filler, or nonsensical answers heavily. '
        . 'If the answer is empty or meaningless, use very low scores and set is_off_topic true when appropriate.';

    /**
     * @param array $ctx Keys: question_text, passage, reference_answer, key_points_lines, evaluation_criteria,
     *                  rubric_weights (assoc), max_score (float), question_marks (float), difficulty (int),
     *                  custom_instructions (string), student_answer (string), keyword_hint (string optional)
     * @return array[] array of ['role'=>'system'|'user','content'=>string]
     */
    public function build_messages(array $ctx)
    {
        $weights = isset($ctx['rubric_weights']) && is_array($ctx['rubric_weights']) ? $ctx['rubric_weights'] : array();
        $sum = 0;
        foreach ($weights as $v) {
            $sum += (float) $v;
        }
        $maxScore = isset($ctx['max_score']) ? (float) $ctx['max_score'] : $sum;
        if ($maxScore <= 0) {
            $maxScore = 10;
        }

        $schema = array(
            'total_score' => 'number — sum of breakdown values (can be decimal)',
            'max_score' => 'number — must equal ' . $maxScore,
            'percentage' => 'number 0–100',
            'breakdown' => array(
                'accuracy' => '0 to ' . (isset($weights['accuracy']) ? $weights['accuracy'] : 0),
                'relevance' => '0 to ' . (isset($weights['relevance']) ? $weights['relevance'] : 0),
                'completeness' => '0 to ' . (isset($weights['completeness']) ? $weights['completeness'] : 0),
                'clarity' => '0 to ' . (isset($weights['clarity']) ? $weights['clarity'] : 0),
                'grammar' => '0 to ' . (isset($weights['grammar']) ? $weights['grammar'] : 0),
            ),
            'strengths' => 'array of short strings',
            'weaknesses' => 'array of short strings',
            'feedback' => 'one concise paragraph for the student',
            'is_off_topic' => 'boolean',
            'confidence' => 'number 0–1 grader confidence',
        );

        $user = array();
        $user[] = '### Task';
        $user[] = 'Evaluate the student answer against the question and materials below.';
        $user[] = '### Question';
        $user[] = $this->block($ctx['question_text'] ?? '');
        if (!empty($ctx['passage'])) {
            $user[] = '### Passage / context';
            $user[] = $this->block($ctx['passage']);
        }
        if (!empty($ctx['reference_answer'])) {
            $user[] = '### Reference / model answer (guide only; equivalent valid answers may differ)';
            $user[] = $this->block($ctx['reference_answer']);
        }
        if (!empty($ctx['key_points_lines']) && is_array($ctx['key_points_lines'])) {
            $user[] = '### Expected key points (student should cover most)';
            $user[] = implode("\n", array_map(function ($l) {
                return '- ' . $l;
            }, $ctx['key_points_lines']));
        }
        if (!empty($ctx['evaluation_criteria'])) {
            $user[] = '### Evaluation criteria (rubric description)';
            $user[] = $this->block($ctx['evaluation_criteria']);
        }
        if (!empty($ctx['custom_instructions'])) {
            $user[] = '### Additional instructions from instructor';
            $user[] = $this->block($ctx['custom_instructions']);
        }
        if (!empty($ctx['keyword_hint'])) {
            $user[] = '### Automated key-point coverage note (for calibration; still use your own judgment)';
            $user[] = $this->block($ctx['keyword_hint']);
        }

        $user[] = '### Scoring scale';
        $user[] = 'Per-criterion caps (do not exceed): ' . json_encode($weights);
        $user[] = 'max_score for this response must be exactly: ' . $maxScore;
        $user[] = 'Difficulty level (1=easy, 5=hard): ' . (int) ($ctx['difficulty'] ?? 2);
        $user[] = 'Question weight in exam (for context only): ' . ($ctx['question_marks'] ?? '') . ' marks';

        $user[] = '### Student answer';
        $user[] = $this->block($ctx['student_answer'] ?? '');

        $user[] = '### Rules';
        $user[] = '- total_score must equal the sum of breakdown values (within 0.01).';
        $user[] = '- percentage = (total_score / max_score) * 100.';
        $user[] = '- List specific missing key points briefly in weaknesses when applicable.';
        $user[] = '- If answer is off-topic or nonsense, set is_off_topic true and keep total_score very low.';
        $user[] = '- Do not be lenient; partial credit should reflect genuine partial understanding.';

        $user[] = '### Required JSON shape';
        $user[] = json_encode($schema, JSON_PRETTY_PRINT);
        $user[] = 'Respond with JSON only.';

        return array(
            array('role' => 'system', 'content' => self::SYSTEM_PROMPT),
            array('role' => 'user', 'content' => implode("\n\n", $user)),
        );
    }

    private function block($text)
    {
        $t = trim((string) $text);
        return $t === '' ? '(none)' : $t;
    }
}
