<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$from_date = isset($from_date) ? $from_date : date('Y-m-d');
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$teacher_id = isset($teacher_id) ? (int) $teacher_id : 0;
$teacher_options = isset($teacher_options) ? $teacher_options : array('' => translate('select'));
$teacher_name = isset($teacher_name) ? $teacher_name : '';
$cols = isset($teacher_columns) ? $teacher_columns : array();
$rows = isset($teacher_rows) ? $teacher_rows : array();
$today_marks = isset($teacher_today_marks) ? $teacher_today_marks : '—';
$back_q = array();
if (!empty($branch_id)) {
	$back_q['branch_id'] = $branch_id;
}
$back_url = base_url('digital_diary/syllabus_report') . (!empty($back_q) ? '?' . http_build_query($back_q) : '');
?>

<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<div class="row">
					<div class="col-md-12 text-center">
						<h3 class="mt-none mb-sm" style="font-weight:600;color:#c00;">
							Syllabus Report<?= $teacher_name ? ' of ' . html_escape($teacher_name) : '' ?>
						</h3>
					</div>
				</div>
				<div class="panel-btn">
					<a href="<?= html_escape($back_url) ?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/syllabus_report_teacher_detail'), array('method' => 'get', 'class' => 'mb-md')); ?>
				<div class="row mb-sm">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-2">
						<label class="control-label"><?= translate('branch') ?></label>
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width=\"100%\"");
						?>
					</div>
					<?php endif; ?>
					<div class="col-md-2">
						<label class="control-label">From</label>
						<input type="text" class="form-control" name="from" value="<?= html_escape($from_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
					</div>
					<div class="col-md-2">
						<label class="control-label">To</label>
						<input type="text" class="form-control" name="to" value="<?= html_escape($to_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
					</div>
					<div class="col-md-3">
						<label class="control-label">Teacher</label>
						<?= form_dropdown('teacher_id', $teacher_options, $teacher_id, "class='form-control' id='teacher_id' data-plugin-selectTwo data-width=\"100%\"") ?>
					</div>
					<div class="col-md-3 text-right" style="padding-top:24px;">
						<button type="submit" class="btn btn-primary" style="min-width:110px;"><?= translate('search') ?></button>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div class="table-responsive">
					<table class="table table-bordered table-condensed text-center">
						<thead>
							<tr>
								<th style="min-width:100px;">Date</th>
								<?php foreach ($cols as $key => $c): ?>
								<th>
									<?php
										$sp = $c['syllabus_percent'];
										$subTitle = $c['subject_name'] . ($sp !== '—' ? ' - ' . $sp : '');
									?>
									<span><?= html_escape($subTitle) ?></span><br>
									<small style="color:green;">
										<?= html_escape($c['class_name']) ?><?= $c['section_name'] ? ' - ' . html_escape($c['section_name']) : '' ?>
									</small>
								</th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php if ($teacher_id <= 0): ?>
							<tr>
								<td colspan="<?= 1 + count($cols) ?>" class="text-muted text-center">
									<?= translate('select') ?> <?= translate('teacher') ?> <?= translate('to') ?> <?= translate('continue') ?>.
								</td>
							</tr>
							<?php elseif (empty($rows)): ?>
							<tr>
								<td colspan="<?= 1 + count($cols) ?>" class="text-muted text-center">
									<?= translate('no_information_available'); ?>
								</td>
							</tr>
							<?php else: ?>
								<?php foreach ($rows as $r): ?>
								<tr>
									<td class="text-left"><?= html_escape(date('d-m-Y', strtotime($r['date']))) ?></td>
									<?php foreach ($cols as $key => $c): ?>
										<?php $txt = isset($r['cells'][$key]) ? $r['cells'][$key] : ''; ?>
										<td class="text-left" style="white-space:pre-line;"><?= $txt !== '' ? html_escape($txt) : '' ?></td>
									<?php endforeach; ?>
								</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="row mt-md">
					<div class="col-md-6">
						<strong>Today Marks - <?= html_escape($today_marks) ?></strong>
					</div>
					<div class="col-md-6 text-right">
						<button type="button" class="btn btn-default btn-sm" style="min-width:90px;">Share</button>
						<button type="button" class="btn btn-default btn-sm" onclick="window.print();" style="min-width:90px;">PDF</button>
						<button type="button" class="btn btn-default btn-sm" style="min-width:90px;">Excel</button>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>

