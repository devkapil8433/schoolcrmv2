<div class="row">
	<div class="col-md-5">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i> Assign Coordinator</h4>
			</header>
			<?php echo form_open('digital_diary/role_allocation_save', array('class' => 'frm-submit')); ?>
			<div class="panel-body">
				<p class="text-muted">Coordinator is based on <strong>activity type</strong>. Select a teacher and assign multiple classes for that activity.</p>
				<input type="hidden" name="role_type" id="role_type" value="coordinator">
				<?php if (is_superadmin_loggedin()): ?>
				<div class="form-group">
					<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
					<?php
					$arrayBranch = $this->app_lib->getSelectList('branch');
					echo form_dropdown('branch_id', $arrayBranch, set_value('branch_id', $branch_id), "class='form-control' id='branch_id' data-width='100%' data-plugin-selectTwo data-minimum-results-for-search='Infinity'");
					?>
					<span class="error"></span>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<label class="control-label">Activity type <span class="required">*</span></label>
					<?php
					$actOpts = array('' => translate('select'));
					if (!empty($activity_options) && is_array($activity_options)) {
						foreach ($activity_options as $aid => $aname) {
							$actOpts[$aid] = $aname;
						}
					}
					$selAct = set_value('teaching_activity_id', isset($preset_activity_id) ? $preset_activity_id : '');
					echo form_dropdown('teaching_activity_id', $actOpts, $selAct, "class='form-control' id='teaching_activity_id' data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"></span>
				</div>
				<div class="form-group">
					<label class="control-label"><?= translate('teacher') ?> <span class="required">*</span></label>
					<?php
					$arrayTeacher = $this->app_lib->getStaffList($branch_id, 3);
					echo form_dropdown('staff_id', $arrayTeacher, set_value('staff_id'), "class='form-control' id='staff_id' data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"></span>
				</div>
				<div class="form-group mb-md">
					<label class="control-label"><?= translate('class') ?> / <?= translate('section') ?> <span class="required">*</span></label>
					<?php
					$csOpts = isset($class_section_options) && is_array($class_section_options) ? $class_section_options : array();
					echo form_dropdown('class_section[]', $csOpts, array(), "class='form-control' id='class_section' multiple data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"></span>
					<p class="help-block mb-none">You can select multiple classes for this coordinator.</p>
				</div>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-md-12">
						<button type="submit" class="btn btn-default pull-right" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<i class="fas fa-plus-circle"></i> <?= translate('save') ?>
						</button>
					</div>
				</div>
			</div>
			<?php echo form_close(); ?>
		</section>
	</div>
	<div class="col-md-7">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-list-ul"></i> Coordinator list</h4>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-condensed mb-none">
						<thead>
							<tr>
								<th>#</th>
								<th><?= translate('branch') ?></th>
								<th>Activity</th>
								<th><?= translate('teacher') ?></th>
								<th><?= translate('class') ?></th>
								<th><?= translate('action') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$groups = isset($query_groups) ? $query_groups : array();
							if (!empty($groups)) {
								$count = 1;
								foreach ($groups as $allocation):
							?>
							<tr>
								<td><?= $count++ ?></td>
								<td><?= html_escape(get_type_name_by_id('branch', $allocation->branch_id)) ?></td>
								<td><?= html_escape($allocation->activity_name) ?></td>
								<td><?= html_escape($allocation->teacher_name) ?></td>
								<td><?= html_escape($allocation->classes_label) ?></td>
								<td>
									<a href="javascript:void(0);" class="btn btn-circle btn-default icon" onclick="getDiaryRoleAllocation('<?= (int) $allocation->id ?>')">
										<i class="fas fa-pen-nib"></i>
									</a>
									<?= btn_delete('digital_diary/role_allocation_delete/' . (int) $allocation->id) ?>
								</td>
							</tr>
							<?php
								endforeach;
							} else {
								echo '<tr><td colspan="6"><h5 class="text-danger text-center">' . translate('no_information_available') . '</h5></td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>

<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<?php echo form_open('digital_diary/role_allocation_save', array('class' => 'frm-submit')); ?>
	<section class="panel" id="allocation"></section>
	<?php echo form_close(); ?>
</div>

<script type="text/javascript">
	function reloadDiaryRoleActivities() {
		var branchID = $('#branch_id').val() || '<?= (int) $branch_id ?>';
		var role = $('#role_type').val() || '';
		var selected = $('#teaching_activity_id').val() || '';
		$.ajax({
			url: base_url + 'digital_diary/ajax_teaching_activities_for_role',
			type: 'POST',
			data: { branch_id: branchID, role_type: role, selected: selected },
			success: function (html) {
				$('#teaching_activity_id').html(html).trigger('change.select2');
			}
		});
	}
	function reloadDiaryRoleClassSections() {
		var branchID = $('#branch_id').val() || '<?= (int) $branch_id ?>';
		$.ajax({
			url: base_url + 'digital_diary/ajax_class_section_pairs',
			type: 'POST',
			data: { branch_id: branchID },
			success: function (html) {
				$('#class_section').html(html).trigger('change.select2');
			}
		});
	}
	$(document).ready(function () {
		$(document).on('change', '#branch_id', function () {
			var branchID = $(this).val();
			getStaffListRole(branchID, 3);
			reloadDiaryRoleActivities();
			reloadDiaryRoleClassSections();
		});
	});

	function getDiaryRoleAllocation(id) {
		$.ajax({
			url: base_url + 'digital_diary/get_role_allocation',
			type: 'POST',
			data: { id: id },
			dataType: 'html',
			success: function (data) {
				$('#allocation').html(data);
				mfp_modal('#modal');
			},
			complete: function () {
				$('.selecttwo').select2({
					theme: 'bootstrap',
					width: '100%'
				});
			}
		});
	}
</script>
