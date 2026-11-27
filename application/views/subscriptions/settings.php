<section class="panel">
	<header class="panel-heading"><h4 class="panel-title"><i class="fas fa-sliders-h"></i> <?=translate('subscription_settings')?></h4></header>
	<?php echo form_open('subscriptions/settings', array('class' => 'form-horizontal form-bordered')); ?>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-md-3 control-label">Default grace period (days) <span class="required">*</span></label>
			<div class="col-md-6">
				<input type="number" min="0" max="90" name="default_grace_days" class="form-control" value="<?=html_escape(set_value('default_grace_days', $settings->default_grace_days))?>">
				<p class="help-block">After expiry, access continues for this many days (a warning is shown to admins). Typical values: 0, 3, 7, 15. A school can override this on its subscription.</p>
				<span class="error"><?=form_error('default_grace_days')?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label">Show expiry warning (days before) <span class="required">*</span></label>
			<div class="col-md-6">
				<input type="number" min="0" max="90" name="warning_days" class="form-control" value="<?=html_escape(set_value('warning_days', $settings->warning_days))?>">
				<span class="error"><?=form_error('warning_days')?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label">Message shown when blocked <span class="required">*</span></label>
			<div class="col-md-6">
				<textarea name="support_message" rows="3" class="form-control"><?=html_escape(set_value('support_message', $settings->support_message))?></textarea>
				<span class="error"><?=form_error('support_message')?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label">Expiry policy</label>
			<div class="col-md-6"><p class="form-control-static">Expired / suspended schools are blocked server-side after the grace period. School data is never deleted or changed automatically; reactivating the subscription restores access immediately.</p></div>
		</div>
	</div>
	<footer class="panel-footer"><div class="row"><div class="col-md-offset-3 col-md-6"><button class="btn btn-default" type="submit" name="save" value="1"><i class="fas fa-plus-circle"></i> <?=translate('save')?></button></div></div></footer>
	<?php echo form_close(); ?>
</section>
