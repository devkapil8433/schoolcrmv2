<div class="row">
	<div class="col-md-5">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i> Assign Coordinator / Moderator</h4>
			</header>
			<?php echo form_open('digital_diary/role_allocation_save', array('class' => 'frm-submit')); ?>
			<div class="panel-body">
				<p class="text-muted">Select an existing teacher (same list as class teacher). Coordinator reviews Today's Work for that class/section; Moderator assists on the same inbox for assigned classes.</p>
				<?php if (is_superadmin_loggedin()): ?>
				<div class="form-group">
					<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
					<?php
					$arrayBranch = $this->app_lib->getSelectList('branch');
					echo form_dropdown('branch_id', $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' data-width='100%' data-plugin-selectTwo data-minimum-results-for-search='Infinity'");
					?>
					<span class="error"></span>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<label class="control-label">Role <span class="required">*</span></label>
					<?php
					$roleOpts = array(
						'' => translate('select'),
						'coordinator' => 'Coordinator',
						'moderator' => 'Moderator',
					);
					echo form_dropdown('role_type', $roleOpts, set_value('role_type'), "class='form-control' id='role_type' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
					?>
					<span class="error"></span>
				</div>
				<div class="form-group">
					<label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
					<?php
					$arrayClass = $this->app_lib->getClass($branch_id);
					echo form_dropdown('class_id', $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
					?>
					<span class="error"></span>
				</div>
				<div class="form-group">
					<label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
					<?php
					$arraySection = $this->app_lib->getSections(set_value('class_id'));
					echo form_dropdown('section_id', $arraySection, set_value('section_id'), "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
					?>
					<span class="error"></span>
				</div>
				<div class="form-group mb-md">
					<label class="control-label"><?= translate('teacher') ?> <span class="required">*</span></label>
					<?php
					$arrayTeacher = $this->app_lib->getStaffList($branch_id, 3);
					echo form_dropdown('staff_id', $arrayTeacher, set_value('staff_id'), "class='form-control' id='staff_id' data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"></span>
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
				<h4 class="panel-title"><i class="fas fa-list-ul"></i> Coordinator / Moderator list</h4>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-condensed mb-none">
						<thead>
							<tr>
								<th>#</th>
								<th><?= translate('branch') ?></th>
								<th>Role</th>
								<th><?= translate('teacher') ?></th>
								<th><?= translate('class') ?></th>
								<th><?= translate('section') ?></th>
								<th><?= translate('action') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ($query->num_rows() > 0) {
								$count = 1;
								foreach ($query->result() as $allocation):
									$roleLabel = $allocation->role_type === 'moderator' ? 'Moderator' : 'Coordinator';
							?>
							<tr>
								<td><?= $count++ ?></td>
								<td><?= html_escape(get_type_name_by_id('branch', $allocation->branch_id)) ?></td>
								<td><span class="label label-<?= $allocation->role_type === 'moderator' ? 'warning' : 'primary' ?>"><?= html_escape($roleLabel) ?></span></td>
								<td><?= html_escape($allocation->teacher_name) ?></td>
								<td><?= html_escape($allocation->class_name) ?></td>
								<td><?= html_escape($allocation->section_name) ?></td>
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
								echo '<tr><td colspan="7"><h5 class="text-danger text-center">' . translate('no_information_available') . '</h5></td></tr>';
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
	$(document).ready(function () {
		$(document).on('change', '#branch_id', function () {
			var branchID = $(this).val();
			getClassByBranch(branchID);
			getStaffListRole(branchID, 3);
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
					width: '100%',
					minimumResultsForSearch: 'Infinity'
				});
			}
		});
	}
</script>
