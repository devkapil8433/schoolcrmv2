<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$school_name = isset($school_name) && $school_name !== '' ? $school_name : (isset($branch_id) && $branch_id !== '' ? get_type_name_by_id('branch', $branch_id) : '');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$filter_student_id = isset($filter_student_id) ? (int) $filter_student_id : 0;
$class_options = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$student_options = isset($student_options) ? $student_options : array('' => translate('select'));
$student_block = isset($student_block) ? $student_block : null;

$month = isset($month) ? (int) $month : (int) date('n');
$year = isset($year) ? (int) $year : (int) date('Y');
if ($month < 1 || $month > 12) {
	$month = (int) date('n');
}
if ($year < 2000 || $year > 2100) {
	$year = (int) date('Y');
}
$monthLabel = isset($month_label) ? $month_label : date('F Y', strtotime($year . '-' . $month . '-01'));
$table_subjects = isset($table_subjects) && is_array($table_subjects) ? $table_subjects : array(
	array('subject_id' => 0, 'subject_name' => 'Hindi'),
	array('subject_id' => 0, 'subject_name' => 'English'),
);
$summary = isset($summary) && is_array($summary) ? $summary : array(
	'school_open_days' => 0,
	'attend_days' => 0,
	'absent_days' => 0,
	'without_dress' => 0,
	'bad_conduct' => 0,
	'subject_scores' => array(),
	'grade' => '—',
	'attendance_status' => '—',
	'discipline_status' => '—',
	'work_status' => '—',
	'parent_note' => '',
	'is_low' => false,
);
$report_rows = isset($report_rows) && is_array($report_rows) ? $report_rows : array();
$student_valid = !empty($student_valid);

if (!function_exists('ptm_table_cell_class')) {
	function ptm_table_cell_class($type, $val, $attendance = '')
	{
		$v = trim((string) $val);
		$att = trim((string) $attendance);
		if ($att === 'Holiday' || strcasecmp($v, 'Holiday') === 0) {
			return 'ptm-val-holiday';
		}
		$present = ($att !== '' && $att !== 'A');
		if ($type === 'attendance') {
			if ($v === 'P') {
				return 'ptm-val-ok';
			}
			if ($v === 'A') {
				return 'ptm-val-bad';
			}
			if ($v === 'L' || $v === 'HD') {
				return 'ptm-val-warn';
			}
			return '';
		}
		if ($type === 'mark') {
			if ($v === 'Y' || $v === '✓') {
				return 'ptm-val-ok';
			}
			if ($v === 'A' || $att === 'A') {
				return 'ptm-val-bad';
			}
			if ($present) {
				return 'ptm-val-miss';
			}
		}
		if ($type === 'discipline') {
			if ($v === 'Y' || $v === '✓') {
				return 'ptm-val-ok';
			}
			if ($v === 'A' || $att === 'A') {
				return 'ptm-val-bad';
			}
			if ($present) {
				return 'ptm-val-warn';
			}
		}
		return '';
	}
}

// Keep a minimum body height like the sample sheet.
$minRows = 18;
while (count($report_rows) < $minRows) {
	$report_rows[] = array(
		'sr' => '',
		'date_disp' => '',
		't_days' => '',
		'attendance' => '',
		'dress' => '',
		'conduct' => '',
		'subjects' => array(),
		'is_holiday' => false,
	);
}
?>

<div class="row">
	<div class="col-md-12">
		<section class="panel panel-ptm-report-wrap">
			<header class="panel-heading no-print">
				<h4 class="panel-title"><i class="fas fa-handshake"></i> PTM Report Format (Monthly)</h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/ptm_report_format'), array('method' => 'get', 'class' => 'mb-md no-print ptm-filter-form')); ?>
				<div class="ptm-filters">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="ptm-filter-item ptm-filter-branch">
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
					<div class="ptm-filter-item ptm-filter-month">
						<label class="control-label">Month</label>
						<select class="form-control" name="month" data-plugin-selectTwo data-width="100%">
							<?php for ($m = 1; $m <= 12; $m++): ?>
								<option value="<?= (int) $m ?>" <?= $m === $month ? 'selected' : '' ?>>
									<?= html_escape(date('F', mktime(0, 0, 0, $m, 1, 2000))) ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="ptm-filter-item ptm-filter-year">
						<label class="control-label">Year</label>
						<input type="number" class="form-control" name="year" value="<?= (int) $year ?>" min="2000" max="2100">
					</div>
					<div class="ptm-filter-item">
						<label class="control-label"><?= translate('class') ?></label>
						<?= form_dropdown('class_id', $class_options, $filter_class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="ptm-filter-item">
						<label class="control-label"><?= translate('section') ?></label>
						<?= form_dropdown('section_id', $section_options, $filter_section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="ptm-filter-item ptm-filter-student">
						<label class="control-label"><?= translate('student') ?></label>
						<?= form_dropdown('student_id', $student_options, $filter_student_id, "class='form-control' id='student_id' data-plugin-selectTwo data-width='100%'") ?>
					</div>
					<div class="ptm-filter-actions">
						<button type="submit" class="btn btn-primary" title="<?= translate('search') ?>" aria-label="<?= translate('search') ?>">
							<i class="fas fa-search"></i>
						</button>
						<button type="button" class="btn btn-default" title="Print / PDF" aria-label="Print" onclick="ptmOpenPdf();">
							<i class="fas fa-print"></i>
						</button>
					</div>
				</div>
				<?php echo form_close(); ?>

				<div class="ptm-report-sheet" id="ptm_print_area">
					<div class="ptm-side-label">Report</div>
					<div class="ptm-inner">
						<?php
						$ptmLogoUrl = '';
						if (!empty($branch_id)) {
							$CI =& get_instance();
							$ptmLogoUrl = $CI->application_model->getBranchImage($branch_id, 'printing-logo');
						}
						?>
						<div class="ptm-print-top">
							<div class="ptm-header-row">
								<div class="ptm-header-side ptm-header-logo">
									<?php if ($ptmLogoUrl !== ''): ?>
									<img src="<?= html_escape($ptmLogoUrl) ?>" alt="School Logo" class="ptm-logo-img">
									<?php else: ?>
									<div class="ptm-logo-placeholder"></div>
									<?php endif; ?>
								</div>
								<div class="ptm-header-center">
									<h1 class="ptm-school-name"><?= html_escape($school_name !== '' ? $school_name : 'School Name') ?></h1>
									<div class="ptm-month-label"><?= html_escape($monthLabel) ?></div>
									<h2 class="ptm-report-title">Student Work Evaluation Report</h2>
								</div>
								<div class="ptm-header-side ptm-header-side-spacer" aria-hidden="true"></div>
							</div>

							<?php if ($student_block): ?>
							<?php $pendingFee = isset($student_block['pending_fee']) ? (float) $student_block['pending_fee'] : 0; ?>
							<div class="ptm-student-card ptm-student-inline">
								<div class="ptm-student-left">
									<span class="ptm-student-name"><?= html_escape($student_block['student_name']) ?></span>
									<span><strong>Class:</strong> <?= html_escape($student_block['class_name']) ?></span>
									<span><strong>Roll No:</strong> <?= html_escape(!empty($student_block['roll']) ? $student_block['roll'] : '—') ?></span>
								</div>
								<?php if ($pendingFee > 0): ?>
								<div class="ptm-pending-fee">
									<strong>Pending Fee:</strong>
									<?= function_exists('currencyFormat') ? currencyFormat($pendingFee) : html_escape(number_format($pendingFee, 2)) ?>
								</div>
								<?php endif; ?>
							</div>
							<?php endif; ?>
						</div>

						<div class="table-responsive ptm-table-wrap">
							<table class="table ptm-table table-bordered">
								<thead>
									<tr>
										<th rowspan="2" class="ptm-th-sr">Sr.</th>
										<th rowspan="2" class="ptm-th-date">Date</th>
								<th colspan="2">Attendance</th>
								<th colspan="2">Discipline</th>
								<?php foreach ($table_subjects as $ts): ?>
								<th colspan="3"><?= html_escape($ts['subject_name']) ?></th>
								<?php endforeach; ?>
							</tr>
							<tr>
								<th class="ptm-th-small">T.Days</th>
								<th class="ptm-th-small">Attend</th>
								<th class="ptm-th-small ptm-rotate"><div>Dress</div></th>
								<th class="ptm-th-small ptm-rotate"><div>Conduct</div></th>
								<?php foreach ($table_subjects as $ts): ?>
								<th class="ptm-th-small">Fair</th>
								<th class="ptm-th-small">Writing</th>
								<th class="ptm-th-small">Learn</th>
								<?php endforeach; ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($report_rows as $r): ?>
									<?php
									$subjectsMap = isset($r['subjects']) && is_array($r['subjects']) ? $r['subjects'] : array();
									$attVal = !empty($r['attendance']) ? (string) $r['attendance'] : '';
									$dressVal = !empty($r['dress']) ? (string) $r['dress'] : '';
									$conductVal = !empty($r['conduct']) ? (string) $r['conduct'] : '';
									$hasDate = !empty($r['date_disp']);
									$isHoliday = !empty($r['is_holiday']) || strcasecmp($attVal, 'Holiday') === 0;
									$colspanSubjects = count($table_subjects) * 3;
									?>
									<tr class="<?= $isHoliday ? 'ptm-row-holiday' : '' ?>">
										<td class="ptm-td-sr"><?= $r['sr'] !== '' ? (int) $r['sr'] : '&nbsp;' ?></td>
										<td class="ptm-td-date"><?= $hasDate ? html_escape($r['date_disp']) : '&nbsp;' ?></td>
										<?php if ($isHoliday): ?>
										<td class="ptm-td-blank ptm-val-holiday" colspan="<?= 4 + $colspanSubjects ?>">Holiday</td>
										<?php else: ?>
										<td class="ptm-td-blank"><?= !empty($r['t_days']) ? html_escape($r['t_days']) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('attendance', $attVal)) : '' ?>"><?= $attVal !== '' ? html_escape($attVal) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('discipline', $dressVal, $attVal)) : '' ?>"><?= $dressVal !== '' ? html_escape($dressVal) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('discipline', $conductVal, $attVal)) : '' ?>"><?= $conductVal !== '' ? html_escape($conductVal) : '&nbsp;' ?></td>
										<?php foreach ($table_subjects as $ts):
											$tsid = (int) $ts['subject_id'];
											$sc = isset($subjectsMap[$tsid]) ? $subjectsMap[$tsid] : array('fair' => '', 'writing' => '', 'learn' => '');
											$fairVal = !empty($sc['fair']) ? (string) $sc['fair'] : '';
											$writingVal = !empty($sc['writing']) ? (string) $sc['writing'] : '';
											$learnVal = !empty($sc['learn']) ? (string) $sc['learn'] : '';
										?>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('mark', $fairVal, $attVal)) : '' ?>"><?= $fairVal !== '' ? html_escape($fairVal) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('mark', $writingVal, $attVal)) : '' ?>"><?= $writingVal !== '' ? html_escape($writingVal) : '&nbsp;' ?></td>
										<td class="ptm-td-blank <?= $hasDate ? html_escape(ptm_table_cell_class('mark', $learnVal, $attVal)) : '' ?>"><?= $learnVal !== '' ? html_escape($learnVal) : '&nbsp;' ?></td>
										<?php endforeach; ?>
										<?php endif; ?>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<?php
						$attendance_summary = isset($attendance_summary) ? $attendance_summary : '—';
						$discipline_dress = isset($discipline_dress) ? $discipline_dress : '—';
						$discipline_conduct = isset($discipline_conduct) ? $discipline_conduct : '—';
						$work_summary_subjects = isset($work_summary_subjects) && is_array($work_summary_subjects) ? $work_summary_subjects : array();
						$grade_display = isset($grade_display) && $grade_display !== '' ? $grade_display : (isset($summary['grade']) ? $summary['grade'] : '—');
						$parentSkillSummary = isset($parent_skill_summary) && is_array($parent_skill_summary)
							? $parent_skill_summary
							: array('date_from' => '', 'date_to' => '', 'window_days' => 15, 'skills' => array());
						$parentSkillRows = isset($parentSkillSummary['skills']) && is_array($parentSkillSummary['skills']) ? $parentSkillSummary['skills'] : array();
						$parentSkillWindow = isset($parentSkillSummary['window_days']) ? (int) $parentSkillSummary['window_days'] : 15;
						if ($parentSkillWindow < 1) {
							$parentSkillWindow = 15;
						}
						$parentSkillFrom = !empty($parentSkillSummary['date_from']) && strtotime($parentSkillSummary['date_from'])
							? date('d M Y', strtotime($parentSkillSummary['date_from']))
							: '';
						$parentSkillTo = !empty($parentSkillSummary['date_to']) && strtotime($parentSkillSummary['date_to'])
							? date('d M Y', strtotime($parentSkillSummary['date_to']))
							: '';
						$parentSkillTitle = 'Student Skill Summary — last ' . $parentSkillWindow . ' days';
						if ($parentSkillFrom !== '' && $parentSkillTo !== '') {
							$parentSkillTitle .= ' (' . $parentSkillFrom . ' – ' . $parentSkillTo . ')';
						}
						$ptmBoxClass = function ($tier) {
							if ($tier === 'green') {
								return 'bx hindi-green';
							}
							if ($tier === 'yellow') {
								return 'bx prf-bx-yellow';
							}
							if ($tier === 'orange') {
								return 'bx prf-bx-orange';
							}
							if ($tier === 'red') {
								return 'bx prf-bx-red';
							}
							return 'bx';
						};
						$attNorm = strtolower(trim((string) $attendance_summary));
						$attPctVal = (int) (isset($summary['attendance_pct']) ? $summary['attendance_pct'] : 0);
						$attBoxClass = 'swr-field-box prf-att-box';
						if ($attNorm === 'absent' || $attNorm === 'a' || $attPctVal <= 0) {
							$attBoxClass .= ' prf-field-red';
						} elseif ($attPctVal >= 75) {
							$attBoxClass .= ' prf-field-green';
						} elseif ($attNorm !== '' && $attNorm !== '—') {
							$attBoxClass .= ' prf-field-yellow';
						}
						$dressNorm = strtolower(trim((string) $discipline_dress));
						$dressBoxClass = 'swr-field-box';
						if ($dressNorm === 'completed') {
							$dressBoxClass .= ' prf-field-green';
						} elseif ($dressNorm === 'not completed') {
							$dressBoxClass .= ' prf-field-red';
						}
						$conductNorm = strtolower(trim((string) $discipline_conduct));
						$conductBoxClass = 'swr-field-box';
						if ($conductNorm === 'good') {
							$conductBoxClass .= ' prf-field-green';
						} elseif ($conductNorm === 'found guilty') {
							$conductBoxClass .= ' prf-field-red';
						}
						$gradeLetter = strtoupper(trim((string) $grade_display));
						$swr_grade_class = '';
						if ($gradeLetter === 'A') {
							$swr_grade_class = 'g-a';
						} elseif ($gradeLetter === 'B') {
							$swr_grade_class = 'g-b';
						} elseif ($gradeLetter === 'C') {
							$swr_grade_class = 'g-c';
						} elseif ($gradeLetter !== '' && $gradeLetter !== '—') {
							$swr_grade_class = 'g-d';
						}
						$parentNoteHtml = !empty($summary['parent_note'])
							? $summary['parent_note']
							: '';
						$noteIsLow = !empty($summary['is_low']);
						?>
						<div class="ptm-print-bottom">
							<div class="ptm-legend-row">
								<span>Green = good / completed</span>
								<span>Yellow = low</span>
								<span>Orange = needs attention</span>
								<span>Red = meet principal</span>
								<span>Blue = holiday / Sunday</span>
							</div>
							<div class="swr-report-block ptm-footer-report">
								<h3>Report:-</h3>
								<div class="prf-report-body">
									<div class="prf-report-left">
										<div class="prf-sum-row">
											<div class="prf-rlbl">1. Attendance Summary</div>
											<div class="prf-rval">
												<div class="swr-field-row">
													<label>Attendance</label>
													<span class="<?= html_escape($attBoxClass) ?>"><?= html_escape($attendance_summary) ?></span>
												</div>
											</div>
										</div>
										<div class="prf-sum-row">
											<div class="prf-rlbl">2. Discipline Summary</div>
											<div class="prf-rval">
												<div class="swr-field-row">
													<label>Dress</label>
													<span class="<?= html_escape($dressBoxClass) ?>"><?= html_escape($discipline_dress) ?></span>
													<label>Conduct</label>
													<span class="<?= html_escape($conductBoxClass) ?>"><?= html_escape($discipline_conduct) ?></span>
												</div>
											</div>
										</div>
										<div class="prf-sum-row">
											<div class="prf-rlbl">3. Work Summary</div>
											<div class="prf-rval">
												<div class="swr-subj-grid">
													<?php foreach ($work_summary_subjects as $ws):
														$tier = isset($ws['tier']) ? $ws['tier'] : 'empty';
														$txt = isset($ws['text']) ? $ws['text'] : '';
														$sn = isset($ws['subject_name']) ? $ws['subject_name'] : '';
													?>
													<div class="swr-subj-item">
														<div class="nm"><?= html_escape($sn) ?></div>
														<div class="<?= $ptmBoxClass($tier) ?>">
															<?= html_escape($txt !== '' ? $txt : '—') ?>
														</div>
													</div>
													<?php endforeach; ?>
													<?php if (empty($work_summary_subjects)): ?>
													<div class="swr-subj-item">
														<div class="nm">—</div>
														<div class="bx">—</div>
													</div>
													<?php endif; ?>
												</div>
											</div>
										</div>
									</div>
									<div class="prf-report-mid">
										<div class="prf-skill-box">
											<div class="prf-skill-title"><?= html_escape($parentSkillTitle) ?></div>
											<?php if (empty($parentSkillRows)): ?>
											<p class="text-muted" style="margin:0;font-size:12px;">No skill reviews recorded for this period.</p>
											<?php else: ?>
											<?php foreach ($parentSkillRows as $sk):
												$pending = !empty($sk['pending']) || (int) (isset($sk['score']) ? $sk['score'] : 0) < 1;
												$score = (int) (isset($sk['score']) ? $sk['score'] : 0);
												$comment = isset($sk['comment']) ? trim((string) $sk['comment']) : '';
												$name = isset($sk['activity_name']) ? $sk['activity_name'] : '';
												$pct = $pending ? 0 : max(0, min(100, $score * 10));
												if ($pending && $comment === '') {
													$comment = 'Not reviewed yet';
												}
												$fillClass = 'prf-skill-fill pending';
												if (!$pending) {
													if ($score >= 9) {
														$fillClass = 'prf-skill-fill ex';
													} elseif ($score >= 7) {
														$fillClass = 'prf-skill-fill good';
													} elseif ($score >= 5) {
														$fillClass = 'prf-skill-fill mid';
													} else {
														$fillClass = 'prf-skill-fill low';
													}
												}
											?>
											<div class="prf-skill-row">
												<div class="prf-skill-name"><?= html_escape($name) ?></div>
												<div class="prf-skill-track"><div class="<?= $fillClass ?>" style="width:<?= (int) $pct ?>%;"></div></div>
												<div class="prf-skill-score"><?= $pending ? '(—/10)' : '(' . (int) $score . '/10)' ?></div>
												<div class="prf-skill-comment"><?= $comment !== '' ? html_escape($comment) : '' ?></div>
											</div>
											<?php endforeach; ?>
											<?php endif; ?>
										</div>
									</div>
									<div class="prf-report-right">
										<div class="swr-grade-box">
											<div class="g-label">Grade</div>
											<div class="g-val <?= html_escape($swr_grade_class) ?>"><?= $grade_display !== '' ? html_escape($grade_display) : '—' ?></div>
										</div>
									</div>
								</div>
							</div>
							<?php if ($parentNoteHtml !== ''): ?>
							<div class="ptm-note<?= $noteIsLow ? ' ptm-note-low' : ' ptm-note-ok' ?>">
								<?= $parentNoteHtml ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php if ($filter_class_id && $filter_section_id && !$filter_student_id): ?>
				<p class="text-muted no-print mt-md"><?= translate('select') ?> <?= translate('student') ?> <?= translate('to') ?> load monthly report data.</p>
				<?php elseif ($filter_student_id && !$student_valid): ?>
				<p class="text-muted no-print mt-md">Selected student is not in this class/section.</p>
				<?php endif; ?>

			</div>
		</section>
	</div>
</div>

<style type="text/css" id="ptm-report-css">
.panel-ptm-report-wrap .panel-body { overflow-x: hidden; }
.ptm-filters {
	display: flex;
	flex-wrap: wrap;
	align-items: flex-end;
	gap: 8px 10px;
	margin-bottom: 14px;
}
.ptm-filter-item {
	flex: 0 1 auto;
	width: 150px;
}
.ptm-filter-branch { width: 190px; }
.ptm-filter-month { width: 120px; }
.ptm-filter-year { width: 90px; }
.ptm-filter-student { width: 180px; }
.ptm-filter-item label {
	display: block;
	font-weight: 600;
	margin-bottom: 3px;
	font-size: 12px;
}
.ptm-filter-item .form-control,
.ptm-filter-item .select2-container {
	width: 100% !important;
}
.ptm-filter-actions {
	display: flex;
	gap: 8px;
	align-items: center;
	padding-bottom: 1px;
	flex: 0 0 auto;
	margin-left: auto;
}
@media (min-width: 1200px) {
	.ptm-filters { flex-wrap: nowrap; }
	.ptm-filter-actions { margin-left: 0; }
}
.panel-ptm-report-wrap .ptm-report-sheet {
	position: relative;
	background: #fff;
	border: 1px solid #ddd;
	padding: 12px 14px 14px 34px;
	font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	color: #111;
}
.ptm-side-label {
	position: absolute;
	left: 2px;
	top: 45%;
	transform: translateY(-50%) rotate(-90deg);
	transform-origin: center center;
	font-size: 15px;
	font-weight: 700;
	color: #c00;
	letter-spacing: 2px;
	white-space: nowrap;
	pointer-events: none;
}
.ptm-print-top {
	border: 1px solid #222;
	margin: 0 0 10px;
	background: #fff;
	overflow: hidden;
	page-break-inside: avoid;
	break-inside: avoid;
}
.ptm-header-row {
	display: grid;
	grid-template-columns: 88px 1fr 88px;
	align-items: center;
	gap: 14px;
	padding: 12px 14px 10px;
	margin: 0;
	background: linear-gradient(180deg, #fff8f8 0%, #ffffff 100%);
	border-bottom: 2px solid #c00;
}
.ptm-header-side {
	display: flex;
	align-items: center;
	justify-content: center;
	min-height: 78px;
}
.ptm-header-center {
	text-align: center;
	min-width: 0;
}
.ptm-school-name {
	text-align: center;
	font-size: 30px;
	font-weight: 800;
	color: #b71c1c;
	margin: 0 0 6px;
	line-height: 1.2;
	letter-spacing: 0.3px;
	text-transform: none;
}
.ptm-month-label {
	display: inline-block;
	text-align: center;
	font-size: 13px;
	font-weight: 700;
	margin: 0 0 6px;
	color: #333;
	background: #fff;
	border: 1px solid #ccc;
	padding: 3px 14px;
	letter-spacing: 0.4px;
}
.ptm-header-center .ptm-report-title {
	font-size: 18px;
	font-weight: 800;
	margin: 0;
	color: #111;
	letter-spacing: 0.3px;
}
.ptm-logo-img {
	width: 76px;
	height: 76px;
	object-fit: contain;
	display: block;
	border: 1px solid #ddd;
	background: #fff;
	padding: 3px;
}
.ptm-logo-placeholder {
	width: 76px;
	height: 76px;
	border: 1px dashed #bbb;
	background: #fafafa;
}
.ptm-student-card {
	border: 0;
	border-bottom: 1px solid #d0d0d0;
	padding: 10px 14px;
	margin: 0;
	background: #f7f7f7;
}
.ptm-student-inline {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	justify-content: space-between;
	gap: 8px 16px;
	font-size: 14px;
	color: #222;
	line-height: 1.4;
}
.ptm-student-left {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 8px 0;
	min-width: 0;
}
.ptm-student-left > span {
	padding: 0 14px 0 0;
	margin-right: 14px;
	border-right: 1px solid #c8c8c8;
}
.ptm-student-left > span:last-child {
	border-right: 0;
	margin-right: 0;
	padding-right: 0;
}
.ptm-student-inline .ptm-student-name {
	font-size: 18px;
	font-weight: 800;
	color: #111;
	letter-spacing: 0.2px;
}
.ptm-student-inline span strong { font-weight: 700; color: #111; }
.ptm-pending-fee {
	margin-left: auto;
	font-size: 13px;
	font-weight: 700;
	color: #b71c1c;
	white-space: nowrap;
	background: #ffebee;
	border: 1px solid #ef9a9a;
	padding: 4px 10px;
}
.ptm-color-legend {
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
	gap: 8px 16px;
	font-size: 11px;
	color: #444;
	margin: 0;
	text-align: center;
}
.ptm-leg {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	font-weight: 600;
	white-space: nowrap;
}
.ptm-dot {
	display: inline-block;
	width: 11px;
	height: 11px;
	border: 1px solid rgba(0,0,0,.18);
	border-radius: 2px;
	flex: 0 0 auto;
}
.ptm-dot-green { background: #c8e6c9; }
.ptm-dot-red { background: #ffcdd2; }
.ptm-dot-orange { background: #ffe0b2; }
.ptm-dot-pink { background: #fce4ec; }
.ptm-dot-blue { background: #bbdefb; }
.ptm-table-wrap {
	max-height: min(58vh, 560px);
	overflow: auto;
	border: 1px solid #ccc;
	margin: 0 0 12px;
	-webkit-overflow-scrolling: touch;
	background: #fff;
}
.ptm-table { margin-bottom: 0; font-size: 11px; border-collapse: separate; border-spacing: 0; }
.ptm-table thead th {
	background: #fff;
	font-weight: 700;
	text-align: center;
	vertical-align: middle;
	padding: 5px 3px;
	position: sticky;
	z-index: 4;
	box-shadow: 0 1px 0 #999;
	line-height: 1.2;
	white-space: normal;
	word-break: break-word;
}
.ptm-table thead tr:first-child th {
	top: 0;
	background: #f7f7f7;
}
.ptm-table thead tr:nth-child(2) th {
	top: 34px;
	z-index: 3;
	background: #fafafa;
	font-size: 10px;
}
.ptm-th-sr,
.ptm-td-sr {
	width: 40px;
	min-width: 40px;
	left: 0;
	position: sticky;
	z-index: 2;
	background: #fff;
	box-shadow: 1px 0 0 #bbb;
}
.ptm-th-date,
.ptm-td-date {
	width: 96px;
	min-width: 96px;
	left: 40px;
	position: sticky;
	z-index: 2;
	background: #fff;
	box-shadow: 1px 0 0 #bbb;
}
.ptm-table thead .ptm-th-sr,
.ptm-table thead .ptm-th-date {
	z-index: 6;
	background: #f7f7f7;
}
.ptm-th-small { min-width: 54px; width: 54px; }
.ptm-td-sr { text-align: center; font-weight: 600; }
.ptm-td-date { font-weight: 600; white-space: nowrap; }
.ptm-td-blank { height: 24px; text-align: center; }
.ptm-table tbody tr.ptm-row-holiday .ptm-td-sr,
.ptm-table tbody tr.ptm-row-holiday .ptm-td-date {
	background: #e3f2fd;
}
.ptm-table td.ptm-val-ok { background: #c8e6c9 !important; color: #1b5e20; font-weight: 700; }
.ptm-table td.ptm-val-bad { background: #ffcdd2 !important; color: #b71c1c; font-weight: 700; }
.ptm-table td.ptm-val-warn { background: #ffe0b2 !important; color: #e65100; font-weight: 700; }
.ptm-table td.ptm-val-miss { background: #fce4ec !important; color: #880e4f; }
.ptm-table tr.ptm-row-holiday td { background: #e3f2fd !important; }
.ptm-table td.ptm-val-holiday {
	background: #bbdefb !important;
	color: #0d47a1;
	font-weight: 700;
	text-align: center;
}
.ptm-rotate { min-width: 48px; width: 48px; padding: 8px 2px; height: 48px; }
.ptm-rotate > div {
	transform: rotate(-30deg);
	white-space: nowrap;
	font-weight: 700;
	display: inline-block;
	font-size: 10px;
}
.ptm-print-bottom {
	margin: 0;
	background: #fff;
	page-break-inside: avoid;
	break-inside: avoid;
}
.ptm-legend-row {
	font-size: 11px;
	color: #555;
	margin: 0 0 8px;
	display: flex;
	flex-wrap: wrap;
	gap: 8px 16px;
}
.ptm-footer-report.swr-report-block {
	margin: 0;
	border: 1px solid #000;
	padding: 12px 14px;
	background: #fff;
}
.ptm-footer-report h3 {
	font-size: 15px;
	font-weight: 700;
	margin: 0 0 12px;
}
.ptm-print-bottom .prf-report-body {
	display: grid;
	grid-template-columns: minmax(280px, 1.55fr) minmax(180px, 0.85fr) 90px;
	gap: 12px 14px;
	align-items: stretch;
}
.ptm-print-bottom .prf-report-left {
	display: flex;
	flex-direction: column;
	justify-content: flex-start;
	gap: 12px;
	min-width: 0;
}
.ptm-print-bottom .prf-report-mid { min-width: 0; }
.ptm-print-bottom .prf-sum-row {
	display: grid;
	grid-template-columns: minmax(120px, 140px) minmax(0, 1fr);
	gap: 8px 10px;
	align-items: start;
}
.ptm-print-bottom .prf-rlbl { font-weight: 700; font-size: 13px; line-height: 1.3; }
.ptm-print-bottom .prf-rval { min-width: 0; }
.ptm-print-bottom .swr-field-row {
	display: flex;
	flex-wrap: nowrap;
	gap: 8px;
	margin: 0;
	align-items: center;
}
.ptm-print-bottom .swr-field-row label {
	display: inline-block;
	font-size: 12px;
	margin: 0;
	font-weight: normal;
	white-space: nowrap;
}
.ptm-print-bottom .swr-field-box {
	border: 1px solid #000;
	min-width: 36px;
	flex: 1 1 auto;
	height: 28px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 0 8px;
	font-weight: 700;
	background: #fff;
}
.ptm-print-bottom .swr-field-box.prf-att-box {
	flex: 0 0 auto;
	min-width: 0;
	width: auto;
	max-width: 140px;
	height: 24px;
	padding: 0 10px;
	font-size: 12px;
}
.ptm-print-bottom .swr-field-box.prf-field-green { background: #2e7d32; color: #fff; border-color: #1b5e20; }
.ptm-print-bottom .swr-field-box.prf-field-yellow { background: #ffeb3b; color: #000; border-color: #f9a825; }
.ptm-print-bottom .swr-field-box.prf-field-orange { background: #ff9800; color: #fff; border-color: #e65100; }
.ptm-print-bottom .swr-field-box.prf-field-red { background: #e53935; color: #fff; border-color: #c62828; }
.ptm-print-bottom .swr-subj-grid {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
	align-items: stretch;
	margin: 0;
}
.ptm-print-bottom .swr-subj-item {
	text-align: center;
	flex: 0 0 92px;
	width: 92px;
	max-width: 100%;
}
.ptm-print-bottom .swr-subj-item .nm {
	font-size: 11px;
	margin-bottom: 2px;
	min-height: 28px;
	line-height: 1.2;
	font-weight: 600;
	display: flex;
	align-items: flex-end;
	justify-content: center;
}
.ptm-print-bottom .swr-subj-item .bx {
	border: 1px solid #000;
	width: 100%;
	min-height: 40px;
	padding: 6px 4px;
	font-size: 10px;
	font-weight: 700;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	box-sizing: border-box;
	background: #fff;
}
.ptm-print-bottom .swr-subj-item .bx.hindi-green { background: #2e7d32; color: #fff; border-color: #1b5e20; }
.ptm-print-bottom .swr-subj-item .bx.prf-bx-yellow { background: #ffeb3b; color: #000; border-color: #f9a825; }
.ptm-print-bottom .swr-subj-item .bx.prf-bx-orange { background: #ff9800; color: #fff; border-color: #e65100; }
.ptm-print-bottom .swr-subj-item .bx.prf-bx-red { background: #e53935; color: #fff; border-color: #c62828; }
.ptm-print-bottom .prf-report-right {
	display: flex;
	align-items: center;
	justify-content: center;
	min-width: 0;
}
.ptm-print-bottom .swr-grade-box {
	flex: 0 0 88px;
	text-align: center;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	margin: 0;
}
.ptm-print-bottom .swr-grade-box .g-label { font-size: 14px; font-weight: 700; margin-bottom: 6px; }
.ptm-print-bottom .swr-grade-box .g-val {
	width: 78px;
	height: 78px;
	border: 1px solid #000;
	font-size: 40px;
	font-weight: 800;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto;
	background: #fff;
}
.ptm-print-bottom .swr-grade-box .g-val.g-a { background: #2e7d32; color: #fff; }
.ptm-print-bottom .swr-grade-box .g-val.g-b { background: #66bb6a; color: #000; }
.ptm-print-bottom .swr-grade-box .g-val.g-c { background: #fdd835; color: #000; }
.ptm-print-bottom .swr-grade-box .g-val.g-d { background: #e53935; color: #fff; }
.ptm-print-bottom .prf-skill-box {
	border: 1px solid #000;
	padding: 8px 10px;
	background: #fff;
	height: 100%;
	box-sizing: border-box;
}
.ptm-print-bottom .prf-skill-box .prf-skill-title { font-size: 12px; font-weight: 700; margin: 0 0 8px; line-height: 1.3; }
.ptm-print-bottom .prf-skill-row {
	display: grid;
	grid-template-columns: 58px minmax(36px, 1fr) 40px minmax(48px, 0.9fr);
	gap: 4px 6px;
	align-items: center;
	margin-bottom: 5px;
}
.ptm-print-bottom .prf-skill-name { font-weight: 700; font-size: 11px; }
.ptm-print-bottom .prf-skill-track {
	width: 100%;
	max-width: 64px;
	height: 7px;
	background: #e8e8e8;
	border-radius: 6px;
	overflow: hidden;
	border: 1px solid #ccc;
}
.ptm-print-bottom .prf-skill-fill { height: 100%; background: #2e7d32; }
.ptm-print-bottom .prf-skill-fill.low { background: #c0392b; }
.ptm-print-bottom .prf-skill-fill.mid { background: #e67e22; }
.ptm-print-bottom .prf-skill-fill.good { background: #27ae60; }
.ptm-print-bottom .prf-skill-fill.ex { background: #1e8449; }
.ptm-print-bottom .prf-skill-fill.pending { background: #cbd5e1; width: 0 !important; }
.ptm-print-bottom .prf-skill-score { font-size: 10px; font-weight: 700; white-space: nowrap; color: #333; }
.ptm-print-bottom .prf-skill-comment { font-size: 11px; font-weight: 700; text-align: right; color: #111; line-height: 1.25; }
.ptm-note {
	margin: 8px 0 0;
	padding: 10px 14px;
	font-size: 12px;
	font-weight: 600;
	text-align: center;
	line-height: 1.45;
	border: 1px solid #ddd;
}
.ptm-note-low { background: #fff8f8; color: #b71c1c; }
.ptm-note-ok { background: #f1f8e9; color: #1b5e20; }
@media (max-width: 1100px) {
	.ptm-print-bottom .prf-report-body {
		grid-template-columns: 1fr 90px;
		grid-template-areas:
			"left grade"
			"mid mid";
	}
	.ptm-print-bottom .prf-report-left { grid-area: left; }
	.ptm-print-bottom .prf-report-mid { grid-area: mid; }
	.ptm-print-bottom .prf-report-right { grid-area: grade; align-items: flex-start; }
}
@media (max-width: 760px) {
	.ptm-filter-item,
	.ptm-filter-branch,
	.ptm-filter-month,
	.ptm-filter-year,
	.ptm-filter-student { width: calc(50% - 6px); }
	.ptm-filter-actions { width: 100%; margin-left: 0; }
	.ptm-print-bottom .prf-report-body {
		grid-template-columns: 1fr;
		grid-template-areas:
			"left"
			"mid"
			"grade";
	}
	.ptm-print-bottom .prf-sum-row { grid-template-columns: 1fr; }
	.ptm-print-bottom .swr-field-row { flex-wrap: wrap; }
	.ptm-print-bottom .prf-report-right { justify-content: flex-start; }
	.ptm-side-label { display: none; }
	.panel-ptm-report-wrap .ptm-report-sheet { padding-left: 12px; }
}
@media print {
	@page { size: A4 landscape; margin: 8mm; }
	.no-print,
	.sidebar-left, .page-header, .inner-menu, header.page-header, .header,
	.panel-ptm-report-wrap .panel-heading,
	.panel-ptm-report-wrap form.mb-md {
		display: none !important;
	}
	html, body, .inner-wrapper, .content-body, .panel, .panel-body, .row, .col-md-12 {
		width: 100% !important;
		margin: 0 !important;
		padding: 0 !important;
		float: none !important;
		background: #fff !important;
		border: none !important;
		box-shadow: none !important;
	}
	.panel-ptm-report-wrap { border: none; box-shadow: none; }
	.panel-ptm-report-wrap .panel-body { padding: 0; }
	.ptm-report-sheet {
		border: none !important;
		padding: 0 0 0 18px !important;
		background: #fff !important;
	}
	.ptm-side-label { color: #c00 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	.ptm-print-top {
		border: 1px solid #000 !important;
		margin: 0 0 8px !important;
		page-break-inside: avoid;
		break-inside: avoid;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.ptm-header-row {
		grid-template-columns: 68px 1fr 68px !important;
		padding: 8px 10px !important;
		border-bottom: 2px solid #c00 !important;
		background: #fff8f8 !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.ptm-header-side { min-height: 60px !important; }
	.ptm-school-name { font-size: 24px !important; color: #b71c1c !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	.ptm-month-label {
		font-size: 12px !important;
		border: 1px solid #999 !important;
		padding: 2px 10px !important;
		background: #fff !important;
	}
	.ptm-logo-img, .ptm-logo-placeholder { width: 58px !important; height: 58px !important; }
	.ptm-student-card {
		border: 0 !important;
		border-bottom: 1px solid #bbb !important;
		background: #f5f5f5 !important;
		padding: 8px 10px !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.ptm-student-inline { font-size: 12px !important; padding: 7px 10px !important; }
	.ptm-student-inline .ptm-student-name { font-size: 15px !important; }
	.ptm-student-left > span { padding-right: 10px !important; margin-right: 10px !important; }
	.ptm-pending-fee {
		font-size: 11px !important;
		color: #b71c1c !important;
		background: #ffebee !important;
		border: 1px solid #ef9a9a !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.ptm-header-center .ptm-report-title { font-size: 15px !important; margin: 0 !important; }
	.ptm-color-legend { font-size: 10px !important; gap: 6px 12px !important; color: #333 !important; }
	.ptm-dot {
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.ptm-dot-green { background: #c8e6c9 !important; }
	.ptm-dot-red { background: #ffcdd2 !important; }
	.ptm-dot-orange { background: #ffe0b2 !important; }
	.ptm-dot-pink { background: #fce4ec !important; }
	.ptm-dot-blue { background: #bbdefb !important; }
	.ptm-table tr.ptm-row-holiday td,
	.ptm-table td.ptm-val-holiday {
		background: #bbdefb !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.table-responsive,
	.ptm-table-wrap {
		overflow: visible !important;
		max-height: none !important;
		border: 1px solid #111 !important;
	}
	.ptm-table {
		page-break-inside: auto;
		border-collapse: collapse !important;
		width: 100% !important;
	}
	.ptm-table thead {
		display: table-header-group !important;
	}
	.ptm-table tfoot {
		display: table-footer-group !important;
	}
	.ptm-table thead th {
		position: static !important;
		left: auto !important;
		top: auto !important;
		box-shadow: none !important;
		background: #fff !important;
		border: 1px solid #111 !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.ptm-th-sr,
	.ptm-th-date,
	.ptm-td-sr,
	.ptm-td-date {
		position: static !important;
		left: auto !important;
		box-shadow: none !important;
		background: #fff !important;
	}
	.ptm-print-bottom { box-shadow: none !important; }
	.ptm-table tr { page-break-inside: avoid; }
	.ptm-table td, .ptm-table th { border: 1px solid #111 !important; }
	.ptm-table td.ptm-val-ok,
	.ptm-table td.ptm-val-bad,
	.ptm-table td.ptm-val-warn,
	.ptm-table td.ptm-val-miss,
	.ptm-print-bottom .swr-field-box.prf-field-green,
	.ptm-print-bottom .swr-field-box.prf-field-yellow,
	.ptm-print-bottom .swr-field-box.prf-field-orange,
	.ptm-print-bottom .swr-field-box.prf-field-red,
	.ptm-print-bottom .swr-subj-item .bx.hindi-green,
	.ptm-print-bottom .swr-subj-item .bx.prf-bx-yellow,
	.ptm-print-bottom .swr-subj-item .bx.prf-bx-orange,
	.ptm-print-bottom .swr-subj-item .bx.prf-bx-red,
	.ptm-print-bottom .swr-grade-box .g-val,
	.ptm-print-bottom .prf-skill-fill,
	.ptm-note-low,
	.ptm-note-ok {
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.ptm-table td.ptm-val-ok { background: #c8e6c9 !important; }
	.ptm-table td.ptm-val-bad { background: #ffcdd2 !important; }
	.ptm-table td.ptm-val-warn { background: #ffe0b2 !important; }
	.ptm-table td.ptm-val-miss { background: #fce4ec !important; }
	.ptm-print-bottom {
		page-break-inside: avoid;
		break-inside: avoid;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.ptm-print-bottom .prf-report-body { grid-template-columns: minmax(280px, 1.55fr) minmax(180px, 0.85fr) 88px !important; }
	.ptm-note {
		padding: 8px 10px !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
}
</style>

<script type="text/javascript">
function ptmOpenPdf() {
	var area = document.getElementById('ptm_print_area');
	if (!area) {
		window.print();
		return;
	}
	var clone = area.cloneNode(true);
	var hide = clone.querySelectorAll('.no-print');
	for (var i = 0; i < hide.length; i++) {
		if (hide[i].parentNode) {
			hide[i].parentNode.removeChild(hide[i]);
		}
	}
	var cssEl = document.getElementById('ptm-report-css');
	var css = cssEl ? cssEl.innerHTML : '';
	css += [
		'html,body{background:#fff;margin:10px;font-family:"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#111;}',
		'.ptm-report-sheet{border:none!important;padding:0!important;background:#fff!important;}',
		'.ptm-side-label{display:none!important;}',
		'.table-responsive,.ptm-table-wrap{overflow:visible!important;max-height:none!important;border:1px solid #111!important;}',
		'.ptm-table{width:100%!important;border-collapse:collapse!important;page-break-inside:auto;}',
		'.ptm-table thead{display:table-header-group!important;}',
		'.ptm-table thead th{position:static!important;top:auto!important;left:auto!important;background:#fff!important;border:1px solid #111!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}',
		'.ptm-th-sr,.ptm-th-date,.ptm-td-sr,.ptm-td-date{position:static!important;left:auto!important;box-shadow:none!important;background:#fff!important;}',
		'.ptm-table td,.ptm-table th{border:1px solid #111!important;}',
		'.ptm-table tr{page-break-inside:avoid;}',
		'*{-webkit-print-color-adjust:exact;print-color-adjust:exact;}',
		'@page{size:A4 landscape;margin:8mm;}',
		'@media print{html,body{margin:6px;} .no-print{display:none!important;}}'
	].join('');
	var title = 'PTM Report';
	var nameEl = area.querySelector('.ptm-student-name');
	var monthEl = area.querySelector('.ptm-month-label');
	if (nameEl && nameEl.textContent) {
		title = 'PTM Report - ' + nameEl.textContent.replace(/\s+/g, ' ').trim();
	}
	if (monthEl && monthEl.textContent) {
		title += ' (' + monthEl.textContent.replace(/\s+/g, ' ').trim() + ')';
	}
	var w = window.open('', '_blank');
	if (!w) {
		window.print();
		return;
	}
	w.document.open();
	w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + title.replace(/</g, '') + '</title><style>' + css + '</style></head><body><div class="panel-ptm-report-wrap">' + clone.outerHTML + '</div></body></html>');
	w.document.close();
	w.focus();
	setTimeout(function () {
		w.print();
	}, 450);
}
function ptmReinitSelect2(sel) {
	try {
		if ($(sel).data('select2')) {
			$(sel).select2('destroy');
		}
	} catch (e) {}
	$(sel).removeData('__select2');
	$(sel).themePluginSelect2({});
}
function ptmStudentPlaceholder(mode) {
	if (mode === 'class') {
		return '<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	if (mode === 'section') {
		return '<option value=""><?= htmlspecialchars(translate('select') . ' ' . translate('section'), ENT_QUOTES, 'UTF-8') ?></option>';
	}
	return '<option value=""><?= htmlspecialchars(translate('select'), ENT_QUOTES, 'UTF-8') ?></option>';
}
function ptmLoadStudents(selectedStudentId) {
	var cid = $('#class_id').val();
	var sid = $('#section_id').val();
	if (!cid || !sid) {
		$('#student_id').html(ptmStudentPlaceholder(!cid ? 'class' : 'section'));
		ptmReinitSelect2('#student_id');
		return;
	}
	$.ajax({
		url: base_url + 'student/get_students_by_class_section',
		type: 'POST',
		data: { class_id: cid, section_id: sid },
		success: function (html) {
			$('#student_id').html(html);
			if (selectedStudentId) {
				$('#student_id').val(String(selectedStudentId));
			}
			ptmReinitSelect2('#student_id');
		}
	});
}
$(document).ready(function () {
	var preselectedStudent = <?= (int) $filter_student_id ?>;
	var initialClass = $('#class_id').val();
	var initialSection = $('#section_id').val();
	if (initialClass && initialSection) {
		ptmLoadStudents(preselectedStudent);
	}
	$('#class_id').on('change', function () {
		var v = $(this).val();
		if (!v) {
			$('#section_id').html('<option value=""><?= htmlspecialchars(translate('select_class_first'), ENT_QUOTES, 'UTF-8') ?></option>');
			ptmReinitSelect2('#section_id');
			$('#student_id').html(ptmStudentPlaceholder('class'));
			ptmReinitSelect2('#student_id');
			return;
		}
		$('#student_id').html(ptmStudentPlaceholder('section'));
		ptmReinitSelect2('#student_id');
	});
	$('#section_id').on('change', function () {
		ptmLoadStudents(0);
	});
});
$(document).ajaxComplete(function (event, xhr, settings) {
	if (!settings.url) {
		return;
	}
	if (settings.url.indexOf('ajax/getSectionByClass') === -1) {
		return;
	}
	ptmReinitSelect2('#section_id');
	$('#student_id').html(ptmStudentPlaceholder('section'));
	ptmReinitSelect2('#student_id');
});
</script>
