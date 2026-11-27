<?php $isNew = empty($sub); ?>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-receipt"></i> <?=html_escape($school->name)?> - <?=$isNew ? translate('add') : translate('edit')?> <?=translate('subscription')?></h4>
	</header>
	<?php echo form_open(base_url('subscriptions/edit/' . $school->id), array('class' => 'form-horizontal form-bordered')); ?>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('plan')?></label>
			<div class="col-md-6">
				<input type="text" class="form-control" name="plan_name" value="<?=html_escape(set_value('plan_name', $isNew ? '' : $sub->plan_name))?>">
				<span class="error"><?=form_error('plan_name')?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('amount')?> <span class="required">*</span></label>
			<div class="col-md-6">
				<input type="text" class="form-control" name="amount" value="<?=html_escape(set_value('amount', $isNew ? '0.00' : $sub->amount))?>">
				<span class="error"><?=form_error('amount')?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('start_date')?> <span class="required">*</span></label>
			<div class="col-md-6">
				<input type="date" class="form-control" name="start_date" value="<?=html_escape(set_value('start_date', $isNew ? date('Y-m-d') : $sub->start_date))?>">
				<span class="error"><?=form_error('start_date')?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('expiry_date')?> <span class="required">*</span></label>
			<div class="col-md-6">
				<input type="date" class="form-control" name="expiry_date" value="<?=html_escape(set_value('expiry_date', $isNew ? date('Y-m-d', strtotime('+1 year')) : $sub->expiry_date))?>">
				<span class="error"><?=form_error('expiry_date')?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('grace_period')?> (days)</label>
			<div class="col-md-6">
				<input type="number" min="0" max="90" class="form-control" name="grace_days" value="<?=html_escape(set_value('grace_days', $isNew ? '' : $sub->grace_days))?>" placeholder="Platform default: <?=(int) $settings->default_grace_days?>">
				<p class="help-block">Leave empty to use the platform default (<?=(int) $settings->default_grace_days?> days). Common values: 0, 3, 7, 15.</p>
				<span class="error"><?=form_error('grace_days')?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('notes')?></label>
			<div class="col-md-6">
				<textarea class="form-control" name="notes" rows="3"><?=html_escape(set_value('notes', $isNew ? '' : $sub->notes))?></textarea>
			</div>
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-offset-3 col-md-6">
				<button type="submit" name="save" value="1" class="btn btn-default"><i class="fas fa-plus-circle"></i> <?=translate('save')?></button>
				<a href="<?=base_url('subscriptions/view/' . $school->id)?>" class="btn btn-default"><?=translate('cancel')?></a>
			</div>
		</div>
	</footer>
	<?php echo form_close(); ?>
</section>
