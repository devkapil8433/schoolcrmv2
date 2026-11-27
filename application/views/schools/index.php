<section class="panel">
	<header class="panel-heading">
		<div class="panel-btn">
			<a href="<?=base_url('schools/edit')?>" class="btn btn-default btn-circle"><i class="fas fa-plus-circle"></i> <?=translate('add')?> <?=translate('school')?></a>
		</div>
		<h4 class="panel-title"><i class="fas fa-school"></i> <?=translate('school_management')?></h4>
	</header>
	<div class="panel-body">
		<table class="table table-bordered table-hover table-condensed table-export">
			<thead>
				<tr>
					<th>#</th>
					<th><?=translate('school')?></th>
					<th><?=translate('code')?></th>
					<th><?=translate('branch')?></th>
					<th><?=translate('subscription')?></th>
					<th><?=translate('expiry_date')?></th>
					<th><?=translate('status')?></th>
					<th class="no-sort"><?=translate('action')?></th>
				</tr>
			</thead>
			<tbody>
				<?php $i = 1; foreach ($schools as $row): ?>
				<tr>
					<td><?=$i++?></td>
					<td><?=html_escape($row['name'])?></td>
					<td><?=html_escape($row['code'])?></td>
					<td><?=(int) $row['active_branch_count']?> / <?=(int) $row['branch_count']?></td>
					<td><?=subscription_badge($row['st']['state'])?></td>
					<td><?=empty($row['expiry_date']) ? '-' : date('d M Y', strtotime($row['expiry_date']))?></td>
					<td><?=$row['status'] ? '<span class="label label-success-custom">Active</span>' : '<span class="label label-danger-custom">Inactive</span>'?></td>
					<td class="action">
						<a href="<?=base_url('schools/edit/' . $row['id'])?>" class="btn btn-default btn-circle icon" title="<?=translate('edit')?>"><i class="fas fa-pen-nib"></i></a>
						<a href="<?=base_url('subscriptions/view/' . $row['id'])?>" class="btn btn-default btn-circle icon" title="<?=translate('subscription')?>"><i class="fas fa-receipt"></i></a>
						<a href="<?=base_url('branch_manager?school_id=' . $row['id'])?>" class="btn btn-default btn-circle icon" title="<?=translate('branch')?>"><i class="fas fa-code-branch"></i></a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
