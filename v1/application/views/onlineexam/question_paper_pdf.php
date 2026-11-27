<?php
	$paper = isset($paper) && is_array($paper) ? $paper : array();
	$questions = isset($paper_questions) && is_array($paper_questions) ? $paper_questions : array();
	$typeMap = $this->onlineexam_model->question_type();
	$bd = isset($branch_details) && is_array($branch_details) ? $branch_details : array();
	$schoolName = !empty($bd['school_name']) ? $bd['school_name'] : (!empty($bd['name']) ? $bd['name'] : 'School Name');
	$cityLine = trim((isset($bd['city']) ? $bd['city'] : '') . ' ' . (isset($bd['state']) ? $bd['state'] : ''));
	$logoPath = isset($paper_logo_path) ? $paper_logo_path : '';
	$optLetters = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D');
	$linesByType = array(8 => 2, 9 => 3, 10 => 5, 11 => 8, 4 => 6, 6 => 5, 7 => 6);
	$paper_sections = isset($paper_sections) && is_array($paper_sections) ? $paper_sections : array();
	$paper_has_choice = false;
	foreach ($paper_sections as $ps) {
		if (!empty($ps['attempt']) && !empty($ps['qnum']) && (int) $ps['attempt'] < (int) $ps['qnum']) {
			$paper_has_choice = true;
			break;
		}
	}
	if (empty($paper_sections) && !empty($questions)) {
		$grouped = array();
		foreach ($questions as $q) {
			$qt = (int) (isset($q['type']) ? $q['type'] : 0);
			if (!isset($grouped[$qt])) {
				$grouped[$qt] = array();
			}
			$grouped[$qt][] = $q;
		}
		$roman = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K');
		$secIdx = 0;
		foreach ($grouped as $qt => $qlist) {
			$secIdx++;
			$n = count($qlist);
			$secMarks = 0;
			foreach ($qlist as $qq) {
				$secMarks += isset($qq['mark']) ? (float) $qq['mark'] : 0;
			}
			$paper_sections[] = array(
				'letter' => isset($roman[$secIdx]) ? $roman[$secIdx] : (string) $secIdx,
				'name' => isset($typeMap[(string) $qt]) ? $typeMap[(string) $qt] : ('Type ' . $qt),
				'qnum' => $n,
				'attempt' => $n,
				'marks' => $secMarks,
				'attempt_label' => 'Attempt all ' . $n,
				'questions' => $qlist,
			);
		}
	}
	$examTitle = !empty($paper['exam_name']) ? $paper['exam_name'] : (isset($paper['title']) ? $paper['title'] : 'Question Paper');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		body { font-family: dejavusans, sans-serif; font-size: 11pt; color: #111; line-height: 1.4; }
		.top { font-size: 9pt; color: #444; margin-bottom: 6px; }
		.school { font-size: 16pt; font-weight: bold; margin: 0; }
		.city { font-size: 10pt; color: #444; margin: 2px 0 0; }
		.title { text-align: center; font-size: 12pt; font-weight: bold; text-transform: uppercase; margin: 8px 0; letter-spacing: 0.5px; }
		.note { border: 1px solid #333; padding: 6px 8px; font-size: 9.5pt; margin: 8px 0 10px; }
		.sec { text-align: center; font-weight: bold; text-transform: uppercase; font-size: 11pt;
			border-top: 1.5px solid #111; border-bottom: 1px solid #111; padding: 4px 0; margin: 12px 0 8px; }
		.sec-att { display: block; margin-top: 2px; font-size: 9.5pt; font-weight: 600; text-transform: none; font-style: italic; }
		.q { margin: 0 0 10px; page-break-inside: avoid; }
		.qno { font-weight: bold; width: 22px; }
		.marks { font-size: 9pt; font-weight: bold; }
		.opt-l { font-weight: bold; width: 24px; }
		.line { border-bottom: 1px solid #888; height: 18px; }
		.match { border-collapse: collapse; width: 100%; margin-top: 4px; font-size: 10pt; }
		.match th, .match td { border: 1px solid #444; padding: 5px 6px; vertical-align: top; }
		.match th { background: #f0f0f0; text-align: center; }
		.end { text-align: center; font-weight: bold; margin-top: 16px; border-top: 1px dashed #666; padding-top: 8px; font-size: 10pt; }
		.meta td { padding: 2px 0; font-size: 10pt; }
		.stu td { padding: 4px 0; font-size: 10pt; }
		.blank { display: inline-block; min-width: 120px; border-bottom: 1px solid #222; }
	</style>
</head>
<body>
	<table class="top" width="100%"><tr>
		<td width="28%">Reg. No. ________</td>
		<td width="44%" align="center"><strong>Recognized by U.P Government</strong></td>
		<td width="28%" align="right">UDISE ________</td>
	</tr></table>

	<table width="100%" style="border-bottom:2px solid #111; padding-bottom:8px; margin-bottom:6px;">
		<tr>
			<?php if ($logoPath !== '' && file_exists($logoPath)): ?>
			<td width="70" valign="middle"><img src="<?= html_escape($logoPath) ?>" width="58" height="58"></td>
			<?php endif; ?>
			<td valign="middle">
				<div class="school"><?= html_escape($schoolName) ?></div>
				<?php if ($cityLine !== ''): ?><div class="city"><?= html_escape($cityLine) ?></div><?php endif; ?>
			</td>
		</tr>
	</table>

	<div class="title"><?= html_escape($examTitle) ?></div>

	<table class="stu" width="100%" style="border-bottom:1px solid #333; margin-bottom:8px;">
		<tr>
			<td width="40%">Name : <span class="blank">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
			<td width="30%">Roll No. : <span class="blank">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
			<td width="30%">Date : <span class="blank">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
		</tr>
	</table>

	<table class="meta" width="100%">
		<tr>
			<td width="50%"><strong>Class / Section :</strong> <?= html_escape((isset($paper['class_name']) ? $paper['class_name'] : '—') . ' / ' . (isset($paper['section_name']) ? $paper['section_name'] : '—')) ?></td>
			<td width="50%"><strong>Subject :</strong> <?= html_escape(isset($paper['subject_name']) ? $paper['subject_name'] : '—') ?></td>
		</tr>
		<tr>
			<td><strong>Time Allowed :</strong> <?= html_escape(!empty($paper['paper_time']) ? $paper['paper_time'] . ' Minutes' : '—') ?></td>
			<td><strong>Maximum Marks :</strong> <?= html_escape(isset($paper['max_marks']) && $paper['max_marks'] !== '' ? $paper['max_marks'] : '—') ?></td>
		</tr>
	</table>

	<div class="note"><strong>General Instructions :</strong> <?php if ($paper_has_choice): ?>Each section may include extra questions for choice — answer only up to the max attempt shown in that section. Write neatly and clearly.<?php else: ?>All questions are compulsory. Write neatly and clearly. Read each question carefully before answering.<?php endif; ?></div>

	<?php if (!empty($paper_sections)):
		$globalSn = 1;
		foreach ($paper_sections as $secBlock):
			$qlist = isset($secBlock['questions']) && is_array($secBlock['questions']) ? $secBlock['questions'] : array();
			if (empty($qlist)) {
				continue;
			}
			$secMarks = isset($secBlock['marks']) ? (float) $secBlock['marks'] : 0;
	?>
	<div class="sec">
		Section <?= html_escape(isset($secBlock['letter']) ? $secBlock['letter'] : '') ?> — <?= html_escape(isset($secBlock['name']) ? $secBlock['name'] : '') ?><?php if ($secMarks > 0): ?> (<?= rtrim(rtrim(number_format($secMarks, 1), '0'), '.') ?> Marks)<?php endif; ?>
		<?php if (!empty($secBlock['attempt_label'])): ?>
		<span class="sec-att"><?= html_escape($secBlock['attempt_label']) ?></span>
		<?php endif; ?>
	</div>
	<?php foreach ($qlist as $q):
		$qt = (int) (isset($q['type']) ? $q['type'] : 0);
		$qMark = isset($q['mark']) ? (float) $q['mark'] : 0;
		$stem = isset($q['question']) ? $q['question'] : '';
		$matchLeft = array();
		$matchRight = array();
		if ((int) $qt === 5) {
			$dec = json_decode($stem, true);
			if (is_array($dec)) {
				if (!empty($dec['instruction'])) {
					$stem = $dec['instruction'];
				}
				$matchLeft = !empty($dec['left']) && is_array($dec['left']) ? $dec['left'] : array();
				$matchRight = !empty($dec['right']) && is_array($dec['right']) ? $dec['right'] : array();
			}
		}
		$stemHtml = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', '', (string) $stem);
	?>
	<div class="q">
		<table width="100%"><tr>
			<td class="qno" valign="top"><?= (int) $globalSn++ ?>.</td>
			<td valign="top">
				<?php if ($qMark > 0): ?><div align="right" class="marks">[<?= rtrim(rtrim(number_format($qMark, 1), '0'), '.') ?>]</div><?php endif; ?>
				<div><?= $stemHtml ?></div>

				<?php if (in_array((int) $qt, array(1, 2), true)): ?>
				<table width="100%" style="margin-top:4px;">
					<?php
					$opts = array();
					for ($o = 1; $o <= 4; $o++) {
						$opt = isset($q['opt_' . $o]) ? trim(strip_tags($q['opt_' . $o])) : '';
						if ($opt !== '') {
							$opts[] = array('l' => $optLetters[$o], 't' => $opt);
						}
					}
					for ($i = 0; $i < count($opts); $i += 2):
					?>
					<tr>
						<td width="50%" valign="top"><span class="opt-l">(<?= $opts[$i]['l'] ?>)</span> <?= html_escape($opts[$i]['t']) ?></td>
						<td width="50%" valign="top"><?php if (isset($opts[$i + 1])): ?><span class="opt-l">(<?= $opts[$i + 1]['l'] ?>)</span> <?= html_escape($opts[$i + 1]['t']) ?><?php endif; ?></td>
					</tr>
					<?php endfor; ?>
				</table>
				<?php elseif ((int) $qt === 3): ?>
				<table width="100%" style="margin-top:4px;"><tr>
					<td width="50%"><span class="opt-l">(A)</span> True</td>
					<td width="50%"><span class="opt-l">(B)</span> False</td>
				</tr></table>
				<?php elseif ((int) $qt === 5):
					if (empty($matchRight)) {
						for ($o = 1; $o <= 4; $o++) {
							$ro = isset($q['opt_' . $o]) ? trim(strip_tags($q['opt_' . $o])) : '';
							if ($ro !== '') { $matchRight[] = $ro; }
						}
					}
					$rows = max(count($matchLeft), count($matchRight), 1);
				?>
				<table class="match">
					<thead><tr><th width="42%">Column A</th><th width="12%">Ans</th><th width="46%">Column B</th></tr></thead>
					<tbody>
					<?php for ($i = 0; $i < $rows; $i++): ?>
						<tr>
							<td><?= isset($matchLeft[$i]) ? html_escape(strip_tags((string) $matchLeft[$i])) : ('Item ' . ($i + 1)) ?></td>
							<td align="center">____</td>
							<td><?= isset($matchRight[$i]) ? ('(' . $optLetters[$i + 1] . ') ' . html_escape(strip_tags((string) $matchRight[$i]))) : '' ?></td>
						</tr>
					<?php endfor; ?>
					</tbody>
				</table>
				<?php else:
					$nLines = isset($linesByType[(int) $qt]) ? $linesByType[(int) $qt] : 4;
					for ($li = 0; $li < $nLines; $li++):
				?>
				<div class="line">&nbsp;</div>
				<?php endfor; endif; ?>
			</td>
		</tr></table>
	</div>
	<?php endforeach; endforeach; ?>
	<div class="end">*** End of Question Paper ***</div>
	<?php else: ?>
	<p align="center" style="color:#666;">No questions linked to this paper yet.</p>
	<?php endif; ?>
</body>
</html>
