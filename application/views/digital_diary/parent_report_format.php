<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$school_name = isset($school_name) && $school_name !== '' ? $school_name : $branch_name;
$school_print_info = isset($school_print_info) && is_array($school_print_info) ? $school_print_info : array();
$school_print_code = isset($school_print_code) ? trim((string) $school_print_code) : '';
$school_print_code_label = isset($school_print_code_label) ? $school_print_code_label : 'Branch ID';
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
$renderWorkLine = function ($label, $value) {
	$value = trim((string) $value);
	$display = $value === '' || $value === '—'
		? '<span class="text-muted">—</span>'
		: nl2br(html_escape($value));
	return '<div class="prf-work-line"><span class="prf-work-label">' . html_escape($label) . '</span><span class="prf-work-value">' . $display . '</span></div>';
};
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
.swr-eval-wrap table.swr-grid { width: 100%; min-width: 0; table-layout: fixed; border: 1px solid #000; border-collapse: collapse; background: #fff; font-size: 12px; }
.swr-eval-wrap table.swr-grid th,
.swr-eval-wrap table.swr-grid td { padding: 9px 10px; border: 1px solid #000; color: #435265; text-align: left; vertical-align: middle; overflow-wrap: anywhere; }
.swr-eval-wrap table.swr-grid th { padding: 10px 8px; border: 1px solid #000; background: #f7f9fc; color: #617185; font-weight: 700; text-align: center; white-space: normal; }
.swr-eval-wrap table.swr-grid th:nth-child(1),
.swr-eval-wrap table.swr-grid td:nth-child(1) { width: 12%; }
.swr-eval-wrap table.swr-grid th:nth-child(2),
.swr-eval-wrap table.swr-grid td:nth-child(2) { width: 7%; }
.swr-eval-wrap table.swr-grid th:nth-child(3),
.swr-eval-wrap table.swr-grid td:nth-child(3) { width: 13%; }
.swr-eval-wrap table.swr-grid th:nth-child(4),
.swr-eval-wrap table.swr-grid td:nth-child(4),
.swr-eval-wrap table.swr-grid th:nth-child(5),
.swr-eval-wrap table.swr-grid td:nth-child(5) { width: 23%; }
.swr-eval-wrap table.swr-grid th:nth-child(6),
.swr-eval-wrap table.swr-grid td:nth-child(6) { width: 13%; }
.swr-eval-wrap table.swr-grid th:nth-child(7),
.swr-eval-wrap table.swr-grid td:nth-child(7) { width: 9%; }
.swr-eval-wrap table.swr-grid tbody tr:nth-child(even) { background: #fcfdff; }
.swr-eval-wrap table.swr-grid tbody tr:hover { background: #f7faff; }
.swr-eval-wrap table.swr-grid tbody tr:last-child td { border-bottom: 1px solid #000; }
.swr-eval-wrap table.swr-grid td:first-child { color: #26384a; }
.swr-eval-wrap table.swr-grid td.ctr { text-align: center; }
.swr-eval-wrap .prf-section-title { color: #293d51; font-size: 18px; font-weight: 700; margin: 22px 0 12px; padding: 0; border: 0; }
.swr-eval-wrap table.swr-grid td.prf-learning-cell { text-align: left; font-size: 12px; }
.swr-eval-wrap table.swr-grid td.prf-work-cell { vertical-align: top; white-space: normal; }
.swr-eval-wrap .prf-work-line { display: grid; grid-template-columns: 65px minmax(0, 1fr); gap: 6px; padding: 4px 0; line-height: 1.4; }
.swr-eval-wrap .prf-work-line + .prf-work-line { border-top: 1px solid #f0f3f6; }
.swr-eval-wrap .prf-work-label { color: #8a97a6; font-size: 9px; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; }
.swr-eval-wrap .prf-work-value { min-width: 0; color: #435265; overflow-wrap: anywhere; }
.swr-eval-wrap .prf-work-line:first-child .prf-work-value { color: #34495e; font-weight: 600; }
.swr-eval-wrap .swr-report-block { margin-top: 16px; padding: 16px; border: 1px solid #e6ecf2; border-radius: 12px; background: #fff; box-shadow: 0 6px 20px rgba(37, 55, 75, .04); }
.swr-eval-wrap .swr-report-block h3 { display: flex; align-items: center; gap: 8px; margin: 0 0 14px; color: #2f4358; font-size: 16px; font-weight: 700; }
.swr-eval-wrap .swr-report-block h3 i { color: #6386ad; }
.swr-eval-wrap .prf-report-body {
	display: grid;
	grid-template-columns: minmax(0, 1.5fr) minmax(220px, .9fr) 88px;
	gap: 10px;
	align-items: start;
}
.swr-eval-wrap .prf-report-left {
	display: flex;
	flex-direction: column;
	justify-content: flex-start;
	gap: 8px;
	min-width: 0;
}
.swr-eval-wrap .prf-report-mid { min-width: 0; }
.swr-eval-wrap .prf-sum-row {
	display: grid;
	grid-template-columns: minmax(125px, 145px) minmax(0, 1fr);
	gap: 8px;
	align-items: center;
	padding: 10px 12px;
	border: 1px solid #eaf0f5;
	border-radius: 9px;
	background: #fbfcfe;
}
.swr-eval-wrap .prf-rlbl { color: #526579; font-weight: 700; font-size: 12px; line-height: 1.35; }
.swr-eval-wrap .prf-rval { min-width: 0; }
.swr-eval-wrap .swr-field-row { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; align-items: center; }
.swr-eval-wrap .swr-field-row label { display: inline-block; margin: 0; color: #7d8b99; font-size: 11px; font-weight: 600; white-space: nowrap; }
.swr-eval-wrap .swr-field-box { min-width: 36px; height: 28px; padding: 0 10px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e5ebf1; border-radius: 7px; background: #fff; color: #435265; font-weight: 700; }
.swr-eval-wrap .swr-field-box.prf-att-box { flex: 0 0 auto; min-width: 0; width: auto; max-width: 160px; height: 28px; padding: 0 10px; font-size: 12px; }
.swr-eval-wrap .swr-field-box.prf-field-green { background: #dcefe1; color: #356b48; border-color: #b6d6bf; }
.swr-eval-wrap .swr-field-box.prf-field-yellow { background: #f7ebbd; color: #725b1e; border-color: #dfcf86; }
.swr-eval-wrap .swr-field-box.prf-field-orange { background: #f8dfc9; color: #8b5125; border-color: #e2bd9c; }
.swr-eval-wrap .swr-field-box.prf-field-red { background: #f4d8d5; color: #8b433c; border-color: #dfb0aa; }
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
	border: 1px solid #e5ebf1;
	border-radius: 7px;
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
.swr-eval-wrap .swr-subj-item .bx.hindi-green { background: #dcefe1; color: #356b48; border-color: #b6d6bf; }
.swr-eval-wrap .swr-subj-item .bx.prf-bx-yellow { background: #f7ebbd; color: #725b1e; border-color: #dfcf86; }
.swr-eval-wrap .swr-subj-item .bx.prf-bx-orange { background: #f8dfc9; color: #8b5125; border-color: #e2bd9c; }
.swr-eval-wrap .swr-subj-item .bx.prf-bx-red { background: #f4d8d5; color: #8b433c; border-color: #dfb0aa; }
.swr-eval-wrap .prf-report-right { display: flex; align-items: center; justify-content: center; min-width: 0; }
.swr-eval-wrap .swr-grade-box { flex: 0 0 88px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; margin: 0; }
.swr-eval-wrap .swr-grade-box .g-label { color: #647589; font-size: 12px; font-weight: 700; margin-bottom: 6px; }
.swr-eval-wrap .swr-grade-box .g-val { width: 72px; height: 72px; border: 1px solid #e5ebf1; border-radius: 12px; font-size: 36px; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto; background: #fff; }
.swr-eval-wrap .swr-grade-box .g-val.g-a { background: #dcefe1; color: #356b48; }
.swr-eval-wrap .swr-grade-box .g-val.g-b { background: #e1efdc; color: #47734c; }
.swr-eval-wrap .swr-grade-box .g-val.g-c { background: #f7ebbd; color: #725b1e; }
.swr-eval-wrap .swr-grade-box .g-val.g-d { background: #f4d8d5; color: #8b433c; }
.swr-eval-wrap .swr-student-card { border: 1px solid #e6ecf2; border-radius: 10px; padding: 12px 14px; margin: 0 0 12px; background: #fbfcfe; }
.swr-eval-wrap .swr-student-card .swr-student-name { color: #26384a; font-size: 20px; font-weight: 700; margin: 0 0 7px; }
.swr-eval-wrap .swr-student-meta { display: flex; flex-wrap: wrap; gap: 8px 18px; color: #657587; font-size: 12px; }
.swr-eval-wrap .swr-student-meta span strong { font-weight: 700; }
.swr-eval-wrap .swr-legend { display: flex; flex-wrap: wrap; gap: 6px; margin: 8px 0 10px; padding: 0; color: #657587; font-size: 10px; }
.swr-eval-wrap .swr-legend span { margin: 0; padding: 4px 8px; border: 1px solid #e9eef4; border-radius: 12px; background: #fbfcfe; }
.swr-eval-wrap .swr-actions { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; width: fit-content; max-width: 100%; margin: 16px auto 0; padding: 10px 12px; border: 1px solid #e9eef4; border-radius: 10px; background: #fbfcfe; }
.swr-eval-wrap .swr-actions .btn-swr { display: inline-flex; align-items: center; justify-content: center; min-height: 36px; padding: 7px 14px; border: 1px solid #dfe6ee; border-radius: 7px; background: #fff; color: #526579; font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .15s ease, border-color .15s ease, color .15s ease; }
.swr-eval-wrap .swr-actions .btn-swr i { margin-right: 7px; }
.swr-eval-wrap .swr-actions .btn-swr:hover,
.swr-eval-wrap .swr-actions .btn-swr:focus { background: #f5f8fb; border-color: #cbd7e3; color: #34495e; text-decoration: none; }
.swr-eval-wrap .swr-actions .btn-swr-whatsapp { background: #dcefe1; border-color: #b6d6bf; color: #356b48; }
.swr-eval-wrap .swr-actions .btn-swr-whatsapp:hover,
.swr-eval-wrap .swr-actions .btn-swr-whatsapp:focus { background: #cfe6d5; border-color: #a4c9ae; color: #2e603f; }
.swr-eval-wrap .swr-actions .btn-swr-pdf { background: #fbf0ef; border-color: #f0d8d5; color: #a85b54; }
.swr-eval-wrap .swr-actions .btn-swr-pdf:hover,
.swr-eval-wrap .swr-actions .btn-swr-pdf:focus { background: #f8e6e3; border-color: #e8c5c1; color: #914941; }
.panel-btn .prf-print-button { min-height: 36px; padding: 7px 14px; border: 1px solid #dfe6ee; border-radius: 18px; background: #fff; color: #526579; box-shadow: none; font-size: 12px; font-weight: 600; }
.panel-btn .prf-print-button:hover,
.panel-btn .prf-print-button:focus { border-color: #cbd7e3; background: #f5f8fb; color: #34495e; }
.swr-eval-wrap .prf-skill-box { border: 1px solid #eaf0f5; border-radius: 9px; padding: 12px; background: #fbfcfe; height: 100%; box-sizing: border-box; }
.swr-eval-wrap .prf-skill-box .prf-skill-title { color: #526579; font-size: 12px; font-weight: 700; margin: 0 0 10px; line-height: 1.3; }
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
.swr-eval-wrap .prf-print-school-header { display: none; }
@page { size: A4 portrait; margin: 5mm; }
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
	body { font-size: 9px !important; }
	.swr-eval-wrap { width: 100% !important; max-width: 100% !important; padding: 0 !important; zoom: 1; box-sizing: border-box; }
	#prf_print_area { width: 100%; max-width: 100%; overflow: visible; }
	.swr-eval-wrap .prf-print-school-header { display: flex !important; align-items: center; gap: 12px; margin: 0 0 7px; padding: 0 0 7px; border-bottom: 1px solid #8794a2; }
	.swr-eval-wrap .prf-print-school-logo { display: flex; flex: 0 0 76px; width: 76px; height: 58px; align-items: center; justify-content: center; }
	.swr-eval-wrap .prf-print-school-logo img { display: block; max-width: 76px; max-height: 58px; object-fit: contain; }
	.swr-eval-wrap .prf-print-school-details { min-width: 0; flex: 1; text-align: center; }
	.swr-eval-wrap .prf-print-school-details h1 { margin: 0 0 2px; color: #1f3448; font-size: 18px; font-weight: 700; line-height: 1.2; }
	.swr-eval-wrap .prf-print-school-address { margin: 0 0 3px; color: #526579; font-size: 9px; line-height: 1.25; }
	.swr-eval-wrap .prf-print-school-meta { display: flex; flex-wrap: wrap; justify-content: center; gap: 3px 12px; color: #435265; font-size: 8px; }
	.table-responsive { overflow: visible !important; }
	.swr-eval-wrap .swr-student-card { margin: 0 0 6px; padding: 7px 9px; border-radius: 5px; }
	.swr-eval-wrap .swr-student-card .swr-student-name { margin: 0 0 4px; font-size: 15px; }
	.swr-eval-wrap .swr-student-meta { gap: 4px 10px; font-size: 9px; }
	.swr-eval-wrap .prf-section-title { margin: 8px 0 5px; font-size: 14px; }
	.swr-eval-wrap table.swr-grid { width: 100% !important; min-width: 0 !important; font-size: 9px; }
	.swr-eval-wrap table.swr-grid th,
	.swr-eval-wrap table.swr-grid td { padding: 4px 5px; line-height: 1.2; }
	.swr-eval-wrap .prf-work-line { grid-template-columns: 48px minmax(0, 1fr); gap: 3px; padding: 2px 0; line-height: 1.2; font-size: 9px; }
	.swr-eval-wrap .prf-work-label { font-size: 7px; }
	.swr-eval-wrap .swr-legend { gap: 4px; margin: 5px 0; font-size: 8px; }
	.swr-eval-wrap .swr-legend span { padding: 3px 6px; }
	.swr-eval-wrap .swr-report-block { margin-top: 7px; padding: 9px; border-radius: 6px; box-shadow: none; }
	.swr-eval-wrap .swr-report-block h3 { margin: 0 0 6px; font-size: 12px; }
	.swr-eval-wrap .prf-report-body {
		grid-template-columns: minmax(0, 1.4fr) minmax(0, .85fr) 58px !important;
		gap: 5px;
	}
	.swr-eval-wrap .prf-report-left { gap: 4px; }
	.swr-eval-wrap .prf-sum-row { grid-template-columns: minmax(105px, 115px) minmax(0, 1fr); gap: 5px; padding: 5px 7px; border-radius: 5px; }
	.swr-eval-wrap .prf-rlbl { font-size: 9px; }
	.swr-eval-wrap .swr-field-row { gap: 5px; }
	.swr-eval-wrap .swr-field-row label,
	.swr-eval-wrap .swr-field-box.prf-att-box { font-size: 8px; }
	.swr-eval-wrap .swr-field-box.prf-att-box { height: 20px; padding: 0 6px; }
	.swr-eval-wrap .swr-subj-grid { gap: 3px; }
	.swr-eval-wrap .swr-subj-item { flex-basis: 66px; width: 66px; }
	.swr-eval-wrap .swr-subj-item .nm { min-height: 16px; margin: 0; font-size: 8px; }
	.swr-eval-wrap .swr-subj-item .bx { min-height: 24px; padding: 2px; font-size: 8px; }
	.swr-eval-wrap .prf-skill-box { padding: 6px; border-radius: 5px; }
	.swr-eval-wrap .prf-skill-box .prf-skill-title { margin: 0 0 5px; font-size: 9px; }
	.swr-eval-wrap .prf-skill-row { grid-template-columns: 46px minmax(20px, 1fr) 30px minmax(30px, .9fr); gap: 3px; margin-bottom: 3px; }
	.swr-eval-wrap .prf-skill-name,
	.swr-eval-wrap .prf-skill-comment { font-size: 8px; }
	.swr-eval-wrap .prf-skill-score { font-size: 8px; }
	.swr-eval-wrap .prf-skill-track { height: 6px; }
	.swr-eval-wrap .swr-grade-box { flex-basis: 54px; }
	.swr-eval-wrap .swr-grade-box .g-label { margin-bottom: 3px; font-size: 9px; }
	.swr-eval-wrap .swr-grade-box .g-val { width: 48px; height: 48px; border-radius: 6px; font-size: 24px; }
	.swr-eval-wrap .swr-grade-box .g-val.g-a,
	.swr-eval-wrap .swr-grade-box .g-val.g-b,
	.swr-eval-wrap .swr-grade-box .g-val.g-c,
	.swr-eval-wrap .swr-grade-box .g-val.g-d { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
@media (max-width: 767px) {
	.swr-eval-wrap table.swr-grid { min-width: 1000px; }
}
@media print {
	.swr-eval-wrap table.swr-grid { width: 100% !important; min-width: 0 !important; }
	.swr-eval-wrap .prf-report-body { grid-template-columns: minmax(0, 1.4fr) minmax(0, .85fr) 58px !important; grid-template-areas: none !important; }
	.swr-eval-wrap .prf-report-left,
	.swr-eval-wrap .prf-report-mid,
	.swr-eval-wrap .prf-report-right { grid-area: auto !important; }
}
</style>

<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-file-alt"></i> Parent Report Format</h4>
				<div class="panel-btn">
					<button type="button" class="btn btn-default prf-print-button" onclick="prfOpenPdf();"><i class="fas fa-print"></i> Print</button>
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
				<?php
				$schoolPrintLogo = !empty($branch_id)
					? $this->application_model->getBranchImage((int) $branch_id, 'printing-logo')
					: '';
				$schoolPrintAddress = isset($school_print_info['address']) ? trim((string) $school_print_info['address']) : '';
				$schoolPrintCity = isset($school_print_info['city']) ? trim((string) $school_print_info['city']) : '';
				$schoolPrintState = isset($school_print_info['state']) ? trim((string) $school_print_info['state']) : '';
				$schoolPrintLocation = implode(', ', array_filter(array($schoolPrintCity, $schoolPrintState), 'strlen'));
				?>
				<div class="prf-print-school-header">
					<?php if ($schoolPrintLogo !== ''): ?>
					<div class="prf-print-school-logo">
						<img src="<?= html_escape($schoolPrintLogo) ?>" alt="School logo">
					</div>
					<?php endif; ?>
					<div class="prf-print-school-details">
						<h1><?= html_escape($school_name !== '' ? $school_name : 'School') ?></h1>
						<?php if ($schoolPrintAddress !== '' || $schoolPrintLocation !== ''): ?>
						<p class="prf-print-school-address">
							<?= html_escape(implode(', ', array_filter(array($schoolPrintAddress, $schoolPrintLocation), 'strlen'))) ?>
						</p>
						<?php endif; ?>
						<div class="prf-print-school-meta">
							<?php if (!empty($school_print_info['mobileno'])): ?>
							<span><strong>Mobile:</strong> <?= html_escape($school_print_info['mobileno']) ?></span>
							<?php endif; ?>
							<?php if (!empty($school_print_info['email'])): ?>
							<span><strong>Email:</strong> <?= html_escape($school_print_info['email']) ?></span>
							<?php endif; ?>
							<?php if ($school_print_code !== ''): ?>
							<span><strong><?= html_escape($school_print_code_label) ?>:</strong> <?= html_escape($school_print_code) ?></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
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
								<td class="prf-work-cell">
									<?= $renderWorkLine('Work / activity', isset($row['class_work_activity']) && trim((string) $row['class_work_activity']) !== '' ? $row['class_work_activity'] : (isset($row['class_work']) ? $row['class_work'] : '—')) ?>
									<?= $renderWorkLine('Notebook', isset($row['class_work_notebook']) ? $row['class_work_notebook'] : '—') ?>
								</td>
								<td class="prf-work-cell">
									<?= $renderWorkLine('Work / activity', isset($row['home_work_activity']) && trim((string) $row['home_work_activity']) !== '' ? $row['home_work_activity'] : (isset($row['home_work']) ? $row['home_work'] : '—')) ?>
									<?= $renderWorkLine('Notebook', isset($row['home_work_notebook']) ? $row['home_work_notebook'] : '—') ?>
								</td>
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
					<h3><i class="fas fa-chart-line"></i> Report Summary</h3>
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
					<a class="btn-swr btn-swr-whatsapp" href="<?= html_escape($waHref) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>
					<?php else: ?>
					<button type="button" class="btn-swr btn-swr-whatsapp" onclick="window.open('https://wa.me/','_blank');"><i class="fab fa-whatsapp"></i> WhatsApp</button>
					<?php endif; ?>
					<?php if ($mailHref !== ''): ?>
					<a class="btn-swr" href="<?= html_escape($mailHref) ?>"><i class="far fa-envelope"></i> Email</a>
					<?php else: ?>
					<button type="button" class="btn-swr" onclick="window.location.href='mailto:?subject=Parent%20Report';"><i class="far fa-envelope"></i> Email</button>
					<?php endif; ?>
					<button type="button" class="btn-swr btn-swr-pdf" onclick="prfOpenPdf();"><i class="far fa-file-pdf"></i> PDF</button>
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
	var schoolHeader = area.querySelector('.prf-print-school-header');
	var sectionTitle = area.querySelector('.prf-section-title');
	var table = area.querySelector('.table-responsive');
	if (!schoolHeader || !sectionTitle || !table) {
		window.alert('The school details and class work table are not available to print.');
		return;
	}
	var printContent = document.createElement('div');
	printContent.appendChild(schoolHeader.cloneNode(true));
	printContent.appendChild(sectionTitle.cloneNode(true));
	var classSelect = document.getElementById('class_id');
	var sectionSelect = document.getElementById('section_id');
	var className = classSelect && classSelect.selectedIndex >= 0 ? classSelect.options[classSelect.selectedIndex].text : '';
	var sectionName = sectionSelect && sectionSelect.selectedIndex >= 0 ? sectionSelect.options[sectionSelect.selectedIndex].text : '';
	if (className || sectionName) {
		var classMeta = document.createElement('div');
		classMeta.className = 'prf-print-class-meta';
		classMeta.textContent = 'Class: ' + className + (sectionName ? ' - ' + sectionName : '');
		printContent.appendChild(classMeta);
	}
	printContent.appendChild(table.cloneNode(true));
	var cssEl = document.getElementById('prf-report-css');
	var css = cssEl ? cssEl.innerHTML : '';
	css += '@page{size:A4 portrait;margin:5mm;} html,body{background:#fff;margin:0;} .table-responsive{overflow:visible!important;} .prf-print-class-meta{margin:0 0 5px;color:#526579;font-size:9px;} *{-webkit-print-color-adjust:exact;print-color-adjust:exact;} @media print{html,body{margin:0!important;} .no-print{display:none!important;}}';
	var w = window.open('', '_blank');
	if (!w) {
		window.alert('Allow pop-ups to open the student-free class work PDF preview.');
		return;
	}
	w.document.open();
	w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Class Work and Home Work</title><style>' + css + '</style></head><body><div class="swr-eval-wrap">' + printContent.innerHTML + '</div></body></html>');
	w.document.close();
	w.focus();
	setTimeout(function () {
		w.print();
	}, 400);
}
function prfShareClassPdf(button) {
	var pdfUrl = button.getAttribute('data-pdf-url');
	var filename = button.getAttribute('data-pdf-filename') || 'class-work-home-work.pdf';
	var originalHtml = button.innerHTML;

	function downloadPdf(blob, message) {
		var objectUrl = window.URL.createObjectURL(blob);
		var link = document.createElement('a');
		link.href = objectUrl;
		link.download = filename;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		window.setTimeout(function () {
			window.URL.revokeObjectURL(objectUrl);
		}, 1000);
		window.alert(message || 'PDF downloaded. Attach it in WhatsApp if no compatible share target is available.');
	}

	function restoreButton() {
		button.disabled = false;
		button.innerHTML = originalHtml;
	}

	if (button._prfShareFile && navigator.share) {
		button.disabled = true;
		button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening share panel...';
		try {
			navigator.share({ files: [button._prfShareFile] }).catch(function (error) {
				if (error && error.name === 'AbortError') {
					window.alert('Sharing was cancelled or Windows has no compatible share target for this PDF.');
					return;
				}
				window.alert('The native share handoff failed. The PDF has been downloaded instead.');
				downloadPdf(button._prfShareFile, 'PDF downloaded. Attach it in WhatsApp if the native share handoff is unavailable.');
			}).then(restoreButton);
		} catch (error) {
			window.alert('The native share handoff is unavailable. The PDF has been downloaded instead.');
			downloadPdf(button._prfShareFile, 'PDF downloaded. Attach it in WhatsApp if the native share handoff is unavailable.');
			restoreButton();
		}
		return;
	}

	button.disabled = true;
	button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing PDF...';
	fetch(pdfUrl, { credentials: 'same-origin' })
		.then(function (response) {
			if (!response.ok) {
				throw new Error('Unable to create the class PDF. Please check the selected class and date.');
			}
			var contentType = response.headers.get('Content-Type') || '';
			if (contentType.toLowerCase().indexOf('application/pdf') === -1) {
				throw new Error('The server did not return a PDF. Please sign in again and retry.');
			}
			return response.blob();
		})
		.then(function (blob) {
			downloadPdf(blob, 'This browser could not share the PDF natively. The file was downloaded; attach it in WhatsApp manually.');
		})
		.catch(function (error) {
			window.alert(error && error.message ? error.message : 'Unable to prepare the class PDF. Please try again.');
		})
		.then(restoreButton);
}
function prfPrepareClassPdfShare() {
	var button = document.querySelector('.btn-swr-class-share[data-pdf-url]');
	if (!button || !navigator.share || !navigator.canShare || typeof File === 'undefined') {
		return;
	}
	var probe;
	try {
		probe = new File([''], 'class-report.pdf', { type: 'application/pdf' });
		if (!navigator.canShare({ files: [probe] })) {
			return;
		}
	} catch (error) {
		return;
	}

	var url = button.getAttribute('data-pdf-url');
	var filename = button.getAttribute('data-pdf-filename') || 'class-work-home-work.pdf';
	var originalHtml = button.innerHTML;
	button.disabled = true;
	button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing PDF...';
	fetch(url, { credentials: 'same-origin' })
		.then(function (response) {
			if (!response.ok || (response.headers.get('Content-Type') || '').toLowerCase().indexOf('application/pdf') === -1) {
				throw new Error('Unable to prepare the class PDF for native sharing.');
			}
			return response.blob();
		})
		.then(function (blob) {
			var file = new File([blob], filename, { type: 'application/pdf' });
			if (!navigator.canShare({ files: [file] })) {
				throw new Error('The browser cannot share this class PDF.');
			}
			button._prfShareFile = file;
			button.title = 'Class PDF is ready for native sharing.';
		})
		.catch(function (error) {
			button.title = error && error.message ? error.message : 'Class PDF could not be prepared for native sharing.';
		})
		.then(function () {
			button.disabled = false;
			button.innerHTML = originalHtml;
		});
}
function prfShareClassJpg(button) {
	var table = document.querySelector('#prf_print_area table.swr-grid');
	if (!table) {
		window.alert('The class work table is not available. Select a class and section first.');
		return;
	}

	var originalHtml = button.innerHTML;
	button.disabled = true;
	button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing JPG...';

	function restoreButton() {
		button.disabled = false;
		button.innerHTML = originalHtml;
	}

	function wrapCanvasText(context, value, maxWidth) {
		var lines = [];
		var paragraphs = String(value || '—').replace(/\r/g, '').split('\n');
		for (var p = 0; p < paragraphs.length; p++) {
			var words = paragraphs[p].split(/\s+/);
			var line = '';
			for (var w = 0; w < words.length; w++) {
				if (!words[w]) {
					continue;
				}
				var testLine = line ? line + ' ' + words[w] : words[w];
				if (context.measureText(testLine).width <= maxWidth || !line) {
					line = testLine;
				} else {
					lines.push(line);
					line = words[w];
				}
			}
			lines.push(line || ' ');
		}
		return lines;
	}

	function getCellText(cell) {
		var workLines = cell.querySelectorAll('.prf-work-line');
		if (!workLines.length) {
			return (cell.innerText || cell.textContent || '').replace(/\s+/g, ' ').trim() || '—';
		}
		var lines = [];
		for (var i = 0; i < workLines.length; i++) {
			var label = workLines[i].querySelector('.prf-work-label');
			var value = workLines[i].querySelector('.prf-work-value');
			var labelText = label ? label.textContent.trim() : '';
			var valueText = value ? (value.innerText || value.textContent).replace(/\s+/g, ' ').trim() : '';
			lines.push((labelText ? labelText + ': ' : '') + (valueText || '—'));
		}
		return lines.join('\n');
	}

	function downloadJpg(blob, filename) {
		var objectUrl = window.URL.createObjectURL(blob);
		var link = document.createElement('a');
		link.href = objectUrl;
		link.download = filename;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		window.setTimeout(function () {
			window.URL.revokeObjectURL(objectUrl);
		}, 1000);
		window.alert('JPG downloaded. Open the WhatsApp class group, attach this image, choose the group, and tap Send.');
		restoreButton();
	}

	try {
		var headers = Array.prototype.slice.call(table.querySelectorAll('thead th')).map(function (cell) {
			return (cell.innerText || cell.textContent || '').replace(/\s+/g, ' ').trim();
		});
		var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr')).map(function (row) {
			return Array.prototype.slice.call(row.querySelectorAll('td')).map(getCellText);
		}).filter(function (row) {
			return row.length === headers.length;
		});
		var emptyMessage = table.querySelector('tbody td[colspan]');
		var hasEmptyMessage = rows.length === 0 && !!emptyMessage;
		if (hasEmptyMessage) {
			rows.push([getCellText(emptyMessage)]);
		}
		var canvas = document.createElement('canvas');
		var context = canvas.getContext('2d');
		if (!context || !headers.length) {
			throw new Error('Unable to prepare the class JPG in this browser.');
		}

		var scale = 2;
		var margin = 32;
		var tableWidth = 1336;
		var ratios = [0.12, 0.07, 0.13, 0.23, 0.23, 0.13, 0.09];
		var columnWidths = [];
		for (var c = 0; c < headers.length; c++) {
			columnWidths.push(tableWidth * (ratios[c] || (1 / headers.length)));
		}

		var preparedRows = [];
		var rowHeightTotal = 0;
		context.font = '14px Arial, sans-serif';
		for (var r = 0; r < rows.length; r++) {
			var preparedCells = [];
			var maxLines = 1;
			for (var col = 0; col < headers.length; col++) {
				var cellText = rows[r][col] || '—';
				context.font = col === 0 ? 'bold 14px Arial, sans-serif' : '14px Arial, sans-serif';
				var cellLines = wrapCanvasText(context, cellText, columnWidths[col] - 18);
				preparedCells.push(cellLines);
				maxLines = Math.max(maxLines, cellLines.length);
			}
			var rowHeight = Math.max(42, maxLines * 19 + 18);
			preparedRows.push({ cells: preparedCells, height: rowHeight, empty: hasEmptyMessage && r === 0 });
			rowHeightTotal += rowHeight;
		}

		var tableTop = 151;
		var headerHeight = 46;
		var logicalHeight = tableTop + headerHeight + rowHeightTotal + margin;
		canvas.width = (tableWidth + margin * 2) * scale;
		canvas.height = logicalHeight * scale;
		context.scale(scale, scale);
		context.fillStyle = '#ffffff';
		context.fillRect(0, 0, canvas.width, canvas.height);

		var schoolHeader = document.querySelector('.prf-print-school-details h1');
		var schoolName = schoolHeader ? schoolHeader.textContent.trim() : 'School';
		var classSelect = document.getElementById('class_id');
		var sectionSelect = document.getElementById('section_id');
		var dateInput = document.getElementById('prf_date');
		var className = classSelect && classSelect.selectedIndex >= 0 ? classSelect.options[classSelect.selectedIndex].text : '';
		var sectionName = sectionSelect && sectionSelect.selectedIndex >= 0 ? sectionSelect.options[sectionSelect.selectedIndex].text : '';
		var dateText = dateInput ? dateInput.value : '';
		var addressElement = document.querySelector('.prf-print-school-address');
		var metaElement = document.querySelector('.prf-print-school-meta');
		var schoolAddress = addressElement ? (addressElement.innerText || addressElement.textContent).replace(/\s+/g, ' ').trim() : '';
		var schoolMeta = metaElement ? (metaElement.innerText || metaElement.textContent).replace(/\s+/g, ' ').trim() : '';
		var logo = document.querySelector('.prf-print-school-logo img');

		context.fillStyle = '#20364a';
		context.textAlign = 'center';
		context.font = 'bold 26px Arial, sans-serif';
		context.fillText(schoolName || 'School', margin + tableWidth / 2, 42, tableWidth - 150);
		context.fillStyle = '#526579';
		context.font = '12px Arial, sans-serif';
		if (schoolAddress) {
			context.fillText(schoolAddress, margin + tableWidth / 2, 62, tableWidth - 160);
		}
		context.font = '10px Arial, sans-serif';
		if (schoolMeta) {
			context.fillText(schoolMeta, margin + tableWidth / 2, 80, tableWidth - 160);
		}
		context.fillStyle = '#293d51';
		context.font = 'bold 19px Arial, sans-serif';
		context.fillText('Class work / Home work', margin + tableWidth / 2, 108);
		context.fillStyle = '#506176';
		context.font = '15px Arial, sans-serif';
		context.fillText('Class: ' + className + (sectionName ? ' - ' + sectionName : '') + '    Date: ' + dateText, margin + tableWidth / 2, 132);
		if (logo && logo.complete && logo.naturalWidth > 0) {
			try {
				context.drawImage(logo, margin, 17, 74, 58);
			} catch (ignoreLogoError) {}
		}

		var x = margin;
		var y = tableTop;
		context.textAlign = 'center';
		for (var h = 0; h < headers.length; h++) {
			context.fillStyle = '#edf2f7';
			context.fillRect(x, y, columnWidths[h], headerHeight);
			context.strokeStyle = '#1f2937';
			context.lineWidth = 1;
			context.strokeRect(x, y, columnWidths[h], headerHeight);
			context.fillStyle = '#34495e';
			context.font = 'bold 14px Arial, sans-serif';
			var headerLines = wrapCanvasText(context, headers[h], columnWidths[h] - 12);
			for (var hl = 0; hl < headerLines.length; hl++) {
				context.fillText(headerLines[hl], x + columnWidths[h] / 2, y + 18 + hl * 16, columnWidths[h] - 12);
			}
			x += columnWidths[h];
		}
		y += headerHeight;

		for (var pr = 0; pr < preparedRows.length; pr++) {
			x = margin;
			var preparedRow = preparedRows[pr];
			if (preparedRow.empty) {
				context.fillStyle = '#ffffff';
				context.fillRect(margin, y, tableWidth, preparedRow.height);
				context.strokeStyle = '#1f2937';
				context.lineWidth = 1;
				context.strokeRect(margin, y, tableWidth, preparedRow.height);
				context.fillStyle = '#64748b';
				context.textAlign = 'center';
				context.font = '14px Arial, sans-serif';
				var emptyLines = wrapCanvasText(context, preparedRow.cells[0].join(' '), tableWidth - 24);
				for (var emptyLine = 0; emptyLine < emptyLines.length; emptyLine++) {
					context.fillText(emptyLines[emptyLine], margin + tableWidth / 2, y + 20 + emptyLine * 19, tableWidth - 24);
				}
				y += preparedRow.height;
				continue;
			}
			for (var pc = 0; pc < headers.length; pc++) {
				context.fillStyle = pr % 2 ? '#fcfdff' : '#ffffff';
				context.fillRect(x, y, columnWidths[pc], preparedRow.height);
				context.strokeStyle = '#1f2937';
				context.lineWidth = 1;
				context.strokeRect(x, y, columnWidths[pc], preparedRow.height);
				context.fillStyle = '#334155';
				context.textAlign = pc === 1 || pc === 6 ? 'center' : 'left';
				context.font = pc === 0 ? 'bold 14px Arial, sans-serif' : '14px Arial, sans-serif';
				for (var line = 0; line < preparedRow.cells[pc].length; line++) {
					var textX = context.textAlign === 'center' ? x + columnWidths[pc] / 2 : x + 9;
					context.fillText(preparedRow.cells[pc][line], textX, y + 20 + line * 19, columnWidths[pc] - 18);
				}
				x += columnWidths[pc];
			}
			y += preparedRow.height;
		}

		var filename = 'class-work-home-work.jpg';
		var dateForFile = dateText.replace(/[^0-9-]/g, '');
		if (dateForFile) {
			filename = 'class-work-home-work-' + dateForFile + '.jpg';
		}
		var dataUrl = canvas.toDataURL('image/jpeg', 0.92);
		var base64 = dataUrl.substring(dataUrl.indexOf(',') + 1);
		var binary = window.atob(base64);
		var bytes = new Uint8Array(binary.length);
		for (var byteIndex = 0; byteIndex < binary.length; byteIndex++) {
			bytes[byteIndex] = binary.charCodeAt(byteIndex);
		}
		var blob = new Blob([bytes], { type: 'image/jpeg' });
		var file = null;
		var canShareFile = false;
		try {
			if (typeof File !== 'undefined') {
				file = new File([blob], filename, { type: 'image/jpeg' });
				canShareFile = !!(navigator.share
					&& navigator.canShare
					&& navigator.canShare({ files: [file] }));
			}
		} catch (shareCheckError) {
			canShareFile = false;
		}
		if (canShareFile) {
			try {
				navigator.share({ files: [file] }).catch(function (error) {
					if (error && error.name === 'AbortError') {
						window.alert('Sharing was cancelled or Windows has no compatible share target for this JPG.');
						return;
					}
					downloadJpg(blob, filename);
				}).then(restoreButton);
			} catch (shareError) {
				downloadJpg(blob, filename);
			}
			return;
		}
		downloadJpg(blob, filename);
	} catch (error) {
		window.alert(error && error.message ? error.message : 'Unable to prepare the class JPG. Please try again.');
		restoreButton();
	}
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
