<?php
if (empty($questions_detail)) {
	return;
}
$studentView = function_exists('is_student_loggedin') && is_student_loggedin();
?>
<hr>
<h5><i class="fas fa-list-ol"></i> <?=translate('question')?> — <?=translate('details')?></h5>
<?php
$qi = 0;
foreach ($questions_detail as $row) {
	$qi++;
	$qtext = $row->question;
	if ((int) $row->type === 5) {
		$qd = json_decode($row->question, true);
		$qtext = !empty($qd['instruction']) ? $qd['instruction'] : $row->question;
	}
	$corr = '';
	if ((int) $row->type === 1) {
		$k = 'opt_' . (int) $row->answer;
		$corr = isset($row->{$k}) ? $row->{$k} : '';
	} elseif ((int) $row->type === 2) {
		$ans = json_decode($row->answer, true);
		$bits = array();
		if (is_array($ans)) {
			foreach ($ans as $v) {
				$ok = 'opt_' . (int) $v;
				$bits[] = isset($row->{$ok}) ? $row->{$ok} : $v;
			}
		}
		$corr = implode(', ', $bits);
	} elseif ((int) $row->type === 3) {
		$corr = ((int) $row->answer === 1) ? strtoupper(translate('true')) : strtoupper(translate('false'));
	} elseif (in_array((int) $row->type, array(4, 8, 9, 10, 11), true)) {
		$corr = $row->answer;
	} elseif ((int) $row->type === 5) {
		$corr = $row->answer;
	} elseif ((int) $row->type === 6 || (int) $row->type === 7) {
		$corr = !empty($row->answer) ? $row->answer : '— (reference optional)';
	}
	$sba = $row->sb_ans;
	if ((int) $row->type === 2 && $sba !== null && $sba !== '') {
		$sa = json_decode($sba, true);
		$bits = array();
		if (is_array($sa)) {
			foreach ($sa as $v) {
				$ok = 'opt_' . (int) $v;
				$bits[] = isset($row->{$ok}) ? $row->{$ok} : $v;
			}
		}
		$sba = implode(', ', $bits);
	}
	if ((int) $row->type === 3 && $sba !== null && $sba !== '') {
		$sba = ((int) $sba === 1 || $sba === '1') ? strtoupper(translate('true')) : strtoupper(translate('false'));
	}
	if ($sba === null || $sba === '') {
		$sba = '—';
	}
	$hasExpl = !empty($row->explanation);

	$aiOn = isset($row->subjective_ai_enabled) && (int) $row->subjective_ai_enabled === 1;
	$evalStatus = isset($row->eval_status) ? $row->eval_status : null;
	$showMarks = !$studentView || (!empty($row->subjective_show_marks));
	$showFb = !$studentView || !empty($row->subjective_show_feedback);
	$showStr = !$studentView || !empty($row->subjective_show_strengths);
	$showWk = !$studentView || !empty($row->subjective_show_weaknesses);
	$showBr = !$studentView || !empty($row->subjective_show_breakdown);
	?>
	<section class="panel mb-md">
		<header class="panel-heading">
			<h4 class="panel-title"><?=translate('question')?> <?=$qi?></h4>
		</header>
		<div class="panel-body">
			<?php if (!empty($row->subjective_passage)) { ?>
			<div class="well well-sm mb-md"><strong>Passage / context:</strong><div><?= $row->subjective_passage ?></div></div>
			<?php } ?>
			<div class="mb-md"><?=$qtext?></div>
			<p class="mb-xs"><strong><?=translate('correct_answer')?> / reference:</strong> <?=$corr?></p>
			<p class="mb-none"><strong><?=translate('your')?> <?=translate('answer')?>:</strong> <?= nl2br(html_escape((string) $sba)) ?></p>

			<?php if ($aiOn && ((int) $row->type === 6 || (int) $row->type === 7)) {
				$obtained = null;
				if (isset($row->eval_manual_override_marks) && $row->eval_manual_override_marks !== null && $row->eval_manual_override_marks !== '') {
					$obtained = (float) $row->eval_manual_override_marks;
				} elseif ($evalStatus === 'completed' && isset($row->eval_total_score, $row->eval_max_score)) {
					$ts = (float) $row->eval_total_score;
					$ms = (float) $row->eval_max_score;
					$hy = isset($row->eval_hybrid_adjustment) ? (float) $row->eval_hybrid_adjustment : 0;
					$qm = (float) $row->marks;
					$obtained = $ms > 0 ? min($qm, ($ts / $ms) * $qm + $hy) : 0;
				}
			?>
			<div class="well well-sm mt-md mb-none">
				<strong>AI evaluation</strong>
				<span>(<?= html_escape((string) $evalStatus) ?>)</span>
				<?php if ($showMarks && $obtained !== null) { ?>
					<p class="mb-xs mt-xs"><strong>Obtained (this question):</strong> <?= number_format($obtained, 2, '.', '') ?> / <?= html_escape((string) $row->marks) ?></p>
				<?php } elseif ($showMarks) { ?>
					<p class="mb-xs mt-xs">Score pending or unavailable.</p>
				<?php } ?>
				<?php if ($showFb && !empty($row->eval_feedback_text)) { ?>
					<p class="mb-xs"><strong>Feedback:</strong> <?= nl2br(html_escape($row->eval_feedback_text)) ?></p>
				<?php } ?>
				<?php
				$strList = array();
				$wkList = array();
				if (!empty($row->eval_strengths_json)) {
					$strList = json_decode($row->eval_strengths_json, true);
				}
				if (!empty($row->eval_weaknesses_json)) {
					$wkList = json_decode($row->eval_weaknesses_json, true);
				}
				if ($showStr && is_array($strList) && count($strList) > 0) { ?>
					<p class="mb-xs"><strong>Strengths:</strong></p>
					<ul class="mb-sm"><li><?= implode('</li><li>', array_map('html_escape', $strList)) ?></li></ul>
				<?php } ?>
				<?php if ($showWk && is_array($wkList) && count($wkList) > 0) { ?>
					<p class="mb-xs"><strong>Weaknesses:</strong></p>
					<ul class="mb-sm"><li><?= implode('</li><li>', array_map('html_escape', $wkList)) ?></li></ul>
				<?php } ?>
				<?php if ($showBr && !empty($row->eval_breakdown_json)) {
					$bd = json_decode($row->eval_breakdown_json, true);
					if (is_array($bd)) { ?>
					<p class="mb-xs"><strong>Breakdown:</strong></p>
					<ul class="mb-none"><?php foreach ($bd as $k => $v) { ?>
						<li><?= html_escape((string) $k) ?>: <?= html_escape((string) $v) ?></li>
					<?php } ?></ul>
				<?php }
				} ?>
				<?php if (!$studentView && !empty($row->ans_id)) { ?>
				<hr class="solid mt-sm mb-sm">
				<div class="row">
					<div class="col-sm-8">
						<div class="form-inline subjective-admin-tools" data-answer-id="<?= (int) $row->ans_id ?>">
							<label class="mr-xs">Manual marks override</label>
							<input type="number" step="0.01" class="portal-input input-sm subjective-override-input" style="width:100px;" placeholder="Max <?= html_escape((string) $row->marks) ?>">
							<button type="button" class="btn btn-sm btn-primary subjective-override-btn ml-xs">Save override</button>
							<button type="button" class="btn btn-sm btn-default subjective-reeval-btn ml-xs">Re-evaluate with AI</button>
						</div>
						<p class="mt-xs mb-none">Requires API key and question AI enabled. Override clears on re-evaluate.</p>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if ($hasExpl) { ?>
			<div class="mt-md">
				<strong><?=translate('explanation')?>:</strong>
				<div class="mt-xs"><?=$row->explanation?></div>
			</div>
			<?php } ?>
		</div>
	</section>
<?php } ?>
<?php if (!$studentView) { ?>
<script type="text/javascript">
(function() {
	var base = <?= json_encode(site_url()) ?>;
	$(document).off('click', '.subjective-override-btn').on('click', '.subjective-override-btn', function() {
		var $w = $(this).closest('.subjective-admin-tools');
		var aid = $w.data('answer-id');
		var marks = $w.find('.subjective-override-input').val();
		$.post(base + 'onlineexam/subjective_override_marks', { answer_id: aid, manual_marks: marks }, function(res) {
			try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
			if (res && res.status === 'success') { alert('Saved'); location.reload(); }
			else { alert(res.error || 'Failed'); }
		});
	});
	$(document).off('click', '.subjective-reeval-btn').on('click', '.subjective-reeval-btn', function() {
		var $w = $(this).closest('.subjective-admin-tools');
		var aid = $w.data('answer-id');
		if (!confirm('Re-run AI for this answer?')) return;
		$.post(base + 'onlineexam/subjective_reevaluate', { answer_id: aid }, function(res) {
			try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
			if (res && res.status === 'success') { alert('Done'); location.reload(); }
			else { alert(res.error || 'Failed'); }
		});
	});
})();
</script>
<?php } ?>
