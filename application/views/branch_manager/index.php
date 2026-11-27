<section class="panel">
	<header class="panel-heading">
		<div class="panel-btn">
			<a href="<?=base_url('branch_manager/form')?>" class="btn btn-default btn-circle"><i class="fas fa-plus-circle"></i> <?=translate('create_branch')?></a>
		</div>
		<h4 class="panel-title"><i class="fas fa-code-branch"></i> <?=translate('branch_management')?></h4>
	</header>
	<div class="panel-body">
		<?php echo form_open('branch_manager', array('method' => 'get', 'class' => 'form-inline mb-md')); ?>
			<input type="text" name="q" class="form-control" placeholder="<?=translate('search')?>" value="<?=html_escape($filters['q'])?>">
			<select name="school_id" class="form-control">
				<option value=""><?=translate('all')?> <?=translate('school')?></option>
				<?php foreach ($schools as $s): ?><option value="<?=$s->id?>" <?=$filters['school_id'] == $s->id ? 'selected' : ''?>><?=html_escape($s->name)?></option><?php endforeach; ?>
			</select>
			<select name="status" class="form-control">
				<option value=""><?=translate('all')?> <?=translate('status')?></option>
				<option value="1" <?=$filters['status'] === '1' ? 'selected' : ''?>>Active</option>
				<option value="0" <?=$filters['status'] === '0' ? 'selected' : ''?>>Inactive</option>
			</select>
			<button class="btn btn-default" type="submit"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
		<?php echo form_close(); ?>
		<div class="table-responsive">
		<table class="table table-bordered table-hover table-condensed">
			<thead>
				<tr>
					<th>#</th><th><?=translate('branch')?></th><th><?=translate('school')?></th><th><?=translate('city')?></th>
					<th><?=translate('status')?></th><th>Created</th><th>Last status change</th><th class="no-sort"><?=translate('action')?></th>
				</tr>
			</thead>
			<tbody>
			<?php $i = 1; foreach ($branches as $b): ?>
				<tr>
					<td><?=$i++?></td>
					<td><?=html_escape($b->name)?></td>
					<td><?=html_escape($b->school_label ?: $b->school_name)?></td>
					<td><?=html_escape($b->city)?></td>
					<td><?=$b->status ? '<span class="label label-success-custom">Active</span>' : '<span class="label label-danger-custom">Inactive</span>'?></td>
					<td><?=date('d M Y', strtotime($b->created_at))?></td>
					<td><?=$b->status_changed_at ? date('d M Y H:i', strtotime($b->status_changed_at)) . ' <small class="text-muted">' . html_escape($b->changed_by_name) . '</small>' : '-'?></td>
					<td class="action">
						<a href="<?=base_url('branch_manager/form/' . $b->id)?>" class="btn btn-default btn-circle icon" title="<?=translate('edit')?>"><i class="fas fa-pen-nib"></i></a>
						<?php echo form_open('branch_manager/set_status', array('style' => 'display:inline')); ?>
							<input type="hidden" name="id" value="<?=(int) $b->id?>">
							<input type="hidden" name="status" value="<?=$b->status ? 0 : 1?>">
							<button type="submit" class="btn btn-default btn-circle icon" title="<?=$b->status ? 'Deactivate' : 'Activate'?>"
							<?=$b->status ? 'onclick="return confirm(\'Deactivate this branch? Its users lose access until it is reactivated. No data is deleted.\');"' : ''?>>
								<i class="fas fa-<?=$b->status ? 'toggle-on text-success' : 'toggle-off text-danger'?>"></i>
							</button>
						<?php echo form_close(); ?>
						<?php if (is_admin_loggedin() && $b->status && $b->id != get_loggedin_branch_id()): ?>
						<?php echo form_open('branch_manager/switch_branch', array('style' => 'display:inline')); ?>
							<input type="hidden" name="id" value="<?=(int) $b->id?>">
							<button type="submit" class="btn btn-default btn-circle icon" title="Work in this branch"><i class="fas fa-sign-in-alt"></i></button>
						<?php echo form_close(); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; if (!$branches): ?>
				<tr><td colspan="8" class="text-muted">No branches found.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
		</div>
	</div>
</section>
