<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-receipt"></i> <?=translate('subscription_management')?></h4>
	</header>
	<div class="panel-body">
		<?php echo form_open('subscriptions', array('method' => 'get', 'class' => 'form-inline mb-md')); ?>
			<input type="text" name="q" class="form-control" placeholder="<?=translate('search')?>" value="<?=html_escape($q)?>">
			<select name="state" class="form-control">
				<option value=""><?=translate('all')?> <?=translate('status')?></option>
				<?php foreach (array('active' => 'Active', 'grace' => 'Grace Period', 'expired' => 'Expired', 'suspended' => 'Suspended', 'none' => 'No Subscription') as $k => $label): ?>
				<option value="<?=$k?>" <?=$state === $k ? 'selected' : ''?>><?=$label?></option>
				<?php endforeach; ?>
			</select>
			<button class="btn btn-default" type="submit"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
		<?php echo form_close(); ?>
		<table class="table table-bordered table-hover table-condensed table-export">
			<thead>
				<tr>
					<th>#</th><th><?=translate('school')?></th><th><?=translate('plan')?></th><th><?=translate('amount')?></th>
					<th><?=translate('start_date')?></th><th><?=translate('expiry_date')?></th><th>Days left</th>
					<th><?=translate('status')?></th><th>Payment</th><th class="no-sort"><?=translate('action')?></th>
				</tr>
			</thead>
			<tbody>
				<?php $i = 1; foreach ($rows as $r): ?>
				<tr>
					<td><?=$i++?></td>
					<td><?=html_escape($r['name'])?></td>
					<td><?=html_escape($r['plan_name'])?></td>
					<td><?=isset($r['amount']) ? number_format((float) $r['amount'], 2) : '-'?></td>
					<td><?=empty($r['start_date']) ? '-' : date('d M Y', strtotime($r['start_date']))?></td>
					<td><?=empty($r['expiry_date']) ? '-' : date('d M Y', strtotime($r['expiry_date']))?></td>
					<td><?=$r['st']['days_left'] === null ? '-' : $r['st']['days_left']?></td>
					<td><?=subscription_badge($r['st']['state'])?></td>
					<td><?=empty($r['payment_status']) ? '-' : ($r['payment_status'] === 'paid' ? '<span class="label label-success-custom">Paid</span>' : '<span class="label label-warning-custom">Unpaid</span>')?></td>
					<td class="action">
						<a href="<?=base_url('subscriptions/view/' . $r['id'])?>" class="btn btn-default btn-circle icon" title="<?=translate('view')?>"><i class="fas fa-eye"></i></a>
						<a href="<?=base_url('subscriptions/edit/' . $r['id'])?>" class="btn btn-default btn-circle icon" title="<?=translate('edit')?>"><i class="fas fa-pen-nib"></i></a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
