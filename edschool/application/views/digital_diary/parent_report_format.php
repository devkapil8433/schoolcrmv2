<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$school_name = isset($school_name) && $school_name !== '' ? $school_name : (isset($branch_id) && $branch_id !== '' ? get_type_name_by_id('branch', $branch_id) : '');
$today_date = isset($today_date) && !empty($today_date) ? $today_date : date('Y-m-d');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$filter_student_id = isset($filter_student_id) ? (int) $filter_student_id : 0;
$class_options = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$student_options = isset($student_options) ? $student_options : array('' => translate('select'));
$report_rows = isset($report_rows) ? $report_rows : array();
$student_block = isset($student_block) ? $student_block : null;
$attendance_summary = isset($attendance_summary) ? $attendance_summary : '—';
$discipline_dress = isset($discipline_dress) ? $discipline_dress : '—';
$discipline_conduct = isset($discipline_conduct) ? $discipline_conduct : '—';
$work_summary_subjects = isset($work_summary_subjects) ? $work_summary_subjects : array();
$grade_display = isset($grade_display) ? $grade_display : '';

$table_rows = $report_rows;
$minBodyRows = 5;
while (count($table_rows) < $minBodyRows) {
	$table_rows[] = array(
		'subject_name' => '.........',
		'total_lesson' => '',
		'now_going_on' => '',
		'class_work' => '',
		'home_work' => '',
		'syllabus_percentage' => '',
	);
}
?>

<div class="row">
	<div class="col-md-12">
		<section class="panel panel-parent-report-wrap">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-file-alt"></i> Parent Report Format</h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/parent_report_format'), array('method' => 'get', 'class' => 'mb-md no-print')); ?>
				<div class="row mb-sm">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-2">
						<label class="control-label"><?= translate('branch') ?></label>
						<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown(
							'branch_id',
							$arrayBranch,
							$branch_id,
							"class='form-control' id='branch_id' onchange='getClassByBranch(this.value)' data-plugin-selectTwo data-width=\"100%\""
						);
						?>
					</div>
					<?php endif; ?>
					<div class="col-md-2">
						<label class="control-label"><?= translate('date') ?></label>
						<input type="text" class="form-control" name="date" id="prf_date" value="<?= html_escape($today_date) ?>" data-plugin-datepicker data-plugin-options='{"format": "yyyy-mm-dd"}'>
					</div>
					<div class="col-md-2">
						<label class="control-label"><?= translate('class') ?></label>
						<?= form_dropdown('class_id', $class_options, $filter_class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="col-md-2">
						<label class="control-label"><?= translate('section') ?></label>
						<?= form_dropdown('section_id', $section_options, $filter_section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="col-md-2">
						<label class="control-label"><?= translate('student') ?></label>
						<?= form_dropdown('student_id', $student_options, $filter_student_id, "class='form-control' id='student_id' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="col-md-2 text-right" style="padding-top:24px;">
						<button type="submit" class="btn btn-primary" title="<?= translate('search') ?>" aria-label="<?= translate('search') ?>">
							<i class="fas fa-search"></i>
						</button>
						<button type="button" class="btn btn-default" title="Print" aria-label="Print" onclick="window.print();">
							<i class="fas fa-print"></i>
						</button>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div class="parent-report-sheet">
					<div class="pr-side-label">Report</div>
					<div class="pr-inner">
						<h1 class="pr-school-name"><?= html_escape($school_name !== '' ? $school_name : 'School Name') ?></h1>

						<div class="pr-student-grid">
							<div class="pr-col">
								<div class="pr-line"><span class="pr-label">Admission no.</span> <span class="pr-val"><?= $student_block ? html_escape($student_block['admission_no']) : '—' ?></span></div>
								<div class="pr-line"><span class="pr-label">Class</span> <span class="pr-val"><?= $student_block ? html_escape($student_block['class_name']) : '—' ?></span></div>
								<div class="pr-line"><span class="pr-label">Roll. No.</span> <span class="pr-val"><?= $student_block ? html_escape($student_block['roll']) : '—' ?></span></div>
							</div>
							<div class="pr-col">
								<div class="pr-line"><span class="pr-label">Student&rsquo;s name</span> <span class="pr-val"><?= $student_block ? html_escape($student_block['student_name']) : '—' ?></span></div>
								<div class="pr-line"><span class="pr-label">Father&rsquo;s name</span> <span class="pr-val"><?= $student_block ? html_escape($student_block['father_name']) : '—' ?></span></div>
								<div class="pr-line"><span class="pr-label">Address</span> <span class="pr-val"><?= $student_block ? html_escape($student_block['address']) : '—' ?></span></div>
							</div>
							<div class="pr-col">
								<div class="pr-line"><span class="pr-label">Mother&rsquo;s name</span> <span class="pr-val"><?= $student_block ? html_escape($student_block['mother_name']) : '—' ?></span></div>
								<div class="pr-line"><span class="pr-label">D.O.B.</span> <span class="pr-val"><?= $student_block ? html_escape($student_block['dob']) : '—' ?></span></div>
							</div>
						</div>

						<h2 class="pr-report-title">Home work and Syllabus Report</h2>

						<div class="table-responsive pr-table-wrap">
							<table class="table pr-table table-bordered">
								<thead>
									<tr>
										<th>Subject</th>
										<th>Total Lesson</th>
										<th>Now Going On</th>
										<th>Class Work</th>
										<th>Home Work</th>
										<th>Syllabus Percentage</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($table_rows as $row): ?>
									<tr>
										<td><?= !empty($row['subject_name']) ? html_escape($row['subject_name']) : '—' ?></td>
										<td class="pr-num"><?= isset($row['total_lesson']) && $row['total_lesson'] !== '' ? (int) $row['total_lesson'] : '' ?></td>
										<td><?= isset($row['now_going_on']) && $row['now_going_on'] !== '' ? html_escape($row['now_going_on']) : '' ?></td>
										<td><?= isset($row['class_work']) && $row['class_work'] !== '' ? html_escape($row['class_work']) : '' ?></td>
										<td><?= isset($row['home_work']) && $row['home_work'] !== '' ? html_escape($row['home_work']) : '' ?></td>
										<td><?= isset($row['syllabus_percentage']) && $row['syllabus_percentage'] !== '' ? html_escape($row['syllabus_percentage']) : '' ?></td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<div class="pr-report-block">
							<p class="pr-report-heading">Report:-</p>
							<ol class="pr-report-list">
								<li>
									<strong>Attendance Summary:</strong>
									<span class="pr-linkline"><?= html_escape($attendance_summary) ?></span>
								</li>
								<li>
									<strong>Discipline Summary:</strong>
									<span class="pr-sub">Dress:</span> <span class="pr-linkline"><?= html_escape($discipline_dress) ?></span>
									&nbsp;&nbsp;
									<span class="pr-sub">Conduct:</span> <span class="pr-linkline"><?= html_escape($discipline_conduct) ?></span>
								</li>
								<li class="pr-work-li">
									<strong>Work Summary:</strong>
									<div class="pr-work-grade-row">
										<div class="pr-work-boxes">
											<?php foreach ($work_summary_subjects as $ws): ?>
												<?php
												$tier = isset($ws['tier']) ? $ws['tier'] : 'empty';
												if (!in_array($tier, array('green', 'yellow', 'orange', 'red', 'empty'), true)) {
													$tier = 'empty';
												}
												$boxClass = 'pr-wbox pr-wbox--' . $tier;
												$txt = isset($ws['text']) ? $ws['text'] : '';
												$sn = isset($ws['subject_name']) ? $ws['subject_name'] : '';
												?>
											<div class="<?= html_escape($boxClass) ?>">
												<div class="pr-wbox-subj"><?= html_escape($sn) ?></div>
												<div class="pr-wbox-status"><?= html_escape($txt) ?></div>
											</div>
											<?php endforeach; ?>
											<?php if (empty($work_summary_subjects)): ?>
											<div class="pr-wbox pr-wbox--empty"><div class="pr-wbox-subj">—</div></div>
											<?php endif; ?>
										</div>
										<div class="pr-grade-box">
											<div class="pr-grade-label">Grade</div>
											<div class="pr-grade-cell"><?= $grade_display !== '' ? html_escape($grade_display) : '&nbsp;' ?></div>
										</div>
									</div>
								</li>
							</ol>
						</div>

						<div class="pr-footer-row">
							<div class="pr-legend">
								<div class="pr-leg-line"><span class="pr-wbox pr-wbox--green pr-leg-swatch"></span> Green box: Student performance is good.</div>
								<div class="pr-leg-line"><span class="pr-wbox pr-wbox--yellow pr-leg-swatch"></span> Yellow box: Student performance is low.</div>
								<div class="pr-leg-line"><span class="pr-wbox pr-wbox--orange pr-leg-swatch"></span> Orange box: Student performance poor, needs attention.</div>
								<div class="pr-leg-line"><span class="pr-wbox pr-wbox--red pr-leg-swatch"></span> Red box: Student performance is critical, meet principal immediately.</div>
							</div>
							<div class="pr-actions no-print">
								<a href="#" class="btn pr-act-btn" onclick="return false;">Whatsapp</a>
								<a href="#" class="btn pr-act-btn" onclick="return false;">Massage</a>
								<a href="#" class="btn pr-act-btn" onclick="return false;">Email</a>
							</div>
						</div>
					</div>
				</div>

				<?php if (empty($filter_class_id) || empty($filter_section_id)): ?>
				<p class="text-muted no-print mt-md"><?= translate('select') ?> <?= translate('class') ?> &amp; <?= translate('section') ?> <?= translate('to') ?> <?= translate('continue') ?>.</p>
				<?php elseif (!$student_block): ?>
				<p class="text-muted no-print mt-md"><?= translate('select') ?> <?= translate('student') ?> <?= translate('to') ?> load attendance, discipline, work summary, and grade.</p>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<style type="text/css">
.panel-parent-report-wrap .parent-report-sheet {
	position: relative;
	background: #fff;
	border: 1px solid #ddd;
	padding: 12px 12px 12px 28px;
	font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	color: #111;
}
.pr-side-label {
	position: absolute;
	left: 0;
	top: 50%;
	transform: translateY(-50%) rotate(-90deg);
	transform-origin: center center;
	font-size: 18px;
	font-weight: 700;
	color: #c00;
	letter-spacing: 2px;
	width: 24px;
	text-align: center;
}
.pr-inner { max-width: 100%; }
.pr-school-name {
	text-align: center;
	font-size: 1.65rem;
	font-weight: 700;
	color: #c00;
	margin: 8px 0 20px;
}
.pr-student-grid {
	display: flex;
	flex-wrap: wrap;
	justify-content: space-between;
	gap: 12px 24px;
	margin-bottom: 16px;
	font-size: 13px;
}
.pr-col { flex: 1 1 200px; min-width: 180px; }
.pr-line { margin-bottom: 6px; }
.pr-label { font-weight: 600; }
.pr-label:after { content: ' -'; }
.pr-val { font-weight: 400; }
.pr-report-title {
	text-align: center;
	font-size: 1.45rem;
	font-weight: 700;
	color: #111;
	margin: 18px 0 14px;
}
.pr-table { margin-bottom: 18px; font-size: 13px; }
.pr-table thead th {
	background: #f8f8f8;
	font-weight: 700;
	text-align: center;
	vertical-align: middle;
}
.pr-table td { vertical-align: top; }
.pr-table .pr-num { font-size: 18px; font-weight: 600; text-align: center; }
.pr-report-block { font-size: 13px; margin-top: 8px; }
.pr-report-heading { font-weight: 700; margin-bottom: 8px; }
.pr-report-list { padding-left: 18px; margin: 0; }
.pr-report-list li { margin-bottom: 10px; }
.pr-linkline {
	color: #039be5;
	text-decoration: underline;
	font-weight: 600;
	margin-left: 4px;
}
.pr-sub { font-weight: 600; }
.pr-work-li { list-style-position: outside; }
.pr-work-grade-row {
	display: flex;
	flex-wrap: wrap;
	align-items: flex-start;
	gap: 16px;
	margin-top: 10px;
}
.pr-work-boxes {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	flex: 1 1 280px;
}
.pr-wbox {
	min-width: 100px;
	min-height: 56px;
	border-radius: 8px;
	padding: 6px 8px;
	text-align: center;
	border: 1px solid #333;
	display: flex;
	flex-direction: column;
	justify-content: center;
}
.pr-wbox-subj { font-weight: 700; font-size: 12px; margin-bottom: 4px; }
.pr-wbox-status { font-size: 11px; font-weight: 600; line-height: 1.2; }
.pr-wbox--green { background: #2e7d32; border-color: #1b5e20; color: #fff; }
.pr-wbox--yellow { background: #ffeb3b; border-color: #f9a825; color: #c62828; }
.pr-wbox--orange { background: #ff9800; border-color: #e65100; color: #fff; }
.pr-wbox--red { background: #c62828; border-color: #b71c1c; color: #fff; }
.pr-wbox--empty { background: #fff; color: #111; }
.pr-grade-box { text-align: center; min-width: 100px; }
.pr-grade-label { font-weight: 700; margin-bottom: 6px; }
.pr-grade-cell {
	min-height: 72px;
	min-width: 88px;
	border: 2px solid #333;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28px;
	font-weight: 700;
}
.pr-footer-row {
	display: flex;
	flex-wrap: wrap;
	justify-content: space-between;
	align-items: flex-end;
	gap: 16px;
	margin-top: 20px;
	padding-top: 12px;
	border-top: 1px solid #eee;
}
.pr-legend { font-size: 12px; max-width: 520px; line-height: 1.5; }
.pr-leg-line { margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
.pr-leg-swatch { min-width: 22px; min-height: 16px; padding: 0; display: inline-block; }
.pr-actions { display: flex; flex-wrap: wrap; gap: 8px; }
.pr-act-btn {
	background: #b3e5fc !important;
	border: 1px solid #0288d1 !important;
	color: #111 !important;
	min-width: 88px;
	border-radius: 6px;
}
@media print {
	.no-print { display: none !important; }
	.panel-parent-report-wrap { border: none; box-shadow: none; }
	.panel-parent-report-wrap .panel-body { padding: 0; }
	.parent-report-sheet { border: none; }
}
</style>

<script type="text/javascript">
function prfReinitSelect2(sel) {
	try {
		if ($(sel).data('select2')) {
			$(sel).select2('destroy');
		}
	} catch (e) {}
	$(sel).removeData('__select2');
	$(sel).themePluginSelect2({});
}
function prfStudentPlaceholder(mode) {
	if (mode === 'class') {
		return '<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	if (mode === 'section') {
		return '<option value=""><?= htmlspecialchars(translate('select') . ' ' . translate('section'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	return '<option value=""><?= htmlspecialchars(translate('select'), ENT_QUOTES, 'UTF-8') ?></option>';
}
function prfLoadStudents() {
	var cid = $('#class_id').val();
	var sid = $('#section_id').val();
	if (!cid || !sid) {
		$('#student_id').html(prfStudentPlaceholder(!cid ? 'class' : 'section'));
		prfReinitSelect2('#student_id');
		return;
	}
	$.ajax({
		url: base_url + 'student/get_students_by_class_section',
		type: 'POST',
		data: { class_id: cid, section_id: sid },
		success: function (html) {
			$('#student_id').html(html);
			prfReinitSelect2('#student_id');
		}
	});
}
$(document).ready(function () {
	$('#class_id').on('change', function () {
		var v = $(this).val();
		if (!v) {
			$('#section_id').html('<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>');
			prfReinitSelect2('#section_id');
			$('#student_id').html(prfStudentPlaceholder('class'));
			prfReinitSelect2('#student_id');
			return;
		}
		$('#student_id').html(prfStudentPlaceholder('section'));
		prfReinitSelect2('#student_id');
	});
	$('#section_id').on('change', function () {
		prfLoadStudents();
	});
});
$(document).ajaxComplete(function (event, xhr, settings) {
	if (!settings.url) {
		return;
	}
	if (settings.url.indexOf('ajax/getSectionByClass') === -1) {
		return;
	}
	prfReinitSelect2('#section_id');
	$('#student_id').html(prfStudentPlaceholder('section'));
	prfReinitSelect2('#student_id');
});
</script>
