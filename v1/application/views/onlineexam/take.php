<?php
$studentAttempts = $this->onlineexam_model->getStudentAttempt($exam->id);
$startTime = strtotime($exam->exam_start);
$endTime = strtotime($exam->exam_end);
$now = strtotime('now');
$examActive = ($startTime <= $now && $now <= $endTime) && $exam->publish_status == 1;
$paymentRow = null;
if ($exam->exam_type == 1) {
    $paymentRow = $this->db->select('transaction_id')->where(array('student_id' => get_loggedin_user_id(), 'exam_id' => $exam->id))->get('online_exam_payment')->row();
}
?>
<section class="panel">
    <header class="panel-heading">
        <div class="panel-actions">
            <a href="<?php echo base_url('userrole/online_exam'); ?>" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> <?php echo translate('online_exam') . ' ' . translate('list'); ?>
            </a>
        </div>
        <h4 class="panel-title"><i class="fas fa-laptop-code"></i> <?php echo translate('start_exam'); ?></h4>
    </header>
    <div class="panel-body">
        <div class="well well-sm mb-md">
            <p class="text-muted mb-xs"><?php echo translate('exam') . ' ' . translate('name'); ?></p>
            <h3 class="mt-none mb-xs"><?php echo html_escape($exam->title); ?></h3>
            <?php if ($examActive && empty($studentSubmitted)) { ?>
                <span class="label label-success"><?php echo translate('exam_status'); ?>: <?php echo translate('active'); ?></span>
            <?php } ?>
        </div>

        <div class="row mb-md">
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('start_time'); ?></strong><br>
                <?php echo _d($exam->exam_start); ?> <?php echo date('h:i A', strtotime($exam->exam_start)); ?>
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('end_time'); ?></strong><br>
                <?php echo _d($exam->exam_end); ?> <?php echo date('h:i A', strtotime($exam->exam_end)); ?>
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('class'); ?></strong><br>
                <?php echo html_escape($exam->class_name); ?> (<?php echo $this->onlineexam_model->getSectionDetails($exam->section_id); ?>)
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('subject'); ?></strong><br>
                <?php echo str_replace('<br>', ', ', $this->onlineexam_model->getSubjectDetails($exam->subject_id)); ?>
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('total') . ' ' . translate('question'); ?></strong><br>
                <?php echo $exam->questions_qty; ?>
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('duration'); ?></strong><br>
                <?php echo html_escape($exam->duration); ?>
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('exam') . ' ' . translate('total_attempt'); ?></strong><br>
                <?php echo $exam->limits_participation; ?>
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('your') . ' ' . translate('total_attempt'); ?></strong><br>
                <?php echo $studentAttempts; ?>
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('passing_mark'); ?></strong><br>
                <?php echo $exam->passing_mark . ($exam->mark_type == 1 ? ' (%)' : ''); ?>
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('negative_mark'); ?></strong><br>
                <?php echo ($exam->neg_mark == 1) ? translate('yes') : translate('no'); ?>
            </div>
<?php if ($exam->exam_type == 1) { ?>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('exam') . ' ' . translate('fees'); ?></strong><br>
                <?php echo currencyFormat($exam->fee); ?>
            </div>
            <div class="col-md-4 col-sm-6 mb-sm">
                <strong><?php echo translate('payment_informations'); ?></strong><br>
                <?php echo (!empty($paymentRow) && !empty($paymentRow->transaction_id)) ? html_escape($paymentRow->transaction_id) : '—'; ?>
            </div>
<?php } ?>
        </div>

        <div class="well mb-md">
            <h5 class="mt-none"><i class="fas fa-circle-info"></i> <?php echo translate('instruction'); ?></h5>
            <div><?php echo $exam->instruction; ?></div>
        </div>

<?php if (empty($studentSubmitted)) {
    if ($examActive) { ?>
        <div class="text-center">
            <button type="button" class="btn btn-default btn-lg start_btn" data-examid="<?php echo $exam->id; ?>" data-duration="<?php echo html_escape($exam->duration); ?>" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                <i class="fas fa-play"></i> <?php echo translate('start_exam'); ?>
            </button>
            <p class="text-muted mt-sm mb-none"><?php echo translate('duration'); ?>: <?php echo html_escape($exam->duration); ?> · <?php echo translate('total') . ' ' . translate('question'); ?>: <?php echo $exam->questions_qty; ?></p>
        </div>
<?php } else { ?>
        <div class="text-center">
            <i class="fas fa-clock fa-3x text-muted"></i>
            <p class="text-muted"><?php echo ($now < $startTime) ? translate('exam') . ' ' . translate('not_started_yet') : translate('exam') . ' ' . translate('expired'); ?></p>
        </div>
<?php }
} elseif ($exam->publish_result == 1) { ?>
        <div class="text-center">
            <i class="fas fa-chart-line fa-3x text-success"></i>
            <p class="text-success"><?php echo translate('exam_result') . ' ' . translate('published'); ?>.</p>
        </div>
<?php } else { ?>
        <div class="text-center">
            <i class="fas fa-check-circle fa-3x text-warning"></i>
            <p class="text-warning"><?php echo translate('you_have_already_submitted'); ?>.</p>
        </div>
<?php } ?>
    </div>
</section>

<div id="ans_modalBox" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fas fa-users-between-lines"></i> <?php echo html_escape($exam->title); ?></h4>
            </div>
            <div class="modal-body">
                <div id="online_questions"></div>
            </div>
        </div>
    </div>
</div>
