<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$today_date = isset($today_date) && !empty($today_date) ? $today_date : date('Y-m-d');
$class_rows = isset($class_rows) ? $class_rows : array();
$teacher_rows = isset($teacher_rows) ? $teacher_rows : array();
$subject_rows = isset($subject_rows) ? $subject_rows : array();
$winner_class_name = isset($winner_class_name) ? $winner_class_name : '';
$winner_teacher_name = isset($winner_teacher_name) ? $winner_teacher_name : '';
$winner_subject_name = isset($winner_subject_name) ? $winner_subject_name : '';
$syllabus_mode = isset($syllabus_mode) ? $syllabus_mode : 'class';
$teacher_filter = isset($teacher_filter) ? $teacher_filter : '';
$dateDisp = strtotime($today_date) ? date('d M Y', strtotime($today_date)) : $today_date;

if ($syllabus_mode === 'teacher') {
	$modeTitle = 'Teacher wise';
	$rowsCount = count($teacher_rows);
	$winnerLabel = $winner_teacher_name;
	$winnerHeading = 'Today Winner Teacher';
} elseif ($syllabus_mode === 'subject') {
	$modeTitle = 'Subject wise';
	$rowsCount = count($subject_rows);
	$winnerLabel = $winner_subject_name;
	$winnerHeading = 'Today Winner Subject';
} else {
	$modeTitle = 'Class wise';
	$rowsCount = count($class_rows);
	$winnerLabel = $winner_class_name;
	$winnerHeading = 'Today Winner Class';
}

$pctLabel = function ($v) {
	$v = (int) $v;
	if ($v >= 70) {
		return 'label-success';
	}
	if ($v >= 40) {
		return 'label-warning';
	}
	return 'label-danger';
};
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-book-open"></i> Syllabus Report — <?= html_escape($modeTitle) ?></h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/syllabus_report'), array('method' => 'get', 'class' => 'mb-md no-print')); ?>
				<div class="row">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label"><?= translate('branch') ?></label>
							<?php $arrayBranch = $this->app_lib->getSelectList('branch'); ?>
							<?= form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'") ?>
						</div>
					</div>
					<?php else: ?>
					<input type="hidden" name="branch_id" value="<?= html_escape($branch_id) ?>">
					<?php endif; ?>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label"><?= translate('date') ?></label>
							<input type="text" class="form-control" name="date" value="<?= html_escape($today_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label">Report wise <span class="required">*</span></label>
							<?php
								$modeOptions = array(
									'class' => 'Class wise',
									'teacher' => 'Teacher wise',
									'subject' => 'Subject wise',
								);
								echo form_dropdown('mode', $modeOptions, $syllabus_mode, "class='form-control' id='syllabus_mode' data-plugin-selectTwo data-width='100%' required");
							?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label">&nbsp;</label>
							<button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> <?= translate('filter') ?></button>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>

				<ul class="nav nav-pills mb-md no-print">
					<li class="<?= $syllabus_mode === 'class' ? 'active' : '' ?>">
						<a href="<?= base_url('digital_diary/syllabus_report') . '?' . http_build_query(array('mode' => 'class', 'branch_id' => $branch_id, 'date' => $today_date)) ?>">Class wise</a>
					</li>
					<li class="<?= $syllabus_mode === 'teacher' ? 'active' : '' ?>">
						<a href="<?= base_url('digital_diary/syllabus_report') . '?' . http_build_query(array('mode' => 'teacher', 'branch_id' => $branch_id, 'date' => $today_date)) ?>">Teacher wise</a>
					</li>
					<li class="<?= $syllabus_mode === 'subject' ? 'active' : '' ?>">
						<a href="<?= base_url('digital_diary/syllabus_report') . '?' . http_build_query(array('mode' => 'subject', 'branch_id' => $branch_id, 'date' => $today_date)) ?>">Subject wise</a>
					</li>
				</ul>

				<div id="syl_print_area">
					<div class="text-center mb-md">
						<h3 class="mt-none mb-xs">Syllabus Report — <?= html_escape($modeTitle) ?></h3>
						<p class="mb-none">
							<span class="label label-primary"><?= html_escape($dateDisp) ?></span>
							<?php if ($branch_name !== ''): ?>
							<span class="label label-default"><?= html_escape($branch_name) ?></span>
							<?php endif; ?>
						</p>
					</div>

					<div class="row">
						<div class="col-md-4">
							<section class="panel panel-featured-left panel-featured-primary">
								<div class="panel-body">
									<small class="text-muted">Rows</small>
									<h3 class="mt-none mb-none"><?= (int) $rowsCount ?></h3>
								</div>
							</section>
						</div>
						<div class="col-md-4">
							<section class="panel panel-featured-left panel-featured-success">
								<div class="panel-body">
									<small class="text-muted"><?= html_escape($winnerHeading) ?></small>
									<h4 class="mt-none mb-none"><?= !empty($winnerLabel) ? html_escape($winnerLabel) : '—' ?></h4>
								</div>
							</section>
						</div>
						<div class="col-md-4">
							<section class="panel panel-featured-left panel-featured-info">
								<div class="panel-body">
									<small class="text-muted">Mode</small>
									<h4 class="mt-none mb-none"><?= html_escape($modeTitle) ?></h4>
								</div>
							</section>
						</div>
					</div>

					<section class="panel panel-featured-left panel-featured-primary mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-table"></i> <?= html_escape($modeTitle) ?> Ranking</h4>
						</header>
						<div class="panel-body">
							<div class="table-responsive">
								<?php if ($syllabus_mode === 'teacher'): ?>
								<table class="table table-bordered table-condensed table-hover mb-none" id="syl_report_table">
									<thead>
										<tr>
											<th class="text-left">Teacher</th>
											<th class="text-left">Classes</th>
											<th class="text-center">Assignment %</th>
											<th class="text-center">Work %</th>
											<th class="text-center">Syllabus %</th>
											<th class="text-center">Overall %</th>
											<th class="text-center">Rank</th>
											<th class="text-center no-print">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($teacher_rows)): ?>
										<tr>
											<td colspan="8" class="text-muted text-center"><?= translate('no_information_available'); ?></td>
										</tr>
										<?php else: ?>
										<?php foreach ($teacher_rows as $row): ?>
										<tr class="<?= (int) $row['rank'] === 1 ? 'success' : '' ?>">
											<td class="text-left"><strong><?= html_escape($row['teacher']) ?></strong></td>
											<td class="text-left"><?= html_escape($row['classes']) ?></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['assign_percent']) ?>"><?= (int) $row['assign_percent'] ?>%</span></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['work_percent']) ?>"><?= (int) $row['work_percent'] ?>%</span></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['syllabus_percent']) ?>"><?= (int) $row['syllabus_percent'] ?>%</span></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['overall_percent']) ?>"><?= (int) $row['overall_percent'] ?>%</span></td>
											<td class="text-center"><strong>#<?= (int) $row['rank'] ?></strong></td>
											<td class="text-center no-print">
												<a href="<?= base_url('digital_diary/syllabus_report_teacher_detail') . '?' . http_build_query(array('branch_id' => $branch_id, 'from' => $today_date, 'to' => $today_date, 'teacher_id' => (int) $row['teacher_id'], 'teacher_name' => $row['teacher'])) ?>" class="btn btn-xs btn-primary">View</a>
											</td>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
								<?php elseif ($syllabus_mode === 'subject'): ?>
								<table class="table table-bordered table-condensed table-hover mb-none" id="syl_report_table">
									<thead>
										<tr>
											<th class="text-left">Class</th>
											<th class="text-left">Subject</th>
											<th class="text-center">Assignment %</th>
											<th class="text-center">Work %</th>
											<th class="text-center">Syllabus %</th>
											<th class="text-center">Overall %</th>
											<th class="text-center">Rank</th>
											<th class="text-center no-print">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($subject_rows)): ?>
										<tr>
											<td colspan="8" class="text-muted text-center"><?= translate('no_information_available'); ?></td>
										</tr>
										<?php else: ?>
										<?php foreach ($subject_rows as $row): ?>
										<tr class="<?= (int) $row['rank'] === 1 ? 'success' : '' ?>">
											<td class="text-left"><?= html_escape(isset($row['class_name']) ? $row['class_name'] : '') ?></td>
											<td class="text-left"><strong><?= html_escape($row['subject_name']) ?></strong></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['assign_percent']) ?>"><?= (int) $row['assign_percent'] ?>%</span></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['work_percent']) ?>"><?= (int) $row['work_percent'] ?>%</span></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['syllabus_percent']) ?>"><?= (int) $row['syllabus_percent'] ?>%</span></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['overall_percent']) ?>"><?= (int) $row['overall_percent'] ?>%</span></td>
											<td class="text-center"><strong>#<?= (int) $row['rank'] ?></strong></td>
											<td class="text-center no-print">
												<a href="<?= base_url('digital_diary/todays_work_report') . '?' . http_build_query(array('branch_id' => $branch_id, 'class_id' => (int) $row['class_id'], 'section_id' => (int) $row['section_id'], 'date' => $today_date)) ?>" class="btn btn-xs btn-default" title="Today's Work"><i class="fas fa-info-circle"></i></a>
											</td>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
								<?php else: ?>
								<table class="table table-bordered table-condensed table-hover mb-none" id="syl_report_table">
									<thead>
										<tr>
											<th class="text-left">Class</th>
											<th class="text-left">Class Teacher</th>
											<th class="text-center">Assignment %</th>
											<th class="text-center">Work %</th>
											<th class="text-center">Syllabus %</th>
											<th class="text-center">Overall %</th>
											<th class="text-center">Rank</th>
											<th class="text-center no-print">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($class_rows)): ?>
										<tr>
											<td colspan="8" class="text-muted text-center"><?= translate('no_information_available'); ?></td>
										</tr>
										<?php else: ?>
										<?php foreach ($class_rows as $row): ?>
										<tr class="<?= (int) $row['rank'] === 1 ? 'success' : '' ?>">
											<td class="text-left"><strong><?= html_escape($row['class_name']) ?></strong></td>
											<td class="text-left"><?= html_escape($row['class_teacher']) ?></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['assign_percent']) ?>"><?= (int) $row['assign_percent'] ?>%</span></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['work_percent']) ?>"><?= (int) $row['work_percent'] ?>%</span></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['syllabus_percent']) ?>"><?= (int) $row['syllabus_percent'] ?>%</span></td>
											<td class="text-center"><span class="label <?= $pctLabel($row['overall_percent']) ?>"><?= (int) $row['overall_percent'] ?>%</span></td>
											<td class="text-center"><strong>#<?= (int) $row['rank'] ?></strong></td>
											<td class="text-center no-print">
												<a href="<?= base_url('digital_diary/syllabus_report_class_detail') . '?' . http_build_query(array('branch_id' => $branch_id, 'from' => $today_date, 'to' => $today_date, 'class_id' => (int) $row['class_id'], 'section_id' => isset($row['section_id']) ? (int) $row['section_id'] : 0)) ?>" class="btn btn-xs btn-primary">View</a>
											</td>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
								<?php endif; ?>
							</div>
						</div>
					</section>

					<div class="alert alert-info mb-none">
						<strong><?= html_escape($winnerHeading) ?>:</strong>
						<?= !empty($winnerLabel) ? html_escape($winnerLabel) : '—' ?>
						<span class="text-muted"> — Click View for detailed homework and syllabus for that class/teacher.</span>
					</div>
				</div>

				<div class="well well-sm text-center no-print mt-md mb-none">
					<strong>Export</strong>
					<button type="button" class="btn btn-success" onclick="sylExportExcel();"><i class="fas fa-file-excel"></i> Excel</button>
				</div>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
function sylExportExcel() {
	var t = document.getElementById('syl_report_table');
	if (!t) return;
	var clone = t.cloneNode(true);
	var hide = clone.querySelectorAll('.no-print');
	for (var i = 0; i < hide.length; i++) {
		if (hide[i].parentNode) hide[i].parentNode.removeChild(hide[i]);
	}
	var h = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Syllabus</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>' + clone.outerHTML + '</body></html>';
	var a = document.createElement('a');
	a.href = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(h);
	a.download = 'syllabus_report_<?= html_escape($syllabus_mode) ?>.xls';
	a.click();
}
</script>
