<div class="row">
	<div class="col-md-12">
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body">
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
				<a href="<?=base_url('digital_diary')?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
			</div>
		</section>
		<?php endif; ?>

		<section class="panel">
			<?php echo form_open($this->uri->uri_string(), array('class' => 'form-bordered form-horizontal frm-submit-data'));?>
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-pen-nib"></i> <?=translate('edit')?> <?=isset($this->lang->language['diary_entry']) ? translate('diary_entry') : 'Diary Entry'?></h4>
				<div class="panel-btn">
					<a href="<?=base_url('digital_diary') . (is_superadmin_loggedin() && !empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '')?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body mb-md">
				<div class="mt-md"></div>
				<?php if (is_superadmin_loggedin()): ?>
				<div class="form-group">
					<label class="control-label col-md-3"><?=translate('branch')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown("branch_id", $arrayBranch, $diary['branch_id'], "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('class')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							$arrayClass = $this->app_lib->getClass($diary['branch_id']);
							echo form_dropdown("class_id", $arrayClass, $diary['class_id'], "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('section')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							$arraySection = $this->app_lib->getSections($diary['class_id'], true);
							echo form_dropdown("section_id", $arraySection, $diary['section_id'], "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=isset($this->lang->language['entry_date']) ? translate('entry_date') : 'Entry Date'?> <span class="required">*</span></label>
					<div class="col-md-6">
						<div class="input-group">
							<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
							<input type="text" class="form-control" name="entry_date" value="<?=set_value('entry_date', $diary['entry_date'])?>" autocomplete="off" data-plugin-datepicker data-plugin-options='{ "todayHighlight" : true }' />
						</div>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=isset($this->lang->language['entry_title']) ? translate('entry_title') : 'Entry Title'?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="entry_title" value="<?=set_value('entry_title', $diary['title'])?>" placeholder="<?=isset($this->lang->language['entry_title']) ? translate('entry_title') : 'Entry Title'?>">
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=isset($this->lang->language['description']) ? translate('description') : 'Description'?></label>
					<div class="col-md-6">
						<textarea class="form-control" name="description" rows="5" placeholder="<?=isset($this->lang->language['description']) ? translate('description') : 'Description'?>"><?=set_value('description', $diary['description'])?></textarea>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=isset($this->lang->language['status']) ? translate('status') : 'Status'?></label>
					<div class="col-md-6">
						<?php
							$status_options = array('0' => (isset($this->lang->language['draft']) ? translate('draft') : 'Draft'), '1' => translate('published'));
							echo form_dropdown("status", $status_options, set_value('status', $diary['status']), "class='form-control' data-plugin-selectTwo data-width='100%'");
						?>
						<span class="error"></span>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
						<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><?=translate('update')?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close();?>
		</section>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('#branch_id').on('change', function() {
			var branchID = $(this).val();
			getClassByBranch(branchID);
		});
	});
</script>
