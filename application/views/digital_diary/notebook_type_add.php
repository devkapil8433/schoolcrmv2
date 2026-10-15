<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<?php echo form_open(base_url('digital_diary/notebook_type_add'), array('class' => 'form-bordered form-horizontal frm-submit-data')); ?>
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-plus-circle"></i> Add Note Book Type</h4>
				<div class="panel-btn">
					<a href="<?=base_url('digital_diary/notebook_teaching_activity') . (is_superadmin_loggedin() && !empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '')?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body mb-md">
				<?php if (is_superadmin_loggedin()): ?>
				<div class="form-group">
					<label class="control-label col-md-3"><?=translate('branch')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<label class="col-md-3 control-label">Name <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" name="name" class="form-control" value="<?=set_value('name')?>" placeholder="e.g. Class Work, Home Work, Practical">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Code</label>
					<div class="col-md-6">
						<input type="text" name="code" class="form-control" value="<?=set_value('code')?>" placeholder="Short code (optional)">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Description</label>
					<div class="col-md-6">
						<textarea name="description" class="form-control" rows="3" placeholder="Optional description"><?=set_value('description')?></textarea>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Sort Order</label>
					<div class="col-md-6">
						<input type="number" name="sort_order" class="form-control" value="<?=set_value('sort_order', 0)?>" min="0">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Status</label>
					<div class="col-md-6">
						<?php
							$options = array(1 => translate('active'), 0 => (isset($this->lang->language['inactive']) ? translate('inactive') : 'Inactive'));
							echo form_dropdown('is_active', $options, set_value('is_active', 1), "class='form-control' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
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
