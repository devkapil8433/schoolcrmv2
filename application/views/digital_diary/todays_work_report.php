<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$today_date = isset($today_date) && !empty($today_date) ? $today_date : date('Y-m-d');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$class_name = isset($class_name) && $class_name !== '' ? $class_name : (!empty($filter_class_id) ? get_type_name_by_id('class', $filter_class_id) : '');
$section_name = isset($section_name) && $section_name !== '' ? $section_name : (!empty($filter_section_id) ? get_type_name_by_id('section', $filter_section_id) : '');
$class_options = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$report_rows = isset($report_rows) ? $report_rows : array();
$dateDisp = strtotime($today_date) ? date('d M Y', strtotime($today_date)) : $today_date;
$classLabel = trim($class_name . ($section_name !== '' ? ' (' . $section_name . ')' : ''));

$swr_q = array(
	'date' => $today_date,
	'class_id' => $filter_class_id,
	'section_id' => $filter_section_id,
);
if (!empty($branch_id)) {
	$swr_q['branch_id'] = $branch_id;
}
$swr_url = base_url('digital_diary/student_work_report_entry') . '?' . http_build_query($swr_q);

$pctLabel = function ($v) {
	if ($v === '' || $v === null || $v === '—') {
		return 'label-default';
	}
	$n = (int) preg_replace('/[^0-9\-]/', '', (string) $v);
	if ($n >= 70) {
		return 'label-success';
	}
	if ($n >= 40) {
		return 'label-warning';
	}
	return 'label-danger';
};
$renderWorkLine = function ($label, $value) {
	$value = trim((string) $value);
	$display = $value === '' || $value === '—'
		? '<span class="text-muted">—</span>'
		: nl2br(html_escape($value));
	return '<div class="twr-cell-line"><span class="twr-cell-label">' . html_escape($label) . '</span><span class="twr-cell-value">' . $display . '</span></div>';
};

$avgSyl = null;
$avgWork = null;
$sylSum = 0;
$sylN = 0;
$workSum = 0;
$workN = 0;
foreach ($report_rows as $rr) {
	if (isset($rr['syllabus_percentage']) && $rr['syllabus_percentage'] !== '' && $rr['syllabus_percentage'] !== '—') {
		$sylSum += (int) $rr['syllabus_percentage'];
		$sylN++;
	}
	if (isset($rr['student_work_percentage']) && $rr['student_work_percentage'] !== '' && $rr['student_work_percentage'] !== '—') {
		$workSum += (int) $rr['student_work_percentage'];
		$workN++;
	}
}
if ($sylN > 0) {
	$avgSyl = (int) round($sylSum / $sylN);
}
if ($workN > 0) {
	$avgWork = (int) round($workSum / $workN);
}
?>
<style>
	.twr-report-page > .col-md-12 > .panel {
		overflow: hidden;
		border: 1px solid #e8edf3;
		border-radius: 14px;
		background: #fff;
		box-shadow: 0 8px 26px rgba(37, 55, 75, .045);
	}
	.twr-report-page > .col-md-12 > .panel > .panel-heading {
		padding: 18px 22px;
		border-top: 0;
		border-bottom: 1px solid #edf1f5;
		background: #fff;
	}
	.twr-report-page > .col-md-12 > .panel > .panel-heading .panel-title {
		color: #26384a;
		font-size: 17px;
		font-weight: 700;
	}
	.twr-report-page > .col-md-12 > .panel > .panel-heading .panel-title i {
		margin-right: 7px;
		color: #6488b5;
	}
	.twr-report-page > .col-md-12 > .panel > .panel-body {
		padding: 22px;
	}
	.twr-report-page .twr-filter-panel {
		margin-bottom: 24px;
		padding: 20px;
		border: 1px solid #e9eef4;
		border-radius: 12px;
		background: #fbfcfe;
	}
	.twr-report-page .twr-filter-heading {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		margin-bottom: 16px;
	}
	.twr-report-page .twr-filter-heading strong,
	.twr-report-page .twr-filter-heading small {
		display: block;
	}
	.twr-report-page .twr-filter-heading strong {
		color: #33475b;
		font-size: 14px;
	}
	.twr-report-page .twr-filter-heading small,
	.twr-report-page .twr-filter-hint {
		margin-top: 3px;
		color: #8995a2;
		font-size: 11px;
	}
	.twr-report-page .twr-filter-panel .form-group {
		margin-bottom: 0;
	}
	.twr-report-page .twr-filter-panel label {
		margin-bottom: 6px;
		color: #536273;
		font-size: 12px;
		font-weight: 600;
	}
	.twr-report-page .twr-filter-panel .form-control,
	.twr-report-page .twr-filter-panel .btn {
		height: 44px;
		border-radius: 7px;
		box-shadow: none;
	}
	.twr-report-page .twr-filter-panel .form-control {
		border-color: #dfe6ee;
		background: #fff;
		color: #34495e;
	}
	.twr-report-page .twr-filter-panel .form-control:focus {
		border-color: #9ab8d9;
		box-shadow: 0 0 0 3px rgba(94, 139, 190, .12);
	}
	.twr-report-page .twr-filter-panel .btn-primary {
		border-color: #527fac;
		background: #527fac;
		color: #fff;
	}
	.twr-report-page .twr-filter-panel .btn-primary:hover,
	.twr-report-page .twr-filter-panel .btn-primary:focus {
		border-color: #426d99;
		background: #426d99;
	}
	.twr-report-page .twr-overview {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		margin: 0 0 22px;
	}
	.twr-report-page .twr-overview h3 {
		margin: 0;
		color: #293d51;
		font-size: 22px;
		font-weight: 700;
	}
	.twr-report-page .twr-overview p {
		margin: 6px 0 0;
		color: #7b8794;
		font-size: 13px;
	}
	.twr-report-page .twr-context {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}
	.twr-report-page .twr-context span {
		padding: 8px 12px;
		border: 1px solid #e9eef4;
		border-radius: 8px;
		background: #f9fbfd;
		color: #657587;
		font-size: 12px;
	}
	.twr-report-page .twr-stat-card {
		display: flex;
		align-items: center;
		min-height: 112px;
		margin-bottom: 18px;
		padding: 18px;
		border: 1px solid #e9eef4;
		border-radius: 11px;
		background: #fbfcfe;
		box-shadow: none;
	}
	.twr-report-page .twr-stat-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex: 0 0 42px;
		width: 42px;
		height: 42px;
		margin-right: 13px;
		border-radius: 11px;
		background: #eef4fa;
		color: #6284aa;
		font-size: 16px;
	}
	.twr-report-page .twr-stat-card.twr-stat-syllabus .twr-stat-icon {
		background: #f3f1fa;
		color: #7d75a5;
	}
	.twr-report-page .twr-stat-card.twr-stat-work .twr-stat-icon {
		background: #edf6f1;
		color: #578b6e;
	}
	.twr-report-page .twr-stat-card.twr-stat-class .twr-stat-icon {
		background: #faf4e9;
		color: #a78653;
	}
	.twr-report-page .twr-stat-copy {
		min-width: 0;
	}
	.twr-report-page .twr-stat-label,
	.twr-report-page .twr-stat-value,
	.twr-report-page .twr-stat-note {
		display: block;
	}
	.twr-report-page .twr-stat-label {
		color: #778697;
		font-size: 12px;
		font-weight: 600;
	}
	.twr-report-page .twr-stat-value {
		margin-top: 4px;
		color: #2d4054;
		font-size: 25px;
		font-weight: 700;
		line-height: 1.15;
	}
	.twr-report-page .twr-stat-value.twr-stat-class-name {
		font-size: 17px;
	}
	.twr-report-page .twr-stat-note {
		margin-top: 4px;
		color: #8995a2;
		font-size: 11px;
	}
	.twr-report-page .twr-status-panel {
		overflow: hidden;
		border: 1px solid #e8edf3;
		border-radius: 12px;
		box-shadow: none;
	}
	.twr-report-page .twr-status-panel > .panel-heading {
		padding: 15px 18px;
		border-bottom: 1px solid #edf1f5;
		background: #fff;
	}
	.twr-report-page .twr-status-panel > .panel-heading .panel-title {
		color: #34495e;
		font-size: 15px;
		font-weight: 700;
	}
	.twr-report-page .twr-status-panel > .panel-body {
		padding: 16px 18px 18px;
	}
	.twr-report-page .twr-table {
		margin-bottom: 0;
		min-width: 1080px;
		border: 0;
	}
	.twr-report-page .twr-table thead th {
		padding: 13px 14px;
		border-top: 0;
		border-bottom: 1px solid #e6ebf1;
		background: #f7f9fc;
		color: #617185;
		font-size: 12px;
		font-weight: 700;
		vertical-align: middle;
		white-space: nowrap;
	}
	.twr-report-page .twr-table tbody td {
		padding: 15px 14px;
		border-top: 1px solid #edf1f5;
		color: #435265;
		vertical-align: middle;
	}
	.twr-report-page .twr-table.table-bordered > thead > tr > th,
	.twr-report-page .twr-table.table-bordered > tbody > tr > td {
		border-right: 0;
		border-left: 0;
	}
	.twr-report-page .twr-table.table-bordered > thead > tr > th {
		border-top: 0;
		border-bottom: 1px solid #e6ebf1;
	}
	.twr-report-page .twr-table.table-bordered > tbody > tr > td {
		border-top: 1px solid #edf1f5;
		border-bottom: 0;
	}
	.twr-report-page .twr-table tbody tr:nth-child(even) {
		background: #fcfdff;
	}
	.twr-report-page .twr-table tbody tr:hover {
		background: #f7faff;
	}
	.twr-report-page .twr-subject-name {
		color: #26384a;
		font-weight: 700;
	}
	.twr-report-page .twr-total-lessons {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 30px;
		height: 28px;
		padding: 0 8px;
		border-radius: 8px;
		background: #f1f4f7;
		color: #526579;
		font-weight: 700;
	}
	.twr-report-page .twr-work-cell {
		min-width: 230px;
		max-width: 370px;
		white-space: normal;
	}
	.twr-report-page .twr-cell-line {
		display: grid;
		grid-template-columns: 70px minmax(0, 1fr);
		gap: 8px;
		padding: 7px 0;
		line-height: 1.5;
	}
	.twr-report-page .twr-cell-line + .twr-cell-line {
		border-top: 1px solid #f0f3f6;
	}
	.twr-report-page .twr-cell-label {
		color: #8a97a6;
		font-size: 10px;
		font-weight: 700;
		letter-spacing: .02em;
		text-transform: uppercase;
	}
	.twr-report-page .twr-cell-value {
		min-width: 0;
		color: #435265;
		overflow-wrap: anywhere;
	}
	.twr-report-page .twr-cell-line:first-child .twr-cell-value {
		color: #34495e;
		font-weight: 600;
	}
	.twr-report-page .twr-score {
		display: inline-block;
		min-width: 48px;
		padding: 6px 10px;
		border: 0;
		border-radius: 14px;
		font-size: 11px;
		font-weight: 700;
	}
	.twr-report-page .twr-score.label-success {
		background: #e8f4ec;
		color: #4a8060;
	}
	.twr-report-page .twr-score.label-warning {
		background: #f8f1df;
		color: #96743c;
	}
	.twr-report-page .twr-score.label-danger {
		background: #f8eaea;
		color: #a65c5c;
	}
	.twr-report-page .twr-score.label-default {
		background: #eff2f5;
		color: #798694;
	}
	.twr-report-page .twr-empty-state {
		padding: 28px 14px !important;
		color: #7b8794 !important;
		text-align: center;
	}
	.twr-report-page .twr-actions {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: center;
		gap: 8px;
		margin-top: 16px;
		padding: 16px;
		border: 1px solid #e9eef4;
		border-radius: 11px;
		background: #fbfcfe;
	}
	.twr-report-page .twr-actions strong {
		margin-right: 4px;
		color: #536273;
	}
	.twr-report-page .twr-actions .btn-success {
		border-color: #d7e9dd;
		background: #eaf5ee;
		color: #4a8060;
	}
	.twr-report-page .twr-actions .btn-success:hover,
	.twr-report-page .twr-actions .btn-success:focus {
		border-color: #c6dfcf;
		background: #e1f0e6;
		color: #3e7052;
	}
	.twr-report-page .twr-actions .btn-primary {
		border-color: #d9e5f1;
		background: #edf3f9;
		color: #52779c;
	}
	.twr-report-page .twr-actions .btn-primary:hover,
	.twr-report-page .twr-actions .btn-primary:focus {
		border-color: #c6d8e9;
		background: #e5eef7;
		color: #426889;
	}
	@media (max-width: 767px) {
		.twr-report-page > .col-md-12 > .panel > .panel-body {
			padding: 14px;
		}
		.twr-report-page .twr-filter-heading {
			align-items: flex-start;
			flex-direction: column;
		}
		.twr-report-page .twr-filter-panel .form-group {
			margin-bottom: 12px;
		}
		.twr-report-page .twr-overview {
			align-items: flex-start;
			flex-direction: column;
		}
		.twr-report-page .twr-stat-card {
			min-height: 90px;
			padding: 13px;
		}
		.twr-report-page .twr-stat-icon {
			flex-basis: 36px;
			width: 36px;
			height: 36px;
			margin-right: 10px;
		}
		.twr-report-page .twr-stat-value {
			font-size: 21px;
		}
	}
</style>

<div class="row twr-report-page">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-clipboard-list"></i> Today's Work Report</h4>
			</header>
			<div class="panel-body">
				<?php if (!$this->db->table_exists('digital_diary_teacher_activity_log') || !$this->db->table_exists('digital_diary_lesson')): ?>
				<div class="alert alert-warning">
					<strong>Setup required.</strong> Run <code>application/sql/digital_diary_teacher_activity_log.sql</code> and <code>application/sql/digital_diary.sql</code>.
				</div>
				<?php else: ?>
				<?php echo form_open(base_url('digital_diary/todays_work_report'), array('method' => 'get', 'class' => 'twr-filter-panel no-print')); ?>
				<div class="twr-filter-heading">
					<div>
						<strong><i class="fas fa-sliders-h"></i> Report filters</strong>
						<small>Select a date, class and section to view the daily report.</small>
					</div>
					<span class="twr-filter-hint"><i class="fas fa-info-circle"></i> Use the same filters to export or open student work details.</span>
				</div>
				<div class="row">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-3 col-sm-6">
						<div class="form-group">
							<label class="control-label"><?= translate('branch') ?></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_id' onchange='getClassByBranch(this.value)' data-plugin-selectTwo data-width='100%'");
							?>
						</div>
					</div>
					<?php else: ?>
					<input type="hidden" name="branch_id" value="<?= html_escape($branch_id) ?>">
					<?php endif; ?>
					<div class="col-md-2 col-sm-6">
						<div class="form-group">
							<label class="control-label"><?= translate('date') ?></label>
							<input type="text" class="form-control" name="date" id="date" value="<?= html_escape($today_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
					</div>
					<div class="col-md-2 col-sm-6">
						<div class="form-group">
							<label class="control-label"><?= translate('class') ?></label>
							<?= form_dropdown('class_id', $class_options, $filter_class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'") ?>
						</div>
					</div>
					<div class="col-md-2 col-sm-6">
						<div class="form-group">
							<label class="control-label"><?= translate('section') ?></label>
							<?= form_dropdown('section_id', $section_options, $filter_section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'") ?>
						</div>
					</div>
					<div class="col-md-2 col-sm-6">
						<div class="form-group">
							<label class="control-label">&nbsp;</label>
							<button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> <?= translate('search') ?></button>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div id="twr_print_area">
					<div class="twr-overview">
						<div>
							<h3>Homework &amp; syllabus overview</h3>
							<p>Daily classwork, homework and progress at a glance.</p>
						</div>
						<div class="twr-context">
							<span><i class="far fa-calendar-alt"></i> <?= html_escape($dateDisp) ?></span>
							<?php if ($classLabel !== ''): ?><span><i class="fas fa-users"></i> <?= html_escape($classLabel) ?></span><?php endif; ?>
							<?php if ($branch_name !== ''): ?><span><i class="fas fa-school"></i> <?= html_escape($branch_name) ?></span><?php endif; ?>
						</div>
					</div>

					<?php if (!empty($filter_class_id) && !empty($filter_section_id)): ?>
					<div class="row">
						<div class="col-xs-6 col-md-3">
							<div class="twr-stat-card">
								<span class="twr-stat-icon"><i class="fas fa-book-open"></i></span>
								<div class="twr-stat-copy">
									<span class="twr-stat-label">Subjects</span>
									<strong class="twr-stat-value"><?= count($report_rows) ?></strong>
									<small class="twr-stat-note">Included in this class report</small>
								</div>
							</div>
						</div>
						<div class="col-xs-6 col-md-3">
							<div class="twr-stat-card twr-stat-syllabus">
								<span class="twr-stat-icon"><i class="fas fa-chart-line"></i></span>
								<div class="twr-stat-copy">
									<span class="twr-stat-label">Average syllabus</span>
									<strong class="twr-stat-value"><?= $avgSyl !== null ? (int) $avgSyl . '%' : '—' ?></strong>
									<small class="twr-stat-note">Completion across available subjects</small>
								</div>
							</div>
						</div>
						<div class="col-xs-6 col-md-3">
							<div class="twr-stat-card twr-stat-work">
								<span class="twr-stat-icon"><i class="fas fa-clipboard-check"></i></span>
								<div class="twr-stat-copy">
									<span class="twr-stat-label">Student work</span>
									<strong class="twr-stat-value"><?= $avgWork !== null ? (int) $avgWork . '%' : '—' ?></strong>
									<small class="twr-stat-note">Average work completion</small>
								</div>
							</div>
						</div>
						<div class="col-xs-6 col-md-3">
							<div class="twr-stat-card twr-stat-class">
								<span class="twr-stat-icon"><i class="fas fa-chalkboard"></i></span>
								<div class="twr-stat-copy">
									<span class="twr-stat-label">Class</span>
									<strong class="twr-stat-value twr-stat-class-name"><?= $classLabel !== '' ? html_escape($classLabel) : '—' ?></strong>
									<small class="twr-stat-note">Selected report scope</small>
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<section class="panel twr-status-panel mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-table"></i> Subject work status <small class="text-muted"><?= !empty($filter_class_id) && !empty($filter_section_id) ? '· ' . count($report_rows) . ' subjects' : '' ?></small></h4>
						</header>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover twr-table" id="twr_report_table">
									<thead>
										<tr>
											<th class="text-left"><?= translate('subject') ?></th>
											<th class="text-center">Total Lesson</th>
											<th class="text-left">Now Going On</th>
											<th class="text-left">Class Work</th>
											<th class="text-left">Home Work</th>
											<th class="text-center">Syllabus %</th>
											<th class="text-center">Student Work %</th>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($filter_class_id) || empty($filter_section_id)): ?>
										<tr>
											<td colspan="7" class="twr-empty-state"><i class="fas fa-filter"></i><br><?= translate('select') ?> <?= translate('class') ?> &amp; <?= translate('section') ?> to view the report.</td>
										</tr>
										<?php elseif (empty($report_rows)): ?>
										<tr>
											<td colspan="7" class="twr-empty-state"><i class="far fa-folder-open"></i><br>No subject work data found for the selected filters.</td>
										</tr>
										<?php else: ?>
										<?php foreach ($report_rows as $row):
											$syl = isset($row['syllabus_percentage']) && $row['syllabus_percentage'] !== '' ? $row['syllabus_percentage'] : '—';
											$work = isset($row['student_work_percentage']) && $row['student_work_percentage'] !== '' ? $row['student_work_percentage'] : '—';
											$sylDisp = ($syl !== '—' && strpos((string) $syl, '%') === false) ? ((int) $syl . '%') : $syl;
											$workDisp = ($work !== '—' && strpos((string) $work, '%') === false) ? ((int) $work . '%') : $work;
										?>
										<tr>
											<td><strong class="twr-subject-name"><?= !empty($row['subject_name']) ? html_escape($row['subject_name']) : '—' ?></strong></td>
											<td class="text-center"><span class="twr-total-lessons"><?= isset($row['total_lesson']) ? (int) $row['total_lesson'] : 0 ?></span></td>
											<td class="twr-work-cell"><?= !empty($row['now_going_on']) ? nl2br(html_escape($row['now_going_on'])) : '<span class="text-muted">—</span>' ?></td>
											<td class="twr-work-cell">
												<div class="twr-work-lines">
													<?= $renderWorkLine('Work / activity', isset($row['class_work_activity']) ? $row['class_work_activity'] : (isset($row['class_work']) ? $row['class_work'] : '—')) ?>
													<?= $renderWorkLine('Notebook', isset($row['class_work_notebook']) ? $row['class_work_notebook'] : '—') ?>
												</div>
											</td>
											<td class="twr-work-cell">
												<div class="twr-work-lines">
													<?= $renderWorkLine('Work / activity', isset($row['home_work_activity']) ? $row['home_work_activity'] : (isset($row['home_work']) ? $row['home_work'] : '—')) ?>
													<?= $renderWorkLine('Notebook', isset($row['home_work_notebook']) ? $row['home_work_notebook'] : '—') ?>
												</div>
											</td>
											<td class="text-center"><span class="twr-score label <?= $pctLabel($syl) ?>"><?= html_escape($sylDisp) ?></span></td>
											<td class="text-center"><span class="twr-score label <?= $pctLabel($work) ?>"><?= html_escape($workDisp) ?></span></td>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					</section>
				</div>

				<?php if (!empty($filter_class_id) && !empty($filter_section_id)): ?>
				<div class="twr-actions no-print">
					<strong>More actions:</strong>
					<button type="button" class="btn btn-success" onclick="twrExportExcel();"><i class="fas fa-file-excel"></i> Excel</button>
					<a class="btn btn-primary" href="<?= html_escape($swr_url) ?>"><i class="fas fa-users"></i> Go for all students work report</a>
				</div>
				<?php endif; ?>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
function twrExportExcel() {
	var t = document.getElementById('twr_report_table');
	if (!t) return;
	var h = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>TodaysWork</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>' + t.outerHTML + '</body></html>';
	var a = document.createElement('a');
	a.href = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(h);
	a.download = 'todays_work_report.xls';
	a.click();
}
</script>
