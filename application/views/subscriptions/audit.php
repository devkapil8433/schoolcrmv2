<section class="panel">
	<header class="panel-heading"><h4 class="panel-title"><i class="fas fa-clipboard-list"></i> <?=translate('audit_log')?></h4></header>
	<div class="panel-body">
		<?php echo form_open('subscriptions/audit', array('method' => 'get', 'class' => 'form-inline mb-md')); ?>
			<select name="school_id" class="form-control">
				<option value=""><?=translate('all')?> <?=translate('school')?></option>
				<?php foreach ($schools as $s): ?><option value="<?=$s->id?>" <?=$filters['school_id'] == $s->id ? 'selected' : ''?>><?=html_escape($s->name)?></option><?php endforeach; ?>
			</select>
			<select name="entity" class="form-control">
				<option value="">All types</option>
				<?php foreach (array('school', 'branch', 'subscription', 'payment', 'settings', 'school_admin') as $e): ?><option value="<?=$e?>" <?=$filters['entity'] === $e ? 'selected' : ''?>><?=$e?></option><?php endforeach; ?>
			</select>
			<button class="btn btn-default" type="submit"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
		<?php echo form_close(); ?>
		<table class="table table-bordered table-condensed table-hover">
			<thead><tr><th><?=translate('date')?></th><th><?=translate('school')?></th><th>Action</th><th>Entity</th><th>By</th><th>IP</th><th>Before</th><th>After</th></tr></thead>
			<tbody>
			<?php foreach ($logs as $l): ?>
				<tr>
					<td><?=$l->created_at?></td>
					<td><?=html_escape($l->school_name)?></td>
					<td><?=html_escape($l->action)?></td>
					<td><?=html_escape($l->entity_type . ' #' . $l->entity_id)?></td>
					<td><?=html_escape($l->actor_name)?></td>
					<td><?=html_escape($l->ip_address)?></td>
					<td><small><?=html_escape($l->old_value)?></small></td>
					<td><small><?=html_escape($l->new_value)?></small></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
