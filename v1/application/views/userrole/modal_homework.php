<?php
if ($homework->ev_status == 'u' || $homework->ev_status == '') {
    if (empty($homework->hs_id) || $homework->ev_status == 'u') {
        $statusBadge = 'label label-danger-custom';
        $statusText = translate('incomplete');
    } else {
        $statusBadge = 'label label-info-custom';
        $statusText = translate('submitted');
    }
} else {
    $statusText = translate('complete');
    $statusBadge = 'label label-success-custom';
}
$date_of_submission = strtotime($homework->date_of_submission);
$today = strtotime(date('Y-m-d'));
?>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-book-open"></i> <?php echo translate('homework') . ' ' . translate('details'); ?></h4>
	</header>
	<div class="panel-body">
		<?php echo $homework->description; ?>
	</div>
</section>

<?php if ($homework->ev_status !== 'c' && $date_of_submission >= $today) { ?>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-upload"></i> <?php echo translate('submit') . ' ' . translate('assignment'); ?></h4>
	</header>
	<div class="panel-body">
		<?php echo form_open_multipart('userrole/assignment_upload', array('class' => 'frm-assigment')); ?>
		<input type="hidden" name="homework_id" value="<?php echo $homework->id ?>">
		<input type="hidden" name="assigment_id" value="<?php echo $homework->hs_id ?>">
		<div class="form-group">
			<label class="control-label"><?php echo translate('attachment_file') ?></label>
			<div class="fileupload fileupload-new" data-provides="fileupload">
				<div class="input-append">
					<div class="uneditable-input">
						<i class="fas fa-file fileupload-exists"></i>
						<span class="fileupload-preview"></span>
					</div>
					<span class="btn btn-default btn-file">
						<span class="fileupload-exists"><?= translate('change') ?></span>
						<span class="fileupload-new"><?= translate('select') ?></span>
						<input type="file" name="attachment_file" value="" />
					</span>
					<a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload"><?= translate('remove') ?></a>
				</div>
			</div>
			<span class="error"></span>
			<input type="hidden" name="old_file" value="<?php echo $homework->enc_name ?>">
		</div>
		<div class="form-group">
			<label class="control-label"><?php echo translate('message') ?> <span class="required">*</span></label>
			<textarea name="message" rows="4" autocomplete="off" class="form-control"><?php echo $homework->message ?></textarea>
			<span class="error"></span>
		</div>
		<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><?php echo empty($homework->hs_id) ? translate('submit') : translate('re_submit'); ?></button>
		<?php echo form_close(); ?>
	</div>
</section>
<?php } ?>

<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-circle-info"></i> <?= translate('details') ?></h4>
	</header>
	<div class="panel-body">
		<table class="table table-bordered table-condensed mb-none">
			<tbody>
				<tr><th><?= translate('date_of_homework') ?></th><td><?= _d($homework->date_of_homework) ?></td></tr>
				<tr><th><?= translate('date_of_submission') ?></th><td><?= _d($homework->date_of_submission) ?></td></tr>
				<tr><th><?= translate('evaluation_date') ?></th><td><?= $homework->evaluation_date != null ? _d($homework->evaluation_date) : 'N/A' ?></td></tr>
				<tr><th><?= translate('created_by') ?></th><td><?= $homework->created_by != null ? get_type_name_by_id('staff', $homework->created_by) : 'N/A' ?></td></tr>
				<tr><th><?= translate('status') ?></th><td><span class="<?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td></tr>
				<tr><th><?= translate('evaluated_by') ?></th><td><?= !empty($homework->evaluated_by) ? get_type_name_by_id('staff', $homework->evaluated_by) : 'N/A' ?></td></tr>
				<tr><th><?= translate('rank_out_of_5') ?></th><td><?= !empty($homework->rank) ? $homework->rank : 'N/A' ?></td></tr>
				<tr><th><?= translate('remarks') ?></th><td><?= !empty($homework->ev_remarks) ? html_escape($homework->ev_remarks) : 'N/A' ?></td></tr>
				<tr><th><?= translate('subject') ?></th><td><?= html_escape($homework->subject_name) ?></td></tr>
				<tr><th><?= translate('class') ?></th><td><?= html_escape($homework->class_name) ?></td></tr>
				<tr><th><?= translate('section') ?></th><td><?= html_escape($homework->section_name) ?></td></tr>
<?php if (!empty($homework->document)) { ?>
				<tr><th><?= translate('documents') ?></th><td><a href="<?= base_url('homework/download/' . $homework->id) ?>" class="btn btn-default btn-sm"><i class="fas fa-cloud-download-alt"></i> <?= translate('download') ?></a></td></tr>
<?php } ?>
<?php if (!empty($homework->enc_name)) { ?>
				<tr><th><?= translate('submitted_file') ?></th><td><a href="<?= base_url('homework/download_submitted?file=' . $homework->enc_name) ?>" class="btn btn-default btn-sm"><i class="fas fa-cloud-download-alt"></i> <?= translate('download') ?></a></td></tr>
<?php } ?>
			</tbody>
		</table>
	</div>
</section>

<script type="text/javascript">
	$("form.frm-assigment").on('submit', function(e){
		e.preventDefault();
		var $this = $(this);
		var btn = $this.find('[type="submit"]');
		$.ajax({
			url: $(this).attr('action'),
			type: "POST",
			data: new FormData(this),
			dataType: "json",
			contentType: false,
			processData: false,
			cache: false,
			beforeSend: function () {
				btn.button('loading');
			},
			success: function (data) {
				$('.error').html("");
				if (data.status == "fail") {
					$.each(data.error, function (index, value) {
						$this.find("[name='" + index + "']").parents('.form-group').find('.error').html(value);
					});
					btn.button('reset');
				} else if (data.status == "access_denied") {
					window.location.href = base_url + "dashboard";
				} else {
					location.reload(true);
				}
			},
			error: function () {
				btn.button('reset');
			}
		});
	});
</script>
