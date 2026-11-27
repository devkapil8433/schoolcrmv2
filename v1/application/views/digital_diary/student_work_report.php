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
$swr_colspan_total = 6 + (count($subjects_for_grid) * 3);
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
$skill_summary = isset($skill_summary) ? $skill_summary : array();

$gradeLetter = strtoupper(trim((string) (isset($summary['grade']) ? $summary['grade'] : '')));
if ($gradeLetter === 'A' || $gradeLetter === 'B') {
	$gradeLabelClass = 'label-success';
} elseif ($gradeLetter === 'C') {
	$gradeLabelClass = 'label-warning';
} elseif ($gradeLetter !== '' && $gradeLetter !== '—') {
	$gradeLabelClass = 'label-danger';
} else {
	$gradeLabelClass = 'label-default';
}
$attPct = (int) $summary['attendance_pct'];
if ($attPct >= 85) {
	$attLabelClass = 'label-success';
} elseif ($attPct >= 70) {
	$attLabelClass = 'label-warning';
} else {
	$attLabelClass = 'label-danger';
}

$canShareActions = $student_valid && !empty($student_info);

$waHref = '';
if ($canShareActions) {
	$rawMobile = isset($student_info['mobile']) ? preg_replace('/\D+/', '', (string) $student_info['mobile']) : '';
	$shareLines = array();
	$shareLines[] = ($branch_name !== '' ? $branch_name : 'School');
	$shareLines[] = 'STUDENT WORK EVALUATION REPORT';
	$shareLines[] = 'Student: ' . $student_info['name'];
	$shareLines[] = 'Class: ' . $student_info['class_name'] . ' — ' . $student_info['section_name'];
	if (!empty($period_info)) {
		$shareLines[] = 'Period: ' . $period_info['date_from_disp'] . ' to ' . $period_info['date_to_disp'];
	}
	$shareLines[] = 'Attendance: ' . $attPct . '% (' . (int) $summary['attend_days'] . ' present / ' . (int) $summary['absent_days'] . ' absent)';
	$shareLines[] = 'Without Dress: ' . (int) $summary['without_dress'] . ' · Bad Conduct: ' . (int) $summary['bad_conduct'];
	$shareOutOf = !empty($summary['school_open_days']) ? (int) $summary['school_open_days'] : 0;
	$shareLines[] = 'Work Score: ' . (int) $summary['avg_score'] . ($shareOutOf > 0 ? '/' . $shareOutOf : '') . ' · Grade: ' . $summary['grade'];
	if (!empty($summary['subject_scores'])) {
		$shareLines[] = '';
		$shareLines[] = 'SUBJECT SCORES';
		foreach ($summary['subject_scores'] as $scShare) {
			$shareLines[] = '• ' . $scShare['label'] . ': ' . $scShare['text'] . ' (' . (isset($scShare['percent']) ? (int) $scShare['percent'] : 0) . '%)';
		}
	}
	$shareLines[] = '';
	$shareLines[] = '— End of report —';
	$waText = implode("\n", $shareLines);
	if (strlen($waText) > 3500) {
		$waText = substr($waText, 0, 3400) . "\n…";
	}
	if ($rawMobile !== '') {
		if (strlen($rawMobile) === 10) {
			$rawMobile = '91' . $rawMobile;
		}
		$waHref = 'https://wa.me/' . $rawMobile . '?text=' . rawurlencode($waText);
	}
}

$cellClass = function ($type, $val, $present = false) {
	$v = trim((string) $val);
	if ($type === 'attendance') {
		if ($v === 'P') {
			return 'success';
		}
		if ($v === 'A') {
			return 'danger';
		}
		if ($v === 'L' || $v === 'HD') {
			return 'warning';
		}
		return 'active';
	}
	if ($v !== '') {
		return 'success';
	}
	return $present ? 'danger' : 'active';
};
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-file-alt"></i> Student Work Evaluation Report</h4>
				<div class="panel-btn">
					<a href="<?= base_url('digital_diary/student_work_report_entry') . (is_superadmin_loggedin() && !empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '') ?>" class="btn btn-default btn-circle"><i class="fas fa-table"></i> Daily work grid</a>
				</div>
			</header>
			<div class="panel-body">
				<?php if (!$swr_table_ok): ?>
				<div class="alert alert-warning">
					<strong>Setup required.</strong> Run <code>application/sql/digital_diary_student_work_report.sql</code> on the database.
				</div>
				<?php endif; ?>

				<?php if (is_superadmin_loggedin() && empty($branch_id)): ?>
				<section class="panel mb-md no-print">
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
				<div class="alert alert-info no-print">
					<strong><?= translate('branch') ?>:</strong> <?= html_escape($branch_name ?: get_type_name_by_id('branch', $branch_id)) ?>
					<a href="<?= base_url('digital_diary/student_work_report') ?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> <?= translate('change') ?></a>
					<div class="clearfix"></div>
				</div>
				<?php endif; ?>

				<?php if ($swr_table_ok && (!is_superadmin_loggedin() || !empty($branch_id))): ?>
				<?php echo form_open(base_url('digital_diary/student_work_report'), array('method' => 'get', 'class' => 'mb-md no-print', 'id' => 'frm_swr_eval')); ?>
				<?php if (is_superadmin_loggedin() && !empty($branch_id)): ?>
				<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
				<?php endif; ?>
				<div class="row">
					<div class="col-md-2">
						<div class="form-group">
							<label class="control-label">Search</label>
							<input type="text" name="search" class="form-control" value="<?= html_escape($search_q) ?>" placeholder="Name / roll" autocomplete="off">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label class="control-label"><?= translate('class') ?></label>
							<?= form_dropdown('class_id', $class_options, $filter_class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'") ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label class="control-label"><?= translate('section') ?></label>
							<?= form_dropdown('section_id', $section_options, $filter_section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'") ?>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label class="control-label"><?= translate('student') ?></label>
							<?= form_dropdown('student_id', $student_options, $filter_student_id, "class='form-control' id='student_id' data-plugin-selectTwo data-width='100%'") ?>
						</div>
					</div>
					<div class="col-md-1">
						<div class="form-group">
							<label class="control-label">From</label>
							<input type="text" name="date_from" class="form-control" value="<?= html_escape($date_from) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
					</div>
					<div class="col-md-1">
						<div class="form-group">
							<label class="control-label">To</label>
							<input type="text" name="date_to" class="form-control" value="<?= html_escape($date_to) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label class="control-label">&nbsp;</label>
							<button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div id="swr_print_area">
					<div class="text-center mb-md">
						<h3 class="mt-none mb-xs">Student Work Evaluation Report</h3>
						<?php if (!empty($period_info)): ?>
						<p class="mb-none"><span class="label label-primary"><?= html_escape($period_info['date_from_disp']) ?> to <?= html_escape($period_info['date_to_disp']) ?></span></p>
						<?php endif; ?>
					</div>

					<?php if (!empty($student_info)): ?>
					<section class="panel panel-horizontal mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-user-graduate"></i> <?= html_escape($student_info['name']) ?></h4>
						</header>
						<div class="panel-body">
							<table class="table table-bordered table-condensed mb-none">
								<tbody>
									<tr>
										<th width="140">Class</th>
										<td><?= html_escape($student_info['class_name']) ?> — <?= html_escape($student_info['section_name']) ?></td>
										<th width="140">Roll No</th>
										<td><?= html_escape(!empty($student_info['roll']) ? $student_info['roll'] : '—') ?></td>
									</tr>
									<tr>
										<th>Admission No</th>
										<td><?= html_escape(!empty($student_info['admission_no']) ? $student_info['admission_no'] : '—') ?></td>
										<th>Period</th>
										<td><?= !empty($period_info) ? html_escape($period_info['date_from_disp'] . ' to ' . $period_info['date_to_disp']) : '—' ?></td>
									</tr>
								</tbody>
							</table>
						</div>
					</section>
					<?php endif; ?>

					<?php
					if (!empty($skill_summary)) {
						$this->load->view('digital_diary/_skill_summary_graph', array(
							'skills' => $skill_summary,
							'graph_title' => 'Student Skill Summary',
						));
					}
					?>

					<?php if (!empty($period_info) && $student_valid): ?>
					<div class="row">
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-primary">
								<div class="panel-body">
									<small class="text-muted">School open days</small>
									<h3 class="mt-none mb-none"><?= (int) $summary['school_open_days'] ?></h3>
									<small class="text-muted"><?= (int) $period_info['excluded_days'] ?> holiday/weekend excluded</small>
								</div>
							</section>
						</div>
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-success">
								<div class="panel-body">
									<small class="text-muted">Attendance</small>
									<h3 class="mt-none mb-none"><span class="label <?= $attLabelClass ?>"><?= $attPct ?>%</span></h3>
									<small class="text-muted"><?= (int) $summary['attend_days'] ?> present · <?= (int) $summary['absent_days'] ?> absent</small>
								</div>
							</section>
						</div>
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-warning">
								<div class="panel-body">
									<small class="text-muted">Discipline issues</small>
									<h3 class="mt-none mb-none"><?= (int) $summary['without_dress'] + (int) $summary['bad_conduct'] ?></h3>
									<small class="text-muted"><?= (int) $summary['without_dress'] ?> dress · <?= (int) $summary['bad_conduct'] ?> conduct</small>
								</div>
							</section>
						</div>
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-danger">
								<div class="panel-body">
									<small class="text-muted">Overall work score</small>
									<h3 class="mt-none mb-none"><?= (int) $summary['avg_score'] ?><?= !empty($summary['school_open_days']) ? '/' . (int) $summary['school_open_days'] : '' ?> · <span class="label <?= $gradeLabelClass ?>"><?= html_escape($summary['grade']) ?></span></h3>
									<small class="text-muted">Average across subjects</small>
								</div>
							</section>
						</div>
					</div>
					<?php elseif (!empty($period_info) && !empty($report_rows)): ?>
					<div class="alert alert-info">
						<strong>School open days:</strong> <?= (int) $period_info['school_open_days'] ?>
						· Calendar days: <?= (int) $period_info['calendar_days'] ?>
						· Excluded: <?= (int) $period_info['excluded_days'] ?>
					</div>
					<?php endif; ?>

					<section class="panel panel-featured-left panel-featured-info mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-table"></i> Daily Evaluation Grid</h4>
						</header>
						<div class="panel-body">
							<div class="alert alert-info mb-md no-print">
								<span class="label label-success">Green</span> Present / Done
								<span class="label label-danger">Red</span> Absent / Not done
								<span class="label label-warning">Orange</span> Late / Half day / Dress-conduct issue
								<span class="label label-default">Gray</span> No data
							</div>
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-hover mb-none" id="swr_eval_table">
									<thead>
										<tr>
											<th rowspan="2" class="text-center">Sr.</th>
											<th rowspan="2" class="text-center">Date</th>
											<th colspan="2" class="text-center">Attendance</th>
											<th colspan="2" class="text-center">Discipline</th>
											<?php foreach ($subjects_for_grid as $sg): ?>
											<th colspan="3" class="text-center"><?= html_escape($sg['subject_name']) ?></th>
											<?php endforeach; ?>
										</tr>
										<tr>
											<th class="text-center">T. Days</th>
											<th class="text-center">Attend.</th>
											<th class="text-center">Dress</th>
											<th class="text-center">Conduct</th>
											<?php foreach ($subjects_for_grid as $sg): ?>
											<th class="text-center">Fair</th>
											<th class="text-center">Writing</th>
											<th class="text-center">Learning</th>
											<?php endforeach; ?>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($report_rows)): ?>
										<tr>
											<td colspan="<?= (int) $swr_colspan_total ?>" class="text-center text-muted">
												<?= translate('select') ?> <?= translate('class') ?>, <?= translate('section') ?>, <?= translate('student') ?> and date range.
											</td>
										</tr>
										<?php else: ?>
										<?php foreach ($report_rows as $row):
											$attVal = isset($row['attendance']) ? (string) $row['attendance'] : '';
											$present = ($attVal !== '' && $attVal !== 'A');
											$dressVal = isset($row['dress']) ? (string) $row['dress'] : '';
											$conductVal = isset($row['conduct']) ? (string) $row['conduct'] : '';
											$subj_map = isset($row['subjects']) && is_array($row['subjects']) ? $row['subjects'] : array();
										?>
										<tr>
											<td class="text-center"><?= (int) $row['sr'] ?></td>
											<td class="text-center"><?= html_escape($row['date_disp']) ?></td>
											<td class="text-center"><?= html_escape($row['t_days']) ?></td>
											<td class="text-center <?= $cellClass('attendance', $attVal) ?>"><?= html_escape($attVal !== '' ? $attVal : '—') ?></td>
											<td class="text-center <?= $cellClass('mark', $dressVal, $present) ?>"><?= html_escape($dressVal !== '' ? $dressVal : '—') ?></td>
											<td class="text-center <?= $cellClass('mark', $conductVal, $present) ?>"><?= html_escape($conductVal !== '' ? $conductVal : '—') ?></td>
											<?php foreach ($subjects_for_grid as $sg):
												$sid = (int) $sg['subject_id'];
												$sc = isset($subj_map[$sid]) ? $subj_map[$sid] : array('fair' => '', 'writing' => '', 'learn' => '');
											?>
											<td class="text-center <?= $cellClass('mark', $sc['fair'], $present) ?>"><?= !empty($sc['fair']) ? html_escape($sc['fair']) : '—' ?></td>
											<td class="text-center <?= $cellClass('mark', $sc['writing'], $present) ?>"><?= !empty($sc['writing']) ? html_escape($sc['writing']) : '—' ?></td>
											<td class="text-center <?= $cellClass('mark', $sc['learn'], $present) ?>"><?= !empty($sc['learn']) ? html_escape($sc['learn']) : '—' ?></td>
											<?php endforeach; ?>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
									<?php if (!empty($grid_totals) && !empty($report_rows)): ?>
									<tfoot>
										<tr class="active">
											<th colspan="2" class="text-center">Total</th>
											<th class="text-center"><?= (int) $summary['school_open_days'] ?></th>
											<th class="text-center"><?= (int) $grid_totals['present'] ?>P / <?= (int) $grid_totals['absent'] ?>A</th>
											<th class="text-center"><?= (int) $grid_totals['without_dress'] ?></th>
											<th class="text-center"><?= (int) $grid_totals['bad_conduct'] ?></th>
											<?php foreach ($subjects_for_grid as $sg):
												$sid = (int) $sg['subject_id'];
												$gt = isset($grid_totals['subjects'][$sid]) ? $grid_totals['subjects'][$sid] : array('fair' => 0, 'writing' => 0, 'learning' => 0);
											?>
											<th class="text-center"><?= (int) $gt['fair'] ?></th>
											<th class="text-center"><?= (int) $gt['writing'] ?></th>
											<th class="text-center"><?= (int) $gt['learning'] ?></th>
											<?php endforeach; ?>
										</tr>
									</tfoot>
									<?php endif; ?>
								</table>
							</div>
						</div>
					</section>

					<?php if ($student_valid): ?>
					<div class="row">
						<div class="col-md-4">
							<section class="panel panel-featured-left panel-featured-primary">
								<div class="panel-body">
									<h5 class="mt-none mb-sm"><i class="fas fa-clipboard-check"></i> Attendance Summary</h5>
									<table class="table table-condensed table-bordered mb-none">
										<tr><td>School open</td><td class="text-right"><strong><?= (int) $summary['school_open_days'] ?></strong></td></tr>
										<tr><td>Present</td><td class="text-right"><strong><?= (int) $summary['attend_days'] ?></strong></td></tr>
										<tr><td>Absent</td><td class="text-right"><strong><?= (int) $summary['absent_days'] ?></strong></td></tr>
										<tr><td>Attendance %</td><td class="text-right"><span class="label <?= $attLabelClass ?>"><?= $attPct ?>%</span></td></tr>
									</table>
									<?php if ((int) $summary['school_open_days'] > 0): ?>
									<div class="progress progress-sm light m-md mb-none">
										<div class="progress-bar <?= $attPct >= 85 ? 'progress-bar-success' : ($attPct >= 70 ? 'progress-bar-warning' : 'progress-bar-danger') ?>" role="progressbar" style="width:<?= max(4, $attPct) ?>%;"><?= $attPct ?>%</div>
									</div>
									<?php endif; ?>
								</div>
							</section>
						</div>
						<div class="col-md-4">
							<section class="panel panel-featured-left panel-featured-warning">
								<div class="panel-body">
									<h5 class="mt-none mb-sm"><i class="fas fa-user-shield"></i> Discipline Summary</h5>
									<table class="table table-condensed table-bordered mb-none">
										<tr>
											<td>Without Dress</td>
											<td class="text-right"><span class="label <?= (int) $summary['without_dress'] > 0 ? 'label-danger' : 'label-success' ?>"><?= (int) $summary['without_dress'] ?></span></td>
										</tr>
										<tr>
											<td>Bad Conduct Found</td>
											<td class="text-right"><span class="label <?= (int) $summary['bad_conduct'] > 0 ? 'label-danger' : 'label-success' ?>"><?= (int) $summary['bad_conduct'] ?></span></td>
										</tr>
									</table>
								</div>
							</section>
						</div>
						<div class="col-md-4">
							<section class="panel panel-featured-left panel-featured-success">
								<div class="panel-body">
									<h5 class="mt-none mb-sm"><i class="fas fa-award"></i> Grade</h5>
									<p class="text-center mb-sm">
										<span class="label <?= $gradeLabelClass ?>"><?= html_escape($summary['grade']) ?></span>
										· Avg <?= (int) $summary['avg_score'] ?><?= !empty($summary['school_open_days']) ? '/' . (int) $summary['school_open_days'] : '' ?>
									</p>
									<small class="text-muted">A ≥ 90% · B ≥ 80% · C ≥ 66% · D &lt; 66% of school open days</small>
								</div>
							</section>
						</div>
					</div>

					<section class="panel panel-featured-left panel-featured-danger mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-tasks"></i> Work Summary</h4>
						</header>
						<div class="panel-body">
							<?php $ss = !empty($summary['subject_scores']) ? $summary['subject_scores'] : array(); ?>
							<?php if (empty($ss)): ?>
							<div class="alert alert-warning mb-none">Work summary data is not available.</div>
							<?php else: ?>
							<table class="table table-bordered table-condensed mb-none">
								<thead>
									<tr>
										<th>Subject</th>
										<th class="text-center">Score</th>
										<th class="text-center">%</th>
										<th class="text-center">Fair / Writing / Learning</th>
										<th>Progress</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($ss as $sc):
										$pct = isset($sc['percent']) ? (int) $sc['percent'] : 0;
										if ($pct >= 80) {
											$scLabel = 'label-success';
											$barClass = 'progress-bar-success';
										} elseif ($pct >= 66) {
											$scLabel = 'label-warning';
											$barClass = 'progress-bar-warning';
										} else {
											$scLabel = 'label-danger';
											$barClass = 'progress-bar-danger';
										}
									?>
									<tr>
										<td><?= html_escape($sc['label']) ?></td>
										<td class="text-center"><span class="label <?= $scLabel ?>"><?= html_escape($sc['text']) ?></span></td>
										<td class="text-center"><?= $pct ?>%</td>
										<td class="text-center">
											F: <?= isset($sc['fair']) ? (int) $sc['fair'] : 0 ?>
											· W: <?= isset($sc['writing']) ? (int) $sc['writing'] : 0 ?>
											· L: <?= isset($sc['learning']) ? (int) $sc['learning'] : 0 ?>
										</td>
										<td>
											<div class="progress progress-sm light mb-none">
												<div class="progress-bar <?= $barClass ?>" role="progressbar" style="width:<?= max(4, min(100, $pct)) ?>%;"></div>
											</div>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<p class="text-muted mb-none mt-sm">
								Each subject score is out of 50 (Fair + Writing + Learning on school open days).
								Holidays and weekly offs are excluded.
							</p>
							<?php endif; ?>
						</div>
					</section>
					<?php elseif ($filter_class_id && $filter_section_id && !$filter_student_id): ?>
					<div class="alert alert-info no-print">
						<i class="fas fa-info-circle"></i>
						<?= translate('select') ?> <?= translate('student') ?> <?= translate('to') ?> load attendance and work data.
					</div>
					<?php elseif ($filter_student_id && !$student_valid): ?>
					<div class="alert alert-danger no-print">Selected student is not in this class/section.</div>
					<?php endif; ?>
				</div>

				<?php if ($canShareActions): ?>
				<div class="well well-sm text-center no-print mt-md mb-none">
					<p class="mb-sm"><strong>Share detailed report</strong></p>
					<?php if ($waHref !== ''): ?>
					<a class="btn btn-success" href="<?= html_escape($waHref) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>
					<?php else: ?>
					<button type="button" class="btn btn-success" disabled title="Parent mobile not available"><i class="fab fa-whatsapp"></i> WhatsApp</button>
					<?php endif; ?>
					<button type="button" class="btn btn-default" onclick="swrEvalExportExcel();"><i class="fas fa-file-excel"></i> Excel</button>
				</div>
				<?php endif; ?>

				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<?php if ($swr_table_ok && (!is_superadmin_loggedin() || !empty($branch_id))): ?>
<script type="text/javascript">
function swrEvalExportExcel() {
	var t = document.getElementById('swr_eval_table');
	if (!t) return;
	var h = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Report</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>' + t.outerHTML + '</body></html>';
	var a = document.createElement('a');
	a.href = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(h);
	a.download = 'student_work_evaluation.xls';
	a.click();
}
function swrEvalReinitSelect2(sel) {
	try { if ($(sel).data('select2')) { $(sel).select2('destroy'); } } catch (e) {}
	$(sel).removeData('__select2');
	$(sel).themePluginSelect2({});
}
function swrEvalStudentPlaceholder(mode) {
	if (mode === 'class') return '<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>';
	if (mode === 'section') return '<option value=""><?= htmlspecialchars(translate('select') . ' ' . translate('section'), ENT_QUOTES, 'UTF-8') ?></option>';
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
			if (selectedStudentId) $('#frm_swr_eval #student_id').val(String(selectedStudentId));
			swrEvalReinitSelect2('#frm_swr_eval #student_id');
		}
	});
}
$(document).ready(function () {
	var $form = $('#frm_swr_eval');
	if (!$form.length) return;
	var preselectedStudent = <?= (int) $filter_student_id ?>;
	if ($('#frm_swr_eval #class_id').val() && $('#frm_swr_eval #section_id').val()) {
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
	$form.on('change', '#section_id', function () { swrEvalLoadStudentsForEval(0); });
});
$(document).ajaxComplete(function (event, xhr, settings) {
	if (!settings.url || !$('#frm_swr_eval').length) return;
	if (settings.url.indexOf('ajax/getSectionByClass') === -1) return;
	swrEvalReinitSelect2('#frm_swr_eval #section_id');
	$('#frm_swr_eval #student_id').html(swrEvalStudentPlaceholder('section'));
	swrEvalReinitSelect2('#frm_swr_eval #student_id');
});
</script>
<?php endif; ?>
