<?php
$branch_id        = isset($branch_id) ? $branch_id : '';
$branch_name      = isset($branch_name) ? $branch_name : '';
$today_date       = isset($today_date) && !empty($today_date) ? $today_date : date('Y-m-d');
$filter_class_id  = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$class_options    = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options  = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$report_rows      = isset($report_rows) ? $report_rows : array();
?>

<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<div class="row">
					<div class="col-md-12 text-center">
						<h3 class="mt-none mb-sm" style="font-weight:600;">Home work and Syllabus Report</h3>
					</div>
				</div>
			</header>
			<div class="panel-body">
				<?php if (!$this->db->table_exists('digital_diary_teacher_activity_log') || !$this->db->table_exists('digital_diary_lesson')): ?>
					<div class="alert alert-warning">
						<strong>Setup required.</strong> Run <code>application/sql/digital_diary_teacher_activity_log.sql</code> and <code>application/sql/digital_diary.sql</code>.
					</div>
				<?php else: ?>
					<?php echo form_open(base_url('digital_diary/todays_work_report'), array('method' => 'get', 'class' => 'mb-md')); ?>
					<div class="row mb-sm">
						<?php if (is_superadmin_loggedin()): ?>
						<div class="col-md-2">
							<label class="control-label"><?= translate('branch') ?></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown(
									'branch_id',
									$arrayBranch,
									$branch_id,
									"class='form-control' id='branch_id' onchange='getClassByBranch(this.value)' data-plugin-selectTwo data-width=\"100%\""
								);
							?>
						</div>
						<?php endif; ?>
						<div class="col-md-2">
							<label class="control-label"><?= translate('date') ?></label>
							<input type="text" class="form-control" name="date" id="date" value="<?= html_escape($today_date) ?>" data-plugin-datepicker data-plugin-options='{"format": "yyyy-mm-dd"}'>
						</div>
						<div class="col-md-2">
							<label class="control-label"><?= translate('class') ?></label>
							<?= form_dropdown('class_id', $class_options, $filter_class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'") ?>
						</div>
						<div class="col-md-2">
							<label class="control-label"><?= translate('section') ?></label>
							<?= form_dropdown('section_id', $section_options, $filter_section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'") ?>
						</div>
						<div class="col-md-2 text-right" style="padding-top:24px;">
							<button type="submit" class="btn btn-primary" style="min-width:110px;"><?= translate('search') ?></button>
						</div>
					</div>
					<?php echo form_close(); ?>

					<div class="table-responsive">
						<table class="table table-bordered table-condensed table-hover">
							<thead>
								<tr>
									<th><?=translate('subject')?></th>
									<th>Total Lesson</th>
									<th>Now Going On</th>
									<th>Class Work</th>
									<th>Home Work</th>
									<th>Syllabus Percentage</th>
									<th>Student Work Per%</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($filter_class_id) || empty($filter_section_id)): ?>
									<tr>
										<td colspan="7" class="text-center text-muted"><?=translate('select')?> <?=translate('class')?> &amp; <?=translate('section')?> <?=translate('to')?> <?=translate('continue')?>.</td>
									</tr>
								<?php elseif (empty($report_rows)): ?>
									<tr>
										<td colspan="7" class="text-center text-muted">No data found for selected filters.</td>
									</tr>
								<?php else: ?>
									<?php foreach ($report_rows as $row): ?>
										<tr>
											<td><strong><?= !empty($row['subject_name']) ? html_escape($row['subject_name']) : '—' ?></strong></td>
											<td style="font-size:20px;font-weight:600;"><?= isset($row['total_lesson']) ? (int) $row['total_lesson'] : 0 ?></td>
											<td><?= !empty($row['now_going_on']) ? html_escape($row['now_going_on']) : '—' ?></td>
											<td><?= !empty($row['class_work']) ? html_escape($row['class_work']) : '—' ?></td>
											<td><?= !empty($row['home_work']) ? html_escape($row['home_work']) : '—' ?></td>
											<td><?= !empty($row['syllabus_percentage']) ? html_escape($row['syllabus_percentage']) : '—' ?></td>
											<td><?= !empty($row['student_work_percentage']) ? html_escape($row['student_work_percentage']) : '—' ?></td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					<?php if (!empty($filter_class_id) && !empty($filter_section_id)): ?>
						<?php
							$swr_q = array(
								'date' => $today_date,
								'class_id' => $filter_class_id,
								'section_id' => $filter_section_id,
							);
							if (is_superadmin_loggedin() && !empty($branch_id)) {
								$swr_q['branch_id'] = $branch_id;
							}
							$swr_url = base_url('digital_diary/student_work_report_entry') . '?' . http_build_query($swr_q);
						?>
						<p class="mt-md mb-sm" style="font-size:13px;">
							After clicking the <span style="color:#c00;font-weight:600;">go for student work report</span> button the next window will show the All Student Work Report for this class.
						</p>
						<div class="row">
							<div class="col-md-6">
								<button type="button" class="btn btn-default btn-sm" onclick="window.print();" style="min-width:90px;margin-right:6px;">PDF</button>
								<!-- Placeholder Excel button; hook up to export endpoint if available -->
								<button type="button" class="btn btn-default btn-sm" style="min-width:90px;">Excel</button>
							</div>
							<div class="col-md-6 text-right">
								<a href="<?= html_escape($swr_url) ?>" class="btn btn-primary" style="min-width:220px;">Go for all students work report</a>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

