<?php
$result = $this->onlineexam_model->examResult($exam->id, $studentID);
$correct_ans = $result['correct_ans'];
$total_question = $result['total_question'];
$total_marks = $result['total_marks'];
$total_obtain_marks = $result['total_obtain_marks'];
$wrong_ans = $result['wrong_ans'];
$total_answered = $result['total_answered'];
$total_neg_marks = ($exam->neg_mark == 0 ? 0 : $result['total_neg_marks']);
$net_marks = $total_obtain_marks - $total_neg_marks;
$score_pct = ($total_marks === 0) ? '0.00' : number_format((($net_marks * 100) / $total_marks), 2, '.', '');
$studentName = $this->db->select('CONCAT_WS(" ",first_name, last_name) as name')->where('id', $studentID)->get('student')->row()->name;
if ($exam->mark_type == 1) {
    $obtain = $total_obtain_marks == 0 ? 0 : (($net_marks * 100) / $total_marks);
} else {
    $obtain = $net_marks;
}
$passed = $obtain >= $exam->passing_mark;
?>
<div class="well well-sm mb-md">
    <p class="text-muted mb-xs"><?php echo translate('student') . ' ' . translate('name'); ?></p>
    <h4 class="mt-none mb-xs"><?php echo html_escape($studentName); ?></h4>
    <p class="mb-xs"><?php echo html_escape($exam->title); ?></p>
    <span class="label label-<?php echo $passed ? 'success' : 'danger'; ?>"><?php echo $passed ? translate('passed') : translate('failed'); ?></span>
</div>

<table class="table table-bordered table-condensed mb-md">
    <tbody>
        <tr><th width="35%"><?php echo translate('class'); ?></th><td><?php echo html_escape($exam->class_name) . ' (' . $this->onlineexam_model->getSectionDetails($exam->section_id) . ')'; ?></td></tr>
        <tr><th><?php echo translate('subject'); ?></th><td><?php echo str_replace('<br>', ', ', $this->onlineexam_model->getSubjectDetails($exam->subject_id)); ?></td></tr>
        <tr><th><?php echo translate('start_time'); ?></th><td><?php echo _d($exam->exam_start); ?> <?php echo date('h:i A', strtotime($exam->exam_start)); ?></td></tr>
        <tr><th><?php echo translate('end_time'); ?></th><td><?php echo _d($exam->exam_end); ?> <?php echo date('h:i A', strtotime($exam->exam_end)); ?></td></tr>
        <tr><th><?php echo translate('mark') . ' ' . translate('type'); ?></th><td><?php echo $exam->mark_type == 1 ? translate('percent') : translate('fixed'); ?></td></tr>
        <tr><th><?php echo translate('passing_mark'); ?></th><td><?php echo $exam->passing_mark . ($exam->mark_type == 1 ? '%' : ''); ?></td></tr>
        <tr><th><?php echo translate('total') . ' ' . translate('question'); ?></th><td><?php echo $total_question; ?></td></tr>
        <tr><th><?php echo translate('total_answered'); ?></th><td><?php echo $total_answered; ?></td></tr>
        <tr><th><?php echo translate('total') . ' ' . translate('mark'); ?></th><td><?php echo $total_marks; ?></td></tr>
        <tr><th><?php echo translate('negative_mark'); ?></th><td><?php echo $total_neg_marks; ?></td></tr>
        <tr><th><?php echo translate('total_obtain_mark'); ?></th><td><?php echo $net_marks; ?></td></tr>
        <tr><th><?php echo translate('score'); ?></th><td><?php echo $score_pct; ?> (%)</td></tr>
        <tr><th><?php echo translate('correct_answer'); ?></th><td><?php echo $correct_ans; ?></td></tr>
        <tr><th><?php echo translate('incorrect_answer'); ?></th><td><?php echo $wrong_ans; ?></td></tr>
    </tbody>
</table>

<?php if (!empty($exam->publish_result)) {
    $this->load->view('onlineexam/result_question_breakdown', array('questions_detail' => isset($questions_detail) ? $questions_detail : array()));
} ?>
