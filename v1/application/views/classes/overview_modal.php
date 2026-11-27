<?php
$type = isset($type) ? $type : '';
$isEdit = !empty($is_edit);
$titles = array(
	'class_teacher' => translate('class_teacher'),
	'coordinator' => 'Coordinator',
	'subject_teacher' => translate('subject') . ' ' . translate('teacher'),
);
$title = isset($titles[$type]) ? $titles[$type] : translate('assign');
?>
<header class="panel-heading">
	<h4 class="panel-title"><i class="far fa-edit"></i> <?= $isEdit ? translate('edit') : translate('add') ?> <?= html_escape($title) ?></h4>
</header>
<div class="panel-body">
	<?php if (is_superadmin_loggedin()): ?>
	<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
	<?php endif; ?>
	<input type="hidden" name="class_id" value="<?= (int) $class_id ?>">
	<input type="hidden" name="section_id" value="<?= (int) $section_id ?>">
	<input type="hidden" name="filter_class_id" value="<?= (int) $filter_class_id ?>">
	<?php if (!empty($allocation_id)): ?>
	<input type="hidden" name="allocation_id" value="<?= (int) $allocation_id ?>">
	<?php endif; ?>

	<div class="form-group">
		<label class="control-label"><?= translate('class') ?> / <?= translate('section') ?></label>
		<input type="text" class="form-control" value="<?= html_escape(trim($class_name . ' - ' . $section_name, ' -')) ?>" readonly>
	</div>

	<?php if ($type === 'coordinator'): ?>
	<?php if (!empty($activity_id)): ?>
	<input type="hidden" name="teaching_activity_id" value="<?= (int) $activity_id ?>">
	<div class="form-group">
		<label class="control-label">Activity type</label>
		<input type="text" class="form-control" value="<?= html_escape(!empty($activity_name) ? $activity_name : '') ?>" readonly>
	</div>
	<?php else: ?>
	<div class="form-group">
		<label class="control-label">Activity type <span class="required">*</span></label>
		<?php
		$actOpts = array('' => translate('select'));
		if (!empty($activities) && is_array($activities)) {
			foreach ($activities as $aid => $aname) {
				$actOpts[$aid] = $aname;
			}
		}
		echo form_dropdown('teaching_activity_id', $actOpts, '', "class='form-control selecttwo'");
		?>
		<span class="error"></span>
	</div>
	<?php endif; ?>
	<?php elseif ($type === 'subject_teacher'): ?>
	<input type="hidden" name="subject_id" value="<?= (int) $subject_id ?>">
	<div class="form-group">
		<label class="control-label"><?= translate('subject') ?></label>
		<input type="text" class="form-control" value="<?= html_escape(!empty($subject_name) ? $subject_name : '') ?>" readonly>
	</div>
	<?php endif; ?>

	<div class="form-group mb-md">
		<label class="control-label"><?= translate('teacher') ?> <span class="required">*</span></label>
		<?php
		echo form_dropdown('staff_id', $teachers, set_value('staff_id', $staff_id), "class='form-control selecttwo' id='overview_staff_id'");
		?>
		<span class="error"></span>
	</div>
</div>
<footer class="panel-footer">
	<div class="row">
		<div class="col-md-12 text-right">
			<button class="btn btn-default mr-xs" type="submit" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
				<i class="fas fa-plus-circle"></i> <?= $isEdit ? translate('update') : translate('save') ?>
			</button>
			<button class="btn btn-default modal-dismiss"><?= translate('close') ?></button>
		</div>
	</div>
</footer>
