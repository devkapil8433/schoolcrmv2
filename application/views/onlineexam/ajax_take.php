<?php
if (!empty($questions)) {
	$totalQuestions = count($questions);
?>
<div class="row">
	<div class="col-md-3">
		<section class="panel">
			<div class="panel-body">
				<h5 class="panel-title"><i class="fas fa-clock"></i> <?=translate('time') . " " . translate('status')?></h5>
				<table class="table table-condensed mb-none">
					<tbody>
						<tr>
							<td><strong><?=translate('total') . " " . translate('time')?></strong></td>
							<td><?=$exam->duration?></td>
						</tr>
						<tr>
							<td><strong><?=translate('remain_time')?></strong></td>
							<td><h4 class="mt-none mb-none remain_duration"><?=$exam->duration?></h4></td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>
		<section class="panel">
			<div class="panel-body">
				<h5 class="panel-title"><i class="fas fa-circle-question"></i> <?=translate('total_questions_map')?></h5>
				<nav>
					<ul class="nav nav-pills nav-stacked exam-question-map">
						<?php foreach ($questions as $key => $question) { ?>
						<li><a class="exam-que-btn <?=$key == 0 ? 'active' : '' ?>" id="question<?php echo $key + 1 ?>" href="javascript:void(0);" onclick="changeQuestion(<?php echo $key + 1 ?>)"><?php echo $key + 1 ?></a></li>
						<?php } ?>
					</ul>
				</nav>
			</div>
		</section>
	</div>
	<div class="col-md-9">
		<div class="wizard" data-initialize="wizard" id="fueluxWizard">
			<div class="steps-container hidden">
				<ul class="steps hidden">
					<?php foreach (range(1, $totalQuestions) as $value) { ?>
					<li data-step="<?=$value?>" class="<?=$value == 1 ? 'active' : ''?>"></li>
					<?php } ?>
				</ul>
			</div>
			<?php echo form_open('userrole/onlineexam_submit_answer', array('id' => 'answerForm')); ?>
			<input type="hidden" name="online_exam_id" value="<?=$exam->id?>">
			<div class="step-content">
				<?php foreach ($questions as $key => $question) { ?>
				<div class="step-pane <?=$key == 0 ? 'active' : '' ?>" data-step="<?=$key + 1?>">
					<section class="panel">
						<div class="panel-body">
							<h5 class="panel-title"><i class="fas fa-clipboard-question"></i> <?=translate('question')?> <?=$key + 1?> <?=translate('of')?> <?=$totalQuestions?></h5>
							<div class="mb-md">
								<?php
								if ($question->type == 5) {
									$qDecoded = json_decode($question->question, true);
									echo !empty($qDecoded['instruction']) ? $qDecoded['instruction'] : $question->question;
								} else {
									echo $question->question;
								}
								?>
							</div>
							<div id="step<?=$key + 1?>">
								<?php
								$quesOption = array('opt_1' => 1, 'opt_2' => 2, 'opt_3' => 3, 'opt_4' => 4);
								if ($question->type == 1) {
									foreach ($quesOption as $quesOption_key => $quesOption_value) {
										if (!empty($question->{$quesOption_key})) {
								?>
								<div class="radio-custom radio-primary mb-sm">
									<input type="radio" value="<?=$quesOption_value?>" name="answer[<?=$question->question_id?>][<?=$question->type?>]" id="opt<?=$key . $quesOption_value?>">
									<label for="opt<?=$key . $quesOption_value?>"><?=$question->{$quesOption_key}?></label>
								</div>
								<?php } }
								} elseif ($question->type == 2) {
									foreach ($quesOption as $quesOption_key => $quesOption_value) {
										if (!empty($question->{$quesOption_key})) {
								?>
								<div class="checkbox-custom checkbox-primary mb-sm">
									<input type="checkbox" name="answer[<?=$question->question_id?>][<?=$question->type?>][]" value="<?=$quesOption_value?>" id="chk<?=$key . $quesOption_value?>">
									<label for="chk<?=$key . $quesOption_value?>"><?=$question->{$quesOption_key}?></label>
								</div>
								<?php } }
								} elseif ($question->type == 3) { ?>
								<div class="radio-custom radio-primary mb-sm">
									<input type="radio" value="1" name="answer[<?=$question->question_id?>][<?=$question->type?>]" id="tf1<?=$key?>">
									<label for="tf1<?=$key?>">TRUE</label>
								</div>
								<div class="radio-custom radio-primary mb-sm">
									<input type="radio" value="2" name="answer[<?=$question->question_id?>][<?=$question->type?>]" id="tf0<?=$key?>">
									<label for="tf0<?=$key?>">FALSE</label>
								</div>
								<?php } elseif ($question->type == 4) { ?>
								<div class="form-group">
									<label class="control-label">Answer</label>
									<input type="text" class="form-control" name="answer[<?=$question->question_id?>][<?=$question->type?>]">
								</div>
								<?php } elseif ((int) $question->type === 8) { ?>
								<div class="form-group">
									<label class="control-label">Answer</label>
									<input type="text" class="form-control" name="answer[<?=$question->question_id?>][<?=$question->type?>]" placeholder="Fill in the blank">
								</div>
								<?php } elseif (in_array((int) $question->type, array(9, 10, 11), true)) {
									$taRows = ((int) $question->type === 9) ? 3 : (((int) $question->type === 10) ? 5 : 8);
								?>
								<div class="form-group">
									<label class="control-label">Your answer</label>
									<textarea class="form-control" rows="<?= (int) $taRows ?>" name="answer[<?=$question->question_id?>][<?=$question->type?>]" placeholder="Type your answer here"></textarea>
								</div>
								<?php } elseif ($question->type == 6 || $question->type == 7) {
									$pass = isset($question->subjective_passage) ? trim($question->subjective_passage) : '';
									if ($pass !== '') { ?>
								<div class="well well-sm mb-md">
									<label class="control-label">Passage / context</label>
									<div><?=$pass?></div>
								</div>
								<?php } ?>
								<div class="form-group">
									<label class="control-label">Your answer</label>
									<textarea class="form-control" rows="8" name="answer[<?=$question->question_id?>][<?=$question->type?>]" placeholder="Type your answer here"></textarea>
								</div>
								<?php } elseif ($question->type == 5) {
									$qDecoded = json_decode($question->question, true);
									$leftItems = !empty($qDecoded['left']) ? $qDecoded['left'] : array();
									$rightOptions = array();
									if (!empty($qDecoded['right'])) {
										foreach ($qDecoded['right'] as $ri => $rt) {
											if (trim(strip_tags($rt)) !== '') $rightOptions[$ri + 1] = $rt;
										}
									} else {
										for ($i = 1; $i <= 4; $i++) {
											$optKey = 'opt_' . $i;
											if (!empty($question->{$optKey})) $rightOptions[$i] = $question->{$optKey};
										}
									}
									$matchCount = max(count($leftItems), count($rightOptions), 4);
									$matchCount = min($matchCount, 10);
									for ($idx = 0; $idx < $matchCount; $idx++) {
										$leftLabel = isset($leftItems[$idx]) ? $leftItems[$idx] : ('Item ' . ($idx + 1));
										$rowNum = $idx + 1;
								?>
								<div class="form-group">
									<label class="control-label"><?=strip_tags($leftLabel)?></label>
									<select class="form-control match-select" data-question-id="<?=$question->question_id?>" data-row="<?=$rowNum?>">
										<option value="">— Select match —</option>
										<?php foreach ($rightOptions as $optVal => $optText) { ?>
										<option value="<?=$optVal?>"><?=strip_tags($optText)?></option>
										<?php } ?>
									</select>
								</div>
								<?php } ?>
								<input type="hidden" name="answer[<?=$question->question_id?>][5]" id="match_ans_<?=$question->question_id?>" value="">
								<?php } ?>
								<?php if ($exam->marks_display == 1 || $exam->neg_mark == 1) { ?>
								<div class="mt-md">
									<?php if ($exam->marks_display == 1) { ?>
									<span class="mr-md">Marks: <strong><?=$question->marks?></strong></span>
									<?php } if ($exam->neg_mark == 1) { ?>
									<span><?=translate('negative_marks')?>: <strong><?=$question->neg_marks?></strong></span>
									<?php } ?>
								</div>
								<?php } ?>
							</div>
						</div>
					</section>
				</div>
				<?php } ?>
			</div>
			<div class="panel-footer">
				<button class="btn btn-default btn-prev" type="button" id="prevbutton" disabled="disabled"><i class="fa fa-angle-left"></i> Previous</button>
				<button class="btn btn-default btn-next" type="button" id="nextbutton" data-last="Complete"><?=translate('next')?> <i class="fa fa-angle-right"></i></button>
				<button class="btn btn-danger" type="button" onclick="clearAnswer()"><i class="fas fa-xmark"></i> Clear Answer</button>
				<?php if ($exam->questions_qty > 2) { ?>
				<button class="btn btn-default" type="button" id="finishedbutton" onclick="completeExams()"><i class="fas fa-check"></i> <?=translate('submit')?></button>
				<?php } ?>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
<?php } else {
	echo '<div class="alert alert-warning mb-none">' . translate('no_questions_have_been_assigned') . ' !</div>';
} ?>
<script type="text/javascript">
$(document).off('change', '.match-select').on('change', '.match-select', function() {
	var qid = $(this).data('question-id');
	var parts = [];
	$('.match-select[data-question-id="' + qid + '"]').each(function() {
		var row = $(this).data('row');
		var val = $(this).val();
		if (val) parts.push(row + '-' + val);
	});
	$('#match_ans_' + qid).val(parts.sort().join(','));
});
</script>
