<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$from_date = isset($from_date) ? $from_date : date('Y-m-d');
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$class_name = isset($class_name) ? $class_name : '';
$subject_name = isset($subject_name) ? $subject_name : '';
$np_detail_rows = isset($np_detail_rows) ? $np_detail_rows : array();
$back_q = array('branch_id' => $branch_id, 'from' => $from_date, 'to' => $to_date);
$back_url = base_url('digital_diary/non_performer_report') . '?' . http_build_query($back_q);
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-user-times"></i> Non Performer Report</h4>
				<div class="panel-btn">
					<a href="<?= html_escape($back_url) ?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<div class="text-center mb-lg">
					<h3 class="mt-none mb-sm np-detail-title" style="font-weight:700;color:#c00;text-decoration:underline;">
						Non Performer for class - <?= html_escape($class_name !== '' ? $class_name : '—') ?> on <?= html_escape($subject_name !== '' ? $subject_name : '—') ?>
					</h3>
					<?php if ($from_date !== $to_date): ?>
					<p class="text-muted mb-none"><?= html_escape($from_date) ?> — <?= html_escape($to_date) ?></p>
					<?php else: ?>
					<p class="text-muted mb-none"><?= html_escape($from_date) ?></p>
					<?php endif; ?>
				</div>

				<div class="table-responsive">
					<table class="table table-bordered table-condensed text-center">
						<thead>
							<tr>
								<th style="width:40px;"><input type="checkbox" title="Select all" onclick="jQuery('.np-row-chk').prop('checked', this.checked);"></th>
								<th>Adm number</th>
								<th>Roll N0.</th>
								<th class="text-left">Student's Name</th>
								<th>Fair Notebook</th>
								<th>Written Work</th>
								<th>Learning Work</th>
								<th class="text-left">Remark</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$adm = 1;
							foreach ($np_detail_rows as $dr):
								$rollDisp = isset($dr['roll']) && $dr['roll'] !== '' && $dr['roll'] !== null
									? str_pad((string) (int) $dr['roll'], 2, '0', STR_PAD_LEFT)
									: '—';
							?>
							<tr>
								<td><input type="checkbox" class="np-row-chk" value="<?= (int) $dr['student_id'] ?>"></td>
								<td><?= (int) $adm ?></td>
								<td><?= html_escape($rollDisp) ?></td>
								<td class="text-left"><?= html_escape($dr['fullname']) ?></td>
								<td><?= html_escape($dr['fair_notebook']) ?></td>
								<td><?= html_escape($dr['written_work']) ?></td>
								<td><?= html_escape($dr['learning_work']) ?></td>
								<td class="text-left"><?= html_escape($dr['remarks']) ?></td>
							</tr>
							<?php
								$adm++;
							endforeach;
							?>
							<?php if (empty($np_detail_rows)): ?>
							<tr>
								<td colspan="8" class="text-muted"><?= translate('no_information_available') ?></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="text-center np-detail-actions mt-md">
					<a href="#" class="btn np-act-btn" onclick="return false;">Whatsapp</a>
					<a href="#" class="btn np-act-btn" onclick="return false;">Message</a>
					<a href="#" class="btn np-act-btn" onclick="return false;">Email</a>
					<a href="#" class="btn np-act-btn" onclick="return false;">Share</a>
					<a href="#" class="btn np-act-btn" onclick="return false;">PDF</a>
					<a href="#" class="btn np-act-btn" onclick="return false;">Excel</a>
				</div>
			</div>
		</section>
	</div>
</div>
<style type="text/css">
.np-detail-actions .np-act-btn {
	background: #b3e5fc;
	border: 1px solid #0288d1;
	color: #111;
	margin: 4px;
	min-width: 88px;
}
.np-detail-title { font-size: 1.35em; }
</style>
