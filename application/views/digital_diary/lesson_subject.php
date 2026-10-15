<?php
	$add_lesson_url = base_url('digital_diary/lesson_add') . '?class_id=' . $class_id . '&section_id=' . $section_id . '&subject_id=' . $subject_id . (!empty($branch_id) ? '&branch_id=' . urlencode($branch_id) : '');
	$lesson_class_url = base_url('digital_diary/lesson_class/' . $class_id . '/' . $section_id) . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '');
?>
<div class="row">
	<div class="col-md-12">
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body">
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
				<a href="<?=base_url('digital_diary/lesson') . '?branch_id=' . urlencode($branch_id)?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
			</div>
		</section>
		<?php endif; ?>

		<section class="panel" data-appear-animation-type="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-book"></i> Lessons — <?=html_escape($subject_name)?></h4>
				<div class="panel-btn">
					<button type="button" id="btnRearrangeOrder" class="btn btn-default btn-circle" title="Show sequence controls"><i class="fas fa-sort"></i> <span id="btnRearrangeLabel">Rearrange order</span></button>
					<a href="<?=$lesson_class_url?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
					<a href="<?=$add_lesson_url?>" class="btn btn-default btn-circle"><i class="fas fa-plus-circle"></i> <?=translate('add_lesson')?></a>
				</div>
			</header>
			<div class="panel-body">
				<style type="text/css">
					#lessonSubjectTable .col-sequence { display: none; }
					#lessonSubjectTable.rearrange-mode .col-sequence { display: table-cell; }
				</style>
				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-hover" id="lessonSubjectTable">
						<thead>
							<tr>
								<th><?=translate('sl')?></th>
								<th><?=translate('subject')?></th>
								<th><?=translate('lesson_no')?></th>
								<th><?=translate('lesson_name')?></th>
								<th class="col-sequence">Sequence</th>
								<th><?=translate('action')?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if (!empty($lessons)):
								$sn = 1;
								$total = count($lessons);
								$completed_lesson_ids = isset($completed_lesson_ids) ? $completed_lesson_ids : array();
								foreach ($lessons as $idx => $lesson):
									$can_up = $idx > 0;
									$can_down = $idx < $total - 1;
									$is_done = in_array((int) $lesson['id'], $completed_lesson_ids, true);
							?>
							<tr data-lesson-id="<?=(int)$lesson['id']?>" class="<?= $is_done ? 'success' : '' ?>">
								<td><?=$sn++?></td>
								<td><?=html_escape($lesson['subject_name'])?></td>
								<td><?=html_escape($lesson['lesson_no'])?></td>
								<td><?=html_escape($lesson['lesson_name'])?></td>
								<td class="lesson-sequence col-sequence">
									<?php if ($can_up): ?>
									<button type="button" class="btn btn-default btn-sm btn-move-up" title="Move up" data-id="<?=(int)$lesson['id']?>"><i class="fas fa-arrow-up"></i></button>
									<?php else: ?>
									<button type="button" class="btn btn-default btn-sm" disabled><i class="fas fa-arrow-up"></i></button>
									<?php endif; ?>
									<?php if ($can_down): ?>
									<button type="button" class="btn btn-default btn-sm btn-move-down" title="Move down" data-id="<?=(int)$lesson['id']?>"><i class="fas fa-arrow-down"></i></button>
									<?php else: ?>
									<button type="button" class="btn btn-default btn-sm" disabled><i class="fas fa-arrow-down"></i></button>
									<?php endif; ?>
								</td>
								<td>
									<a href="<?=base_url('digital_diary/lesson_edit/' . $lesson['id']) . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '')?>" class="btn btn-circle btn-default btn-sm" title="Edit"><i class="fas fa-pen-nib"></i></a>
									<?=btn_delete('digital_diary/lesson_delete/' . $lesson['id'])?>
								</td>
							</tr>
							<?php
								endforeach;
							else:
							?>
							<tr>
								<td colspan="6" class="text-center text-muted">No lessons yet. <a href="<?=$add_lesson_url?>">Add Lesson</a></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('#btnRearrangeOrder').on('click', function() {
		var $table = $('#lessonSubjectTable');
		var isOn = $table.toggleClass('rearrange-mode').hasClass('rearrange-mode');
		$(this).toggleClass('btn-primary', isOn);
		$(this).find('i').toggleClass('fa-sort', !isOn).toggleClass('fa-check', isOn);
		$('#btnRearrangeLabel').text(isOn ? 'Done' : 'Rearrange order');
		$(this).attr('title', isOn ? 'Hide sequence controls' : 'Show sequence controls');
	});
	$(document).on('click', '.btn-move-up, .btn-move-down', function(e) {
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
		}).done(function(res) {
			if (res && res.status === 'success') {
				window.location.reload();
			} else {
				$btn.prop('disabled', false);
				var msg = (res && res.message) ? res.message : 'Could not change sequence';
				if (typeof alertMsg === 'function') alertMsg(msg, 'error');
				else alert(msg);
			}
		}).fail(function(xhr, status, err) {
			$btn.prop('disabled', false);
			var msg = 'Request failed';
			if (xhr.responseText && xhr.responseText.length < 200) msg = xhr.responseText;
			else if (xhr.status) msg = 'Request failed (' + xhr.status + ')';
			try {
				var r = JSON.parse(xhr.responseText);
				if (r && r.message) msg = r.message;
			} catch (e) {}
			if (typeof alertMsg === 'function') alertMsg(msg, 'error');
			else alert(msg);
		});
		return false;
	});
});
</script>
