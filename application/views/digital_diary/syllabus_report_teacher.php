<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$today_date = isset($today_date) && $today_date !== '' ? $today_date : date('Y-m-d');
$rows = isset($teacher_syllabus_rows) && is_array($teacher_syllabus_rows) ? $teacher_syllabus_rows : array();
$scopeCounts = isset($teacher_syllabus_scope_counts) ? $teacher_syllabus_scope_counts : array('assigned' => 0, 'class_teacher' => 0);
$dateDisp = strtotime($today_date) ? date('d M Y', strtotime($today_date)) : $today_date;
$classOptions = array();
$overviewByScope = array(
	'assigned' => array('subjects' => 0, 'lessons_total' => 0, 'lessons_completed' => 0, 'progress_total' => 0, 'progress_count' => 0),
	'class_teacher' => array('subjects' => 0, 'lessons_total' => 0, 'lessons_completed' => 0, 'progress_total' => 0, 'progress_count' => 0),
);
$uniqueSubjectsByScope = array('assigned' => array(), 'class_teacher' => array());
foreach ($rows as $row) {
	$classOptions[(int) $row['class_id']] = (string) $row['class_name'];
	$scopeType = $row['scope_type'] === 'class_teacher' ? 'class_teacher' : 'assigned';
	$uniqueKey = (int) $row['class_id'] . ':' . (int) $row['section_id'] . ':' . (int) $row['subject_id'];
	if (isset($uniqueSubjectsByScope[$scopeType][$uniqueKey])) {
		continue;
	}
	$uniqueSubjectsByScope[$scopeType][$uniqueKey] = true;
	$overviewByScope[$scopeType]['subjects']++;
	if ($row['lessons_completed'] !== null) {
		$overviewByScope[$scopeType]['lessons_total'] += (int) $row['lessons_total'];
		$overviewByScope[$scopeType]['lessons_completed'] += (int) $row['lessons_completed'];
	}
	if ($row['syllabus_percent'] !== '—') {
		$overviewByScope[$scopeType]['progress_total'] += (int) $row['syllabus_percent'];
		$overviewByScope[$scopeType]['progress_count']++;
	}
}
$initialScope = !empty($scopeCounts['assigned']) || empty($scopeCounts['class_teacher']) ? 'assigned' : 'class_teacher';
foreach (array('assigned', 'class_teacher') as $scopeType) {
	$overviewByScope[$scopeType]['average_progress'] = $overviewByScope[$scopeType]['progress_count'] > 0
		? (int) round($overviewByScope[$scopeType]['progress_total'] / $overviewByScope[$scopeType]['progress_count'])
		: null;
}
$activeOverview = $overviewByScope[$initialScope];
?>
<style>
	.syl-teacher-page .syl-filter-panel {
		margin-bottom: 20px;
		padding: 16px 18px;
		border: 1px solid #e7edf4;
		border-radius: 12px;
		background: #f8fafc;
	}
	.syl-teacher-page .syl-filter-panel .form-group {
		margin-bottom: 0;
	}
	.syl-teacher-page .syl-filter-panel label {
		color: #536273;
		font-size: 12px;
		font-weight: 600;
	}
	.syl-teacher-page .syl-filter-panel .form-control,
	.syl-teacher-page .syl-filter-panel .btn {
		height: 42px;
		border-radius: 8px;
	}
	.syl-teacher-page .syl-overview {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		margin-bottom: 16px;
	}
	.syl-teacher-page .syl-overview h3 {
		margin: 0;
		color: #26384a;
		font-size: 20px;
		font-weight: 700;
	}
	.syl-teacher-page .syl-overview small {
		display: block;
		margin-top: 4px;
		color: #7b8794;
	}
	.syl-teacher-page .syl-context {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}
	.syl-teacher-page .syl-context span {
		padding: 7px 10px;
		border: 1px solid #e5ebf2;
		border-radius: 20px;
		background: #fff;
		color: #536273;
		font-size: 12px;
	}
	.syl-teacher-page .syl-stat {
		min-height: 120px;
		margin-bottom: 16px;
		padding: 16px 18px;
		border: 1px solid #e7edf4;
		border-radius: 12px;
		background: #fff;
		box-shadow: 0 4px 14px rgba(31, 45, 61, .045);
	}
	.syl-teacher-page .syl-stat-label {
		display: block;
		color: #657587;
		font-size: 12px;
		font-weight: 600;
	}
	.syl-teacher-page .syl-stat-value {
		display: block;
		margin-top: 8px;
		color: #26384a;
		font-size: 28px;
		font-weight: 700;
		line-height: 1.15;
	}
	.syl-teacher-page .syl-stat-note {
		display: block;
		margin-top: 5px;
		color: #8995a2;
		font-size: 11px;
	}
	.syl-teacher-page .syl-scope-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-bottom: 14px;
	}
	.syl-teacher-page .syl-scope-tab {
		min-width: 220px;
		padding: 10px 14px;
		text-align: left;
	}
	.syl-teacher-page .syl-scope-tab small {
		display: block;
		margin: 4px 0 0 22px;
		color: inherit;
		opacity: .8;
	}
	.syl-teacher-page .syl-table th {
		vertical-align: middle;
		white-space: nowrap;
	}
	.syl-teacher-page .syl-progress-track {
		width: 110px;
		height: 7px;
		margin: 0 auto 5px;
		overflow: hidden;
		border-radius: 6px;
		background: #edf1f5;
	}
	.syl-teacher-page .syl-progress-fill {
		height: 100%;
		border-radius: inherit;
		background: #33a06f;
	}
	.syl-teacher-page .syl-progress-fill.syl-progress-mid {
		background: #e6a23c;
	}
	.syl-teacher-page .syl-progress-fill.syl-progress-low {
		background: #d9534f;
	}
	@media (max-width: 600px) {
		.syl-teacher-page .syl-overview {
			align-items: flex-start;
			flex-direction: column;
		}
		.syl-teacher-page .syl-scope-tab {
			min-width: 0;
			flex: 1 1 100%;
		}
	}
</style>

<div class="row syl-teacher-page">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-book-open"></i> My Syllabus Progress</h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/syllabus_report'), array('method' => 'get', 'class' => 'syl-filter-panel no-print')); ?>
				<input type="hidden" name="branch_id" value="<?= html_escape($branch_id) ?>">
				<input type="hidden" name="mode" value="teacher">
				<div class="row">
					<div class="col-sm-8 col-md-6">
						<div class="form-group">
							<label for="syl_teacher_date">Report date</label>
							<input type="text" class="form-control" id="syl_teacher_date" name="date" value="<?= html_escape($today_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
					</div>
					<div class="col-sm-4 col-md-3">
						<div class="form-group">
							<label>&nbsp;</label>
							<button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Apply</button>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div class="syl-scope-tabs no-print" role="group" aria-label="Choose syllabus overview">
					<button type="button" class="btn <?= $initialScope === 'assigned' ? 'btn-primary active' : 'btn-default' ?> syl-scope-tab" data-scope="assigned">
						<i class="fas fa-book"></i> Subject teacher
						<small>My allocated subjects · <?= (int) $scopeCounts['assigned'] ?></small>
					</button>
					<button type="button" class="btn <?= $initialScope === 'class_teacher' ? 'btn-primary active' : 'btn-default' ?> syl-scope-tab" data-scope="class_teacher" <?= empty($scopeCounts['class_teacher']) ? 'disabled' : '' ?>>
						<i class="fas fa-users"></i> Class teacher
						<small>All class subjects · <?= (int) $scopeCounts['class_teacher'] ?></small>
					</button>
				</div>

				<div id="syl_teacher_print_area">
					<div class="syl-overview">
						<div>
							<h3>Syllabus overview · <span id="syl_overview_scope_name"><?= $initialScope === 'class_teacher' ? 'Class teacher' : 'Subject teacher' ?></span></h3>
							<small id="syl_overview_description"><?= $initialScope === 'class_teacher' ? 'Progress across all subjects in your class-teacher class.' : 'Progress across your allocated subjects.' ?></small>
						</div>
						<div class="syl-context">
							<span><i class="far fa-calendar-alt"></i> <?= html_escape($dateDisp) ?></span>
							<?php if ($branch_name !== ''): ?><span><i class="fas fa-school"></i> <?= html_escape($branch_name) ?></span><?php endif; ?>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-6 col-md-4">
							<div class="syl-stat">
								<span class="syl-stat-label">Class-subjects tracked</span>
								<strong class="syl-stat-value" id="syl_overview_subjects" data-assigned="<?= (int) $overviewByScope['assigned']['subjects'] ?>" data-class-teacher="<?= (int) $overviewByScope['class_teacher']['subjects'] ?>"><?= (int) $activeOverview['subjects'] ?></strong>
								<small class="syl-stat-note">In the selected teacher view</small>
							</div>
						</div>
						<div class="col-xs-6 col-md-4">
							<div class="syl-stat">
								<span class="syl-stat-label">Lessons completed</span>
								<strong class="syl-stat-value" id="syl_overview_lessons" data-assigned="<?= (int) $overviewByScope['assigned']['lessons_completed'] ?> / <?= (int) $overviewByScope['assigned']['lessons_total'] ?>" data-class-teacher="<?= (int) $overviewByScope['class_teacher']['lessons_completed'] ?> / <?= (int) $overviewByScope['class_teacher']['lessons_total'] ?>"><?= (int) $activeOverview['lessons_completed'] ?> / <?= (int) $activeOverview['lessons_total'] ?></strong>
								<small class="syl-stat-note">Based on the selected report date</small>
							</div>
						</div>
						<div class="col-xs-12 col-md-4">
							<div class="syl-stat">
								<span class="syl-stat-label">Average syllabus progress</span>
								<strong class="syl-stat-value" id="syl_overview_progress" data-assigned="<?= $overviewByScope['assigned']['average_progress'] === null ? '—' : (int) $overviewByScope['assigned']['average_progress'] . '%' ?>" data-class-teacher="<?= $overviewByScope['class_teacher']['average_progress'] === null ? '—' : (int) $overviewByScope['class_teacher']['average_progress'] . '%' ?>"><?= $activeOverview['average_progress'] === null ? '—' : (int) $activeOverview['average_progress'] . '%' ?></strong>
								<small class="syl-stat-note">Across subjects with syllabus data</small>
							</div>
						</div>
					</div>

					<section class="panel panel-featured-left panel-featured-primary">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-search"></i> Find syllabus progress</h4>
						</header>
						<div class="panel-body">
							<div class="row no-print">
								<div class="col-md-4 col-sm-6">
									<div class="form-group">
										<label for="syl_teacher_class">Filter by class</label>
										<select class="form-control" id="syl_teacher_class">
											<option value="">All classes</option>
											<?php foreach ($classOptions as $classID => $className): ?>
											<option value="<?= (int) $classID ?>"><?= html_escape($className) ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
								<div class="col-md-8 col-sm-6">
									<div class="form-group">
										<label for="syl_teacher_search">Search class, subject, or teacher</label>
										<input type="search" class="form-control" id="syl_teacher_search" placeholder="Type a class, subject, or teacher name">
									</div>
								</div>
							</div>

							<div class="table-responsive">
								<table class="table table-bordered table-hover mb-none syl-table" id="syl_teacher_report_table">
									<thead>
										<tr>
											<th>Class / Section</th>
											<th>Subject</th>
											<th>Subject teacher</th>
											<th class="text-center">Lessons completed</th>
											<th class="text-center">Syllabus progress</th>
											<th class="text-center no-print">Details</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($rows as $row):
											$detailQuery = array(
												'branch_id' => $branch_id,
												'from' => $today_date,
												'to' => $today_date,
												'class_id' => (int) $row['class_id'],
												'section_id' => (int) $row['section_id'],
												'subject_id' => (int) $row['subject_id'],
												'scope_type' => $row['scope_type'],
											);
											$detailUrl = base_url('digital_diary/syllabus_report_class_detail') . '?' . http_build_query($detailQuery);
											$percentValue = $row['syllabus_percent'] !== '—' ? (int) $row['syllabus_percent'] : null;
											$progressClass = $percentValue !== null && $percentValue < 40
												? 'syl-progress-low'
												: ($percentValue !== null && $percentValue < 70 ? 'syl-progress-mid' : '');
										?>
										<tr class="syl-teacher-row" data-scope="<?= html_escape($row['scope_type']) ?>" data-class-id="<?= (int) $row['class_id'] ?>">
											<td><strong><?= html_escape($row['class_name']) ?></strong> <span class="text-muted">(<?= html_escape($row['section_name']) ?>)</span></td>
											<td>
												<?= html_escape($row['subject_name']) ?>
												<small class="syl-scope-kind"><?= $row['scope_type'] === 'class_teacher' ? 'Class subject' : 'My allocated subject' ?></small>
											</td>
											<td><?= !empty($row['subject_teacher_names']) ? html_escape(implode(', ', $row['subject_teacher_names'])) : '<span class="text-muted">Not assigned</span>' ?></td>
											<td class="text-center">
												<?php if ($row['lessons_completed'] === null): ?>
												<span class="text-muted">Not recorded / <?= (int) $row['lessons_total'] ?></span>
												<?php else: ?>
												<strong><?= (int) $row['lessons_completed'] ?></strong> / <?= (int) $row['lessons_total'] ?>
												<?php endif; ?>
											</td>
											<td class="text-center">
												<?php if ($percentValue === null): ?>
												<span class="text-muted">No syllabus data</span>
												<?php else: ?>
												<div class="syl-progress-track"><div class="syl-progress-fill <?= $progressClass ?>" style="width:<?= max(0, min(100, $percentValue)) ?>%;"></div></div>
												<strong><?= $percentValue ?>%</strong>
												<?php endif; ?>
											</td>
											<td class="text-center no-print"><a href="<?= html_escape($detailUrl) ?>" class="btn btn-primary btn-xs"><i class="fas fa-eye"></i> View</a></td>
										</tr>
										<?php endforeach; ?>
										<tr id="syl_teacher_no_results" style="display:none;">
											<td colspan="6" class="text-center text-muted">No subjects match this view. Try another class or search.</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="text-muted mt-sm no-print" id="syl_teacher_match_count"></div>
						</div>
					</section>
				</div>

				<div class="well well-sm text-center no-print">
					<button type="button" class="btn btn-success" onclick="sylTeacherExportExcel();"><i class="fas fa-file-excel"></i> Excel</button>
				</div>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
(function ($) {
	var $rows = $('#syl_teacher_report_table .syl-teacher-row');
	var $class = $('#syl_teacher_class');
	var $search = $('#syl_teacher_search');
	var scope = <?= json_encode($initialScope) ?>;
	var updateOverview = function () {
		var isClassTeacher = scope === 'class_teacher';
		var scopeName = isClassTeacher ? 'Class teacher' : 'Subject teacher';
		$('#syl_overview_scope_name').text(scopeName);
		$('#syl_overview_description').text(
			isClassTeacher
				? 'Progress across all subjects in your class-teacher class.'
				: 'Progress across your allocated subjects.'
		);
		$('#syl_overview_subjects, #syl_overview_lessons, #syl_overview_progress').each(function () {
			$(this).text($(this).attr(isClassTeacher ? 'data-class-teacher' : 'data-assigned'));
		});
	};
	var filterRows = function () {
		var classID = String($class.val() || '');
		var query = String($search.val() || '').toLowerCase().trim();
		var visible = 0;
		$rows.each(function () {
			var $row = $(this);
			var matches = String($row.attr('data-scope')) === scope;
			matches = matches && (!classID || String($row.attr('data-class-id')) === classID);
			matches = matches && (!query || $row.text().toLowerCase().indexOf(query) !== -1);
			$row.toggle(matches);
			if (matches) {
				visible++;
			}
		});
		$('#syl_teacher_no_results').toggle(visible === 0);
		$('#syl_teacher_match_count').text('Showing ' + visible + ' subject(s)');
	};
	$('.syl-scope-tab').on('click', function () {
		if ($(this).prop('disabled')) {
			return;
		}
		scope = String($(this).attr('data-scope'));
		$('.syl-scope-tab').removeClass('btn-primary active').addClass('btn-default');
		$(this).removeClass('btn-default').addClass('btn-primary active');
		filterRows();
		updateOverview();
	});
	$class.add($search).on('input change', filterRows);
	filterRows();
	updateOverview();
})(jQuery);

function sylTeacherExportExcel() {
	var table = document.getElementById('syl_teacher_report_table');
	if (!table) return;
	var clone = table.cloneNode(true);
	var hidden = clone.querySelectorAll('.no-print');
	for (var i = 0; i < hidden.length; i++) {
		if (hidden[i].parentNode) hidden[i].parentNode.removeChild(hidden[i]);
	}
	var html = '<html><head><meta charset="UTF-8"></head><body>' + clone.outerHTML + '</body></html>';
	var link = document.createElement('a');
	link.href = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
	link.download = 'syllabus_report.xls';
	link.click();
}
</script>
