<style type="text/css">
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
<?php $widget = (is_superadmin_loggedin() ? 3 : 4);
$edit_lesson_default = (!empty($questions) && isset($questions['lesson_id'])) ? (int) $questions['lesson_id'] : 0;
$selected_lesson_id_edit = set_value('lesson_id', $edit_lesson_default);
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
		<?php echo form_open($this->uri->uri_string(), array('class' => 'validate'));?>
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><?=translate('select_ground')?></h4>
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
				<div class="row mb-sm">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="portal-label"><?=translate('branch')?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $questions['branch_id']), "class='portal-input' id='branch_id'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
						</div>
					</div>
					<?php endif; ?>

					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="portal-label"><?=translate('class')?> <span class="required">*</span></label>
							<?php
								$arrayClass = $this->app_lib->getClass($questions['branch_id']);
								echo form_dropdown("class_id", $arrayClass, set_value('class_id', $questions['class_id']), "class='portal-input' id='class_id' onchange='getSectionByClass(this.value,0)'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="portal-label"><?=translate('section')?> <span class="required">*</span></label>
							<?php
								$arraySection = $this->app_lib->getSections(set_value('class_id', $questions['class_id']), false);
								echo form_dropdown("section_id", $arraySection, set_value('section_id', $questions['section_id']), "class='portal-input' id='section_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div class="col-md-<?php echo $widget; ?>">
						<div class="form-group">
							<label class="portal-label"><?=translate('subject')?></label>
							<?php
								if(!empty(set_value('class_id', $questions['class_id']))) {
									$arraySubject = array("" => translate('select'));
									$query = $this->subject_model->getSubjectByClassSection(set_value('class_id', $questions['class_id']), set_value('section_id', $questions['section_id']));
									$subjects = $query->result_array();
									foreach ($subjects as $row){
										$subjectID = $row['subject_id'];
										$arraySubject[$subjectID] = $row['subjectname'];
									}
								} else {
									$arraySubject = array("" => translate('select_class_first'));
								}
								echo form_dropdown("subject_id", $arraySubject, set_value('subject_id', $questions['subject_id']), "class='portal-input' id='subject_id'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
				</div>
				<?php if (!empty($digital_diary_lesson_enabled)) { ?>
				<div class="row mb-sm">
					<div class="col-md-6 mb-sm">
						<div class="form-group">
							<label class="portal-label"><?=translate('lesson')?></label>
							<?php
							$arrayLessonEdit = array('' => translate('select'));
							if (!empty($digital_diary_lessons) && is_array($digital_diary_lessons)) {
								foreach ($digital_diary_lessons as $lrow) {
									$lid = (int) $lrow['id'];
									$arrayLessonEdit[$lid] = trim($lrow['lesson_no'] . '. ' . $lrow['lesson_name']);
								}
							}
							echo form_dropdown('lesson_id', $arrayLessonEdit, $selected_lesson_id_edit, "class='portal-input' id='lesson_id' data-plugin-selectTwo data-width='100%' ");
							?>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button type="submit" name="search" value="1" class="btn btn-portal-secondary w-full"> <i class="fas fa-filter"></i> <?=translate('filter')?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close();?>
		</section>
		
<?php if (!empty($questions)) { ?>
		<section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<div class="tabs-custom">
				<ul class="nav nav-tabs">
					<li class="<?php echo $questions['type'] == 1 ? 'active' : ''; ?>">
	                <a href="#single" data-toggle="tab">
	                    <i class="far fa-edit"></i> Single Choice
	                </a>
					</li>
					<li class="<?php echo $questions['type'] == 2 ? 'active' : ''; ?>">
	                <a href="#multiple" data-toggle="tab">
	                   <i class="far fa-edit"></i> Multiple Choice
	                </a>
					</li>
					<li class="<?php echo $questions['type'] == 3 ? 'active' : ''; ?>">
	                <a href="#true_false" data-toggle="tab">
	                   <i class="far fa-edit"></i> True/False
	                </a>
					</li>
					<?php if ((int) $questions['type'] === 4) { ?>
					<li class="active">
	                <a href="#descriptive" data-toggle="tab">
	                   <i class="far fa-edit"></i> Descriptive (legacy)
	                </a>
					</li>
					<?php } ?>
					<?php if (in_array((int) $questions['type'], array(8, 9, 10, 11), true)) {
						$tdTid = (int) $questions['type'];
						$tdLabel = $this->onlineexam_model->resolve_question_type_label(
							$tdTid,
							isset($questions['custom_question_type']) ? $questions['custom_question_type'] : ''
						);
					?>
					<li class="active">
						<a href="#edit_text_<?= $tdTid ?>" data-toggle="tab"><i class="far fa-edit"></i> <?= html_escape($tdLabel) ?></a>
					</li>
					<?php } ?>
					<li class="<?php echo $questions['type'] == 5 ? 'active' : ''; ?>">
	                <a href="#match" data-toggle="tab">
	                   <i class="far fa-edit"></i> Match
	                </a>
					</li>
					<?php if (in_array((int) $questions['type'], array(6, 7), true)) {
						$rwTid = (int) $questions['type'];
						$rwLabel = $rwTid === 6 ? 'Reading' : 'Writing';
					?>
					<li class="active">
						<a href="#edit_rw_<?= $rwTid ?>" data-toggle="tab"><i class="far fa-edit"></i> <?= html_escape($rwLabel) ?></a>
					</li>
					<?php } ?>
				</ul>
				<div class="tab-content">
					<div class="tab-pane box <?php echo $questions['type'] == 1 ? 'active' : ''; ?>" id="single">
						<?php echo form_open_multipart('onlineexam/question_edit_save', array('class' => 'form-bordered form-horizontal frm-submit-data'));
							$answer1 = "";
							if ($questions['type'] == 1) {
								$answer1 = $questions['answer'];
							}
						?>
							<input type="hidden" name="question_id" value="<?php echo $questions['id'] ?>">
							<input type="hidden" name="branch_id" value="<?php echo $branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo set_value('class_id') ?>">
							<input type="hidden" name="section_id" value="<?php echo set_value('section_id') ?>">
							<input type="hidden" name="subject_id" value="<?php echo set_value('subject_id') ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id_edit ?>">
							<input type="hidden" name="question_type" value="1">
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										$arrayLevel = $this->onlineexam_model->question_level();
										echo form_dropdown("question_level", $arrayLevel, $questions['level'], "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										$arrayExercise = $this->onlineexam_model->exercise($questions['branch_id'], set_value('class_id', $questions['class_id']), set_value('subject_id', $questions['subject_id']), set_value('exercise_id', $questions['exercise_id']));
										echo form_dropdown("exercise_id", $arrayExercise, set_value('exercise_id', $questions['exercise_id']), "class='portal-input exercise-select'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('mark')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<input type="text" class="portal-input" name="mark" autocomplete="off" value="<?php echo $questions['mark'] ?>">
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" rows="2" class="question_note"><?php echo $questions['question'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 1 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option1" class="question_note"><?php echo $questions['opt_1'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 2 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option2" class="question_note"><?php echo $questions['opt_2'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 3</label>
								<div class="col-md-6">
									<textarea name="option3" class="question_note"><?php echo $questions['opt_3'] ?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 4</label>
								<div class="col-md-6">
									<textarea name="option4" class="question_note"><?php echo $questions['opt_4'] ?></textarea>
								</div>
							</div>
							<div class="form-group">
							   <label class="col-md-3 portal-label"><?=translate('answer')?> <span class="required">*</span></label>
							   <div class="col-md-6 mb-lg">
							       <div class="radio-custom radio-success radio-inline mb-xs">
							           <input type="radio" value="1" name="answer" id="opt1" <?php echo ($answer1 == 1 ? "checked" : ""); ?>>
							           <label for="opt1"><?=translate('option')?> 1</label>
							       </div>
							       <div class="radio-custom radio-success radio-inline">
							           <input type="radio" value="2" name="answer" id="opt2" <?php echo ($answer1 == 2 ? "checked" : ""); ?>>
							           <label for="opt2"><?=translate('option')?> 2</label>
							       </div>
							       <div class="radio-custom radio-success radio-inline">
							           <input type="radio" value="3" name="answer" id="opt3" <?php echo ($answer1 == 3 ? "checked" : ""); ?>>
							           <label for="opt3"><?=translate('option')?> 3</label>
							       </div>
							       <div class="radio-custom radio-success radio-inline mb-none">
							           <input type="radio" value="4" name="answer" id="opt4" <?php echo ($answer1 == 4 ? "checked" : ""); ?>>
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
									<textarea name="explanation" class="question_note"><?php echo isset($questions['explanation']) ? $questions['explanation'] : '' ?></textarea>
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
					<div class="tab-pane box <?php echo $questions['type'] == 2 ? 'active' : ''; ?>" id="multiple">
						<?php echo form_open_multipart('onlineexam/question_edit_save', array('class' => 'form-bordered form-horizontal frm-submit-data'));
							$answer2 = [];
							if ($questions['type'] == 2) {
								$answer2 = json_decode($questions['answer'], true);
							}
						?>
							<input type="hidden" name="question_id" value="<?php echo $questions['id'] ?>">
							<input type="hidden" name="branch_id" value="<?php echo $branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo set_value('class_id') ?>">
							<input type="hidden" name="section_id" value="<?php echo set_value('section_id') ?>">
							<input type="hidden" name="subject_id" value="<?php echo set_value('subject_id') ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id_edit ?>">
							<input type="hidden" name="question_type" value="2">
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("question_level", $arrayLevel, $questions['level'], "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("exercise_id", $arrayExercise, set_value('exercise_id', $questions['exercise_id']), "class='portal-input exercise-select'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('mark')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<input type="text" class="portal-input" name="mark" autocomplete="off" value="<?php echo $questions['mark'] ?>">
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" class="question_note"><?php echo $questions['question'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 1 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option1" class="question_note"><?php echo $questions['opt_1'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 2 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option2" class="question_note"><?php echo $questions['opt_2'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 3 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option3" class="question_note"><?php echo $questions['opt_3'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 4 <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="option4" class="question_note"><?php echo $questions['opt_4'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
							   <label class="col-md-3 portal-label">Answer <span class="required">*</span></label>
							   <div class="col-md-6 mb-lg">
									<div class="checkbox-replace radio-inline">
										<label class="i-checks"><input type="checkbox" name="answer[]" <?php echo (in_array(1, $answer2) ? 'checked' : ''); ?> value="1"><i></i> <?=translate('option')?> 1</label>
									</div>
									<div class="checkbox-replace radio-inline">
										<label class="i-checks"><input type="checkbox" name="answer[]" <?php echo (in_array(2, $answer2) ? 'checked' : ''); ?> value="2"><i></i> <?=translate('option')?> 2</label>
									</div>
									<div class="checkbox-replace radio-inline">
										<label class="i-checks"><input type="checkbox" name="answer[]" <?php echo (in_array(3, $answer2) ? 'checked' : ''); ?> value="3"><i></i> <?=translate('option')?> 3</label>
									</div>
									<div class="checkbox-replace radio-inline">
										<label class="i-checks"><input type="checkbox" name="answer[]" <?php echo (in_array(4, $answer2) ? 'checked' : ''); ?> value="4"><i></i> <?=translate('option')?> 4</label>
									</div>
									<div>
							      	<span class="error"></span>
							      </div>
							   </div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"><?php echo isset($questions['explanation']) ? $questions['explanation'] : '' ?></textarea>
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
					<div class="tab-pane box <?php echo $questions['type'] == 3 ? 'active' : ''; ?>" id="true_false">
						<?php echo form_open_multipart('onlineexam/question_edit_save', array('class' => 'form-bordered form-horizontal frm-submit-data'));?>
							<input type="hidden" name="question_id" value="<?php echo $questions['id'] ?>">
							<input type="hidden" name="branch_id" value="<?php echo $branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo set_value('class_id') ?>">
							<input type="hidden" name="section_id" value="<?php echo set_value('section_id') ?>">
							<input type="hidden" name="subject_id" value="<?php echo set_value('subject_id') ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id_edit ?>">
							<input type="hidden" name="question_type" value="3">

							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("question_level", $arrayLevel, $questions['level'], "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("exercise_id", $arrayExercise, set_value('exercise_id', $questions['exercise_id']), "class='portal-input exercise-select'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('mark')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<input type="text" class="portal-input" name="mark" autocomplete="off" value="<?php echo $questions['mark'] ?>">
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" class="question_note"><?php echo $questions['question'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
							   <label class="col-md-3 portal-label"><?=translate('answer')?> <span class="required">*</span></label>
							   <div class="col-md-6 mb-lg">
									<?php
										$answer3 = "";
										if ($questions['type'] == 3) {
											$answer3 = $questions['answer'];
										}
										$arrayAnswer = array(
											'' => translate('select'), 
											'1' => translate('true'), 
											'2' => translate('false'), 
										);
										echo form_dropdown("answer", $arrayAnswer, $answer3, "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
							      <span class="error"></span>
							   </div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"><?php echo isset($questions['explanation']) ? $questions['explanation'] : '' ?></textarea>
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
					<div class="tab-pane box <?php echo $questions['type'] == 4 ? 'active' : ''; ?>" id="descriptive">
						<?php echo form_open_multipart('onlineexam/question_edit_save', array('class' => 'form-bordered form-horizontal frm-submit-data'));
							$answer4 = "";
							if ($questions['type'] == 4) {
								$answer4 = $questions['answer'];
							}
						?>
							<input type="hidden" name="question_id" value="<?php echo $questions['id'] ?>">
							<input type="hidden" name="branch_id" value="<?php echo $branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo set_value('class_id') ?>">
							<input type="hidden" name="section_id" value="<?php echo set_value('section_id') ?>">
							<input type="hidden" name="subject_id" value="<?php echo set_value('subject_id') ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id_edit ?>">
							<input type="hidden" name="question_type" value="4">

							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("question_level", $arrayLevel, $questions['level'], "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("exercise_id", $arrayExercise, set_value('exercise_id', $questions['exercise_id']), "class='portal-input exercise-select'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('mark')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<input type="text" class="portal-input" name="mark" autocomplete="off" value="<?php echo $questions['mark'] ?>">
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" class="question_note"><?php echo $questions['question'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('answer')?> <span class="required">*</span></label>
								<div class="col-md-6 mb-md">
									<textarea name="answer" rows="4" class="portal-input question-hi-input"><?php echo $answer4 ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"><?php echo isset($questions['explanation']) ? $questions['explanation'] : '' ?></textarea>
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
					<?php
					if (in_array((int) $questions['type'], array(8, 9, 10, 11), true)) {
						$tdTid = (int) $questions['type'];
						$tdLabel = $this->onlineexam_model->resolve_question_type_label(
							$tdTid,
							isset($questions['custom_question_type']) ? $questions['custom_question_type'] : ''
						);
						$answerTd = $questions['answer'];
						$answerRows = ($tdTid === 8) ? 2 : (($tdTid === 9) ? 3 : (($tdTid === 10) ? 4 : 6));
					?>
					<div class="tab-pane box active" id="edit_text_<?= $tdTid ?>">
						<?php echo form_open_multipart('onlineexam/question_edit_save', array('class' => 'form-bordered form-horizontal frm-submit-data')); ?>
							<input type="hidden" name="question_id" value="<?php echo $questions['id'] ?>">
							<input type="hidden" name="branch_id" value="<?php echo $branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo set_value('class_id') ?>">
							<input type="hidden" name="section_id" value="<?php echo set_value('section_id') ?>">
							<input type="hidden" name="subject_id" value="<?php echo set_value('subject_id') ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id_edit ?>">
							<input type="hidden" name="question_type" value="<?= $tdTid ?>">
							<input type="hidden" name="custom_question_type" value="<?= html_escape($tdLabel) ?>">

							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("question_level", $arrayLevel, $questions['level'], "class='portal-input'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php
										echo form_dropdown("exercise_id", $arrayExercise, set_value('exercise_id', $questions['exercise_id']), "class='portal-input exercise-select'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('mark')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<input type="text" class="portal-input" name="mark" autocomplete="off" value="<?php echo $questions['mark'] ?>">
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" class="question_note"><?php echo $questions['question'] ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('answer')?> <span class="required">*</span></label>
								<div class="col-md-6 mb-md">
									<textarea name="answer" rows="<?= (int) $answerRows ?>" class="portal-input question-hi-input"><?php echo $answerTd ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"><?php echo isset($questions['explanation']) ? $questions['explanation'] : '' ?></textarea>
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
					<?php } ?>
					<?php
					if (in_array((int) $questions['type'], array(6, 7), true)) {
						$rwTid = (int) $questions['type'];
						$rwLabel = $rwTid === 6 ? 'Reading' : 'Writing';
						$qsRow = isset($question_subjective) && is_array($question_subjective) ? $question_subjective : null;
					?>
					<div class="tab-pane box active" id="edit_rw_<?= $rwTid ?>">
						<?php echo form_open_multipart('onlineexam/question_edit_save', array('class' => 'form-bordered form-horizontal frm-submit-data')); ?>
							<input type="hidden" name="question_id" value="<?php echo $questions['id'] ?>">
							<input type="hidden" name="branch_id" value="<?php echo $branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo set_value('class_id') ?>">
							<input type="hidden" name="section_id" value="<?php echo set_value('section_id') ?>">
							<input type="hidden" name="subject_id" value="<?php echo set_value('subject_id') ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id_edit ?>">
							<input type="hidden" name="question_type" value="<?= $rwTid ?>">
							<input type="hidden" name="custom_question_type" value="<?= html_escape($rwLabel) ?>">
							<?php
							$this->load->view('onlineexam/subjective_question_fields', array(
								'questions' => $questions,
								'subjective' => $qsRow,
								'arrayLevel' => $this->onlineexam_model->question_level(),
								'arrayExercise' => $this->onlineexam_model->exercise($questions['branch_id'], set_value('class_id', $questions['class_id']), set_value('subject_id', $questions['subject_id']), set_value('exercise_id', $questions['exercise_id'])),
								'selected_exercise_id' => set_value('exercise_id', $questions['exercise_id']),
								'is_edit' => true,
								'tab_suffix' => 'ed_' . $rwTid,
							));
							?>
							<footer class="panel-footer">
								<div class="row">
									<div class="col-md-offset-3 col-md-2">
										<button type="submit" class="btn-portal-secondary w-full" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
											<i class="fas fa-plus-circle"></i> <?= translate('save') ?>
										</button>
									</div>
								</div>
							</footer>
						<?php echo form_close(); ?>
					</div>
					<?php } ?>
					<div class="tab-pane box <?php echo $questions['type'] == 5 ? 'active' : ''; ?>" id="match">
						<?php
							$matchInstruction = '';
							$matchLeft = array_pad(array(), 10, '');
							$matchRight = array_pad(array(), 10, '');
							$matchShowOption = array(5 => false, 6 => false, 7 => false, 8 => false, 9 => false, 10 => false);
							if ($questions['type'] == 5) {
								$qDecoded = json_decode($questions['question'], true);
								if (!empty($qDecoded['instruction'])) $matchInstruction = $qDecoded['instruction'];
								if (!empty($qDecoded['left'])) $matchLeft = array_pad($qDecoded['left'], 10, '');
								if (!empty($qDecoded['right'])) {
									$matchRight = array_pad($qDecoded['right'], 10, '');
								} else {
									$matchRight = array(
										$questions['opt_1'], $questions['opt_2'], $questions['opt_3'],
										$questions['opt_4'], '', '', '', '', '', ''
									);
								}
								for ($n = 5; $n <= 10; $n++) {
									$i = $n - 1;
									$matchShowOption[$n] = !empty(trim(strip_tags($matchLeft[$i]))) || !empty(trim(strip_tags($matchRight[$i])));
								}
							}
						?>
						<?php echo form_open_multipart('onlineexam/question_edit_save', array('class' => 'form-bordered form-horizontal frm-submit-data'));?>
							<input type="hidden" name="question_id" value="<?php echo $questions['id'] ?>">
							<input type="hidden" name="branch_id" value="<?php echo $branch_id ?>">
							<input type="hidden" name="class_id" value="<?php echo set_value('class_id') ?>">
							<input type="hidden" name="section_id" value="<?php echo set_value('section_id') ?>">
							<input type="hidden" name="subject_id" value="<?php echo set_value('subject_id') ?>">
							<input type="hidden" name="lesson_id" value="<?php echo (int) $selected_lesson_id_edit ?>">
							<input type="hidden" name="question_type" value="5">
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question_level')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php echo form_dropdown("question_level", $arrayLevel, $questions['level'], "class='portal-input' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' "); ?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('exercise')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<?php echo form_dropdown("exercise_id", $arrayExercise, set_value('exercise_id', $questions['exercise_id']), "class='portal-input exercise-select' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' "); ?>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('mark')?> <span class="required">*</span></label>
								<div class="col-md-6">
									<input type="text" class="portal-input" name="mark" autocomplete="off" value="<?php echo $questions['mark'] ?>">
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('question')?> (<?=translate('instruction')?>) <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea name="question" class="question_note"><?php echo $matchInstruction ?></textarea>
									<span class="error"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"></label>
								<div class="col-md-6"><p class="text-muted mb-sm">Enter pairs (min 4). Click "Add more" to add one pair at a time (max 10).</p></div>
							</div>
							<div id="match-pairs-container">
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 1 <span class="required">*</span></label>
								<div class="col-md-3">
									<textarea name="match_left_1" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[0]) ? $matchLeft[0] : '' ?></textarea>
									<span class="error"></span>
								</div>
								<div class="col-md-3">
									<textarea name="option1" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[0]) ? $matchRight[0] : '' ?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 2 <span class="required">*</span></label>
								<div class="col-md-3">
									<textarea name="match_left_2" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[1]) ? $matchLeft[1] : '' ?></textarea>
									<span class="error"></span>
								</div>
								<div class="col-md-3">
									<textarea name="option2" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[1]) ? $matchRight[1] : '' ?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 3 <span class="required">*</span></label>
								<div class="col-md-3">
									<textarea name="match_left_3" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[2]) ? $matchLeft[2] : '' ?></textarea>
									<span class="error"></span>
								</div>
								<div class="col-md-3">
									<textarea name="option3" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[2]) ? $matchRight[2] : '' ?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('option')?> 4 <span class="required">*</span></label>
								<div class="col-md-3">
									<textarea name="match_left_4" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[3]) ? $matchLeft[3] : '' ?></textarea>
									<span class="error"></span>
								</div>
								<div class="col-md-3">
									<textarea name="option4" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[3]) ? $matchRight[3] : '' ?></textarea>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-5" style="display:<?php echo !empty($matchShowOption[5]) ? 'block' : 'none' ?>;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 5</label>
									<div class="col-md-3">
										<textarea name="match_left_5" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[4]) ? $matchLeft[4] : '' ?></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option5" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[4]) ? $matchRight[4] : '' ?></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-6" style="display:<?php echo !empty($matchShowOption[6]) ? 'block' : 'none' ?>;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 6</label>
									<div class="col-md-3">
										<textarea name="match_left_6" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[5]) ? $matchLeft[5] : '' ?></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option6" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[5]) ? $matchRight[5] : '' ?></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-7" style="display:<?php echo !empty($matchShowOption[7]) ? 'block' : 'none' ?>;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 7</label>
									<div class="col-md-3">
										<textarea name="match_left_7" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[6]) ? $matchLeft[6] : '' ?></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option7" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[6]) ? $matchRight[6] : '' ?></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-8" style="display:<?php echo !empty($matchShowOption[8]) ? 'block' : 'none' ?>;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 8</label>
									<div class="col-md-3">
										<textarea name="match_left_8" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[7]) ? $matchLeft[7] : '' ?></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option8" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[7]) ? $matchRight[7] : '' ?></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-9" style="display:<?php echo !empty($matchShowOption[9]) ? 'block' : 'none' ?>;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 9</label>
									<div class="col-md-3">
										<textarea name="match_left_9" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[8]) ? $matchLeft[8] : '' ?></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option9" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[8]) ? $matchRight[8] : '' ?></textarea>
									</div>
								</div>
							</div>
							<div class="match-extra-pair" id="match-option-10" style="display:<?php echo !empty($matchShowOption[10]) ? 'block' : 'none' ?>;">
								<div class="form-group">
									<label class="col-md-3 portal-label"><?=translate('option')?> 10</label>
									<div class="col-md-3">
										<textarea name="match_left_10" rows="1" class="question_note" placeholder="Left"><?php echo isset($matchLeft[9]) ? $matchLeft[9] : '' ?></textarea>
										<span class="error"></span>
									</div>
									<div class="col-md-3">
										<textarea name="option10" rows="1" class="question_note" placeholder="Right"><?php echo isset($matchRight[9]) ? $matchRight[9] : '' ?></textarea>
									</div>
								</div>
							</div>
							</div>
							<div class="form-group" id="match-add-more-wrap">
								<label class="col-md-3 portal-label"></label>
								<div class="col-md-6">
									<button type="button" class="btn-portal-secondary btn-sm" id="match-add-more-btn" <?php echo !empty($matchShowOption[10]) ? 'disabled' : '' ?>>
										<i class="fas fa-plus"></i> <?php echo !empty($matchShowOption[10]) ? 'Max 10 options' : 'Add more' ?>
									</button>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 portal-label"><?=translate('explanation')?></label>
								<div class="col-md-6">
									<textarea name="explanation" class="question_note"><?php echo isset($questions['explanation']) ? $questions['explanation'] : '' ?></textarea>
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
				</div>
			</div>
		</section>
	<?php } ?>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$("form.frm-submit-data").each(function(i, el)
		{
		  var $this = $(el);
		  $this.on('submit', function(e){
		      e.preventDefault();
		      var btn = $this.find('[type="submit"]');
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
		              }
		          },
		          complete: function () {
		              try { btn.button('reset'); } catch (e) { btn.prop('disabled', false); }
		          },
		          error: function (xhr, status) {
		              var msg = 'Save failed.';
		              if (status === 'parsererror') msg = 'Invalid server response.';
		              if (typeof Swal !== 'undefined') {
		                  Swal.fire({ icon: 'error', title: 'Error', text: msg });
		              } else {
		                  alert(msg);
		              }
		          }
		      });
		  });
		});

		$('#branch_id').on('change', function() {
			var branchID = $(this).val();
			getClassByBranch(branchID);
			$('#subject_id').html('').append('<option value=""><?=translate("select")?></option>');
			qeReloadExercises();
		});

		$('#class_id').on('change', function() {
			qeReloadExercises();
		});

		$('#section_id').on('change', function() {
			var classID = $('#class_id').val();
			var sectionID =$(this).val();
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
			qeReloadExercises();
		});

		function qeReloadExercises() {
			var branchID = $('#branch_id').length ? $('#branch_id').val() : '<?=(int)$questions["branch_id"]?>';
			var classID = $('#class_id').val();
			var subjectID = $('#subject_id').val();
			var selected = '<?=(int)set_value("exercise_id", $questions["exercise_id"])?>';
			if (!branchID || !classID || !subjectID) {
				$('.exercise-select').each(function () {
					$(this).html('<option value=""><?=translate("select")?></option>').trigger('change');
				});
				return;
			}
			$.post(base_url + 'onlineexam/getExercisesByClassSubject', {
				branch_id: branchID,
				class_id: classID,
				subject_id: subjectID,
				selected: selected
			}, function (html) {
				$('.exercise-select').each(function () {
					$(this).html(html).trigger('change');
				});
			});
		}

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

		$('a[data-toggle="tab"]').on('shown.bs.tab', function () {
			if (typeof window.refreshQuestionHindiInput === 'function') {
				setTimeout(window.refreshQuestionHindiInput, 50);
			}
		});
		
	});
</script>	