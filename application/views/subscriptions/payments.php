<section class="panel">
	<header class="panel-heading"><h4 class="panel-title"><i class="fas fa-money-check-alt"></i> <?=translate('payment_history')?></h4></header>
	<div class="panel-body">
		<?php echo form_open('subscriptions/payments', array('method' => 'get', 'class' => 'form-inline mb-md')); ?>
			<select name="school_id" class="form-control">
				<option value=""><?=translate('all')?> <?=translate('school')?></option>
				<?php foreach ($schools as $s): ?><option value="<?=$s->id?>" <?=$filters['school_id'] == $s->id ? 'selected' : ''?>><?=html_escape($s->name)?></option><?php endforeach; ?>
			</select>
			<input type="date" name="from" class="form-control" value="<?=html_escape($filters['from'])?>">
			<input type="date" name="to" class="form-control" value="<?=html_escape($filters['to'])?>">
			<button class="btn btn-default" type="submit"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
		<?php echo form_close(); ?>
		<table class="table table-bordered table-hover table-condensed table-export">
			<thead><tr><th>#</th><th><?=translate('school')?></th><th><?=translate('date')?></th><th><?=translate('amount')?></th><th>Method</th><th>Reference</th><th>Recorded by</th><th><?=translate('status')?></th></tr></thead>
			<tbody>
			<?php foreach ($payments as $p): ?>
				<tr>
					<td><?=$p->id?></td>
					<td><a href="<?=base_url('subscriptions/view/' . $p->school_id)?>"><?=html_escape($p->school_name)?></a></td>
					<td><?=date('d M Y', strtotime($p->payment_date))?></td>
					<td><?=number_format((float) $p->amount, 2)?></td>
					<td><?=html_escape($p->method)?></td>
					<td><?=html_escape($p->reference_no)?></td>
					<td><?=html_escape($p->recorded_by_name)?></td>
					<td><?=$p->status === 'void' ? '<span class="label label-danger-custom">Void</span> ' . html_escape($p->void_reason) : '<span class="label label-success-custom">Recorded</span>'?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
