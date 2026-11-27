<?php
defined('BASEPATH') or exit('No direct script access allowed');
$sub = isset($subjective) && is_array($subjective) ? $subjective : array();
$defaults = array(
	'passage_context' => '',
	'key_points' => '',
	'evaluation_criteria' => '',
	'rubric_weights' => '{"accuracy":4,"relevance":2,"completeness":2,"clarity":1,"grammar":1}',
	'difficulty' => 2,
	'ai_evaluation_enabled' => 1,
	'hybrid_keyword_boost' => 0,
	'ai_custom_instructions' => '',
	'student_show_obtained_marks' => 1,
	'student_show_feedback' => 1,
	'student_show_strengths' => 1,
	'student_show_weaknesses' => 1,
	'student_show_breakdown' => 0,
);
foreach ($defaults as $k => $v) {
	if (!array_key_exists($k, $sub) || $sub[$k] === null) {
		$sub[$k] = $v;
	}
}
$rubricJson = $sub['rubric_weights'];
if (is_array($rubricJson)) {
	$rubricJson = json_encode($rubricJson);
} elseif (is_string($rubricJson) && $rubricJson !== '' && (isset($rubricJson[0]) ? $rubricJson[0] : '') !== '{') {
	$rubricJson = $defaults['rubric_weights'];
} elseif ($rubricJson === '') {
	$rubricJson = $defaults['rubric_weights'];
} else {
	$tmp = json_decode($rubricJson, true);
	$rubricJson = json_encode($tmp ?: json_decode($defaults['rubric_weights'], true));
}
$q = isset($questions) && is_array($questions) ? $questions : array();
$isEdit = !empty($is_edit);
$levelVal = isset($q['level']) ? $q['level'] : set_value('question_level');
$exerciseVal = isset($q['exercise_id']) ? $q['exercise_id'] : $selected_exercise_id;
$markVal = isset($q['mark']) ? $q['mark'] : '';
$qText = isset($q['question']) ? $q['question'] : '';
$ansText = isset($q['answer']) ? $q['answer'] : '';
$explVal = isset($q['explanation']) ? $q['explanation'] : '';
?>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("question_level", $arrayLevel, $levelVal, "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?></label>
								<div class="col-md-6">
									<?php if (!empty($hide_exercise_field)): ?>
									<input type="hidden" name="exercise_id" value="<?= (int) $exerciseVal ?>">
									<p class="portal-input-static mb-none"><?= html_escape(isset($exercise_label) ? $exercise_label : '—') ?></p>
									<?php else: ?>
									<?php
										echo form_dropdown("exercise_id", $arrayExercise, $exerciseVal, "class='portal-input exercise-select'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
									<?php endif; ?>
								</div>
							</div>
							<?php if ($isEdit): ?>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('mark')?> (max) <span class="required">*</span></label>
								<div class="col-md-6">
									<input type="text" class="portal-input" name="mark" autocomplete="off" value="<?=html_escape($markVal)?>">
									<span class="error"></span>
								</div>
							</div>
							<?php endif; ?>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" class="question_note" rows="3"><?=html_escape($qText)?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label">Passage / context <span class="text-muted">(optional)</span></label>
								<div class="col-md-6">
									<textarea name="passage_context" class="question_note" rows="4"><?=html_escape($sub['passage_context'])?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label">Reference / model answer <span class="text-muted">(optional)</span></label>
								<div class="col-md-6">
									<textarea name="answer" rows="4" class="portal-input question-hi-input"><?=html_escape($ansText)?></textarea>
									<span class="text-muted">Used as a guide for the AI; not for exact matching.</span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label">Expected key points</label>
								<div class="col-md-6">
									<textarea name="key_points" rows="5" class="portal-input question-hi-input" placeholder="One point per line"><?=html_escape($sub['key_points'])?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label">Evaluation criteria (rubric text)</label>
								<div class="col-md-6">
									<textarea name="evaluation_criteria" class="portal-input" rows="4"><?=html_escape($sub['evaluation_criteria'])?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label">Rubric weights (JSON)</label>
								<div class="col-md-6">
									<textarea name="rubric_weights_json" class="portal-input" rows="2"><?=html_escape($rubricJson)?></textarea>
									<span class="text-muted">Caps per criterion; sum = AI scale (e.g. accuracy+relevance+…).</span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label">Difficulty</label>
								<div class="col-md-6">
									<select name="subjective_difficulty" class="portal-input">
										<?php for ($d = 1; $d <= 5; $d++) { ?>
										<option value="<?=$d?>" <?=(int) $sub['difficulty'] === $d ? 'selected' : ''?>><?=$d?> — <?= $d <= 2 ? 'easier' : ($d >= 4 ? 'harder' : 'medium') ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label">AI evaluation</label>
								<div class="col-md-6">
									<div class="checkbox-custom checkbox-primary">
										<input type="checkbox" name="ai_evaluation_enabled" id="ai_eval_<?=isset($tab_suffix)?$tab_suffix:''?>" value="1" <?= !empty($sub['ai_evaluation_enabled']) ? 'checked' : '' ?>>
										<label for="ai_eval_<?=isset($tab_suffix)?$tab_suffix:''?>">Enable AI scoring for this question</label>
									</div>
									<div class="checkbox-custom checkbox-primary mt-xs">
										<input type="checkbox" name="hybrid_keyword_boost" id="hybrid_<?=isset($tab_suffix)?$tab_suffix:''?>" value="1" <?= !empty($sub['hybrid_keyword_boost']) ? 'checked' : '' ?>>
										<label for="hybrid_<?=isset($tab_suffix)?$tab_suffix:''?>">Apply small keyword/key-point boost after AI (hybrid)</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label">Custom instructions for AI</label>
								<div class="col-md-6">
									<textarea name="ai_custom_instructions" class="portal-input" rows="2"><?=html_escape($sub['ai_custom_instructions'])?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label">Show students</label>
								<div class="col-md-6">
									<div class="checkbox-custom checkbox-primary"><input type="checkbox" name="student_show_obtained_marks" value="1" <?= !empty($sub['student_show_obtained_marks']) ? 'checked' : '' ?> id="ssm_<?=isset($tab_suffix)?$tab_suffix:''?>"><label for="ssm_<?=isset($tab_suffix)?$tab_suffix:''?>">Obtained marks</label></div>
									<div class="checkbox-custom checkbox-primary"><input type="checkbox" name="student_show_feedback" value="1" <?= !empty($sub['student_show_feedback']) ? 'checked' : '' ?> id="ssf_<?=isset($tab_suffix)?$tab_suffix:''?>"><label for="ssf_<?=isset($tab_suffix)?$tab_suffix:''?>">Short feedback</label></div>
									<div class="checkbox-custom checkbox-primary"><input type="checkbox" name="student_show_strengths" value="1" <?= !empty($sub['student_show_strengths']) ? 'checked' : '' ?> id="sss_<?=isset($tab_suffix)?$tab_suffix:''?>"><label for="sss_<?=isset($tab_suffix)?$tab_suffix:''?>">Strengths</label></div>
									<div class="checkbox-custom checkbox-primary"><input type="checkbox" name="student_show_weaknesses" value="1" <?= !empty($sub['student_show_weaknesses']) ? 'checked' : '' ?> id="ssw_<?=isset($tab_suffix)?$tab_suffix:''?>"><label for="ssw_<?=isset($tab_suffix)?$tab_suffix:''?>">Weaknesses</label></div>
									<div class="checkbox-custom checkbox-primary"><input type="checkbox" name="student_show_breakdown" value="1" <?= !empty($sub['student_show_breakdown']) ? 'checked' : '' ?> id="ssb_<?=isset($tab_suffix)?$tab_suffix:''?>"><label for="ssb_<?=isset($tab_suffix)?$tab_suffix:''?>">Criterion breakdown</label></div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"><?=html_escape($explVal)?></textarea>
								</div>
							</div>
