<?php
	$work_date = isset($work_date) ? $work_date : date('Y-m-d');
	$list = isset($today_work_list) && is_array($today_work_list) ? $today_work_list : array();
?>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-clipboard-list"></i> Today's Work</h4>
	</header>
	<div class="panel-body">
		<?php echo form_open(base_url('userrole/today_work'), array('method' => 'get', 'class' => 'form-inline mb-md')); ?>
			<div class="form-group">
				<label class="control-label mr-sm"><?= translate('date') ?></label>
				<input type="text" name="date" class="form-control" data-plugin-datepicker data-plugin-options='{"todayHighlight": true}' value="<?= html_escape($work_date) ?>">
			</div>
			<button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
		<?php echo form_close(); ?>

		<?php if (empty($list)): ?>
			<p class="text-muted text-center">No Today's Work saved for this date.</p>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table table-bordered table-condensed table-hover">
					<thead>
						<tr>
							<th><?= translate('subject') ?></th>
							<th>Class work</th>
							<th>Home work</th>
							<th>Learning work</th>
							<th>Status</th>
							<th><?= translate('action') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($list as $row): ?>
						<tr>
							<td><?= html_escape($row['subject_name']) ?></td>
							<td><?= html_escape($row['class_work']) ?></td>
							<td><?= html_escape($row['home_work']) ?></td>
							<td><?= html_escape($row['learning_work']) ?></td>
							<td>
								<?php if (!empty($row['learning_status'])): ?>
									<span class="label label-<?= $row['learning_status'] === 'verified' ? 'success' : 'info' ?>">Learning: <?= html_escape($row['learning_status']) ?></span>
								<?php endif; ?>
								<?php if (!empty($row['hw_status'])): ?>
									<span class="label label-<?= $row['hw_status'] === 'verified' ? 'success' : 'info' ?>">HW: <?= html_escape($row['hw_status']) ?></span>
								<?php endif; ?>
								<?php if (empty($row['learning_status']) && empty($row['hw_status'])): ?>
									<span class="text-muted">Pending</span>
								<?php endif; ?>
							</td>
							<td>
								<a href="<?= base_url('userrole/today_work_view/' . (int) $row['id']) ?>" class="btn btn-default btn-circle btn-sm"><i class="fas fa-eye"></i> Open</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</section>
