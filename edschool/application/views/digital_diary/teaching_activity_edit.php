<?php $teaching_activity = isset($teaching_activity) ? $teaching_activity : array(); ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<?php echo form_open(base_url('digital_diary/teaching_activity_edit/' . (isset($teaching_activity['id']) ? $teaching_activity['id'] : '')), array('class' => 'form-bordered form-horizontal frm-submit-data')); ?>
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-pen-nib"></i> Edit Teaching Activity</h4>
				<div class="panel-btn">
					<a href="<?=base_url('digital_diary/teaching_activity_types') . (is_superadmin_loggedin() && !empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '')?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body mb-md">
				<p class="text-muted">Update this activity type. <?=translate('teacher')?> assignment is not done here.</p>
				<?php if (is_superadmin_loggedin()): ?>
				<div class="form-group">
					<label class="control-label col-md-3"><?=translate('branch')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							$selBranch = set_value('branch_id', isset($teaching_activity['branch_id']) ? $teaching_activity['branch_id'] : (isset($branch_id) ? $branch_id : ''));
							echo form_dropdown('branch_id', $arrayBranch, $selBranch, "class='form-control' id='branch_id' required onchange='teachingActivityBranchChange(this.value)' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<label class="col-md-3 control-label">Activity type <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" name="name" class="form-control" value="<?=set_value('name', isset($teaching_activity['name']) ? $teaching_activity['name'] : '')?>" placeholder="e.g. Lecture, Discussion, Practical">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Note</label>
					<div class="col-md-6">
						<input type="text" name="code" class="form-control" value="<?=set_value('code', isset($teaching_activity['code']) ? $teaching_activity['code'] : '')?>" placeholder="Short note (optional)">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Notebook Type</label>
					<div class="col-md-6">
						<?php
							$nbTypes = isset($notebook_types_multiselect) ? $notebook_types_multiselect : array();
							$selNb = array();
							if ($this->input->post('notebook_type_ids')) {
								$selNb = array_map('intval', (array) $this->input->post('notebook_type_ids'));
							} elseif (isset($teaching_activity['notebook_type_ids']) && trim((string) $teaching_activity['notebook_type_ids']) !== '') {
								$selNb = array_map('intval', explode(',', $teaching_activity['notebook_type_ids']));
							} elseif (!empty($teaching_activity['notebook_type_id'])) {
								$selNb = array((int) $teaching_activity['notebook_type_id']);
							}
							echo form_dropdown('notebook_type_ids[]', $nbTypes, $selNb, "class='form-control' id='notebook_type_ids' data-plugin-selectTwo data-width='100%' multiple");
						?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('remarks')?></label>
					<div class="col-md-6">
						<textarea name="description" class="form-control" rows="3" placeholder=""><?=set_value('description', isset($teaching_activity['description']) ? $teaching_activity['description'] : '')?></textarea>
						<span class="error"></span>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><?=translate('update')?></button>
			</footer>
			<?php echo form_close(); ?>
		</section>
	</div>
</div>
<?php if (is_superadmin_loggedin()): ?>
<script type="text/javascript">
	function teachingActivityBranchChange(branchID) {
		branchID = branchID || '';
		var optFirst = <?=json_encode('<option value="">' . translate('select_branch_first') . '</option>')?>;
		if (branchID !== '') {
			$.ajax({
				url: base_url + 'digital_diary/ajax_notebook_types_options',
				type: 'POST',
				data: { branch_id: branchID, multi: 1 },
				success: function (html) {
					var $nb = $('#notebook_type_ids');
					if ($nb.hasClass('select2-hidden-accessible')) {
						$nb.select2('destroy');
					}
					$nb.removeData('__select2');
					$nb.html(html);
					if ($.fn.themePluginSelect2) {
						$nb.themePluginSelect2({});
					} else {
						$nb.select2({ width: '100%' });
					}
				}
			});
		} else {
			var $nb = $('#notebook_type_ids');
			if ($nb.hasClass('select2-hidden-accessible')) {
				$nb.select2('destroy');
			}
			$nb.removeData('__select2');
			$nb.html(optFirst);
			if ($.fn.themePluginSelect2) {
				$nb.themePluginSelect2({});
			} else {
				$nb.select2({ width: '100%' });
			}
		}
	}
</script>
<?php endif; ?>
