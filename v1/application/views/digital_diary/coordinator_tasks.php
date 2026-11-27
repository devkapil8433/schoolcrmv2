<?php
	$task_groups_pending = isset($task_groups_pending) && is_array($task_groups_pending) ? $task_groups_pending : array();
	$task_groups_reviewed = isset($task_groups_reviewed) && is_array($task_groups_reviewed) ? $task_groups_reviewed : array();
	$task_groups_all = isset($task_groups_all) && is_array($task_groups_all) ? $task_groups_all : array();
	$unread_count = isset($unread_count) ? (int) $unread_count : 0;
	$pending_count = isset($pending_count) ? (int) $pending_count : count($task_groups_pending);
	$reviewed_count = isset($reviewed_count) ? (int) $reviewed_count : count($task_groups_reviewed);
	$all_count = isset($all_count) ? (int) $all_count : count($task_groups_all);
	$overdue_count = isset($overdue_count) ? (int) $overdue_count : 0;
	$branch_id = isset($branch_id) ? $branch_id : '';
	$branch_name = isset($branch_name) ? $branch_name : '';
	$filter_work_date = isset($filter_work_date) ? $filter_work_date : '';
	$active_tab = isset($active_tab) ? $active_tab : 'pending';
	if ($active_tab === 'completed') {
		$active_tab = 'reviewed';
	}
	if (!in_array($active_tab, array('pending', 'reviewed', 'all'), true)) {
		$active_tab = 'pending';
	}
	$tasksUrl = base_url('digital_diary/coordinator_tasks');
	$qsKeep = '';
	if (!empty($branch_id)) {
		$qsKeep .= '&branch_id=' . urlencode($branch_id);
	}
	if ($filter_work_date !== '') {
		$qsKeep .= '&work_date=' . urlencode($filter_work_date);
	}

	$resetQs = array();
	if (!empty($branch_id)) {
		$resetQs['branch_id'] = $branch_id;
	}
	if ($active_tab !== '') {
		$resetQs['tab'] = $active_tab;
	}
	$resetUrl = $tasksUrl . (!empty($resetQs) ? '?' . http_build_query($resetQs) : '');

	$render_cards = function ($list) use ($branch_id, $active_tab, $tasksUrl, $qsKeep, $filter_work_date) {
		if (empty($list)) {
			echo '<div class="alert alert-subl text-center text-muted mb-none">' . translate('no_information_available') . '</div>';
			return;
		}
		$byDate = array();
		foreach ($list as $item) {
			$d = !empty($item['work_date']) ? $item['work_date'] : '—';
			if (!isset($byDate[$d])) {
				$byDate[$d] = array();
			}
			$byDate[$d][] = $item;
		}
		$showDateHeadings = ($filter_work_date === '');
		$firstDate = true;
		foreach ($byDate as $workDate => $dayList) {
			$dateHeading = ($workDate !== '—' && strtotime($workDate))
				? date('l, d M Y', strtotime($workDate))
				: $workDate;
			if ($showDateHeadings) {
				if (!$firstDate) {
					echo '<hr>';
				}
				$firstDate = false;
				?>
			<h4 class="mb-md">
				<i class="far fa-calendar-alt"></i> <?= html_escape($dateHeading) ?>
				<span class="badge"><?= count($dayList) ?></span>
			</h4>
				<?php
			}
			?>
			<div class="row">
			<?php
			foreach ($dayList as $a) {
			$isUnread = !empty($a['group_unread']);
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
			if (!empty($a['teaching_activity_id']) && (int) $a['teaching_activity_id'] > 0) {
				$reviewQ['activity_id'] = (int) $a['teaching_activity_id'];
			}
			$reviewUrl = base_url('digital_diary/coordinator_review') . '?' . http_build_query($reviewQ);
			$classLabel = trim((isset($a['class_name']) ? $a['class_name'] : '') . ' / ' . (isset($a['section_name']) ? $a['section_name'] : ''), ' /');
			$activityLabel = !empty($a['activity_name']) ? $a['activity_name'] : 'All activities';
			$eventLabel = isset($a['latest_event']) && $a['latest_event'] !== ''
				? $a['latest_event']
				: (isset($a['event_label']) ? $a['event_label'] : '');
			$rwStatus = isset($a['review_window_status']) ? $a['review_window_status'] : '';
			$deadline = !empty($a['review_deadline']) ? $a['review_deadline'] : null;
			$daysLeft = isset($a['review_days_left']) ? $a['review_days_left'] : null;
			$rated = isset($a['rated_count']) ? (int) $a['rated_count'] : 0;
			$total = isset($a['student_count']) ? (int) $a['student_count'] : 0;
			$pct = $total > 0 ? (int) round(($rated / $total) * 100) : 0;
			if ($status === 'reviewed') {
				$statusBadge = '<span class="label label-success">Reviewed</span>';
				$barClass = 'progress-bar-success';
			} elseif ($rwStatus === 'overdue') {
				$statusBadge = '<span class="label label-danger">Overdue</span>';
				$barClass = 'progress-bar-danger';
			} elseif ($rwStatus === 'open') {
				$leftTxt = ($daysLeft !== null)
					? ((int) $daysLeft . ' day' . ((int) $daysLeft === 1 ? '' : 's') . ' left')
					: 'Open';
				$statusBadge = '<span class="label label-warning">' . html_escape($leftTxt) . '</span>';
				$barClass = 'progress-bar-warning';
			} else {
				$statusBadge = '<span class="label label-warning">' . html_escape(isset($a['status_label']) ? $a['status_label'] : 'Pending') . '</span>';
				$barClass = 'progress-bar-primary';
			}
			?>
			<div class="col-lg-3 col-md-3 col-sm-6">
				<div class="well well-sm">
					<p class="mb-xs">
						<strong><i class="fas fa-bookmark"></i> <?= html_escape($activityLabel) ?></strong>
					</p>
					<p class="mb-xs">
						<?= $statusBadge ?>
						<?php if ($isUnread): ?>
						<span class="label label-danger">New</span>
						<?php endif; ?>
					</p>
					<p class="mb-xs"><strong><?= html_escape($classLabel !== '' ? $classLabel : '—') ?></strong></p>
					<?php if ($status !== 'reviewed' && $deadline): ?>
					<p class="text-muted mb-xs"><i class="far fa-calendar-alt"></i> Due <?= html_escape(date('d-M-Y', strtotime($deadline))) ?></p>
					<?php endif; ?>
					<p class="mb-xs"><?= (int) $rated ?> / <?= (int) $total ?> students rated</p>
					<div class="progress progress-xs light mb-sm">
						<div class="progress-bar <?= $barClass ?>" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $pct ?>%;"></div>
					</div>
					<?php if ($eventLabel !== ''): ?>
					<p class="text-muted mb-sm"><?= html_escape($eventLabel) ?></p>
					<?php endif; ?>
					<p class="mb-none">
						<?php if ($status !== 'reviewed'): ?>
						<a href="<?= html_escape($reviewUrl) ?>" class="btn btn-default btn-xs"><i class="fas fa-clipboard-check"></i> Review</a>
						<?php else: ?>
						<a href="<?= html_escape($reviewUrl) ?>" class="btn btn-default btn-xs"><i class="fas fa-eye"></i> View</a>
						<?php endif; ?>
						<a href="<?= html_escape($twUrl) ?>" class="btn btn-default btn-xs">Today's Work</a>
						<a href="<?= html_escape($viewUrl) ?>" class="btn btn-default btn-xs">SWR</a>
						<?php if ($isUnread && !empty($a['id'])): ?>
						<a href="<?= $tasksUrl . '?read=' . (int) $a['id'] . $qsKeep . '&tab=' . urlencode($active_tab) ?>" class="btn btn-default btn-xs">Mark read</a>
						<?php endif; ?>
					</p>
				</div>
			</div>
			<?php
			}
			?>
			</div>
			<?php
		}
	};
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<div class="row widget-row-in">
				<div class="col-lg-3 col-sm-6">
					<div class="panel-body">
						<div class="widget-col-in row">
							<div class="col-md-6 col-sm-6 col-xs-6">
								<i class="fas fa-hourglass-half"></i>
								<h5>Pending</h5>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6">
								<h3 class="counter text-right mt-md text-warning"><?= (int) $pending_count ?></h3>
							</div>
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="box-top-line line-color-primary">
									<span class="text-uppercase">Activity reviews</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-sm-6">
					<div class="panel-body">
						<div class="widget-col-in row">
							<div class="col-md-6 col-sm-6 col-xs-6">
								<i class="fas fa-exclamation-triangle"></i>
								<h5>Overdue</h5>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6">
								<h3 class="counter text-right mt-md text-danger"><?= (int) $overdue_count ?></h3>
							</div>
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="box-top-line line-color-primary">
									<span class="text-uppercase">Need attention</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-sm-6">
					<div class="panel-body">
						<div class="widget-col-in row">
							<div class="col-md-6 col-sm-6 col-xs-6">
								<i class="fas fa-check-circle"></i>
								<h5>Reviewed</h5>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6">
								<h3 class="counter text-right mt-md text-success"><?= (int) $reviewed_count ?></h3>
							</div>
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="box-top-line line-color-primary">
									<span class="text-uppercase">Completed</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-sm-6">
					<div class="panel-body">
						<div class="widget-col-in row">
							<div class="col-md-6 col-sm-6 col-xs-6">
								<i class="fas fa-bell"></i>
								<h5>Unread</h5>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6">
								<h3 class="counter text-right mt-md text-primary"><?= (int) $unread_count ?></h3>
							</div>
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="box-top-line line-color-primary">
									<span class="text-uppercase">Events</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title">
					<i class="fas fa-clipboard-check"></i> Coordinator Tasks
				</h4>
				<div class="panel-btn">
					<?php if ($unread_count > 0): ?>
					<a href="<?= $tasksUrl . '?mark_all=1' . $qsKeep . '&tab=' . urlencode($active_tab) ?>" class="btn btn-default btn-circle btn-sm">Mark all read</a>
					<?php endif; ?>
				</div>
			</header>
			<div class="panel-body">
				<?php echo form_open($tasksUrl, array('method' => 'get', 'class' => 'mb-md', 'id' => 'frm_coordinator_tasks_filter')); ?>
				<input type="hidden" name="tab" value="<?= html_escape($active_tab) ?>">
				<div class="row mb-md">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-3">
						<label><?= translate('branch') ?></label>
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' data-plugin-selectTwo data-width='100%'");
						?>
					</div>
					<?php elseif (!empty($branch_id)): ?>
					<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
					<?php endif; ?>
					<div class="col-md-2">
						<label><?= translate('date') ?></label>
						<input type="text" class="form-control" name="work_date" id="ct_work_date" value="<?= html_escape($filter_work_date) ?>" placeholder="<?= translate('all') ?>" autocomplete="off" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
					</div>
					<div class="col-md-4">
						<label>&nbsp;</label>
						<div>
							<button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
							<a href="<?= html_escape($resetUrl) ?>" class="btn btn-default">All dates</a>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>
				<?php if (is_superadmin_loggedin() && !empty($branch_name)): ?>
				<p class="text-muted mb-md"><strong><?= translate('branch') ?>:</strong> <?= html_escape($branch_name) ?></p>
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
							<?php $render_cards($task_groups_pending); ?>
						</div>
						<div id="ct_tab_reviewed" class="tab-pane <?= $active_tab === 'reviewed' ? 'active' : '' ?>">
							<?php $render_cards($task_groups_reviewed); ?>
						</div>
						<div id="ct_tab_all" class="tab-pane <?= $active_tab === 'all' ? 'active' : '' ?>">
							<?php $render_cards($task_groups_all); ?>
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
			$('#frm_coordinator_tasks_filter input[name="tab"]').val(tab);
		} catch (err) {}
	});
	var pageWorkDate = $('#ct_work_date').val() || '';
	var ctReadyAt = Date.now() + 600;
	$('#ct_work_date').on('changeDate', function () {
		if (Date.now() < ctReadyAt) {
			return;
		}
		var v = $(this).val() || '';
		if (v === pageWorkDate) {
			return;
		}
		$('#frm_coordinator_tasks_filter').submit();
	});
})(jQuery);
</script>
