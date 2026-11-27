<?php
	$paper = isset($paper) && is_array($paper) ? $paper : array();
	$questions = isset($paper_questions) && is_array($paper_questions) ? $paper_questions : array();
	$all_questions = isset($paper_all_questions) && is_array($paper_all_questions) ? $paper_all_questions : array();
	$branch_id = isset($branch_id) ? (int) $branch_id : 0;
	$branch_query = $branch_id > 0 ? ('?branch_id=' . urlencode($branch_id)) : '';
	$typeMap = $this->onlineexam_model->question_type();
	$selected_ids = array();
	foreach ($questions as $sq) {
		$selected_ids[] = (int) $sq['id'];
	}
	$selected_ids = array_values(array_unique($selected_ids));
?>
<style type="text/css">
	.qp-qpick-box { border: 1px solid #e5e5e5; border-radius: 4px; padding: 10px; max-height: 340px; overflow-y: auto; background: #fff; }
	.qp-qpick-item { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px; font-size: 13px; }
	.qp-qpick-item .qtxt { line-height: 1.3; }
	.qp-qpick-tools { margin: 6px 0 10px; }
</style>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-pen-to-square"></i> Edit Question Paper</h4>
				<div class="flex items-center gap-2">
					<a href="<?= base_url('onlineexam/question_paper_view/' . (int) $paper['id']) . $branch_query; ?>" class="btn-portal-secondary btn-portal-sm"><i class="fas fa-eye"></i> View</a>
					<a href="<?= base_url('onlineexam/question_paper_pdf/' . (int) $paper['id']) . $branch_query; ?>" class="btn-portal-secondary btn-portal-sm"><i class="fas fa-file-pdf"></i> Download PDF</a>
					<a href="<?= base_url('onlineexam/my_question_paper') . $branch_query; ?>" class="btn-portal-secondary btn-portal-sm"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<?php echo form_open('onlineexam/question_paper_update/' . (int) $paper['id'] . $branch_query); ?>
				<div class="row">
					<div class="col-md-6 mb-sm">
						<div class="form-group">
							<label class="portal-label">Title <span class="required">*</span></label>
							<input type="text" name="title" class="portal-input" required value="<?= html_escape(isset($paper['title']) ? $paper['title'] : ''); ?>">
						</div>
					</div>
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="portal-label">Exam Name</label>
							<input type="text" name="exam_name" class="portal-input" value="<?= html_escape(isset($paper['exam_name']) ? $paper['exam_name'] : ''); ?>">
						</div>
					</div>
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="portal-label">Exam Mode</label>
							<input type="text" name="exam_mode" class="portal-input" value="<?= html_escape(isset($paper['exam_mode']) ? $paper['exam_mode'] : ''); ?>">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="portal-label">Exam Type</label>
							<input type="text" name="exam_type" class="portal-input" value="<?= html_escape(isset($paper['exam_type']) ? $paper['exam_type'] : ''); ?>">
						</div>
					</div>
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="portal-label">Assessment</label>
							<input type="text" name="exam_assessment" class="portal-input" value="<?= html_escape(isset($paper['exam_assessment']) ? $paper['exam_assessment'] : ''); ?>">
						</div>
					</div>
					<div class="col-md-2 mb-sm">
						<div class="form-group">
							<label class="portal-label">Time</label>
							<input type="text" name="paper_time" class="portal-input" value="<?= html_escape(isset($paper['paper_time']) ? $paper['paper_time'] : ''); ?>">
						</div>
					</div>
					<div class="col-md-2 mb-sm">
						<div class="form-group">
							<label class="portal-label">Max Marks</label>
							<input type="text" name="max_marks" class="portal-input" value="<?= html_escape(isset($paper['max_marks']) ? $paper['max_marks'] : ''); ?>">
						</div>
					</div>
					<div class="col-md-2 mb-sm">
						<div class="form-group">
							<label class="portal-label">Publish Date</label>
							<input type="date" name="publish_date" class="portal-input" value="<?= html_escape(isset($paper['publish_date']) ? $paper['publish_date'] : ''); ?>">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-2 mb-sm">
						<div class="form-group">
							<label class="portal-label">Publish Time</label>
							<input type="time" name="publish_time" class="portal-input" value="<?= html_escape(isset($paper['publish_time']) ? $paper['publish_time'] : ''); ?>">
						</div>
					</div>
				</div>

				<hr>
				<h5 class="mt-md mb-xs"><i class="fas fa-list-check"></i> Manage Questions</h5>
				<p class="text-muted mb-sm">Uncheck to remove selected question, check to add more questions.</p>
				<div class="qp-qpick-tools">
					<button type="button" class="btn btn-xs btn-default" id="qp_sel_all">Select all</button>
					<button type="button" class="btn btn-xs btn-default" id="qp_unsel_all">Unselect all</button>
				</div>
				<div class="qp-qpick-box">
					<?php if (!empty($all_questions)): ?>
						<?php foreach ($all_questions as $aq): ?>
							<?php
								$qid = (int) $aq['id'];
								$qt = (int) (isset($aq['type']) ? $aq['type'] : 0);
								$label = $this->onlineexam_model->resolve_question_type_label(
									$qt,
									isset($aq['custom_question_type']) ? $aq['custom_question_type'] : ''
								);
								$chapter = isset($aq['exercise_name']) ? (string) $aq['exercise_name'] : '';
								$qtext = strip_tags(isset($aq['question']) ? (string) $aq['question'] : '');
								$qtext = preg_replace('/\s+/', ' ', $qtext);
								$qtext = trim((string) $qtext);
								if (strlen($qtext) > 200) {
									$qtext = substr($qtext, 0, 200) . '...';
								}
							?>
							<label class="qp-qpick-item">
								<input type="checkbox" class="qp-qcb" name="question_ids[]" value="<?= (int) $qid; ?>" <?= in_array($qid, $selected_ids, true) ? 'checked' : ''; ?>>
								<div class="qtxt">
									<strong><?= html_escape($label); ?></strong>
									<?php if ($chapter !== ''): ?><span class="text-muted"> | <?= html_escape($chapter); ?></span><?php endif; ?>
									<br><?= html_escape($qtext); ?>
								</div>
							</label>
						<?php endforeach; ?>
					<?php else: ?>
						<p class="text-muted mb-none">No question available for this class/section/subject.</p>
					<?php endif; ?>
				</div>
				<button type="submit" class="btn-portal-primary"><i class="fas fa-save"></i> Update</button>
				<?php echo form_close(); ?>

				<hr>
				<h5 class="mt-md mb-sm"><i class="fas fa-list-check"></i> Selected Questions</h5>
				<div class="portal-table-wrap">
					<table class="table table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th width="60">#</th>
								<th width="160">Type</th>
								<th width="200">Chapter / Lesson</th>
								<th>Question</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($questions)): ?>
								<?php $sn = 1; foreach ($questions as $q): ?>
									<tr>
										<td><?= (int) $sn++; ?></td>
										<td>
											<?php
												$qt = (int) (isset($q['type']) ? $q['type'] : 0);
												$label = $this->onlineexam_model->resolve_question_type_label(
													$qt,
													isset($q['custom_question_type']) ? $q['custom_question_type'] : ''
												);
												echo html_escape($label);
											?>
										</td>
										<td>
											<?= html_escape(isset($q['exercise_name']) ? $q['exercise_name'] : ''); ?>
											<?php if (!empty($q['lesson_id'])): ?>
												<br><span class="text-muted small">
													<?= html_escape(trim((isset($q['lesson_no']) ? $q['lesson_no'] : '') . '. ' . (isset($q['lesson_name']) ? $q['lesson_name'] : ''))); ?>
												</span>
											<?php endif; ?>
										</td>
										<td><?= isset($q['question']) ? $q['question'] : ''; ?></td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr><td colspan="4" class="text-center text-muted">No questions linked.</td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>
<script type="text/javascript">
(function ($) {
	'use strict';
	$('#qp_sel_all').on('click', function () {
		$('.qp-qcb').prop('checked', true);
	});
	$('#qp_unsel_all').on('click', function () {
		$('.qp-qcb').prop('checked', false);
	});
})(jQuery);
</script>
