<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$work_date = isset($work_date) ? $work_date : date('Y-m-d');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$alert_id = isset($alert_id) ? (int) $alert_id : 0;
$subject_rows = isset($subject_rows) ? $subject_rows : array();
$students = isset($students) ? $students : array();
$saved_maps = isset($saved_maps) ? $saved_maps : array();
$submissions = isset($submissions) ? $submissions : array();
$tw_map = isset($tw_map) ? $tw_map : array();
$review_window = isset($review_window) && is_array($review_window) ? $review_window : array('status' => 'not_final', 'label' => '');
$can_edit_review = !empty($can_edit_review);
$className = $filter_class_id ? get_type_name_by_id('class', $filter_class_id) : '';
$sectionName = $filter_section_id ? get_type_name_by_id('section', $filter_section_id) : '';
$allReviewed = !empty($subject_rows);
foreach ($subject_rows as $srCheck) {
	if (empty($srCheck['reviewed'])) {
		$allReviewed = false;
		break;
	}
}
$rwStatus = isset($review_window['status']) ? $review_window['status'] : 'not_final';
$rwLabel = isset($review_window['label']) ? $review_window['label'] : '';
$rwDeadline = !empty($review_window['deadline']) ? $review_window['deadline'] : null;
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-clipboard-check"></i> Coordinator Review — <?= html_escape($className) ?> / <?= html_escape($sectionName) ?> — <?= html_escape($work_date) ?></h4>
				<div class="panel-btn">
					<a href="<?= base_url('digital_diary/coordinator_tasks' . (!empty($branch_id) ? ('?branch_id=' . urlencode($branch_id)) : '')) ?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<?php if ($filter_class_id < 1 || $filter_section_id < 1): ?>
				<p class="text-muted">Open this page from Coordinator Tasks with a class and section.</p>
				<?php elseif ($branch_id < 1): ?>
				<p class="text-muted">Select a branch (or open Review from Coordinator Tasks so branch is included).</p>
				<?php elseif (empty($subject_rows)): ?>
				<p class="text-muted">No Today's Work saved for this class/section on <?= html_escape($work_date) ?>.</p>
				<?php else: ?>

				<?php if ($rwStatus === 'closed_reviewed' || $allReviewed): ?>
				<div class="alert alert-success"><i class="fas fa-check"></i> Already reviewed by coordinator<?= $rwDeadline ? (' (deadline was ' . html_escape(date('d-M-Y', strtotime($rwDeadline))) . ')') : '' ?>.</div>
				<?php elseif ($rwStatus === 'not_final'): ?>
				<div class="alert alert-info"><i class="fas fa-info-circle"></i> Review opens after the class teacher <strong>Final Submit</strong>. Marks are read-only until then.</div>
				<?php elseif ($rwStatus === 'overdue'): ?>
				<div class="alert alert-danger">
					<i class="fas fa-exclamation-triangle"></i>
					<strong>Overdue</strong> — review was due by <?= $rwDeadline ? html_escape(date('d-M-Y', strtotime($rwDeadline))) : '—' ?>.
					You can still mark Fair / Writing / Learning and submit.
				</div>
				<?php elseif ($rwStatus === 'open'): ?>
				<div class="alert alert-warning">
					<i class="fas fa-calendar-check"></i>
					<strong>Review by <?= $rwDeadline ? html_escape(date('d-M-Y', strtotime($rwDeadline))) : '—' ?></strong>
					<?php if (isset($review_window['days_left']) && $review_window['days_left'] !== null): ?>
					— <?= (int) $review_window['days_left'] ?> working day<?= ((int) $review_window['days_left'] === 1) ? '' : 's' ?> left
					<?php endif; ?>
					(7 working days after Final Submit). Mark Fair / Writing / Learning per student, then Check &amp; Submit.
				</div>
				<?php endif; ?>

				<h5>Today's Work summary</h5>
				<div class="table-responsive mb-md">
					<table class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>Subject</th>
								<th>Class work</th>
								<th>Home work</th>
								<th>Teacher photos</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($subject_rows as $sr): ?>
							<tr>
								<td><?= html_escape($sr['subject_name']) ?></td>
								<td><?= html_escape($sr['class_work']) ?></td>
								<td><?= html_escape($sr['home_work']) ?></td>
								<td>
									<?php if (!empty($sr['cw_attachment'])): ?>
									<a href="<?= base_url('uploads/attachments/digital_diary_today_work/' . rawurlencode($sr['cw_attachment'])) ?>" target="_blank" class="btn btn-xs btn-link">CW</a>
									<?php endif; ?>
									<?php if (!empty($sr['hw_attachment'])): ?>
									<a href="<?= base_url('uploads/attachments/digital_diary_today_work/' . rawurlencode($sr['hw_attachment'])) ?>" target="_blank" class="btn btn-xs btn-link">HW</a>
									<?php endif; ?>
									<?php if (empty($sr['cw_attachment']) && empty($sr['hw_attachment'])): ?>—<?php endif; ?>
								</td>
								<td><?= !empty($sr['reviewed']) ? '<span class="label label-success">Reviewed</span>' : '<span class="label label-warning">Pending</span>' ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<?php if ($can_edit_review): ?>
				<?php echo form_open(base_url('digital_diary/coordinator_review')); ?>
				<input type="hidden" name="work_date" value="<?= html_escape($work_date) ?>">
				<input type="hidden" name="class_id" value="<?= (int) $filter_class_id ?>">
				<input type="hidden" name="section_id" value="<?= (int) $filter_section_id ?>">
				<input type="hidden" name="alert_id" value="<?= (int) $alert_id ?>">
				<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
				<?php endif; ?>

				<h5>Students — Fair / Writing / Learning<?= $can_edit_review ? ' (editable)' : '' ?></h5>
				<div class="table-responsive mb-md">
					<table class="table table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Student</th>
								<?php foreach ($subject_rows as $sr): ?>
								<th class="text-center"><?= html_escape($sr['subject_name']) ?><br><small>F / W / L</small></th>
								<?php endforeach; ?>
								<th>HW photos / Learning</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$n = 1;
							foreach ($students as $st):
								$sid = (int) $st['student_id'];
							?>
							<tr>
								<td><?= $n++ ?></td>
								<td><?= html_escape($st['fullname']) ?></td>
								<?php foreach ($subject_rows as $sr):
									$subjId = (int) $sr['subject_id'];
									$row = isset($saved_maps[$subjId][$sid]) ? $saved_maps[$subjId][$sid] : array();
									$fc = !empty($row['fair_copy']);
									$ww = !empty($row['writing_work']);
									$lw = !empty($row['learning_work']);
								?>
								<td class="text-center" style="white-space:nowrap;">
									<?php if ($can_edit_review): ?>
									<label class="checkbox-inline" title="Fair Copy"><input type="checkbox" name="swr[<?= $subjId ?>][<?= $sid ?>][fair_copy]" value="1"<?= $fc ? ' checked' : '' ?>> F</label>
									<label class="checkbox-inline" title="Writing Work"><input type="checkbox" name="swr[<?= $subjId ?>][<?= $sid ?>][writing_work]" value="1"<?= $ww ? ' checked' : '' ?>> W</label>
									<label class="checkbox-inline" title="Learning Work"><input type="checkbox" name="swr[<?= $subjId ?>][<?= $sid ?>][learning_work]" value="1"<?= $lw ? ' checked' : '' ?>> L</label>
									<?php else: ?>
									<?= $fc ? '✓' : '—' ?> / <?= $ww ? '✓' : '—' ?> / <?= $lw ? '✓' : '—' ?>
									<?php endif; ?>
								</td>
								<?php endforeach; ?>
								<td>
									<?php
									$bits = array();
									foreach ($subject_rows as $sr) {
										$twid = (int) $sr['today_work_id'];
										if (isset($submissions[$twid][$sid]['homework']['photo']) && $submissions[$twid][$sid]['homework']['photo'] !== '') {
											$bits[] = '<a href="' . base_url('uploads/attachments/digital_diary_submit/' . rawurlencode($submissions[$twid][$sid]['homework']['photo'])) . '" target="_blank">HW photo</a>';
										}
										if (isset($submissions[$twid][$sid]['learning']['status'])) {
											$bits[] = 'Learning: ' . html_escape($submissions[$twid][$sid]['learning']['status']);
										}
									}
									echo !empty($bits) ? implode(' · ', $bits) : '—';
									?>
								</td>
							</tr>
							<?php endforeach; ?>
							<?php if (empty($students)): ?>
							<tr><td colspan="<?= 3 + count($subject_rows) ?>" class="text-center text-muted">No students found.</td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="text-right">
					<?php if ($can_edit_review): ?>
					<button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Check &amp; Submit review</button>
					<?php echo form_close(); ?>
					<?php elseif ($allReviewed || $rwStatus === 'closed_reviewed'): ?>
					<span class="text-muted">Review locked after submit.</span>
					<?php else: ?>
					<span class="text-muted"><?= html_escape($rwLabel !== '' ? $rwLabel : 'Waiting for Final Submit') ?></span>
					<?php endif; ?>
				</div>

				<?php endif; ?>
			</div>
		</section>
	</div>
</div>
