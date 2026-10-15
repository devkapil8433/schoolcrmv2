<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$school_name = isset($school_name) && $school_name !== '' ? $school_name : $branch_name;
$search_q = isset($search_q) ? $search_q : '';
$today_date = isset($today_date) && !empty($today_date) ? $today_date : date('Y-m-d');
$subjects_for_grid = isset($subjects_for_grid) ? $subjects_for_grid : array();
$swr_grid_rows = isset($swr_grid_rows) ? $swr_grid_rows : array();
$grid_totals = isset($grid_totals) && is_array($grid_totals) ? $grid_totals : null;
$summary = isset($summary) && is_array($summary) ? $summary : array('school_open_days' => 0);
$swr_table_ok = !empty($swr_table_ok);
$student_valid = !empty($student_valid);
$swr_subject_count = count($subjects_for_grid);
$swr_colspan_total = 6 + ($swr_subject_count * 3);
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$filter_student_id = isset($filter_student_id) ? (int) $filter_student_id : 0;
$class_options = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$student_options = isset($student_options) ? $student_options : array('' => translate('select'));
$report_rows = isset($report_rows) ? $report_rows : array();
$student_block = isset($student_block) ? $student_block : null;
$attendance_summary = isset($attendance_summary) ? $attendance_summary : '—';
$discipline_dress = isset($discipline_dress) ? $discipline_dress : '—';
$discipline_conduct = isset($discipline_conduct) ? $discipline_conduct : '—';
$work_summary_subjects = isset($work_summary_subjects) ? $work_summary_subjects : array();
$grade_display = isset($grade_display) ? $grade_display : '';
$dateDisp = strtotime($today_date) ? date('d M Y', strtotime($today_date)) : $today_date;
$parentSkillSummary = isset($parent_skill_summary) && is_array($parent_skill_summary)
	? $parent_skill_summary
	: array('date_from' => '', 'date_to' => '', 'window_days' => 15, 'skills' => array());
$parentSkillRows = isset($parentSkillSummary['skills']) && is_array($parentSkillSummary['skills']) ? $parentSkillSummary['skills'] : array();
$parentSkillWindow = isset($parentSkillSummary['window_days']) ? (int) $parentSkillSummary['window_days'] : 15;
if ($parentSkillWindow < 1) {
	$parentSkillWindow = 15;
}
$parentSkillFrom = !empty($parentSkillSummary['date_from']) && strtotime($parentSkillSummary['date_from'])
	? date('d M Y', strtotime($parentSkillSummary['date_from']))
	: '';
$parentSkillTo = !empty($parentSkillSummary['date_to']) && strtotime($parentSkillSummary['date_to'])
	? date('d M Y', strtotime($parentSkillSummary['date_to']))
	: $dateDisp;
if ($parentSkillFrom === '' && !empty($today_date) && strtotime($today_date)) {
	$parentSkillFrom = date('d M Y', strtotime($today_date . ' -' . ($parentSkillWindow - 1) . ' days'));
}
$parentSkillTitle = 'Student Skill Summary — last ' . $parentSkillWindow . ' days';
if ($parentSkillFrom !== '') {
	$parentSkillTitle .= ' (' . $parentSkillFrom . ' – ' . $parentSkillTo . ')';
}

$seenSubjects = array();
$dedupedRows = array();
foreach ($report_rows as $ri => $row) {
	$sid = isset($row['subject_id']) ? (int) $row['subject_id'] : 0;
	$sn = isset($row['subject_name']) ? trim((string) $row['subject_name']) : '';
	$dupKey = $sid > 0 ? 'id:' . $sid : 'nm:' . strtolower($sn);
	if ($sn === '' && $sid <= 0) {
		$dupKey = 'row:' . $ri;
	}
	if (isset($seenSubjects[$dupKey])) {
		continue;
	}
	$seenSubjects[$dupKey] = true;
	if (!isset($row['learning_work']) || $row['learning_work'] === '' || $row['learning_work'] === null) {
		$row['learning_work'] = '—';
	}
	$dedupedRows[] = $row;
}
$report_rows = $dedupedRows;
$seenWork = array();
$dedupedWork = array();
foreach ($work_summary_subjects as $ws) {
	$wn = isset($ws['subject_name']) ? strtolower(trim((string) $ws['subject_name'])) : '';
	if ($wn === '' || isset($seenWork[$wn])) {
		continue;
	}
	$seenWork[$wn] = true;
	$dedupedWork[] = $ws;
}
$work_summary_subjects = $dedupedWork;

$gradeLetter = strtoupper(trim((string) $grade_display));
$swr_grade_class = '';
if ($gradeLetter === 'A') {
	$swr_grade_class = 'g-a';
} elseif ($gradeLetter === 'B') {
	$swr_grade_class = 'g-b';
} elseif ($gradeLetter === 'C') {
	$swr_grade_class = 'g-c';
} elseif ($gradeLetter !== '') {
	$swr_grade_class = 'g-d';
}

$shareName = $student_block ? $student_block['student_name'] : '';
$shareText = $shareName !== ''
	? (($school_name !== '' ? $school_name : 'School') . "\nParent Report — " . $shareName . "\n" . $dateDisp . "\nAttendance: " . $attendance_summary . ($grade_display !== '' ? "\nGrade: " . $grade_display : ''))
	: '';
if ($shareText !== '' && $student_block && !empty($parentSkillRows)) {
	$shareText .= "\n" . $parentSkillTitle;
	foreach ($parentSkillRows as $skShare) {
		$sn = isset($skShare['activity_name']) ? $skShare['activity_name'] : 'Skill';
		$sc = (int) (isset($skShare['score']) ? $skShare['score'] : 0);
		$pendingShare = !empty($skShare['pending']) || $sc < 1;
		if ($pendingShare) {
			$shareText .= "\n" . $sn . ': Not reviewed';
			continue;
		}
		$daysShare = isset($skShare['days_reviewed']) ? (int) $skShare['days_reviewed'] : 0;
		$srcShare = isset($skShare['source']) ? (string) $skShare['source'] : '';
		$shareLine = $sn . ' ' . $sc . '/10';
		if ($srcShare === 'attendance' && $daysShare > 0) {
			$shareLine .= ' (' . $daysShare . ' present days)';
		} elseif ($daysShare > 0) {
			$shareLine .= ' (' . $daysShare . '/' . $parentSkillWindow . ' days)';
		}
		$shareText .= "\n" . $shareLine;
	}
}
$waHref = '';
$mailHref = '';
if ($student_block) {
	$rawMobile = isset($student_block['mobile']) ? preg_replace('/\D+/', '', (string) $student_block['mobile']) : '';
	if ($rawMobile !== '') {
		if (strlen($rawMobile) === 10) {
			$rawMobile = '91' . $rawMobile;
		}
		$waHref = 'https://wa.me/' . $rawMobile . '?text=' . rawurlencode($shareText);
	}
	$rawEmail = isset($student_block['email']) ? trim((string) $student_block['email']) : '';
	if ($rawEmail !== '' && filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
		$mailHref = 'mailto:' . rawurlencode($rawEmail) . '?subject=' . rawurlencode('Parent Report — ' . $shareName) . '&body=' . rawurlencode($shareText);
	}
}
$boxClass = function ($tier) {
	if ($tier === 'green') {
		return 'bx hindi-green';
	}
	if ($tier === 'yellow') {
		return 'bx prf-bx-yellow';
	}
	if ($tier === 'orange') {
		return 'bx prf-bx-orange';
	}
	if ($tier === 'red') {
		return 'bx prf-bx-red';
	}
	return 'bx';
};
$attNorm = strtolower(trim((string) $attendance_summary));
$attBoxClass = 'swr-field-box prf-att-box';
if ($attNorm === 'present today' || $attNorm === 'p' || strpos($attNorm, 'present') !== false) {
	$attBoxClass .= ' prf-field-green';
} elseif ($attNorm === 'absent' || $attNorm === 'a') {
	$attBoxClass .= ' prf-field-red';
} elseif ($attNorm !== '' && $attNorm !== '—') {
	$attBoxClass .= ' prf-field-yellow';
}
$dressNorm = strtolower(trim((string) $discipline_dress));
$dressBoxClass = 'swr-field-box';
if ($dressNorm === 'completed') {
	$dressBoxClass .= ' prf-field-green';
} elseif ($dressNorm === 'not completed') {
	$dressBoxClass .= ' prf-field-red';
}
$conductNorm = strtolower(trim((string) $discipline_conduct));
$conductBoxClass = 'swr-field-box';
if ($conductNorm === 'good') {
	$conductBoxClass .= ' prf-field-green';
} elseif ($conductNorm === 'found guilty') {
	$conductBoxClass .= ' prf-field-red';
}
?>
<style id="prf-report-css">
.swr-eval-wrap { font-family: Arial, Helvetica, sans-serif; color: #000; background: #fff; }
.swr-eval-wrap .swr-search-row { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
.swr-eval-wrap .swr-search-row label.swr-red { color: #c00; font-weight: 600; margin: 0; }
.swr-eval-wrap .swr-search-row input[type="text"].swr-search-input { flex: 1; min-width: 200px; border: 1px solid #000; padding: 4px 8px; height: 28px; }
.swr-eval-wrap .swr-filters { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; margin-bottom: 8px; }
.swr-eval-wrap .swr-filters .form-group { margin-bottom: 0; min-width: 140px; }
.swr-eval-wrap .swr-filters label { display: block; font-weight: 600; margin-bottom: 2px; }
.swr-eval-wrap .swr-filters select,
.swr-eval-wrap .swr-filters input.form-control { border: 1px solid #aaa; border-radius: 0; height: 30px; padding: 2px 6px; }
.swr-eval-wrap table.swr-grid { width: 100%; border-collapse: collapse; font-size: 12px; }
.swr-eval-wrap table.swr-grid th,
.swr-eval-wrap table.swr-grid td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: middle; }
.swr-eval-wrap table.swr-grid th { background: #fff; font-weight: 700; text-align: center; }
.swr-eval-wrap table.swr-grid td.ctr { text-align: center; }
.swr-eval-wrap .prf-section-title { font-size: 15px; font-weight: 700; margin: 16px 0 8px; padding: 0; border: 0; }
.swr-eval-wrap table.swr-grid td.prf-learning-cell { text-align: left; font-size: 12px; }
.swr-eval-wrap .swr-report-block { margin-top: 14px; border: 1px solid #000; padding: 12px 14px; }
.swr-eval-wrap .swr-report-block h3 { font-size: 15px; font-weight: 700; margin: 0 0 12px; }
.swr-eval-wrap .prf-report-body {
	display: grid;
	grid-template-columns: minmax(280px, 1.55fr) minmax(180px, 0.85fr) 90px;
	gap: 12px 14px;
	align-items: stretch;
}
.swr-eval-wrap .prf-report-left {
	display: flex;
	flex-direction: column;
	justify-content: flex-start;
	gap: 12px;
	min-width: 0;
}
.swr-eval-wrap .prf-report-mid { min-width: 0; }
.swr-eval-wrap .prf-sum-row {
	display: grid;
	grid-template-columns: minmax(120px, 140px) minmax(0, 1fr);
	gap: 8px 10px;
	align-items: start;
}
.swr-eval-wrap .prf-rlbl { font-weight: 700; font-size: 13px; line-height: 1.3; }
.swr-eval-wrap .prf-rval { min-width: 0; }
.swr-eval-wrap .swr-field-row { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; align-items: center; }
.swr-eval-wrap .swr-field-row label { display: inline-block; font-size: 12px; margin: 0; font-weight: normal; white-space: nowrap; }
.swr-eval-wrap .swr-field-box { border: 1px solid #000; min-width: 36px; flex: 0 0 auto; height: 28px; display: inline-flex; align-items: center; justify-content: center; padding: 0 8px; font-weight: 700; background: #fff; }
.swr-eval-wrap .swr-field-box.prf-att-box { flex: 0 0 auto; min-width: 0; width: auto; max-width: 140px; height: 24px; padding: 0 10px; font-size: 12px; }
.swr-eval-wrap .swr-field-box.prf-field-green { background: #2e7d32; color: #fff; border-color: #1b5e20; }
.swr-eval-wrap .swr-field-box.prf-field-yellow { background: #ffeb3b; color: #000; border-color: #f9a825; }
.swr-eval-wrap .swr-field-box.prf-field-orange { background: #ff9800; color: #fff; border-color: #e65100; }
.swr-eval-wrap .swr-field-box.prf-field-red { background: #e53935; color: #fff; border-color: #c62828; }
.swr-eval-wrap .swr-subj-grid { display: flex; flex-wrap: wrap; gap: 8px; align-items: stretch; margin: 0; }
.swr-eval-wrap .swr-subj-item { text-align: center; flex: 0 0 92px; width: 92px; max-width: 100%; }
.swr-eval-wrap .swr-subj-item .nm {
	font-size: 11px;
	margin-bottom: 2px;
	min-height: 28px;
	line-height: 1.2;
	font-weight: 600;
	display: flex;
	align-items: flex-end;
	justify-content: center;
}
.swr-eval-wrap .swr-subj-item .bx {
	border: 1px solid #000;
	width: 100%;
	min-height: 40px;
	padding: 6px 4px;
	font-size: 10px;
	font-weight: 700;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	box-sizing: border-box;
	background: #fff;
}
.swr-eval-wrap .swr-subj-item .bx.hindi-green { background: #2e7d32; color: #fff; border-color: #1b5e20; }
.swr-eval-wrap .swr-subj-item .bx.prf-bx-yellow { background: #ffeb3b; color: #000; border-color: #f9a825; }
.swr-eval-wrap .swr-subj-item .bx.prf-bx-orange { background: #ff9800; color: #fff; border-color: #e65100; }
.swr-eval-wrap .swr-subj-item .bx.prf-bx-red { background: #e53935; color: #fff; border-color: #c62828; }
.swr-eval-wrap .prf-report-right { display: flex; align-items: center; justify-content: center; min-width: 0; }
.swr-eval-wrap .swr-grade-box { flex: 0 0 88px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; margin: 0; }
.swr-eval-wrap .swr-grade-box .g-label { font-size: 14px; font-weight: 700; margin-bottom: 6px; }
.swr-eval-wrap .swr-grade-box .g-val { width: 78px; height: 78px; border: 1px solid #000; font-size: 40px; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto; background: #fff; }
.swr-eval-wrap .swr-grade-box .g-val.g-a { background: #2e7d32; color: #fff; }
.swr-eval-wrap .swr-grade-box .g-val.g-b { background: #66bb6a; color: #000; }
.swr-eval-wrap .swr-grade-box .g-val.g-c { background: #fdd835; color: #000; }
.swr-eval-wrap .swr-grade-box .g-val.g-d { background: #e53935; color: #fff; }
.swr-eval-wrap .swr-student-card { border: 1px solid #000; padding: 12px 14px; margin: 0 0 14px; background: #f3f3f3; }
.swr-eval-wrap .swr-student-card .swr-student-name { font-size: 22px; font-weight: 700; margin: 0 0 8px; }
.swr-eval-wrap .swr-student-meta { display: flex; flex-wrap: wrap; gap: 10px 22px; font-size: 13px; }
.swr-eval-wrap .swr-student-meta span strong { font-weight: 700; }
.swr-eval-wrap .swr-legend { font-size: 11px; color: #555; margin: 8px 0 10px; padding: 0; background: transparent; border: 0; }
.swr-eval-wrap .swr-legend span { margin-right: 18px; }
.swr-eval-wrap .swr-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 18px; padding-top: 12px; border-top: 0; }
.swr-eval-wrap .swr-actions .btn-swr { background: #00e5ff; border: 1px solid #000; color: #000; font-weight: 700; padding: 8px 22px; border-radius: 2px; cursor: pointer; font-size: 13px; text-decoration: none; display: inline-block; }
.swr-eval-wrap .prf-skill-box { border: 1px solid #000; padding: 8px 10px; background: #fff; height: 100%; box-sizing: border-box; }
.swr-eval-wrap .prf-skill-box .prf-skill-title { font-size: 12px; font-weight: 700; margin: 0 0 8px; line-height: 1.3; }
.swr-eval-wrap .prf-skill-row {
	display: grid;
	grid-template-columns: 58px minmax(36px, 1fr) 40px minmax(48px, 0.9fr);
	gap: 4px 6px;
	align-items: center;
	margin-bottom: 5px;
}
.swr-eval-wrap .prf-skill-name { font-weight: 700; font-size: 11px; }
.swr-eval-wrap .prf-skill-track {
	width: 100%;
	max-width: 64px;
	height: 7px;
	background: #e8e8e8;
	border-radius: 6px;
	overflow: hidden;
	border: 1px solid #ccc;
}
.swr-eval-wrap .prf-skill-fill { height: 100%; background: #2e7d32; }
.swr-eval-wrap .prf-skill-fill.low { background: #c0392b; }
.swr-eval-wrap .prf-skill-fill.mid { background: #e67e22; }
.swr-eval-wrap .prf-skill-fill.good { background: #27ae60; }
.swr-eval-wrap .prf-skill-fill.ex { background: #1e8449; }
.swr-eval-wrap .prf-skill-fill.pending { background: #cbd5e1; width: 0 !important; }
.swr-eval-wrap .prf-skill-score { font-size: 10px; font-weight: 700; white-space: nowrap; color: #333; }
.swr-eval-wrap .prf-skill-comment { font-size: 11px; font-weight: 700; text-align: right; color: #111; line-height: 1.25; }
@media print {
	.no-print, .swr-eval-wrap .panel-btn, .swr-eval-wrap .swr-actions,
	.sidebar-left, .page-header, .inner-menu, header.page-header, .header { display: none !important; }
	html, body, .inner-wrapper, .content-body, .panel, .panel-body, .row, .col-md-12 {
		width: 100% !important;
		margin: 0 !important;
		padding: 0 !important;
		float: none !important;
		background: #fff !important;
		border: none !important;
		box-shadow: none !important;
	}
	.table-responsive { overflow: visible !important; }
	.swr-eval-wrap .prf-report-body {
		grid-template-columns: minmax(280px, 1.55fr) minmax(180px, 0.85fr) 88px !important;
	}
	.swr-eval-wrap .swr-subj-item .bx.hindi-green,
	.swr-eval-wrap .swr-subj-item .bx.prf-bx-yellow,
	.swr-eval-wrap .swr-subj-item .bx.prf-bx-orange,
	.swr-eval-wrap .swr-subj-item .bx.prf-bx-red,
	.swr-eval-wrap .swr-field-box.prf-field-green,
	.swr-eval-wrap .swr-field-box.prf-field-yellow,
	.swr-eval-wrap .swr-field-box.prf-field-orange,
	.swr-eval-wrap .swr-field-box.prf-field-red,
	.swr-eval-wrap .swr-grade-box .g-val,
	.swr-eval-wrap .prf-skill-fill {
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
}
@media (max-width: 1200px) {
	.swr-eval-wrap .prf-report-body {
		grid-template-columns: minmax(0, 1fr) 90px;
		grid-template-areas:
			"left left"
			"mid grade";
	}
	.swr-eval-wrap .prf-report-left { grid-area: left; }
	.swr-eval-wrap .prf-report-mid { grid-area: mid; }
	.swr-eval-wrap .prf-report-right { grid-area: grade; align-items: flex-start; padding-top: 2px; }
}
@media (max-width: 520px) {
	.swr-eval-wrap .prf-report-body {
		grid-template-columns: 1fr;
		grid-template-areas:
			"left"
			"mid"
			"grade";
	}
	.swr-eval-wrap .prf-sum-row { grid-template-columns: 1fr; }
	.swr-eval-wrap .prf-report-right { justify-content: flex-start; }
}
</style>

<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-file-alt"></i> Parent Report Format</h4>
				<div class="panel-btn">
					<button type="button" class="btn btn-default btn-circle" onclick="prfOpenPdf();"><i class="fas fa-print"></i> Print</button>
				</div>
			</header>
			<div class="panel-body swr-eval-wrap">
				<?php if (is_superadmin_loggedin() && empty($branch_id)): ?>
				<section class="panel mb-md no-print">
					<header class="panel-heading"><h4 class="panel-title"><?= translate('branch') ?></h4></header>
					<div class="panel-body">
						<?php echo form_open(base_url('digital_diary/parent_report_format'), array('method' => 'get', 'class' => 'form-inline')); ?>
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
				<div class="mb-md no-print">
					<strong><?= translate('branch') ?>:</strong> <?= html_escape($branch_name ?: $school_name) ?>
					<a href="<?= base_url('digital_diary/parent_report_format') ?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> <?= translate('change') ?></a>
				</div>
				<?php endif; ?>

				<?php if (!is_superadmin_loggedin() || !empty($branch_id)): ?>
				<?php echo form_open(base_url('digital_diary/parent_report_format'), array('method' => 'get', 'class' => 'swr-eval-form no-print', 'id' => 'frm_parent_report')); ?>
				<?php if (!empty($branch_id)): ?>
				<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
				<?php endif; ?>
				<div class="swr-search-row">
					<label class="swr-red">Search By</label>
					<input type="text" name="search" class="swr-search-input" value="<?= html_escape($search_q) ?>" placeholder="" autocomplete="off">
				</div>
				<div class="swr-filters">
					<div class="form-group">
						<label><?= translate('class') ?></label>
						<?= form_dropdown('class_id', $class_options, $filter_class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="form-group">
						<label><?= translate('section') ?></label>
						<?= form_dropdown('section_id', $section_options, $filter_section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="form-group">
						<label><?= translate('student') ?></label>
						<?= form_dropdown('student_id', $student_options, $filter_student_id, "class='form-control' id='student_id' data-plugin-selectTwo data-width='220px'") ?>
					</div>
					<div class="form-group">
						<label><?= translate('date') ?></label>
						<input type="text" name="date" id="prf_date" class="form-control" value="<?= html_escape($today_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div id="prf_print_area">
				<?php if ($student_block): ?>
				<div class="swr-student-card">
					<div class="swr-student-name"><?= html_escape($student_block['student_name']) ?></div>
					<div class="swr-student-meta">
						<span><strong>Class:</strong> <?= html_escape($student_block['class_name']) ?></span>
						<span><strong>Roll No:</strong> <?= html_escape(!empty($student_block['roll']) ? $student_block['roll'] : '—') ?></span>
						<span><strong>Admission no:</strong> <?= html_escape(!empty($student_block['admission_no']) ? $student_block['admission_no'] : '—') ?></span>
						<span><strong>Father:</strong> <?= html_escape(!empty($student_block['father_name']) ? $student_block['father_name'] : '—') ?></span>
						<span><strong>Mother:</strong> <?= html_escape(!empty($student_block['mother_name']) ? $student_block['mother_name'] : '—') ?></span>
						<span><strong>D.O.B.:</strong> <?= html_escape(!empty($student_block['dob']) ? $student_block['dob'] : '—') ?></span>
						<span><strong>Address:</strong> <?= html_escape(!empty($student_block['address']) ? $student_block['address'] : '—') ?></span>
					</div>
				</div>
				<?php endif; ?>

				<h2 class="prf-section-title">Class work / Home work — <?= html_escape($dateDisp) ?></h2>
				<div class="table-responsive">
					<table class="swr-grid">
						<thead>
							<tr>
								<th>Subject</th>
								<th>Total Lesson</th>
								<th>Now Going On</th>
								<th>Class Work</th>
								<th>Home Work</th>
								<th>Learning work</th>
								<th>Syllabus Percentage</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($filter_class_id) || empty($filter_section_id)): ?>
							<tr><td colspan="7" class="ctr text-muted"><?= translate('select') ?> <?= translate('class') ?> &amp; <?= translate('section') ?> <?= translate('to') ?> <?= translate('continue') ?>.</td></tr>
							<?php elseif (empty($report_rows)): ?>
							<tr><td colspan="7" class="ctr text-muted">No homework recorded for this date.</td></tr>
							<?php else: ?>
							<?php foreach ($report_rows as $row): ?>
							<tr>
								<td><strong><?= !empty($row['subject_name']) ? html_escape($row['subject_name']) : '—' ?></strong></td>
								<td class="ctr"><?= isset($row['total_lesson']) && $row['total_lesson'] !== '' ? (int) $row['total_lesson'] : '—' ?></td>
								<td><?= isset($row['now_going_on']) && $row['now_going_on'] !== '' ? html_escape($row['now_going_on']) : '—' ?></td>
								<td><?= isset($row['class_work']) && $row['class_work'] !== '' ? nl2br(html_escape($row['class_work'])) : '—' ?></td>
								<td><?= isset($row['home_work']) && $row['home_work'] !== '' ? nl2br(html_escape($row['home_work'])) : '—' ?></td>
								<td class="prf-learning-cell"><?= isset($row['learning_work']) && $row['learning_work'] !== '' ? nl2br(html_escape($row['learning_work'])) : '—' ?></td>
								<td class="ctr"><?= isset($row['syllabus_percentage']) && $row['syllabus_percentage'] !== '' ? html_escape($row['syllabus_percentage']) : '—' ?></td>
							</tr>
							<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<?php if ($student_block): ?>
				<div class="swr-legend">
					<span>Green = good / completed</span>
					<span>Yellow = low</span>
					<span>Orange = needs attention</span>
					<span>Red = meet principal</span>
				</div>
				<div class="swr-report-block">
					<h3>Report:-</h3>
					<div class="prf-report-body">
						<div class="prf-report-left">
							<div class="prf-sum-row">
								<div class="prf-rlbl">1. Attendance Summary</div>
								<div class="prf-rval">
									<div class="swr-field-row">
										<label>Attendance</label>
										<span class="<?= html_escape($attBoxClass) ?>"><?= html_escape($attendance_summary) ?></span>
									</div>
								</div>
							</div>
							<div class="prf-sum-row">
								<div class="prf-rlbl">2. Discipline Summary</div>
								<div class="prf-rval">
									<div class="swr-field-row">
										<label>Dress</label>
										<span class="<?= html_escape($dressBoxClass) ?>"><?= html_escape($discipline_dress) ?></span>
										<label>Conduct</label>
										<span class="<?= html_escape($conductBoxClass) ?>"><?= html_escape($discipline_conduct) ?></span>
									</div>
								</div>
							</div>
							<div class="prf-sum-row">
								<div class="prf-rlbl">3. Work Summary</div>
								<div class="prf-rval">
									<div class="swr-subj-grid">
										<?php foreach ($work_summary_subjects as $ws):
											$tier = isset($ws['tier']) ? $ws['tier'] : 'empty';
											$txt = isset($ws['text']) ? $ws['text'] : '';
											$sn = isset($ws['subject_name']) ? $ws['subject_name'] : '';
										?>
										<div class="swr-subj-item">
											<div class="nm"><?= html_escape($sn) ?></div>
											<div class="<?= $boxClass($tier) ?>">
												<?= html_escape($txt !== '' ? $txt : '—') ?>
											</div>
										</div>
										<?php endforeach; ?>
										<?php if (empty($work_summary_subjects)): ?>
										<div class="swr-subj-item">
											<div class="nm">—</div>
											<div class="bx">—</div>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
						<div class="prf-report-mid">
							<div class="prf-skill-box">
								<div class="prf-skill-title"><?= html_escape($parentSkillTitle) ?></div>
								<?php if (empty($parentSkillRows)): ?>
								<p class="text-muted" style="margin:0;font-size:12px;">No skill reviews recorded for this period.</p>
								<?php else: ?>
								<?php foreach ($parentSkillRows as $sk):
									$pending = !empty($sk['pending']) || (int) (isset($sk['score']) ? $sk['score'] : 0) < 1;
									$score = (int) (isset($sk['score']) ? $sk['score'] : 0);
									$comment = isset($sk['comment']) ? trim((string) $sk['comment']) : '';
									$name = isset($sk['activity_name']) ? $sk['activity_name'] : '';
									$pct = $pending ? 0 : max(0, min(100, $score * 10));
									if ($pending && $comment === '') {
										$comment = 'Not reviewed';
									}
									$fillClass = 'prf-skill-fill pending';
									if (!$pending) {
										if ($score >= 9) {
											$fillClass = 'prf-skill-fill ex';
										} elseif ($score >= 7) {
											$fillClass = 'prf-skill-fill good';
										} elseif ($score >= 5) {
											$fillClass = 'prf-skill-fill mid';
										} else {
											$fillClass = 'prf-skill-fill low';
										}
									}
								?>
								<div class="prf-skill-row">
									<div class="prf-skill-name"><?= html_escape($name) ?></div>
									<div class="prf-skill-track"><div class="<?= $fillClass ?>" style="width:<?= (int) $pct ?>%;"></div></div>
									<div class="prf-skill-score"><?= $pending ? '(—/10)' : '(' . (int) $score . '/10)' ?></div>
									<div class="prf-skill-comment"><?= $comment !== '' ? html_escape($comment) : '' ?></div>
								</div>
								<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</div>
						<div class="prf-report-right">
							<div class="swr-grade-box">
								<div class="g-label">Grade</div>
								<div class="g-val <?= html_escape($swr_grade_class) ?>"><?= $grade_display !== '' ? html_escape($grade_display) : '—' ?></div>
							</div>
						</div>
					</div>
				</div>
				<?php elseif ($filter_class_id && $filter_section_id && !$filter_student_id): ?>
				<p class="text-muted text-center no-print" style="margin-top:10px;"><i class="fas fa-info-circle"></i> <?= translate('select') ?> <?= translate('student') ?> to load attendance, discipline, work summary, and grade.</p>
				<?php endif; ?>
				</div>

				<div class="swr-actions no-print">
					<?php if ($waHref !== ''): ?>
					<a class="btn-swr" href="<?= html_escape($waHref) ?>" target="_blank" rel="noopener">Whatsapp</a>
					<?php else: ?>
					<button type="button" class="btn-swr" onclick="window.open('https://wa.me/','_blank');">Whatsapp</button>
					<?php endif; ?>
					<?php if ($mailHref !== ''): ?>
					<a class="btn-swr" href="<?= html_escape($mailHref) ?>">Email</a>
					<?php else: ?>
					<button type="button" class="btn-swr" onclick="window.location.href='mailto:?subject=Parent%20Report';">Email</button>
					<?php endif; ?>
					<button type="button" class="btn-swr" onclick="prfOpenPdf();">PDF</button>
				</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
function prfOpenPdf() {
	var area = document.getElementById('prf_print_area');
	if (!area) {
		return;
	}
	var clone = area.cloneNode(true);
	var hide = clone.querySelectorAll('.no-print');
	for (var i = 0; i < hide.length; i++) {
		if (hide[i].parentNode) {
			hide[i].parentNode.removeChild(hide[i]);
		}
	}
	var cssEl = document.getElementById('prf-report-css');
	var css = cssEl ? cssEl.innerHTML : '';
	css += 'html,body{background:#fff;margin:16px;} .table-responsive{overflow:visible!important;} *{-webkit-print-color-adjust:exact;print-color-adjust:exact;} @media print{html,body{margin:8px;} .no-print{display:none!important;}}';
	var title = 'Parent Report';
	var nameEl = area.querySelector('.swr-student-name');
	if (nameEl && nameEl.textContent) {
		title = 'Parent Report - ' + nameEl.textContent.replace(/\s+/g, ' ').trim();
	}
	var w = window.open('', '_blank');
	if (!w) {
		window.print();
		return;
	}
	w.document.open();
	w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + title.replace(/</g, '') + '</title><style>' + css + '</style></head><body><div class="swr-eval-wrap">' + clone.innerHTML + '</div></body></html>');
	w.document.close();
	w.focus();
	setTimeout(function () {
		w.print();
	}, 400);
}
function prfReinitSelect2(sel) {
	try {
		if ($(sel).data('select2')) {
			$(sel).select2('destroy');
		}
	} catch (e) {}
	$(sel).removeData('__select2');
	$(sel).themePluginSelect2({});
}
function prfStudentPlaceholder(mode) {
	if (mode === 'class') {
		return '<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	if (mode === 'section') {
		return '<option value=""><?= htmlspecialchars(translate('select') . ' ' . translate('section'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	return '<option value=""><?= htmlspecialchars(translate('select'), ENT_QUOTES, 'UTF-8') ?></option>';
}
function prfLoadStudents(selectedStudentId) {
	var cid = $('#frm_parent_report #class_id').val();
	var sid = $('#frm_parent_report #section_id').val();
	if (!cid || !sid) {
		$('#frm_parent_report #student_id').html(prfStudentPlaceholder(!cid ? 'class' : 'section'));
		prfReinitSelect2('#frm_parent_report #student_id');
		return;
	}
	$.ajax({
		url: base_url + 'student/get_students_by_class_section',
		type: 'POST',
		data: { class_id: cid, section_id: sid },
		success: function (html) {
			$('#frm_parent_report #student_id').html(html);
			if (selectedStudentId) {
				$('#frm_parent_report #student_id').val(String(selectedStudentId));
			}
			prfReinitSelect2('#frm_parent_report #student_id');
		}
	});
}
$(document).ready(function () {
	var $form = $('#frm_parent_report');
	if (!$form.length) {
		return;
	}
	var preselectedStudent = <?= (int) $filter_student_id ?>;
	if ($('#frm_parent_report #class_id').val() && $('#frm_parent_report #section_id').val()) {
		prfLoadStudents(preselectedStudent);
	}
	$form.on('change', '#class_id', function () {
		var v = $(this).val();
		if (!v) {
			$('#frm_parent_report #section_id').html('<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>');
			prfReinitSelect2('#frm_parent_report #section_id');
			$('#frm_parent_report #student_id').html(prfStudentPlaceholder('class'));
			prfReinitSelect2('#frm_parent_report #student_id');
			return;
		}
		$('#frm_parent_report #student_id').html(prfStudentPlaceholder('section'));
		prfReinitSelect2('#frm_parent_report #student_id');
	});
	$form.on('change', '#section_id', function () {
		prfLoadStudents(0);
	});
});
$(document).ajaxComplete(function (event, xhr, settings) {
	if (!settings.url || !$('#frm_parent_report').length) {
		return;
	}
	if (settings.url.indexOf('ajax/getSectionByClass') === -1) {
		return;
	}
	prfReinitSelect2('#frm_parent_report #section_id');
	$('#frm_parent_report #student_id').html(prfStudentPlaceholder('section'));
	prfReinitSelect2('#frm_parent_report #student_id');
});
</script>
