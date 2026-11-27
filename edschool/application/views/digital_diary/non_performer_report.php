<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$from_date = isset($from_date) ? $from_date : date('Y-m-d');
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$subjects = isset($np_subjects) ? $np_subjects : array();
$rows = isset($np_rows) ? $np_rows : array();
$totals = isset($np_totals_per_subject) ? $np_totals_per_subject : array();
$total_students_all = isset($np_total_students_all) ? (int) $np_total_students_all : 0;
$colspan = 2 + count($subjects);
?>

<div class="row">
	<div class="col-md-12">
		<section class="panel np-report-panel">
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/non_performer_report'), array('method' => 'get', 'class' => 'mb-md')); ?>
				<div class="row mb-md">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-3 col-sm-6">
						<label class="control-label"><?= translate('branch') ?></label>
						<?php $arrayBranch = $this->app_lib->getSelectList('branch'); ?>
						<?= form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<?php endif; ?>
					<div class="col-md-3 col-sm-6">
						<label class="control-label">From</label>
						<input type="text" class="form-control" name="from" value="<?= html_escape($from_date) ?>" placeholder="Date" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
					</div>
					<div class="col-md-3 col-sm-6">
						<label class="control-label">To</label>
						<input type="text" class="form-control" name="to" value="<?= html_escape($to_date) ?>" placeholder="Date" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
					</div>
					<div class="col-md-3 col-sm-6 text-right" style="padding-top:24px;">
						<button type="submit" class="btn btn-primary" style="min-width:110px;"><?= translate('search') ?></button>
					</div>
				</div>
				<?php echo form_close(); ?>

				<h2 class="np-report-title text-center">Non Performer Report</h2>

				<div class="table-responsive np-report-table-wrap">
					<table class="table table-bordered table-condensed text-center np-report-table">
						<thead>
							<tr>
								<th class="text-left np-th-class">Class</th>
								<th class="np-th-total">Total Student</th>
								<?php foreach ($subjects as $s): ?>
								<th class="np-th-subj"><?= html_escape($s['name']) ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($rows)): ?>
							<tr>
								<td colspan="<?= (int) $colspan ?>" class="text-muted text-center">
									<?= translate('no_information_available'); ?>
								</td>
							</tr>
							<?php else: ?>
								<?php foreach ($rows as $r): ?>
								<tr>
									<td class="text-left"><?= html_escape($r['class_name']) ?></td>
									<td><?= (int) $r['total_students'] ?></td>
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
									<td>
										<?php if ($val > 0): ?>
										<a href="<?= html_escape($detail_url) ?>" class="np-cell-link"><?= $val ?></a>
										<?php else: ?>
										<?= $val !== 0 ? $val : '' ?>
										<?php endif; ?>
									</td>
									<?php endforeach; ?>
								</tr>
								<?php endforeach; ?>
							<tr class="np-total-row np-total-row-main">
								<td class="text-left">Total</td>
								<td><?= (int) $total_students_all ?></td>
								<?php foreach ($subjects as $s): ?>
									<?php $sid = (int) $s['id']; $v = isset($totals[$sid]) ? (int) $totals[$sid] : 0; ?>
									<td><?= $v ?></td>
								<?php endforeach; ?>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="np-report-footer clearfix">
					<p class="np-report-hint text-muted pull-left mb-none">
						After Clicking Any Subject and class the next window is on next page....
					</p>
					<div class="pull-right np-report-actions">
						<button type="button" class="btn np-footer-btn" onclick="return false;">Share</button>
						<button type="button" class="btn np-footer-btn" onclick="return false;">PDF</button>
						<button type="button" class="btn np-footer-btn" onclick="return false;">Excel</button>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>

<style type="text/css">
.np-report-panel { border: 1px solid #ccc; }
.np-report-title {
	font-weight: 700;
	color: #c00;
	text-decoration: underline;
	font-size: 1.75em;
	margin: 8px 0 20px;
	letter-spacing: 0.02em;
}
.np-report-table-wrap { margin-bottom: 16px; }
.np-report-table {
	border: 2px solid #111 !important;
	background: #fff;
}
.np-report-table > thead > tr > th,
.np-report-table > tbody > tr > td {
	border: 1px solid #111 !important;
	vertical-align: middle !important;
}
.np-report-table > thead > tr > th {
	background: #f5f5f5;
	font-weight: 600;
	color: #111;
}
.np-th-class { min-width: 120px; }
.np-th-total { min-width: 88px; white-space: nowrap; }
.np-th-subj { min-width: 64px; }
.np-cell-link {
	color: #06c !important;
	text-decoration: underline !important;
	font-weight: 600;
}
.np-cell-link:hover { color: #004499 !important; }
.np-total-row > td {
	font-weight: 600;
}
.np-total-row-main {
	background: #e8e3ff !important;
}
.np-report-footer {
	margin-top: 12px;
	padding-top: 8px;
}
.np-report-hint {
	max-width: 55%;
	font-size: 13px;
	line-height: 1.5;
}
.np-footer-btn {
	background: #1976d2;
	border: 1px solid #0d47a1;
	color: #fff !important;
	margin-left: 8px;
	min-width: 80px;
	font-weight: 600;
}
.np-footer-btn:hover {
	background: #1565c0;
	color: #fff !important;
}
@media (max-width: 767px) {
	.np-report-hint { max-width: 100%; margin-bottom: 12px; float: none !important; }
	.np-report-actions { float: none !important; text-align: center; }
}
</style>
