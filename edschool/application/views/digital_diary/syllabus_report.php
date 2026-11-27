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
?>

<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<div class="row">
					<div class="col-md-12 text-center">
						<h3 class="mt-none mb-sm" style="font-weight:600;color:#c00;">Syllabus Report Class wise</h3>
					</div>
				</div>
			</header>
			<div class="panel-body">
				<?php if (is_superadmin_loggedin()): ?>
				<section class="panel mb-md">
					<header class="panel-heading">
						<h4 class="panel-title"><?= translate('branch') ?></h4>
					</header>
					<div class="panel-body">
						<?php echo form_open(base_url('digital_diary/syllabus_report'), array('method' => 'get', 'class' => 'form-inline')); ?>
						<div class="form-group mr-sm">
							<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width='260px'");
							?>
						</div>
						<div class="form-group mr-sm">
							<label class="control-label"><?= translate('date') ?></label>
							<input type="text" class="form-control" name="date" value="<?= html_escape($today_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
						<button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
						<?php echo form_close(); ?>
					</div>
				</section>
				<?php else: ?>
				<div class="mb-md">
					<strong><?= translate('branch') ?>:</strong> <?= html_escape(!empty($branch_name) ? $branch_name : get_type_name_by_id('branch', $branch_id)) ?>
				</div>
				<?php endif; ?>

				<ul class="nav nav-pills mb-md">
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

				<?php if ($syllabus_mode === 'teacher'): ?>
					<div class="table-responsive">
						<table class="table table-bordered table-condensed text-center">
							<thead>
								<tr>
									<th style="min-width:180px;">Teacher</th>
									<th>Classes</th>
									<th>Assignment Percent</th>
									<th>Work Percent</th>
									<th>Syllabus Percent</th>
									<th>Overall Percent</th>
									<th>Rank</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($teacher_rows)): ?>
								<tr>
									<td colspan="8" class="text-muted text-center"><?= translate('no_information_available'); ?></td>
								</tr>
								<?php else: ?>
									<?php foreach ($teacher_rows as $row): ?>
									<tr>
										<td class="text-left"><?= html_escape($row['teacher']) ?></td>
										<td class="text-left"><?= html_escape($row['classes']) ?></td>
										<td><?= (int) $row['assign_percent'] ?>%</td>
										<td><?= (int) $row['work_percent'] ?>%</td>
										<td><?= (int) $row['syllabus_percent'] ?>%</td>
										<td><?= (int) $row['overall_percent'] ?>%</td>
										<td><?= (int) $row['rank'] ?></td>
										<td>
											<a href="<?= base_url('digital_diary/syllabus_report_teacher_detail') . '?' . http_build_query(array('branch_id' => $branch_id, 'from' => $today_date, 'to' => $today_date, 'teacher_id' => (int) $row['teacher_id'], 'teacher_name' => $row['teacher'])) ?>" class="btn btn-xs btn-primary">
												View
											</a>
										</td>
									</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				<?php elseif ($syllabus_mode === 'subject'): ?>
					<div class="table-responsive">
						<table class="table table-bordered table-condensed text-center">
							<thead>
								<tr>
									<th style="min-width:160px;">Class</th>
									<th style="min-width:160px;">Subject</th>
									<th>Assignment Percent</th>
									<th>Work Percent</th>
									<th>Syllabus Percent</th>
									<th>Overall Percent</th>
									<th>Rank</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($subject_rows)): ?>
								<tr>
									<td colspan="8" class="text-muted text-center"><?= translate('no_information_available'); ?></td>
								</tr>
								<?php else: ?>
									<?php foreach ($subject_rows as $row): ?>
									<tr>
										<td class="text-left">
											<?php
												$clsName = isset($row['class_name']) ? $row['class_name'] : '';
												$secList = $this->app_lib->getSections((int) $row['class_id'], false);
												if (isset($secList[''])) {
													unset($secList['']);
												}
												$secText = !empty($secList) ? implode(', ', $secList) : '';
											?>
											<?= html_escape($clsName) ?>
											<?php if ($secText !== ''): ?>
												<span class="text-muted"> (<?= html_escape($secText) ?>)</span>
											<?php endif; ?>
										</td>
										<td class="text-left"><?= html_escape($row['subject_name']) ?></td>
										<td><?= (int) $row['assign_percent'] ?>%</td>
										<td><?= (int) $row['work_percent'] ?>%</td>
										<td><?= (int) $row['syllabus_percent'] ?>%</td>
										<td><?= (int) $row['overall_percent'] ?>%</td>
										<td><?= (int) $row['rank'] ?></td>
										<td>
											<a href="<?= base_url('digital_diary/todays_work_report') . '?' . http_build_query(array('class_id' => (int) $row['class_id'], 'section_id' => (int) $row['section_id'])) ?>">
												<i class="fas fa-info-circle"></i>
											</a>
										</td>
									</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-bordered table-condensed text-center">
							<thead>
								<tr>
									<th style="min-width:140px;">Class</th>
									<th style="min-width:160px;">Class Teacher</th>
									<th>Assignment Percent</th>
									<th>Work Percent</th>
									<th>Syllabus Percent</th>
									<th>Overall Percent</th>
									<th>Rank</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($class_rows)): ?>
								<tr>
									<td colspan="8" class="text-muted text-center"><?= translate('no_information_available'); ?></td>
								</tr>
								<?php else: ?>
									<?php foreach ($class_rows as $row): ?>
									<tr>
										<td class="text-left"><?= html_escape($row['class_name']) ?></td>
										<td class="text-left"><?= html_escape($row['class_teacher']) ?></td>
										<td><?= (int) $row['assign_percent'] ?>%</td>
										<td><?= (int) $row['work_percent'] ?>%</td>
										<td><?= (int) $row['syllabus_percent'] ?>%</td>
										<td><?= (int) $row['overall_percent'] ?>%</td>
										<td><?= (int) $row['rank'] ?></td>
										<td>
											<a href="<?= base_url('digital_diary/syllabus_report_class_detail') . '?' . http_build_query(array('branch_id' => $branch_id, 'class_id' => (int) $row['class_id'], 'section_id' => isset($row['section_id']) ? (int) $row['section_id'] : 0)) ?>" class="btn btn-xs btn-primary">
												View
											</a>
										</td>
									</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>

				<div class="row mt-md">
					<div class="col-md-6">
						<p class="mb-xs" style="font-size:13px;">
							After clicking the class or action button the next window will show detailed Home work and Syllabus Report for that class.
						</p>
					</div>
					<div class="col-md-3 text-right">
						<strong>
							<?php if ($syllabus_mode === 'teacher'): ?>
								Today Winner Teacher
							<?php elseif ($syllabus_mode === 'subject'): ?>
								Today Winner Subject
							<?php else: ?>
								Today Winner Class
							<?php endif; ?>
						</strong>
					</div>
					<div class="col-md-3">
						<?php
							if ($syllabus_mode === 'teacher') {
								$winnerLabel = $winner_teacher_name;
							} elseif ($syllabus_mode === 'subject') {
								$winnerLabel = $winner_subject_name;
							} else {
								$winnerLabel = $winner_class_name;
							}
						?>
						<span class="btn btn-success btn-block" style="font-weight:600;"><?= !empty($winnerLabel) ? html_escape($winnerLabel) : '—' ?></span>
					</div>
				</div>

				<div class="text-right mt-md">
					<button type="button" class="btn btn-default btn-sm" style="min-width:90px;">Share</button>
					<button type="button" class="btn btn-default btn-sm" onclick="window.print();" style="min-width:90px;">PDF</button>
					<button type="button" class="btn btn-default btn-sm" style="min-width:90px;">Excel</button>
				</div>
			</div>
		</section>
	</div>
</div>

