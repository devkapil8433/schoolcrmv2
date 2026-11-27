<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_name = isset($branch_name) ? $branch_name : '';
$filterColumn = is_superadmin_loggedin() ? 'col-md-3 col-sm-6' : 'col-md-4 col-sm-6';
$from_date = isset($from_date) ? $from_date : date('Y-m-d');
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$subjects = isset($np_subjects) ? $np_subjects : array();
$rows = isset($np_rows) ? $np_rows : array();
$totals = isset($np_totals_per_subject) ? $np_totals_per_subject : array();
$teacherSubjectGroups = isset($np_teacher_subject_groups) ? $np_teacher_subject_groups : array('assigned' => array(), 'class_teacher' => array());
$teacherNamesByScope = isset($np_teacher_names_by_scope) ? $np_teacher_names_by_scope : array();
$totalsByType = isset($np_totals_per_subject_type) ? $np_totals_per_subject_type : array('assigned' => array(), 'class_teacher' => array());
$teacherSubjectLists = array('assigned' => array(), 'class_teacher' => array());
foreach ($subjects as $subject) {
	$subjectId = (int) $subject['id'];
	foreach (array('assigned', 'class_teacher') as $scopeType) {
		if (!empty($teacherSubjectGroups[$scopeType][$subjectId])) {
			$teacherSubjectLists[$scopeType][] = $subject;
		}
	}
}
$total_students_all = isset($np_total_students_all) ? (int) $np_total_students_all : 0;
$total_non_performers = isset($np_total_non_performers) ? (int) $np_total_non_performers : 0;
$student_list = isset($np_student_list) ? $np_student_list : array();
$colspan = 2 + count($subjects);
$fromDisp = strtotime($from_date) ? date('d M Y', strtotime($from_date)) : $from_date;
$toDisp = strtotime($to_date) ? date('d M Y', strtotime($to_date)) : $to_date;
$classesWithIssues = 0;
foreach ($rows as $rr) {
	if (!empty($rr['non_performer_sum'])) {
		$classesWithIssues++;
	}
}
$studentClassOptions = array();
foreach ($rows as $classRow) {
	$classId = (int) $classRow['class_id'];
	if ($classId > 0) {
		$studentClassOptions[$classId] = (string) $classRow['class_name'];
	}
}
$teacherScopeRows = array();
$teacherScopeClasses = array();
$teacherScopeRowCounts = array('assigned' => 0, 'class_teacher' => 0);
foreach ($rows as $classRow) {
	foreach (array('assigned', 'class_teacher') as $scopeType) {
		$scopeSubjectIDs = isset($classRow['subject_ids_by_type'][$scopeType])
			? $classRow['subject_ids_by_type'][$scopeType]
			: array();
		foreach ($teacherSubjectLists[$scopeType] as $subject) {
			$subjectId = (int) $subject['id'];
			if (!in_array($subjectId, $scopeSubjectIDs, true)) {
				continue;
			}
			$teacherNameKey = (int) $classRow['class_id'] . ':' . $subjectId . ':' . $scopeType;
			$teacherScopeRows[] = array(
				'class_id' => (int) $classRow['class_id'],
				'class_name' => (string) $classRow['class_name'],
				'total_students' => (int) $classRow['total_students'],
				'is_class_teacher' => !empty($classRow['is_class_teacher']),
				'scope_type' => $scopeType,
				'subject_id' => $subjectId,
				'subject_name' => (string) $subject['name'],
				'subject_teacher_names' => isset($teacherNamesByScope[$teacherNameKey]) ? $teacherNamesByScope[$teacherNameKey] : array(),
				'non_performers' => isset($classRow['subject_counts_by_type'][$scopeType][$subjectId])
					? (int) $classRow['subject_counts_by_type'][$scopeType][$subjectId]
					: 0,
			);
			$teacherScopeRowCounts[$scopeType]++;
			$teacherScopeClasses[$classRow['class_id']] = (string) $classRow['class_name'];
		}
	}
}
$teacherScopeOverview = isset($np_teacher_scope_overview) ? $np_teacher_scope_overview : array();
$initialTeacherScope = !empty($teacherSubjectLists['assigned']) || empty($teacherSubjectLists['class_teacher']) ? 'assigned' : 'class_teacher';
$activeTeacherOverview = isset($teacherScopeOverview[$initialTeacherScope])
	? $teacherScopeOverview[$initialTeacherScope]
	: array(
		'students' => $total_students_all,
		'non_performers' => $total_non_performers,
		'classes_with_issues' => $classesWithIssues,
		'subjects_tracked' => count($subjects),
	);
$assignedOverview = isset($teacherScopeOverview['assigned']) ? $teacherScopeOverview['assigned'] : $activeTeacherOverview;
$classTeacherOverview = isset($teacherScopeOverview['class_teacher']) ? $teacherScopeOverview['class_teacher'] : $activeTeacherOverview;
?>
<style>
	.np-report-page .np-class-card {
		height: 100%;
		margin-bottom: 20px;
		overflow: hidden;
		border: 1px solid #e6ebf2;
		border-radius: 12px;
		background: #fff;
		box-shadow: 0 4px 14px rgba(31, 45, 61, .06);
	}
	.np-report-page .np-class-nav {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-bottom: 16px;
	}
	.np-report-page .np-class-nav a {
		display: inline-flex;
		align-items: center;
		gap: 7px;
		padding: 7px 11px;
		border: 1px solid #e3eaf3;
		border-radius: 20px;
		background: #fff;
		color: #34495e;
		font-size: 12px;
		font-weight: 600;
		text-decoration: none;
	}
	.np-report-page .np-class-nav a:hover,
	.np-report-page .np-class-nav a:focus {
		border-color: #9dbce5;
		background: #f4f8ff;
		color: #2868bd;
	}
	.np-report-page .np-class-nav small {
		color: #8a96a3;
		font-size: 10px;
		font-weight: 400;
	}
	.np-report-page .np-class-card-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		padding: 18px 20px;
		border-bottom: 1px solid #edf0f5;
		background: linear-gradient(120deg, #f8fbff, #fff);
	}
	.np-report-page .np-class-title-wrap,
	.np-report-page .np-subject-main {
		display: flex;
		align-items: center;
		gap: 12px;
		min-width: 0;
	}
	.np-report-page .np-class-icon,
	.np-report-page .np-subject-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex: 0 0 40px;
		width: 40px;
		height: 40px;
		border-radius: 11px;
		background: #eaf2ff;
		color: #2868bd;
	}
	.np-report-page .np-class-kicker {
		display: block;
		margin-bottom: 3px;
		color: #7b8794;
		font-size: 11px;
		font-weight: 600;
		letter-spacing: .06em;
		text-transform: uppercase;
	}
	.np-report-page .np-class-title {
		margin: 0;
		color: #243447;
		font-size: 18px;
		font-weight: 700;
	}
	.np-report-page .np-class-badge {
		display: inline-block;
		margin-top: 5px;
		padding: 3px 8px;
		border-radius: 20px;
		background: #e8f7ef;
		color: #218653;
		font-size: 11px;
		font-weight: 600;
	}
	.np-report-page .np-class-stats {
		display: flex;
		flex: 0 0 auto;
		gap: 18px;
		text-align: right;
	}
	.np-report-page .np-class-stats strong,
	.np-report-page .np-class-stats small {
		display: block;
	}
	.np-report-page .np-class-stats strong {
		color: #243447;
		font-size: 19px;
		line-height: 1.2;
	}
	.np-report-page .np-class-stats small {
		margin-top: 3px;
		color: #7b8794;
		font-size: 11px;
	}
	.np-report-page .np-class-card-body {
		padding: 8px 20px 16px;
	}
	.np-report-page .np-subject-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 14px;
		padding: 12px 0;
		border-bottom: 1px solid #f0f2f5;
	}
	.np-report-page .np-subject-row:last-child {
		border-bottom: 0;
	}
	.np-report-page .np-subject-icon {
		flex-basis: 34px;
		width: 34px;
		height: 34px;
		border-radius: 9px;
		background: #f1f5fa;
		color: #66788a;
	}
	.np-report-page .np-subject-name {
		overflow: hidden;
		color: #34495e;
		font-weight: 600;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
	.np-report-page .np-subject-name small {
		display: block;
		margin-top: 2px;
		color: #8a96a3;
		font-size: 11px;
		font-weight: 400;
	}
	.np-report-page .np-subject-count {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 38px;
		height: 32px;
		padding: 0 10px;
		border-radius: 18px;
		font-size: 13px;
		font-weight: 700;
	}
	.np-report-page .np-count-alert {
		background: #fff0ef;
		color: #d64545;
	}
	.np-report-page .np-count-clear {
		background: #eaf7f0;
		color: #248653;
	}
	.np-report-page .np-class-empty {
		padding: 22px 10px;
		color: #7b8794;
		text-align: center;
	}
	.np-report-page .np-teacher-matrix thead th {
		vertical-align: middle;
		white-space: nowrap;
	}
	.np-report-page .np-assigned-heading {
		background: #eaf2ff;
		color: #2868bd;
	}
	.np-report-page .np-class-teacher-heading {
		background: #e8f7ef;
		color: #218653;
	}
	.np-report-page .np-scope-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}
	.np-report-page .np-scope-tab {
		min-width: 220px;
		padding: 10px 14px;
		text-align: left;
	}
	.np-report-page .np-scope-tab small {
		display: block;
		margin: 4px 0 0 22px;
		color: inherit;
		opacity: .8;
	}
	.np-report-page .np-scope-kind {
		display: block;
		margin-top: 3px;
		color: #7b8794;
	}
	.np-report-page .np-count-button {
		min-width: 94px;
	}
	.np-report-page .np-count-button span {
		margin-left: 4px;
	}
	.np-report-page .np-report-filter-panel {
		margin-bottom: 22px;
		padding: 16px 18px;
		border: 1px solid #e7edf4;
		border-radius: 12px;
		background: #f8fafc;
	}
	.np-report-page .np-filter-heading {
		margin-bottom: 14px;
	}
	.np-report-page .np-filter-heading strong {
		display: block;
		color: #26384a;
		font-size: 15px;
	}
	.np-report-page .np-filter-heading small {
		color: #7b8794;
	}
	.np-report-page .np-report-filter-panel .form-group {
		margin-bottom: 0;
	}
	.np-report-page .np-report-filter-panel label {
		margin-bottom: 6px;
		color: #536273;
		font-size: 12px;
		font-weight: 600;
	}
	.np-report-page .np-report-filter-panel .form-control,
	.np-report-page .np-report-filter-panel .btn {
		height: 42px;
		border-radius: 8px;
	}
	.np-report-page .np-report-filter-panel .btn {
		padding-top: 10px;
	}
	.np-report-page .np-report-heading {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		margin: 0 0 16px;
	}
	.np-report-page .np-report-heading h3 {
		margin: 0;
		color: #26384a;
		font-size: 20px;
		font-weight: 700;
	}
	.np-report-page .np-report-heading small {
		display: block;
		margin-top: 4px;
		color: #7b8794;
	}
	.np-report-page .np-report-context {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		justify-content: flex-end;
	}
	.np-report-page .np-context-chip {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 7px 10px;
		border: 1px solid #e5ebf2;
		border-radius: 20px;
		background: #fff;
		color: #536273;
		font-size: 12px;
	}
	.np-report-page .np-summary-card {
		position: relative;
		min-height: 136px;
		margin-bottom: 16px;
		padding: 17px 18px;
		overflow: hidden;
		border: 1px solid #e7edf4;
		border-radius: 12px;
		background: #fff;
		box-shadow: 0 4px 14px rgba(31, 45, 61, .045);
	}
	.np-report-page .np-summary-icon {
		float: right;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 40px;
		height: 40px;
		border-radius: 12px;
		background: #edf4ff;
		color: #3478c8;
		font-size: 16px;
	}
	.np-report-page .np-summary-label {
		display: block;
		margin: 1px 0 10px;
		color: #657587;
		font-size: 12px;
		font-weight: 600;
	}
	.np-report-page .np-summary-value {
		display: block;
		color: #26384a;
		font-size: 30px;
		font-weight: 700;
		line-height: 1.1;
	}
	.np-report-page .np-summary-note {
		display: block;
		margin-top: 8px;
		color: #8995a2;
		font-size: 11px;
	}
	.np-report-page .np-summary-danger .np-summary-icon {
		background: #fff0ef;
		color: #d64545;
	}
	.np-report-page .np-summary-warning .np-summary-icon {
		background: #fff6e5;
		color: #c88313;
	}
	.np-report-page .np-summary-info .np-summary-icon {
		background: #eaf7f0;
		color: #218653;
	}
	@media (max-width: 600px) {
		.np-report-page .np-report-heading {
			align-items: flex-start;
			flex-direction: column;
		}
		.np-report-page .np-report-context {
			justify-content: flex-start;
		}
		.np-report-page .np-report-filter-panel .form-group {
			margin-bottom: 12px;
		}
		.np-report-page .np-class-card-head {
			align-items: flex-start;
			flex-direction: column;
			padding: 15px;
		}
		.np-report-page .np-class-stats {
			width: 100%;
			justify-content: flex-start;
			text-align: left;
		}
		.np-report-page .np-class-card-body {
			padding-right: 15px;
			padding-left: 15px;
		}
	}
</style>
<div class="row np-report-page" id="np_report_table">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-user-times"></i> Non Performer Report</h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/non_performer_report'), array('method' => 'get', 'class' => 'mb-md no-print np-report-filter-panel')); ?>
				<div class="np-filter-heading">
					<strong><i class="far fa-calendar-alt"></i> Select report period</strong>
					<small>Choose the dates to review incomplete work.</small>
				</div>
				<div class="row">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-3 col-sm-6">
						<div class="form-group">
							<label class="control-label"><?= translate('branch') ?></label>
							<?php $arrayBranch = $this->app_lib->getSelectList('branch'); ?>
							<?= form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'") ?>
						</div>
					</div>
					<?php endif; ?>
					<div class="<?= $filterColumn ?>">
						<div class="form-group">
							<label class="control-label">From date</label>
							<input type="text" class="form-control" name="from" value="<?= html_escape($from_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
					</div>
					<div class="<?= $filterColumn ?>">
						<div class="form-group">
							<label class="control-label">To date</label>
							<input type="text" class="form-control" name="to" value="<?= html_escape($to_date) ?>" data-plugin-datepicker data-plugin-options='{"format":"yyyy-mm-dd"}'>
						</div>
					</div>
					<div class="<?= $filterColumn ?>">
						<div class="form-group">
							<label class="control-label">&nbsp;</label>
							<button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Apply filters</button>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>

				<?php if (is_teacher_loggedin()): ?>
				<div class="np-scope-finder np-scope-tabs mb-md no-print">
					<button type="button" class="btn <?= $initialTeacherScope === 'assigned' ? 'btn-primary active' : 'btn-default' ?> np-scope-tab" data-scope="assigned">
						<i class="fas fa-book"></i> Subject teacher <small>My subjects · <?= count($teacherSubjectLists['assigned']) ?></small>
					</button>
					<button type="button" class="btn <?= $initialTeacherScope === 'class_teacher' ? 'btn-primary active' : 'btn-default' ?> np-scope-tab" data-scope="class_teacher" <?= empty($teacherSubjectLists['class_teacher']) ? 'disabled' : '' ?>>
						<i class="fas fa-users"></i> Class teacher <small>All class subjects · <?= count($teacherSubjectLists['class_teacher']) ?></small>
					</button>
				</div>
				<?php endif; ?>

				<div id="np_print_area">
					<div class="np-report-heading">
						<div>
							<h3>Report overview<?php if (is_teacher_loggedin()): ?> · <span id="np_overview_scope_name"><?= $initialTeacherScope === 'class_teacher' ? 'Class teacher' : 'Subject teacher' ?></span><?php endif; ?></h3>
							<small id="np_overview_description"><?= is_teacher_loggedin() ? 'Students and subjects in the selected teacher view' : 'Students and subjects needing attention in the selected period' ?></small>
							<h3 class="visible-print">Non Performer Report</h3>
						</div>
						<div class="np-report-context">
							<span class="np-context-chip"><i class="far fa-calendar-alt"></i> <?= html_escape($fromDisp) ?><?= $from_date !== $to_date ? ' – ' . html_escape($toDisp) : '' ?></span>
							<?php if ($branch_name !== ''): ?>
							<span class="np-context-chip"><i class="fas fa-school"></i> <?= html_escape($branch_name) ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div class="row np-summary-row">
						<div class="col-xs-6 col-md-3">
							<div class="np-summary-card">
								<span class="np-summary-icon"><i class="fas fa-users"></i></span>
								<span class="np-summary-label"><?= is_teacher_loggedin() ? 'Allocated students' : 'Total students' ?></span>
								<strong class="np-summary-value" id="np_overview_students" <?= is_teacher_loggedin() ? 'data-assigned="' . (int) $assignedOverview['students'] . '" data-class-teacher="' . (int) $classTeacherOverview['students'] . '"' : '' ?>><?= is_teacher_loggedin() ? (int) $activeTeacherOverview['students'] : (int) $total_students_all ?></strong>
								<small class="np-summary-note"><?= is_teacher_loggedin() ? 'In the selected teacher view' : 'Currently enrolled students' ?></small>
							</div>
						</div>
						<div class="col-xs-6 col-md-3">
							<div class="np-summary-card np-summary-danger">
								<span class="np-summary-icon"><i class="fas fa-user-clock"></i></span>
								<span class="np-summary-label">Students needing support</span>
								<strong class="np-summary-value" id="np_overview_non_performers" <?= is_teacher_loggedin() ? 'data-assigned="' . (int) $assignedOverview['non_performers'] . '" data-class-teacher="' . (int) $classTeacherOverview['non_performers'] . '"' : '' ?>><?= is_teacher_loggedin() ? (int) $activeTeacherOverview['non_performers'] : (int) $total_non_performers ?></strong>
								<small class="np-summary-note">Unique students with incomplete work</small>
							</div>
						</div>
						<div class="col-xs-6 col-md-3">
							<div class="np-summary-card np-summary-warning">
								<span class="np-summary-icon"><i class="fas fa-school"></i></span>
								<span class="np-summary-label">Classes with issues</span>
								<strong class="np-summary-value" id="np_overview_classes" <?= is_teacher_loggedin() ? 'data-assigned="' . (int) $assignedOverview['classes_with_issues'] . '" data-class-teacher="' . (int) $classTeacherOverview['classes_with_issues'] . '"' : '' ?>><?= is_teacher_loggedin() ? (int) $activeTeacherOverview['classes_with_issues'] : (int) $classesWithIssues ?></strong>
								<small class="np-summary-note">Classes with incomplete-work records</small>
							</div>
						</div>
						<div class="col-xs-6 col-md-3">
							<div class="np-summary-card np-summary-info">
								<span class="np-summary-icon"><i class="fas fa-book-open"></i></span>
								<span class="np-summary-label">Subjects tracked</span>
								<strong class="np-summary-value" id="np_overview_subjects" <?= is_teacher_loggedin() ? 'data-assigned="' . (int) $assignedOverview['subjects_tracked'] . '" data-class-teacher="' . (int) $classTeacherOverview['subjects_tracked'] . '"' : '' ?>><?= is_teacher_loggedin() ? (int) $activeTeacherOverview['subjects_tracked'] : count($subjects) ?></strong>
								<small class="np-summary-note">Subjects in your report scope</small>
							</div>
						</div>
					</div>

					<section class="panel panel-featured-left panel-featured-danger mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-table"></i> <?= is_teacher_loggedin() ? 'Class-wise defaulter list · <span id="np_report_scope_name">' . ($initialTeacherScope === 'class_teacher' ? 'Class teacher' : 'Subject teacher') . '</span>' : 'Class × Subject Non Performers' ?></h4>
						</header>
						<div class="panel-body">
							<div class="alert alert-info no-print mb-md">
								<?= is_teacher_loggedin()
									? 'Filter by class or subject, then select a non-performer count to see the students.'
									: 'Click a subject count to open the student list for that class and subject.' ?>
							</div>
							<?php if (is_teacher_loggedin()): ?>
							<?php if (empty($rows)): ?>
							<div class="text-center text-muted"><?= translate('no_information_available'); ?></div>
							<?php else: ?>
							<div class="np-scope-finder mb-md">
								<div class="row mt-sm">
									<div class="col-md-4 col-sm-6">
										<label for="np_scope_class">Filter by class</label>
										<select class="form-control" id="np_scope_class">
											<option value="">All classes</option>
											<?php foreach ($teacherScopeClasses as $classId => $className): ?>
											<option value="<?= (int) $classId ?>"><?= html_escape($className) ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="col-md-8 col-sm-6">
										<label for="np_scope_search">Search class, subject, or teacher</label>
										<input type="search" class="form-control" id="np_scope_search" placeholder="Type a class, subject, or teacher name">
									</div>
								</div>
							</div>
							<div class="table-responsive">
								<table class="table table-bordered table-hover mb-none np-teacher-matrix" id="np_teacher_report_table">
									<thead>
										<tr>
											<th>Class</th>
											<th>Subject</th>
											<th>Subject teacher</th>
											<th class="text-center">Students</th>
											<th class="text-center">Non-performers</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($teacherScopeRows as $scopeRow):
											$detail_q = array(
												'branch_id' => $branch_id,
												'from' => $from_date,
												'to' => $to_date,
												'class_id' => $scopeRow['class_id'],
												'subject_id' => $scopeRow['subject_id'],
												'scope_type' => $scopeRow['scope_type'],
											);
											$detail_url = base_url('digital_diary/non_performer_report_detail') . '?' . http_build_query($detail_q);
										?>
										<tr class="np-scope-row" data-scope="<?= html_escape($scopeRow['scope_type']) ?>" data-class-id="<?= (int) $scopeRow['class_id'] ?>">
											<td>
												<strong><?= html_escape($scopeRow['class_name']) ?></strong>
												<?php if (!empty($scopeRow['is_class_teacher'])): ?><span class="label label-info">Class teacher</span><?php endif; ?>
											</td>
											<td>
												<?= html_escape($scopeRow['subject_name']) ?>
												<small class="np-scope-kind"><?= $scopeRow['scope_type'] === 'class_teacher' ? 'Class subject' : 'My allocated subject' ?></small>
											</td>
											<td><?= !empty($scopeRow['subject_teacher_names']) ? html_escape(implode(', ', $scopeRow['subject_teacher_names'])) : '<span class="text-muted">Not assigned</span>' ?></td>
											<td class="text-center"><?= (int) $scopeRow['total_students'] ?></td>
											<td class="text-center">
												<?php if ($scopeRow['non_performers'] > 0): ?>
												<a href="<?= html_escape($detail_url) ?>" class="btn btn-danger btn-xs np-count-button"><?= (int) $scopeRow['non_performers'] ?> <span>View students</span></a>
												<?php else: ?>
												<span class="text-success"><i class="fas fa-check"></i> 0</span>
												<?php endif; ?>
											</td>
										</tr>
										<?php endforeach; ?>
										<tr id="np_scope_no_results" style="display:none;">
											<td colspan="5" class="text-center text-muted">No subjects match this view. Try another class or search.</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="text-muted mt-sm" id="np_scope_match_count"></div>
							<?php endif; ?>
							<?php else: ?>
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-hover mb-none" id="np_report_table">
									<thead>
										<tr>
											<th class="text-left">Class</th>
											<th class="text-center">Total Student</th>
											<?php foreach ($subjects as $s): ?>
											<th class="text-center"><?= html_escape($s['name']) ?></th>
											<?php endforeach; ?>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($rows)): ?>
										<tr>
											<td colspan="<?= (int) $colspan ?>" class="text-center text-muted"><?= translate('no_information_available'); ?></td>
										</tr>
										<?php else: ?>
										<?php foreach ($rows as $r): ?>
										<tr>
											<td class="text-left"><strong><?= html_escape($r['class_name']) ?></strong></td>
											<td class="text-center"><?= (int) $r['total_students'] ?></td>
											<?php foreach ($subjects as $s):
												$sid = (int) $s['id'];
												$val = isset($r['subjects'][$sid]) ? (int) $r['subjects'][$sid] : 0;
												$detail_q = array(
													'branch_id' => $branch_id,
													'from' => $from_date,
													'to' => $to_date,
													'class_id' => (int) $r['class_id'],
													'subject_id' => $sid,
												);
												$detail_url = base_url('digital_diary/non_performer_report_detail') . '?' . http_build_query($detail_q);
											?>
											<td class="text-center <?= $val > 0 ? 'danger' : 'active' ?>">
												<?php if ($val > 0): ?>
												<a href="<?= html_escape($detail_url) ?>" class="label label-danger"><?= $val ?></a>
												<?php else: ?>
												<span class="text-muted">0</span>
												<?php endif; ?>
											</td>
											<?php endforeach; ?>
										</tr>
										<?php endforeach; ?>
										<tr class="active">
											<th class="text-left">Total</th>
											<th class="text-center"><?= (int) $total_students_all ?></th>
											<?php foreach ($subjects as $s):
												$sid = (int) $s['id'];
												$v = isset($totals[$sid]) ? (int) $totals[$sid] : 0;
											?>
											<th class="text-center">
												<?php if ($v > 0): ?>
												<span class="label label-danger"><?= $v ?></span>
												<?php else: ?>
												0
												<?php endif; ?>
											</th>
											<?php endforeach; ?>
										</tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
							<?php endif; ?>
						</div>
					</section>

					<?php if (!is_teacher_loggedin()): ?>
					<section class="panel panel-featured-left panel-featured-warning mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-user-graduate"></i> Non Performer Students <span class="label label-warning"><?= (int) $total_non_performers ?></span></h4>
						</header>
						<div class="panel-body">
							<div class="row mb-md no-print">
								<div class="col-md-4 col-sm-6">
									<label class="control-label" for="np_class_filter">Show students from class</label>
									<select class="form-control" id="np_class_filter">
										<option value=""><?= is_teacher_loggedin() ? 'All assigned classes' : 'All classes' ?></option>
										<?php foreach ($studentClassOptions as $classId => $className): ?>
										<option value="<?= (int) $classId ?>"><?= html_escape($className) ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-8 col-sm-6 text-muted" id="np_student_match_count" style="padding-top:28px;">
									Showing <?= count($student_list) ?> student-subject records
								</div>
							</div>
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-hover mb-none" id="np_student_table">
									<thead>
										<tr>
											<th class="text-left">Class / Section</th>
											<th class="text-center" width="50">#</th>
											<th class="text-left">Subject</th>
											<th class="text-center">Roll No.</th>
											<th>Adm number</th>
											<th class="text-left">Student's Name</th>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($student_list)): ?>
										<tr>
											<td colspan="6" class="text-center text-muted"><?= translate('no_information_available'); ?></td>
										</tr>
										<?php else: ?>
										<?php
										$sn = 1;
										foreach ($student_list as $st):
											$rollDisp = isset($st['roll']) && $st['roll'] !== '' && $st['roll'] !== null
												? str_pad((string) (int) $st['roll'], 2, '0', STR_PAD_LEFT)
												: '—';
											$admNo = !empty($st['register_no']) ? $st['register_no'] : '—';
											$name = !empty($st['fullname']) ? $st['fullname'] : '—';
											$classId = isset($st['class_id']) ? (int) $st['class_id'] : 0;
											$sectionName = isset($st['section_name']) ? $st['section_name'] : '';
										?>
										<tr class="np-student-row" data-class-id="<?= $classId ?>">
											<td class="text-left" style="vertical-align:middle"><strong><?= html_escape($st['class_name']) ?></strong> / <?= html_escape($sectionName) ?></td>
											<td class="text-center" style="vertical-align:middle"><?= (int) $sn ?></td>
											<td class="text-left" style="vertical-align:middle"><?= html_escape($st['subject_name']) ?></td>
											<td class="text-center" style="vertical-align:middle"><?= html_escape($rollDisp) ?></td>
											<td style="vertical-align:middle"><?= html_escape($admNo) ?></td>
											<td class="text-left" style="vertical-align:middle"><strong><?= html_escape($name) ?></strong></td>
										</tr>
										<?php $sn++; endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					</section>
					<?php endif; ?>
				</div>

				<div class="well well-sm text-center no-print mb-none">
					<strong>Export</strong>
					<button type="button" class="btn btn-success" onclick="npExportExcel();"><i class="fas fa-file-excel"></i> Excel</button>
				</div>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
(function ($) {
	var $scopeRows = $('#np_teacher_report_table .np-scope-row');
	var activeScope = <?= json_encode($initialTeacherScope) ?>;
	var $scopeClass = $('#np_scope_class');
	var $scopeSearch = $('#np_scope_search');
	var updateOverview = function () {
		var isClassTeacher = activeScope === 'class_teacher';
		var scopeName = isClassTeacher ? 'Class teacher' : 'Subject teacher';
		$('#np_overview_scope_name, #np_report_scope_name').text(scopeName);
		$('#np_overview_description').text(
			isClassTeacher
				? 'Students and subjects across your class-teacher class'
				: 'Students and subjects across your allocated subjects'
		);
		$('#np_overview_students, #np_overview_non_performers, #np_overview_classes, #np_overview_subjects').each(function () {
			$(this).text($(this).attr(isClassTeacher ? 'data-class-teacher' : 'data-assigned'));
		});
	};
	var applyScopeFilters = function () {
		var selectedClass = String($scopeClass.val() || '');
		var search = String($scopeSearch.val() || '').toLowerCase().trim();
		var visibleCount = 0;
		$scopeRows.each(function () {
			var $row = $(this);
			var matches = String($row.attr('data-scope')) === activeScope;
			matches = matches && (!selectedClass || String($row.attr('data-class-id')) === selectedClass);
			matches = matches && (!search || $row.text().toLowerCase().indexOf(search) !== -1);
			$row.toggle(matches);
			if (matches) {
				visibleCount++;
			}
		});
		$('#np_scope_no_results').toggle(visibleCount === 0);
		$('#np_scope_match_count').text('Showing ' + visibleCount + ' subject(s)');
	};
	$('.np-scope-tab').on('click', function () {
		if ($(this).prop('disabled')) {
			return;
		}
		activeScope = String($(this).attr('data-scope'));
		$('.np-scope-tab').removeClass('btn-primary active').addClass('btn-default');
		$(this).removeClass('btn-default').addClass('btn-primary active');
		applyScopeFilters();
		updateOverview();
	});
	$scopeClass.add($scopeSearch).on('input change', applyScopeFilters);
	applyScopeFilters();
	updateOverview();

	var $studentRows = $('#np_student_table .np-student-row');
	if ($studentRows.length) {
		$('#np_class_filter').on('change', function () {
			var classID = String($(this).val() || '');
			var visibleCount = 0;
			$studentRows.each(function () {
				var matches = !classID || String($(this).attr('data-class-id')) === classID;
				$(this).toggle(matches);
				if (matches) {
					visibleCount++;
				}
			});
			$('#np_student_match_count').text('Showing ' + visibleCount + ' of ' + $studentRows.length + ' student-subject records');
		});
	}
})(jQuery);

function npExportExcel() {
	var t1 = document.getElementById('np_report_table');
	var t2 = document.getElementById('np_student_table');
	if (!t1 && !t2) return;
	var body = '';
	if (t1) body += '<h3>Class × Subject</h3>' + t1.outerHTML;
	if (t2) body += '<h3>Student Names</h3>' + t2.outerHTML;
	var h = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>NonPerformer</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>' + body + '</body></html>';
	var a = document.createElement('a');
	a.href = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(h);
	a.download = 'non_performer_report.xls';
	a.click();
}
</script>
