<?php
$groups = array(
	array('Expiring within ' . (int) $days . ' days', $expiring, 'label-warning-custom'),
	array('In grace period (warning shown, access still allowed)', $grace, 'label-warning-custom'),
	array('Expired (access blocked)', $expired, 'label-danger-custom'),
	array('Suspended (access blocked)', $suspended, 'label-danger-custom'),
);
?>
<section class="panel">
	<header class="panel-heading"><h4 class="panel-title"><i class="fas fa-hourglass-half"></i> <?=translate('expiring_and_expired_subscriptions')?></h4></header>
	<div class="panel-body">
		<?php echo form_open('subscriptions/expiring', array('method' => 'get', 'class' => 'form-inline')); ?>
			<label>Show subscriptions expiring within</label>
			<input type="number" name="days" min="1" max="365" value="<?=(int) $days?>" class="form-control" style="width:90px"> days
			<button class="btn btn-default" type="submit"><?=translate('filter')?></button>
		<?php echo form_close(); ?>
	</div>
</section>
<?php foreach ($groups as $g): ?>
<section class="panel">
	<header class="panel-heading"><h4 class="panel-title"><?=$g[0]?> <span class="label <?=$g[2]?>"><?=count($g[1])?></span></h4></header>
	<div class="panel-body">
		<table class="table table-bordered table-condensed">
			<thead><tr><th><?=translate('school')?></th><th><?=translate('expiry_date')?></th><th>Days left</th><th>Grace ends</th><th>Payment</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($g[1] as $r): ?>
				<tr>
					<td><?=html_escape($r['name'])?></td>
					<td><?=date('d M Y', strtotime($r['expiry_date']))?></td>
					<td><?=$r['st']['days_left']?></td>
					<td><?=date('d M Y', strtotime($r['st']['grace_end']))?></td>
					<td><?=$r['payment_status'] === 'paid' ? 'Paid' : 'Unpaid'?></td>
					<td><a class="btn btn-default btn-xs" href="<?=base_url('subscriptions/view/' . $r['id'])?>"><?=translate('manage')?></a></td>
				</tr>
			<?php endforeach; if (!$g[1]): ?>
				<tr><td colspan="6" class="text-muted">None</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</section>
<?php endforeach; ?>
