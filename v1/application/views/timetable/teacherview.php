<?php
$staff_id = isset($staff_id) ? (int) $staff_id : (int) set_value('staff_id');
$schoolyears = isset($schoolyears) && is_array($schoolyears) ? $schoolyears : array();
$class_list = isset($class_list) && is_array($class_list) ? $class_list : array('' => translate('select'));
$arrayDay = array(
	"monday" => "Monday",
	"tuesday" => "Tuesday",
	"wednesday" => "Wednesday",
	"thursday" => "Thursday",
	"friday" => "Friday",
	"saturday" => "Saturday",
);
$canCopySchedule = !is_teacher_loggedin()
	&& (get_permission('class_timetable', 'is_add') || get_permission('class_timetable', 'is_edit'));
$canEditCell = !is_teacher_loggedin()
	&& (get_permission('class_timetable', 'is_edit') || get_permission('class_timetable', 'is_add'));
$canAddCell = !is_teacher_loggedin() && get_permission('class_timetable', 'is_add');
$canDeleteCell = !is_teacher_loggedin()
	&& (get_permission('class_timetable', 'is_delete') || get_permission('class_timetable', 'is_edit'));
$dayIndexMap = array(
	'sunday' => '0',
	'monday' => '1',
	'tuesday' => '2',
	'wednesday' => '3',
	'thursday' => '4',
	'friday' => '5',
	'saturday' => '6',
);
$weekendDays = array();
$weekendRaw = $this->application_model->getWeekends($branch_id);
if ($weekendRaw !== '' && $weekendRaw !== null) {
	foreach (explode(',', $weekendRaw) as $w) {
		$w = trim((string) $w);
		if ($w !== '') {
			$weekendDays[$w] = true;
		}
	}
}
?>
<style>
.swr-tt-cell { min-width: 160px; vertical-align: middle !important; }
.swr-tt-cell-inner {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 8px;
	text-align: left;
}
.swr-tt-cell-body { flex: 1; min-width: 0; }
.swr-tt-cell-side { flex: 0 0 auto; padding-top: 2px; }
.swr-tt-add-cell { min-width: 52px; text-align: center; vertical-align: middle !important; }
.swr-tt-holiday-row > td { background: #fff8e1; }
.swr-tt-holiday-row .timetable { color: #ef6c00; }
.swr-tt-cell-body .tt-p-subject { display: block; font-weight: 700; margin-bottom: 2px; }
.swr-tt-cell-body .tt-p-time,
.swr-tt-cell-body .tt-p-meta { display: block; font-size: 12px; color: #555; line-height: 1.35; }
.swr-tt-cell-body .tt-p-break { display: block; font-weight: 700; }
</style>
<?php if (!is_teacher_loggedin()) {
$widget = (is_superadmin_loggedin() ? "col-md-6" : "col-md-offset-3 col-md-6"); ?>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><?=translate('select_ground')?></h4>
	</header>
	<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
	<div class="panel-body">
		<div class="row mb-sm">
			<?php if (is_superadmin_loggedin()): ?>
			<div class="col-md-6 mb-sm">
				<div class="form-group">
					<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
					<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='form-control' required onchange='getStaffListRole(this.value, 3)'
						data-width='100%' data-plugin-selectTwo data-minimum-results-for-search='Infinity'");
					?>
				</div>
			</div>
			<?php endif; ?>
			<div class="<?php echo $widget; ?> mb-sm">
				<div class="form-group">
					<label class="control-label"><?=translate('teacher')?> <span class="required">*</span></label>
					<?php
						$arrayStaff = $this->app_lib->getStaffList($branch_id, 3);
						echo form_dropdown("staff_id", $arrayStaff, set_value('staff_id', $staff_id), "class='form-control' id='staff_id'
						required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
					?>
				</div>
			</div>
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-offset-10 col-md-2">
				<button type="submit" class="btn btn btn-default btn-block"> <i class="fas fa-filter"></i> <?=translate('filter')?></button>
			</div>
		</div>
	</footer>
	<?php echo form_close();?>
</section>
<?php } ?>

<?php if(isset($timetables)): ?>
<?php if (!is_teacher_loggedin()): ?>
	<section class="panel appear-animation mt-sm" data-appear-animation="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
<?php else: ?>
	<section class="panel mt-md">
<?php endif; ?>
		<header class="panel-heading">
			<div class="panel-btn">
				<?php if ($canCopySchedule && $staff_id > 0): ?>
				<a class="copy_schedule_modal btn btn-default btn-circle" href="javascript:void(0);" title="Copy this teacher's schedule to another weekday or academic year">
					<i class="fas fa-copy"></i> Copy to another day / year
				</a>
				<?php endif; ?>
				<button onclick="fn_printElem('printResult')" class="btn btn-default btn-circle icon"><i class="fas fa-print"></i></button>
			</div>
			<h4 class="panel-title"><i class="fas fa-user-clock"></i> <?=translate('schedule') . " " . translate('list')?></h4>
		</header>
		<div class="panel-body">
			<?php if ($canEditCell || $canAddCell): ?>
			<p class="text-muted mb-sm hidden-print">Use the pencil to edit a period, or <i class="fas fa-plus"></i> on a day row to add one.</p>
			<?php endif; ?>
			<div class="table-responsive">
				<div id="printResult" class="tt-print-root">
					<style type="text/css" media="print">
						@page { size: A4 landscape; margin: 10mm; }
						* { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
						body { margin: 0; padding: 8px; font-family: Arial, Helvetica, sans-serif; color: #111; background: #fff; }
						.swr-tt-cell-side,
						.swr-tt-add-cell,
						.hidden-print,
						.tt-print-hide { display: none !important; }
						.tt-print-header,
						.tt-print-footer,
						.visible-print { display: block !important; }
						.tt-print-header { text-align: center; margin: 0 0 12px; }
						.tt-print-header .tt-school { margin: 0; font-size: 20px; font-weight: 700; line-height: 1.25; }
						.tt-print-header .tt-address { margin: 3px 0 0; font-size: 11px; color: #444; }
						.tt-print-header .tt-doc-title {
							margin: 10px 0 8px; font-size: 15px; font-weight: 700;
							letter-spacing: .6px; text-transform: uppercase;
						}
						.tt-print-bar {
							display: table; width: 100%; border: 1px solid #222; border-collapse: collapse;
							margin: 0 0 12px; font-size: 11px;
						}
						.tt-print-bar span {
							display: table-cell; padding: 6px 10px; border-right: 1px solid #ccc; vertical-align: middle;
						}
						.tt-print-bar span:last-child { border-right: 0; }
						.tt-print-bar b { display: block; font-size: 9px; text-transform: uppercase; color: #666; letter-spacing: .4px; }
						#teacherTimetableGrid {
							width: 100% !important; border-collapse: collapse !important;
							table-layout: fixed; page-break-inside: auto;
						}
						#teacherTimetableGrid > tbody > tr { page-break-inside: avoid; }
						#teacherTimetableGrid > tbody > tr > td {
							border: 1px solid #222 !important; padding: 6px 5px !important;
							vertical-align: top !important; font-size: 10.5px; color: #111;
							background: #fff !important;
						}
						#teacherTimetableGrid td.timetable {
							width: 78px !important; font-weight: 700; text-align: center;
							vertical-align: middle !important; background: #efefef !important;
							font-size: 11px; letter-spacing: .2px;
						}
						#teacherTimetableGrid .swr-tt-holiday-row > td { background: #f7f1e3 !important; }
						#teacherTimetableGrid .label { display: inline-block; padding: 1px 5px; font-size: 9px; border: 1px solid #a67c00; color: #7a5a00; background: #fff8e1; border-radius: 2px; }
						.swr-tt-cell-inner { display: block !important; }
						.swr-tt-cell-body .tt-p-subject { display: block; font-weight: 700; font-size: 11px; margin-bottom: 2px; line-height: 1.2; }
						.swr-tt-cell-body .tt-p-time { display: block; font-size: 10px; color: #222; margin-bottom: 2px; }
						.swr-tt-cell-body .tt-p-meta { display: block; font-size: 9.5px; color: #333; line-height: 1.25; }
						.swr-tt-cell-body .tt-p-break { display: block; font-weight: 700; text-align: center; padding: 8px 0; letter-spacing: .5px; }
						.tt-print-footer {
							margin-top: 10px; padding-top: 6px; border-top: 1px solid #999;
							font-size: 9px; color: #555; display: table; width: 100%;
						}
						.tt-print-footer .left { display: table-cell; text-align: left; }
						.tt-print-footer .right { display: table-cell; text-align: right; }
					</style>
					<div class="tt-print-header visible-print">
						<div class="tt-school"><?= html_escape($global_config['institute_name']) ?></div>
						<?php if (!empty($global_config['address'])): ?>
						<div class="tt-address"><?= html_escape($global_config['address']) ?></div>
						<?php endif; ?>
						<div class="tt-doc-title">Teacher Timetable</div>
						<div class="tt-print-bar">
							<span><b><?= translate('teacher') ?></b><?= html_escape(get_type_name_by_id('staff', $staff_id)) ?></span>
							<span><b><?= translate('academic_year') ?></b><?= html_escape(get_type_name_by_id('schoolyear', get_session_id(), 'school_year')) ?></span>
						</div>
					</div>
					<table class="table table-bordered table-striped table-condensed text-dark" id="teacherTimetableGrid">
						<tbody>
						<?php
						$days = array(
							'monday',
							'tuesday',
							'wednesday',
							'thursday',
							'friday',
							'saturday'
						);
						$byDay = array();
						foreach ($days as $d) {
							$byDay[$d] = array();
						}
						foreach ($timetables as $timetable) {
							$dKey = strtolower((string) $timetable->day);
							if (!isset($byDay[$dKey])) {
								$byDay[$dKey] = array();
							}
							$byDay[$dKey][] = $timetable;
						}
						$maxSlots = 0;
						foreach ($days as $d) {
							if (empty($weekendDays[$dayIndexMap[$d]])) {
								$maxSlots = max($maxSlots, count($byDay[$d]));
							}
						}
						if ($maxSlots < 1) {
							$maxSlots = 1;
						}
						foreach ($days as $day):
							$isHolidayDay = !empty($weekendDays[$dayIndexMap[$day]]);
							echo '<tr data-day="' . html_escape($day) . '"' . ($isHolidayDay ? ' class="swr-tt-holiday-row"' : '') . '>';
							echo '<td width="140" class="timetable">' . strtoupper($day);
							if ($isHolidayDay) {
								echo '<br><span class="label label-warning">Holiday</span>';
							}
							echo '</td>';
							if ($isHolidayDay) {
								echo '<td class="center text-muted" colspan="' . (int) $maxSlots . '"><small>Weekly off — no class schedule.</small></td>';
								if ($canAddCell) {
									echo '<td class="swr-tt-add-cell hidden-print"></td>';
								}
							} else {
								$row_count = 0;
								foreach ($byDay[$day] as $timetable) {
									$row_count++;
									$isBreak = ((string) $timetable->break === '1' || (int) $timetable->break === 1);
									$tStart = date('g:i A', strtotime($timetable->time_start));
									$tEnd = date('g:i A', strtotime($timetable->time_end));
									echo '<td class="swr-tt-cell"';
									echo ' data-id="' . (int) $timetable->id . '"';
									echo ' data-day="' . html_escape($day) . '"';
									echo ' data-class="' . (int) $timetable->class_id . '"';
									echo ' data-section="' . (int) $timetable->section_id . '"';
									echo ' data-break="' . ($isBreak ? '1' : '0') . '"';
									echo ' data-subject="' . (int) $timetable->subject_id . '"';
									echo ' data-teacher="' . (int) $timetable->teacher_id . '"';
									echo ' data-time-start="' . html_escape(date('h:i A', strtotime($timetable->time_start))) . '"';
									echo ' data-time-end="' . html_escape(date('h:i A', strtotime($timetable->time_end))) . '"';
									echo ' data-room="' . html_escape($timetable->class_room) . '">';
									echo '<div class="swr-tt-cell-inner">';
									echo '<div class="swr-tt-cell-body">';
									if ($isBreak) {
										echo '<span class="tt-p-break">BREAK</span>';
										echo '<span class="tt-p-time">' . html_escape($tStart) . ' – ' . html_escape($tEnd) . '</span>';
									} else {
										$subLabel = !empty($timetable->subject_name) ? $timetable->subject_name : get_type_name_by_id('subject', $timetable->subject_id);
										$subCode = !empty($timetable->subject_code) ? ' (' . $timetable->subject_code . ')' : '';
										echo '<span class="tt-p-subject">' . html_escape($subLabel . $subCode) . '</span>';
										echo '<span class="tt-p-time">' . html_escape($tStart) . ' – ' . html_escape($tEnd) . '</span>';
										echo '<span class="tt-p-meta">' . html_escape($timetable->class_name) . ' (' . html_escape($timetable->section_name) . ')</span>';
										if ($timetable->class_room != '') {
											echo '<span class="tt-p-meta">' . html_escape($timetable->class_room) . '</span>';
										}
									}
									echo '</div>';
									if ($canEditCell) {
										echo '<div class="swr-tt-cell-side hidden-print">';
										echo '<button type="button" class="btn btn-default btn-circle icon tt-edit-cell" title="' . html_escape(translate('edit')) . '"><i class="fas fa-pen-nib"></i></button>';
										echo '</div>';
									}
									echo '</div>';
									echo '</td>';
								}
								while ($row_count < $maxSlots) {
									$row_count++;
									echo '<td class="center text-muted swr-tt-empty-cell">';
									echo '<small>—</small>';
									echo '</td>';
								}
								if ($canAddCell) {
									echo '<td class="swr-tt-add-cell hidden-print">';
									echo '<button type="button" class="btn btn-default btn-circle icon tt-add-cell" data-day="' . html_escape($day) . '" title="' . html_escape(translate('add')) . '">';
									echo '<i class="fas fa-plus"></i>';
									echo '</button></td>';
								}
							}
							echo '</tr>';
						endforeach;
						?>
						</tbody>
					</table>
					<div class="tt-print-footer visible-print">
						<span class="left"><?= translate('teacher') ?>: <?= html_escape(get_type_name_by_id('staff', $staff_id)) ?></span>
						<span class="right">Printed on <?= html_escape(date('d M Y, g:i A')) ?></span>
					</div>
				</div>
			</div>
			<?php if (count($timetables) < 1): ?>
			<div class="alert alert-subl mt-md text-center hidden-print">No periods yet<?php if ($canAddCell): ?> — use the <i class="fas fa-plus"></i> button on a day to create one<?php endif; ?>.</div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ($canEditCell || $canAddCell): ?>
	<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="cellScheduleModal">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title" id="cellScheduleModalTitle"><i class="fas fa-pen-nib"></i> Edit period</h4>
			</header>
			<?php echo form_open('timetable/class_period_save', array('id' => 'frmCellSchedule', 'class' => 'validate')); ?>
			<div class="panel-body">
				<input type="hidden" name="id" id="cell_id" value="0">
				<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
				<input type="hidden" name="teacher_id" id="cell_teacher_id" value="<?= (int) $staff_id ?>">
				<input type="hidden" name="day" id="cell_day" value="">
				<div class="form-group">
					<label class="control-label"><?=translate('day')?></label>
					<input type="text" class="form-control" id="cell_day_label" readonly>
				</div>
				<div class="form-group cell-class-fields">
					<label class="control-label"><?=translate('class')?> <span class="required">*</span></label>
					<?php
						echo form_dropdown('class_id', $class_list, '', "class='form-control' id='cell_class_id'
						data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
					?>
					<span class="error"></span>
				</div>
				<div class="form-group cell-class-fields">
					<label class="control-label"><?=translate('section')?> <span class="required">*</span></label>
					<select name="section_id" id="cell_section_id" class="form-control" data-plugin-selectTwo data-width="100%" data-minimum-results-for-search="Infinity">
						<option value=""><?=translate('select')?></option>
					</select>
					<span class="error"></span>
				</div>
				<div class="form-group">
					<div class="checkbox-replace">
						<label class="i-checks">
							<input type="checkbox" name="break" id="cell_break" value="1">
							<i></i> BREAK
						</label>
					</div>
				</div>
				<div class="form-group cell-teaching-fields">
					<label class="control-label"><?=translate('subject')?> <span class="required">*</span></label>
					<select name="subject_id" id="cell_subject" class="form-control" data-plugin-selectTwo data-width="100%" data-minimum-results-for-search="Infinity">
						<option value=""><?=translate('select')?></option>
					</select>
					<span class="error"></span>
				</div>
				<div class="form-group">
					<label class="control-label"><?=translate('starting_time')?> <span class="required">*</span></label>
					<div class="input-group">
						<span class="input-group-addon"><i class="far fa-clock"></i></span>
						<input type="text" name="time_start" id="cell_time_start" class="form-control" data-plugin-timepicker data-plugin-options='{"minuteStep":5}' autocomplete="off">
					</div>
					<span class="error"></span>
				</div>
				<div class="form-group">
					<label class="control-label"><?=translate('ending_time')?> <span class="required">*</span></label>
					<div class="input-group">
						<span class="input-group-addon"><i class="far fa-clock"></i></span>
						<input type="text" name="time_end" id="cell_time_end" class="form-control" data-plugin-timepicker data-plugin-options='{"minuteStep":5}' autocomplete="off">
					</div>
					<span class="error"></span>
				</div>
				<div class="form-group">
					<label class="control-label"><?=translate('class_room')?></label>
					<input type="text" name="class_room" id="cell_room" class="form-control" autocomplete="off">
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-4 text-left">
						<?php if ($canDeleteCell): ?>
						<button type="button" class="btn btn-danger" id="btnCellDelete" style="display:none;">
							<i class="fas fa-trash-alt"></i> <?=translate('delete')?>
						</button>
						<?php endif; ?>
					</div>
					<div class="col-md-8 text-right">
						<button type="submit" class="btn btn-default" id="btnCellSave" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<?=translate('save')?>
						</button>
						<button type="button" class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close(); ?>
		</section>
	</div>
	<?php endif; ?>

	<?php if ($canCopySchedule && $staff_id > 0): ?>
	<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="copyScheduleModal">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-copy"></i> Copy this teacher's schedule</h4>
			</header>
			<?php echo form_open('timetable/copy_teacher_schedule', array('class' => 'validate', 'id' => 'frmCopyTeacherSchedule')); ?>
			<div class="panel-body">
				<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
				<input type="hidden" name="staff_id" value="<?= (int) $staff_id ?>">
				<p class="text-muted mb-md">
					Duplicate this teacher's periods:
					choose <strong>another weekday</strong> (e.g. Monday → Tuesday),
					or <strong>another academic year</strong>.
				</p>
				<div class="form-group">
					<label class="control-label">What do you want to copy to? <span class="required">*</span></label>
					<div class="radio-custom radio-primary">
						<input type="radio" id="copy_mode_day" name="copy_mode" value="day" checked>
						<label for="copy_mode_day">Another weekday (Mon–Sat)</label>
					</div>
					<div class="radio-custom radio-primary">
						<input type="radio" id="copy_mode_year" name="copy_mode" value="year">
						<label for="copy_mode_year">Another academic year / session</label>
					</div>
				</div>

				<div class="copy-mode-panel" data-mode="day">
					<div class="form-group">
						<label class="control-label">From day <span class="required">*</span></label>
						<?php
							echo form_dropdown("from_day", $arrayDay, 'monday', "class='form-control' id='copy_from_day'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
					</div>
					<div class="form-group">
						<label class="control-label">To day <span class="required">*</span></label>
						<?php
							echo form_dropdown("to_day", $arrayDay, 'tuesday', "class='form-control' id='copy_to_day'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
					</div>
				</div>

				<div class="copy-mode-panel" data-mode="year" style="display:none;">
					<div class="form-group">
						<label class="control-label">From academic year <span class="required">*</span></label>
						<?php
							$arrayFromYear = array('' => translate('select'));
							foreach ($schoolyears as $year) {
								$arrayFromYear[$year->id] = $year->school_year;
							}
							echo form_dropdown("from_session_id", $arrayFromYear, get_session_id(), "class='form-control' id='copy_from_session'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
					</div>
					<div class="form-group">
						<label class="control-label">To academic year <span class="required">*</span></label>
						<?php
							$arrayToYear = array('' => translate('select'));
							foreach ($schoolyears as $year) {
								$arrayToYear[$year->id] = $year->school_year;
							}
							echo form_dropdown("to_session_id", $arrayToYear, '', "class='form-control' id='copy_to_session'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
					</div>
				</div>

				<div class="checkbox-replace mt-md">
					<label class="i-checks">
						<input type="checkbox" name="replace" value="1" checked>
						<i></i> Replace existing periods for this teacher on the target
					</label>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default"><i class="fas fa-copy"></i> Copy now</button>
						<button type="button" class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close(); ?>
		</section>
	</div>
	<?php endif; ?>

	<script type="text/javascript">
		$(document).ready(function () {
			"use strict";

			var reloadUrl = <?= json_encode(base_url('timetable/teacherview?' . http_build_query(array(
				'branch_id' => (int) $branch_id,
				'staff_id' => (int) $staff_id,
			)))) ?>;
			var deleteUrl = <?= json_encode(base_url('timetable/class_period_delete')) ?>;
			var teacherId = <?= (int) $staff_id ?>;
			var pendingSectionId = 0;
			var pendingSubjectId = 0;

			function toggleCopyModePanels() {
				var mode = $('input[name="copy_mode"]:checked').val();
				$('.copy-mode-panel').hide();
				$('.copy-mode-panel[data-mode="' + mode + '"]').show();
			}

			function toggleCellTeachingFields() {
				var isBreak = $('#cell_break').is(':checked');
				$('.cell-teaching-fields').toggle(!isBreak);
				$('#cell_subject').prop('disabled', isBreak);
			}

			function loadSections(classId, selectedSection) {
				pendingSectionId = selectedSection || 0;
				$('#cell_section_id').html('<option value=""><?=translate('select')?></option>');
				if (!classId) {
					return;
				}
				$.ajax({
					url: base_url + 'ajax/getSectionByClass',
					type: 'POST',
					data: { class_id: classId, all: 0, multi: 0 },
					success: function (response) {
						$('#cell_section_id').html(response);
						if (pendingSectionId) {
							$('#cell_section_id').val(String(pendingSectionId)).trigger('change');
						} else {
							$('#cell_section_id').trigger('change');
						}
					}
				});
			}

			function loadSubjects(classId, selectedSubject) {
				pendingSubjectId = selectedSubject || 0;
				$('#cell_subject').html('<option value=""><?=translate('select')?></option>');
				if (!classId) {
					return;
				}
				$.ajax({
					url: base_url + 'ajax/getSubjectByClass',
					type: 'POST',
					data: { classID: classId },
					success: function (response) {
						$('#cell_subject').html(response);
						if (pendingSubjectId) {
							$('#cell_subject').val(String(pendingSubjectId)).trigger('change');
						} else {
							$('#cell_subject').trigger('change');
						}
					}
				});
			}

			function openCellModal(mode, data) {
				$('#frmCellSchedule')[0].reset();
				$('#frmCellSchedule').find('.error').html('');
				$('#cell_id').val(data.id || 0);
				$('#cell_day').val(data.day || '');
				$('#cell_day_label').val((data.day || '').toUpperCase());
				$('#cell_teacher_id').val(teacherId);
				$('#cell_break').prop('checked', !!data.isBreak);
				$('#cell_room').val(data.room || '');
				$('#cell_time_start').val(data.timeStart || '');
				$('#cell_time_end').val(data.timeEnd || '');
				$('#cellScheduleModalTitle').html(
					mode === 'add'
						? '<i class="fas fa-plus"></i> Add period'
						: '<i class="fas fa-pen-nib"></i> Edit period'
				);
				if (mode === 'edit' && data.id) {
					$('#btnCellDelete').show();
					$('.cell-class-fields').find('select').prop('disabled', false);
				} else {
					$('#btnCellDelete').hide();
				}
				toggleCellTeachingFields();
				$('#cell_class_id').val(data.classId ? String(data.classId) : '').trigger('change.select2');
				loadSections(data.classId || 0, data.sectionId || 0);
				loadSubjects(data.classId || 0, data.subject || 0);
				mfp_modal('#cellScheduleModal');
				setTimeout(function () {
					if ($.fn.timepicker) {
						$('#cell_time_start, #cell_time_end').timepicker({ minuteStep: 5, showMeridian: true });
					}
				}, 150);
			}

			$(document).on('click', '.copy_schedule_modal', function () {
				mfp_modal('#copyScheduleModal');
				toggleCopyModePanels();
			});
			$(document).on('change', 'input[name="copy_mode"]', toggleCopyModePanels);
			$(document).on('change', '#cell_break', toggleCellTeachingFields);

			$(document).on('change', '#cell_class_id', function () {
				var classId = $(this).val();
				loadSections(classId, 0);
				loadSubjects(classId, 0);
			});

			$(document).on('click', '.tt-edit-cell', function (e) {
				e.preventDefault();
				var $td = $(this).closest('td');
				openCellModal('edit', {
					id: $td.data('id'),
					day: $td.data('day'),
					classId: $td.data('class'),
					sectionId: $td.data('section'),
					isBreak: String($td.data('break')) === '1',
					subject: $td.data('subject'),
					timeStart: $td.data('time-start'),
					timeEnd: $td.data('time-end'),
					room: $td.data('room')
				});
			});

			$(document).on('click', '.tt-add-cell', function (e) {
				e.preventDefault();
				openCellModal('add', {
					id: 0,
					day: $(this).data('day'),
					classId: '',
					sectionId: '',
					isBreak: false,
					subject: '',
					timeStart: '',
					timeEnd: '',
					room: ''
				});
			});

			$('#frmCellSchedule').on('submit', function (e) {
				e.preventDefault();
				var $form = $(this);
				var $btn = $('#btnCellSave');
				$btn.button('loading');
				$.ajax({
					url: $form.attr('action'),
					type: 'POST',
					data: $form.serialize(),
					dataType: 'json',
					success: function (data) {
						$btn.button('reset');
						$form.find('.error').html('');
						if (data.status === 'success') {
							if (typeof swal === 'function') {
								swal({ type: 'success', title: 'OK', text: data.message, timer: 1200, showConfirmButton: false });
							}
							setTimeout(function () { window.location.href = reloadUrl; }, 400);
						} else if (data.error) {
							$.each(data.error, function (key, val) {
								$form.find('[name="' + key + '"]').closest('.form-group').find('.error').html(val);
							});
						} else if (typeof swal === 'function') {
							swal({ type: 'error', title: 'Error', text: data.message || 'Save failed.' });
						}
					},
					error: function () {
						$btn.button('reset');
						if (typeof swal === 'function') {
							swal({ type: 'error', title: 'Error', text: 'Save failed.' });
						}
					}
				});
			});

			$(document).on('click', '#btnCellDelete', function () {
				var id = parseInt($('#cell_id').val(), 10) || 0;
				var classId = parseInt($('#cell_class_id').val(), 10) || 0;
				var sectionId = parseInt($('#cell_section_id').val(), 10) || 0;
				if (!id || !classId || !sectionId) {
					return;
				}
				var doDelete = function () {
					var delData = {
						id: id,
						branch_id: <?= (int) $branch_id ?>,
						class_id: classId,
						section_id: sectionId
					};
					var $csrf = $('#frmCellSchedule').find('input[name="<?= $this->security->get_csrf_token_name() ?>"]');
					if ($csrf.length) {
						delData[$csrf.attr('name')] = $csrf.val();
					}
					$.ajax({
						url: deleteUrl,
						type: 'POST',
						dataType: 'json',
						data: delData,
						success: function (data) {
							if (data.status === 'success') {
								window.location.href = reloadUrl;
							} else if (typeof swal === 'function') {
								swal({ type: 'error', title: 'Error', text: data.message || 'Delete failed.' });
							}
						}
					});
				};
				if (typeof swal === 'function') {
					swal({
						title: 'Delete this period?',
						type: 'warning',
						showCancelButton: true,
						confirmButtonClass: 'btn btn-default',
						confirmButtonText: 'Yes, delete'
					}, function (isConfirm) {
						if (isConfirm) {
							doDelete();
						}
					});
				} else if (window.confirm('Delete this period?')) {
					doDelete();
				}
			});
		});
	</script>
<?php endif;?>
