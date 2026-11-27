<div class="portal-table-wrap mt-md mb-md">
	<table class="portal-table">
		<tbody>
			<tr>
				<th><?=translate('question')?></th>
				<td colspan="3"><?php
					if ($questions['type'] == 5) {
						$qDec = json_decode($questions['question'], true);
						echo !empty($qDec['instruction']) ? $qDec['instruction'] : $questions['question'];
					} else {
						echo $questions['question'];
					}
				?></td>
			</tr>
			<tr>
				<th><?=translate('class')?></th>
				<td><?php echo get_type_name_by_id('class', $questions['class_id']) ?> (<?php echo get_type_name_by_id('section', $questions['section_id']) ?>)</td>
				<th><?=translate('subject')?></th>
				<td><?php echo (!empty($questions['subject_id']) ? get_type_name_by_id('subject', $questions['subject_id']) : 'N/A'); ?></td>
			</tr>
			<tr>
				<th><?=translate('type')?></th>
				<td><?php
					echo html_escape($this->onlineexam_model->resolve_question_type_label(
						isset($questions['type']) ? $questions['type'] : 0,
						isset($questions['custom_question_type']) ? $questions['custom_question_type'] : ''
					));
				?></td>
				<th><?=translate('level')?></th>
				<td><?php $arrayLevel = $this->onlineexam_model->question_level(); echo $arrayLevel[$questions['level']]; ?></td>
			</tr>
			<tr>
				<th><?=translate('default_mark')?></th>
				<td colspan="3"><?php echo $questions['mark']; ?></td>
			</tr>
			<?php if (!empty($questions['explanation'])) { ?>
			<tr>
				<th><?=translate('explanation')?></th>
				<td colspan="3"><?php echo $questions['explanation']; ?></td>
			</tr>
			<?php } ?>

		</tbody>
	</table>
</div>

<h5 class="mt-lg text-weight-bold"><?php echo translate('answer')?> : </h5>
<?php
$optionArr = array (
	'opt_1' => '1',
	'opt_2' => '2',
	'opt_3' => '3',
	'opt_4' => '4',
);
if ($questions['type'] == 1) { 
	$answer = $questions['answer'];
	?>
<div class="portal-table-wrap mt-md mb-md">
	<table class="portal-table">
		<thead>
			<tr>
				<th><?=translate('option')?></th>
				<th><?=translate('correct')?></th>
				<th><?=translate('details')?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ($optionArr as $key => $value) {
		if (!empty($questions[$key])) { ?>
			<tr>
				<th><?=translate('option') . " " . $value ?></th>
				<td><?php if ($value == $answer) { ?><i class="far fa-circle-check text-xl"></i><?php } else { ?><i class="fas fa-ellipsis text-xl"></i><?php } ?></td>
				<td><?php echo $questions[$key] ?></td>
			</tr>
		<?php } } ?>
		</tbody>
	</table>
</div>
<?php } elseif($questions['type'] == 2) { 
$answer = json_decode($questions['answer'], true );
	?>
<div class="portal-table-wrap mt-md mb-md">
	<table class="table table-striped table-bordered  table-condensed mb-none">
		<thead>
			<tr>
				<th><?=translate('option')?></th>
				<th><?=translate('correct')?></th>
				<th><?=translate('details')?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ($optionArr as $key => $value) {
		if (!empty($questions[$key])) { ?>
			<tr>
				<th><?=translate('option') . " " . $value ?></th>
				<td><?php if (in_array($value, $answer)) { ?><i class="far fa-circle-check text-xl"></i><?php } else { ?><i class="fas fa-ellipsis text-xl"></i><?php } ?></td>
				<td><?php echo $questions[$key] ?></td>
			</tr>
		<?php } } ?>
		</tbody>
	</table>
</div>
<?php } elseif($questions['type'] == 3) { 
	echo $questions['answer'] == 1 ? strtoupper(translate("true")) : strtoupper(translate("false"));
} elseif($questions['type'] == 4 || $questions['type'] == 6 || $questions['type'] == 7) {
	echo $questions['answer'];
} elseif($questions['type'] == 5) {
	$qDec = json_decode($questions['question'], true);
	$leftItems = !empty($qDec['left']) ? $qDec['left'] : array();
	$rightItems = !empty($qDec['right']) ? $qDec['right'] : array();
	$pairCount = max(count($leftItems), count($rightItems), 4);
	$pairCount = min($pairCount, 10);
	?>
<div class="portal-table-wrap mt-md mb-md">
	<table class="portal-table">
		<thead>
			<tr>
				<th>#</th>
				<th>Left</th>
				<th>Right (Match)</th>
			</tr>
		</thead>
		<tbody>
		<?php for ($i = 0; $i < $pairCount; $i++) {
			$optKey = 'opt_' . ($i + 1);
			$rightVal = isset($rightItems[$i]) ? $rightItems[$i] : (isset($questions[$optKey]) ? $questions[$optKey] : '');
			if (!empty($rightVal) || !empty($leftItems[$i])) { ?>
			<tr>
				<td><?php echo $i + 1 ?></td>
				<td><?php echo isset($leftItems[$i]) ? $leftItems[$i] : '—' ?></td>
				<td><?php echo $rightVal ? $rightVal : '—' ?></td>
			</tr>
		<?php } } ?>
		</tbody>
	</table>
</div>
<?php } ?>