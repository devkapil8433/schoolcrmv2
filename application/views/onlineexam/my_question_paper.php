<?php
	$branch_id = isset($branch_id) ? (int) $branch_id : 0;
	$branch_query = $branch_id > 0 ? ('?branch_id=' . urlencode($branch_id)) : '';
	$rows = isset($question_papers) && is_array($question_papers) ? $question_papers : array();
	$can_edit = get_permission('question_bank', 'is_edit');

	// Group: Exam Name → Class — Section → Subject → papers
	$flow = array();
	foreach ($rows as $row) {
		$examName = trim((string) (isset($row['exam_name']) ? $row['exam_name'] : ''));
		if ($examName === '') {
			$examName = 'Other';
		}
		$classId = (int) (isset($row['class_id']) ? $row['class_id'] : 0);
		$sectionId = (int) (isset($row['section_id']) ? $row['section_id'] : 0);
		$subjectId = (int) (isset($row['subject_id']) ? $row['subject_id'] : 0);
		$classKey = $classId . '_' . $sectionId;

		if (!isset($flow[$examName])) {
			$flow[$examName] = array(
				'exam_name' => $examName,
				'paper_count' => 0,
				'question_count' => 0,
				'classes' => array(),
			);
		}
		if (!isset($flow[$examName]['classes'][$classKey])) {
			$flow[$examName]['classes'][$classKey] = array(
				'class_id' => $classId,
				'section_id' => $sectionId,
				'class_name' => !empty($row['class_name']) ? $row['class_name'] : translate('class'),
				'section_name' => !empty($row['section_name']) ? $row['section_name'] : translate('section'),
				'paper_count' => 0,
				'question_count' => 0,
				'subjects' => array(),
			);
		}
		if (!isset($flow[$examName]['classes'][$classKey]['subjects'][$subjectId])) {
			$flow[$examName]['classes'][$classKey]['subjects'][$subjectId] = array(
				'subject_id' => $subjectId,
				'subject_name' => !empty($row['subject_name']) ? $row['subject_name'] : translate('subject'),
				'paper_count' => 0,
				'question_count' => 0,
				'papers' => array(),
			);
		}

		$qCsv = isset($row['question_ids']) ? (string) $row['question_ids'] : '';
		$qCount = $qCsv !== '' ? count(array_filter(explode(',', $qCsv))) : 0;
		$row['_qcount'] = $qCount;

		$flow[$examName]['classes'][$classKey]['subjects'][$subjectId]['papers'][] = $row;
		$flow[$examName]['classes'][$classKey]['subjects'][$subjectId]['paper_count']++;
		$flow[$examName]['classes'][$classKey]['subjects'][$subjectId]['question_count'] += $qCount;
		$flow[$examName]['classes'][$classKey]['paper_count']++;
		$flow[$examName]['classes'][$classKey]['question_count'] += $qCount;
		$flow[$examName]['paper_count']++;
		$flow[$examName]['question_count'] += $qCount;
	}

	// Stable sort by exam name (Other last)
	uksort($flow, function ($a, $b) {
		if ($a === 'Other') return 1;
		if ($b === 'Other') return -1;
		return strcasecmp($a, $b);
	});

	$totalPapers = count($rows);
	$examIdx = 0;
?>
<div class="row">
	<div class="col-md-12">
		<?php if (is_superadmin_loggedin()): ?>
			<?php if ($branch_id > 0): ?>
			<section class="panel">
				<div class="panel-body clearfix">
					<a href="<?= base_url('onlineexam/my_question_paper') ?>" class="btn-portal-secondary btn-portal-sm pull-right"><i class="fas fa-edit"></i> Change</a>
					<strong><?= translate('branch') ?>:</strong> <?= html_escape(get_type_name_by_id('branch', $branch_id)) ?>
				</div>
			</section>
			<?php else: ?>
			<section class="panel">
				<header class="panel-heading">
					<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-code-branch"></i> <?= translate('branch') ?></h4>
				</header>
				<div class="panel-body">
					<p class="text-muted mb-md"><?= translate('select') ?> <?= translate('branch') ?> to view question papers.</p>
					<?php echo form_open('onlineexam/my_question_paper', array('method' => 'get')); ?>
					<div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
						<div class="form-group mb-none" style="flex:1 1 260px; min-width:220px; max-width:360px;">
							<label class="portal-label"><?= translate('branch') ?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='portal-input' required data-plugin-selectTwo data-width='100%'");
							?>
						</div>
						<div class="form-group mb-none">
							<button type="submit" class="btn-portal-secondary"><i class="fas fa-check"></i> <?= translate('select') ?></button>
						</div>
					</div>
					<?php echo form_close(); ?>
				</div>
			</section>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ($branch_id > 0 || !is_superadmin_loggedin()): ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2">
					<i class="fas fa-folder-open"></i> My Question Paper
					<span class="badge"><?= (int) $totalPapers ?></span>
				</h4>
				<div class="flex items-center gap-2">
					<a href="<?= base_url('onlineexam/create_question_paper') . $branch_query ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-plus"></i> Create Question Paper
					</a>
					<a href="<?= base_url('onlineexam/question') . $branch_query ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-book-open"></i> Question Bank
					</a>
				</div>
			</header>
			<div class="panel-body">
				<p class="text-muted mb-md">Saved papers grouped by Exam Name → Class → Subject.</p>

				<?php if (!empty($flow)): ?>
				<div class="panel-group" id="mqpExamAccordion">
					<?php foreach ($flow as $examNode):
						$examIdx++;
						$examPanelId = 'mqp_exam_' . $examIdx;
					?>
					<div class="panel panel-default">
						<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#mqpExamAccordion" data-target="#<?= $examPanelId ?>" role="button" style="cursor:pointer;">
							<h5 class="panel-title" style="margin:0; font-size:14px; line-height:22px;">
								<span class="label label-info" title="Exam"><i class="fas fa-file-pen"></i></span>
								<strong><?= html_escape($examNode['exam_name']) ?></strong>
								<span class="pull-right">
									<span class="label label-primary" title="<?= translate('class') ?>"><i class="fas fa-school"></i> <?= count($examNode['classes']) ?></span>
									<span class="label label-default" title="Papers"><i class="fas fa-file-lines"></i> <?= (int) $examNode['paper_count'] ?></span>
									<span class="label label-warning" title="<?= translate('question') ?>"><i class="fas fa-circle-question"></i> <?= (int) $examNode['question_count'] ?></span>
									<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
								</span>
							</h5>
						</div>
						<div id="<?= $examPanelId ?>" class="accordion-body collapse">
							<div class="panel-body">
								<div class="panel-group" id="mqpClasses_<?= $examPanelId ?>">
									<?php foreach ($examNode['classes'] as $classKey => $classNode):
										$classPanelId = $examPanelId . '_class_' . preg_replace('/[^0-9_]/', '', $classKey);
									?>
									<div class="panel panel-default">
										<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#mqpClasses_<?= $examPanelId ?>" data-target="#<?= $classPanelId ?>" role="button" style="cursor:pointer;">
											<h5 class="panel-title" style="margin:0; font-size:13px; line-height:20px;">
												<span class="label label-primary" title="<?= translate('class') ?>"><i class="fas fa-school"></i></span>
												<strong><?= html_escape($classNode['class_name']) ?></strong>
												<span class="text-muted">&mdash;</span>
												<?= html_escape($classNode['section_name']) ?>
												<span class="pull-right">
													<span class="label label-warning" title="<?= translate('subject') ?>"><i class="fas fa-book"></i> <?= count($classNode['subjects']) ?></span>
													<span class="label label-default" title="Papers"><i class="fas fa-file-lines"></i> <?= (int) $classNode['paper_count'] ?></span>
													<span class="label label-info" title="<?= translate('question') ?>"><i class="fas fa-circle-question"></i> <?= (int) $classNode['question_count'] ?></span>
													<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
												</span>
											</h5>
										</div>
										<div id="<?= $classPanelId ?>" class="accordion-body collapse">
											<div class="panel-body">
												<div class="panel-group" id="mqpSubjects_<?= $classPanelId ?>">
													<?php foreach ($classNode['subjects'] as $subject):
														$subjectId = (int) $subject['subject_id'];
														$subjectPanelId = $classPanelId . '_subject_' . $subjectId;
													?>
													<div class="panel panel-default">
														<div class="panel-heading collapsed" data-toggle="collapse" data-parent="#mqpSubjects_<?= $classPanelId ?>" data-target="#<?= $subjectPanelId ?>" role="button" style="cursor:pointer;">
															<h5 class="panel-title" style="margin:0; font-size:12px; line-height:20px;">
																<span class="label label-warning" title="<?= translate('subject') ?>"><i class="fas fa-book"></i></span>
																<strong><?= html_escape($subject['subject_name']) ?></strong>
																<span class="pull-right">
																	<span class="label label-default" title="Papers"><i class="fas fa-file-lines"></i> <?= (int) $subject['paper_count'] ?></span>
																	<span class="label label-info" title="<?= translate('question') ?>"><i class="fas fa-circle-question"></i> <?= (int) $subject['question_count'] ?></span>
																	<i class="fas fa-chevron-down toggle-indicator text-muted" style="margin-left:10px;"></i>
																</span>
															</h5>
														</div>
														<div id="<?= $subjectPanelId ?>" class="accordion-body collapse">
															<div class="panel-body">
																<div class="portal-table-wrap">
																	<table class="portal-table">
																		<thead>
																			<tr>
																				<th width="45">#</th>
																				<th>Title</th>
																				<th width="90"><?= translate('question') ?></th>
																				<th width="160">Created On</th>
																				<th width="140"><?= translate('action') ?></th>
																			</tr>
																		</thead>
																		<tbody>
																			<?php $sn = 1; foreach ($subject['papers'] as $row): ?>
																			<tr>
																				<td><?= $sn++ ?></td>
																				<td>
																					<strong><?= html_escape(isset($row['title']) ? $row['title'] : 'Question Paper') ?></strong>
																				</td>
																				<td><span class="label label-default"><?= (int) $row['_qcount'] ?></span></td>
																				<td><?= !empty($row['created_at']) ? _d($row['created_at']) . ' ' . date('h:i A', strtotime($row['created_at'])) : '—' ?></td>
																				<td class="action">
																					<a href="<?= base_url('onlineexam/question_paper_view/' . (int) $row['id']) . $branch_query ?>" class="btn-portal-secondary btn-portal-sm" title="<?= translate('view') ?>"><i class="fas fa-eye"></i></a>
																					<a href="<?= base_url('onlineexam/question_paper_view/' . (int) $row['id']) . $branch_query ?>" class="btn-portal-secondary btn-portal-sm" title="Print" target="_blank"><i class="fas fa-print"></i></a>
																					<a href="<?= base_url('onlineexam/question_paper_pdf/' . (int) $row['id']) . $branch_query ?>" class="btn-portal-secondary btn-portal-sm" title="Download PDF"><i class="fas fa-file-pdf text-danger"></i></a>
																					<?php if ($can_edit): ?>
																					<a href="<?= base_url('onlineexam/question_paper_edit/' . (int) $row['id']) . $branch_query ?>" class="btn-portal-secondary btn-portal-sm" title="<?= translate('edit') ?>"><i class="fas fa-pen-nib"></i></a>
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
				<?php else: ?>
				<div class="alert alert-subl text-center mb-none">No question paper created yet.</div>
				<?php endif; ?>
			</div>
		</section>
		<?php endif; ?>
	</div>
</div>
