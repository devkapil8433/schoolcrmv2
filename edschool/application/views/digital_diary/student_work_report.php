<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$search_q = isset($search_q) ? $search_q : '';
$date_from = isset($date_from) ? $date_from : date('Y-m-d', strtotime('-8 days'));
$date_to = isset($date_to) ? $date_to : date('Y-m-d');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$filter_student_id = isset($filter_student_id) ? (int) $filter_student_id : 0;
$class_options = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$student_options = isset($student_options) ? $student_options : array('' => translate('select'));
$subjects_for_grid = isset($subjects_for_grid) ? $subjects_for_grid : array();
$report_rows = isset($report_rows) ? $report_rows : array();
$swr_subject_count = count($subjects_for_grid);
$swr_colspan_total = 6 + ($swr_subject_count * 3);
$summary = isset($summary) && is_array($summary) ? $summary : array(
	'school_open_days' => 0,
	'attend_days' => 0,
	'absent_days' => 0,
	'attendance_pct' => 0,
	'without_dress' => 0,
	'bad_conduct' => 0,
	'subject_scores' => array(),
	'grade' => '—',
	'avg_score' => 0,
);
$student_info = isset($student_info) && is_array($student_info) ? $student_info : null;
$period_info = isset($period_info) && is_array($period_info) ? $period_info : null;
$grid_totals = isset($grid_totals) && is_array($grid_totals) ? $grid_totals : null;
$swr_table_ok = !empty($swr_table_ok);
$student_valid = !empty($student_valid);
$swr_grade_class = 'g-d';
if (!empty($summary['grade'])) {
	$g = strtoupper((string) $summary['grade']);
	if ($g === 'A') {
		$swr_grade_class = 'g-a';
	} elseif ($g === 'B') {
		$swr_grade_class = 'g-b';
	} elseif ($g === 'C') {
		$swr_grade_class = 'g-c';
	}
}
?>
<style>
.swr-eval-wrap { font-family: Arial, Helvetica, sans-serif; color: #000; background: #fff; }
.swr-eval-wrap .swr-search-row { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
.swr-eval-wrap .swr-search-row label.swr-red { color: #c00; font-weight: 600; margin: 0; }
.swr-eval-wrap .swr-search-row input[type="text"].swr-search-input { flex: 1; min-width: 200px; border: 1px solid #000; padding: 4px 8px; height: 28px; }
.swr-eval-wrap .swr-filters { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; margin-bottom: 8px; }
.swr-eval-wrap .swr-filters .form-group { margin-bottom: 0; min-width: 140px; }
.swr-eval-wrap .swr-filters label { display: block; font-weight: 600; margin-bottom: 2px; }
.swr-eval-wrap .swr-filters select,
.swr-eval-wrap .swr-filters input.form-control { border: 1px solid #aaa; border-radius: 0; height: 30px; padding: 2px 6px; }
.swr-eval-wrap h1.swr-title { text-align: center; font-size: 22px; font-weight: 700; margin: 16px 0 14px; }
.swr-eval-wrap table.swr-grid { width: 100%; border-collapse: collapse; font-size: 12px; table-layout: fixed; }
.swr-eval-wrap table.swr-grid th,
.swr-eval-wrap table.swr-grid td { border: 1px solid #000; padding: 4px 2px; text-align: center; vertical-align: middle; min-height: 28px; }
.swr-eval-wrap table.swr-grid th { background: #f9f9f9; font-weight: 600; }
.swr-eval-wrap .swr-rot { writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; max-height: 64px; margin: 0 auto; font-size: 11px; }
.swr-eval-wrap .swr-report-block { margin-top: 18px; border: 1px solid #000; padding: 12px; }
.swr-eval-wrap .swr-report-block h3 { font-size: 15px; font-weight: 700; margin: 0 0 10px; }
.swr-eval-wrap .swr-report-cols { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-start; }
.swr-eval-wrap .swr-report-labels { flex: 0 0 140px; font-size: 13px; line-height: 1.7; }
.swr-eval-wrap .swr-report-main { flex: 1; min-width: 280px; }
.swr-eval-wrap .swr-field-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 8px; align-items: center; }
.swr-eval-wrap .swr-field-row label { font-size: 12px; margin: 0; font-weight: normal; }
.swr-eval-wrap .swr-field-box { border: 1px solid #000; min-width: 36px; height: 26px; display: inline-flex; align-items: center; justify-content: center; padding: 0 6px; font-weight: 600; }
.swr-eval-wrap .swr-subj-grid { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
.swr-eval-wrap .swr-subj-item { text-align: center; }
.swr-eval-wrap .swr-subj-item .nm { font-size: 11px; margin-bottom: 2px; }
.swr-eval-wrap .swr-subj-item .bx { border: 1px solid #000; min-width: 52px; padding: 4px; font-size: 12px; font-weight: 600; }
.swr-eval-wrap .swr-subj-item .bx.hindi-green { background: #2e7d32; color: #fff; border-color: #1b5e20; }
.swr-eval-wrap .swr-grade-box { text-align: center; margin-left: auto; }
.swr-eval-wrap .swr-grade-box .g-label { font-size: 13px; font-weight: 600; margin-bottom: 4px; }
.swr-eval-wrap .swr-grade-box .g-val { width: 72px; height: 72px; border: 1px solid #000; font-size: 36px; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
.swr-eval-wrap .swr-grade-box .g-val.g-a { background: #2e7d32; color: #fff; }
.swr-eval-wrap .swr-grade-box .g-val.g-b { background: #66bb6a; color: #000; }
.swr-eval-wrap .swr-grade-box .g-val.g-c { background: #fdd835; color: #000; }
.swr-eval-wrap .swr-grade-box .g-val.g-d { background: #ef5350; color: #fff; }
.swr-eval-wrap .swr-student-card { border: 1px solid #000; padding: 12px 14px; margin: 12px 0 14px; background: #fafafa; }
.swr-eval-wrap .swr-student-card .swr-student-name { font-size: 18px; font-weight: 700; margin: 0 0 6px; }
.swr-eval-wrap .swr-student-meta { display: flex; flex-wrap: wrap; gap: 14px 24px; font-size: 13px; }
.swr-eval-wrap .swr-student-meta span strong { font-weight: 700; }
.swr-eval-wrap .swr-stat-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; }
.swr-eval-wrap .swr-stat-card { flex: 1 1 140px; border: 1px solid #000; padding: 8px 10px; background: #fff; min-width: 120px; }
.swr-eval-wrap .swr-stat-card .lbl { font-size: 11px; color: #444; margin-bottom: 2px; }
.swr-eval-wrap .swr-stat-card .val { font-size: 20px; font-weight: 700; line-height: 1.2; }
.swr-eval-wrap .swr-stat-card .sub { font-size: 10px; color: #666; margin-top: 2px; }
.swr-eval-wrap .swr-legend { font-size: 11px; color: #444; margin-bottom: 10px; padding: 6px 8px; background: #f5f5f5; border: 1px solid #ddd; }
.swr-eval-wrap .swr-legend span { margin-right: 14px; }
.swr-eval-wrap .swr-legend .swr-tick { color: #1b5e20; font-weight: 700; }
.swr-eval-wrap table.swr-grid td.swr-val-ok { background: #c8e6c9; color: #1b5e20; font-weight: 700; }
.swr-eval-wrap table.swr-grid td.swr-val-bad { background: #ffcdd2; color: #b71c1c; font-weight: 700; }
.swr-eval-wrap table.swr-grid td.swr-val-warn { background: #ffe0b2; color: #e65100; font-weight: 700; }
.swr-eval-wrap table.swr-grid td.swr-val-miss { background: #fce4ec; color: #880e4f; }
.swr-eval-wrap table.swr-grid td.swr-att-p { background: #c8e6c9; color: #1b5e20; font-weight: 700; }
.swr-eval-wrap table.swr-grid td.swr-att-a { background: #ffcdd2; color: #b71c1c; font-weight: 700; }
.swr-eval-wrap table.swr-grid td.swr-att-l,
.swr-eval-wrap table.swr-grid td.swr-att-hd { background: #ffe0b2; color: #e65100; font-weight: 700; }
.swr-eval-wrap table.swr-grid td.swr-done { background: #c8e6c9; color: #1b5e20; font-weight: 700; }
.swr-eval-wrap table.swr-grid tfoot td { background: #f0f0f0; font-weight: 700; font-size: 11px; }
.swr-eval-wrap .swr-subj-item .bx .swr-pct { display: block; font-size: 10px; font-weight: normal; margin-top: 2px; opacity: 0.9; }
.swr-eval-wrap .swr-subj-item .swr-fwl { font-size: 10px; color: #555; margin-top: 3px; line-height: 1.3; }
.swr-eval-wrap .swr-progress { height: 6px; background: #e0e0e0; margin-top: 4px; border: 1px solid #bbb; }
.swr-eval-wrap .swr-progress > span { display: block; height: 100%; background: #2e7d32; }
.swr-eval-wrap .swr-score-note { font-size: 11px; color: #555; margin: 8px 0 0; line-height: 1.5; }
.swr-eval-wrap .swr-grade-scale { font-size: 11px; color: #555; margin-top: 8px; }
.swr-eval-wrap .swr-report-labels div { font-weight: 600; }
.swr-eval-wrap .swr-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 18px; padding-top: 12px; border-top: 1px solid #ccc; }
.swr-eval-wrap .swr-actions .btn-swr { background: #00d4ff; border: 1px solid #000; color: #000; font-weight: 600; padding: 8px 18px; border-radius: 2px; cursor: pointer; font-size: 13px; }
.swr-eval-wrap .swr-actions .btn-swr:hover { filter: brightness(0.95); }
@media print {
	.swr-eval-wrap .panel-btn, .swr-eval-wrap .swr-actions { display: none; }
	.swr-eval-wrap table.swr-grid td.swr-val-ok,
	.swr-eval-wrap table.swr-grid td.swr-att-p,
	.swr-eval-wrap table.swr-grid td.swr-done { background: #c8e6c9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	.swr-eval-wrap table.swr-grid td.swr-val-bad,
	.swr-eval-wrap table.swr-grid td.swr-att-a { background: #ffcdd2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	.swr-eval-wrap table.swr-grid td.swr-val-warn,
	.swr-eval-wrap table.swr-grid td.swr-att-l,
	.swr-eval-wrap table.swr-grid td.swr-att-hd { background: #ffe0b2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	.swr-eval-wrap table.swr-grid td.swr-val-miss { background: #fce4ec !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-file-alt"></i> Student Work Evaluation Report</h4>
				<div class="panel-btn">
					<a href="<?= base_url('digital_diary/student_work_report_entry') . (is_superadmin_loggedin() && !empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '') ?>" class="btn btn-default btn-circle"><i class="fas fa-table"></i> Daily work grid</a>
				</div>
			</header>
			<div class="panel-body swr-eval-wrap">
				<?php if (!$swr_table_ok): ?>
				<div class="alert alert-warning">
					<strong>Setup required.</strong> Run <code>application/sql/digital_diary_student_work_report.sql</code> on the database.
				</div>
				<?php endif; ?>

				<?php if (is_superadmin_loggedin() && empty($branch_id)): ?>
				<section class="panel mb-md">
					<header class="panel-heading"><h4 class="panel-title"><?= translate('branch') ?></h4></header>
					<div class="panel-body">
						<?php echo form_open(base_url('digital_diary/student_work_report'), array('method' => 'get', 'class' => 'form-inline')); ?>
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
					<a href="<?= base_url('digital_diary/student_work_report') ?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> <?= translate('change') ?></a>
				</div>
				<?php endif; ?>

				<?php if ($swr_table_ok && (!is_superadmin_loggedin() || !empty($branch_id))): ?>
				<?php echo form_open(base_url('digital_diary/student_work_report'), array('method' => 'get', 'class' => 'swr-eval-form', 'id' => 'frm_swr_eval')); ?>
				<?php if (is_superadmin_loggedin() && !empty($branch_id)): ?>
				<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
				<?php endif; ?>

				<div class="swr-search-row">
					<label class="swr-red">Search By</label>
					<input type="text" name="search" class="swr-search-input" value="<?= html_escape($search_q) ?>" placeholder="" autocomplete="off">
				</div>

				<div class="swr-filters" id="swr_eval_filters">
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
						<label>From</label>
						<input type="text" name="date_from" class="form-control" value="<?= html_escape($date_from) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
					</div>
					<div class="form-group">
						<label>To</label>
						<input type="text" name="date_to" class="form-control" value="<?= html_escape($date_to) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
					</div>
					<div class="form-group" style="padding-top: 18px;">
						<button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
					</div>
				</div>
				<?php echo form_close(); ?>

				<h1 class="swr-title">Student Work Evaluation Report</h1>

				<?php if (!empty($student_info)): ?>
				<div class="swr-student-card">
					<div class="swr-student-name"><?= html_escape($student_info['name']) ?></div>
					<div class="swr-student-meta">
						<span><strong>Class:</strong> <?= html_escape($student_info['class_name']) ?> — <?= html_escape($student_info['section_name']) ?></span>
						<?php if (!empty($student_info['roll'])): ?>
						<span><strong>Roll No:</strong> <?= html_escape($student_info['roll']) ?></span>
						<?php endif; ?>
						<?php if (!empty($period_info)): ?>
						<span><strong>Period:</strong> <?= html_escape($period_info['date_from_disp']) ?> to <?= html_escape($period_info['date_to_disp']) ?></span>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>

				<?php if (!empty($period_info) && $student_valid): ?>
				<div class="swr-stat-row">
					<div class="swr-stat-card">
						<div class="lbl">School open days</div>
						<div class="val"><?= (int) $summary['school_open_days'] ?></div>
						<div class="sub"><?= (int) $period_info['excluded_days'] ?> holiday/weekend days excluded</div>
					</div>
					<div class="swr-stat-card">
						<div class="lbl">Attendance</div>
						<div class="val"><?= (int) $summary['attendance_pct'] ?>%</div>
						<div class="sub"><?= (int) $summary['attend_days'] ?> present · <?= (int) $summary['absent_days'] ?> absent</div>
					</div>
					<div class="swr-stat-card">
						<div class="lbl">Discipline issues</div>
						<div class="val"><?= (int) $summary['without_dress'] + (int) $summary['bad_conduct'] ?></div>
						<div class="sub"><?= (int) $summary['without_dress'] ?> dress · <?= (int) $summary['bad_conduct'] ?> conduct</div>
					</div>
					<div class="swr-stat-card">
						<div class="lbl">Overall work score</div>
						<div class="val"><?= (int) $summary['avg_score'] ?>/50</div>
						<div class="sub">Average across subjects</div>
					</div>
				</div>
				<?php elseif (!empty($period_info) && !empty($report_rows)): ?>
				<div class="swr-stat-row">
					<div class="swr-stat-card">
						<div class="lbl">Calendar days in range</div>
						<div class="val"><?= (int) $period_info['calendar_days'] ?></div>
					</div>
					<div class="swr-stat-card">
						<div class="lbl">School open days</div>
						<div class="val"><?= (int) $period_info['school_open_days'] ?></div>
						<div class="sub"><?= (int) $period_info['excluded_days'] ?> excluded (holidays &amp; weekly off)</div>
					</div>
				</div>
				<?php endif; ?>

				<?php if (!empty($report_rows)): ?>
				<div class="swr-legend">
					<span><span class="swr-tick">✓</span> / <strong>P</strong> = green (done / present)</span>
					<span><strong>A</strong> = red (absent)</span>
					<span><strong>L</strong> / <strong>HD</strong> = orange (late / half day)</span>
					<span>pink = not done (student was present)</span>
					<span><strong>T. Days</strong> = teaching day number (holidays/weekends not counted)</span>
					<span><strong>Score</strong> = completed work checks ÷ (open days × 3) × 50</span>
				</div>
				<?php endif; ?>

				<div class="table-responsive swr-grid-scroll">
					<table class="swr-grid">
						<thead>
							<tr>
								<th rowspan="2" style="width:36px;">Sr.</th>
								<th rowspan="2" style="width:88px;">Date</th>
								<th colspan="2">Attendance</th>
								<th colspan="2">Discipline</th>
								<?php foreach ($subjects_for_grid as $sg): ?>
								<th colspan="3"><?= html_escape($sg['subject_name']) ?></th>
								<?php endforeach; ?>
							</tr>
							<tr>
								<th style="width:40px;"><span class="swr-rot">T. Days</span></th>
								<th style="width:40px;">Attend.</th>
								<th style="width:36px;"><span class="swr-rot">Dress</span></th>
								<th style="width:36px;"><span class="swr-rot">Conduct</span></th>
								<?php foreach ($subjects_for_grid as $sg): ?>
								<th>Fair Copy</th>
								<th>Writing Work</th>
								<th>Learning Work</th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($report_rows)): ?>
							<tr><td colspan="<?= (int) $swr_colspan_total ?>" class="text-center text-muted" style="padding:16px;"><?= translate('select') ?> <?= translate('class') ?>, <?= translate('section') ?>, <?= translate('student') ?> and date range.</td></tr>
							<?php else: ?>
								<?php foreach ($report_rows as $row): ?>
							<tr>
								<td><?= (int) $row['sr'] ?></td>
								<td><?= html_escape($row['date_disp']) ?></td>
								<td><?= html_escape($row['t_days']) ?></td>
								<?php
								$attVal = isset($row['attendance']) ? (string) $row['attendance'] : '';
								$attClass = '';
								if ($attVal === 'P') {
									$attClass = 'swr-att-p';
								} elseif ($attVal === 'A') {
									$attClass = 'swr-att-a';
								} elseif ($attVal === 'L') {
									$attClass = 'swr-att-l';
								} elseif ($attVal === 'HD') {
									$attClass = 'swr-att-hd';
								}
								?>
								<td class="<?= html_escape($attClass) ?>"><?= html_escape($attVal) ?></td>
								<?php
								$dressVal = isset($row['dress']) ? (string) $row['dress'] : '';
								$conductVal = isset($row['conduct']) ? (string) $row['conduct'] : '';
								$dressClass = $dressVal !== '' ? 'swr-val-ok' : (($attVal !== '' && $attVal !== 'A') ? 'swr-val-warn' : '');
								$conductClass = $conductVal !== '' ? 'swr-val-ok' : (($attVal !== '' && $attVal !== 'A') ? 'swr-val-warn' : '');
								?>
								<td class="<?= html_escape($dressClass) ?>"><?= html_escape($dressVal) ?></td>
								<td class="<?= html_escape($conductClass) ?>"><?= html_escape($conductVal) ?></td>
								<?php
								$subj_map = isset($row['subjects']) && is_array($row['subjects']) ? $row['subjects'] : array();
								$workMiss = ($attVal !== '' && $attVal !== 'A');
								foreach ($subjects_for_grid as $sg):
									$sid = (int) $sg['subject_id'];
									$sc = isset($subj_map[$sid]) ? $subj_map[$sid] : array('fair' => '', 'writing' => '', 'learn' => '');
									$fairClass = !empty($sc['fair']) ? 'swr-val-ok' : ($workMiss ? 'swr-val-miss' : '');
									$writingClass = !empty($sc['writing']) ? 'swr-val-ok' : ($workMiss ? 'swr-val-miss' : '');
									$learnClass = !empty($sc['learn']) ? 'swr-val-ok' : ($workMiss ? 'swr-val-miss' : '');
								?>
								<td class="swr-subj-first <?= html_escape($fairClass) ?>"><?= html_escape($sc['fair']) ?></td>
								<td class="<?= html_escape($writingClass) ?>"><?= html_escape($sc['writing']) ?></td>
								<td class="<?= html_escape($learnClass) ?>"><?= html_escape($sc['learn']) ?></td>
								<?php endforeach; ?>
							</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
						<?php if (!empty($grid_totals) && !empty($report_rows)): ?>
						<tfoot>
							<tr>
								<td colspan="2">Total</td>
								<td><?= (int) $summary['school_open_days'] ?></td>
								<td><?= (int) $grid_totals['present'] ?>P / <?= (int) $grid_totals['absent'] ?>A</td>
								<td><?= (int) $grid_totals['without_dress'] ?></td>
								<td><?= (int) $grid_totals['bad_conduct'] ?></td>
								<?php foreach ($subjects_for_grid as $sg):
									$sid = (int) $sg['subject_id'];
									$gt = isset($grid_totals['subjects'][$sid]) ? $grid_totals['subjects'][$sid] : array('fair' => 0, 'writing' => 0, 'learning' => 0);
								?>
								<td><?= (int) $gt['fair'] ?></td>
								<td><?= (int) $gt['writing'] ?></td>
								<td><?= (int) $gt['learning'] ?></td>
								<?php endforeach; ?>
							</tr>
						</tfoot>
						<?php endif; ?>
					</table>
				</div>

				<div class="swr-report-block">
					<h3>Report:-</h3>
					<div class="swr-report-cols">
						<div class="swr-report-labels">
							<div>1. Attendance Summary</div>
							<div>2. Discipline Summary</div>
							<div>3. Work Summary</div>
						</div>
						<div class="swr-report-main">
							<div class="swr-field-row">
								<label>Number of days school open</label>
								<span class="swr-field-box"><?= (int) $summary['school_open_days'] ?></span>
								<label>Number of days student attend the class</label>
								<span class="swr-field-box"><?= (int) $summary['attend_days'] ?></span>
								<?php if ($student_valid): ?>
								<label>Absent days</label>
								<span class="swr-field-box"><?= (int) $summary['absent_days'] ?></span>
								<label>Attendance %</label>
								<span class="swr-field-box"><?= (int) $summary['attendance_pct'] ?>%</span>
								<?php endif; ?>
							</div>
							<div class="swr-field-row">
								<label>Without Dress</label>
								<span class="swr-field-box"><?= (int) $summary['without_dress'] ?></span>
								<label>Bad Conduct Found</label>
								<span class="swr-field-box"><?= (int) $summary['bad_conduct'] ?></span>
							</div>
							<div class="swr-subj-grid" style="margin-top:10px;">
								<?php
								$ss = !empty($summary['subject_scores']) ? $summary['subject_scores'] : array();
								foreach ($ss as $sc):
									$bx = !empty($sc['is_hindi']) ? 'bx hindi-green' : 'bx';
									$pct = isset($sc['percent']) ? (int) $sc['percent'] : 0;
									$checks = isset($sc['checks']) ? (int) $sc['checks'] : 0;
									$maxChk = isset($sc['max_checks']) ? (int) $sc['max_checks'] : 0;
								?>
								<div class="swr-subj-item">
									<div class="nm"><?= html_escape($sc['label']) ?></div>
									<div class="<?= $bx ?>">
										<?= html_escape($sc['text']) ?>
										<span class="swr-pct"><?= $pct ?>% complete</span>
									</div>
									<div class="swr-progress" title="<?= $checks ?> of <?= $maxChk ?> checks"><span style="width:<?= min(100, $pct) ?>%;"></span></div>
									<?php if (isset($sc['fair'])): ?>
									<div class="swr-fwl">F: <?= (int) $sc['fair'] ?> · W: <?= (int) $sc['writing'] ?> · L: <?= (int) $sc['learning'] ?></div>
									<?php endif; ?>
								</div>
								<?php endforeach; ?>
								<div class="swr-grade-box">
									<div class="g-label">Grade</div>
									<div class="g-val <?= html_escape($swr_grade_class) ?>"><?= html_escape($summary['grade']) ?></div>
								</div>
							</div>
							<p class="swr-score-note">Each subject score is out of 50, based on Fair Copy, Writing Work, and Learning Work completed on school open days (<?= (int) $summary['school_open_days'] ?> days × 3 checks = <?= (int) $summary['school_open_days'] * 3 ?> possible checks per subject). Holidays and weekly offs are excluded.</p>
							<div class="swr-grade-scale"><strong>Grade scale:</strong> A ≥ 45 · B ≥ 40 · C ≥ 33 · D &lt; 33 (average of subject scores)</div>
						</div>
					</div>
				</div>

				<div class="swr-actions">
					<button type="button" class="btn-swr" onclick="window.open('https://wa.me/','_blank');">Whatsapp</button>
					<button type="button" class="btn-swr" onclick="alert('Message');">Message</button>
					<button type="button" class="btn-swr" onclick="window.location.href='mailto:?subject=Student%20Work%20Evaluation';">Email</button>
					<button type="button" class="btn-swr" onclick="if (navigator.share) navigator.share({title:'Report'}); else alert('Share');">Share</button>
					<button type="button" class="btn-swr" onclick="window.print();">PDF</button>
					<button type="button" class="btn-swr" onclick="swrEvalExportExcel();">Excel</button>
				</div>

				<?php if ($filter_class_id && $filter_section_id && !$filter_student_id): ?>
				<p class="text-muted text-center" style="margin-top:10px;"><i class="fas fa-info-circle"></i> <?= translate('select') ?> <?= translate('student') ?> <?= translate('to') ?> load attendance and work data.</p>
				<?php elseif ($filter_student_id && !$student_valid): ?>
				<p class="text-muted text-center" style="margin-top:10px;">Selected student is not in this class/section.</p>
				<?php endif; ?>

				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<?php if ($swr_table_ok && (!is_superadmin_loggedin() || !empty($branch_id))): ?>
<script type="text/javascript">
function swrEvalExportExcel() {
	var t = document.querySelector('.swr-eval-wrap table.swr-grid');
	if (!t) return;
	var h = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Report</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>' + t.outerHTML + '</body></html>';
	var a = document.createElement('a');
	a.href = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(h);
	a.download = 'student_work_evaluation.xls';
	a.click();
}
function swrEvalReinitSelect2(sel) {
	try {
		if ($(sel).data('select2')) {
			$(sel).select2('destroy');
		}
	} catch (e) {}
	$(sel).removeData('__select2');
	$(sel).themePluginSelect2({});
}
function swrEvalStudentPlaceholder(mode) {
	if (mode === 'class') {
		return '<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	if (mode === 'section') {
		return '<option value=""><?= htmlspecialchars(translate('select') . ' ' . translate('section'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	return '<option value=""><?= htmlspecialchars(translate('select'), ENT_QUOTES, 'UTF-8') ?></option>';
}
function swrEvalLoadStudentsForEval(selectedStudentId) {
	var cid = $('#frm_swr_eval #class_id').val();
	var sid = $('#frm_swr_eval #section_id').val();
	if (!cid || !sid) {
		$('#frm_swr_eval #student_id').html(swrEvalStudentPlaceholder(!cid ? 'class' : 'section'));
		swrEvalReinitSelect2('#frm_swr_eval #student_id');
		return;
	}
	$.ajax({
		url: base_url + 'student/get_students_by_class_section',
		type: 'POST',
		data: { class_id: cid, section_id: sid },
		success: function (html) {
			$('#frm_swr_eval #student_id').html(html);
			if (selectedStudentId) {
				$('#frm_swr_eval #student_id').val(String(selectedStudentId));
			}
			swrEvalReinitSelect2('#frm_swr_eval #student_id');
		}
	});
}
$(document).ready(function () {
	var $form = $('#frm_swr_eval');
	if (!$form.length) {
		return;
	}
	var preselectedStudent = <?= (int) $filter_student_id ?>;
	var initialClass = $('#frm_swr_eval #class_id').val();
	var initialSection = $('#frm_swr_eval #section_id').val();
	if (initialClass && initialSection) {
		swrEvalLoadStudentsForEval(preselectedStudent);
	}
	$form.on('change', '#class_id', function () {
		var v = $(this).val();
		if (!v) {
			$('#frm_swr_eval #section_id').html('<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>');
			swrEvalReinitSelect2('#frm_swr_eval #section_id');
			$('#frm_swr_eval #student_id').html(swrEvalStudentPlaceholder('class'));
			swrEvalReinitSelect2('#frm_swr_eval #student_id');
			return;
		}
		$('#frm_swr_eval #student_id').html(swrEvalStudentPlaceholder('section'));
		swrEvalReinitSelect2('#frm_swr_eval #student_id');
	});
	$form.on('change', '#section_id', function () {
		swrEvalLoadStudentsForEval(0);
	});
});
$(document).ajaxComplete(function (event, xhr, settings) {
	if (!settings.url || !$('#frm_swr_eval').length) {
		return;
	}
	if (settings.url.indexOf('ajax/getSectionByClass') === -1) {
		return;
	}
	swrEvalReinitSelect2('#frm_swr_eval #section_id');
	$('#frm_swr_eval #student_id').html(swrEvalStudentPlaceholder('section'));
	swrEvalReinitSelect2('#frm_swr_eval #student_id');
});
</script>
<?php endif; ?>
