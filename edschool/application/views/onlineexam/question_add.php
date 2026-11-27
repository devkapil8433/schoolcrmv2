<style type="text/css">
	.qb-two-column .text-base font-semibold text-slate-900 flex items-center gap-2 {
		text-transform: capitalize;
	}
	.qb-filter-toolbar .form-group {
		margin-bottom: 0;
	}
	.checkbox-replace {
		padding-left: 0px !important;
	}
	.note-editor .modal-content {
		padding: 10px;
	}
	.note-editor .modal {
		z-index: 1150;
	}
</style>
<?php
$selected_branch_id = set_value('branch_id', !empty($prefill_branch_id) ? $prefill_branch_id : $branch_id);
$selected_class_id = set_value('class_id', !empty($prefill_class_id) ? $prefill_class_id : '');
$selected_section_id = set_value('section_id', !empty($prefill_section_id) ? $prefill_section_id : '');
$selected_subject_id = set_value('subject_id', !empty($prefill_subject_id) ? $prefill_subject_id : '');
$selected_exercise_id = set_value('exercise_id', !empty($prefill_exercise_id) ? $prefill_exercise_id : '');
$selected_lesson_id = set_value('lesson_id', !empty($prefill_lesson_id) ? $prefill_lesson_id : '');
/** Branch for loading class list: GET prefill/superadmin must win over empty session branch */
$filter_branch_id = !empty($selected_branch_id) ? $selected_branch_id : $branch_id;
$fixedQuestionTypes = $this->onlineexam_model->question_type();
$branchTypeById = array();
if (!empty($branch_question_types) && is_array($branch_question_types)) {
	foreach ($branch_question_types as $qtRow) {
		$qid = isset($qtRow['question_type_id']) ? (int) $qtRow['question_type_id'] : 0;
		if ($qid > 0) {
			$branchTypeById[$qid] = $qtRow;
		}
	}
}
$extraQuestionTypes = array();
foreach (array(6, 7) as $fixedId) {
	if (!isset($fixedQuestionTypes[(string) $fixedId])) {
		continue;
	}
	$extraQuestionTypes[] = array(
		'question_type_id' => $fixedId,
		'name' => !empty($branchTypeById[$fixedId]['name']) ? $branchTypeById[$fixedId]['name'] : $fixedQuestionTypes[(string) $fixedId],
		'marks' => isset($branchTypeById[$fixedId]['marks']) ? (int) $branchTypeById[$fixedId]['marks'] : 1,
	);
}
$textDescriptiveTypes = array();
foreach (array(8, 9, 10, 11) as $fixedId) {
	if (!isset($fixedQuestionTypes[(string) $fixedId])) {
		continue;
	}
	$textDescriptiveTypes[] = array(
		'question_type_id' => $fixedId,
		'name' => !empty($branchTypeById[$fixedId]['name']) ? $branchTypeById[$fixedId]['name'] : $fixedQuestionTypes[(string) $fixedId],
		'marks' => isset($branchTypeById[$fixedId]['marks']) ? (int) $branchTypeById[$fixedId]['marks'] : 1,
	);
}
?>
<div class="row qb-two-column">
	<div class="col-md-12 col-sm-12">
		<section class="panel">
		<?php echo form_open('onlineexam/question_add', array('method' => 'get', 'class' => 'validate', 'id' => 'questionAddFilterForm'));?>
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-sliders"></i> Options</h4>
				<div class="flex items-center gap-2">
					<a href="javascript:void(0);" onclick="window.history.back();" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-arrow-left"></i> <?=translate('back')?>
					</a>
					<a href="<?=base_url('onlineexam/image_database')?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-images"></i> Image DB
					</a>
					<a href="<?=base_url('onlineexam/question')?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-list"></i> <?=translate('question') . " " . translate('list')?>
					</a>
				</div>
			</header>
			<div class="panel-body">
				<?php
				$arrayExerciseFilter = $this->onlineexam_model->exercise($filter_branch_id, $selected_class_id, $selected_subject_id, $selected_exercise_id);
				$filter_exercise_label = ($selected_exercise_id && isset($arrayExerciseFilter[$selected_exercise_id])) ? $arrayExerciseFilter[$selected_exercise_id] : '—';
				$qb_context_ready = !empty($selected_branch_id) && !empty($selected_class_id) && !empty($selected_section_id) && !empty($selected_subject_id);
				$qb_lesson_label = '';
				if (!empty($selected_lesson_id) && !empty($digital_diary_lessons) && is_array($digital_diary_lessons)) {
					foreach ($digital_diary_lessons as $lrow) {
						if ((int) $lrow['id'] === (int) $selected_lesson_id) {
							$qb_lesson_label = trim($lrow['lesson_no'] . '. ' . $lrow['lesson_name']);
							break;
						}
					}
				}
				?>
				<?php if ($qb_context_ready): ?>
				<div id="qbOptionsSummary" class="clearfix">
					<button type="button" id="qbOptionsChangeBtn" class="btn-portal-secondary btn-portal-sm pull-right">
						<i class="fas fa-pen"></i> Change
					</button>
					<div style="line-height: 30px;">
						<?php if (is_superadmin_loggedin()): ?>
						<span class="label label-default" title="<?=translate('branch')?>"><i class="fas fa-code-branch"></i> <?=html_escape(get_type_name_by_id('branch', $selected_branch_id))?></span>
						<?php endif; ?>
						<span class="label label-primary" title="<?=translate('class')?> / <?=translate('section')?>"><i class="fas fa-school"></i> <?=html_escape(get_type_name_by_id('class', $selected_class_id))?> &mdash; <?=html_escape(get_type_name_by_id('section', $selected_section_id))?></span>
						<span class="label label-info" title="<?=translate('subject')?>"><i class="fas fa-book-open"></i> <?=html_escape(get_type_name_by_id('subject', $selected_subject_id))?></span>
						<span class="label label-warning" title="<?=translate('exercise')?>"><i class="fas fa-layer-group"></i> <?=html_escape($filter_exercise_label)?></span>
						<?php if ($qb_lesson_label !== ''): ?>
						<span class="label label-success" title="<?=translate('lesson')?>"><i class="fas fa-list-ol"></i> <?=html_escape($qb_lesson_label)?></span>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>
				<div id="qbOptionsFormRow" <?= $qb_context_ready ? 'style="display:none;"' : '' ?>>
				<div class="qb-filter-toolbar" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin-top:<?= $qb_context_ready ? '12px' : '0' ?>;">
					<?php if (!is_superadmin_loggedin()): ?>
					<input type="hidden" name="branch_id" id="branch_id" value="<?= (int) $filter_branch_id ?>">
					<?php endif; ?>
					<?php if (is_superadmin_loggedin()): ?>
					<div style="flex:1 1 0; min-width:110px;">
						<div class="form-group">
							<label class="portal-label"><i class="fas fa-code-branch text-muted"></i> <?=translate('branch')?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, $selected_branch_id, "class='portal-input' id='branch_id'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
						</div>
					</div>
					<?php endif; ?>

					<div style="flex:1 1 0; min-width:110px;">
						<div class="form-group">
							<label class="portal-label"><i class="fas fa-school text-muted"></i> <?=translate('class')?> <span class="required">*</span></label>
							<?php
								$arrayClass = $this->app_lib->getClass($filter_branch_id);
								echo form_dropdown("class_id", $arrayClass, $selected_class_id, "class='portal-input' id='class_id'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div style="flex:1 1 0; min-width:110px;">
						<div class="form-group">
							<label class="portal-label"><i class="fas fa-users text-muted"></i> <?=translate('section')?> <span class="required">*</span></label>
							<?php
								$arraySection = $this->app_lib->getSections($selected_class_id, false);
								echo form_dropdown("section_id", $arraySection, $selected_section_id, "class='portal-input' id='section_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div style="flex:1 1 0; min-width:110px;">
						<div class="form-group">
							<label class="portal-label"><i class="fas fa-book-open text-muted"></i> <?=translate('subject')?> <span class="required">*</span></label>
							<?php
								if(!empty($selected_class_id)) {
									$arraySubject = array("" => translate('select'));
									$query = $this->subject_model->getSubjectByClassSection($selected_class_id, $selected_section_id);
									$subjects = $query->result_array();
									foreach ($subjects as $row){
										$subjectID = $row['subject_id'];
										$arraySubject[$subjectID] = $row['subjectname'];
									}
								} else {
									$arraySubject = array("" => translate('select_class_first'));
								}
								echo form_dropdown("subject_id", $arraySubject, $selected_subject_id, "class='portal-input' id='subject_id'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div style="flex:1 1 0; min-width:110px;">
						<div class="form-group">
							<label class="portal-label"><i class="fas fa-layer-group text-muted"></i> <?=translate('exercise')?> <span class="text-muted">(optional)</span></label>
							<?php
								echo form_dropdown('exercise_id', $arrayExerciseFilter, $selected_exercise_id, "class='portal-input' id='filter_exercise_id'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
						</div>
					</div>
					<?php if (!empty($digital_diary_lesson_enabled)) { ?>
					<div style="flex:1 1 0; min-width:110px;">
						<div class="form-group">
							<label class="portal-label"><i class="fas fa-list-ol text-muted"></i> <?=translate('lesson')?></label>
							<?php
							$arrayLesson = array('' => translate('select'));
							if (!empty($digital_diary_lessons) && is_array($digital_diary_lessons)) {
								foreach ($digital_diary_lessons as $lrow) {
									$lid = (int) $lrow['id'];
									$arrayLesson[$lid] = trim($lrow['lesson_no'] . '. ' . $lrow['lesson_name']);
								}
							}
							echo form_dropdown('lesson_id', $arrayLesson, $selected_lesson_id, "class='portal-input' id='lesson_id' data-plugin-selectTwo data-width='100%' ");
							?>
						</div>
					</div>
					<?php } ?>
					<div style="flex:0 0 auto;">
						<div class="form-group">
							<button type="submit" class="btn-portal-secondary"><i class="fas fa-check"></i> Apply</button>
						</div>
					</div>
				</div>
				</div>
			</div>
			<?php echo form_close();?>
			<p class="help-block text-muted mb-sm" style="padding:0 15px;">Questions are organized by Class → Subject → Lesson → Type. Exercise is optional. Used for Question Papers and AS / Learning work.</p>
		</section>
<?php
$filter_ready = !empty($selected_branch_id) && !empty($selected_class_id) && !empty($selected_section_id) && !empty($selected_subject_id);
?>
		<div class="row">
			<div class="<?= $filter_ready ? 'col-md-7 col-sm-12' : 'col-md-12' ?>">
<?php if ($filter_ready) { ?>
		<section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?=translate('add') . ' ' . translate('question')?></h4>
			</header>
			<div class="panel-body">
				<div class="form-group">
					<label class="control-label"><?=translate('question_type')?> <span class="required">*</span></label>
					<?php
						$qtIconMap = array(
							1 => 'far fa-circle-dot',
							2 => 'fas fa-list-check',
							3 => 'fas fa-toggle-on',
							8 => 'fas fa-i-cursor',
							9 => 'fas fa-grip-lines',
							10 => 'fas fa-align-left',
							11 => 'fas fa-align-justify',
							5 => 'fas fa-right-left',
							6 => 'fas fa-book-open-reader',
							7 => 'fas fa-pen-fancy',
						);
						$arrayQuestionTypeSelect = array(
							array('id' => 1, 'name' => 'Single Choice'),
							array('id' => 2, 'name' => 'Multiple Choice'),
							array('id' => 3, 'name' => 'True/False'),
						);
						foreach ($textDescriptiveTypes as $qtItem) {
							$arrayQuestionTypeSelect[] = array('id' => (int) $qtItem['question_type_id'], 'name' => $qtItem['name']);
						}
						$arrayQuestionTypeSelect[] = array('id' => 5, 'name' => 'Match');
						foreach ($extraQuestionTypes as $qtItem) {
							$arrayQuestionTypeSelect[] = array('id' => (int) $qtItem['question_type_id'], 'name' => $qtItem['name']);
						}
					?>
					<select name="ui_question_type" id="ui_question_type" class="form-control">
						<option value="" data-icon="fas fa-layer-group"><?=translate('select')?> / All types</option>
						<?php foreach ($arrayQuestionTypeSelect as $qtOpt):
							$qtIcon = isset($qtIconMap[$qtOpt['id']]) ? $qtIconMap[$qtOpt['id']] : 'far fa-circle-question';
						?>
						<option value="<?= (int) $qtOpt['id'] ?>" data-icon="<?=html_escape($qtIcon)?>"><?=html_escape($qtOpt['name'])?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<hr class="solid mb-md mt-md">
				<div class="qt-form-panels">
					<div class="qt-form-panel" data-qt="1" id="single">
						<?php echo form_open_multipart('onlineexam/question_save', array('class' => 'form-bordered form-horizontal frm-submit-question'));?>
							<input type="hidden" name="branch_id" value="<?php echo $selected_branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo $selected_class_id ?>">
							<input type="hidden" name="section_id" value="<?php echo $selected_section_id ?>">
							<input type="hidden" name="subject_id" value="<?php echo $selected_subject_id ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id ?>">
							<input type="hidden" name="question_type" value="1">
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										$arrayLevel = $this->onlineexam_model->question_level();
										echo form_dropdown("question_level", $arrayLevel, set_value('question_level'), "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?></label>
								<div class="col-md-6">
									<input type="hidden" name="exercise_id" value="<?= (int) $selected_exercise_id ?>">
									<p class="portal-input-static mb-none"><?= html_escape($filter_exercise_label) ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" rows="2" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 1 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option1" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 2 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option2" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 3</label>
								<div class="col-md-6">
									<textarea name="option3" class="question_note"></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 4</label>
								<div class="col-md-6">
									<textarea name="option4" class="question_note"></textarea>
								</div>
							</div>
							<div class="form-group">
							   <label class="col-md-3 portal-label"><?=translate('answer')?> <span class="required">*</span></label>
							   <div class="col-md-6 mb-lg">
							       <div class="radio-custom radio-success radio-inline mb-xs">
							           <input type="radio" value="1" name="answer" id="opt1">
							           <label for="opt1"><?=translate('option')?> 1</label>
							       </div>
							       <div class="radio-custom radio-success radio-inline">
							           <input type="radio" value="2" name="answer" id="opt2">
							           <label for="opt2"><?=translate('option')?> 2</label>
							       </div>
							       <div class="radio-custom radio-success radio-inline">
							           <input type="radio" value="3" name="answer" id="opt3">
							           <label for="opt3"><?=translate('option')?> 3</label>
							       </div>
							       <div class="radio-custom radio-success radio-inline mb-none">
							           <input type="radio" value="4" name="answer" id="opt4">
							           <label for="opt4"><?=translate('option')?> 4</label>
							       </div>
							       <div>
							       	<span class="error"></span>
							       </div>
							   </div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"></textarea>
								</div>
							</div>
							<footer class="panel-footer">
								<div class="row">
									<div class="col-md-offset-3 col-md-2">
										<button type="submit" class="btn-portal-secondary w-full" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
											<i class="fas fa-plus-circle"></i> <?=translate('save')?>
										</button>
									</div>
								</div>
							</footer>
						<?php echo form_close(); ?>
					</div>
					<div class="qt-form-panel" data-qt="2" id="multiple" style="display:none;">
						<?php echo form_open_multipart('onlineexam/question_save', array('class' => 'form-bordered form-horizontal frm-submit-question'));?>
							<input type="hidden" name="branch_id" value="<?php echo $selected_branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo $selected_class_id ?>">
							<input type="hidden" name="section_id" value="<?php echo $selected_section_id ?>">
							<input type="hidden" name="subject_id" value="<?php echo $selected_subject_id ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id ?>">
							<input type="hidden" name="question_type" value="2">
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("question_level", $arrayLevel, set_value('question_level'), "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?></label>
								<div class="col-md-6">
									<input type="hidden" name="exercise_id" value="<?= (int) $selected_exercise_id ?>">
									<p class="portal-input-static mb-none"><?= html_escape($filter_exercise_label) ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 1 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option1" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 2 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option2" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 3 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option3" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 4 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option4" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
							   <label class="col-md-3 portal-label">Answer <span class="required">*</span></label>
							   <div class="col-md-6 mb-lg">
									<div class="checkbox-replace radio-inline">
										<label class="i-checks"><input type="checkbox" name="answer[]" value="1"><i></i> <?=translate('option')?> 1</label>
									</div>
									<div class="checkbox-replace radio-inline">
										<label class="i-checks"><input type="checkbox" name="answer[]" value="2"><i></i> <?=translate('option')?> 2</label>
									</div>
									<div class="checkbox-replace radio-inline">
										<label class="i-checks"><input type="checkbox" name="answer[]" value="3"><i></i> <?=translate('option')?> 3</label>
									</div>
									<div class="checkbox-replace radio-inline">
										<label class="i-checks"><input type="checkbox" name="answer[]" value="4"><i></i> <?=translate('option')?> 4</label>
									</div>
									<div>
							      	<span class="error"></span>
							      </div>
							   </div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"></textarea>
								</div>
							</div>
							<footer class="panel-footer">
								<div class="row">
									<div class="col-md-offset-3 col-md-2">
										<button type="submit" class="btn-portal-secondary w-full" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
											<i class="fas fa-plus-circle"></i> <?=translate('save')?>
										</button>
									</div>
								</div>
							</footer>
						<?php echo form_close(); ?>
					</div>
					<div class="qt-form-panel" data-qt="3" id="true_false" style="display:none;">
						<?php echo form_open_multipart('onlineexam/question_save', array('class' => 'form-bordered form-horizontal frm-submit-question'));?>
							<input type="hidden" name="branch_id" value="<?php echo $selected_branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo $selected_class_id ?>">
							<input type="hidden" name="section_id" value="<?php echo $selected_section_id ?>">
							<input type="hidden" name="subject_id" value="<?php echo $selected_subject_id ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id ?>">
							<input type="hidden" name="question_type" value="3">

							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("question_level", $arrayLevel, set_value('question_level'), "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?></label>
								<div class="col-md-6">
									<input type="hidden" name="exercise_id" value="<?= (int) $selected_exercise_id ?>">
									<p class="portal-input-static mb-none"><?= html_escape($filter_exercise_label) ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
							   <label class="col-md-3 portal-label"><?=translate('answer')?> <span class="required">*</span></label>
							   <div class="col-md-6 mb-lg">
									<?php
										$arrayAnswer = array(
											'' => translate('select'), 
											'1' => translate('true'), 
											'2' => translate('false'), 
										);
										echo form_dropdown("answer", $arrayAnswer, set_value('answer'), "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
							      <span class="error"></span>
							   </div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"></textarea>
								</div>
							</div>
							<footer class="panel-footer">
								<div class="row">
									<div class="col-md-offset-3 col-md-2">
										<button type="submit" class="btn-portal-secondary w-full" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
											<i class="fas fa-plus-circle"></i> <?=translate('save')?>
										</button>
									</div>
								</div>
							</footer>
						<?php echo form_close(); ?>
					</div>
					<?php foreach ($textDescriptiveTypes as $qtItem) :
						$qtTabId = 'text_qt_' . (int) $qtItem['question_type_id'];
						$qtMarks = isset($qtItem['marks']) ? (int) $qtItem['marks'] : 1;
						$answerRows = ((int) $qtItem['question_type_id'] === 8) ? 2 : (((int) $qtItem['question_type_id'] === 9) ? 3 : (((int) $qtItem['question_type_id'] === 10) ? 4 : 6));
					?>
					<div class="qt-form-panel" data-qt="<?= (int) $qtItem['question_type_id'] ?>" id="<?=$qtTabId?>" style="display:none;">
						<?php echo form_open_multipart('onlineexam/question_save', array('class' => 'form-bordered form-horizontal frm-submit-question'));?>
							<input type="hidden" name="branch_id" value="<?php echo $selected_branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo $selected_class_id ?>">
							<input type="hidden" name="section_id" value="<?php echo $selected_section_id ?>">
							<input type="hidden" name="subject_id" value="<?php echo $selected_subject_id ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id ?>">
							<input type="hidden" name="question_type" value="<?= (int) $qtItem['question_type_id'] ?>">
							<input type="hidden" name="custom_question_type" value="<?=html_escape($qtItem['name'])?>">

							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("question_level", $arrayLevel, set_value('question_level'), "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?></label>
								<div class="col-md-6">
									<input type="hidden" name="exercise_id" value="<?= (int) $selected_exercise_id ?>">
									<p class="portal-input-static mb-none"><?= html_escape($filter_exercise_label) ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('answer')?> <span class="required">*</span></label>
								<div class="col-md-6 mb-md">
									<textarea name="answer" rows="<?= (int) $answerRows ?>" class="portal-input question-hi-input"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"></textarea>
								</div>
							</div>
							<footer class="panel-footer">
								<div class="row">
									<div class="col-md-offset-3 col-md-2">
										<button type="submit" class="btn-portal-secondary w-full" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
											<i class="fas fa-plus-circle"></i> <?=translate('save')?>
										</button>
									</div>
								</div>
							</footer>
						<?php echo form_close(); ?>
					</div>
					<?php endforeach; ?>
					<div class="qt-form-panel" data-qt="5" id="match" style="display:none;">
						<?php echo form_open_multipart('onlineexam/question_save', array('class' => 'form-bordered form-horizontal frm-submit-question'));?>
							<input type="hidden" name="branch_id" value="<?php echo $selected_branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo $selected_class_id ?>">
							<input type="hidden" name="section_id" value="<?php echo $selected_section_id ?>">
							<input type="hidden" name="subject_id" value="<?php echo $selected_subject_id ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id ?>">
							<input type="hidden" name="question_type" value="5">
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("question_level", $arrayLevel, set_value('question_level'), "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?></label>
								<div class="col-md-6">
									<input type="hidden" name="exercise_id" value="<?= (int) $selected_exercise_id ?>">
									<p class="portal-input-static mb-none"><?= html_escape($filter_exercise_label) ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> (<?=translate('instruction')?>) <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" rows="2" class="question_note"></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"></label>
								<div class="col-md-6">
									<p class="text-muted mb-sm">Enter pairs (min 4). Click "Add more" to add one pair at a time (max 10).</p>
								</div>
							</div>
							<div id="match-pairs-container">
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 1 <span class="required">*</span></label>
								<div class="col-md-3">
									<textarea name="match_left_1" rows="1" class="question_note" placeholder="Left column"></textarea>
									<span class="error"></span>
								</div>
								<div class="col-md-3">
									<textarea name="option1" rows="1" class="question_note" placeholder="Right column"></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 2 <span class="required">*</span></label>
								<div class="col-md-3">
									<textarea name="match_left_2" rows="1" class="question_note" placeholder="Left column"></textarea>
									<span class="error"></span>
								</div>
								<div class="col-md-3">
									<textarea name="option2" rows="1" class="question_note" placeholder="Right column"></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 3 <span class="required">*</span></label>
								<div class="col-md-3">
									<textarea name="match_left_3" rows="1" class="question_note" placeholder="Left column"></textarea>
									<span class="error"></span>
								</div>
								<div class="col-md-3">
									<textarea name="option3" rows="1" class="question_note" placeholder="Right column"></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 4 <span class="required">*</span></label>
								<div class="col-md-3">
									<textarea name="match_left_4" rows="1" class="question_note" placeholder="Left column"></textarea>
									<span class="error"></span>
								</div>
								<div class="col-md-3">
									<textarea name="option4" rows="1" class="question_note" placeholder="Right column"></textarea>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-5" style="display:none;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 5</label>
									<div class="col-md-3">
										<textarea name="match_left_5" rows="1" class="question_note" placeholder="Left column"></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option5" rows="1" class="question_note" placeholder="Right column"></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-6" style="display:none;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 6</label>
									<div class="col-md-3">
										<textarea name="match_left_6" rows="1" class="question_note" placeholder="Left column"></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option6" rows="1" class="question_note" placeholder="Right column"></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-7" style="display:none;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 7</label>
									<div class="col-md-3">
										<textarea name="match_left_7" rows="1" class="question_note" placeholder="Left column"></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option7" rows="1" class="question_note" placeholder="Right column"></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-8" style="display:none;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 8</label>
									<div class="col-md-3">
										<textarea name="match_left_8" rows="1" class="question_note" placeholder="Left column"></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option8" rows="1" class="question_note" placeholder="Right column"></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-9" style="display:none;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 9</label>
									<div class="col-md-3">
										<textarea name="match_left_9" rows="1" class="question_note" placeholder="Left column"></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option9" rows="1" class="question_note" placeholder="Right column"></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-10" style="display:none;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 10</label>
									<div class="col-md-3">
										<textarea name="match_left_10" rows="1" class="question_note" placeholder="Left column"></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option10" rows="1" class="question_note" placeholder="Right column"></textarea>
									</div>
								</div>
							</div>
							</div>
							<div class="form-group" id="match-add-more-wrap">
								<label class="col-md-3 portal-label"></label>
								<div class="col-md-6">
									<button type="button" class="btn-portal-secondary btn-sm" id="match-add-more-btn"><i class="fas fa-plus"></i> Add more</button>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"></textarea>
								</div>
							</div>
							<footer class="panel-footer">
								<div class="row">
									<div class="col-md-offset-3 col-md-2">
										<button type="submit" class="btn-portal-secondary w-full" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
											<i class="fas fa-plus-circle"></i> <?=translate('save')?>
										</button>
									</div>
								</div>
							</footer>
						<?php echo form_close(); ?>
					</div>
					<?php foreach ($extraQuestionTypes as $qtItem) :
						$qtTabId = 'custom_qt_' . (int) $qtItem['question_type_id'];
					?>
					<div class="qt-form-panel" data-qt="<?= (int) $qtItem['question_type_id'] ?>" id="<?=$qtTabId?>" style="display:none;">
						<?php echo form_open_multipart('onlineexam/question_save', array('class' => 'form-bordered form-horizontal frm-submit-question'));?>
							<input type="hidden" name="branch_id" value="<?php echo $selected_branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo $selected_class_id ?>">
							<input type="hidden" name="section_id" value="<?php echo $selected_section_id ?>">
							<input type="hidden" name="subject_id" value="<?php echo $selected_subject_id ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id ?>">
							<input type="hidden" name="question_type" value="<?= (int) $qtItem['question_type_id'] ?>">
							<input type="hidden" name="custom_question_type" value="<?=html_escape($qtItem['name'])?>">
							<?php
							$this->load->view('onlineexam/subjective_question_fields', array(
								'questions' => array('mark' => isset($qtItem['marks']) ? (int) $qtItem['marks'] : ''),
								'subjective' => null,
								'arrayLevel' => $arrayLevel,
								'arrayExercise' => $arrayExerciseFilter,
								'selected_exercise_id' => $selected_exercise_id,
								'exercise_label' => $filter_exercise_label,
								'hide_exercise_field' => true,
								'is_edit' => false,
								'tab_suffix' => 'add_' . (int) $qtItem['question_type_id'],
							));
							?>
							<footer class="panel-footer">
								<div class="row">
									<div class="col-md-offset-3 col-md-2">
										<button type="submit" class="btn-portal-secondary w-full" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
											<i class="fas fa-plus-circle"></i> <?=translate('save')?>
										</button>
									</div>
								</div>
							</footer>
						<?php echo form_close(); ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php } else { ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><?=translate('question') . " " . translate('add')?></h4>
			</header>
			<div class="panel-body">
				<p class="text-muted mb-none">Select branch, class, section, subject, and exercise above, then click <strong><?=translate('filter')?></strong> to load the question add form.</p>
			</div>
		</section>
	<?php } ?>
			</div>
			<?php if ($filter_ready) { ?>
			<div class="col-md-5 col-sm-12">
				<section class="panel">
					<header class="panel-heading">
						<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2">
							<i class="fas fa-file-circle-question"></i>
							Existing Questions
							<span class="label label-info" id="existingQuestionsTypeLabel" style="margin-left:6px;"></span>
						</h4>
					</header>
					<div class="panel-body">
						<div class="portal-table-wrap">
							<table class="table table-bordered table-hover table-condensed table-question-existing" cellpadding="0" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th class="no-sort"><?=translate('sl')?></th>
										<th><?=translate('question')?></th>
										<th><?=translate('type')?></th>
										<th><?=translate('level')?></th>
										<th><?=translate('action')?></th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</section>
			</div>
			<?php } ?>
		</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('#qbOptionsChangeBtn').on('click', function () {
			var $row = $('#qbOptionsFormRow');
			var opening = !$row.is(':visible');
			$row.slideToggle(180);
			$(this).find('i').toggleClass('fa-pen', !opening).toggleClass('fa-xmark', opening);
			$(this).contents().last()[0].textContent = opening ? ' Close' : ' Change';
		});

		function showQuestionTypePanel(typeId) {
			$('.qt-form-panel').hide();
			$('#qtFormHint').remove();
			if (!typeId) {
				$('.qt-form-panels').before('<p id="qtFormHint" class="text-muted mb-md">Select a question type above to add a new question.</p>');
				return;
			}
			$('.qt-form-panel[data-qt="' + typeId + '"]').show();
			if (typeof window.refreshQuestionHindiInput === 'function') {
				setTimeout(window.refreshQuestionHindiInput, 50);
			}
		}

		function qtOptionTemplate(state) {
			if (!state.id) {
				return state.text;
			}
			var icon = $(state.element).data('icon') || 'far fa-circle-question';
			return $('<span><i class="' + icon + '" style="margin-right:8px; width:16px; text-align:center;"></i>' + state.text + '</span>');
		}

		if ($.fn.select2) {
			$('#ui_question_type').select2({
				width: '100%',
				minimumResultsForSearch: Infinity,
				placeholder: '<?=translate('select')?> / All types',
				allowClear: true,
				templateResult: qtOptionTemplate,
				templateSelection: qtOptionTemplate
			});
		}

		$('#ui_question_type').on('change', function () {
			var typeId = $(this).val();
			showQuestionTypePanel(typeId);
			reloadExistingQuestionsByType(typeId);
		});

		var existingQuestionsTable = null;
		var existingQuestionsBaseUrl = '';
		<?php if ($filter_ready) { ?>
		existingQuestionsBaseUrl = 'onlineexam/getQuestionListForAddDT?branch_id=<?=(int)$selected_branch_id?>&class_id=<?=(int)$selected_class_id?>&section_id=<?=(int)$selected_section_id?>&subject_id=<?=(int)$selected_subject_id?>&exercise_id=<?=(int)$selected_exercise_id?>';
		<?php } ?>

		function updateExistingTypeLabel(typeId) {
			if (!typeId) {
				$('#existingQuestionsTypeLabel').text('All types');
				return;
			}
			var $opt = $('#ui_question_type option[value="' + typeId + '"]');
			var name = $opt.length ? $.trim($opt.text()) : '';
			$('#existingQuestionsTypeLabel').text(name);
		}

		function reloadExistingQuestionsByType(typeId) {
			updateExistingTypeLabel(typeId);
			if (!existingQuestionsBaseUrl) {
				return;
			}
			var urlPath = existingQuestionsBaseUrl;
			if (typeId) {
				urlPath += '&question_type=' + encodeURIComponent(typeId);
			}
			var fullUrl = base_url + urlPath;
			if (existingQuestionsTable) {
				existingQuestionsTable.ajax.url(fullUrl).load();
				return;
			}
			existingQuestionsTable = initDatatable('.table-question-existing', urlPath);
		}

		showQuestionTypePanel($('#ui_question_type').val() || '');
		<?php if ($filter_ready) { ?>
		reloadExistingQuestionsByType($('#ui_question_type').val() || '');
		<?php } ?>

		function qaGetBranchId() {
			var bid = $('#branch_id').val();
			return bid ? bid : '<?= (int) $filter_branch_id ?>';
		}

		function qaReloadLessons() {
			var $lesson = $('#lesson_id');
			if (!$lesson.length) {
				return;
			}
			var currentLessonID = $lesson.val();
			var classID = $('#class_id').val();
			var sectionID = $('#section_id').val();
			var subjectID = $('#subject_id').val();
			if (!classID || !sectionID || !subjectID) {
				$lesson.html('<option value=""><?=translate('select')?></option>').trigger('change');
				return;
			}
			$.ajax({
				url: base_url + 'digital_diary/ajax_today_work_lessons',
				type: 'POST',
				dataType: 'json',
				data: {
					branch_id: qaGetBranchId(),
					class_id: classID,
					section_id: sectionID,
					subject_id: subjectID
				},
				success: function (res) {
					var html = '<option value=""><?=translate('select')?></option>';
					if (res && res.lessons && res.lessons.length) {
						$.each(res.lessons, function (i, row) {
							var label = row.label || (row.lesson_no + '. ' + row.lesson_name);
							html += '<option value="' + row.id + '">' + label + '</option>';
						});
					}
					$lesson.html(html);
					if (currentLessonID && $lesson.find('option[value="' + currentLessonID + '"]').length) {
						$lesson.val(currentLessonID);
					}
					$lesson.trigger('change');
				}
			});
		}

		$("form.frm-submit-question").each(function(i, el)
		{
		  var $this = $(el);
		  $this.on('submit', function(e){
		      e.preventDefault();
		      var btn = $this.find('[type="submit"]');
		      var formEl = this;
		      // Sync Summernote content back to textareas so FormData includes it
		      $this.find('.question_note').each(function() {
		          try {
		              if ($(this).data('summernote') && typeof $(this).summernote === 'function') {
		                  $(this).val($(this).summernote('code'));
		              }
		          } catch (err) {}
		      });
		      btn.button('loading');
		      $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: new FormData(this),
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
		          success: function (data) {
		              $('.error').html("");
		              if (data.status == "fail") {
		                  $.each(data.error, function (index, value) {
		                      $this.find("[name='" + index + "']").parents('.form-group').find('.error').html(value);
		                  });
		              } else if (data.status == "access_denied") {
		                  window.location.href = base_url + "dashboard";
		                  return;
		              } else {
		                  if (typeof Swal !== 'undefined') {
		                      Swal.fire({
		                          toast: true,
		                          position: 'top-end',
		                          icon: 'success',
		                          title: data.message || '',
		                          showConfirmButton: false,
		                          timer: 8000
		                      });
		                  }
		                  $(el).trigger("reset");
		                  $("select[name='question_level'], select[name='answer']").val('').trigger('change');
		                  $this.find(".question_note").each(function(c, elm) {
		                  	try { $(elm).summernote('code', '<p><br></p>'); } catch (e) {}
		                  });
		                  if ($this.find('input[name="question_type"][value="5"]').length) {
		                  	$('.match-extra-pair').hide();
		                  	$('#match-add-more-btn').prop('disabled', false).html('<i class="fas fa-plus"></i> Add more');
		                  }
		                  if (existingQuestionsTable) {
		                  	existingQuestionsTable.ajax.reload(null, false);
		                  }
		              }
		          },
		          complete: function () {
		              try { btn.button('reset'); } catch (e) { btn.prop('disabled', false); }
		          },
		          error: function (xhr, status, err) {
		              var msg = 'Save failed. ';
		              if (xhr.status === 404) msg += 'URL not found.';
		              else if (xhr.status === 500) msg += 'Server error. Check required fields (Level, Exercise, Mark, Question, Options 1 & 2, Answer).';
		              else if (status === 'parsererror') msg += 'Invalid server response.';
		              else if (xhr.status) msg += 'Status: ' + xhr.status;
		              if (typeof Swal !== 'undefined') {
		                  Swal.fire({ icon: 'error', title: 'Error', text: msg });
		              } else {
		                  alert(msg);
		              }
		          }
		      });
		  });
		});

		function qaReloadExercises(selected) {
			if (!$('#filter_exercise_id').length) return;
			var branchID = $('#branch_id').length ? $('#branch_id').val() : '<?=(int)$filter_branch_id?>';
			var classID = $('#class_id').val();
			var subjectID = $('#subject_id').val();
			if (!branchID || !classID || !subjectID) {
				$('#filter_exercise_id').html('<option value=""><?=translate("select")?></option>').trigger('change');
				return;
			}
			$.post(base_url + 'onlineexam/getExercisesByClassSubject', {
				branch_id: branchID,
				class_id: classID,
				subject_id: subjectID,
				selected: selected || 0
			}, function (html) {
				$('#filter_exercise_id').html(html).trigger('change');
			});
		}

		$('#branch_id').on('change', function() {
			var branchID = $(this).val();
			getClassByBranch(branchID);
			$('#subject_id').html('').append('<option value=""><?=translate("select")?></option>').trigger('change');
			qaReloadExercises();
			qaReloadLessons();
		});

		$('#class_id').on('change', function() {
			var cid = $(this).val();
			if (cid !== undefined && cid !== null && cid !== '') {
				getSectionByClass(cid, 0);
			} else {
				$('#section_id').html('<option value=""><?=translate("select")?></option>').trigger('change');
			}
			$('#subject_id').html('<option value=""><?=translate("select")?></option>').trigger('change');
			qaReloadExercises();
			qaReloadLessons();
		});
		
		$('#section_id').on('change', function() {
			var classID = $('#class_id').val();
			var sectionID = $(this).val();
			if (!classID || !sectionID) {
				$('#subject_id').html('<option value=""><?=translate("select")?></option>').trigger('change');
				qaReloadLessons();
				return;
			}
			$.ajax({
				url: base_url + 'subject/getByClassSection',
				type: 'POST',
				data: {
					classID: classID,
					sectionID: sectionID
				},
				success: function (data) {
					$('#subject_id').html(data).trigger('change');
				}
			});
		});

		$('#subject_id').on('change', function() {
			qaReloadExercises();
			qaReloadLessons();
		});

		// Match: Add more — one option at a time (5 through 10, max 10)
		$('#match-add-more-btn').on('click', function() {
			var $btn = $(this);
			if ($btn.prop('disabled')) return;
			for (var n = 5; n <= 10; n++) {
				if ($('#match-option-' + n).is(':hidden')) {
					$('#match-option-' + n).show();
					if (n === 10) {
						$btn.prop('disabled', true).html('Max 10 options');
					} else {
						$btn.html('<i class="fas fa-plus"></i> Add more');
					}
					break;
				}
			}
		});
	});
</script>	