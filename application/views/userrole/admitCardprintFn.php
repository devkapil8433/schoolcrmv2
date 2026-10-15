<?php
$certPadding = $template['top_space'] . 'px ' . $template['right_space'] . 'px ' . $template['bottom_space'] . 'px ' . $template['left_space'] . 'px';
$certBg = empty($template['background'])
    ? 'background:#fff;'
    : 'background-image:url("' . base_url('uploads/certificate/' . $template['background']) . '");background-repeat:no-repeat;background-size:100% 100%;';
$certSize = 'width:' . $template['layout_width'] . 'mm;height:' . $template['layout_height'] . 'mm;';
?>
<div class="font-sans print:[color-adjust:exact] print:[-webkit-print-color-adjust:exact]" id="certificate" style="padding: <?php echo $certPadding; ?>; <?php echo $certBg . $certSize; ?>">
	<?=$this->card_manage_model->admitCardTagsReplace($userID, $template, $print_date, $exam_id)?>
</section>
