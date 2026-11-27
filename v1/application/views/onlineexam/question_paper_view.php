<?php
	$paper = isset($paper) && is_array($paper) ? $paper : array();
	$questions = isset($paper_questions) && is_array($paper_questions) ? $paper_questions : array();
	$branch_id = isset($branch_id) ? (int) $branch_id : 0;
	$branch_query = $branch_id > 0 ? ('?branch_id=' . urlencode($branch_id)) : '';
	$typeMap = $this->onlineexam_model->question_type();
	$bd = isset($branch_details) && is_array($branch_details) ? $branch_details : array();
	$schoolName = !empty($bd['school_name']) ? $bd['school_name'] : (!empty($bd['name']) ? $bd['name'] : get_type_name_by_id('branch', $branch_id));
	$cityLine = trim((isset($bd['city']) ? $bd['city'] : '') . ' ' . (isset($bd['state']) ? $bd['state'] : ''));
	$logoUrl = isset($paper_logo_url) ? $paper_logo_url : '';
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
	// Fallback: group by type if section plan missing
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
?>
<style type="text/css">
	.qpv-screen-only { margin-bottom: 12px; }
	.qpv-sheet {
		--ink: #111;
		--muted: #444;
		background: #fff;
		color: var(--ink);
		max-width: 210mm;
		margin: 0 auto;
		padding: 14mm 16mm 18mm;
		border: 1px solid #ddd;
		font-family: "Times New Roman", Times, Georgia, serif;
		font-size: 12.5pt;
		line-height: 1.45;
	}
	.qpv-sheet * { box-sizing: border-box; }
	.qpv-topbar {
		display: flex; justify-content: space-between; align-items: flex-start;
		font-size: 9.5pt; color: var(--muted); margin-bottom: 8px;
	}
	.qpv-topbar .qpv-center { flex: 1; text-align: center; font-weight: 700; color: var(--ink); padding: 0 10px; font-size: 10pt; }
	.qpv-brand {
		display: flex; align-items: center; gap: 12px; margin-bottom: 10px;
		border-bottom: 2px solid #111; padding-bottom: 10px;
	}
	.qpv-brand img { width: 58px; height: 58px; object-fit: contain; }
	.qpv-brand h1 { margin: 0; font-size: 18pt; font-weight: 700; letter-spacing: 0.2px; }
	.qpv-brand p { margin: 2px 0 0; font-size: 11pt; font-weight: 600; color: var(--muted); }
	.qpv-exam-title {
		text-align: center; font-size: 13pt; font-weight: 700; margin: 8px 0 10px;
		text-transform: uppercase; letter-spacing: 0.5px;
	}
	.qpv-student {
		display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 8px 16px;
		font-size: 11pt; margin-bottom: 10px; padding: 8px 0;
		border-bottom: 1px solid #333;
	}
	.qpv-student .blank {
		display: inline-block; min-width: 55%; border-bottom: 1px solid #222; height: 1.1em; vertical-align: bottom;
	}
	.qpv-meta-grid {
		display: grid; grid-template-columns: 1fr 1fr; gap: 2px 24px;
		font-size: 11pt; margin-bottom: 8px;
	}
	.qpv-note {
		font-size: 10.5pt; margin: 0 0 12px; padding: 6px 8px;
		border: 1px solid #333; background: #fafafa;
	}
	.qpv-sec {
		margin: 14px 0 8px; padding: 4px 0;
		border-top: 1.5px solid #111; border-bottom: 1px solid #111;
		font-size: 12pt; font-weight: 700; text-align: center;
		text-transform: uppercase; letter-spacing: 0.4px;
	}
	.qpv-sec-att {
		display: block; margin-top: 2px; font-size: 10pt; font-weight: 600;
		text-transform: none; font-style: italic; letter-spacing: 0;
	}
	.qpv-q { margin: 0 0 14px; page-break-inside: avoid; }
	.qpv-q-head { display: flex; gap: 8px; align-items: flex-start; margin-bottom: 4px; }
	.qpv-q-no { font-weight: 700; min-width: 1.6em; flex-shrink: 0; }
	.qpv-q-body { flex: 1; min-width: 0; }
	.qpv-q-stem { margin: 0 0 6px; }
	.qpv-q-stem img { max-width: 100%; height: auto; }
	.qpv-q-marks { float: right; font-size: 10pt; font-weight: 700; white-space: nowrap; margin-left: 8px; }
	.qpv-opts {
		display: grid; grid-template-columns: 1fr 1fr; gap: 4px 18px;
		margin: 2px 0 0 4px; font-size: 11.5pt;
	}
	.qpv-opt { display: flex; gap: 6px; align-items: flex-start; }
	.qpv-opt-letter { font-weight: 700; min-width: 1.5em; }
	.qpv-tf { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 18px; margin-left: 4px; }
	.qpv-lines { margin: 6px 0 0; }
	.qpv-lines .ln {
		border-bottom: 1px solid #888; height: 22px; margin: 0;
	}
	.qpv-match { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 11pt; }
	.qpv-match th, .qpv-match td { border: 1px solid #444; padding: 6px 8px; vertical-align: top; }
	.qpv-match th { background: #f3f3f3; text-align: center; }
	.qpv-match .ans-blank { border-bottom: 1px solid #222; display: inline-block; min-width: 2.2em; height: 1em; }
	.qpv-end {
		margin-top: 20px; text-align: center; font-weight: 700; font-size: 11pt;
		border-top: 1px dashed #666; padding-top: 10px;
	}
	.panel-heading.qpv-toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
	@media print {
		@page { size: A4; margin: 12mm 12mm 14mm; }
		html, body { background: #fff !important; }
		body * { visibility: hidden !important; }
		.qpv-sheet, .qpv-sheet * { visibility: visible !important; }
		.qpv-sheet {
			position: absolute !important; left: 0; top: 0; width: 100%;
			max-width: none; border: none !important; padding: 0 !important; margin: 0 !important;
			box-shadow: none !important;
		}
		.qpv-screen-only, .panel-heading, .sidebar-left, .header, .page-header,
		.inner-menu, .content-with-menu-sm, .btn, button, .notice-top-bar, footer { display: none !important; }
		.qpv-note { background: transparent !important; }
		.qpv-q { page-break-inside: avoid; }
	}
</style>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading qpv-toolbar">
				<h4 class="panel-title"><i class="fas fa-file-lines"></i> Student question paper</h4>
				<div style="display:flex;gap:6px;flex-wrap:wrap;">
					<button type="button" class="btn btn-primary btn-sm" onclick="window.print();"><i class="fas fa-print"></i> Print for students</button>
					<a href="<?= base_url('onlineexam/question_paper_pdf/' . (int) $paper['id']) . $branch_query; ?>" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> Download PDF</a>
					<a href="<?= base_url('onlineexam/question_paper_edit/' . (int) $paper['id']) . $branch_query; ?>" class="btn btn-default btn-sm"><i class="fas fa-pen"></i> Edit</a>
					<a href="<?= base_url('onlineexam/my_question_paper') . $branch_query; ?>" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<p class="qpv-screen-only text-muted small">Ready to photocopy or distribute. Use <strong>Print for students</strong> (A4) or <strong>Download PDF</strong>.</p>

				<div class="qpv-sheet" id="qpv_print_area">
					<div class="qpv-topbar">
						<span>Reg. No. ________</span>
						<span class="qpv-center">Recognized by U.P Government</span>
						<span>UDISE ________</span>
					</div>
					<div class="qpv-brand">
						<?php if ($logoUrl !== ''): ?>
							<img src="<?= html_escape($logoUrl) ?>" alt="">
						<?php endif; ?>
						<div>
							<h1><?= html_escape($schoolName !== '' ? $schoolName : 'School Name'); ?></h1>
							<p><?= html_escape($cityLine !== '' ? $cityLine : ''); ?></p>
						</div>
					</div>
					<div class="qpv-exam-title">
						<?= html_escape(!empty($paper['exam_name']) ? $paper['exam_name'] : (isset($paper['title']) ? $paper['title'] : 'Question Paper')); ?>
					</div>
					<div class="qpv-student">
						<div>Name : <span class="blank"></span></div>
						<div>Roll No. : <span class="blank"></span></div>
						<div>Date : <span class="blank"></span></div>
					</div>
					<div class="qpv-meta-grid">
						<div><strong>Class / Section :</strong> <?= html_escape((isset($paper['class_name']) ? $paper['class_name'] : '—') . ' / ' . (isset($paper['section_name']) ? $paper['section_name'] : '—')) ?></div>
						<div><strong>Subject :</strong> <?= html_escape(isset($paper['subject_name']) ? $paper['subject_name'] : '—') ?></div>
						<div><strong>Time Allowed :</strong> <?= html_escape(!empty($paper['paper_time']) ? $paper['paper_time'] . ' Minutes' : '—') ?></div>
						<div><strong>Maximum Marks :</strong> <?= html_escape(isset($paper['max_marks']) && $paper['max_marks'] !== '' ? $paper['max_marks'] : '—') ?></div>
					</div>
					<div class="qpv-note"><strong>General Instructions :</strong> <?php if ($paper_has_choice): ?>Each section may include extra questions for choice — answer only up to the <em>max attempt</em> shown in that section. Write neatly and clearly.<?php else: ?>All questions are compulsory. Write neatly and clearly. Read each question carefully before answering.<?php endif; ?></div>

					<?php if (!empty($paper_sections)):
						$globalSn = 1;
						foreach ($paper_sections as $secBlock):
							$qlist = isset($secBlock['questions']) && is_array($secBlock['questions']) ? $secBlock['questions'] : array();
							if (empty($qlist)) {
								continue;
							}
							$secMarks = isset($secBlock['marks']) ? (float) $secBlock['marks'] : 0;
					?>
					<div class="qpv-sec">
						Section <?= html_escape(isset($secBlock['letter']) ? $secBlock['letter'] : '') ?> — <?= html_escape(isset($secBlock['name']) ? $secBlock['name'] : '') ?><?php if ($secMarks > 0): ?> (<?= rtrim(rtrim(number_format($secMarks, 1), '0'), '.') ?> Marks)<?php endif; ?>
						<?php if (!empty($secBlock['attempt_label'])): ?>
						<span class="qpv-sec-att"><?= html_escape($secBlock['attempt_label']) ?></span>
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
					?>
					<div class="qpv-q">
						<div class="qpv-q-head">
							<span class="qpv-q-no"><?= (int) $globalSn++ ?>.</span>
							<div class="qpv-q-body">
								<?php if ($qMark > 0): ?><span class="qpv-q-marks">[<?= rtrim(rtrim(number_format($qMark, 1), '0'), '.') ?>]</span><?php endif; ?>
								<div class="qpv-q-stem"><?= $stem ?></div>

								<?php if (in_array((int) $qt, array(1, 2), true)): ?>
								<div class="qpv-opts">
									<?php for ($o = 1; $o <= 4; $o++):
										$opt = isset($q['opt_' . $o]) ? trim(strip_tags($q['opt_' . $o])) : '';
										if ($opt === '') { continue; }
									?>
									<div class="qpv-opt"><span class="qpv-opt-letter">(<?= $optLetters[$o] ?>)</span><span><?= html_escape($opt) ?></span></div>
									<?php endfor; ?>
								</div>
								<?php elseif ((int) $qt === 3): ?>
								<div class="qpv-tf">
									<div class="qpv-opt"><span class="qpv-opt-letter">(A)</span><span>True</span></div>
									<div class="qpv-opt"><span class="qpv-opt-letter">(B)</span><span>False</span></div>
								</div>
								<?php elseif ((int) $qt === 5):
									if (empty($matchRight)) {
										for ($o = 1; $o <= 4; $o++) {
											$ro = isset($q['opt_' . $o]) ? trim(strip_tags($q['opt_' . $o])) : '';
											if ($ro !== '') { $matchRight[] = $ro; }
										}
									}
									$rows = max(count($matchLeft), count($matchRight), 1);
								?>
								<table class="qpv-match">
									<thead><tr><th width="45%">Column A</th><th width="10%">Ans</th><th width="45%">Column B</th></tr></thead>
									<tbody>
									<?php for ($i = 0; $i < $rows; $i++): ?>
										<tr>
											<td><?= isset($matchLeft[$i]) ? html_escape(strip_tags((string) $matchLeft[$i])) : ('Item ' . ($i + 1)) ?></td>
											<td style="text-align:center;"><span class="ans-blank"></span></td>
											<td><?= isset($matchRight[$i]) ? ('(' . $optLetters[$i + 1] . ') ' . html_escape(strip_tags((string) $matchRight[$i]))) : '' ?></td>
										</tr>
									<?php endfor; ?>
									</tbody>
								</table>
								<?php else:
									$nLines = isset($linesByType[(int) $qt]) ? $linesByType[(int) $qt] : 4;
								?>
								<div class="qpv-lines">
									<?php for ($li = 0; $li < $nLines; $li++): ?><div class="ln"></div><?php endfor; ?>
								</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php endforeach; endforeach; ?>
					<div class="qpv-end">*** End of Question Paper ***</div>
					<?php else: ?>
					<p style="text-align:center;color:#666;">No questions linked to this paper yet.</p>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</div>
</div>
