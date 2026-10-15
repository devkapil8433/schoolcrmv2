<?php
	$branch_id = isset($branch_id) ? $branch_id : '';
	$def_class = set_value('class_id', isset($preset_class_id) ? $preset_class_id : '');
	$def_section = set_value('section_id', isset($preset_section_id) ? $preset_section_id : '');
	$def_subject = set_value('subject_id', isset($preset_subject_id) ? $preset_subject_id : '');
	$branch_query = (!empty($branch_id)) ? '?branch_id=' . urlencode($branch_id) : '';
	if (!empty($def_class) && !empty($def_section) && !empty($def_subject)) {
		$back_url = base_url('digital_diary/lesson_subject/' . $def_class . '/' . $def_section . '/' . $def_subject) . $branch_query;
	} elseif (!empty($def_class) && !empty($def_section)) {
		$back_url = base_url('digital_diary/lesson_class/' . $def_class . '/' . $def_section) . $branch_query;
	} else {
		$back_url = base_url('digital_diary/lesson') . $branch_query;
	}
	$set_lang = $this->session->userdata('set_lang');
	if ($set_lang === '' || $set_lang === null) {
		$set_lang = get_global_setting('translation');
	}
	$is_hindi_ui = (strtolower((string) $set_lang) === 'hindi');
	$lesson_context_ready = !empty($def_class) && !empty($def_section) && !empty($def_subject);
	$existing_lessons = isset($existing_lessons) && is_array($existing_lessons) ? $existing_lessons : array();
?>
<div class="row">
	<div class="<?= $lesson_context_ready ? 'col-md-7 col-sm-12' : 'col-md-12' ?>">
		<section class="panel">
			<?php echo form_open(base_url('digital_diary/lesson_add'), array('class' => 'form-bordered form-horizontal frm-submit-data', 'accept-charset' => 'UTF-8'));?>
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?=translate('add_lesson')?></h4>
				<div class="panel-btn">
					<a href="<?=$back_url?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> <?=translate('back')?></a>
				</div>
			</header>
			<div class="panel-body mb-md">
				<div class="mt-md"></div>
				<?php if ($lesson_context_ready): ?>
				<div id="lessonContextSummary" class="clearfix" style="margin-bottom: 12px; padding: 0 15px;">
					<button type="button" id="lessonContextChangeBtn" class="btn btn-default btn-sm pull-right">
						<i class="fas fa-pen"></i> Change
					</button>
					<div style="line-height: 30px;">
						<?php if (is_superadmin_loggedin() && !empty($branch_id)): ?>
						<span class="label label-default" title="<?=translate('branch')?>"><i class="fas fa-code-branch"></i> <?=html_escape(get_type_name_by_id('branch', $branch_id))?></span>
						<?php endif; ?>
						<span class="label label-primary" title="<?=translate('class')?> / <?=translate('section')?>"><i class="fas fa-school"></i> <?=html_escape(get_type_name_by_id('class', $def_class))?> &mdash; <?=html_escape(get_type_name_by_id('section', $def_section))?></span>
						<span class="label label-info" title="<?=translate('subject')?>"><i class="fas fa-book-open"></i> <?=html_escape(get_type_name_by_id('subject', $def_subject))?></span>
					</div>
				</div>
				<?php endif; ?>
				<div id="lessonContextRow" <?= $lesson_context_ready ? 'style="display:none;"' : '' ?>>
				<div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; padding: 0 15px; margin-bottom: 10px;">
					<?php if (is_superadmin_loggedin()): ?>
					<div style="flex:1 1 0; min-width:120px;">
						<div class="form-group" style="margin:0;">
							<label class="control-label"><i class="fas fa-code-branch text-muted"></i> <?=translate('branch')?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<?php else: ?>
					<input type="hidden" name="branch_id" value="<?=html_escape($branch_id)?>">
					<?php endif; ?>
					<div style="flex:1 1 0; min-width:120px;">
						<div class="form-group" style="margin:0;">
							<label class="control-label"><i class="fas fa-school text-muted"></i> <?=translate('class')?> <span class="required">*</span></label>
							<?php
								$arrayClass = $this->app_lib->getClass($branch_id);
								echo form_dropdown("class_id", $arrayClass, $def_class, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div style="flex:1 1 0; min-width:120px;">
						<div class="form-group" style="margin:0;">
							<label class="control-label"><i class="fas fa-users text-muted"></i> <?=translate('section')?> <span class="required">*</span></label>
							<?php
								$arraySection = $this->app_lib->getSections($def_class, true);
								echo form_dropdown("section_id", $arraySection, $def_section, "class='form-control' id='section_id' onchange='getLessonAddSubject()' data-plugin-selectTwo data-width='100%'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div style="flex:1 1 0; min-width:120px;">
						<div class="form-group" style="margin:0;">
							<label class="control-label"><i class="fas fa-book-open text-muted"></i> <?=translate('subject')?> <span class="required">*</span></label>
							<?php
								if (!empty($def_class) && !empty($def_section)) {
									$arraySubject = array("" => translate('select'));
									$this->db->where('class_id', (int) $def_class);
									$this->db->where('section_id', (int) $def_section);
									if (!empty($branch_id)) {
										$this->db->where('branch_id', (int) $branch_id);
									}
									$assigns = $this->db->get('subject_assign')->result();
									foreach ($assigns as $row) {
										$arraySubject[$row->subject_id] = get_type_name_by_id('subject', $row->subject_id);
									}
								} else {
									$arraySubject = array("" => translate('select_class_first'));
								}
								echo form_dropdown("subject_id", $arraySubject, $def_subject, "class='form-control' id='subject_id' data-plugin-selectTwo data-width='100%'");
							?>
							<span class="error"></span>
						</div>
					</div>
				</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('lesson_no')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" name="lesson_no" id="lesson_no" class="form-control lesson-hi-input" value="<?=set_value('lesson_no')?>" placeholder="<?=html_escape(translate('lesson_no'))?>" lang="<?= $is_hindi_ui ? 'hi' : 'en' ?>" autocomplete="off">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('lesson_name')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" name="lesson_name" id="lesson_name" class="form-control lesson-hi-input" value="<?=set_value('lesson_name')?>" placeholder="<?=html_escape(translate('lesson_name'))?>" lang="<?= $is_hindi_ui ? 'hi' : 'en' ?>" autocomplete="off">
						<span class="error"></span>
						<div class="lesson-hi-wrap mt-sm">
							<label class="mb-none" style="font-weight:normal;">
								<input type="checkbox" id="lesson_hindi_input" <?= $is_hindi_ui ? 'checked' : '' ?>>
								<?=translate('type_in_hindi')?>
								<small class="text-muted lesson-hi-shortcut">(Ctrl+G)</small>
							</label>
							<p class="help-block mb-none" style="margin-top:4px;"><?=translate('hindi_input_hint')?></p>
							<p class="help-block text-muted mb-none" style="margin-top:2px;font-size:11px;"><?=translate('hindi_os_keyboard_tip')?></p>
						</div>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
						<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><?=translate('save')?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close();?>
		</section>
	</div>
	<?php if ($lesson_context_ready): ?>
	<div class="col-md-5 col-sm-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-list-ol"></i> Current Lessons <span class="badge"><?=count($existing_lessons)?></span></h4>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-hover mb-none">
						<thead>
							<tr>
								<th width="45"><?=translate('sl')?></th>
								<th width="90"><?=translate('lesson_no')?></th>
								<th><?=translate('lesson_name')?></th>
								<th width="60"><?=translate('action')?></th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($existing_lessons)): $sn = 1; foreach ($existing_lessons as $lrow): ?>
							<tr>
								<td><?=$sn++?></td>
								<td><?=html_escape($lrow['lesson_no'])?></td>
								<td><?=html_escape($lrow['lesson_name'])?></td>
								<td>
									<a href="<?=base_url('digital_diary/lesson_edit/' . (int) $lrow['id']) . $branch_query?>" class="btn btn-circle btn-default btn-sm" title="<?=translate('edit')?>"><i class="fas fa-pen-nib"></i></a>
								</td>
							</tr>
							<?php endforeach; else: ?>
							<tr>
								<td colspan="4" class="text-center text-muted">No lessons yet for this subject.</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
	<?php endif; ?>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('#branch_id').on('change', function() {
			var branchID = $(this).val();
			getClassByBranch(branchID);
		});
		$('#lessonContextChangeBtn').on('click', function () {
			var $row = $('#lessonContextRow');
			var opening = !$row.is(':visible');
			$row.slideToggle(180);
			$(this).find('i').toggleClass('fa-pen', !opening).toggleClass('fa-xmark', opening);
			$(this).contents().last()[0].textContent = opening ? ' Close' : ' Change';
		});
	});
	function getLessonAddSubject() {
		var classID = $('#class_id').val();
		var sectionID = $('#section_id').val();
		if (!classID) return;
		$.ajax({
			url: base_url + 'subject/getByClassSection',
			type: 'POST',
			data: { classID: classID, sectionID: sectionID },
			success: function(data) {
				$('#subject_id').html(data);
			}
		});
	}
</script>
<script src="<?= base_url('assets/js/lesson-hindi-input.js') ?>?v=1"></script>
