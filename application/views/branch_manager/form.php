<?php
$isNew = empty($branch);
$v = function ($field, $col = null) use ($branch, $isNew) {
	return html_escape(set_value($field, $isNew ? '' : $branch->{$col ?: $field}));
};
$canMove = is_superadmin_loggedin() || $isNew;
?>
<section class="panel">
	<header class="panel-heading"><h4 class="panel-title"><i class="fas fa-code-branch"></i> <?=$isNew ? translate('create_branch') : translate('edit') . ' ' . translate('branch')?></h4></header>
	<?php echo form_open(base_url('branch_manager/form/' . ($isNew ? '' : $branch->id)), array('class' => 'form-horizontal form-bordered')); ?>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('school')?> <span class="required">*</span></label>
			<div class="col-md-6">
				<select name="school_id" class="form-control" <?=$canMove ? '' : 'readonly style="pointer-events:none;background:#eee"'?>>
					<?php foreach ($schools as $s): ?>
					<option value="<?=$s->id?>" <?=set_select('school_id', $s->id, !$isNew && $branch->school_id == $s->id)?>><?=html_escape($s->name)?></option>
					<?php endforeach; ?>
				</select>
				<span class="error"><?=form_error('school_id')?></span>
			</div>
		</div>
		<?php
		$fields = array(
			array('branch_name', 'name', true), array('email', 'email', true), array('mobileno', 'mobileno', true),
			array('currency', 'currency', true), array('currency_symbol', 'symbol', true),
			array('city', 'city', false), array('state', 'state', false), array('address', 'address', false),
		);
		foreach ($fields as $f): ?>
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate($f[0])?> <?=$f[2] ? '<span class="required">*</span>' : ''?></label>
			<div class="col-md-6">
				<input type="text" class="form-control" name="<?=$f[0]?>" value="<?=$v($f[0], $f[1])?>">
				<span class="error"><?=form_error($f[0])?></span>
			</div>
		</div>
		<?php endforeach; ?>
		<?php if ($isNew): ?>
		<p class="col-md-offset-3 text-muted">New branches start as Active.</p>
		<?php endif; ?>
	</div>
	<footer class="panel-footer">
		<div class="row"><div class="col-md-offset-3 col-md-6">
			<button type="submit" name="save" value="1" class="btn btn-default"><i class="fas fa-plus-circle"></i> <?=translate('save')?></button>
			<a href="<?=base_url('branch_manager')?>" class="btn btn-default"><?=translate('cancel')?></a>
		</div></div>
	</footer>
	<?php echo form_close(); ?>
</section>
