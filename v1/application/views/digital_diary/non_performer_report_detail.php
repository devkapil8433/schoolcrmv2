<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$from_date = isset($from_date) ? $from_date : date('Y-m-d');
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$class_id = isset($class_id) ? (int) $class_id : 0;
$subject_id = isset($subject_id) ? (int) $subject_id : 0;
$class_name = isset($class_name) ? $class_name : '';
$subject_name = isset($subject_name) ? $subject_name : '';
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
						<span class="label label-danger"><?= count($np_detail_rows) ?> student(s)</span>
					</p>
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
										<th class="text-center no-print" width="40">
											<label class="mb-none"><input type="checkbox" id="np_chk_all" title="Select all"></label>
										</th>
										<th class="text-center">#</th>
										<th>Adm number</th>
										<th class="text-center">Roll No.</th>
										<th class="text-left">Student's Name</th>
										<th class="text-center">Fair Notebook</th>
										<th class="text-center">Written Work</th>
										<th class="text-center">Learning Work</th>
										<th class="text-left">Remark</th>
									</tr>
								</thead>
								<tbody>
									<?php if (empty($np_detail_rows)): ?>
									<tr>
										<td colspan="9" class="text-center text-muted"><?= translate('no_information_available') ?></td>
									</tr>
									<?php else: ?>
									<?php $adm = 1; foreach ($np_detail_rows as $dr):
										$rollDisp = isset($dr['roll']) && $dr['roll'] !== '' && $dr['roll'] !== null
											? str_pad((string) (int) $dr['roll'], 2, '0', STR_PAD_LEFT)
											: '—';
										$admNo = !empty($dr['register_no']) ? $dr['register_no'] : (string) $adm;
										$waLine = $dr['fullname'] . ' — Fair: ' . $dr['fair_notebook'] . ', Writing: ' . $dr['written_work'] . ', Learning: ' . $dr['learning_work'];
										if (!empty($dr['remarks'])) {
											$waLine .= ' (' . $dr['remarks'] . ')';
										}
									?>
									<tr>
										<td class="text-center no-print">
											<input type="checkbox" class="np-row-chk" value="<?= (int) $dr['student_id'] ?>"
												data-name="<?= html_escape($dr['fullname']) ?>"
												data-mobile="<?= html_escape(isset($dr['mobile']) ? $dr['mobile'] : '') ?>"
												data-line="<?= html_escape($waLine) ?>">
										</td>
										<td class="text-center"><?= (int) $adm ?></td>
										<td><?= html_escape($admNo) ?></td>
										<td class="text-center"><?= html_escape($rollDisp) ?></td>
										<td class="text-left"><strong><?= html_escape($dr['fullname']) ?></strong></td>
										<td class="text-center"><span class="label <?= $statusLabel($dr['fair_notebook']) ?>"><?= html_escape($dr['fair_notebook']) ?></span></td>
										<td class="text-center"><span class="label <?= $statusLabel($dr['written_work']) ?>"><?= html_escape($dr['written_work']) ?></span></td>
										<td class="text-center"><span class="label <?= $statusLabel($dr['learning_work']) ?>"><?= html_escape($dr['learning_work']) ?></span></td>
										<td class="text-left"><?= html_escape($dr['remarks'] !== '' ? $dr['remarks'] : '—') ?></td>
									</tr>
									<?php $adm++; endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>
				</section>

				<div class="well well-sm text-center no-print mb-none">
					<strong>Share / Export</strong>
					<button type="button" class="btn btn-success" id="npWaBtn"><i class="fab fa-whatsapp"></i> WhatsApp Selected</button>
					<button type="button" class="btn btn-success" onclick="npDetailExportExcel();"><i class="fas fa-file-excel"></i> Excel</button>
				</div>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
(function () {
	var className = <?= json_encode((string) $class_name) ?>;
	var subjectName = <?= json_encode((string) $subject_name) ?>;
	var period = <?= json_encode($fromDisp . ($from_date !== $to_date ? ' to ' . $toDisp : '')) ?>;
	var school = <?= json_encode((string) $branch_name) ?>;

	$('#np_chk_all').on('change', function () {
		$('.np-row-chk').prop('checked', this.checked);
	});

	$('#npWaBtn').on('click', function () {
		var $checked = $('.np-row-chk:checked');
		if (!$checked.length) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({ icon: 'info', title: 'Select students', text: 'Please select one or more students.', confirmButtonText: 'OK' });
			} else {
				alert('Please select one or more students.');
			}
			return;
		}
		var lines = [];
		lines.push(school || 'School');
		lines.push('NON PERFORMER ALERT');
		lines.push('Class: ' + className + ' · Subject: ' + subjectName);
		lines.push('Period: ' + period);
		lines.push('---');
		var firstMobile = '';
		$checked.each(function () {
			var $el = $(this);
			lines.push('• ' + ($el.data('line') || $el.data('name')));
			if (!firstMobile && $el.data('mobile')) {
				firstMobile = String($el.data('mobile')).replace(/\D+/g, '');
			}
		});
		var text = lines.join('\n');
		if (text.length > 3500) {
			text = text.substring(0, 3400) + '\n…';
		}
		var url = 'https://wa.me/';
		if (firstMobile) {
			if (firstMobile.length === 10) firstMobile = '91' + firstMobile;
			url += firstMobile;
		}
		url += '?text=' + encodeURIComponent(text);
		window.open(url, '_blank');
	});
})();

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
