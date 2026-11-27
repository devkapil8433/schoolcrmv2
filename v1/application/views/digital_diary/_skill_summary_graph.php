<?php
$skills = isset($skills) && is_array($skills) ? $skills : array();
$title = isset($graph_title) ? $graph_title : 'Student Skill Summary';
$windowDays = isset($window_days) ? (int) $window_days : 0;
if (empty($skills)) {
	return;
}
$barClass = function ($score, $pending) {
	if ($pending) {
		return 'skill-bar-fill skill-bar-pending';
	}
	$score = (int) $score;
	if ($score >= 9) {
		return 'skill-bar-fill skill-bar-ex';
	}
	if ($score >= 7) {
		return 'skill-bar-fill skill-bar-good';
	}
	if ($score >= 5) {
		return 'skill-bar-fill skill-bar-ok';
	}
	if ($score >= 1) {
		return 'skill-bar-fill skill-bar-low';
	}
	return 'skill-bar-fill';
};
?>
<style>
.skill-summary-graph { margin: 12px 0 18px; padding: 12px 14px; border: 1px solid #e4e8ee; border-radius: 6px; background: #fafbfc; }
.skill-summary-graph h5 { margin: 0 0 10px; font-weight: 700; color: #063944; }
.ssg-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap; }
.ssg-name { width: 120px; font-weight: 600; }
.ssg-track { flex: 1; min-width: 140px; height: 14px; background: #e8edf2; border-radius: 8px; overflow: hidden; }
.skill-bar-fill { height: 100%; background: #94a3b8; }
.skill-bar-pending { background: #cbd5e1; width: 0 !important; }
.skill-bar-low { background: #c0392b; }
.skill-bar-ok { background: #e67e22; }
.skill-bar-good { background: #27ae60; }
.skill-bar-ex { background: #1e8449; }
.ssg-score { width: 58px; font-weight: 700; }
.ssg-comment { flex: 1; min-width: 160px; color: #475569; }
.ssg-hint { width: 100%; margin: -2px 0 4px 130px; font-size: 11px; color: #64748b; }
@media print {
	.skill-summary-graph { break-inside: avoid; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	.skill-bar-fill { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
<div class="skill-summary-graph">
	<h5><?= html_escape($title) ?></h5>
	<?php foreach ($skills as $sk):
		$pending = !empty($sk['pending']) || (int) (isset($sk['score']) ? $sk['score'] : 0) < 1;
		$score = (int) (isset($sk['score']) ? $sk['score'] : 0);
		$pct = $pending ? 0 : max(0, min(100, $score * 10));
		$comment = isset($sk['comment']) ? trim((string) $sk['comment']) : '';
		if ($pending && $comment === '') {
			$comment = 'Not reviewed yet';
		}
		$name = isset($sk['activity_name']) ? $sk['activity_name'] : '';
		$daysReviewed = isset($sk['days_reviewed']) ? (int) $sk['days_reviewed'] : 0;
		$source = isset($sk['source']) ? (string) $sk['source'] : '';
		$hint = '';
		if (!$pending && $daysReviewed > 0) {
			if ($source === 'attendance') {
				$hint = 'based on ' . $daysReviewed . ' present day' . ($daysReviewed === 1 ? '' : 's');
			} elseif ($windowDays > 0) {
				$hint = 'based on ' . $daysReviewed . ' of ' . $windowDays . ' days';
			} else {
				$hint = 'based on ' . $daysReviewed . ' day' . ($daysReviewed === 1 ? '' : 's');
			}
		}
	?>
	<div class="ssg-row">
		<div class="ssg-name"><?= html_escape($name) ?></div>
		<div class="ssg-track"><div class="<?= $barClass($score, $pending) ?>" style="width:<?= (int) $pct ?>%;"></div></div>
		<div class="ssg-score"><?= $pending ? '(—/10)' : '(' . (int) $score . '/10)' ?></div>
		<div class="ssg-comment"><?= $comment !== '' ? '➔ ' . html_escape($comment) : '' ?></div>
		<?php if ($hint !== ''): ?>
		<div class="ssg-hint"><?= html_escape($hint) ?></div>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
</div>
