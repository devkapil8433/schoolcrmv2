<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<?php echo form_open(base_url('digital_diary/teaching_activity_add'), array('class' => 'form-bordered form-horizontal frm-submit-data')); ?>
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-plus-circle"></i> Add Teaching Activity</h4>
				<div class="panel-btn">
					<a href="<?=base_url('digital_diary/teaching_activity_types') . (is_superadmin_loggedin() && !empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '')?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body mb-md">
				<p class="text-muted">Create an activity type only. <?=translate('teacher')?> assignment is not done here.</p>
				<?php if (is_superadmin_loggedin()): ?>
				<div class="form-group">
					<label class="control-label col-md-3"><?=translate('branch')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							$selBranch = set_value('branch_id', isset($branch_id) ? $branch_id : '');
							echo form_dropdown('branch_id', $arrayBranch, $selBranch, "class='form-control' id='branch_id' required onchange='teachingActivityBranchChange(this.value)' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<label class="col-md-3 control-label">Activity type <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" name="name" class="form-control" value="<?=set_value('name')?>" placeholder="e.g. Lecture, Discussion, Practical">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Note</label>
					<div class="col-md-6">
						<input type="text" name="code" class="form-control" value="<?=set_value('code')?>" placeholder="Short note (optional)">
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
							}
							echo form_dropdown('notebook_type_ids[]', $nbTypes, $selNb, "class='form-control' id='notebook_type_ids' data-plugin-selectTwo data-width='100%' multiple");
						?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('remarks')?></label>
					<div class="col-md-6">
						<textarea name="description" class="form-control" rows="3" placeholder=""><?=set_value('description')?></textarea>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Coordinator</label>
					<div class="col-md-6">
						<?php
							$enCoordAdd = $this->input->post() ? ($this->input->post('enable_coordinator') ? 1 : 0) : 1;
						?>
						<input type="hidden" name="enable_coordinator" value="0">
						<label class="checkbox-inline">
							<div class="material-switch ml-xs">
								<input id="enable_coordinator" name="enable_coordinator" value="1" type="checkbox"<?= $enCoordAdd ? ' checked' : '' ?>>
								<label for="enable_coordinator" class="label-primary"></label>
							</div>
							<span style="margin-left:8px;">Coordinator</span>
						</label>
						<p class="help-block mb-none">Uncheck to disable coordinator for this activity type.</p>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
						<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><?=translate('save')?></button>
					</div>
				</div>
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
