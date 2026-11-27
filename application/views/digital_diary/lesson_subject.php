<?php
	$add_lesson_url = base_url('digital_diary/lesson_add') . '?class_id=' . $class_id . '&section_id=' . $section_id . '&subject_id=' . $subject_id . (!empty($branch_id) ? '&branch_id=' . urlencode($branch_id) : '');
	$lesson_class_url = base_url('digital_diary/lesson_class/' . $class_id . '/' . $section_id) . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '');
?>
?>
<style type="text/css">
	.lesson-list-card {
		background: #fff;
		border-radius: 15px;
		box-shadow: 0 4px 20px rgba(0,0,0,0.05);
		border: none;
	}
	.lesson-list-card .panel-heading {
		background: #fff;
		border-bottom: 1px solid #f0f0f0;
		padding: 20px 25px;
		border-radius: 15px 15px 0 0;
	}
	.lesson-list-card .panel-title {
		font-weight: 800;
		font-size: 18px;
		color: #333;
	}
	.table-modern {
		margin-bottom: 0;
	}
	.table-modern thead th {
		background: #f8f9fc;
		border-bottom: 2px solid #edf2f9 !important;
		color: #4a5568;
		font-weight: 700;
		text-transform: uppercase;
		font-size: 11px;
		letter-spacing: 0.5px;
		padding: 15px !important;
	}
	.table-modern tbody td {
		padding: 15px !important;
		vertical-align: middle !important;
		border-bottom: 1px solid #edf2f9 !important;
	}
	.table-modern tbody tr:last-child td {
		border-bottom: none !important;
	}
	.lesson-number {
		background: #ebf4ff;
		color: #3182ce;
		width: 30px;
		height: 30px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 8px;
		font-weight: 700;
		font-size: 12px;
	}
	.lesson-name-text {
		font-weight: 600;
		color: #2d3748;
		font-size: 14px;
	}
	#lessonSubjectTable .col-sequence { display: none; }
	#lessonSubjectTable.rearrange-mode .col-sequence { display: table-cell; }
	
	.status-badge {
		padding: 5px 12px;
		border-radius: 20px;
		font-size: 11px;
		font-weight: 700;
	}
	.status-completed { background: #e6fffa; color: #38b2ac; }
	.status-pending { background: #f7fafc; color: #a0aec0; }
</style>
<div class="row">
	<div class="col-md-12">
		<section class="panel lesson-list-card" data-appear-animation-type="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<div class="pull-left">
					<h4 class="panel-title"><i class="fas fa-book-reader text-primary mr-xs"></i> Lessons: <?=html_escape($subject_name)?></h4>
					<p class="text-muted mb-none mt-xs"><?=html_escape($class_name)?> (<?=html_escape($section_name)?>)</p>
				</div>
				<div class="panel-btn">
					<button type="button" id="btnRearrangeOrder" class="btn btn-default btn-circle" title="Change sorting"><i class="fas fa-sort-amount-down"></i> <span id="btnRearrangeLabel">Sort Order</span></button>
					<a href="<?=$lesson_class_url?>" class="btn btn-default btn-circle"><i class="fas fa-chevron-left"></i> <?=translate('back')?></a>
					<a href="<?=$add_lesson_url?>" class="btn btn-primary btn-circle"><i class="fas fa-plus"></i> <?=translate('add_lesson')?></a>
				</div>
			</header>
			<div class="panel-body" style="padding: 0;">
				<div class="table-responsive">
					<table class="table table-modern" id="lessonSubjectTable">
						<thead>
							<tr>
								<th width="60"><?=translate('sl')?></th>
								<th width="120"><?=translate('lesson_no')?></th>
								<th><?=translate('lesson_name')?></th>
								<th width="120"><?=translate('status')?></th>
								<th width="150" class="col-sequence text-center">Sequence</th>
								<th width="120" class="text-center"><?=translate('action')?></th>
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
							<tr data-lesson-id="<?=(int)$lesson['id']?>">
								<td><span class="text-muted" style="font-size: 12px;"><?=$sn++?></span></td>
								<td><span class="lesson-number"><?=html_escape($lesson['lesson_no'])?></span></td>
								<td><span class="lesson-name-text"><?=html_escape($lesson['lesson_name'])?></span></td>
								<td>
									<?php if ($is_done): ?>
										<span class="status-badge status-completed"><i class="fas fa-check-circle"></i> Done</span>
									<?php else: ?>
										<span class="status-badge status-pending">Pending</span>
									<?php endif; ?>
								</td>
								<td class="lesson-sequence col-sequence text-center">
									<div class="btn-group">
										<button type="button" class="btn btn-default btn-sm btn-move-up" title="Move up" data-id="<?=(int)$lesson['id']?>" <?=!$can_up ? 'disabled' : ''?>><i class="fas fa-arrow-up"></i></button>
										<button type="button" class="btn btn-default btn-sm btn-move-down" title="Move down" data-id="<?=(int)$lesson['id']?>" <?=!$can_down ? 'disabled' : ''?>><i class="fas fa-arrow-down"></i></button>
									</div>
								</td>
								<td class="text-center">
									<a href="<?=base_url('digital_diary/lesson_edit/' . $lesson['id']) . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '')?>" class="btn btn-default btn-circle btn-sm" title="Edit"><i class="fas fa-pencil-alt"></i></a>
									<?=btn_delete('digital_diary/lesson_delete/' . $lesson['id'])?>
								</td>
							</tr>
							<?php
								endforeach;
							else:
							?>
							<tr>
								<td colspan="6" class="text-center" style="padding: 40px !important;">
									<img src="<?=base_url('assets/images/empty-state.png')?>" style="width: 80px; opacity: 0.5; margin-bottom: 15px;" onerror="this.src='https://cdn-icons-png.flaticon.com/512/7486/7486744.png'">
									<p class="text-muted">No lessons added for this subject yet.</p>
									<a href="<?=$add_lesson_url?>" class="btn btn-primary btn-sm mt-sm">Add First Lesson</a>
								</td>
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
