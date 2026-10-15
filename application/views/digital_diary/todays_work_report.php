<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$today_date = isset($today_date) && !empty($today_date) ? $today_date : date('Y-m-d');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$class_name = isset($class_name) ? $class_name : '';
$section_name = isset($section_name) ? $section_name : '';
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
<div class="row">
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
				<?php echo form_open(base_url('digital_diary/todays_work_report'), array('method' => 'get', 'class' => 'mb-md no-print')); ?>
				<div class="row">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-3">
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
					<div class="col-md-2">
						<div class="form-group">
							<label class="control-label"><?= translate('date') ?></label>
							<input type="text" class="form-control" name="date" id="date" value="<?= html_escape($today_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
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
							<label class="control-label">&nbsp;</label>
							<button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> <?= translate('search') ?></button>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div id="twr_print_area">
					<div class="text-center mb-md">
						<h3 class="mt-none mb-xs">Homework and Syllabus Report</h3>
						<p class="mb-none">
							<span class="label label-primary"><?= html_escape($dateDisp) ?></span>
							<?php if ($classLabel !== ''): ?>
							<span class="label label-info"><?= html_escape($classLabel) ?></span>
							<?php endif; ?>
							<?php if ($branch_name !== ''): ?>
							<span class="label label-default"><?= html_escape($branch_name) ?></span>
							<?php endif; ?>
						</p>
					</div>

					<?php if (!empty($filter_class_id) && !empty($filter_section_id)): ?>
					<div class="row">
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-primary">
								<div class="panel-body">
									<small class="text-muted">Subjects</small>
									<h3 class="mt-none mb-none"><?= count($report_rows) ?></h3>
								</div>
							</section>
						</div>
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-success">
								<div class="panel-body">
									<small class="text-muted">Avg Syllabus %</small>
									<h3 class="mt-none mb-none"><?= $avgSyl !== null ? (int) $avgSyl . '%' : '—' ?></h3>
								</div>
							</section>
						</div>
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-warning">
								<div class="panel-body">
									<small class="text-muted">Avg Student Work %</small>
									<h3 class="mt-none mb-none"><?= $avgWork !== null ? (int) $avgWork . '%' : '—' ?></h3>
								</div>
							</section>
						</div>
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-info">
								<div class="panel-body">
									<small class="text-muted">Class</small>
									<h4 class="mt-none mb-none"><?= $classLabel !== '' ? html_escape($classLabel) : '—' ?></h4>
								</div>
							</section>
						</div>
					</div>
					<?php endif; ?>

					<section class="panel panel-featured-left panel-featured-primary mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-table"></i> Subject Work Status</h4>
						</header>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-hover mb-none" id="twr_report_table">
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
											<td colspan="7" class="text-center text-muted"><?= translate('select') ?> <?= translate('class') ?> &amp; <?= translate('section') ?> to continue.</td>
										</tr>
										<?php elseif (empty($report_rows)): ?>
										<tr>
											<td colspan="7" class="text-center text-muted">No data found for selected filters.</td>
										</tr>
										<?php else: ?>
										<?php foreach ($report_rows as $row):
											$syl = isset($row['syllabus_percentage']) && $row['syllabus_percentage'] !== '' ? $row['syllabus_percentage'] : '—';
											$work = isset($row['student_work_percentage']) && $row['student_work_percentage'] !== '' ? $row['student_work_percentage'] : '—';
											$sylDisp = ($syl !== '—' && strpos((string) $syl, '%') === false) ? ((int) $syl . '%') : $syl;
											$workDisp = ($work !== '—' && strpos((string) $work, '%') === false) ? ((int) $work . '%') : $work;
										?>
										<tr>
											<td class="text-left"><strong><?= !empty($row['subject_name']) ? html_escape($row['subject_name']) : '—' ?></strong></td>
											<td class="text-center"><strong><?= isset($row['total_lesson']) ? (int) $row['total_lesson'] : 0 ?></strong></td>
											<td class="text-left"><?= !empty($row['now_going_on']) ? nl2br(html_escape($row['now_going_on'])) : '—' ?></td>
											<td class="text-left"><?= !empty($row['class_work']) ? nl2br(html_escape($row['class_work'])) : '—' ?></td>
											<td class="text-left"><?= !empty($row['home_work']) ? nl2br(html_escape($row['home_work'])) : '—' ?></td>
											<td class="text-center"><span class="label <?= $pctLabel($syl) ?>"><?= html_escape($sylDisp) ?></span></td>
											<td class="text-center"><span class="label <?= $pctLabel($work) ?>"><?= html_escape($workDisp) ?></span></td>
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
				<div class="alert alert-info no-print">
					After clicking <strong>Go for all students work report</strong>, the next window shows the All Student Work Report for this class.
				</div>
				<div class="well well-sm text-center no-print mb-none">
					<strong>Export / Next</strong>
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
