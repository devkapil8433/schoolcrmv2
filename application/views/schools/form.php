<?php $isNew = empty($school); ?>
<div class="row">
	<div class="col-md-<?=$isNew ? '8' : '6'?>">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-school"></i> <?=$isNew ? translate('add') : translate('edit')?> <?=translate('school')?></h4>
			</header>
			<?php echo form_open(base_url('schools/edit/' . ($isNew ? '' : $school->id)), array('class' => 'form-horizontal form-bordered')); ?>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('school_name')?> <span class="required">*</span></label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="name" value="<?=html_escape(set_value('name', $isNew ? '' : $school->name))?>">
						<span class="error"><?=form_error('name')?></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('code')?></label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="code" value="<?=html_escape(set_value('code', $isNew ? '' : $school->code))?>">
						<span class="error"><?=form_error('code')?></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('email')?></label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="email" value="<?=html_escape(set_value('email', $isNew ? '' : $school->email))?>">
						<span class="error"><?=form_error('email')?></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('mobile_no')?></label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="mobileno" value="<?=html_escape(set_value('mobileno', $isNew ? '' : $school->mobileno))?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('active')?></label>
					<div class="col-md-8">
						<label class="checkbox-inline"><input type="checkbox" name="status" value="1" <?=($isNew || $school->status) ? 'checked' : ''?>> <?=translate('active')?></label>
						<p class="help-block">Inactive school blocks all its branches' users (data is kept).</p>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-3 col-md-8">
						<button type="submit" name="save" value="1" class="btn btn-default"><i class="fas fa-plus-circle"></i> <?=translate('save')?></button>
						<a href="<?=base_url('schools')?>" class="btn btn-default"><?=translate('cancel')?></a>
					</div>
				</div>
			</footer>
			<?php echo form_close(); ?>
		</section>
	</div>

	<?php if (!$isNew): ?>
	<div class="col-md-6">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-user-shield"></i> School Admins</h4>
			</header>
			<div class="panel-body">
				<p class="text-muted">School Admins (role "Admin") can manage every branch of this school. Other admins stay limited to their own branch.</p>
				<table class="table table-bordered table-condensed">
					<thead><tr><th><?=translate('name')?></th><th><?=translate('branch')?></th><th></th></tr></thead>
					<tbody>
					<?php foreach ($admins as $a): ?>
						<tr>
							<td><?=html_escape($a->name)?></td>
							<td><?=html_escape($a->branch_name)?></td>
							<td class="text-right">
								<?php echo form_open('schools/remove_admin', array('style' => 'display:inline')); ?>
									<input type="hidden" name="school_id" value="<?=(int) $school->id?>">
									<input type="hidden" name="staff_id" value="<?=(int) $a->id?>">
									<button class="btn btn-default btn-xs" type="submit"><i class="fas fa-times"></i></button>
								<?php echo form_close(); ?>
							</td>
						</tr>
					<?php endforeach; if (!$admins): ?>
						<tr><td colspan="3" class="text-muted">No School Admin assigned.</td></tr>
					<?php endif; ?>
					</tbody>
				</table>
				<?php if ($candidates): ?>
				<?php echo form_open('schools/add_admin', array('class' => 'form-inline')); ?>
					<input type="hidden" name="school_id" value="<?=(int) $school->id?>">
					<select name="staff_id" class="form-control">
						<?php foreach ($candidates as $c): ?>
						<option value="<?=(int) $c->id?>"><?=html_escape($c->name . ' (' . $c->branch_name . ')')?></option>
						<?php endforeach; ?>
					</select>
					<button type="submit" class="btn btn-default"><i class="fas fa-plus-circle"></i> <?=translate('add')?></button>
				<?php echo form_close(); ?>
				<?php else: ?>
				<p class="text-muted">No other active Admin staff found in this school's branches.</p>
				<?php endif; ?>
				<hr>
				<h5><i class="fas fa-user-plus"></i> Create new School Admin</h5>
				<?php echo form_open('schools/create_admin'); ?>
					<input type="hidden" name="school_id" value="<?=(int) $school->id?>">
					<div class="form-group">
						<label class="control-label"><?=translate('branch')?> *</label>
						<select name="branch_id" class="form-control">
							<?php foreach ($school_branches as $sb): ?><option value="<?=(int) $sb->id?>"><?=html_escape($sb->name)?></option><?php endforeach; ?>
						</select>
					</div>
					<div class="row">
						<div class="col-md-6 form-group"><label class="control-label"><?=translate('name')?> *</label><input type="text" name="name" class="form-control" required></div>
						<div class="col-md-6 form-group"><label class="control-label"><?=translate('email')?> *</label><input type="email" name="email" class="form-control" required></div>
					</div>
					<div class="row">
						<div class="col-md-4 form-group"><label class="control-label"><?=translate('mobile_no')?></label><input type="text" name="mobileno" class="form-control"></div>
						<div class="col-md-4 form-group"><label class="control-label"><?=translate('username')?> *</label><input type="text" name="username" class="form-control" autocomplete="off" required></div>
						<div class="col-md-4 form-group"><label class="control-label"><?=translate('password')?> *</label><input type="text" name="password" class="form-control" autocomplete="off" minlength="6" required></div>
					</div>
					<button type="submit" class="btn btn-default"><i class="fas fa-plus-circle"></i> Create School Admin</button>
					<p class="help-block">Login: open the login page, choose the <strong>Admin</strong> tab (not Super Admin).</p>
				<?php echo form_close(); ?>
			</div>
		</section>
	</div>
	<?php endif; ?>
</div>
