<?php
$arrayClass = $this->app_lib->getClass($branch_id);
$arraySection = $this->app_lib->getSections($lesson['class_id'], true);
$arraySubject = array('' => translate('select'));
$assigns = $this->db->get_where('subject_assign', array('class_id' => $lesson['class_id'], 'section_id' => $lesson['section_id']))->result();
foreach ($assigns as $row) {
    $arraySubject[$row->subject_id] = get_type_name_by_id('subject', $row->subject_id);
}
$back_url = base_url('digital_diary/lesson_subject/' . $lesson['class_id'] . '/' . $lesson['section_id'] . '/' . $lesson['subject_id']) . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '');
$set_lang = $this->session->userdata('set_lang');
if ($set_lang === '' || $set_lang === null) {
	$set_lang = get_global_setting('translation');
}
$is_hindi_ui = (strtolower((string) $set_lang) === 'hindi');
?>
<div class="row">
	<div class="col-md-12">
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body">
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
				<a href="<?=base_url('digital_diary/lesson') . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '')?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
			</div>
		</section>
		<?php endif; ?>

		<section class="panel">
			<?php echo form_open(base_url('digital_diary/lesson_edit/' . $lesson['id']) . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : ''), array('class' => 'form-bordered form-horizontal frm-submit-data', 'accept-charset' => 'UTF-8')); ?>
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-pen-nib"></i> <?=translate('edit_lesson')?></h4>
				<div class="panel-btn">
					<a href="<?=$back_url?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> <?=translate('back')?></a>
				</div>
			</header>
			<div class="panel-body mb-md">
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('class')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php echo form_dropdown('class_id', $arrayClass, $lesson['class_id'], 'class="form-control" id="class_id" onchange="getSectionByClass(this.value,0)" data-plugin-selectTwo data-width="100%"'); ?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('section')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php echo form_dropdown('section_id', $arraySection, $lesson['section_id'], 'class="form-control" id="section_id" onchange="getLessonEditSubject()" data-plugin-selectTwo data-width="100%"'); ?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('subject')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php echo form_dropdown('subject_id', $arraySubject, $lesson['subject_id'], 'class="form-control" id="subject_id" data-plugin-selectTwo data-width="100%"'); ?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('lesson_no')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" name="lesson_no" id="lesson_no" class="form-control lesson-hi-input" value="<?=html_escape($lesson['lesson_no'])?>" placeholder="<?=html_escape(translate('lesson_no'))?>" lang="<?= $is_hindi_ui ? 'hi' : 'en' ?>" autocomplete="off">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('lesson_name')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" name="lesson_name" id="lesson_name" class="form-control lesson-hi-input" value="<?=html_escape($lesson['lesson_name'])?>" placeholder="<?=html_escape(translate('lesson_name'))?>" lang="<?= $is_hindi_ui ? 'hi' : 'en' ?>" autocomplete="off">
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
				<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><?=translate('update')?></button>
			</footer>
			<?php echo form_close(); ?>
		</section>
	</div>
</div>
<script type="text/javascript">
	function getLessonEditSubject() {
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
	$('#class_id').on('change', function() {
		getSectionByClass($(this).val(), 0);
	});
</script>
<script src="<?= base_url('assets/js/lesson-hindi-input.js') ?>?v=1"></script>
