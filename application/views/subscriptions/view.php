<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<div class="panel-btn">
					<a href="<?=base_url('subscriptions/edit/' . $school->id)?>" class="btn btn-default btn-circle"><i class="fas fa-pen-nib"></i> <?=$sub ? translate('edit') : translate('add')?> <?=translate('subscription')?></a>
				</div>
				<h4 class="panel-title"><i class="fas fa-school"></i> <?=html_escape($school->name)?> <?=subscription_badge($st['state'])?></h4>
			</header>
			<div class="panel-body">
			<?php if (!$sub): ?>
				<p class="text-muted">This school has no subscription yet. Its users cannot access protected modules until one is created.</p>
			<?php else: ?>
				<div class="row">
					<div class="col-md-3"><strong><?=translate('plan')?>:</strong> <?=html_escape($sub->plan_name ?: '-')?></div>
					<div class="col-md-3"><strong><?=translate('amount')?>:</strong> <?=number_format((float) $sub->amount, 2)?></div>
					<div class="col-md-3"><strong><?=translate('start_date')?>:</strong> <?=date('d M Y', strtotime($sub->start_date))?></div>
					<div class="col-md-3"><strong><?=translate('expiry_date')?>:</strong> <?=date('d M Y', strtotime($sub->expiry_date))?> (<?=$st['days_left']?> days)</div>
				</div>
				<div class="row mt-sm">
					<div class="col-md-3"><strong>Grace period:</strong> <?=(int) $st['grace_days']?> days (until <?=date('d M Y', strtotime($st['grace_end']))?>)</div>
					<div class="col-md-3"><strong>Payment:</strong> <?=$sub->payment_status === 'paid' ? '<span class="label label-success-custom">Paid</span>' : '<span class="label label-warning-custom">Unpaid</span>'?></div>
					<div class="col-md-6"><strong><?=translate('notes')?>:</strong> <?=html_escape($sub->notes)?></div>
				</div>
			<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<?php if ($sub): ?>
<div class="row">
	<div class="col-md-4">
		<section class="panel">
			<header class="panel-heading"><h4 class="panel-title">Access control</h4></header>
			<div class="panel-body">
				<?php echo form_open('subscriptions/set_status'); ?>
					<input type="hidden" name="subscription_id" value="<?=(int) $sub->id?>">
					<?php if ($sub->admin_status === 'active'): ?>
						<input type="hidden" name="status" value="suspended">
						<button class="btn btn-danger btn-block" type="submit" onclick="return confirm('Suspend this school? All its users lose access immediately (data is kept).');"><i class="fas fa-pause-circle"></i> Suspend subscription</button>
					<?php else: ?>
						<input type="hidden" name="status" value="active">
						<button class="btn btn-success btn-block" type="submit"><i class="fas fa-play-circle"></i> Activate subscription</button>
					<?php endif; ?>
				<?php echo form_close(); ?>
				<hr>
				<?php echo form_open('subscriptions/set_payment_status', array('class' => 'form-inline')); ?>
					<input type="hidden" name="subscription_id" value="<?=(int) $sub->id?>">
					<input type="hidden" name="payment_status" value="<?=$sub->payment_status === 'paid' ? 'unpaid' : 'paid'?>">
					<button class="btn btn-default btn-block" type="submit">Mark as <?=$sub->payment_status === 'paid' ? 'UNPAID' : 'PAID'?></button>
				<?php echo form_close(); ?>
			</div>
		</section>

		<section class="panel">
			<header class="panel-heading"><h4 class="panel-title">Extend expiry</h4></header>
			<?php echo form_open('subscriptions/extend'); ?>
			<div class="panel-body">
				<input type="hidden" name="subscription_id" value="<?=(int) $sub->id?>">
				<div class="form-group">
					<label class="control-label">Add days to current expiry</label>
					<select name="days" class="form-control">
						<option value="">-- or pick an exact date below --</option>
						<?php foreach (array(30, 90, 180, 365) as $d): ?><option value="<?=$d?>">+ <?=$d?> days</option><?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">New expiry date</label>
					<input type="date" name="new_expiry" class="form-control">
				</div>
				<div class="form-group">
					<label class="control-label"><?=translate('note')?></label>
					<input type="text" name="note" class="form-control" maxlength="200">
				</div>
				<button class="btn btn-default" type="submit"><i class="fas fa-calendar-plus"></i> Extend</button>
			</div>
			<?php echo form_close(); ?>
		</section>
	</div>

	<div class="col-md-8">
		<section class="panel">
			<header class="panel-heading"><h4 class="panel-title">Record manual payment</h4></header>
			<?php echo form_open('subscriptions/add_payment', array('class' => 'form-horizontal')); ?>
			<div class="panel-body">
				<input type="hidden" name="subscription_id" value="<?=(int) $sub->id?>">
				<div class="row">
					<div class="col-md-3"><label class="control-label"><?=translate('amount')?> *</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" required></div>
					<div class="col-md-3"><label class="control-label">Payment date *</label><input type="date" name="payment_date" value="<?=date('Y-m-d')?>" class="form-control" required></div>
					<div class="col-md-3"><label class="control-label">Method *</label>
						<select name="method" class="form-control"><?php foreach ($methods as $m): ?><option><?=$m?></option><?php endforeach; ?></select></div>
					<div class="col-md-3"><label class="control-label">Reference / Txn no</label><input type="text" name="reference_no" maxlength="100" class="form-control"></div>
				</div>
				<div class="row mt-sm">
					<div class="col-md-4"><label class="control-label"><?=translate('note')?></label><input type="text" name="note" maxlength="255" class="form-control"></div>
					<div class="col-md-4"><label class="control-label">Also extend expiry to (optional)</label><input type="date" name="extend_to" class="form-control"></div>
					<div class="col-md-4"><label class="checkbox-inline mt-lg"><input type="checkbox" name="mark_paid" value="1" checked> Mark subscription as paid</label></div>
				</div>
			</div>
			<footer class="panel-footer"><button class="btn btn-default" type="submit"><i class="fas fa-plus-circle"></i> Save payment</button> <small class="text-muted">Recorded by <?=html_escape($this->session->userdata('name'))?></small></footer>
			<?php echo form_close(); ?>
		</section>

		<section class="panel">
			<header class="panel-heading"><h4 class="panel-title"><?=translate('payment_history')?></h4></header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed">
					<thead><tr><th>#</th><th><?=translate('date')?></th><th><?=translate('amount')?></th><th>Method</th><th>Reference</th><th>Recorded by</th><th><?=translate('status')?></th><th></th></tr></thead>
					<tbody>
					<?php foreach ($payments as $p): ?>
						<tr class="<?=$p->status === 'void' ? 'text-muted' : ''?>">
							<td><?=$p->id?></td>
							<td><?=date('d M Y', strtotime($p->payment_date))?></td>
							<td><?=number_format((float) $p->amount, 2)?></td>
							<td><?=html_escape($p->method)?></td>
							<td><?=html_escape($p->reference_no)?></td>
							<td><?=html_escape($p->recorded_by_name)?></td>
							<td><?=$p->status === 'void' ? '<span class="label label-danger-custom">Void</span> ' . html_escape($p->void_reason) : '<span class="label label-success-custom">Recorded</span>'?></td>
							<td>
							<?php if ($p->status === 'recorded'): ?>
								<?php echo form_open('subscriptions/void_payment', array('class' => 'form-inline', 'onsubmit' => "return (this.reason.value = prompt('Reason for voiding this payment?') || '') !== '';")); ?>
									<input type="hidden" name="payment_id" value="<?=(int) $p->id?>">
									<input type="hidden" name="reason" value="">
									<button class="btn btn-default btn-xs" type="submit">Void</button>
								<?php echo form_close(); ?>
							<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; if (!$payments): ?>
						<tr><td colspan="8" class="text-muted">No payments recorded.</td></tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>
<?php endif; ?>

<section class="panel">
	<header class="panel-heading"><h4 class="panel-title">Subscription &amp; access history</h4></header>
	<div class="panel-body">
		<table class="table table-bordered table-condensed">
			<thead><tr><th><?=translate('date')?></th><th>Action</th><th>By</th><th>Before</th><th>After</th></tr></thead>
			<tbody>
			<?php foreach ($history as $h): ?>
				<tr>
					<td><?=$h->created_at?></td>
					<td><?=html_escape($h->action)?></td>
					<td><?=html_escape($h->actor_name)?></td>
					<td><small><?=html_escape($h->old_value)?></small></td>
					<td><small><?=html_escape($h->new_value)?></small></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
