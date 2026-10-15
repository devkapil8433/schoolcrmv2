<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$from_date = isset($from_date) ? $from_date : date('Y-m-d');
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$class_id = isset($class_id) ? (int) $class_id : 0;
$section_id = isset($section_id) ? (int) $section_id : 0;
$class_options = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$subjects = isset($detail_subjects) ? $detail_subjects : array();
$rows = isset($detail_rows) ? $detail_rows : array();
$today_perf = isset($today_class_performance) ? $today_class_performance : '—';
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
							Syllabus Report of Class<?= $class_id ? ' - ' . html_escape(get_type_name_by_id('class', $class_id)) : '' ?>
						</h3>
					</div>
				</div>
				<div class="panel-btn">
					<a href="<?= html_escape($back_url) ?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/syllabus_report_class_detail'), array('method' => 'get', 'class' => 'mb-md')); ?>
				<?php if (is_superadmin_loggedin()): ?>
				<div class="row mb-sm">
					<div class="col-md-2">
						<label class="control-label"><?= translate('branch') ?></label>
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'");
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
						<label class="control-label"><?= translate('class') ?></label>
						<?= form_dropdown('class_id', $class_options, $class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width=\"100%\"") ?>
					</div>
					<div class="col-md-3">
						<label class="control-label"><?= translate('section') ?></label>
						<?= form_dropdown('section_id', $section_options, $section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width=\"100%\"") ?>
					</div>
				</div>
				<div class="row mb-sm">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-primary" style="min-width:110px;"><?= translate('search') ?></button>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div class="table-responsive">
					<table class="table table-bordered table-condensed text-center">
						<thead>
							<tr>
								<th style="min-width:100px;">Date</th>
								<?php foreach ($subjects as $s): ?>
								<th>
									<?= html_escape($s['subject_name']) ?>
									<?php if (!empty($s['syllabus_percent']) && $s['syllabus_percent'] !== '—'): ?>
										- <?= html_escape($s['syllabus_percent']) ?>
									<?php endif; ?>
								</th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($class_id) || empty($section_id)): ?>
							<tr>
								<td colspan="<?= 1 + count($subjects) ?>" class="text-muted text-center">
									<?= translate('select') ?> <?= translate('class') ?> &amp; <?= translate('section') ?> <?= translate('to') ?> <?= translate('continue') ?>.
								</td>
							</tr>
							<?php elseif (empty($rows)): ?>
							<tr>
								<td colspan="<?= 1 + count($subjects) ?>" class="text-muted text-center">
									<?= translate('no_information_available'); ?>
								</td>
							</tr>
							<?php else: ?>
								<?php foreach ($rows as $r): ?>
								<tr>
									<td class="text-left"><?= html_escape(date('d-m-Y', strtotime($r['date']))) ?></td>
									<?php foreach ($subjects as $s): ?>
										<?php
											$sid = (int) $s['subject_id'];
											$txt = isset($r['cells'][$sid]) ? $r['cells'][$sid] : '';
										?>
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
						<strong>Today Class Performance</strong>
					</div>
					<div class="col-md-3">
						<span class="btn btn-success btn-block" style="font-weight:600;"><?= html_escape($today_perf) ?></span>
					</div>
					<div class="col-md-3 text-right">
						<button type="button" class="btn btn-default btn-sm" style="min-width:90px;">Share</button>
						<button type="button" class="btn btn-default btn-sm" onclick="window.print();" style="min-width:90px;">PDF</button>
						<button type="button" class="btn btn-default btn-sm" style="min-width:90px;">Excel</button>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>

