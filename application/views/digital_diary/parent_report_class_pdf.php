<?php
$school_print_info = isset($school_print_info) && is_array($school_print_info) ? $school_print_info : array();
$school_name = isset($school_name) ? trim((string) $school_name) : '';
$school_print_code = isset($school_print_code) ? trim((string) $school_print_code) : '';
$school_print_code_label = isset($school_print_code_label) ? $school_print_code_label : 'Branch ID';
$school_print_logo = isset($school_print_logo) ? $school_print_logo : '';
$class_name = isset($class_name) ? $class_name : '';
$section_name = isset($section_name) ? $section_name : '';
$report_date = isset($report_date) ? $report_date : date('Y-m-d');
$report_rows = isset($report_rows) && is_array($report_rows) ? $report_rows : array();
$date_display = strtotime($report_date) ? date('d M Y', strtotime($report_date)) : $report_date;
$school_address = isset($school_print_info['address']) ? trim((string) $school_print_info['address']) : '';
$school_city = isset($school_print_info['city']) ? trim((string) $school_print_info['city']) : '';
$school_state = isset($school_print_info['state']) ? trim((string) $school_print_info['state']) : '';
$school_location = implode(', ', array_filter(array($school_city, $school_state), 'strlen'));
$renderWork = function ($label, $value) {
	$value = trim((string) $value);
	$content = $value === '' || $value === '—' ? '—' : nl2br(html_escape($value));
	return '<div class="work-line"><strong>' . html_escape($label) . '</strong><span>' . $content . '</span></div>';
};
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		@page { margin: 5mm; }
		body { font-family: dejavusans, sans-serif; color: #26384a; font-size: 9pt; }
		.school-header { width: 100%; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #718096; }
		.school-header td { border: 0; vertical-align: middle; }
		.school-logo { width: 78px; text-align: left; }
		.school-logo img { max-width: 72px; max-height: 58px; }
		.school-details { text-align: center; }
		.school-name { margin: 0 0 3px; color: #1f3448; font-size: 17pt; font-weight: bold; }
		.school-contact { color: #435265; font-size: 8pt; line-height: 1.4; }
		.report-title { margin: 8px 0 5px; color: #293d51; font-size: 14pt; font-weight: bold; }
		.report-meta { margin-bottom: 7px; color: #526579; font-size: 9pt; }
		table.work-report { width: 100%; table-layout: fixed; border: 1px solid #000; border-collapse: collapse; background: #fff; font-size: 7pt; }
		.work-report th, .work-report td { padding: 4px 5px; border: 1px solid #000; color: #435265; vertical-align: top; line-height: 1.2; }
		.work-report th { background: #f7f9fc; color: #617185; text-align: center; font-weight: bold; }
		.work-report td.subject { color: #26384a; font-weight: bold; }
		.work-report td.center { text-align: center; }
		.work-line { margin: 0; padding: 2px 0; line-height: 1.2; }
		.work-line + .work-line { border-top: 1px solid #dfe6ee; }
		.work-line strong { display: inline-block; width: 48px; color: #66788a; font-size: 7pt; }
		.empty { padding: 12px; color: #66788a; text-align: center; }
	</style>
</head>
<body>
	<table class="school-header">
		<tr>
			<td class="school-logo">
				<?php if ($school_print_logo !== ''): ?>
				<img src="<?= html_escape($school_print_logo) ?>" alt="School logo">
				<?php endif; ?>
			</td>
			<td class="school-details">
				<div class="school-name"><?= html_escape($school_name !== '' ? $school_name : 'School') ?></div>
				<div class="school-contact">
					<?php if ($school_address !== '' || $school_location !== ''): ?>
					<div><?= html_escape(implode(', ', array_filter(array($school_address, $school_location), 'strlen'))) ?></div>
					<?php endif; ?>
					<?php if (!empty($school_print_info['mobileno'])): ?>
					<span>Mobile: <?= html_escape($school_print_info['mobileno']) ?></span>
					<?php endif; ?>
					<?php if (!empty($school_print_info['email'])): ?>
					<span> &nbsp; | &nbsp; Email: <?= html_escape($school_print_info['email']) ?></span>
					<?php endif; ?>
					<?php if ($school_print_code !== ''): ?>
					<span> &nbsp; | &nbsp; <?= html_escape($school_print_code_label) ?>: <?= html_escape($school_print_code) ?></span>
					<?php endif; ?>
				</div>
			</td>
			<td style="width:78px;"></td>
		</tr>
	</table>
	<div class="report-title">Class work / Home work — <?= html_escape($date_display) ?></div>
	<div class="report-meta">
		<strong>Class:</strong> <?= html_escape($class_name) ?> - <?= html_escape($section_name) ?>
	</div>
	<table class="work-report">
		<thead>
			<tr>
				<th style="width:12%;">Subject</th>
				<th style="width:7%;">Total Lesson</th>
				<th style="width:13%;">Now Going On</th>
				<th style="width:23%;">Class Work</th>
				<th style="width:23%;">Home Work</th>
				<th style="width:13%;">Learning work</th>
				<th style="width:9%;">Syllabus Percentage</th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($report_rows)): ?>
			<tr><td colspan="7" class="empty">No work recorded for this class on this date.</td></tr>
			<?php else: ?>
			<?php foreach ($report_rows as $row):
				$classActivity = isset($row['class_work_activity']) && trim((string) $row['class_work_activity']) !== ''
					? $row['class_work_activity']
					: (isset($row['class_work']) ? $row['class_work'] : '—');
				$homeActivity = isset($row['home_work_activity']) && trim((string) $row['home_work_activity']) !== ''
					? $row['home_work_activity']
					: (isset($row['home_work']) ? $row['home_work'] : '—');
			?>
			<tr>
				<td class="subject"><?= !empty($row['subject_name']) ? html_escape($row['subject_name']) : '—' ?></td>
				<td class="center"><?= isset($row['total_lesson']) && $row['total_lesson'] !== '' ? (int) $row['total_lesson'] : '—' ?></td>
				<td><?= isset($row['now_going_on']) && $row['now_going_on'] !== '' ? nl2br(html_escape($row['now_going_on'])) : '—' ?></td>
				<td>
					<?= $renderWork('Work / activity', $classActivity) ?>
					<?= $renderWork('Notebook', isset($row['class_work_notebook']) ? $row['class_work_notebook'] : '—') ?>
				</td>
				<td>
					<?= $renderWork('Work / activity', $homeActivity) ?>
					<?= $renderWork('Notebook', isset($row['home_work_notebook']) ? $row['home_work_notebook'] : '—') ?>
				</td>
				<td><?= isset($row['learning_work']) && $row['learning_work'] !== '' ? nl2br(html_escape($row['learning_work'])) : '—' ?></td>
				<td class="center"><?= isset($row['syllabus_percentage']) && $row['syllabus_percentage'] !== '' ? html_escape($row['syllabus_percentage']) : '—' ?></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</body>
</html>
