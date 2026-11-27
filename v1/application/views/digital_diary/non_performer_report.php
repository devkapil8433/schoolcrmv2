<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$from_date = isset($from_date) ? $from_date : date('Y-m-d');
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$subjects = isset($np_subjects) ? $np_subjects : array();
$rows = isset($np_rows) ? $np_rows : array();
$totals = isset($np_totals_per_subject) ? $np_totals_per_subject : array();
$total_students_all = isset($np_total_students_all) ? (int) $np_total_students_all : 0;
$total_non_performers = isset($np_total_non_performers) ? (int) $np_total_non_performers : 0;
$student_list = isset($np_student_list) ? $np_student_list : array();
$colspan = 2 + count($subjects);
$fromDisp = strtotime($from_date) ? date('d M Y', strtotime($from_date)) : $from_date;
$toDisp = strtotime($to_date) ? date('d M Y', strtotime($to_date)) : $to_date;
$classesWithIssues = 0;
foreach ($rows as $rr) {
	if (!empty($rr['non_performer_sum'])) {
		$classesWithIssues++;
	}
}
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-user-times"></i> Non Performer Report</h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/non_performer_report'), array('method' => 'get', 'class' => 'mb-md no-print')); ?>
				<div class="row">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label"><?= translate('branch') ?></label>
							<?php $arrayBranch = $this->app_lib->getSelectList('branch'); ?>
							<?= form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'") ?>
						</div>
					</div>
					<?php endif; ?>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label">From</label>
							<input type="text" class="form-control" name="from" value="<?= html_escape($from_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label">To</label>
							<input type="text" class="form-control" name="to" value="<?= html_escape($to_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label">&nbsp;</label>
							<button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> <?= translate('search') ?></button>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div id="np_print_area">
					<div class="text-center mb-md">
						<h3 class="mt-none mb-xs">Non Performer Report</h3>
						<p class="mb-none">
							<span class="label label-primary"><?= html_escape($fromDisp) ?><?= $from_date !== $to_date ? ' to ' . html_escape($toDisp) : '' ?></span>
							<?php if ($branch_name !== ''): ?>
							<span class="label label-default"><?= html_escape($branch_name) ?></span>
							<?php endif; ?>
						</p>
					</div>

					<div class="row">
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-primary">
								<div class="panel-body">
									<small class="text-muted">Total Students</small>
									<h3 class="mt-none mb-none"><?= (int) $total_students_all ?></h3>
								</div>
							</section>
						</div>
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-danger">
								<div class="panel-body">
									<small class="text-muted">Non-performer counts</small>
									<h3 class="mt-none mb-none"><?= (int) $total_non_performers ?></h3>
									<small class="text-muted">Across class × subject cells</small>
								</div>
							</section>
						</div>
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-warning">
								<div class="panel-body">
									<small class="text-muted">Classes with issues</small>
									<h3 class="mt-none mb-none"><?= (int) $classesWithIssues ?></h3>
								</div>
							</section>
						</div>
						<div class="col-md-3">
							<section class="panel panel-featured-left panel-featured-info">
								<div class="panel-body">
									<small class="text-muted">Subjects tracked</small>
									<h3 class="mt-none mb-none"><?= count($subjects) ?></h3>
								</div>
							</section>
						</div>
					</div>

					<section class="panel panel-featured-left panel-featured-danger mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-table"></i> Class × Subject Non Performers</h4>
						</header>
						<div class="panel-body">
							<div class="alert alert-info no-print mb-md">
								Click a subject count to open the student list for that class and subject.
							</div>
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-hover mb-none" id="np_report_table">
									<thead>
										<tr>
											<th class="text-left">Class</th>
											<th class="text-center">Total Student</th>
											<?php foreach ($subjects as $s): ?>
											<th class="text-center"><?= html_escape($s['name']) ?></th>
											<?php endforeach; ?>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($rows)): ?>
										<tr>
											<td colspan="<?= (int) $colspan ?>" class="text-center text-muted"><?= translate('no_information_available'); ?></td>
										</tr>
										<?php else: ?>
										<?php foreach ($rows as $r): ?>
										<tr>
											<td class="text-left"><strong><?= html_escape($r['class_name']) ?></strong></td>
											<td class="text-center"><?= (int) $r['total_students'] ?></td>
											<?php foreach ($subjects as $s):
												$sid = (int) $s['id'];
												$val = isset($r['subjects'][$sid]) ? (int) $r['subjects'][$sid] : 0;
												$detail_q = array(
													'branch_id' => $branch_id,
													'from' => $from_date,
													'to' => $to_date,
													'class_id' => (int) $r['class_id'],
													'subject_id' => $sid,
												);
												$detail_url = base_url('digital_diary/non_performer_report_detail') . '?' . http_build_query($detail_q);
											?>
											<td class="text-center <?= $val > 0 ? 'danger' : 'active' ?>">
												<?php if ($val > 0): ?>
												<a href="<?= html_escape($detail_url) ?>" class="label label-danger"><?= $val ?></a>
												<?php else: ?>
												<span class="text-muted">0</span>
												<?php endif; ?>
											</td>
											<?php endforeach; ?>
										</tr>
										<?php endforeach; ?>
										<tr class="active">
											<th class="text-left">Total</th>
											<th class="text-center"><?= (int) $total_students_all ?></th>
											<?php foreach ($subjects as $s):
												$sid = (int) $s['id'];
												$v = isset($totals[$sid]) ? (int) $totals[$sid] : 0;
											?>
											<th class="text-center">
												<?php if ($v > 0): ?>
												<span class="label label-danger"><?= $v ?></span>
												<?php else: ?>
												0
												<?php endif; ?>
											</th>
											<?php endforeach; ?>
										</tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					</section>

					<section class="panel panel-featured-left panel-featured-warning mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-user-graduate"></i> Non Performer Students <span class="label label-warning"><?= count($student_list) ?></span></h4>
						</header>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-hover mb-none" id="np_student_table">
									<thead>
										<tr>
											<th class="text-left">Class</th>
											<th class="text-center" width="50">#</th>
											<th class="text-left">Subject</th>
											<th class="text-center">Roll No.</th>
											<th>Adm number</th>
											<th class="text-left">Student's Name</th>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($student_list)): ?>
										<tr>
											<td colspan="6" class="text-center text-muted"><?= translate('no_information_available'); ?></td>
										</tr>
										<?php else: ?>
										<?php
										$classRowSpans = array();
										foreach ($student_list as $idx => $stCount) {
											$cid = isset($stCount['class_id']) ? (int) $stCount['class_id'] : 0;
											if (!isset($classRowSpans[$cid])) {
												$classRowSpans[$cid] = 0;
											}
											$classRowSpans[$cid]++;
										}
										$sn = 1;
										$prevClassId = null;
										foreach ($student_list as $st):
											$rollDisp = isset($st['roll']) && $st['roll'] !== '' && $st['roll'] !== null
												? str_pad((string) (int) $st['roll'], 2, '0', STR_PAD_LEFT)
												: '—';
											$admNo = !empty($st['register_no']) ? $st['register_no'] : '—';
											$name = !empty($st['fullname']) ? $st['fullname'] : '—';
											$classId = isset($st['class_id']) ? (int) $st['class_id'] : 0;
											$showClass = ($prevClassId === null || $classId !== $prevClassId);
											$prevClassId = $classId;
											$span = isset($classRowSpans[$classId]) ? (int) $classRowSpans[$classId] : 1;
										?>
										<tr>
											<?php if ($showClass): ?>
											<td class="text-left" rowspan="<?= $span ?>" style="vertical-align:middle"><strong><?= html_escape($st['class_name']) ?></strong></td>
											<?php endif; ?>
											<td class="text-center" style="vertical-align:middle"><?= (int) $sn ?></td>
											<td class="text-left" style="vertical-align:middle"><?= html_escape($st['subject_name']) ?></td>
											<td class="text-center" style="vertical-align:middle"><?= html_escape($rollDisp) ?></td>
											<td style="vertical-align:middle"><?= html_escape($admNo) ?></td>
											<td class="text-left" style="vertical-align:middle"><strong><?= html_escape($name) ?></strong></td>
										</tr>
										<?php $sn++; endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					</section>
				</div>

				<div class="well well-sm text-center no-print mb-none">
					<strong>Export</strong>
					<button type="button" class="btn btn-success" onclick="npExportExcel();"><i class="fas fa-file-excel"></i> Excel</button>
				</div>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
function npExportExcel() {
	var t1 = document.getElementById('np_report_table');
	var t2 = document.getElementById('np_student_table');
	if (!t1 && !t2) return;
	var body = '';
	if (t1) body += '<h3>Class × Subject</h3>' + t1.outerHTML;
	if (t2) body += '<h3>Student Names</h3>' + t2.outerHTML;
	var h = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>NonPerformer</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>' + body + '</body></html>';
	var a = document.createElement('a');
	a.href = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(h);
	a.download = 'non_performer_report.xls';
	a.click();
}
</script>
