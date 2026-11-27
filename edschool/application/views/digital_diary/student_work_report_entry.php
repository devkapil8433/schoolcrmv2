<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$report_date = isset($report_date) ? $report_date : date('Y-m-d');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$class_options = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$students = isset($students) ? $students : array();
$subjects_for_grid = isset($subjects_for_grid) ? $subjects_for_grid : array();
$saved_maps_by_subject = isset($saved_maps_by_subject) ? $saved_maps_by_subject : array();
$discipline_display_map = isset($discipline_display_map) ? $discipline_display_map : array();
$swr_subject_can_edit = isset($swr_subject_can_edit) ? $swr_subject_can_edit : array();
$can_edit_discipline = !empty($can_edit_discipline);
$swr_table_ok = !empty($swr_table_ok);
$is_day_final = !empty($is_day_final);
$swr_has_saved_data = !empty($swr_has_saved_data);
$submission_prefills = isset($submission_prefills) && is_array($submission_prefills) ? $submission_prefills : array();
$subject_has_test = isset($subject_has_test) && is_array($subject_has_test) ? $subject_has_test : array();
$ns = count($subjects_for_grid);
$any_test = false;
foreach ($subjects_for_grid as $sgt) {
	if (!empty($subject_has_test[(int) $sgt['subject_id']])) {
		$any_test = true;
		break;
	}
}
$report_q_base = array(
	'date' => $report_date,
	'class_id' => $filter_class_id,
	'section_id' => $filter_section_id,
);
if (!empty($branch_id)) {
	$report_q_base['branch_id'] = $branch_id;
}
// Sort: App-submitted students first (visually distinct)
$students_app = array();
$students_other = array();
foreach ($students as $stSort) {
	$sidSort = (int) $stSort['student_id'];
	$hasApp = !empty($submission_prefills[$sidSort]);
	if ($hasApp) {
		$students_app[] = $stSort;
	} else {
		$students_other[] = $stSort;
	}
}
$students = array_merge($students_app, $students_other);
?>
<style type="text/css">
.swr-app-submitted { background: #e8f5e9 !important; }
.swr-incomplete { background: #fff3e0 !important; }
.swr-app-badge { display:inline-block; margin-left:6px; padding:1px 6px; border-radius:3px; background:#2e7d32; color:#fff; font-size:10px; font-weight:600; }
.swr-pending-badge { display:inline-block; margin-left:6px; padding:1px 6px; border-radius:3px; background:#9e9e9e; color:#fff; font-size:10px; font-weight:600; }
.swr-submitted-badge { display:inline-block; margin-left:4px; padding:1px 5px; border-radius:3px; background:#1565c0; color:#fff; font-size:9px; font-weight:600; }
.swr-yesterday { background:#fafafa; border:1px solid #e0e0e0; border-radius:6px; padding:12px 14px; margin-bottom:16px; }
.swr-yesterday h5 { margin:0 0 8px; font-weight:600; color:#333; }
.swr-yesterday table { margin-bottom:0; font-size:12px; }
.swr-hw-strip { margin-top: 18px; padding-top: 16px; border-top: 1px solid #e0e0e0; }
.swr-hw-title { color: #1a237e; font-weight: 700; font-size: 18px; margin: 0 0 6px 0; letter-spacing: 0.02em; }
.swr-hw-subtitle { color: #666; font-size: 12px; margin: 0 0 14px 0; }
.swr-hw-row {
	display: flex;
	flex-wrap: nowrap;
	gap: 14px;
	overflow-x: auto;
	overflow-y: hidden;
	padding: 4px 2px 12px;
	-webkit-overflow-scrolling: touch;
}
.swr-hw-card { flex: 0 0 auto; width: 210px; text-align: center; }
.swr-hw-card-label { font-size: 13px; font-weight: 700; color: #222; margin-bottom: 8px; line-height: 1.2; min-height: 2.4em; display: flex; align-items: flex-end; justify-content: center; }
.swr-hw-card-box {
	border: 1px solid #333;
	border-radius: 8px;
	padding: 8px 8px 10px;
	background: #fff;
}
.swr-hw-metric {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 8px;
	padding: 6px 4px;
	border-bottom: 1px solid #eee;
	text-align: left;
}
.swr-hw-metric:last-child { border-bottom: 0; }
.swr-hw-metric-name { font-size: 12px; font-weight: 700; min-width: 58px; }
.swr-hw-metric-name.fair { color: #1565c0; }
.swr-hw-metric-name.writing { color: #2e7d32; }
.swr-hw-metric-name.learning { color: #ef6c00; }
.swr-hw-metric-vals { display: flex; align-items: center; gap: 6px; font-family: Georgia, "Times New Roman", Times, serif; }
.swr-hw-frac { display: inline-flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; min-width: 22px; }
.swr-hw-num, .swr-hw-den { font-size: 14px; font-weight: 600; color: #111; }
.swr-hw-bar { width: 100%; min-width: 22px; border: none; border-top: 1px solid #111; margin: 2px 0; align-self: stretch; }
.swr-hw-eq { font-size: 12px; white-space: nowrap; color: #111; min-width: 62px; text-align: right; }
.swr-chart-wrap { max-width: 860px; margin: 18px auto 0; height: 300px; }
</style>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-user-graduate"></i> Today's Work — Step 1: Student Work Report<?= $ns > 0 ? ' <small class="text-muted">(' . (int) $ns . ' ' . translate('subject') . ')</small>' : '' ?></h4>
				<div class="panel-btn">
					<a href="<?= base_url('digital_diary/student_work_report') . (is_superadmin_loggedin() && !empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '') ?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<?php if (!$swr_table_ok): ?>
				<div class="alert alert-warning">
					<strong>Setup required.</strong> Run <code>application/sql/digital_diary_student_work_report.sql</code> on the database.
				</div>
				<?php endif; ?>

				<?php if (is_superadmin_loggedin() && empty($branch_id)): ?>
				<section class="panel mb-md">
					<header class="panel-heading"><h4 class="panel-title"><?= translate('branch') ?></h4></header>
					<div class="panel-body">
						<?php echo form_open(base_url('digital_diary/student_work_report_entry'), array('method' => 'get', 'class' => 'form-inline')); ?>
						<div class="form-group">
							<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
							<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' data-plugin-selectTwo data-width='280px' required");
							?>
						</div>
						<button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
						<?php echo form_close(); ?>
					</div>
				</section>
				<?php elseif (is_superadmin_loggedin() && !empty($branch_id)): ?>
				<div class="mb-md">
					<strong><?= translate('branch') ?>:</strong> <?= html_escape($branch_name ?: get_type_name_by_id('branch', $branch_id)) ?>
					<a href="<?= base_url('digital_diary/student_work_report_entry') ?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> <?= translate('change') ?></a>
				</div>
				<?php endif; ?>

				<?php if ($swr_table_ok && (!is_superadmin_loggedin() || !empty($branch_id))): ?>
				<?php echo form_open(base_url('digital_diary/student_work_report_entry'), array('method' => 'get', 'class' => 'mb-md', 'id' => 'frm_swr_filter')); ?>
				<?php if (!empty($branch_id)): ?>
				<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
				<?php endif; ?>
				<div class="row mb-sm">
					<div class="col-md-2">
						<label><?= translate('date') ?></label>
						<input type="text" class="form-control" name="date" id="swr_date" value="<?= html_escape($report_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
					</div>
					<div class="col-md-2">
						<label><?= translate('class') ?></label>
						<?= form_dropdown('class_id', $class_options, $filter_class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="col-md-2">
						<label><?= translate('section') ?></label>
						<?= form_dropdown('section_id', $section_options, $filter_section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'") ?>
					</div>
				</div>
				<?php echo form_close(); ?>

				<?php if (!empty($filter_class_id) && !empty($filter_section_id) && ($is_day_final || $swr_has_saved_data)): ?>
				<?php
					$swr_date_label = date('j F Y', strtotime($report_date));
					$tw_continue_q = array(
						'date' => $report_date,
						'class_id' => (int) $filter_class_id,
						'section_id' => (int) $filter_section_id,
					);
					if (!empty($branch_id)) {
						$tw_continue_q['branch_id'] = (int) $branch_id;
					}
					$tw_continue_url = base_url('digital_diary/today_work') . '?' . http_build_query($tw_continue_q);
				?>
				<?php if ($is_day_final): ?>
				<?php $can_unlock_final_day = !empty($can_unlock_final_day); ?>
				<div class="alert alert-warning swr-final-notice mb-md">
					<div class="row" style="display:flex;flex-wrap:wrap;align-items:center;">
						<div class="col-xs-12 col-sm-7" style="margin-bottom:8px;">
							<i class="fas fa-lock"></i>
							<strong>Final submitted for <?= html_escape($swr_date_label) ?>.</strong>
						</div>
						<div class="col-xs-12 col-sm-5 text-right" style="margin-bottom:0;">
							<a href="<?= html_escape($tw_continue_url) ?>" class="btn btn-primary"><i class="fas fa-eye"></i> View Today's Work</a>
							<?php if ($can_unlock_final_day): ?>
							<?php echo form_open('digital_diary/today_work_final_revert', array('id' => 'frm_swr_unlock_day', 'style' => 'display:inline-block;margin-left:6px;')); ?>
							<input type="hidden" name="work_date" value="<?= html_escape($report_date) ?>">
							<input type="hidden" name="class_id" value="<?= (int) $filter_class_id ?>">
							<input type="hidden" name="section_id" value="<?= (int) $filter_section_id ?>">
							<?php if (!empty($branch_id)): ?>
							<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
							<?php endif; ?>
							<button type="submit" class="btn btn-default"><i class="fas fa-unlock"></i> Unlock (admin)</button>
							<?php echo form_close(); ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php else: ?>
				<div class="alert alert-success swr-saved-notice mb-md">
					<div class="row" style="display:flex;flex-wrap:wrap;align-items:center;">
						<div class="col-xs-12 col-sm-8" style="margin-bottom:8px;">
							<i class="fas fa-check-circle"></i>
							<strong>Step 1 done for <?= html_escape($swr_date_label) ?>.</strong>
						</div>
						<div class="col-xs-12 col-sm-4 text-right">
							<a href="<?= html_escape($tw_continue_url) ?>" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Step 2: Add Today's Work</a>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<?php endif; ?>

				<?php if (!empty($filter_class_id) && !empty($filter_section_id) && $ns > 0 && !empty($students)): ?>

				<?php
				$yesterday_work_rows = isset($yesterday_work_rows) ? $yesterday_work_rows : array();
				$yesterday_work_date = isset($yesterday_work_date) ? $yesterday_work_date : '';
				if (!empty($yesterday_work_rows)):
				?>
				<div class="swr-yesterday">
					<h5><i class="fas fa-history"></i> Previous work (<?= html_escape(date('d-m-Y', strtotime($yesterday_work_date))) ?>) — verify before marking today</h5>
					<div class="table-responsive">
						<table class="table table-bordered table-condensed mb-none">
							<thead>
								<tr>
									<th>Subject</th>
									<th>Class work</th>
									<th>Home work</th>
									<th>Learning</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($yesterday_work_rows as $ywr): ?>
								<tr>
									<td><?= html_escape($ywr['subject_name']) ?></td>
									<td><?= html_escape($ywr['class_work']) ?></td>
									<td><?= html_escape($ywr['home_work']) ?></td>
									<td><?= html_escape($ywr['learning']) ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php endif; ?>

				<p class="mb-sm">
					<span class="swr-app-badge">Submitted</span> via portal/app.
					<span class="swr-pending-badge">Pending</span> = not submitted yet.
					Orange rows = incomplete work (unchecked). Discipline editable by class teacher only.
				</p>

				<?php
				echo form_open(base_url('digital_diary/student_work_report_save'), array('class' => 'frm-submit-data', 'id' => 'frm_swr_save'));
				?>
				<input type="hidden" name="work_date" value="<?= html_escape($report_date) ?>">
				<input type="hidden" name="class_id" value="<?= (int) $filter_class_id ?>">
				<input type="hidden" name="section_id" value="<?= (int) $filter_section_id ?>">
				<?php if (is_superadmin_loggedin()): ?>
				<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
				<?php else: ?>
				<input type="hidden" name="branch_id" value="<?= (int) get_loggedin_branch_id() ?>">
				<?php endif; ?>

				<div class="table-responsive" style="overflow-x:auto;">
					<table class="table table-bordered table-condensed text-center" style="margin-bottom:0;min-width:<?= 400 + max(0, $ns) * 220 ?>px;">
						<thead>
							<tr>
								<th rowspan="2" class="text-center" style="vertical-align:middle;width:44px;">Sr.</th>
								<th rowspan="2" class="text-center" style="vertical-align:middle;min-width:140px;">Student's Name</th>
								<th colspan="2" class="text-center">Discipline</th>
								<?php foreach ($subjects_for_grid as $sg):
									$subjCols = 4 + (!empty($subject_has_test[(int) $sg['subject_id']]) ? 1 : 0);
								?>
								<th colspan="<?= (int) $subjCols ?>" class="text-center" style="background:#f5f5f5;border-left:2px solid #999;"><?= html_escape($sg['subject_name']) ?></th>
								<?php endforeach; ?>
							</tr>
							<tr>
								<th class="text-center">Dress</th>
								<th class="text-center">Conduct</th>
								<?php foreach ($subjects_for_grid as $sg): ?>
								<th class="text-center" style="border-left:2px solid #999;">Fair Copy</th>
								<th class="text-center">Writing</th>
								<th class="text-center">Learning</th>
								<th class="text-center">Remarks</th>
								<?php if (!empty($subject_has_test[(int) $sg['subject_id']])): ?>
								<th class="text-center">Marks</th>
								<?php endif; ?>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php
							$sr = 1;
							foreach ($students as $st):
								$sid = (int) $st['student_id'];
								$dd = isset($discipline_display_map[$sid]) ? $discipline_display_map[$sid] : array('discipline_dress' => 1, 'discipline_conduct' => 1);
								$dDress = !empty($dd['discipline_dress']);
								$dConduct = !empty($dd['discipline_conduct']);
								$discLocked = $is_day_final || !$can_edit_discipline;
								$rowIncomplete = false;
								foreach ($subjects_for_grid as $sgCheck) {
									$checkSid = (int) $sgCheck['subject_id'];
									$has_saved_c = isset($saved_maps_by_subject[$checkSid][$sid]);
									$saved_c = $has_saved_c ? $saved_maps_by_subject[$checkSid][$sid] : array();
									$pref_c = isset($submission_prefills[$sid][$checkSid]) ? $submission_prefills[$sid][$checkSid] : array();
									$fcOk = $has_saved_c ? !empty($saved_c['fair_copy']) : (empty($pref_c) ? true : false);
									$wwOk = $has_saved_c ? !empty($saved_c['writing_work']) : !empty($pref_c['writing_work']);
									$lwOk = $has_saved_c ? !empty($saved_c['learning_work']) : !empty($pref_c['learning_work']);
									if ($has_saved_c && (!$fcOk || !$wwOk || !$lwOk)) {
										$rowIncomplete = true;
										break;
									}
								}
								$rowClass = '';
								if (!empty($submission_prefills[$sid])) {
									$rowClass = 'swr-app-submitted';
								}
								if ($rowIncomplete) {
									$rowClass = trim($rowClass . ' swr-incomplete');
								}
							?>
							<tr class="<?= html_escape($rowClass) ?>">
								<td class="text-center"><?= $sr++ ?></td>
								<td class="text-left">
									<?= html_escape($st['fullname']) ?>
									<?php if (!empty($submission_prefills[$sid])): ?>
										<span class="swr-app-badge" title="Submitted via App / portal">Submitted</span>
									<?php else: ?>
										<span class="swr-pending-badge">Pending</span>
									<?php endif; ?>
									<?php if ($rowIncomplete): ?>
										<span class="swr-pending-badge" style="background:#ef6c00;">Incomplete</span>
									<?php endif; ?>
									<?php
										$rq = $report_q_base;
										$rq['student_id'] = $sid;
										$reportUrl = base_url('digital_diary/parent_report_format') . '?' . http_build_query($rq);
									?>
									<a href="<?= html_escape($reportUrl) ?>" class="btn btn-xs btn-default" target="_blank" title="Report"><i class="fas fa-file-alt"></i> Report</a>
								</td>
								<td class="text-center">
									<?php if ($discLocked): ?>
									<input type="checkbox" value="1"<?= $dDress ? ' checked' : '' ?> disabled title="<?= $can_edit_discipline ? 'Day is locked.' : 'Only class teacher can edit Discipline.' ?>">
									<?php else: ?>
									<input type="checkbox" name="disc[<?= $sid ?>][dress]" value="1"<?= $dDress ? ' checked' : '' ?>>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php if ($discLocked): ?>
									<input type="checkbox" value="1"<?= $dConduct ? ' checked' : '' ?> disabled title="<?= $can_edit_discipline ? 'Day is locked.' : 'Only class teacher can edit Discipline.' ?>">
									<?php else: ?>
									<input type="checkbox" name="disc[<?= $sid ?>][conduct]" value="1"<?= $dConduct ? ' checked' : '' ?>>
									<?php endif; ?>
								</td>
								<?php foreach ($subjects_for_grid as $sg):
									$subjId = (int) $sg['subject_id'];
									$canWork = !empty($swr_subject_can_edit[$subjId]);
									$workLocked = $is_day_final || !$canWork;
									$has_saved = isset($saved_maps_by_subject[$subjId][$sid]);
									$saved = $has_saved ? $saved_maps_by_subject[$subjId][$sid] : array();
									$pref = isset($submission_prefills[$sid][$subjId]) ? $submission_prefills[$sid][$subjId] : array();
									$ch = function ($key) use ($has_saved, $saved, $pref) {
										if ($has_saved) {
											return !empty($saved[$key]) ? ' checked' : '';
										}
										if ($key === 'writing_work' && !empty($pref['writing_work'])) {
											return ' checked';
										}
										if ($key === 'learning_work' && !empty($pref['learning_work'])) {
											return ' checked';
										}
										// Default checked when no portal submission and no saved row (legacy behaviour)
										if (empty($pref)) {
											return ' checked';
										}
										return '';
									};
									$remarks = isset($saved['remarks']) ? html_escape($saved['remarks']) : '';
									$marksVal = isset($pref['marks']) && $pref['marks'] !== '' && $pref['marks'] !== null ? html_escape($pref['marks']) : '';
								?>
								<td class="text-center" style="border-left:2px solid #999;">
									<?php
									$swrCbAttrs = ' class="swr-work-cb" data-subject="' . (int) $subjId . '" data-student="' . (int) $sid . '"';
									if ($workLocked) {
										$fc = $has_saved ? !empty($saved['fair_copy']) : (empty($pref) ? true : false);
										echo '<input type="checkbox" value="1"' . $swrCbAttrs . ' data-field="fair_copy"' . ($fc ? ' checked' : '') . ' disabled title="You can only edit your own subject(s).">';
									} else {
										echo '<input type="checkbox" name="swr[' . $subjId . '][' . $sid . '][fair_copy]" value="1"' . $swrCbAttrs . ' data-field="fair_copy"' . $ch('fair_copy') . '>';
									}
									?>
								</td>
								<td class="text-center">
									<?php
									if ($workLocked) {
										$ww = $has_saved ? !empty($saved['writing_work']) : !empty($pref['writing_work']);
										if (!$has_saved && empty($pref)) {
											$ww = true;
										}
										echo '<input type="checkbox" value="1"' . $swrCbAttrs . ' data-field="writing_work"' . ($ww ? ' checked' : '') . ' disabled>';
									} else {
										echo '<input type="checkbox" name="swr[' . $subjId . '][' . $sid . '][writing_work]" value="1"' . $swrCbAttrs . ' data-field="writing_work"' . $ch('writing_work') . '>';
									}
									if (!empty($pref['hw_photo'])):
									?>
									<br><a href="<?= base_url('uploads/attachments/digital_diary_submit/' . rawurlencode($pref['hw_photo'])) ?>" target="_blank" class="btn btn-xs btn-link" title="HW photo"><i class="fas fa-image"></i></a>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php
									if ($workLocked) {
										$lw = $has_saved ? !empty($saved['learning_work']) : !empty($pref['learning_work']);
										if (!$has_saved && empty($pref)) {
											$lw = true;
										}
										echo '<input type="checkbox" value="1"' . $swrCbAttrs . ' data-field="learning_work"' . ($lw ? ' checked' : '') . ' disabled>';
									} else {
										echo '<input type="checkbox" name="swr[' . $subjId . '][' . $sid . '][learning_work]" value="1"' . $swrCbAttrs . ' data-field="learning_work"' . $ch('learning_work') . '>';
									}
									if (!empty($pref['learning_status'])):
									?>
									<br><span class="swr-submitted-badge"><?= html_escape(ucfirst($pref['learning_status'])) ?></span>
									<?php elseif (!empty($tw_id_by_subject[$subjId])): ?>
									<br><span class="swr-pending-badge">Pending</span>
									<?php endif; ?>
								</td>
								<td class="text-left">
									<?php if ($workLocked): ?>
									<input type="text" class="form-control input-sm" value="<?= $remarks ?>" placeholder="Text..." disabled title="You can only edit your own subject(s).">
									<?php else: ?>
									<input type="text" class="form-control input-sm" name="swr[<?= $subjId ?>][<?= $sid ?>][remarks]" value="<?= $remarks ?>" placeholder="Text...">
									<?php endif; ?>
								</td>
								<?php if (!empty($subject_has_test[$subjId])): ?>
								<td class="text-center">
									<?php if ($workLocked): ?>
									<input type="text" class="form-control input-sm" value="<?= $marksVal ?>" disabled style="width:64px;display:inline-block;">
									<?php else: ?>
									<input type="number" step="0.01" min="0" class="form-control input-sm" name="marks[<?= $subjId ?>][<?= $sid ?>]" value="<?= $marksVal ?>" placeholder="Marks" style="width:72px;display:inline-block;">
									<?php endif; ?>
								</td>
								<?php endif; ?>
								<?php endforeach; ?>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div class="text-right mt-md">
					<button type="submit" class="btn btn-primary" id="btn_swr_submit" <?= $is_day_final ? 'disabled' : '' ?>><i class="fas fa-save"></i> Submit &amp; continue to Step 2: Today's Work</button>
				</div>
				<?php echo form_close(); ?>

				<?php elseif (!empty($filter_class_id) && !empty($filter_section_id) && $ns > 0 && empty($students)): ?>
				<p class="text-muted text-center">No students found for this class and section.</p>
				<?php elseif (empty($filter_class_id) || empty($filter_section_id)): ?>
				<p class="text-muted"><?= translate('select') ?> <?= translate('class') ?> and <?= translate('section') ?> <?= translate('to') ?> <?= translate('continue') ?>.</p>
				<?php elseif ($ns === 0 && !empty($filter_class_id) && !empty($filter_section_id)): ?>
				<p class="text-muted">No subjects are assigned to this class and section.</p>
				<?php endif; ?>

				<?php if (!empty($filter_class_id) && !empty($filter_section_id) && $ns > 0 && !empty($students)): ?>
				<?php
				// Same effective checkbox rules as the grid (saved → portal prefill → default checked).
				$swr_flag_on = function ($key, $has_saved, $saved, $pref) {
					if ($has_saved) {
						return !empty($saved[$key]);
					}
					if ($key === 'writing_work' && !empty($pref['writing_work'])) {
						return true;
					}
					if ($key === 'learning_work' && !empty($pref['learning_work'])) {
						return true;
					}
					if (empty($pref)) {
						return true;
					}
					return false;
				};
				$swr_metric_defs = array(
					'fair_copy' => array('key' => 'fair', 'label' => 'Fair', 'field' => 'fair_copy'),
					'writing_work' => array('key' => 'writing', 'label' => 'Writing', 'field' => 'writing_work'),
					'learning_work' => array('key' => 'learning', 'label' => 'Learning', 'field' => 'learning_work'),
				);
				$chart_labels = array();
				$chart_fair = array();
				$chart_writing = array();
				$chart_learning = array();
				$subject_ids_for_js = array();
				?>
				<div class="swr-hw-strip mt-lg mb-none" id="swr_hw_strip"
					data-student-count="<?= (int) count($students) ?>">
					<div class="swr-hw-title">Subject wise work Percentage</div>
					<div class="swr-hw-row">
						<?php foreach ($subjects_for_grid as $sg_hw):
							$hwSid = (int) $sg_hw['subject_id'];
							$hwName = isset($sg_hw['subject_name']) ? $sg_hw['subject_name'] : '';
							$hw_total = count($students);
							$counts = array('fair_copy' => 0, 'writing_work' => 0, 'learning_work' => 0);
							foreach ($students as $st_hw) {
								$hw_stu = (int) $st_hw['student_id'];
								$has_saved_hw = isset($saved_maps_by_subject[$hwSid][$hw_stu]);
								$saved_hw = $has_saved_hw ? $saved_maps_by_subject[$hwSid][$hw_stu] : array();
								$pref_hw = isset($submission_prefills[$hw_stu][$hwSid]) ? $submission_prefills[$hw_stu][$hwSid] : array();
								foreach ($counts as $fieldKey => $_) {
									if ($swr_flag_on($fieldKey, $has_saved_hw, $saved_hw, $pref_hw)) {
										$counts[$fieldKey]++;
									}
								}
							}
							$pcts = array();
							foreach ($counts as $fieldKey => $doneCnt) {
								$pcts[$fieldKey] = $hw_total > 0 ? round($doneCnt * 100 / $hw_total, 2) : 0;
							}
							$chart_labels[] = $hwName !== '' ? $hwName : '—';
							$chart_fair[] = (float) $pcts['fair_copy'];
							$chart_writing[] = (float) $pcts['writing_work'];
							$chart_learning[] = (float) $pcts['learning_work'];
							$subject_ids_for_js[] = $hwSid;
						?>
						<div class="swr-hw-card" data-subject-id="<?= (int) $hwSid ?>">
							<div class="swr-hw-card-label"><?= html_escape($hwName !== '' ? $hwName : '—') ?></div>
							<div class="swr-hw-card-box">
								<?php foreach ($swr_metric_defs as $fieldKey => $meta): ?>
								<div class="swr-hw-metric" data-field="<?= html_escape($fieldKey) ?>">
									<span class="swr-hw-metric-name <?= html_escape($meta['key']) ?>"><?= html_escape($meta['label']) ?></span>
									<span class="swr-hw-metric-vals">
										<span class="swr-hw-frac">
											<span class="swr-hw-num"><?= (int) $counts[$fieldKey] ?></span>
											<span class="swr-hw-bar"></span>
											<span class="swr-hw-den"><?= (int) $hw_total ?></span>
										</span>
										<span class="swr-hw-eq">= <span class="swr-hw-pct"><?= number_format((float) $pcts[$fieldKey], 2) ?></span>%</span>
									</span>
								</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
					<?php if (!empty($chart_labels)): ?>
					<div class="swr-chart-wrap">
						<canvas id="swr_completion_chart" height="300"></canvas>
					</div>
					<script type="text/javascript">
					(function () {
						var subjectIds = <?= json_encode(array_values($subject_ids_for_js)) ?>;
						var labels = <?= json_encode($chart_labels) ?>;
						var fields = ['fair_copy', 'writing_work', 'learning_work'];
						var chart = null;

						function pctFromDone(done, total) {
							return total > 0 ? Math.round((done * 10000) / total) / 100 : 0;
						}

						function calcTodayBySubject() {
							var stats = {};
							var i, sid, f;
							for (i = 0; i < subjectIds.length; i++) {
								sid = String(subjectIds[i]);
								stats[sid] = {
									fair_copy: { done: 0, total: 0 },
									writing_work: { done: 0, total: 0 },
									learning_work: { done: 0, total: 0 }
								};
							}
							$('.swr-work-cb').each(function () {
								var $cb = $(this);
								sid = String($cb.data('subject') || '');
								f = String($cb.data('field') || '');
								if (!sid || !stats[sid] || !stats[sid][f]) return;
								stats[sid][f].total++;
								if ($cb.is(':checked')) {
									stats[sid][f].done++;
								}
							});
							var fallbackTotal = parseInt($('#swr_hw_strip').data('student-count'), 10) || 0;
							var out = [];
							for (i = 0; i < subjectIds.length; i++) {
								sid = String(subjectIds[i]);
								var row = { subjectId: subjectIds[i], metrics: {} };
								for (var fi = 0; fi < fields.length; fi++) {
									f = fields[fi];
									var t = stats[sid][f].total || 0;
									var d = stats[sid][f].done || 0;
									if (t < 1) t = fallbackTotal;
									row.metrics[f] = { done: d, total: t, pct: pctFromDone(d, t) };
								}
								out.push(row);
							}
							return out;
						}

						function refreshHwStripAndChart() {
							var rows = calcTodayBySubject();
							var fairPct = [], writingPct = [], learningPct = [];
							for (var i = 0; i < rows.length; i++) {
								var r = rows[i];
								var $card = $('.swr-hw-card[data-subject-id="' + r.subjectId + '"]');
								for (var fi = 0; fi < fields.length; fi++) {
									var f = fields[fi];
									var m = r.metrics[f];
									var $metric = $card.find('.swr-hw-metric[data-field="' + f + '"]');
									$metric.find('.swr-hw-num').text(m.done);
									$metric.find('.swr-hw-den').text(m.total);
									$metric.find('.swr-hw-pct').text(m.pct.toFixed(2));
								}
								fairPct.push(r.metrics.fair_copy.pct);
								writingPct.push(r.metrics.writing_work.pct);
								learningPct.push(r.metrics.learning_work.pct);
							}
							if (chart && chart.data && chart.data.datasets) {
								chart.data.datasets[0].data = fairPct;
								chart.data.datasets[1].data = writingPct;
								chart.data.datasets[2].data = learningPct;
								chart.update();
							}
						}

						window.swrRefreshHwStats = refreshHwStripAndChart;

						$(document).on('change', '.swr-work-cb', function () {
							refreshHwStripAndChart();
						});

						if (typeof Chart !== 'undefined') {
							var ctx = document.getElementById('swr_completion_chart');
							if (ctx) {
								var initial = calcTodayBySubject();
								chart = new Chart(ctx.getContext('2d'), {
									type: 'bar',
									data: {
										labels: labels,
										datasets: [
											{
												label: 'Fair %',
												data: initial.map(function (r) { return r.metrics.fair_copy.pct; }),
												backgroundColor: 'rgba(21, 101, 192, 0.75)'
											},
											{
												label: 'Writing %',
												data: initial.map(function (r) { return r.metrics.writing_work.pct; }),
												backgroundColor: 'rgba(46, 125, 50, 0.75)'
											},
											{
												label: 'Learning %',
												data: initial.map(function (r) { return r.metrics.learning_work.pct; }),
												backgroundColor: 'rgba(239, 108, 0, 0.75)'
											}
										]
									},
									options: {
										responsive: true,
										maintainAspectRatio: false,
										scales: {
											yAxes: [{ ticks: { beginAtZero: true, max: 100 } }]
										},
										legend: { position: 'bottom' },
										title: {
											display: true,
											text: 'Fair / Writing / Learning % by subject (current selection)'
										}
									}
								});
							}
						}
						refreshHwStripAndChart();
					})();
					</script>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<?php if ($swr_table_ok && (!is_superadmin_loggedin() || !empty($branch_id))): ?>
<script type="text/javascript">
	$(document).ready(function () {
		var $frm = $('#frm_swr_filter');
		if (!$frm.length) {
			return;
		}
		var page = {
			date: String($('#swr_date').val() || ''),
			class_id: String($('#class_id').val() || ''),
			section_id: String($('#section_id').val() || '')
		};
		var submitting = false;
		var readyAt = Date.now() + 600;
		var pendingClassReload = false;

		function swrSubmitIfChanged(override) {
			override = override || {};
			var cur = {
				date: String(override.date != null ? override.date : ($('#swr_date').val() || '')),
				class_id: String(override.class_id != null ? override.class_id : ($('#class_id').val() || '')),
				section_id: String(override.section_id != null ? override.section_id : ($('#section_id').val() || ''))
			};
			if (Date.now() < readyAt || submitting) {
				return;
			}
			if (cur.date === page.date && cur.class_id === page.class_id && cur.section_id === page.section_id) {
				return;
			}
			if (!cur.date || !cur.class_id || !cur.section_id) {
				return;
			}
			submitting = true;
			$('#swr_date').val(cur.date);
			var form = $frm.get(0);
			if (form) {
				form.submit();
			}
		}

		function swrDateValue(el, e) {
			var v = '';
			if (e && typeof e.format === 'function') {
				try { v = e.format('yyyy-mm-dd'); } catch (err) { v = e.format(); }
			}
			if (!v && el) {
				try { v = $(el).datepicker('getFormattedDate'); } catch (err2) {}
			}
			if (!v) {
				v = $(el).val();
			}
			return v;
		}

		function swrReinitSectionSelect() {
			try {
				if ($('#section_id').data('select2')) {
					$('#section_id').select2('destroy');
				}
			} catch (e) {}
			$('#section_id').removeData('__select2');
			if ($.fn.themePluginSelect2) {
				$('#section_id').themePluginSelect2({});
			} else if ($.fn.select2) {
				$('#section_id').select2({ width: '100%' });
			}
		}

		$(document).ajaxComplete(function (event, xhr, settings) {
			if (!settings.url || settings.url.indexOf('ajax/getSectionByClass') === -1) {
				return;
			}
			swrReinitSectionSelect();
			if (!pendingClassReload) {
				return;
			}
			pendingClassReload = false;
			var $sec = $('#section_id');
			var opts = $sec.find('option').filter(function () {
				return String($(this).val() || '') !== '';
			});
			// Auto-pick when only one section, otherwise wait for user selection
			if (opts.length === 1) {
				$sec.val(String(opts.first().val())).trigger('change');
			}
		});

		$('#swr_date').on('changeDate', function (e) {
			var v = swrDateValue(this, e);
			if (v) {
				$(this).val(v);
			}
			swrSubmitIfChanged({ date: v });
		});
		$('#class_id').on('change', function () {
			pendingClassReload = true;
			// sections reload via onchange="getSectionByClass(...)"
		});
		$('#section_id').on('change', function () {
			swrSubmitIfChanged({
				class_id: $('#class_id').val(),
				section_id: $(this).val()
			});
		});
	});
</script>
<?php endif; ?>
