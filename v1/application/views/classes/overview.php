<?php
$canClassTeacherAdd = get_permission('assign_class_teacher', 'is_add');
$canClassTeacherEdit = get_permission('assign_class_teacher', 'is_edit');
$canCoordinator = is_superadmin_loggedin() || is_admin_loggedin() || get_permission('assign_class_teacher', 'is_add') || get_permission('assign_class_teacher', 'is_edit');
$canSubject = get_permission('subject_teacher_assign', 'is_add') || get_permission('subject_teacher_assign', 'is_edit');
$filterClassId = isset($filter_class_id) ? (int) $filter_class_id : 0;
$branchId = isset($branch_id) ? (int) $branch_id : 0;
$overview = isset($overview) && is_array($overview) ? $overview : array();
$teacherOverviewOnly = !empty($teacher_overview_only) || is_teacher_loggedin();

if (!function_exists('class_overview_action_btn')) {
	function class_overview_action_btn($canAdd, $canEdit, $assigned, $attrs)
	{
		if ($assigned) {
			if (!$canEdit) {
				return '';
			}
			$label = translate('edit');
			$icon = 'fa-pen-nib';
		} else {
			if (!$canAdd) {
				return '';
			}
			$label = translate('add');
			$icon = 'fa-plus';
		}
		$html = '<button type="button" class="btn btn-default btn-xs btn-overview-assign"';
		foreach ($attrs as $k => $v) {
			$html .= ' ' . $k . '="' . html_escape($v) . '"';
		}
		$html .= '><i class="fas ' . $icon . '"></i> ' . html_escape($label) . '</button>';
		return $html;
	}
}
?>
<style>
.class-overview-filter .panel-body { padding-bottom: 8px; }
.class-overview-card { background: #fff; border: 1px solid #e3e6ea; border-radius: 6px; margin-bottom: 18px; overflow: hidden; }
.class-overview-card .co-class-head { background: #063944; color: #fff; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; }
.class-overview-card .co-class-head h4 { margin: 0; font-size: 16px; font-weight: 600; color: #fff; }
.class-overview-card .co-class-head .co-branch { opacity: .85; font-size: 12px; }
.class-overview-section { border-top: 1px solid #eceff2; padding: 14px 16px 8px; }
.class-overview-section:nth-child(odd) { background: #f8fafb; }
.class-overview-section h5 { margin: 0 0 12px; font-size: 14px; font-weight: 600; color: #063944; }
.class-overview-section h5 i { margin-right: 6px; }
.co-col-title { font-size: 11px; letter-spacing: .04em; text-transform: uppercase; color: #7a8693; font-weight: 600; margin-bottom: 8px; }
.co-item { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 7px 0; border-bottom: 1px dashed #e6eaee; }
.co-item:last-child { border-bottom: 0; }
.co-item .co-label { color: #6b7280; min-width: 90px; }
.co-item .co-name { font-weight: 600; color: #1f2d3d; }
.co-item .co-missing { color: #c0392b; font-style: italic; font-weight: 500; }
.co-empty-box { color: #8a94a0; font-size: 12px; padding: 6px 0 10px; }
html.dark .class-overview-card { background: #2c2c2c; border-color: #3d3d3d; }
html.dark .class-overview-section:nth-child(odd) { background: #262626; }
html.dark .class-overview-section h5,
html.dark .co-item .co-name { color: #e6e6e6; }
</style>

<?php if (!$teacherOverviewOnly): ?>
<section class="panel class-overview-filter">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-th-list"></i> Class Overview</h4>
	</header>
	<form method="get" action="<?= base_url('classes/overview') ?>" class="validate">
	<div class="panel-body">
		<div class="row">
			<?php if (is_superadmin_loggedin()): ?>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
					<?php
					$arrayBranch = $this->app_lib->getSelectList('branch');
					echo form_dropdown('branch_id', $arrayBranch, set_value('branch_id', $branchId), "class='form-control' id='overview_branch_id' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
					?>
				</div>
			</div>
			<?php endif; ?>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label"><?= translate('class') ?></label>
					<?php
					$arrayClass = $this->app_lib->getClass($branchId);
					$arrayClass[''] = translate('all');
					echo form_dropdown('class_id', $arrayClass, set_value('class_id', $filterClassId > 0 ? $filterClassId : ''), "class='form-control' id='overview_class_id' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
					?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">&nbsp;</label>
					<button type="submit" class="btn btn-default btn-block">
						<i class="fas fa-filter"></i> <?= translate('filter') ?>
					</button>
				</div>
			</div>
		</div>
		<p class="help-block mb-none">See class teacher, coordinator and subject teacher for each class. Add if empty, change if already assigned.</p>
	</div>
	</form>
</section>
<?php endif; ?>

<?php if (!empty($show_my_schedule)): ?>
<?php
$this->load->view('classes/partials/teacher_my_schedule', array(
	'my_timetables' => isset($my_timetables) ? $my_timetables : array(),
	'weekend_raw' => isset($weekend_raw) ? $weekend_raw : '',
));
?>
<?php endif; ?>

<?php if ($teacherOverviewOnly): ?>
	<?php /* Teachers: schedule only — no class assignment board */ ?>
<?php elseif ($branchId < 1): ?>
<section class="panel">
	<div class="panel-body">
		<h5 class="text-danger text-center mb-none"><?= translate('select_branch_first') ?></h5>
	</div>
</section>
<?php elseif (empty($overview)): ?>
<section class="panel">
	<div class="panel-body">
		<h5 class="text-danger text-center mb-none"><?= translate('no_information_available') ?></h5>
	</div>
</section>
<?php else: ?>
	<?php foreach ($overview as $classRow): ?>
	<div class="class-overview-card">
		<div class="co-class-head">
			<h4><i class="fas fa-graduation-cap"></i> <?= html_escape($classRow['class_name']) ?></h4>
			<?php if (is_superadmin_loggedin()): ?>
			<span class="co-branch"><?= html_escape($classRow['branch_name']) ?></span>
			<?php endif; ?>
		</div>
		<?php foreach ($classRow['sections'] as $section): ?>
		<div class="class-overview-section" id="cs-<?= (int) $classRow['class_id'] ?>-<?= (int) $section['section_id'] ?>">
			<h5><i class="fas fa-award"></i> <?= translate('section') ?> <?= html_escape($section['section_name']) ?></h5>
			<div class="row">
				<div class="col-md-4">
					<div class="co-col-title"><?= translate('class_teacher') ?></div>
					<?php
					$ct = !empty($section['class_teacher']) ? $section['class_teacher'] : null;
					$ctAssigned = !empty($ct['teacher_id']);
					?>
					<div class="co-item">
						<div>
							<?php if ($ctAssigned): ?>
								<span class="co-name"><?= html_escape($ct['teacher_name']) ?></span>
							<?php else: ?>
								<span class="co-missing">Not assigned</span>
							<?php endif; ?>
						</div>
						<?= class_overview_action_btn($canClassTeacherAdd, $canClassTeacherEdit, $ctAssigned, array(
							'data-type' => 'class_teacher',
							'data-branch' => (int) $classRow['branch_id'],
							'data-class-id' => (int) $classRow['class_id'],
							'data-section-id' => (int) $section['section_id'],
							'data-staff' => $ctAssigned ? (int) $ct['teacher_id'] : 0,
							'data-allocation' => $ctAssigned ? (int) $ct['allocation_id'] : 0,
						)) ?>
					</div>
				</div>
				<div class="col-md-4">
					<div class="co-col-title">Coordinator</div>
					<?php if (empty($section['coordinators'])): ?>
						<div class="co-empty-box">No coordinator activity types enabled.
							<?php if (!is_teacher_loggedin()): ?>
							<a href="<?= base_url('digital_diary/teaching_activity_types') ?>">Teaching Activity</a>
							<?php endif; ?>
						</div>
					<?php else: ?>
						<?php foreach ($section['coordinators'] as $coord): ?>
							<?php $coordAssigned = !empty($coord['teacher_id']); ?>
							<div class="co-item">
								<div>
									<span class="co-label"><?= html_escape($coord['activity_name']) ?></span>
									<?php if ($coordAssigned): ?>
										<div class="co-name"><?= html_escape($coord['teacher_name']) ?></div>
									<?php else: ?>
										<div class="co-missing">Not assigned</div>
									<?php endif; ?>
								</div>
								<?= class_overview_action_btn($canCoordinator, $canCoordinator, $coordAssigned, array(
									'data-type' => 'coordinator',
									'data-branch' => (int) $classRow['branch_id'],
									'data-class-id' => (int) $classRow['class_id'],
									'data-section-id' => (int) $section['section_id'],
									'data-staff' => $coordAssigned ? (int) $coord['teacher_id'] : 0,
									'data-allocation' => $coordAssigned ? (int) $coord['allocation_id'] : 0,
									'data-activity' => (int) $coord['activity_id'],
								)) ?>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<div class="col-md-4">
					<div class="co-col-title"><?= translate('subject') ?> <?= translate('teacher') ?></div>
					<?php if (empty($section['subjects'])): ?>
						<div class="co-empty-box">No subjects assigned to this class.
							<?php if (get_permission('subject_class_assign', 'is_view')): ?>
							<a href="<?= base_url('subject/class_assign') ?>"><?= translate('class_assign') ?></a>
							<?php endif; ?>
						</div>
					<?php else: ?>
						<?php foreach ($section['subjects'] as $sub): ?>
							<?php $subAssigned = !empty($sub['teacher_id']); ?>
							<div class="co-item">
								<div>
									<span class="co-label"><?= html_escape($sub['subject_name']) ?></span>
									<?php if ($subAssigned): ?>
										<div class="co-name"><?= html_escape($sub['teacher_name']) ?></div>
									<?php else: ?>
										<div class="co-missing">Not assigned</div>
									<?php endif; ?>
								</div>
								<?= class_overview_action_btn($canSubject, $canSubject, $subAssigned, array(
									'data-type' => 'subject_teacher',
									'data-branch' => (int) $classRow['branch_id'],
									'data-class-id' => (int) $classRow['class_id'],
									'data-section-id' => (int) $section['section_id'],
									'data-staff' => $subAssigned ? (int) $sub['teacher_id'] : 0,
									'data-subject' => (int) $sub['subject_id'],
								)) ?>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>
<?php endif; ?>

<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="overviewAssignModal">
	<?php echo form_open('#', array('class' => 'frm-submit', 'id' => 'overviewAssignForm')); ?>
	<section class="panel" id="overviewAssignBody"></section>
	<?php echo form_close(); ?>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$(document).on('change', '#overview_branch_id', function () {
			$(this).closest('form').submit();
		});

		$(document).on('click', '.btn-overview-assign', function () {
			var $btn = $(this);
			var type = $btn.data('type');
			var actions = {
				class_teacher: base_url + 'classes/overview_save_class_teacher',
				coordinator: base_url + 'classes/overview_save_coordinator',
				subject_teacher: base_url + 'classes/overview_save_subject_teacher'
			};
			if (!actions[type]) {
				return;
			}
			$('#overviewAssignForm').attr('action', actions[type]);
			$.ajax({
				url: base_url + 'classes/overview_assign_form',
				type: 'POST',
				data: {
					type: type,
					branch_id: $btn.data('branch'),
					class_id: $btn.data('classId'),
					section_id: $btn.data('sectionId'),
					staff_id: $btn.data('staff') || 0,
					allocation_id: $btn.data('allocation') || 0,
					activity_id: $btn.data('activity') || 0,
					subject_id: $btn.data('subject') || 0,
					filter_class_id: '<?= (int) $filterClassId ?>'
				},
				dataType: 'html',
				success: function (html) {
					$('#overviewAssignBody').html(html);
					mfp_modal('#overviewAssignModal');
				},
				complete: function () {
					$('.selecttwo').select2({
						theme: 'bootstrap',
						width: '100%'
					});
				}
			});
		});
	});
</script>
