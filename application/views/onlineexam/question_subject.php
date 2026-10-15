<?php
	$branch_query = (!empty($branch_id)) ? '?branch_id=' . urlencode($branch_id) : '';
	$filter_exercise_id = isset($filter_exercise_id) ? (int) $filter_exercise_id : 0;
	$question_class_url = base_url('onlineexam/question_class/' . $class_id . '/' . $section_id) . $branch_query;
	$add_params = array(
		'branch_id' => (int) $branch_id,
		'class_id' => (int) $class_id,
		'section_id' => (int) $section_id,
		'subject_id' => (int) $subject_id,
	);
	if ($filter_exercise_id > 0) {
		$add_params['exercise_id'] = $filter_exercise_id;
	}
	$add_question_url = base_url('onlineexam/question_add') . '?' . http_build_query($add_params);
	$question_bank_url = base_url('onlineexam/question') . '?' . http_build_query($add_params);
	$show_lesson_col = !empty($question_subject_show_lesson);
	$table_cols = $show_lesson_col ? 5 : 4;
?>
<div class="row">
	<div class="col-md-12">
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body clearfix">
				<a href="<?=base_url('onlineexam/question') . $branch_query?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
			</div>
		</section>
		<?php endif; ?>

		<section class="panel" data-appear-animation-type="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title">
					<div class="pull-right">
						<a href="<?=$question_bank_url?>" class="btn btn-default btn-sm"><i class="fas fa-filter"></i> <?=translate('question') . " " . translate('list')?></a>
						<a href="<?=$question_class_url?>" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> <?=translate('back')?></a>
						<?php if (get_permission('question_bank', 'is_add')) { ?>
						<a href="<?=$add_question_url?>" class="btn btn-default btn-sm"><i class="fas fa-plus-circle"></i> <?=translate('add') . " " . translate('question')?></a>
						<?php } ?>
					</div>
					<i class="fas fa-circle-question"></i> <?=translate('question')?> — <?=html_escape($subject_name)?>
				</h4>
			</header>
			<div class="panel-body">
				<style type="text/css">
					.question-cell img { max-width: 100%; height: auto; }
					.question-cell { word-break: break-word; }
				</style>
				<div class="portal-table-wrap">
					<table class="table table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th width="50"><?=translate('sl')?></th>
								<th width="140"><?=translate('question_type')?></th>
								<?php if ($show_lesson_col): ?>
								<th width="200"><?=translate('lesson')?></th>
								<?php endif; ?>
								<th><?=translate('question')?></th>
								<th width="160"><?=translate('action')?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if (!empty($questions_list)):
								$sn = 1;
								foreach ($questions_list as $q):
									$qt = (int) $q['type'];
									$typeLabel = $this->onlineexam_model->resolve_question_type_label(
										$qt,
										isset($q['custom_question_type']) ? $q['custom_question_type'] : ''
									);
							?>
							<tr>
								<td><?=$sn++?></td>
								<td><span class="label label-default"><?=html_escape($typeLabel)?></span></td>
								<?php if ($show_lesson_col): ?>
								<td class="text-muted small"><?php
									if (!empty($q['lesson_id'])) {
										$ln = isset($q['lesson_no']) ? trim((string) $q['lesson_no']) : '';
										$lname = isset($q['lesson_name']) ? trim((string) $q['lesson_name']) : '';
										echo html_escape(trim($ln . ($ln !== '' ? '. ' : '') . $lname));
									} else {
										echo '—';
									}
								?></td>
								<?php endif; ?>
								<td class="question-cell"><?php
								if ((int) $q['type'] === 5) {
									$qDec = json_decode($q['question'], true);
									if (is_array($qDec) && isset($qDec['instruction']) && $qDec['instruction'] !== '') {
										echo $qDec['instruction'];
									} else {
										echo $q['question'];
									}
								} else {
									echo $q['question'];
								}
								?></td>
								<td>
									<button type="button" class="btn btn-circle btn-default btn-sm icon" data-toggle="tooltip" title="<?=translate('view')?>" data-loading-text="<i class='fas fa-spinner fa-spin'></i>" onclick="getQuestion(<?=(int)$q['id']?>, this);"><i class="fas fa-bars"></i></button>
									<?php if (get_permission('question_bank', 'is_edit')) { ?>
									<a href="<?=base_url('onlineexam/question_edit/' . (int)$q['id'])?>" class="btn btn-circle btn-default btn-sm" title="<?=translate('edit')?>"><i class="fas fa-pen-nib"></i></a>
									<?php } ?>
									<?php if (get_permission('question_bank', 'is_delete')) { ?>
									<?=btn_delete('onlineexam/question_delete/' . (int)$q['id'])?>
									<?php } ?>
								</td>
							</tr>
							<?php
								endforeach;
							else:
							?>
							<tr>
								<td colspan="<?php echo (int) $table_cols; ?>" class="text-center text-muted"><?=translate('no_information_available')?>. <?php if (get_permission('question_bank', 'is_add')) { ?><a href="<?=$add_question_url?>"><?=translate('add') . " " . translate('question')?></a><?php } ?></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>

<div class="zoom-anim-dialog portal-modal portal-modal-lg mfp-hide payroll-t-modal" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-file-circle-question"></i> <?php echo translate('question') . " " . translate('view'); ?></h4>
		</header>
		<div class="panel-body">
			<div id="quick_view"></div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
				</div>
			</div>
		</footer>
	</section>
</div>
