<header class="panel-heading">
	<h4 class="panel-title"><i class="far fa-edit"></i> <?= translate('edit_assign') ?></h4>
</header>
<input type="hidden" name="allocation_id" value="<?= (int) $data['id'] ?>">
<div class="panel-body">
	<?php if (is_superadmin_loggedin()): ?>
	<div class="form-group">
		<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
		<?php
		$arrayBranch = $this->app_lib->getSelectList('branch');
		echo form_dropdown('branch_id', $arrayBranch, $data['branch_id'], "class='form-control selecttwo' id='modal_branch_id'");
		?>
		<span class="error"></span>
	</div>
	<?php endif; ?>
	<input type="hidden" name="role_type" value="coordinator">
	<div class="form-group">
		<label class="control-label">Activity type <span class="required">*</span></label>
		<?php
		$actOpts = array('' => translate('select'));
		if (!empty($activity_options) && is_array($activity_options)) {
			foreach ($activity_options as $aid => $aname) {
				$actOpts[$aid] = $aname;
			}
		}
		$selAct = isset($data['teaching_activity_id']) ? $data['teaching_activity_id'] : '';
		echo form_dropdown('teaching_activity_id', $actOpts, $selAct, "class='form-control selecttwo' id='modal_teaching_activity_id'");
		?>
		<span class="error"></span>
	</div>
	<div class="form-group">
		<label class="control-label"><?= translate('teacher') ?> <span class="required">*</span></label>
		<?php
		$arrayTeacher = $this->app_lib->getStaffList($data['branch_id'], 3);
		echo form_dropdown('staff_id', $arrayTeacher, $data['teacher_id'], "class='form-control selecttwo' id='modal_staff_id'");
		?>
		<span class="error"></span>
	</div>
	<div class="form-group mb-md">
		<label class="control-label"><?= translate('class') ?> / <?= translate('section') ?> <span class="required">*</span></label>
		<?php
		$csOpts = isset($class_section_options) && is_array($class_section_options) ? $class_section_options : array();
		$selCs = isset($data['class_section']) && is_array($data['class_section']) ? $data['class_section'] : array();
		echo form_dropdown('class_section[]', $csOpts, $selCs, "class='form-control selecttwo' id='modal_class_section' multiple");
		?>
		<span class="error"></span>
	</div>
</div>
<footer class="panel-footer">
	<div class="row">
		<div class="col-md-12 text-right">
			<button class="btn btn-default mr-xs" type="submit" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
				<i class="fas fa-plus-circle"></i> <?= translate('update') ?>
			</button>
			<button class="btn btn-default modal-dismiss"><?= translate('close') ?></button>
		</div>
	</div>
</footer>
