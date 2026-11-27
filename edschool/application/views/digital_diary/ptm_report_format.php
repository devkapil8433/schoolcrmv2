<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$school_name = isset($school_name) && $school_name !== '' ? $school_name : (isset($branch_id) && $branch_id !== '' ? get_type_name_by_id('branch', $branch_id) : '');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$filter_student_id = isset($filter_student_id) ? (int) $filter_student_id : 0;
$class_options = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$student_options = isset($student_options) ? $student_options : array('' => translate('select'));
$student_block = isset($student_block) ? $student_block : null;

$month = isset($month) ? (int) $month : (int) date('n');
$year = isset($year) ? (int) $year : (int) date('Y');
if ($month < 1 || $month > 12) {
	$month = (int) date('n');
}
if ($year < 2000 || $year > 2100) {
	$year = (int) date('Y');
}
$monthLabel = isset($month_label) ? $month_label : date('F Y', strtotime($year . '-' . $month . '-01'));
$table_subjects = isset($table_subjects) && is_array($table_subjects) ? $table_subjects : array(
	array('subject_id' => 0, 'subject_name' => 'Hindi'),
	array('subject_id' => 0, 'subject_name' => 'English'),
);
$summary = isset($summary) && is_array($summary) ? $summary : array(
	'school_open_days' => 0,
	'attend_days' => 0,
	'without_dress' => 0,
	'bad_conduct' => 0,
	'subject_scores' => array(),
	'grade' => '—',
);
$report_rows = isset($report_rows) && is_array($report_rows) ? $report_rows : array();
$student_valid = !empty($student_valid);

if (!function_exists('ptm_table_cell_class')) {
	function ptm_table_cell_class($type, $val, $attendance = '')
	{
		$v = trim((string) $val);
		$att = trim((string) $attendance);
		$present = ($att !== '' && $att !== 'A');
		if ($type === 'attendance') {
			if ($v === 'P') {
				return 'ptm-val-ok';
			}
			if ($v === 'A') {
				return 'ptm-val-bad';
			}
			if ($v === 'L' || $v === 'HD') {
				return 'ptm-val-warn';
			}
			return '';
		}
		if ($type === 'mark') {
			if ($v === 'Y' || $v === '✓') {
				return 'ptm-val-ok';
			}
			if ($present) {
				return 'ptm-val-miss';
			}
		}
		if ($type === 'discipline') {
			if ($v === 'Y' || $v === '✓') {
				return 'ptm-val-ok';
			}
			if ($present) {
				return 'ptm-val-warn';
			}
		}
		return '';
	}
}

// Keep a minimum body height like the sample sheet.
$minRows = 18;
while (count($report_rows) < $minRows) {
	$report_rows[] = array(
		'sr' => '',
		'date_disp' => '',
		't_days' => '',
		'attendance' => '',
		'dress' => '',
		'conduct' => '',
		'subjects' => array(),
	);
}
?>

<div class="row">
	<div class="col-md-12">
		<section class="panel panel-ptm-report-wrap">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-handshake"></i> PTM Report Format (Monthly)</h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/ptm_report_format'), array('method' => 'get', 'class' => 'mb-md no-print')); ?>
				<div class="row mb-sm">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-2">
						<label class="control-label"><?= translate('branch') ?></label>
						<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown(
							'branch_id',
							$arrayBranch,
							$branch_id,
							"class='form-control' id='branch_id' onchange='getClassByBranch(this.value)' data-plugin-selectTwo data-width=\"100%\""
						);
						?>
					</div>
					<?php endif; ?>
					<div class="col-md-1">
						<label class="control-label">Month</label>
						<select class="form-control" name="month">
							<?php for ($m = 1; $m <= 12; $m++): ?>
								<option value="<?= (int) $m ?>" <?= $m === $month ? 'selected' : '' ?>>
									<?= html_escape(date('F', mktime(0, 0, 0, $m, 1, 2000))) ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="col-md-1">
						<label class="control-label">Year</label>
						<input type="number" class="form-control" name="year" value="<?= (int) $year ?>" min="2000" max="2100">
					</div>
					<div class="col-md-2">
						<label class="control-label"><?= translate('class') ?></label>
						<?= form_dropdown('class_id', $class_options, $filter_class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="col-md-2">
						<label class="control-label"><?= translate('section') ?></label>
						<?= form_dropdown('section_id', $section_options, $filter_section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="col-md-2">
						<label class="control-label"><?= translate('student') ?></label>
						<?= form_dropdown('student_id', $student_options, $filter_student_id, "class='form-control' id='student_id' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="col-md-2 text-right" style="padding-top:24px; white-space:nowrap;">
						<button type="submit" class="btn btn-primary" title="<?= translate('search') ?>" aria-label="<?= translate('search') ?>">
							<i class="fas fa-search"></i>
						</button>
						<button type="button" class="btn btn-default" title="Print" aria-label="Print" onclick="window.print();">
							<i class="fas fa-print"></i>
						</button>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div class="ptm-report-sheet">
					<div class="ptm-side-label">Report</div>
					<div class="ptm-inner">
						<div class="ptm-header-row">
							<div class="ptm-header-left">
								<h1 class="ptm-school-name"><?= html_escape($school_name !== '' ? $school_name : 'School Name') ?></h1>
								<div class="ptm-month-label"><?= html_escape($monthLabel) ?></div>
							</div>
							<div class="ptm-header-logo">
								<div class="ptm-logo-placeholder"></div>
							</div>
						</div>

						<div class="ptm-student-grid">
							<div class="ptm-col">
								<div class="ptm-line"><span class="ptm-label">Admission no.</span> <span class="ptm-val"><?= $student_block ? html_escape($student_block['admission_no']) : '__________' ?></span></div>
								<div class="ptm-line"><span class="ptm-label">Class</span> <span class="ptm-val"><?= $student_block ? html_escape($student_block['class_name']) : '__________' ?></span></div>
								<div class="ptm-line"><span class="ptm-label">Roll. No.</span> <span class="ptm-val"><?= $student_block ? html_escape($student_block['roll']) : '__________' ?></span></div>
							</div>
							<div class="ptm-col">
								<div class="ptm-line"><span class="ptm-label">Student&rsquo;s name</span> <span class="ptm-val"><?= $student_block ? html_escape($student_block['student_name']) : '________________________' ?></span></div>
								<div class="ptm-line"><span class="ptm-label">Father&rsquo;s name</span> <span class="ptm-val"><?= $student_block ? html_escape($student_block['father_name']) : '________________________' ?></span></div>
								<div class="ptm-line"><span class="ptm-label">Address</span> <span class="ptm-val"><?= $student_block ? html_escape($student_block['address']) : '________________________' ?></span></div>
							</div>
							<div class="ptm-col">
								<div class="ptm-line"><span class="ptm-label">Mother&rsquo;s name</span> <span class="ptm-val"><?= $student_block ? html_escape($student_block['mother_name']) : '________________________' ?></span></div>
								<div class="ptm-line"><span class="ptm-label">D.O.B.</span> <span class="ptm-val"><?= $student_block ? html_escape($student_block['dob']) : '__________' ?></span></div>
							</div>
						</div>

						<h2 class="ptm-report-title">Student Work Evaluation Report</h2>

						<div class="ptm-color-legend no-print">Green = done / present · Red = absent · Orange = late / discipline issue · Pink = work not done</div>

						<div class="table-responsive ptm-table-wrap">
							<table class="table ptm-table table-bordered">
								<thead>
									<tr>
										<th rowspan="2" class="ptm-th-sr">Sr.</th>
										<th rowspan="2" class="ptm-th-date">Date</th>
								<th colspan="2">Attendance</th>
								<th colspan="2">Discipline</th>
								<?php foreach ($table_subjects as $ts): ?>
								<th colspan="3"><?= html_escape($ts['subject_name']) ?></th>
								<?php endforeach; ?>
							</tr>
							<tr>
								<th class="ptm-th-small">T. Days</th>
								<th class="ptm-th-small">Attend.</th>
								<th class="ptm-th-small ptm-rotate"><div>Dress</div></th>
								<th class="ptm-th-small ptm-rotate"><div>Conduct</div></th>
								<?php foreach ($table_subjects as $ts): ?>
								<th class="ptm-th-small">Fair Copy</th>
								<th class="ptm-th-small">Writing Work</th>
								<th class="ptm-th-small">Learning Work</th>
								<?php endforeach; ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($report_rows as $r): ?>
									<?php
									$subjectsMap = isset($r['subjects']) && is_array($r['subjects']) ? $r['subjects'] : array();
									$attVal = !empty($r['attendance']) ? (string) $r['attendance'] : '';
									$dressVal = !empty($r['dress']) ? (string) $r['dress'] : '';
									$conductVal = !empty($r['conduct']) ? (string) $r['conduct'] : '';
									$hasDate = !empty($r['date_disp']);
									?>
									<tr>
										<td class="ptm-td-sr"><?= $r['sr'] !== '' ? (int) $r['sr'] : '&nbsp;' ?></td>
										<td class="ptm-td-date"><?= $hasDate ? html_escape($r['date_disp']) : '&nbsp;' ?></td>
										<td class="ptm-td-blank"><?= !empty($r['t_days']) ? html_escape($r['t_days']) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('attendance', $attVal)) : '' ?>"><?= $attVal !== '' ? html_escape($attVal) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('discipline', $dressVal, $attVal)) : '' ?>"><?= $dressVal !== '' ? html_escape($dressVal) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('discipline', $conductVal, $attVal)) : '' ?>"><?= $conductVal !== '' ? html_escape($conductVal) : '&nbsp;' ?></td>
										<?php foreach ($table_subjects as $ts):
											$tsid = (int) $ts['subject_id'];
											$sc = isset($subjectsMap[$tsid]) ? $subjectsMap[$tsid] : array('fair' => '', 'writing' => '', 'learn' => '');
											$fairVal = !empty($sc['fair']) ? (string) $sc['fair'] : '';
											$writingVal = !empty($sc['writing']) ? (string) $sc['writing'] : '';
											$learnVal = !empty($sc['learn']) ? (string) $sc['learn'] : '';
										?>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('mark', $fairVal, $attVal)) : '' ?>"><?= $fairVal !== '' ? html_escape($fairVal) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('mark', $writingVal, $attVal)) : '' ?>"><?= $writingVal !== '' ? html_escape($writingVal) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('mark', $learnVal, $attVal)) : '' ?>"><?= $learnVal !== '' ? html_escape($learnVal) : '&nbsp;' ?></td>
										<?php endforeach; ?>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<div class="ptm-bottom-grid">
							<div class="ptm-report-block">
								<div class="ptm-report-heading">Report:-</div>
								<ol class="ptm-report-list">
									<li>Attendance Status</li>
									<li>Discipline Status</li>
									<li>Work Status</li>
								</ol>
							</div>

							<div class="ptm-stats-block">
								<div class="ptm-stat">
									<div class="ptm-stat-label">Number of days school open</div>
									<div class="ptm-stat-box"><?= (int) $summary['school_open_days'] ?></div>
								</div>
								<div class="ptm-stat">
									<div class="ptm-stat-label">Number of days student attend the class</div>
									<div class="ptm-stat-box"><?= (int) $summary['attend_days'] ?></div>
								</div>
								<div class="ptm-stat-row">
									<div class="ptm-mini">
										<div class="ptm-mini-label">Without Dress</div>
										<div class="ptm-mini-box"><?= (int) $summary['without_dress'] ?></div>
									</div>
									<div class="ptm-mini">
										<div class="ptm-mini-label">Bad Conduct Found</div>
										<div class="ptm-mini-box"><?= (int) $summary['bad_conduct'] ?></div>
									</div>
								</div>

								<div class="ptm-work-grade-row">
									<div class="ptm-work-boxes">
										<?php $ss = isset($summary['subject_scores']) && is_array($summary['subject_scores']) ? $summary['subject_scores'] : array(); ?>
										<?php foreach ($ss as $sc): ?>
										<?php $isHindi = !empty($sc['is_hindi']); ?>
										<div class="ptm-wbox<?= $isHindi ? ' ptm-wbox--green' : '' ?>">
											<div class="ptm-wbox-subj"><?= html_escape($sc['label']) ?></div>
											<div class="ptm-wbox-status"><?= html_escape($sc['text']) ?></div>
										</div>
										<?php endforeach; ?>
										<?php if (empty($ss)): ?>
										<div class="ptm-wbox"><div class="ptm-wbox-subj">Subject</div><div class="ptm-wbox-status">__/__</div></div>
										<?php endif; ?>
									</div>
									<div class="ptm-grade-box">
										<div class="ptm-grade-label">Grade</div>
										<?php
										$ptmGradeClass = 'ptm-grade-d';
										$g = strtoupper((string) $summary['grade']);
										if ($g === 'A') {
											$ptmGradeClass = 'ptm-grade-a';
										} elseif ($g === 'B') {
											$ptmGradeClass = 'ptm-grade-b';
										} elseif ($g === 'C') {
											$ptmGradeClass = 'ptm-grade-c';
										}
										?>
										<div class="ptm-grade-cell <?= html_escape($ptmGradeClass) ?>"><?= html_escape($summary['grade']) ?></div>
									</div>
								</div>
							</div>
						</div>

						<div class="ptm-note">
							Dear parents, Your child attendance status, work status, Discipline status is low .<br>
							Be careful &amp; come to school and meet to principal sir
						</div>
					</div>
				</div>
				<?php if ($filter_class_id && $filter_section_id && !$filter_student_id): ?>
				<p class="text-muted no-print mt-md"><?= translate('select') ?> <?= translate('student') ?> <?= translate('to') ?> load monthly report data.</p>
				<?php elseif ($filter_student_id && !$student_valid): ?>
				<p class="text-muted no-print mt-md">Selected student is not in this class/section.</p>
				<?php endif; ?>

			</div>
		</section>
	</div>
</div>

<style type="text/css">
.panel-ptm-report-wrap .ptm-report-sheet {
	position: relative;
	background: #fff;
	border: 1px solid #ddd;
	padding: 12px 12px 14px 28px;
	font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	color: #111;
}
.ptm-side-label {
	position: absolute;
	left: 0;
	top: 50%;
	transform: translateY(-50%) rotate(-90deg);
	transform-origin: center center;
	font-size: 18px;
	font-weight: 700;
	color: #c00;
	letter-spacing: 2px;
	width: 24px;
	text-align: center;
}
.ptm-header-row {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	gap: 16px;
}
.ptm-school-name {
	text-align: center;
	font-size: 1.65rem;
	font-weight: 700;
	color: #c00;
	margin: 8px 0 4px;
}
.ptm-month-label {
	text-align: center;
	font-size: 13px;
	font-weight: 600;
	margin-bottom: 10px;
}
.ptm-header-logo { width: 76px; display: flex; justify-content: flex-end; }
.ptm-logo-placeholder {
	width: 62px;
	height: 62px;
	border: 1px dashed #bbb;
	border-radius: 4px;
}
.ptm-student-grid {
	display: flex;
	flex-wrap: wrap;
	justify-content: space-between;
	gap: 12px 24px;
	margin: 6px 0 10px;
	font-size: 13px;
}
.ptm-col { flex: 1 1 220px; min-width: 180px; }
.ptm-line { margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ptm-label { font-weight: 600; }
.ptm-label:after { content: ' -'; }
.ptm-val { font-weight: 400; }
.ptm-report-title {
	text-align: center;
	font-size: 1.15rem;
	font-weight: 700;
	margin: 8px 0 10px;
}
.ptm-table { margin-bottom: 10px; font-size: 11px; }
.ptm-table thead th {
	background: #fff;
	font-weight: 700;
	text-align: center;
	vertical-align: middle;
	padding: 6px 4px;
}
.ptm-th-sr { width: 46px; }
.ptm-th-date { width: 110px; }
.ptm-th-small { width: 64px; }
.ptm-td-sr { text-align: center; font-weight: 600; }
.ptm-td-date { font-weight: 600; }
.ptm-td-blank { height: 26px; }
.ptm-color-legend { font-size: 11px; color: #444; margin: 0 0 8px; text-align: center; }
.ptm-table td.ptm-val-ok { background: #c8e6c9 !important; color: #1b5e20; font-weight: 700; }
.ptm-table td.ptm-val-bad { background: #ffcdd2 !important; color: #b71c1c; font-weight: 700; }
.ptm-table td.ptm-val-warn { background: #ffe0b2 !important; color: #e65100; font-weight: 700; }
.ptm-table td.ptm-val-miss { background: #fce4ec !important; color: #880e4f; }
.ptm-rotate { width: 38px; padding: 0; }
.ptm-rotate > div {
	transform: rotate(-25deg);
	white-space: nowrap;
	font-weight: 700;
	display: inline-block;
}
.ptm-bottom-grid {
	display: flex;
	flex-wrap: wrap;
	justify-content: space-between;
	gap: 14px;
	align-items: flex-start;
	margin-top: 8px;
}
.ptm-report-block { min-width: 180px; font-size: 13px; }
.ptm-report-heading { font-weight: 700; margin-bottom: 6px; }
.ptm-report-list { padding-left: 18px; margin: 0; }
.ptm-stats-block { flex: 1 1 520px; }
.ptm-stat { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.ptm-stat-label { flex: 1 1 auto; font-size: 12px; text-align: center; font-weight: 600; }
.ptm-stat-box {
	width: 64px;
	height: 34px;
	border: 1px solid #333;
	border-radius: 6px;
	background: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
}
.ptm-stat-row { display: flex; gap: 16px; justify-content: center; margin: 6px 0 10px; }
.ptm-mini { text-align: center; }
.ptm-mini-label { font-size: 11px; margin-bottom: 4px; }
.ptm-mini-box { width: 64px; height: 28px; border: 1px solid #333; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 700; }
.ptm-work-grade-row {
	display: flex;
	flex-wrap: wrap;
	align-items: flex-start;
	gap: 14px;
	justify-content: center;
}
.ptm-work-boxes {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	justify-content: center;
}
.ptm-wbox {
	min-width: 92px;
	min-height: 52px;
	border-radius: 8px;
	padding: 6px 8px;
	text-align: center;
	border: 1px solid #333;
	display: flex;
	flex-direction: column;
	justify-content: center;
	background: #fff;
}
.ptm-wbox--green { background: #2e7d32; border-color: #1b5e20; color: #fff; }
.ptm-wbox-subj { font-weight: 700; font-size: 12px; margin-bottom: 4px; }
.ptm-wbox-status { font-size: 12px; font-weight: 700; }
.ptm-grade-box { text-align: center; min-width: 92px; }
.ptm-grade-label { font-weight: 700; margin-bottom: 6px; }
.ptm-grade-cell {
	min-height: 72px;
	min-width: 88px;
	border: 2px solid #333;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28px;
	font-weight: 700;
}
.ptm-grade-cell.ptm-grade-a { background: #2e7d32; color: #fff; }
.ptm-grade-cell.ptm-grade-b { background: #66bb6a; color: #000; }
.ptm-grade-cell.ptm-grade-c { background: #fdd835; color: #000; }
.ptm-grade-cell.ptm-grade-d { background: #ef5350; color: #fff; }
.ptm-note {
	margin-top: 10px;
	font-size: 12px;
	text-align: center;
}
@media print {
	.no-print { display: none !important; }
	.panel-ptm-report-wrap { border: none; box-shadow: none; }
	.panel-ptm-report-wrap .panel-body { padding: 0; }
	.ptm-report-sheet { border: none; }
	.table-responsive { overflow: visible !important; }
	.ptm-table td, .ptm-table th { border-color: #111 !important; }
	.ptm-table td.ptm-val-ok { background: #c8e6c9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	.ptm-table td.ptm-val-bad { background: #ffcdd2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	.ptm-table td.ptm-val-warn { background: #ffe0b2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	.ptm-table td.ptm-val-miss { background: #fce4ec !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<script type="text/javascript">
function ptmReinitSelect2(sel) {
	try {
		if ($(sel).data('select2')) {
			$(sel).select2('destroy');
		}
	} catch (e) {}
	$(sel).removeData('__select2');
	$(sel).themePluginSelect2({});
}
function ptmStudentPlaceholder(mode) {
	if (mode === 'class') {
		return '<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	if (mode === 'section') {
		return '<option value=""><?= htmlspecialchars(translate('select') . ' ' . translate('section'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	return '<option value=""><?= htmlspecialchars(translate('select'), ENT_QUOTES, 'UTF-8') ?></option>';
}
function ptmLoadStudents(selectedStudentId) {
	var cid = $('#class_id').val();
	var sid = $('#section_id').val();
	if (!cid || !sid) {
		$('#student_id').html(ptmStudentPlaceholder(!cid ? 'class' : 'section'));
		ptmReinitSelect2('#student_id');
		return;
	}
	$.ajax({
		url: base_url + 'student/get_students_by_class_section',
		type: 'POST',
		data: { class_id: cid, section_id: sid },
		success: function (html) {
			$('#student_id').html(html);
			if (selectedStudentId) {
				$('#student_id').val(String(selectedStudentId));
			}
			ptmReinitSelect2('#student_id');
		}
	});
}
$(document).ready(function () {
	var preselectedStudent = <?= (int) $filter_student_id ?>;
	var initialClass = $('#class_id').val();
	var initialSection = $('#section_id').val();
	if (initialClass && initialSection) {
		ptmLoadStudents(preselectedStudent);
	}
	$('#class_id').on('change', function () {
		var v = $(this).val();
		if (!v) {
			$('#section_id').html('<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>');
			ptmReinitSelect2('#section_id');
			$('#student_id').html(ptmStudentPlaceholder('class'));
			ptmReinitSelect2('#student_id');
			return;
		}
		$('#student_id').html(ptmStudentPlaceholder('section'));
		ptmReinitSelect2('#student_id');
	});
	$('#section_id').on('change', function () {
		ptmLoadStudents(0);
	});
});
$(document).ajaxComplete(function (event, xhr, settings) {
	if (!settings.url) {
		return;
	}
	if (settings.url.indexOf('ajax/getSectionByClass') === -1) {
		return;
	}
	ptmReinitSelect2('#section_id');
	$('#student_id').html(ptmStudentPlaceholder('section'));
	ptmReinitSelect2('#student_id');
});
</script>
