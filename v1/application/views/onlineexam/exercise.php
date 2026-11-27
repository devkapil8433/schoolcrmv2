<?php
	$branch_id = isset($branch_id) ? (int) $branch_id : 0;
	$exercise_flow = isset($exercise_flow) && is_array($exercise_flow) ? $exercise_flow : array();
	$exercise_count = isset($exercise_count) ? (int) $exercise_count : 0;
	$branch_query = $branch_id > 0 ? '?branch_id=' . $branch_id : '';
	$can_add = get_permission('exercise', 'is_add');
	$can_edit = get_permission('exercise', 'is_edit');
	$can_delete = get_permission('exercise', 'is_delete');
	$arrayClass = $branch_id > 0 ? $this->app_lib->getClass($branch_id) : array('' => translate('select_branch_first'));
	$arraySubjectEmpty = array('' => translate('select_class_first'));
	$rightCol = $can_add ? 7 : 12;
?>
<div class="row">
	<div class="col-md-12">
		<?php if (is_superadmin_loggedin()): ?>
			<?php if (!empty($branch_id) && !empty($branch_name)): ?>
			<section class="panel">
				<div class="panel-body clearfix">
					<a href="<?=base_url('onlineexam/exercise')?>" class="btn-portal-secondary btn-portal-sm pull-right"><i class="fas fa-edit"></i> Change</a>
					<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
				</div>
			</section>
			<?php else: ?>
			<section class="panel">
				<header class="panel-heading">
					<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><?=translate('branch')?></h4>
				</header>
				<div class="panel-body">
					<?php echo form_open(current_url(), array('method' => 'get', 'class' => 'form-inline')); ?>
					<div class="form-group">
						<label class="portal-label"><?=translate('branch')?></label>
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='portal-input' id='branch_filter_id' data-plugin-selectTwo data-width='200px'");
						?>
					</div>
					<button type="submit" class="btn-portal-secondary"><i class="fas fa-check"></i> <?=translate('select')?></button>
					<?php echo form_close(); ?>
				</div>
			</section>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<?php if ($branch_id > 0): ?>
<div class="row">
	<?php if ($can_add): ?>
	<div class="col-md-5">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="far fa-edit"></i> <?=translate('add') . ' ' . translate('exercise')?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string() . $branch_query); ?>
			<div class="panel-body">
				<?php if (is_superadmin_loggedin()): ?>
				<input type="hidden" name="branch_id" value="<?=(int) $branch_id?>">
				<?php endif; ?>
				<div class="form-group">
					<label class="portal-label"><?=translate('class')?> <span class="required">*</span></label>
					<?php
						echo form_dropdown('class_id', $arrayClass, set_value('class_id'), "class='portal-input' id='class_id' data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"><?=form_error('class_id')?></span>
				</div>
				<div class="form-group">
					<label class="portal-label"><?=translate('subject')?> <span class="required">*</span></label>
					<?php
						$arraySubject = $arraySubjectEmpty;
						if (!empty(set_value('class_id'))) {
							$arraySubject = array('' => translate('select'));
							$query = $this->onlineexam_model->getSubjectByClass(set_value('class_id'));
							foreach ($query->result_array() as $row) {
								$arraySubject[$row['subject_id']] = $row['subjectname'];
							}
						}
						echo form_dropdown('subject_id', $arraySubject, set_value('subject_id'), "class='portal-input' id='subject_id' data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"><?=form_error('subject_id')?></span>
				</div>
				<div class="form-group mb-md">
					<label class="portal-label"><?=translate('exercise_name')?> <span class="required">*</span></label>
					<input type="text" class="portal-input" name="exercise_name" value="<?=set_value('exercise_name')?>" placeholder="e.g. Exercise 1" />
					<span class="error"><?=form_error('exercise_name')?></span>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12">
						<button class="btn-portal-secondary flex justify-end gap-2" type="submit" name="exercise" value="1">
							<i class="fas fa-plus-circle"></i> <?=translate('save')?>
						</button>
					</div>
				</div>
			</footer>
			<?php echo form_close(); ?>
		</section>

		<section class="panel">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-layer-group"></i> Bulk Create Exercises</h4>
			</header>
			<?php echo form_open($this->uri->uri_string() . $branch_query, array('id' => 'exerciseBulkForm')); ?>
			<div class="panel-body">
				<?php if (is_superadmin_loggedin()): ?>
				<input type="hidden" name="branch_id" value="<?=(int) $branch_id?>">
				<?php endif; ?>
				<div class="form-group">
					<label class="portal-label"><?=translate('class')?> <span class="required">*</span></label>
					<?php
						echo form_dropdown('bulk_class_id', $arrayClass, set_value('bulk_class_id'), "class='portal-input' id='bulk_class_id' data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"><?=form_error('bulk_class_id')?></span>
				</div>
				<div class="form-group">
					<label class="portal-label"><?=translate('subject')?> <span class="required">*</span></label>
					<?php
						$arrayBulkSubject = $arraySubjectEmpty;
						if (!empty(set_value('bulk_class_id'))) {
							$arrayBulkSubject = array('' => translate('select'));
							$query = $this->onlineexam_model->getSubjectByClass(set_value('bulk_class_id'));
							foreach ($query->result_array() as $row) {
								$arrayBulkSubject[$row['subject_id']] = $row['subjectname'];
							}
						}
						echo form_dropdown('bulk_subject_id', $arrayBulkSubject, set_value('bulk_subject_id'), "class='portal-input' id='bulk_subject_id' data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"><?=form_error('bulk_subject_id')?></span>
				</div>

				<div class="form-group">
					<label class="portal-label">Mode</label>
					<div>
						<label class="radio-inline mr-md">
							<input type="radio" name="bulk_mode" value="generate" class="bulk-mode-radio" <?= set_value('bulk_mode', 'generate') === 'list' ? '' : 'checked' ?>> Generate series
						</label>
						<label class="radio-inline">
							<input type="radio" name="bulk_mode" value="list" class="bulk-mode-radio" <?= set_value('bulk_mode') === 'list' ? 'checked' : '' ?>> Paste list
						</label>
					</div>
				</div>

				<div id="bulkModeGenerate" class="bulk-mode-panel">
					<div class="form-group">
						<label class="portal-label">Prefix</label>
						<input type="text" class="portal-input" name="bulk_prefix" id="bulk_prefix" value="<?=html_escape(set_value('bulk_prefix', 'Exercise'))?>" placeholder="Exercise">
					</div>
					<div class="row">
						<div class="col-xs-6">
							<div class="form-group">
								<label class="portal-label">From</label>
								<input type="number" class="portal-input" name="bulk_from" id="bulk_from" value="<?=html_escape(set_value('bulk_from', '1'))?>" min="1" max="999">
							</div>
						</div>
						<div class="col-xs-6">
							<div class="form-group">
								<label class="portal-label">To</label>
								<input type="number" class="portal-input" name="bulk_to" id="bulk_to" value="<?=html_escape(set_value('bulk_to', '10'))?>" min="1" max="999">
							</div>
						</div>
					</div>
					<p class="help-block text-muted mb-none" id="bulkPreviewHint">Will create: Exercise 1 … Exercise 10</p>
				</div>

				<div id="bulkModeList" class="bulk-mode-panel" style="display:none;">
					<div class="form-group mb-sm">
						<label class="portal-label">Exercise names <span class="required">*</span></label>
						<textarea class="portal-input" name="bulk_names" id="bulk_names" rows="8" placeholder="Exercise 1&#10;Exercise 2&#10;Chapter Test&#10;Worksheet A"><?=html_escape(set_value('bulk_names'))?></textarea>
						<p class="help-block text-muted mb-none">One name per line. Duplicates are skipped.</p>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12">
						<button class="btn-portal-secondary" type="submit" name="exercise_bulk" value="1">
							<i class="fas fa-layer-group"></i> Create All
						</button>
					</div>
				</div>
			</footer>
			<?php echo form_close(); ?>
		</section>
	</div>
	<?php endif; ?>

	<div class="col-md-<?=$rightCol?>">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2">
					<i class="fas fa-list-ul"></i> <?=translate('exercise_list')?>
					<span class="badge"><?=$exercise_count?></span>
				</h4>
			</header>
			<div class="panel-body">
				<?php if (!empty($exercise_flow)): ?>
				<div class="panel-group" id="exerciseFlowAccordion">
					<?php foreach ($exercise_flow as $classNode):
						$classId = (int) $classNode['class_id'];
						$classPanelId = 'exercise_class_' . $classId;
					?>
					<div class="panel panel-default">
						<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#exerciseFlowAccordion" data-target="#<?=$classPanelId?>" role="button" style="cursor:pointer;">
							<h5 class="panel-title" style="margin:0; font-size:14px; line-height:22px;">
								<span class="label label-primary" title="<?=translate('class')?>"><i class="fas fa-school"></i></span>
								<strong><?=html_escape($classNode['class_name'])?></strong>
								<span class="pull-right">
									<span class="label label-default" title="<?=translate('subject')?>"><i class="fas fa-book"></i> <?=count($classNode['subjects'])?></span>
									<span class="label label-warning" title="<?=translate('exercise')?>"><i class="fas fa-layer-group"></i> <?=(int) $classNode['exercise_count']?></span>
									<span class="label label-info" title="<?=translate('question')?>"><i class="fas fa-circle-question"></i> <?=(int) $classNode['question_count']?></span>
									<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
								</span>
							</h5>
						</div>
						<div id="<?=$classPanelId?>" class="accordion-body collapse">
							<div class="panel-body">
								<div class="panel-group" id="exerciseSubjects_<?=$classId?>">
									<?php foreach ($classNode['subjects'] as $subject):
										$subjectId = (int) $subject['subject_id'];
										$subjectPanelId = $classPanelId . '_subject_' . $subjectId;
									?>
									<div class="panel panel-default">
										<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#exerciseSubjects_<?=$classId?>" data-target="#<?=$subjectPanelId?>" role="button" style="cursor:pointer;">
											<h5 class="panel-title" style="margin:0; font-size:13px; line-height:20px;">
												<span class="label label-warning" title="<?=translate('subject')?>"><i class="fas fa-book"></i></span>
												<strong><?=html_escape($subject['subject_name'])?></strong>
												<span class="pull-right">
													<span class="label label-default" title="<?=translate('exercise')?>"><i class="fas fa-layer-group"></i> <?=(int) $subject['exercise_count']?></span>
													<span class="label label-info" title="<?=translate('question')?>"><i class="fas fa-circle-question"></i> <?=(int) $subject['question_count']?></span>
													<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
												</span>
											</h5>
										</div>
										<div id="<?=$subjectPanelId?>" class="accordion-body collapse">
											<div class="panel-body">
												<?php if ($can_add): ?>
												<div class="text-right mb-sm">
													<button type="button" class="btn-portal-secondary btn-portal-sm ex-quick-add"
														data-class-id="<?=$classId?>"
														data-subject-id="<?=$subjectId?>">
														<i class="fas fa-plus"></i> <?=translate('add') . ' ' . translate('exercise')?>
													</button>
												</div>
												<?php endif; ?>
												<div class="portal-table-wrap">
													<table class="portal-table">
														<thead>
															<tr>
																<th width="45"><?=translate('sl')?></th>
																<th><?=translate('name')?></th>
																<th width="90"><?=translate('question')?></th>
																<th width="110"><?=translate('action')?></th>
															</tr>
														</thead>
														<tbody>
															<?php $sn = 1; foreach ($subject['exercises'] as $ex): ?>
															<tr>
																<td><?=$sn++?></td>
																<td><span class="label label-info"><?=html_escape($ex['name'])?></span></td>
																<td><span class="label label-default"><?=(int) $ex['question_count']?></span></td>
																<td class="action">
																	<?php if ($can_edit): ?>
																	<a class="btn-portal-secondary btn-portal-sm" href="javascript:void(0);" title="<?=translate('edit')?>" onclick="getExercise('<?=(int) $ex['id']?>')"><i class="fas fa-pen-nib"></i></a>
																	<?php endif; ?>
																	<?php if ($can_delete): ?>
																	<?=btn_delete('onlineexam/exercise_delete/' . (int) $ex['id'])?>
																	<?php endif; ?>
																</td>
															</tr>
															<?php endforeach; ?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php else: ?>
				<div class="alert alert-subl text-center mb-none"><?=translate('no_information_available')?></div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>
<?php elseif (!is_superadmin_loggedin()): ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<div class="panel-body">
				<div class="alert alert-subl text-center mb-none"><?=translate('no_information_available')?></div>
			</div>
		</section>
	</div>
</div>
<?php endif; ?>

<?php if ($can_edit): ?>
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-edit"></i> <?=translate('edit') . ' ' . translate('exercise')?></h4>
		</header>
		<?php echo form_open('onlineexam/exercise_edit', array('class' => 'frm-submit')); ?>
			<div class="panel-body">
				<input type="hidden" name="exercise_id" id="eexercise_id" value="">
				<?php if (is_superadmin_loggedin()): ?>
					<div class="form-group">
						<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown('branch_id', $arrayBranch, '', "class='form-control' id='ebranch_id' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
					</div>
				<?php else: ?>
					<input type="hidden" name="branch_id" value="<?=(int) $branch_id?>">
				<?php endif; ?>
				<div class="form-group">
					<label class="control-label"><?=translate('class')?> <span class="required">*</span></label>
					<?php
						echo form_dropdown('class_id', $arrayClass, '', "class='form-control' id='eclass_id' data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"></span>
				</div>
				<div class="form-group">
					<label class="control-label"><?=translate('subject')?> <span class="required">*</span></label>
					<?php
						echo form_dropdown('subject_id', $arraySubjectEmpty, '', "class='form-control' id='esubject_id' data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"></span>
				</div>
				<div class="form-group mb-md">
					<label class="control-label"><?=translate('exercise_name')?> <span class="required">*</span></label>
					<input type="text" class="form-control" value="" name="exercise_name" id="eexercise_name" placeholder="e.g. Exercise 1" />
					<span class="error"></span>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<i class="fas fa-plus-circle"></i> <?=translate('update')?>
						</button>
						<button type="button" class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
					</div>
				</div>
			</footer>
		<?php echo form_close(); ?>
	</section>
</div>
<?php endif; ?>

<script type="text/javascript">
(function ($) {
	function loadSubjects($classEl, $subjectEl, selectedSubject, done) {
		var classID = $classEl.val();
		$subjectEl.html('<option value=""><?=translate("select")?></option>');
		if (!classID) {
			$subjectEl.trigger('change');
			if (typeof done === 'function') done();
			return;
		}
		$.post(base_url + 'onlineexam/getByClass', { classID: classID }, function (html) {
			$subjectEl.html('<option value=""><?=translate("select")?></option>' + html);
			if (selectedSubject) {
				$subjectEl.val(String(selectedSubject));
			}
			$subjectEl.trigger('change');
			if (typeof done === 'function') done();
		});
	}

	$('#class_id').on('change', function () {
		loadSubjects($('#class_id'), $('#subject_id'), null);
	});

	$('#bulk_class_id').on('change', function () {
		loadSubjects($('#bulk_class_id'), $('#bulk_subject_id'), null);
	});

	function updateBulkPreview() {
		var prefix = $.trim($('#bulk_prefix').val() || 'Exercise');
		var from = parseInt($('#bulk_from').val(), 10) || 1;
		var to = parseInt($('#bulk_to').val(), 10) || from;
		if (to < from) to = from;
		var count = to - from + 1;
		$('#bulkPreviewHint').text('Will create: ' + prefix + ' ' + from + ' … ' + prefix + ' ' + to + ' (' + count + ')');
	}

	function toggleBulkMode() {
		var mode = $('input[name="bulk_mode"]:checked').val();
		if (mode === 'list') {
			$('#bulkModeGenerate').hide();
			$('#bulkModeList').show();
		} else {
			$('#bulkModeList').hide();
			$('#bulkModeGenerate').show();
			updateBulkPreview();
		}
	}

	$('.bulk-mode-radio').on('change', toggleBulkMode);
	$('#bulk_prefix, #bulk_from, #bulk_to').on('input change', updateBulkPreview);
	toggleBulkMode();

	$('.ex-quick-add').on('click', function () {
		var classId = $(this).data('class-id');
		var subjectId = $(this).data('subject-id');
		$('#class_id').val(String(classId)).trigger('change');
		$('#bulk_class_id').val(String(classId)).trigger('change');
		setTimeout(function () {
			$('#subject_id').val(String(subjectId)).trigger('change');
			$('#bulk_subject_id').val(String(subjectId)).trigger('change');
			$('input[name="exercise_name"]').focus();
		}, 350);
	});

	$('#ebranch_id').on('change', function () {
		if (window._fillingExerciseEdit) {
			return;
		}
		var branchID = $(this).val();
		$.post(base_url + 'ajax/getClassByBranch', { branch_id: branchID }, function (html) {
			$('#eclass_id').html(html).trigger('change');
		});
	});

	// Do not auto-clear subject on edit class change while modal is filling;
	// only wire for user changes after modal is open.
	$('#eclass_id').on('change', function () {
		if (window._fillingExerciseEdit) {
			return;
		}
		loadSubjects($('#eclass_id'), $('#esubject_id'), null);
	});

	window.getExercise = function (id) {
		$.ajax({
			url: base_url + 'onlineexam/exerciseDetails',
			type: 'POST',
			data: { id: id },
			dataType: 'json',
			success: function (data) {
				if (!data || !data.id) {
					return;
				}
				window._fillingExerciseEdit = true;
				$('#eexercise_id').val(data.id);
				$('#eexercise_name').val(data.name);
				var classId = data.class_id ? String(data.class_id) : '';
				var subjectId = data.subject_id ? String(data.subject_id) : '';

				function finishFill() {
					window._fillingExerciseEdit = false;
					mfp_modal('#modal');
				}

				function fillClassSubject() {
					if (!classId) {
						finishFill();
						return;
					}
					$('#eclass_id').val(classId).trigger('change.select2');
					loadSubjects($('#eclass_id'), $('#esubject_id'), subjectId, finishFill);
				}

				if ($('#ebranch_id').length) {
					var branchId = data.branch_id ? String(data.branch_id) : '';
					$('#ebranch_id').val(branchId).trigger('change.select2');
					$.post(base_url + 'ajax/getClassByBranch', { branch_id: branchId }, function (html) {
						$('#eclass_id').html(html);
						fillClassSubject();
					}).fail(finishFill);
				} else {
					fillClassSubject();
				}
			}
		});
	};
})(jQuery);
</script>
