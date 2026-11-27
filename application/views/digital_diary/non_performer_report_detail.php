<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$from_date = isset($from_date) ? $from_date : date('Y-m-d');
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$class_id = isset($class_id) ? (int) $class_id : 0;
$subject_id = isset($subject_id) ? (int) $subject_id : 0;
$scope_type = isset($scope_type) ? (string) $scope_type : '';
$class_name = isset($class_name) ? $class_name : '';
$subject_name = isset($subject_name) ? $subject_name : '';
$subject_teacher_names = isset($subject_teacher_names) ? $subject_teacher_names : array();
$np_detail_rows = isset($np_detail_rows) ? $np_detail_rows : array();
$back_q = array('branch_id' => $branch_id, 'from' => $from_date, 'to' => $to_date);
$back_url = base_url('digital_diary/non_performer_report') . '?' . http_build_query($back_q);
$fromDisp = strtotime($from_date) ? date('d M Y', strtotime($from_date)) : $from_date;
$toDisp = strtotime($to_date) ? date('d M Y', strtotime($to_date)) : $to_date;
$statusLabel = function ($v) {
	$v = trim((string) $v);
	if (strcasecmp($v, 'Completed') === 0) {
		return 'label-success';
	}
	if (strcasecmp($v, 'Uncompleted') === 0) {
		return 'label-danger';
	}
	return 'label-default';
};
$totalUncomplete = 0;
foreach ($np_detail_rows as $drChk) {
	if ($drChk['fair_notebook'] === 'Uncompleted' || $drChk['written_work'] === 'Uncompleted' || $drChk['learning_work'] === 'Uncompleted') {
		$totalUncomplete++;
	}
}
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-user-times"></i> Non Performer Report</h4>
				<div class="panel-btn">
					<a href="<?= html_escape($back_url) ?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<div class="text-center mb-md">
					<h3 class="mt-none mb-xs">Non Performer — <?= html_escape($class_name !== '' ? $class_name : '—') ?> / <?= html_escape($subject_name !== '' ? $subject_name : '—') ?></h3>
					<p class="mb-none">
						<span class="label label-primary"><?= html_escape($fromDisp) ?><?= $from_date !== $to_date ? ' to ' . html_escape($toDisp) : '' ?></span>
						<?php if ($scope_type === 'class_teacher'): ?><span class="label label-success">Class teacher · all subjects</span><?php elseif ($scope_type === 'assigned'): ?><span class="label label-info">My allocated subject</span><?php endif; ?>
						<span class="label label-danger"><?= count($np_detail_rows) ?> student(s)</span>
					</p>
					<?php if (!empty($subject_teacher_names)): ?>
					<p class="text-muted mt-sm mb-none"><strong>Subject teacher(s):</strong> <?= html_escape(implode(', ', $subject_teacher_names)) ?></p>
					<?php endif; ?>
				</div>

				<div class="row">
					<div class="col-md-4">
						<section class="panel panel-featured-left panel-featured-danger">
							<div class="panel-body">
								<small class="text-muted">Students listed</small>
								<h3 class="mt-none mb-none"><?= count($np_detail_rows) ?></h3>
							</div>
						</section>
					</div>
					<div class="col-md-4">
						<section class="panel panel-featured-left panel-featured-warning">
							<div class="panel-body">
								<small class="text-muted">With incomplete work</small>
								<h3 class="mt-none mb-none"><?= (int) $totalUncomplete ?></h3>
							</div>
						</section>
					</div>
					<div class="col-md-4">
						<section class="panel panel-featured-left panel-featured-info">
							<div class="panel-body">
								<small class="text-muted">Subject</small>
								<h4 class="mt-none mb-none"><?= html_escape($subject_name) ?></h4>
							</div>
						</section>
					</div>
				</div>

				<section class="panel panel-featured-left panel-featured-danger mb-md">
					<header class="panel-heading">
						<h4 class="panel-title"><i class="fas fa-list"></i> Student List</h4>
					</header>
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-bordered table-condensed table-hover mb-none" id="np_detail_table">
								<thead>
									<tr>
										<th class="text-center">#</th>
										<th>Adm number</th>
										<th class="text-center">Roll No.</th>
										<th class="text-left">Student's Name</th>
										<th class="text-left">Section</th>
										<th class="text-center">Fair Notebook</th>
										<th class="text-center">Written Work</th>
										<th class="text-center">Learning Work</th>
										<th class="text-left">Remark</th>
										<th class="text-center no-print">Parent</th>
									</tr>
								</thead>
								<tbody>
									<?php if (empty($np_detail_rows)): ?>
									<tr>
										<td colspan="10" class="text-center text-muted"><?= translate('no_information_available') ?></td>
									</tr>
									<?php else: ?>
									<?php $adm = 1; foreach ($np_detail_rows as $dr):
										$rollDisp = isset($dr['roll']) && $dr['roll'] !== '' && $dr['roll'] !== null
											? str_pad((string) (int) $dr['roll'], 2, '0', STR_PAD_LEFT)
											: '—';
										$admNo = !empty($dr['register_no']) ? $dr['register_no'] : (string) $adm;
										$parentMobile = preg_replace('/\D+/', '', (string) $dr['parent_mobile']);
										if (strlen($parentMobile) === 11 && substr($parentMobile, 0, 1) === '0') {
											$parentMobile = substr($parentMobile, 1);
										}
										if (strlen($parentMobile) === 10) {
											$parentMobile = '91' . $parentMobile;
										}
										$waMessage = 'Namaste, this is ' . ($branch_name !== '' ? $branch_name : 'the school') . '. We would like to discuss ' . $dr['fullname'] . '\'s schoolwork in ' . $subject_name . ' for ' . $fromDisp . ($from_date !== $to_date ? ' to ' . $toDisp : '') . '. Please support the student in completing the pending work and contact the school if you need help.';
									?>
									<tr>
										<td class="text-center"><?= (int) $adm ?></td>
										<td><?= html_escape($admNo) ?></td>
										<td class="text-center"><?= html_escape($rollDisp) ?></td>
										<td class="text-left"><strong><?= html_escape($dr['fullname']) ?></strong></td>
										<td><?= html_escape($dr['section_name'] !== '' ? $dr['section_name'] : '—') ?></td>
										<td class="text-center"><span class="label <?= $statusLabel($dr['fair_notebook']) ?>"><?= html_escape($dr['fair_notebook']) ?></span></td>
										<td class="text-center"><span class="label <?= $statusLabel($dr['written_work']) ?>"><?= html_escape($dr['written_work']) ?></span></td>
										<td class="text-center"><span class="label <?= $statusLabel($dr['learning_work']) ?>"><?= html_escape($dr['learning_work']) ?></span></td>
										<td class="text-left"><?= html_escape($dr['remarks'] !== '' ? $dr['remarks'] : '—') ?></td>
										<td class="text-center no-print">
											<?php if (strlen($parentMobile) >= 10 && strlen($parentMobile) <= 15): ?>
											<a class="btn btn-success btn-xs" href="https://wa.me/<?= html_escape($parentMobile) ?>?text=<?= rawurlencode($waMessage) ?>" target="_blank" rel="noopener" title="Open a pre-filled message to this parent"><i class="fab fa-whatsapp"></i> WhatsApp</a>
											<?php else: ?>
											<button type="button" class="btn btn-default btn-xs" disabled title="Parent mobile number is not available"><i class="fab fa-whatsapp"></i> Unavailable</button>
											<?php endif; ?>
										</td>
									</tr>
									<?php $adm++; endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>
				</section>

				<div class="well well-sm text-center no-print mb-none">
					<strong>Export</strong>
					<button type="button" class="btn btn-success" onclick="npDetailExportExcel();"><i class="fas fa-file-excel"></i> Excel</button>
				</div>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
function npDetailExportExcel() {
	var t = document.getElementById('np_detail_table');
	if (!t) return;
	var clone = t.cloneNode(true);
	var hide = clone.querySelectorAll('.no-print');
	for (var i = 0; i < hide.length; i++) {
		if (hide[i].parentNode) hide[i].parentNode.removeChild(hide[i]);
	}
	var h = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Detail</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>' + clone.outerHTML + '</body></html>';
	var a = document.createElement('a');
	a.href = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(h);
	a.download = 'non_performer_detail.xls';
	a.click();
}
</script>
