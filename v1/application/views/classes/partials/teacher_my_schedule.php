<?php
$my_timetables = isset($my_timetables) && is_array($my_timetables) ? $my_timetables : array();
$weekend_raw = isset($weekend_raw) ? $weekend_raw : '';
$dayIndexMap = array(
	'sunday' => '0',
	'monday' => '1',
	'tuesday' => '2',
	'wednesday' => '3',
	'thursday' => '4',
	'friday' => '5',
	'saturday' => '6',
);
$weekendDays = array();
if ($weekend_raw !== '' && $weekend_raw !== null) {
	foreach (explode(',', $weekend_raw) as $w) {
		$w = trim((string) $w);
		if ($w !== '') {
			$weekendDays[$w] = true;
		}
	}
}
$days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
$byDay = array();
foreach ($days as $d) {
	$byDay[$d] = array();
}
foreach ($my_timetables as $timetable) {
	$dKey = strtolower((string) $timetable->day);
	if (!isset($byDay[$dKey])) {
		continue;
	}
	$byDay[$dKey][] = $timetable;
}
$maxSlots = 0;
foreach ($days as $d) {
	if (empty($weekendDays[$dayIndexMap[$d]])) {
		$maxSlots = max($maxSlots, count($byDay[$d]));
	}
}
if ($maxSlots < 1) {
	$maxSlots = 1;
}
$todayKey = strtolower(date('l'));
?>
<style>
.co-my-schedule .panel-heading .panel-btn { top: 8px; }
.co-my-schedule .co-tt-day { width: 110px; font-weight: 700; vertical-align: middle !important; background: #f4f7f8; }
.co-my-schedule .co-tt-cell { min-width: 130px; vertical-align: top !important; font-size: 12px; }
.co-my-schedule .co-tt-subject { display: block; font-weight: 600; color: #1f2d3d; margin-bottom: 2px; }
.co-my-schedule .co-tt-meta { display: block; color: #6b7280; line-height: 1.35; }
.co-my-schedule .co-tt-holiday > td { background: #fff8e1; }
.co-my-schedule .co-tt-today > td.co-tt-day { background: #e3f2fd; color: #1565c0; }
.co-my-schedule .co-tt-empty { color: #9aa3ad; text-align: center; }
</style>
<section class="panel co-my-schedule">
	<header class="panel-heading">
		<div class="panel-btn">
			<a href="<?= base_url('timetable/teacherview') ?>" class="btn btn-default btn-circle">
				<i class="fas fa-external-link-alt"></i> Full schedule
			</a>
		</div>
		<h4 class="panel-title"><i class="fas fa-user-clock"></i> My Schedule</h4>
	</header>
	<div class="panel-body">
		<?php if (empty($my_timetables)): ?>
		<div class="alert alert-subl mb-none text-center">No periods assigned to you in the current timetable.</div>
		<?php else: ?>
		<div class="table-responsive">
			<table class="table table-bordered table-condensed mb-none">
				<tbody>
				<?php foreach ($days as $day):
					$isHolidayDay = !empty($weekendDays[$dayIndexMap[$day]]);
					$isToday = ($day === $todayKey);
					$rowClass = trim(($isHolidayDay ? 'co-tt-holiday' : '') . ($isToday ? ' co-tt-today' : ''));
					echo '<tr' . ($rowClass !== '' ? ' class="' . html_escape($rowClass) . '"' : '') . '>';
					echo '<td class="co-tt-day">' . strtoupper($day);
					if ($isToday) {
						echo '<br><span class="label label-primary">Today</span>';
					} elseif ($isHolidayDay) {
						echo '<br><span class="label label-warning">Holiday</span>';
					}
					echo '</td>';
					if ($isHolidayDay) {
						echo '<td class="co-tt-empty" colspan="' . (int) $maxSlots . '"><small>Weekly off</small></td>';
					} else {
						$rowCount = 0;
						foreach ($byDay[$day] as $timetable) {
							$rowCount++;
							$isBreak = ((string) $timetable->break === '1' || (int) $timetable->break === 1);
							$tStart = date('g:i A', strtotime($timetable->time_start));
							$tEnd = date('g:i A', strtotime($timetable->time_end));
							echo '<td class="co-tt-cell">';
							if ($isBreak) {
								echo '<span class="co-tt-subject">BREAK</span>';
								echo '<span class="co-tt-meta">' . html_escape($tStart) . ' – ' . html_escape($tEnd) . '</span>';
							} else {
								$subLabel = !empty($timetable->subject_name) ? $timetable->subject_name : get_type_name_by_id('subject', $timetable->subject_id);
								echo '<span class="co-tt-subject">' . html_escape($subLabel) . '</span>';
								echo '<span class="co-tt-meta">' . html_escape($tStart) . ' – ' . html_escape($tEnd) . '</span>';
								echo '<span class="co-tt-meta">' . html_escape($timetable->class_name) . ' (' . html_escape($timetable->section_name) . ')</span>';
								if (!empty($timetable->class_room)) {
									echo '<span class="co-tt-meta">' . html_escape($timetable->class_room) . '</span>';
								}
							}
							echo '</td>';
						}
						while ($rowCount < $maxSlots) {
							$rowCount++;
							echo '<td class="co-tt-empty"><small>—</small></td>';
						}
					}
					echo '</tr>';
				endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>
	</div>
</section>
