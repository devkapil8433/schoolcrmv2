<?php $notebook_type = isset($notebook_type) ? $notebook_type : array(); ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<?php echo form_open(base_url('digital_diary/notebook_type_edit/' . (isset($notebook_type['id']) ? $notebook_type['id'] : '')), array('class' => 'form-bordered form-horizontal frm-submit-data')); ?>
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-pen-nib"></i> Edit Note Book Type</h4>
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
							echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', isset($notebook_type['branch_id']) ? $notebook_type['branch_id'] : $branch_id), "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<label class="col-md-3 control-label">Name <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" name="name" class="form-control" value="<?=set_value('name', isset($notebook_type['name']) ? $notebook_type['name'] : '')?>" placeholder="e.g. Class Work, Home Work, Practical">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Code</label>
					<div class="col-md-6">
						<input type="text" name="code" class="form-control" value="<?=set_value('code', isset($notebook_type['code']) ? $notebook_type['code'] : '')?>" placeholder="Short code (optional)">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Description</label>
					<div class="col-md-6">
						<textarea name="description" class="form-control" rows="3" placeholder="Optional description"><?=set_value('description', isset($notebook_type['description']) ? $notebook_type['description'] : '')?></textarea>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Sort Order</label>
					<div class="col-md-6">
						<input type="number" name="sort_order" class="form-control" value="<?=set_value('sort_order', isset($notebook_type['sort_order']) ? $notebook_type['sort_order'] : 0)?>" min="0">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Status</label>
					<div class="col-md-6">
						<?php
							$options = array(1 => translate('active'), 0 => (isset($this->lang->language['inactive']) ? translate('inactive') : 'Inactive'));
							$sel = set_value('is_active', isset($notebook_type['is_active']) ? (int)$notebook_type['is_active'] : 1);
							echo form_dropdown('is_active', $options, $sel, "class='form-control' data-plugin-selectTwo data-width='100%'");
						?>
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
