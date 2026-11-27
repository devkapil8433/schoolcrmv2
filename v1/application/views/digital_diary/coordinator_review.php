<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$work_date = isset($work_date) ? $work_date : date('Y-m-d');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$alert_id = isset($alert_id) ? (int) $alert_id : 0;
$focus_activity_id = isset($focus_activity_id) ? (int) $focus_activity_id : 0;
$focus_student_id = isset($focus_student_id) ? (int) $focus_student_id : 0;
$subject_rows = isset($subject_rows) ? $subject_rows : array();
$students = isset($students) ? $students : array();
$student_work = isset($student_work) && is_array($student_work) ? $student_work : array();
$review_activities = isset($review_activities) && is_array($review_activities) ? $review_activities : array();
$skill_ratings = isset($skill_ratings) && is_array($skill_ratings) ? $skill_ratings : array();
$rated_map = isset($rated_map) && is_array($rated_map) ? $rated_map : array();
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
if (empty($subject_rows) && !empty($review_window['is_reviewed'])) {
	$allReviewed = true;
}
$rwStatus = isset($review_window['status']) ? $review_window['status'] : 'not_final';
$rwLabel = isset($review_window['label']) ? $review_window['label'] : '';
$rwDeadline = !empty($review_window['deadline']) ? $review_window['deadline'] : null;
$actNames = !empty($review_activities) ? implode(', ', array_values($review_activities)) : '';
$studentCount = count($students);
$ratedCount = 0;
$validStudentIds = array();
foreach ($students as $stCount) {
	$sidCount = (int) $stCount['student_id'];
	$validStudentIds[] = $sidCount;
	if (!empty($rated_map[$sidCount])) {
		$ratedCount++;
	}
}
if ($focus_student_id < 1 || !in_array($focus_student_id, $validStudentIds, true)) {
	$focus_student_id = 0;
	foreach ($students as $stPick) {
		$sidPick = (int) $stPick['student_id'];
		if (empty($rated_map[$sidPick])) {
			$focus_student_id = $sidPick;
			break;
		}
	}
	if ($focus_student_id < 1 && !empty($validStudentIds)) {
		$focus_student_id = (int) $validStudentIds[0];
	}
}
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
$backUrl = base_url('digital_diary/coordinator_tasks' . (!empty($branch_id) ? ('?branch_id=' . urlencode($branch_id)) : ''));
$saveUrl = base_url('digital_diary/coordinator_review');
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title">
					<i class="fas fa-clipboard-check"></i> Coordinator Review
					<?php if ($className !== '' || $sectionName !== ''): ?>
					— <?= html_escape($className) ?> / <?= html_escape($sectionName) ?>
					<?php endif; ?>
					<?php if (!empty($work_date)): ?>
					— <?= html_escape($work_date) ?>
					<?php endif; ?>
				</h4>
				<div class="panel-btn">
					<a href="<?= html_escape($backUrl) ?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<?php if ($filter_class_id < 1 || $filter_section_id < 1): ?>
				<p class="text-muted">Open this page from Coordinator Tasks with a class and section.</p>
				<?php elseif ($branch_id < 1): ?>
				<p class="text-muted">Select a branch (or open Review from Coordinator Tasks so branch is included).</p>
				<?php elseif (empty($review_activities)): ?>
				<p class="text-muted">No coordinator activity types are assigned for this class. Assign the coordinator to an activity type first.</p>
				<?php else: ?>

				<p class="mb-sm">
					<strong>Activity:</strong> <?= html_escape($actNames) ?>
					&nbsp;|&nbsp;
					<strong>Students rated:</strong> <span id="cr_rated_count"><?= (int) $ratedCount ?></span> / <?= (int) $studentCount ?>
				</p>

				<div id="cr_toast" class="alert alert-success hidden"></div>

				<?php if ($rwStatus === 'closed_reviewed' || $allReviewed): ?>
				<div class="alert alert-success">
					<i class="fas fa-check"></i> Already reviewed for <?= html_escape($actNames !== '' ? $actNames : 'your activity types') ?><?= $rwDeadline ? (' (deadline was ' . html_escape(date('d-M-Y', strtotime($rwDeadline))) . ')') : '' ?>.
				</div>
				<?php elseif ($rwStatus === 'not_final'): ?>
				<div class="alert alert-info"><i class="fas fa-info-circle"></i> Review opens after the class teacher <strong>Final Submit</strong>. Ratings are read-only until then.</div>
				<?php elseif ($rwStatus === 'overdue'): ?>
				<div class="alert alert-danger">
					<i class="fas fa-exclamation-triangle"></i>
					<strong>Overdue</strong> — review was due by <?= $rwDeadline ? html_escape(date('d-M-Y', strtotime($rwDeadline))) : '—' ?>.
					Save students one by one, then finalize.
				</div>
				<?php elseif ($rwStatus === 'open'): ?>
				<div class="alert alert-warning">
					<i class="fas fa-calendar-check"></i>
					<strong>Review by <?= $rwDeadline ? html_escape(date('d-M-Y', strtotime($rwDeadline))) : '—' ?></strong>
					<?php if (isset($review_window['days_left']) && $review_window['days_left'] !== null): ?>
					— <?= (int) $review_window['days_left'] ?> working day<?= ((int) $review_window['days_left'] === 1) ? '' : 's' ?> left
					<?php endif; ?>
					. Rate the student, click <strong>Save this student</strong>, then finalize when the class is done.
				</div>
				<?php endif; ?>

				<?php if (!empty($subject_rows)): ?>
				<h5>Today's Work</h5>
				<div class="table-responsive mb-md">
					<table class="table table-bordered table-condensed table-hover table-striped mb-none">
						<thead>
							<tr>
								<th><?= translate('subject') ?></th>
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
									<a href="<?= base_url('uploads/attachments/digital_diary_today_work/' . rawurlencode($sr['cw_attachment'])) ?>" target="_blank" class="btn btn-xs btn-default">CW</a>
									<?php endif; ?>
									<?php if (!empty($sr['hw_attachment'])): ?>
									<a href="<?= base_url('uploads/attachments/digital_diary_today_work/' . rawurlencode($sr['hw_attachment'])) ?>" target="_blank" class="btn btn-xs btn-default">HW</a>
									<?php endif; ?>
									<?php if (empty($sr['cw_attachment']) && empty($sr['hw_attachment'])): ?>—<?php endif; ?>
								</td>
								<td><?= !empty($sr['reviewed']) ? '<span class="label label-success">Reviewed</span>' : '<span class="label label-warning">Pending</span>' ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php endif; ?>

				<?php if (empty($students)): ?>
				<p class="text-muted">No students found.</p>
				<?php else: ?>
				<?php echo form_open($saveUrl, array('id' => 'frm_coord_review')); ?>
				<input type="hidden" name="work_date" value="<?= html_escape($work_date) ?>">
				<input type="hidden" name="class_id" value="<?= (int) $filter_class_id ?>">
				<input type="hidden" name="section_id" value="<?= (int) $filter_section_id ?>">
				<input type="hidden" name="alert_id" value="<?= (int) $alert_id ?>">
				<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
				<input type="hidden" name="focus_activity_id" value="<?= (int) $focus_activity_id ?>">
				<input type="hidden" name="submit_mode" id="cr_submit_mode" value="all">
				<input type="hidden" name="student_id" id="cr_student_id" value="<?= (int) $focus_student_id ?>">
				<input type="hidden" name="ajax" id="cr_ajax" value="0">

				<div class="row">
					<div class="col-md-4">
						<section class="panel">
							<header class="panel-heading">
								<h4 class="panel-title">
									Students
									<span class="badge" id="cr_list_count"><?= (int) $ratedCount ?> saved</span>
								</h4>
							</header>
							<div class="panel-body">
								<div class="table-responsive">
									<table class="table table-bordered table-condensed table-hover mb-none">
										<thead>
											<tr>
												<th>#</th>
												<th><?= translate('student') ?></th>
												<th>Status</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($students as $idx => $st):
												$sid = (int) $st['student_id'];
												$isSaved = !empty($rated_map[$sid]);
												$isOn = ($sid === $focus_student_id);
												$roll = isset($st['roll']) && $st['roll'] !== '' ? $st['roll'] : ($idx + 1);
												$rowClass = 'cr-stu';
												if ($isOn) {
													$rowClass .= ' info';
												}
												if ($isSaved) {
													$rowClass .= ' success';
												}
											?>
											<tr class="<?= $rowClass ?>" data-student="<?= $sid ?>" role="button">
												<td><?= html_escape($roll) ?></td>
												<td><?= html_escape($st['fullname']) ?></td>
												<td>
													<?php if ($isSaved): ?>
													<span class="label label-success cr-stu-status">Saved</span>
													<?php else: ?>
													<span class="label label-warning cr-stu-status">Pending</span>
													<?php endif; ?>
												</td>
											</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</section>
					</div>
					<div class="col-md-8">
						<?php foreach ($students as $idx => $st):
							$sid = (int) $st['student_id'];
							$isOn = ($sid === $focus_student_id);
							$isSaved = !empty($rated_map[$sid]);
							$roll = isset($st['roll']) && $st['roll'] !== '' ? $st['roll'] : ($idx + 1);
							$subs = isset($student_work[$sid]) ? $student_work[$sid] : array();
						?>
						<div class="cr-student-pane<?= $isOn ? '' : ' hidden' ?>" data-pane="<?= $sid ?>">
							<section class="panel">
								<header class="panel-heading">
									<h4 class="panel-title">
										<?= html_escape($st['fullname']) ?>
										<small>Roll <?= html_escape($roll) ?> — <?= (int) ($idx + 1) ?> / <?= (int) $studentCount ?></small>
									</h4>
									<div class="panel-btn">
										<button type="button" class="btn btn-default btn-circle btn-sm cr-prev"><i class="fas fa-chevron-left"></i></button>
										<button type="button" class="btn btn-default btn-circle btn-sm cr-next"><i class="fas fa-chevron-right"></i></button>
									</div>
								</header>
								<div class="panel-body">
									<?php if (!empty($subs)): ?>
									<p>
										<?php foreach ($subs as $sub):
											$photo = isset($sub['photo']) ? $sub['photo'] : '';
											$typeLabel = isset($sub['type']) && $sub['type'] === 'learning' ? 'Learning' : 'Homework';
										?>
										<span class="label label-default"><?= html_escape($sub['subject_name']) ?> · <?= html_escape($typeLabel) ?></span>
										<?php if ($photo !== ''): ?>
										<a href="<?= base_url('uploads/attachments/digital_diary_submit/' . rawurlencode($photo)) ?>" target="_blank" class="btn btn-xs btn-default">photo</a>
										<?php endif; ?>
										<?php if (!empty($sub['message'])): ?>
										<span class="text-muted"><?= html_escape($sub['message']) ?></span>
										<?php endif; ?>
										<?php endforeach; ?>
									</p>
									<?php endif; ?>

									<?php foreach ($review_activities as $aid => $aname):
										$aid = (int) $aid;
										$saved = isset($skill_ratings[$sid][$aid]) ? $skill_ratings[$sid][$aid] : array('score' => 0, 'comment' => '');
										$score = (int) $saved['score'];
										$comment = isset($saved['comment']) ? $saved['comment'] : '';
									?>
									<div class="cr-act" data-student="<?= $sid ?>" data-activity="<?= $aid ?>">
										<div class="form-group">
											<label class="control-label"><?= html_escape($aname) ?> (out of 10)</label>
											<?php if ($can_edit_review): ?>
											<select class="form-control cr-score-input" name="skill[<?= $sid ?>][<?= $aid ?>][score]">
												<option value="0"><?= translate('select') ?></option>
												<?php for ($n = 1; $n <= 10; $n++): ?>
												<option value="<?= $n ?>" <?= $score === $n ? 'selected' : '' ?>><?= $n ?></option>
												<?php endfor; ?>
											</select>
											<?php else: ?>
											<p class="form-control-static"><strong><?= $score > 0 ? (int) $score : '—' ?>/10</strong> — <?= $comment !== '' ? html_escape($comment) : 'No comment' ?></p>
											<?php endif; ?>
										</div>
										<?php if ($can_edit_review): ?>
										<div class="form-group">
											<label class="control-label">Comment</label>
											<input type="text" class="form-control skill-comment-input" name="skill[<?= $sid ?>][<?= $aid ?>][comment]" value="<?= html_escape($comment) ?>" placeholder="Comment (auto-fills from score, you can edit)">
										</div>
										<?php endif; ?>
									</div>
									<?php endforeach; ?>

									<p>
										<?php if ($isSaved): ?>
										<span class="label label-success cr-saved-label">Saved</span>
										<?php else: ?>
										<span class="label label-warning cr-saved-label">Not saved yet</span>
										<?php endif; ?>
									</p>
									<?php if ($can_edit_review): ?>
									<button type="button" class="btn btn-default cr-save-one">Save this student</button>
									<button type="button" class="btn btn-primary cr-save-next">Save &amp; next</button>
									<?php endif; ?>
								</div>
							</section>
						</div>
						<?php endforeach; ?>

						<?php if ($can_edit_review): ?>
						<p class="text-muted">Saving one student does not lock the class.</p>
						<button type="submit" class="btn btn-warning" id="cr_finalize"><i class="fas fa-check"></i> Finalize class review</button>
						<?php elseif ($allReviewed || $rwStatus === 'closed_reviewed'): ?>
						<p class="text-muted">Review locked after submit.</p>
						<?php else: ?>
						<p class="text-muted"><?= html_escape($rwLabel !== '' ? $rwLabel : 'Waiting for Final Submit') ?></p>
						<?php endif; ?>
					</div>
				</div>
				<?php echo form_close(); ?>
				<?php endif; ?>

				<?php endif; ?>
			</div>
		</section>
	</div>
</div>
<script type="text/javascript">
(function ($) {
	var studentCount = <?= (int) $studentCount ?>;
	var csrfName = <?= json_encode($csrfName) ?>;
	var csrfHash = <?= json_encode($csrfHash) ?>;

	function skillAutoComment(score) {
		score = parseInt(score, 10) || 0;
		if (score >= 9) return 'Excellent';
		if (score >= 7) return 'Good';
		if (score >= 1) return 'Needs Improvement';
		return '';
	}
	function toast(type, msg) {
		var $t = $('#cr_toast');
		$t.removeClass('hidden alert-success alert-danger alert-info alert-warning')
			.addClass('alert alert-' + type)
			.text(msg)
			.removeClass('hidden');
	}
	function currentId() {
		return parseInt($('.cr-student-pane:not(.hidden)').data('pane'), 10) || 0;
	}
	function ids() {
		return $('.cr-stu').map(function () { return parseInt($(this).data('student'), 10); }).get();
	}
	function showStudent(id) {
		if (!id) return;
		$('.cr-student-pane').addClass('hidden');
		$('.cr-student-pane[data-pane="' + id + '"]').removeClass('hidden');
		$('.cr-stu').removeClass('info');
		$('.cr-stu[data-student="' + id + '"]').addClass('info');
		$('#cr_student_id').val(id);
	}
	function markSaved(id) {
		var $row = $('.cr-stu[data-student="' + id + '"]');
		$row.addClass('success is-saved');
		$row.find('.cr-stu-status').removeClass('label-warning').addClass('label-success').text('Saved');
		$('.cr-student-pane[data-pane="' + id + '"] .cr-saved-label').removeClass('label-warning').addClass('label-success').text('Saved');
		var n = $('.cr-stu.success').length;
		$('#cr_rated_count').text(n);
		$('#cr_list_count').text(n + ' saved');
	}
	function neighbor(dir) {
		var list = ids();
		var cur = currentId();
		var i = list.indexOf(cur);
		if (i < 0) return list[0];
		i += dir;
		if (i < 0) i = list.length - 1;
		if (i >= list.length) i = 0;
		return list[i];
	}
	function nextUnsaved(afterId) {
		var list = ids();
		var start = list.indexOf(afterId);
		var i, sid;
		for (i = start + 1; i < list.length; i++) {
			sid = list[i];
			if (!$('.cr-stu[data-student="' + sid + '"]').hasClass('success')) return sid;
		}
		for (i = 0; i <= start; i++) {
			sid = list[i];
			if (!$('.cr-stu[data-student="' + sid + '"]').hasClass('success')) return sid;
		}
		return 0;
	}
	function saveStudent(thenNext) {
		var id = currentId();
		if (!id) return;
		var $pane = $('.cr-student-pane[data-pane="' + id + '"]');
		var missing = false;
		$pane.find('.cr-score-input').each(function () {
			if (!(parseInt($(this).val(), 10) > 0)) missing = true;
		});
		if (missing) {
			toast('danger', 'Choose a score out of 10 before saving this student.');
			return;
		}
		$('#cr_submit_mode').val('student');
		$('#cr_student_id').val(id);
		$('#cr_ajax').val('1');
		var data = $('#frm_coord_review').serializeArray();
		data.push({ name: csrfName, value: csrfHash });
		$pane.find('.cr-save-one, .cr-save-next').prop('disabled', true);
		$.post(<?= json_encode($saveUrl) ?>, data)
			.done(function (res) {
				if (typeof res === 'string') {
					try { res = JSON.parse(res); } catch (e) { res = { status: 'fail', message: 'Could not save.' }; }
				}
				if (res.status === 'success') {
					markSaved(id);
					toast('success', res.message || 'Student saved.');
					if (thenNext) {
						var nxt = nextUnsaved(id) || neighbor(1);
						if (nxt) showStudent(nxt);
					}
				} else {
					toast('danger', res.message || 'Could not save this student.');
				}
			})
			.fail(function () {
				toast('danger', 'Could not save this student. Try again.');
			})
			.always(function () {
				$('#cr_submit_mode').val('all');
				$('#cr_ajax').val('0');
				$pane.find('.cr-save-one, .cr-save-next').prop('disabled', false);
			});
	}

	$(document).on('click', '.cr-stu', function () {
		showStudent(parseInt($(this).data('student'), 10));
	});
	$(document).on('click', '.cr-prev', function () { showStudent(neighbor(-1)); });
	$(document).on('click', '.cr-next', function () { showStudent(neighbor(1)); });
	$(document).on('click', '.cr-save-one', function () { saveStudent(false); });
	$(document).on('click', '.cr-save-next', function () { saveStudent(true); });
	$(document).on('change', '.cr-score-input', function () {
		var $act = $(this).closest('.cr-act');
		var score = parseInt($(this).val(), 10) || 0;
		var $c = $act.find('.skill-comment-input');
		var cur = $.trim($c.val());
		if (['', 'Excellent', 'Good', 'Needs Improvement'].indexOf(cur) !== -1) {
			$c.val(skillAutoComment(score));
		}
	});
	$('#frm_coord_review').on('submit', function (e) {
		if ($('#cr_ajax').val() === '1') {
			e.preventDefault();
			return false;
		}
		$('#cr_submit_mode').val('all');
		var unsaved = studentCount - $('.cr-stu.success').length;
		if (unsaved > 0) {
			if (!confirm(unsaved + ' student' + (unsaved === 1 ? ' is' : 's are') + ' not saved yet. Finalize the class review anyway?')) {
				e.preventDefault();
				return false;
			}
		} else if (!confirm('Lock this activity review for the class? You will not be able to edit after this.')) {
			e.preventDefault();
			return false;
		}
	});
})(jQuery);
</script>
