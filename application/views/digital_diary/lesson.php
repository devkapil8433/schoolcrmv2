<?php
	$branch_id = isset($branch_id) ? $branch_id : '';
	$lesson_flow = isset($lesson_flow) ? $lesson_flow : array();
	$completed_lesson_ids = isset($completed_lesson_ids) ? $completed_lesson_ids : array();
	$branch_query = (!empty($branch_id)) ? '?branch_id=' . urlencode($branch_id) : '';
	$add_url = base_url('digital_diary/lesson_add') . $branch_query;
?>
<style type="text/css">
	.badge-custom {
		font-weight: 700 !important;
		font-size: 12px !important;
		padding: 5px 12px !important;
		border-radius: 50px !important;
		box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		display: inline-block;
		vertical-align: middle;
		margin-left: 5px;
		text-transform: capitalize;
	}
	.badge-subject {
		background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%) !important;
		color: #fff !important;
	}
	.badge-lesson {
		background: linear-gradient(135deg, #00BCD4 0%, #0097A7 100%) !important;
		color: #fff !important;
	}
	.badge-done {
		background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%) !important;
		color: #fff !important;
	}
	.badge-pending {
		background: linear-gradient(135deg, #9E9E9E 0%, #757575 100%) !important;
		color: #fff !important;
	}
	.panel-heading.collapsed .toggle-indicator {
		transform: rotate(0deg);
		transition: transform 0.3s;
	}
	.panel-heading:not(.collapsed) .toggle-indicator {
		transform: rotate(180deg);
		transition: transform 0.3s;
	}
</style>
<div class="row">
	<div class="col-md-12">
		<?php if (is_superadmin_loggedin()): ?>
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body">
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
				<a href="<?=base_url('digital_diary/lesson')?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
			</div>
		</section>
		<?php else: ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('branch')?></h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(current_url(), array('method' => 'get', 'class' => 'form-inline')); ?>
				<div class="form-group">
					<label class="control-label"><?=translate('branch')?></label>
					<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown("branch_id", $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width='200px'");
					?>
				</div>
				<button type="submit" class="btn btn-default"> <i class="fas fa-filter"></i> <?=translate('filter')?></button>
				<?php echo form_close(); ?>
			</div>
		</section>
		<?php endif; ?>
		<?php endif; ?>

		<section class="panel" data-appear-animation-type="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-layer-group"></i> <?=translate('lesson')?></h4>
				<div class="panel-btn">
					<a href="<?=$add_url?>" class="btn btn-default btn-circle">
						<i class="fas fa-plus-circle"></i> <?=translate('add_lesson')?>
					</a>
				</div>
			</header>
			<div class="panel-body">
				<?php if (!empty($lesson_flow)): ?>
				<div class="panel-group" id="lessonFlowAccordion">
					<?php foreach ($lesson_flow as $cs):
						$csId = 'cs_' . (int) $cs['class_id'] . '_' . (int) $cs['section_id'];
						$subjectCount = count($cs['subjects']);
						$lessonCount = 0;
						$doneCount = 0;
						foreach ($cs['subjects'] as $subj) {
							$lessonCount += count($subj['lessons']);
							foreach ($subj['lessons'] as $l) {
								if (in_array((int) $l['id'], $completed_lesson_ids, true)) {
									$doneCount++;
								}
							}
						}
						$csPct = $lessonCount > 0 ? round($doneCount * 100 / $lessonCount) : 0;
					?>
					<div class="panel panel-default">
						<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#lessonFlowAccordion" data-target="#<?=$csId?>" role="button" style="cursor: pointer; padding: 10px 14px;">
							<h5 class="panel-title" style="font-size: 14px; line-height: 22px; margin: 0;">
								<i class="fas fa-school text-primary" style="margin-right: 6px;"></i>
								<strong><?=html_escape($cs['class_name'])?></strong>
								<span class="text-muted">&mdash;</span>
								<?=html_escape($cs['section_name'])?>
								<span class="pull-right hidden-xs">
									<span class="badge-custom badge-subject"><?=$subjectCount?> <?=$subjectCount === 1 ? 'Subject' : 'Subjects'?></span>
									<span class="badge-custom badge-lesson"><?=$lessonCount?> <?=$lessonCount === 1 ? 'Lesson' : 'Lessons'?></span>
									<span class="badge-custom badge-done"><?=$doneCount?>/<?=$lessonCount?> Done (<?=$csPct?>%)</span>
									<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left: 10px; width: 12px;"></i>
								</span>
							</h5>
						</div>
						<div id="<?=$csId?>" class="accordion-body collapse">
							<div class="panel-body" style="padding: 10px 12px;">
								<?php if (!empty($cs['subjects'])): ?>
								<div class="panel-group" id="subjAcc_<?=$csId?>">
									<?php foreach ($cs['subjects'] as $subj):
										$subjId = $csId . '_subj_' . (int) $subj['subject_id'];
										$add_lesson_url = base_url('digital_diary/lesson_add')
											. '?class_id=' . (int) $cs['class_id']
											. '&section_id=' . (int) $cs['section_id']
											. '&subject_id=' . (int) $subj['subject_id']
											. (!empty($branch_id) ? '&branch_id=' . urlencode($branch_id) : '');
										$totalLessons = count($subj['lessons']);
										$subjDone = 0;
										foreach ($subj['lessons'] as $l) {
											if (in_array((int) $l['id'], $completed_lesson_ids, true)) {
												$subjDone++;
											}
										}
									?>
									<div class="panel panel-default">
										<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#subjAcc_<?=$csId?>" data-target="#<?=$subjId?>" role="button" style="cursor: pointer; padding: 8px 12px;">
											<h5 class="panel-title" style="font-size: 13px; line-height: 20px; margin: 0;">
												<i class="fas fa-book-open text-warning" style="margin-right: 6px;"></i>
												<strong><?=html_escape($subj['subject_name'])?></strong>
												<?php if (!empty($subj['subject_group'])): ?>
													<small class="text-muted">(<?=html_escape($subj['subject_group'])?>)</small>
												<?php endif; ?>
												<span class="pull-right hidden-xs">
													<span class="badge-custom badge-lesson"><?=$totalLessons?> <?=$totalLessons === 1 ? 'Lesson' : 'Lessons'?></span>
													<span class="badge-custom <?= ($totalLessons > 0 && $subjDone === $totalLessons) ? 'badge-done' : 'badge-pending' ?>"><?=$subjDone?>/<?=$totalLessons?> Done</span>
													<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left: 10px; width: 12px;"></i>
												</span>
											</h5>
										</div>
										<div id="<?=$subjId?>" class="accordion-body collapse">
											<div class="panel-body" style="padding: 10px 12px;">
												<div class="clearfix" style="margin-bottom: 8px;">
													<div class="pull-right">
														<button type="button" class="btn btn-default btn-xs btn-rearrange" data-table="#table_<?=$subjId?>" title="Rearrange order">
															<i class="fas fa-sort"></i> <span class="btn-rearrange-label">Rearrange</span>
														</button>
														<a href="<?=$add_lesson_url?>" class="btn btn-default btn-xs" title="<?=translate('add_lesson')?>">
															<i class="fas fa-plus"></i> <?=translate('add_lesson')?>
														</a>
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-bordered table-condensed table-hover" id="table_<?=$subjId?>">
														<thead>
															<tr>
																<th><?=translate('sl')?></th>
																<th><?=translate('lesson_no')?></th>
																<th><?=translate('lesson_name')?></th>
																<th><?=translate('status')?></th>
																<th class="col-sequence hidden">Sequence</th>
																<th><?=translate('action')?></th>
															</tr>
														</thead>
														<tbody>
															<?php if (!empty($subj['lessons'])):
																$sn = 1;
																foreach ($subj['lessons'] as $idx => $lesson):
																	$can_up = $idx > 0;
																	$can_down = $idx < $totalLessons - 1;
																	$is_done = in_array((int) $lesson['id'], $completed_lesson_ids, true);
															?>
															<tr data-lesson-id="<?=(int)$lesson['id']?>" class="<?= $is_done ? 'success' : '' ?>">
																<td><?=$sn++?></td>
																<td><?=html_escape($lesson['lesson_no'])?></td>
																<td><?=html_escape($lesson['lesson_name'])?></td>
																<td>
																	<?php if ($is_done): ?>
																	<span class="label label-success"><i class="fas fa-check"></i> Completed</span>
																	<?php else: ?>
																	<span class="label label-default">Pending</span>
																	<?php endif; ?>
																</td>
																<td class="col-sequence hidden">
																	<button type="button" class="btn btn-default btn-sm btn-move-up" title="Move up" data-id="<?=(int)$lesson['id']?>" <?= $can_up ? '' : 'disabled' ?>><i class="fas fa-arrow-up"></i></button>
																	<button type="button" class="btn btn-default btn-sm btn-move-down" title="Move down" data-id="<?=(int)$lesson['id']?>" <?= $can_down ? '' : 'disabled' ?>><i class="fas fa-arrow-down"></i></button>
																</td>
																<td>
																	<a href="<?=base_url('digital_diary/lesson_edit/' . $lesson['id']) . $branch_query?>" class="btn btn-circle btn-default btn-sm" title="Edit"><i class="fas fa-pen-nib"></i></a>
																	<?=btn_delete('digital_diary/lesson_delete/' . $lesson['id'])?>
																</td>
															</tr>
															<?php endforeach; else: ?>
															<tr>
																<td colspan="6" class="text-center text-muted">No lessons yet. <a href="<?=$add_lesson_url?>">Add Lesson</a></td>
															</tr>
															<?php endif; ?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									<?php endforeach; ?>
								</div>
								<?php else: ?>
								<p class="text-muted text-center">No subjects with lessons.</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php else: ?>
				<div class="text-center text-muted" style="padding: 30px 10px;">
					<p><i class="fas fa-book-open fa-3x"></i></p>
					<h4>No lessons found</h4>
					<p>Start building your lesson flow by adding the first lesson.</p>
					<a href="<?=$add_url?>" class="btn btn-primary btn-sm"><i class="fas fa-plus-circle"></i> <?=translate('add_lesson')?></a>
				</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function () {
	$(document).on('show.bs.collapse hide.bs.collapse', '.accordion-body', function (e) {
		if (e.target !== this) return;
		var $heading = $('[data-target="#' + this.id + '"]');
		var open = (e.type === 'show');
		$heading.find('.toggle-indicator')
			.toggleClass('fa-chevron-down', !open)
			.toggleClass('fa-chevron-up', open);
	});

	$(document).on('click', '.btn-rearrange', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var $btn = $(this);
		var $table = $($btn.data('table'));
		var isOn = !$btn.hasClass('btn-primary');
		$table.find('.col-sequence').toggleClass('hidden', !isOn);
		$btn.toggleClass('btn-primary', isOn);
		$btn.find('i').toggleClass('fa-sort', !isOn).toggleClass('fa-check', isOn);
		$btn.find('.btn-rearrange-label').text(isOn ? 'Done' : 'Rearrange');
	});

	$(document).on('click', '.btn-move-up, .btn-move-down', function (e) {
		e.preventDefault();
		if ($(this).prop('disabled')) return;
		var $btn = $(this);
		var id = $btn.data('id');
		var direction = $btn.hasClass('btn-move-up') ? 'up' : 'down';
		$btn.prop('disabled', true);
		var postData = { id: id, direction: direction };
		if (typeof csrfData !== 'undefined') {
			$.extend(postData, csrfData);
		}
		$.ajax({
			url: base_url + 'digital_diary/lesson_move',
			type: 'POST',
			data: postData,
			dataType: 'json'
		}).done(function (res) {
			if (res && res.status === 'success') {
				window.location.reload();
			} else {
				$btn.prop('disabled', false);
				var msg = (res && res.message) ? res.message : 'Could not change sequence';
				if (typeof alertMsg === 'function') alertMsg(msg, 'error');
				else alert(msg);
			}
		}).fail(function (xhr) {
			$btn.prop('disabled', false);
			var msg = 'Request failed';
			if (xhr.responseText && xhr.responseText.length < 200) msg = xhr.responseText;
			else if (xhr.status) msg = 'Request failed (' + xhr.status + ')';
			try {
				var r = JSON.parse(xhr.responseText);
				if (r && r.message) msg = r.message;
			} catch (err) {}
			if (typeof alertMsg === 'function') alertMsg(msg, 'error');
			else alert(msg);
		});
		return false;
	});
});
</script>
