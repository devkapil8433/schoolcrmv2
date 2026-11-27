<?php
	$branch_id = isset($branch_id) ? (int) $branch_id : 0;
	$question_flow = isset($question_flow) && is_array($question_flow) ? $question_flow : array();
	$branch_query = $branch_id > 0 ? '?branch_id=' . $branch_id : '';
	$question_type_map = $this->onlineexam_model->question_type();
	$can_add = get_permission('question_bank', 'is_add');
	$can_edit = get_permission('question_bank', 'is_edit');
	$can_delete = get_permission('question_bank', 'is_delete');
	if (!function_exists('qi_type_count_html')) {
		function qi_type_count_html($typeCounts, $typeMap) {
			if (empty($typeCounts) || !is_array($typeCounts)) {
				return '';
			}
			$meta = array(
				1 => array('icon' => 'fas fa-circle-dot', 'bg' => '#eff6ff', 'fg' => '#2563eb', 'bd' => '#dbeafe'),
				2 => array('icon' => 'fas fa-square-check', 'bg' => '#f5f3ff', 'fg' => '#7c3aed', 'bd' => '#ede9fe'),
				3 => array('icon' => 'fas fa-toggle-on', 'bg' => '#ecfeff', 'fg' => '#0891b2', 'bd' => '#cffafe'),
				4 => array('icon' => 'fas fa-align-left', 'bg' => '#f8fafc', 'fg' => '#64748b', 'bd' => '#e2e8f0'),
				5 => array('icon' => 'fas fa-right-left', 'bg' => '#fdf2f8', 'fg' => '#db2777', 'bd' => '#fce7f3'),
				6 => array('icon' => 'fas fa-book-open-reader', 'bg' => '#ecfdf5', 'fg' => '#059669', 'bd' => '#d1fae5'),
				7 => array('icon' => 'fas fa-pen', 'bg' => '#fffbeb', 'fg' => '#d97706', 'bd' => '#fef3c7'),
				8 => array('label' => 'FIB', 'bg' => '#fff7ed', 'fg' => '#ea580c', 'bd' => '#ffedd5'),
				9 => array('label' => 'VS', 'bg' => '#eef2ff', 'fg' => '#4f46e5', 'bd' => '#e0e7ff'),
				10 => array('label' => 'Short', 'bg' => '#f0fdfa', 'fg' => '#0d9488', 'bd' => '#ccfbf1'),
				11 => array('label' => 'Long', 'bg' => '#fff1f2', 'fg' => '#be123c', 'bd' => '#ffe4e6'),
			);
			ksort($typeCounts, SORT_NUMERIC);
			$parts = array();
			foreach ($typeCounts as $typeId => $cnt) {
				$cnt = (int) $cnt;
				if ($cnt < 1) {
					continue;
				}
				$typeId = (int) $typeId;
				$lbl = isset($typeMap[(string) $typeId]) ? $typeMap[(string) $typeId] : ('Type ' . $typeId);
				$m = isset($meta[$typeId]) ? $meta[$typeId] : array('icon' => 'fas fa-circle-question', 'bg' => '#f8fafc', 'fg' => '#64748b', 'bd' => '#e2e8f0');
				$inner = !empty($m['label'])
					? html_escape($m['label'])
					: '<i class="' . $m['icon'] . '" aria-hidden="true"></i>';
				$parts[] = '<span class="label" title="' . html_escape($lbl) . '" style="background:' . $m['bg'] . ';color:' . $m['fg'] . ';border:1px solid ' . $m['bd'] . ';font-weight:600;">'
					. $inner . ' ' . $cnt
					. '</span>';
			}
			if (empty($parts)) {
				return '';
			}
			return '<span style="margin-left:6px;">' . implode(' ', $parts) . '</span>';
		}
	}
?>
<style>
	.qb-flow > .panel {
		margin-bottom: 12px;
		border: 1px solid #e3e9f2;
		border-radius: 9px;
		box-shadow: 0 2px 8px rgba(27, 45, 75, .04);
	}
	.qb-flow > .panel > .panel-heading {
		padding: 13px 16px;
		background: linear-gradient(110deg, #f3f7ff, #fff);
		border-bottom-color: #e7edf5;
	}
	.qb-flow .qb-subject-panel > .panel-heading { background: #fbfcfe; }
	.qb-flow .qb-lesson-panel > .panel-heading { background: #f7fafc; }
	.qb-flow .qb-type-panel > .panel-heading { background: #fff; }
	.qb-flow .qb-exercise-panel > .panel-heading { background: #fbf8ff; }
	.qb-flow .qb-level-label {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 4px 8px;
		border-radius: 6px;
		background: #eaf1ff;
		color: #315fc4;
		font-size: 11px;
		font-weight: 700;
	}
	.qb-flow .qb-subject-label { background: #fff5e8; color: #a45b08; }
	.qb-flow .qb-lesson-label { background: #e8f7f1; color: #137653; }
	.qb-flow .qb-type-label { background: #f1edff; color: #6241ae; }
	.qb-flow .qb-exercise-label { background: #fff4e8; color: #a45b08; }
	.qb-flow .qb-qtable {
		border: 1px solid #e5eaf2;
		border-radius: 7px;
		overflow: hidden;
	}
	.qb-flow .qb-qtable thead th {
		background: #f4f7fb;
		color: #52627a;
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .035em;
	}
	.qb-flow .qb-qtable tbody td { vertical-align: middle; }
	.qb-flow .qb-question-preview {
		min-width: 220px;
		max-width: 780px;
		color: #25364d;
		line-height: 1.55;
		overflow-wrap: anywhere;
	}
	.qb-flow .qb-exercise-value {
		display: inline-block;
		padding: 4px 8px;
		border: 1px solid #e3eaf5;
		border-radius: 999px;
		background: #f8faff;
		color: #52627a;
		font-size: 11px;
		white-space: nowrap;
	}
	.qb-flow-guide {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 8px;
		margin-bottom: 14px;
		padding: 10px 12px;
		border: 1px solid #e6ecf5;
		border-radius: 8px;
		background: #f8faff;
		color: #71809a;
		font-size: 11px;
	}
	.qb-flow-guide strong {
		color: #344a68;
		font-weight: 700;
	}
	.qb-flow-guide i { color: #9aa8bb; }
	@media (max-width: 767px) {
		.qb-flow .qb-question-preview { min-width: 160px; }
		.qb-flow .panel-heading .pull-right { display: inline-flex; align-items: center; gap: 5px; }
	}
</style>
<div class="row">
	<div class="col-md-12">
		<?php if (is_superadmin_loggedin()): ?>
			<?php if (!empty($branch_id) && !empty($branch_name)): ?>
			<section class="panel">
				<div class="panel-body clearfix">
					<a href="<?=base_url('onlineexam/question')?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
					<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
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
							echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width='200px'");
						?>
					</div>
					<button type="submit" class="btn btn-default"><i class="fas fa-check"></i> <?=translate('select')?></button>
					<?php echo form_close(); ?>
				</div>
			</section>
			<?php endif; ?>
		<?php endif; ?>

		<section class="panel" data-appear-animation-type="<?=$global_config['animations']?>" data-appear-animation-delay="100">
			<header class="panel-heading clearfix">
				<?php if ($can_add && $branch_id > 0): ?>
				<div class="pull-right">
					<a href="<?=base_url('onlineexam/question_add') . $branch_query?>" class="btn btn-default btn-sm">
						<i class="fas fa-plus-circle"></i> <?=translate('add') . ' ' . translate('question')?>
					</a>
					<a href="<?=base_url('onlineexam/question_import') . $branch_query?>" class="btn btn-default btn-sm">
						<i class="fas fa-file-import"></i> <?=translate('question') . ' ' . translate('import')?>
					</a>
				</div>
				<?php endif; ?>
				<h4 class="panel-title">
					<i class="fas fa-layer-group"></i> <?=translate('question') . ' ' . translate('list')?>
					<span class="badge"><?=(int) $question_count?></span>
				</h4>
			</header>
			<div class="panel-body">
				<?php if (!empty($question_flow)): ?>
				<div class="qb-flow-guide">
					<strong><i class="fas fa-diagram-project"></i> Question layout</strong>
					<span>Class &amp; Section</span><i class="fas fa-chevron-right"></i>
					<span>Subject</span><i class="fas fa-chevron-right"></i>
					<span>Lesson</span><i class="fas fa-chevron-right"></i>
					<span>Question type</span><i class="fas fa-chevron-right"></i>
					<span>Exercise</span><i class="fas fa-chevron-right"></i>
					<span>Question</span>
				</div>
				<div class="panel-group qb-flow" id="questionFlowAccordion">
					<?php foreach ($question_flow as $classSection):
						$classId = (int) $classSection['class_id'];
						$sectionId = (int) $classSection['section_id'];
						$classPanelId = 'question_class_' . $classId . '_' . $sectionId;
					?>
					<div class="panel panel-default qb-class-panel">
						<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#questionFlowAccordion" data-target="#<?=$classPanelId?>" role="button" style="cursor:pointer;">
							<h5 class="panel-title" style="margin:0; font-size:14px; line-height:22px;">
								<span class="qb-level-label"><i class="fas fa-school"></i> <?=translate('class')?></span>
								<strong><?=html_escape($classSection['class_name'])?></strong>
								<span class="text-muted">/</span>
								<span><?=html_escape($classSection['section_name'])?></span>
								<?=qi_type_count_html(isset($classSection['type_counts']) ? $classSection['type_counts'] : array(), $question_type_map)?>
								<span class="pull-right">
									<?php if ($can_add):
										$classAddParams = array('branch_id' => $branch_id, 'class_id' => $classId, 'section_id' => $sectionId);
									?>
									<a href="<?=base_url('onlineexam/question_add') . '?' . http_build_query($classAddParams)?>" class="btn btn-default btn-xs" title="<?=translate('add') . ' ' . translate('question')?>" onclick="event.stopPropagation();"><i class="fas fa-plus"></i></a>
									<?php endif; ?>
									<span class="label label-default" title="Subjects"><i class="fas fa-book"></i> <?=count($classSection['subjects'])?></span>
									<span class="label label-info" title="<?=translate('question')?>"><i class="fas fa-circle-question"></i> <?=(int) $classSection['question_count']?></span>
									<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
								</span>
							</h5>
						</div>
						<div id="<?=$classPanelId?>" class="accordion-body collapse">
							<div class="panel-body">
								<div class="panel-group" id="questionSubjects_<?=$classId?>_<?=$sectionId?>">
									<?php foreach ($classSection['subjects'] as $subject):
										$subjectId = (int) $subject['subject_id'];
										$subjectPanelId = $classPanelId . '_subject_' . $subjectId;
										$subjectParams = array(
											'branch_id' => $branch_id,
											'class_id' => $classId,
											'section_id' => $sectionId,
											'subject_id' => $subjectId,
										);
									?>
									<div class="panel panel-default qb-subject-panel">
										<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#questionSubjects_<?=$classId?>_<?=$sectionId?>" data-target="#<?=$subjectPanelId?>" role="button" style="cursor:pointer;">
											<h5 class="panel-title" style="margin:0; font-size:13px; line-height:20px;">
												<span class="qb-level-label qb-subject-label"><i class="fas fa-book"></i> <?=translate('subject')?></span>
												<strong><?=html_escape($subject['subject_name'])?></strong>
												<?php if (!empty($subject['subject_group'])): ?>
												<small class="text-muted">(<?=html_escape($subject['subject_group'])?>)</small>
												<?php endif; ?>
												<?=qi_type_count_html(isset($subject['type_counts']) ? $subject['type_counts'] : array(), $question_type_map)?>
												<span class="pull-right">
													<?php if ($can_add): ?>
													<a href="<?=base_url('onlineexam/question_add') . '?' . http_build_query($subjectParams)?>" class="btn btn-default btn-xs" title="<?=translate('add') . ' ' . translate('question')?>" onclick="event.stopPropagation();"><i class="fas fa-plus"></i></a>
													<?php endif; ?>
													<span class="label label-info" title="<?=translate('question')?>"><i class="fas fa-circle-question"></i> <?=(int) $subject['question_count']?></span>
													<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
												</span>
											</h5>
										</div>
										<div id="<?=$subjectPanelId?>" class="accordion-body collapse">
											<div class="panel-body">
												<div class="panel-group" id="questionLessons_<?=$classId?>_<?=$sectionId?>_<?=$subjectId?>">
													<?php foreach ($subject['lessons'] as $lesson):
														$lessonId = (int) $lesson['lesson_id'];
														$lessonPanelId = $subjectPanelId . '_lesson_' . $lessonId;
														$lessonParams = $subjectParams;
														if ($lessonId > 0) {
															$lessonParams['lesson_id'] = $lessonId;
														}
														$lessonTitle = $lessonId > 0
															? trim($lesson['lesson_no'] . (!empty($lesson['lesson_no']) ? '. ' : '') . $lesson['lesson_name'])
															: translate('other');
													?>
													<div class="panel panel-default qb-lesson-panel">
														<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#questionLessons_<?=$classId?>_<?=$sectionId?>_<?=$subjectId?>" data-target="#<?=$lessonPanelId?>" role="button" style="cursor:pointer;">
															<h6 class="panel-title" style="margin:0; font-size:12px; line-height:20px;">
																<?php if ($lessonId > 0): ?>
																<span class="qb-level-label qb-lesson-label"><i class="fas fa-chalkboard"></i> <?=translate('lesson')?></span>
																<?php else: ?>
																<span class="qb-level-label"><i class="fas fa-folder"></i> <?=translate('other')?></span>
																<?php endif; ?>
																<?=html_escape($lessonTitle)?>
																<?=qi_type_count_html(isset($lesson['type_counts']) ? $lesson['type_counts'] : array(), $question_type_map)?>
																<span class="pull-right">
																	<?php if ($can_add): ?>
																	<a href="<?=base_url('onlineexam/question_add') . '?' . http_build_query($lessonParams)?>" class="btn btn-default btn-xs" title="<?=translate('add') . ' ' . translate('question')?>" onclick="event.stopPropagation();"><i class="fas fa-plus"></i></a>
																	<?php endif; ?>
																	<span class="label label-warning" title="<?=translate('question_type')?>"><i class="fas fa-tags"></i> <?=isset($lesson['question_types']) ? count($lesson['question_types']) : 0?></span>
																	<span class="label label-default" title="<?=translate('question')?>"><i class="fas fa-circle-question"></i> <?=(int) (isset($lesson['question_count']) ? $lesson['question_count'] : 0)?></span>
																	<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
																</span>
															</h6>
														</div>
														<div id="<?=$lessonPanelId?>" class="accordion-body collapse">
															<div class="panel-body">
																<div class="panel-group" id="questionTypes_<?=$classId?>_<?=$sectionId?>_<?=$subjectId?>_<?=$lessonId?>">
																	<?php
																	$lessonQuestionTypes = isset($lesson['question_types']) && is_array($lesson['question_types']) ? $lesson['question_types'] : array();
																	foreach ($lessonQuestionTypes as $qType):
																		$typeId = (int) $qType['type_id'];
																		$typePanelId = $lessonPanelId . '_type_' . $typeId;
																		$typeTitle = !empty($qType['type_name']) ? $qType['type_name'] : translate('question_type');
																	?>
																	<div class="panel panel-default qb-type-panel">
																		<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#questionTypes_<?=$classId?>_<?=$sectionId?>_<?=$subjectId?>_<?=$lessonId?>" data-target="#<?=$typePanelId?>" role="button" style="cursor:pointer;">
																			<h6 class="panel-title" style="margin:0; font-size:12px; line-height:20px;">
																				<span class="qb-level-label qb-type-label"><i class="fas fa-tags"></i> <?=translate('question_type')?></span>
																				<?=html_escape($typeTitle)?>
																				<span class="pull-right">
																					<?php if ($can_add): ?>
																					<a href="<?=base_url('onlineexam/question_add') . '?' . http_build_query($lessonParams)?>" class="btn btn-default btn-xs" title="<?=translate('add') . ' ' . translate('question')?>" onclick="event.stopPropagation();"><i class="fas fa-plus"></i></a>
																					<?php endif; ?>
																					<span class="label label-default" title="<?=translate('question')?>"><i class="fas fa-circle-question"></i> <?=(int) $qType['question_count']?></span>
																					<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
																				</span>
																			</h6>
																		</div>
																		<div id="<?=$typePanelId?>" class="accordion-body collapse">
																			<div class="panel-body">
																				<div class="panel-group" id="questionExercises_<?=$classId?>_<?=$sectionId?>_<?=$subjectId?>_<?=$lessonId?>_<?=$typeId?>">
																					<?php foreach ($qType['exercises'] as $exercise):
																						$exerciseId = (int) $exercise['exercise_id'];
																						$exercisePanelId = $typePanelId . '_exercise_' . $exerciseId;
																						$exerciseParams = $lessonParams;
																						if ($exerciseId > 0) {
																							$exerciseParams['exercise_id'] = $exerciseId;
																						}
																					?>
																					<div class="panel panel-default qb-exercise-panel">
																						<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#questionExercises_<?=$classId?>_<?=$sectionId?>_<?=$subjectId?>_<?=$lessonId?>_<?=$typeId?>" data-target="#<?=$exercisePanelId?>" role="button" style="cursor:pointer;">
																							<h6 class="panel-title" style="margin:0; font-size:12px; line-height:20px;">
																								<span class="qb-level-label qb-exercise-label"><i class="fas fa-list-ol"></i> <?=translate('exercise')?></span>
																								<strong><?=html_escape($exercise['exercise_name'])?></strong>
																								<span class="pull-right">
																									<?php if ($can_add): ?>
																									<a href="<?=base_url('onlineexam/question_add') . '?' . http_build_query($exerciseParams)?>" class="btn btn-default btn-xs" title="<?=translate('add') . ' ' . translate('question')?>" onclick="event.stopPropagation();"><i class="fas fa-plus"></i></a>
																									<?php endif; ?>
																									<span class="label label-info" title="<?=translate('question')?>"><i class="fas fa-circle-question"></i> <?=(int) $exercise['question_count']?></span>
																									<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
																								</span>
																							</h6>
																						</div>
																						<div id="<?=$exercisePanelId?>" class="accordion-body collapse">
																							<div class="panel-body">
																								<div class="table-responsive">
																									<table class="table table-bordered table-condensed table-hover mb-none qi-qtable qb-qtable">
																										<thead>
																											<tr>
																												<?php if ($can_delete): ?>
																												<th width="36" class="text-center">
																													<label class="i-checks mb-none"><input type="checkbox" class="qi-check-all" title="Select all"><i></i></label>
																												</th>
																												<?php endif; ?>
																												<th width="45"><?=translate('sl')?></th>
																												<th><?=translate('question')?></th>
																												<th width="150"><?=translate('action')?></th>
																											</tr>
																										</thead>
																										<tbody>
																											<?php $sn = 1; foreach ($exercise['questions'] as $question):
																												$type = (int) $question['type'];
																											?>
																											<tr>
																												<?php if ($can_delete): ?>
																												<td class="text-center">
																													<label class="i-checks mb-none"><input type="checkbox" class="qi-qcheck" value="<?=(int) $question['id']?>"><i></i></label>
																												</td>
																												<?php endif; ?>
																												<td><?=$sn++?></td>
																												<td class="qb-question-preview">
																													<?php
																													if ($type === 5) {
																														$decoded = json_decode($question['question'], true);
																														echo is_array($decoded) && !empty($decoded['instruction'])
																															? $decoded['instruction']
																															: $question['question'];
																													} else {
																														echo $question['question'];
																													}
																													?>
																												</td>
																												<td>
																													<button type="button" class="btn btn-circle btn-default btn-sm icon" title="<?=translate('view')?>" data-loading-text="<i class='fas fa-spinner fa-spin'></i>" onclick="getQuestion(<?=(int) $question['id']?>, this);"><i class="fas fa-bars"></i></button>
																													<?php if ($can_edit): ?>
																													<a href="<?=base_url('onlineexam/question_edit/' . (int) $question['id'])?>" class="btn btn-circle btn-default btn-sm" title="<?=translate('edit')?>"><i class="fas fa-pen-nib"></i></a>
																													<?php endif; ?>
																													<?php if ($can_delete): ?>
																													<?=btn_delete('onlineexam/question_delete/' . (int) $question['id'])?>
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
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php else: ?>
				<div class="text-center text-muted" style="padding:30px 10px;">
					<p><i class="fas fa-circle-question fa-3x"></i></p>
					<h4><?=translate('no_information_available')?></h4>
					<?php if ($can_add): ?>
					<a href="<?=base_url('onlineexam/question_add') . $branch_query?>" class="btn btn-primary btn-sm"><i class="fas fa-plus-circle"></i> <?=translate('add') . ' ' . translate('question')?></a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide payroll-t-modal" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="fas fa-file-circle-question"></i> <?=translate('question') . ' ' . translate('view')?></h4>
		</header>
		<div class="panel-body"><div id="quick_view"></div></div>
		<footer class="panel-footer text-right">
			<button class="btn btn-default modal-dismiss"><?=translate('close')?></button>
		</footer>
	</section>
</div>

<?php if ($can_delete): ?>
<div id="qiBulkBar" style="display:none; position:fixed; left:50%; bottom:22px; transform:translateX(-50%); z-index:1050; background:#1e293b; color:#fff; border-radius:999px; padding:10px 16px; box-shadow:0 10px 30px rgba(15,23,42,.35); align-items:center; gap:12px;">
	<span style="font-size:13px; font-weight:600;"><span id="qiBulkBarCount">0</span> selected</span>
	<button type="button" class="btn btn-default btn-xs" id="qiBulkClear">Clear</button>
	<button type="button" class="btn btn-danger btn-xs" id="qiBulkBarDelete"><i class="fas fa-trash-alt"></i> <?=translate('delete')?></button>
</div>
<?php endif; ?>

<script type="text/javascript">
$(document).ready(function () {
	$(document).on('show.bs.collapse hide.bs.collapse', '.accordion-body', function (event) {
		if (event.target !== this) return;
		var open = event.type === 'show';
		$('[data-target="#' + this.id + '"]').find('.toggle-indicator')
			.toggleClass('fa-chevron-down', !open)
			.toggleClass('fa-chevron-up', open);
	});

<?php if ($can_delete): ?>
	function qiSelectedIds() {
		var ids = [];
		$('.qi-qcheck:checked').each(function () {
			var id = parseInt($(this).val(), 10);
			if (id > 0 && ids.indexOf(id) === -1) {
				ids.push(id);
			}
		});
		return ids;
	}

	function qiSyncSelectionUi() {
		var ids = qiSelectedIds();
		var count = ids.length;
		$('#qiBulkBarCount').text(count);
		$('#qiBulkBar').css('display', count > 0 ? 'flex' : 'none');

		$('.qi-qtable').each(function () {
			var $table = $(this);
			var total = $table.find('.qi-qcheck').length;
			var checked = $table.find('.qi-qcheck:checked').length;
			$table.find('.qi-check-all').prop('checked', total > 0 && checked === total);
		});
	}

	function qiDeleteSelected(ids) {
		if (!ids || !ids.length) {
			return;
		}
		Swal.fire({
			title: "<?=translate('are_you_sure')?>",
			text: "Delete " + ids.length + " selected question(s)?",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "<?=translate('yes_continue')?>",
			cancelButtonText: "<?=translate('cancel')?>",
			customClass: {
				confirmButton: 'btn btn-danger',
				cancelButton: 'btn btn-secondary'
			},
			buttonsStyling: false,
			footer: "<?=translate('deleted_note')?>"
		}).then(function (result) {
			if (!result.isConfirmed) {
				return;
			}
			$.ajax({
				url: base_url + 'onlineexam/question_delete_multi',
				type: 'POST',
				dataType: 'json',
				data: { ids: ids },
				success: function (res) {
					if (res && res.status === 'success') {
						Swal.fire({
							title: "<?=translate('deleted')?>",
							text: res.message || "<?=translate('information_deleted')?>",
							icon: "success",
							confirmButtonText: "OK",
							customClass: { confirmButton: 'btn btn-primary' },
							buttonsStyling: false
						}).then(function () {
							location.reload();
						});
					} else {
						Swal.fire({
							title: "Error",
							text: (res && res.message) ? res.message : "Delete failed.",
							icon: "error",
							confirmButtonText: "OK",
							customClass: { confirmButton: 'btn btn-primary' },
							buttonsStyling: false
						});
					}
				},
				error: function () {
					Swal.fire({
						title: "Error",
						text: "Delete request failed.",
						icon: "error",
						confirmButtonText: "OK",
						customClass: { confirmButton: 'btn btn-primary' },
						buttonsStyling: false
					});
				}
			});
		});
	}

	$(document).on('change', '.qi-check-all', function (e) {
		e.stopPropagation();
		var checked = $(this).is(':checked');
		$(this).closest('table').find('.qi-qcheck').prop('checked', checked);
		qiSyncSelectionUi();
	});

	$(document).on('change', '.qi-qcheck', function (e) {
		e.stopPropagation();
		qiSyncSelectionUi();
	});

	$(document).on('click', '.qi-check-all, .qi-qcheck, .i-checks', function (e) {
		e.stopPropagation();
	});

	$('#qiBulkBarDelete').on('click', function () {
		qiDeleteSelected(qiSelectedIds());
	});

	$('#qiBulkClear').on('click', function () {
		$('.qi-qcheck, .qi-check-all').prop('checked', false);
		qiSyncSelectionUi();
	});
<?php endif; ?>
});
</script>
