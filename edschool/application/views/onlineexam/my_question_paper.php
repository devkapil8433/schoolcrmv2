<?php
	$branch_id = isset($branch_id) ? (int) $branch_id : 0;
	$branch_query = $branch_id > 0 ? ('?branch_id=' . urlencode($branch_id)) : '';
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-folder-open"></i> My Question Paper</h4>
				<div class="flex items-center gap-2">
					<a href="<?=base_url('onlineexam/create_question_paper') . $branch_query?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-plus"></i> Create Question Paper
					</a>
					<a href="<?=base_url('onlineexam/question') . $branch_query?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-book-open"></i> Question Bank
					</a>
				</div>
			</header>
			<div class="panel-body">
				<?php if (is_superadmin_loggedin()): ?>
					<div class="alert alert-default mb-md">
						<?php echo form_open('onlineexam/my_question_paper', array('method' => 'get', 'class' => 'form-inline')); ?>
							<div class="form-group">
								<label class="portal-label mr-sm"><?= translate('branch') ?> <span class="required">*</span></label>
								<?php
									$arrayBranch = $this->app_lib->getSelectList('branch');
									echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='portal-input' required data-plugin-selectTwo data-width='260px'");
								?>
							</div>
							<button type="submit" class="btn-portal-secondary ml-sm"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
						<?php echo form_close(); ?>
					</div>
					<?php if ($branch_id < 1): ?>
						<div class="alert alert-warning mb-md"><?= translate('select') ?> <?= translate('branch') ?> to view question papers.</div>
					<?php endif; ?>
				<?php endif; ?>
				<p class="text-muted mb-md">Saved question papers created from Create Question Paper.</p>
				<div class="portal-table-wrap">
					<table class="table table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th width="60">#</th>
								<th>Title</th>
								<th width="220">Class / Section / Subject</th>
								<th width="130">Questions</th>
								<th width="180">Created On</th>
								<th width="130">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php $rows = isset($question_papers) && is_array($question_papers) ? $question_papers : array(); ?>
							<?php if (!empty($rows)): ?>
								<?php $sn = 1; foreach ($rows as $row): ?>
									<tr>
										<td><?= (int) $sn++; ?></td>
										<td>
											<strong><?= html_escape(isset($row['title']) ? $row['title'] : 'Question Paper'); ?></strong><br>
											<span class="text-muted small"><?= html_escape(isset($row['exam_name']) ? $row['exam_name'] : ''); ?></span>
										</td>
										<td>
											<?= html_escape((isset($row['class_name']) ? $row['class_name'] : '') . ' / ' . (isset($row['section_name']) ? $row['section_name'] : '') . ' / ' . (isset($row['subject_name']) ? $row['subject_name'] : '')); ?>
										</td>
										<td>
											<?php
												$qCsv = isset($row['question_ids']) ? (string) $row['question_ids'] : '';
												$qCount = 0;
												if ($qCsv !== '') {
													$qCount = count(array_filter(explode(',', $qCsv)));
												}
												echo (int) $qCount;
											?>
										</td>
										<td><?= !empty($row['created_at']) ? _d($row['created_at']) . ' ' . date('h:i A', strtotime($row['created_at'])) : '—'; ?></td>
										<td>
											<a href="<?= base_url('onlineexam/question_paper_view/' . (int) $row['id']) . $branch_query; ?>" class="btn btn-default btn-xs" title="<?= translate('view') ?>"><i class="fas fa-eye"></i></a>
											<a href="<?= base_url('onlineexam/question_paper_view/' . (int) $row['id']) . $branch_query; ?>" class="btn btn-primary btn-xs" title="Print" onclick="window.open(this.href); return true;"><i class="fas fa-print"></i></a>
											<a href="<?= base_url('onlineexam/question_paper_pdf/' . (int) $row['id']) . $branch_query; ?>" class="btn btn-danger btn-xs" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
											<?php if (get_permission('question_bank', 'is_edit')): ?>
												<a href="<?= base_url('onlineexam/question_paper_edit/' . (int) $row['id']) . $branch_query; ?>" class="btn btn-default btn-xs" title="<?= translate('edit') ?>"><i class="fas fa-pen-nib"></i></a>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="6" class="text-center text-muted">No question paper created yet.</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>
