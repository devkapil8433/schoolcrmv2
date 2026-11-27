<?php
	$alerts = isset($alerts) && is_array($alerts) ? $alerts : array();
	$alerts_pending = isset($alerts_pending) && is_array($alerts_pending) ? $alerts_pending : array();
	$alerts_reviewed = isset($alerts_reviewed) && is_array($alerts_reviewed)
		? $alerts_reviewed
		: (isset($alerts_completed) && is_array($alerts_completed) ? $alerts_completed : array());
	$unread_count = isset($unread_count) ? (int) $unread_count : 0;
	$pending_count = isset($pending_count) ? (int) $pending_count : count($alerts_pending);
	$reviewed_count = isset($reviewed_count) ? (int) $reviewed_count : (isset($completed_count) ? (int) $completed_count : count($alerts_reviewed));
	$all_count = isset($all_count) ? (int) $all_count : count($alerts);
	$branch_id = isset($branch_id) ? $branch_id : '';
	$branch_name = isset($branch_name) ? $branch_name : '';
	$active_tab = isset($active_tab) ? $active_tab : 'pending';
	if ($active_tab === 'completed') {
		$active_tab = 'reviewed';
	}
	if (!in_array($active_tab, array('pending', 'reviewed', 'all'), true)) {
		$active_tab = 'pending';
	}

	$render_rows = function ($list) use ($branch_id, $active_tab) {
		if (empty($list)) {
			echo '<tr><td colspan="7" class="text-center text-muted">No tasks in this tab.</td></tr>';
			return;
		}
		foreach ($list as $a) {
			$isUnread = empty($a['is_read']);
			$status = isset($a['task_status']) ? $a['task_status'] : 'pending';
			if ($status === 'completed') {
				$status = 'reviewed';
			}
			$alertBranch = !empty($a['branch_id']) ? (int) $a['branch_id'] : (int) $branch_id;
			$swrQ = array(
				'date' => $a['work_date'],
				'class_id' => (int) $a['class_id'],
				'section_id' => (int) $a['section_id'],
			);
			if ($alertBranch > 0) {
				$swrQ['branch_id'] = $alertBranch;
			}
			$viewUrl = base_url('digital_diary/student_work_report_entry') . '?' . http_build_query($swrQ);
			$twUrl = base_url('digital_diary/today_work') . '?' . http_build_query($swrQ);
			$reviewQ = $swrQ;
			if (!empty($a['id'])) {
				$reviewQ['alert_id'] = (int) $a['id'];
			}
			$reviewUrl = base_url('digital_diary/coordinator_review') . '?' . http_build_query($reviewQ);
			$classLabel = trim((isset($a['class_name']) ? $a['class_name'] : '') . ' / ' . (isset($a['section_name']) ? $a['section_name'] : ''), ' /');
			$eventLabel = isset($a['event_label']) ? $a['event_label'] : (isset($a['event_type']) ? $a['event_type'] : 'Alert');
			$rwStatus = isset($a['review_window_status']) ? $a['review_window_status'] : '';
			$deadline = !empty($a['review_deadline']) ? $a['review_deadline'] : null;
			$daysLeft = isset($a['review_days_left']) ? $a['review_days_left'] : null;
			if ($status === 'reviewed') {
				$statusBadge = '<span class="label label-success">Reviewed</span>';
			} elseif ($rwStatus === 'overdue') {
				$statusBadge = '<span class="label label-danger">Overdue</span>';
			} elseif ($rwStatus === 'open') {
				$leftTxt = ($daysLeft !== null)
					? ((int) $daysLeft . ' day' . ((int) $daysLeft === 1 ? '' : 's') . ' left')
					: 'Open';
				$statusBadge = '<span class="label label-warning">' . html_escape($leftTxt) . '</span>';
			} else {
				$statusBadge = '<span class="label label-warning">' . html_escape(isset($a['status_label']) ? $a['status_label'] : 'Pending') . '</span>';
			}
			$deadlineHtml = '';
			if ($status !== 'reviewed' && $deadline) {
				$deadlineHtml = '<div class="text-muted" style="font-size:11px;margin-top:3px;">Due ' . html_escape(date('d-M-Y', strtotime($deadline))) . '</div>';
			}
			?>
			<tr class="<?= $isUnread ? 'warning' : '' ?><?= ($status !== 'reviewed' && $rwStatus === 'overdue') ? ' danger' : '' ?>">
				<td><?= $statusBadge ?><?php if ($isUnread): ?> <span class="label label-danger">New</span><?php endif; ?><?= $deadlineHtml ?></td>
				<td><?= !empty($a['work_date']) ? html_escape($a['work_date']) : '—' ?></td>
				<td><?= html_escape($classLabel !== '' ? $classLabel : '—') ?></td>
				<td><?= html_escape($eventLabel) ?></td>
				<td><?= html_escape(isset($a['message']) ? $a['message'] : '') ?></td>
				<td><?= !empty($a['created_at']) ? html_escape($a['created_at']) : '—' ?></td>
				<td>
					<?php if ($status !== 'reviewed'): ?>
					<a href="<?= html_escape($reviewUrl) ?>" class="btn btn-xs btn-warning"><i class="fas fa-clipboard-check"></i> Review &amp; Submit</a>
					<?php else: ?>
					<a href="<?= html_escape($reviewUrl) ?>" class="btn btn-xs btn-default"><i class="fas fa-eye"></i> View</a>
					<?php endif; ?>
					<a href="<?= html_escape($twUrl) ?>" class="btn btn-xs btn-default">Today's Work</a>
					<a href="<?= html_escape($viewUrl) ?>" class="btn btn-xs btn-primary">SWR</a>
					<?php if ($isUnread): ?>
					<a href="<?= base_url('digital_diary/coordinator_tasks?read=' . (int) $a['id'] . (!empty($branch_id) ? ('&branch_id=' . urlencode($branch_id)) : '') . '&tab=' . urlencode($active_tab)) ?>" class="btn btn-xs btn-success">Mark read</a>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}
	};
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title">
					<i class="fas fa-bell"></i> Coordinator Tasks
					<?php if ($unread_count > 0): ?>
						<span class="badge badge-danger"><?= $unread_count ?> unread</span>
					<?php endif; ?>
				</h4>
				<div class="panel-btn">
					<?php if ($unread_count > 0): ?>
					<a href="<?= base_url('digital_diary/coordinator_tasks?mark_all=1' . (!empty($branch_id) ? ('&branch_id=' . urlencode($branch_id)) : '') . '&tab=' . urlencode($active_tab)) ?>" class="btn btn-default btn-circle btn-sm">Mark all read</a>
					<?php endif; ?>
				</div>
			</header>
			<div class="panel-body">
				<?php if (is_superadmin_loggedin()): ?>
				<div class="mb-md">
					<?php echo form_open(base_url('digital_diary/coordinator_tasks'), array('method' => 'get', 'class' => 'form-inline')); ?>
						<input type="hidden" name="tab" value="<?= html_escape($active_tab) ?>">
						<label class="control-label"><?= translate('branch') ?></label>
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' data-plugin-selectTwo data-width='220px'");
						?>
						<button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
					<?php echo form_close(); ?>
					<?php if (!empty($branch_name)): ?>
					<p class="text-muted mb-none mt-sm"><strong><?= translate('branch') ?>:</strong> <?= html_escape($branch_name) ?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<div class="tabs-custom">
					<ul class="nav nav-tabs">
						<li class="<?= $active_tab === 'pending' ? 'active' : '' ?>">
							<a href="#ct_tab_pending" data-toggle="tab">
								<i class="fas fa-hourglass-half"></i> Pending
								<span class="badge"><?= (int) $pending_count ?></span>
							</a>
						</li>
						<li class="<?= $active_tab === 'reviewed' ? 'active' : '' ?>">
							<a href="#ct_tab_reviewed" data-toggle="tab">
								<i class="fas fa-check-circle"></i> Reviewed
								<span class="badge"><?= (int) $reviewed_count ?></span>
							</a>
						</li>
						<li class="<?= $active_tab === 'all' ? 'active' : '' ?>">
							<a href="#ct_tab_all" data-toggle="tab">
								<i class="fas fa-list"></i> All
								<span class="badge"><?= (int) $all_count ?></span>
							</a>
						</li>
					</ul>
					<div class="tab-content">
						<div id="ct_tab_pending" class="tab-pane <?= $active_tab === 'pending' ? 'active' : '' ?>">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-hover table-striped mb-none">
									<thead>
										<tr>
											<th>Status</th>
											<th><?= translate('date') ?></th>
											<th><?= translate('class') ?> / <?= translate('section') ?></th>
											<th>Event</th>
											<th>Message</th>
											<th>When</th>
											<th><?= translate('action') ?></th>
										</tr>
									</thead>
									<tbody>
										<?php $render_rows($alerts_pending); ?>
									</tbody>
								</table>
							</div>
						</div>
						<div id="ct_tab_reviewed" class="tab-pane <?= $active_tab === 'reviewed' ? 'active' : '' ?>">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-hover table-striped mb-none">
									<thead>
										<tr>
											<th>Status</th>
											<th><?= translate('date') ?></th>
											<th><?= translate('class') ?> / <?= translate('section') ?></th>
											<th>Event</th>
											<th>Message</th>
											<th>When</th>
											<th><?= translate('action') ?></th>
										</tr>
									</thead>
									<tbody>
										<?php $render_rows($alerts_reviewed); ?>
									</tbody>
								</table>
							</div>
						</div>
						<div id="ct_tab_all" class="tab-pane <?= $active_tab === 'all' ? 'active' : '' ?>">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-hover table-striped mb-none">
									<thead>
										<tr>
											<th>Status</th>
											<th><?= translate('date') ?></th>
											<th><?= translate('class') ?> / <?= translate('section') ?></th>
											<th>Event</th>
											<th>Message</th>
											<th>When</th>
											<th><?= translate('action') ?></th>
										</tr>
									</thead>
									<tbody>
										<?php $render_rows($alerts); ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>
<script type="text/javascript">
(function ($) {
	$('.tabs-custom a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		try {
			var href = $(e.target).attr('href') || '';
			var tab = 'all';
			if (href.indexOf('pending') !== -1) tab = 'pending';
			else if (href.indexOf('reviewed') !== -1) tab = 'reviewed';
			var url = new URL(window.location.href);
			url.searchParams.set('tab', tab);
			window.history.replaceState({}, '', url.toString());
		} catch (err) {}
	});
})(jQuery);
</script>
