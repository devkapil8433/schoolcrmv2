<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-key"></i> <?=translate('login_manager')?></h4>
			</header>
			<div class="panel-body">
				<ul class="nav nav-pills mb-md">
					<?php foreach ($groups as $key => $label): ?>
					<li class="<?=($type == $key ? 'active' : '')?>">
						<a href="<?=base_url('login_manager?type=' . $key)?>"><?=translate($label)?></a>
					</li>
					<?php endforeach; ?>
				</ul>
				<p class="text-muted">
					<i class="fas fa-info-circle"></i>
					Passwords are stored encrypted and cannot be displayed. Use the edit button to set a new password and share it with the user.
				</p>
				<table class="table table-bordered table-condensed table-hover table-export">
					<thead>
						<tr>
							<th>#</th>
							<th><?=translate('name')?></th>
							<?php if ($type == 'student'): ?>
							<th><?=translate('register_no')?></th>
							<th><?=translate('class')?></th>
							<th><?=translate('section')?></th>
							<?php elseif ($type != 'parent'): ?>
							<th><?=translate('role')?></th>
							<?php endif; ?>
							<th><?=translate('username')?></th>
							<th><?=translate('status')?></th>
							<th><?=translate('last_login')?></th>
							<th class="no-sort"><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1; foreach ($accounts as $row): ?>
						<tr>
							<td><?=$count++?></td>
							<td><?=html_escape($row['name'])?></td>
							<?php if ($type == 'student'): ?>
							<td><?=html_escape($row['code'])?></td>
							<td><?=html_escape($row['class_name'])?></td>
							<td><?=html_escape($row['section_name'])?></td>
							<?php elseif ($type != 'parent'): ?>
							<td><?=html_escape(ucfirst($row['role_name']))?></td>
							<?php endif; ?>
							<td><strong><?=html_escape($row['username'])?></strong></td>
							<td>
								<?php if ($row['active']): ?>
									<span class="label label-success-custom"><?=translate('active')?></span>
								<?php else: ?>
									<span class="label label-danger-custom"><?=translate('inactive')?></span>
								<?php endif; ?>
							</td>
							<td><?=(empty($row['last_login']) ? '-' : html_escape($row['last_login']))?></td>
							<td class="action">
								<a href="javascript:void(0);" class="btn btn-default btn-circle icon" title="<?=translate('edit')?>"
								data-id="<?=(int) $row['id']?>" data-username="<?=html_escape($row['username'])?>" data-name="<?=html_escape($row['name'])?>"
								onclick="editCredential(this);">
									<i class="fas fa-pen-nib"></i>
								</a>
								<?php if ($row['active'] && $row['id'] != get_loggedin_id()): ?>
								<?php echo form_open('login_manager/open', array('style' => 'display:inline')); ?>
									<input type="hidden" name="id" value="<?=(int) $row['id']?>">
									<input type="hidden" name="type" value="<?=html_escape($type)?>">
									<button type="submit" class="btn btn-default btn-circle icon" title="<?=translate('login')?>">
										<i class="fas fa-sign-in-alt"></i>
									</button>
								<?php echo form_close(); ?>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="credentialModal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="fas fa-pen-nib"></i> <?=translate('edit')?> <?=translate('login_credential')?></h4>
		</header>
		<?php echo form_open('login_manager/update', array('class' => 'frm-submit')); ?>
		<div class="panel-body">
			<input type="hidden" name="id" id="cred_id" value="">
			<input type="hidden" name="type" value="<?=html_escape($type)?>">
			<p><strong id="cred_name"></strong></p>
			<div class="form-group">
				<label class="control-label"><?=translate('username')?> <span class="required">*</span></label>
				<input type="text" class="form-control" name="username" id="cred_username" autocomplete="off">
				<span class="error"></span>
			</div>
			<div class="form-group">
				<label class="control-label"><?=translate('new_password')?> (<?=translate('leave_blank_to_keep_current')?>)</label>
				<div class="input-group">
					<input type="text" class="form-control" name="new_password" id="cred_password" autocomplete="off">
					<span class="input-group-btn">
						<button type="button" class="btn btn-default" onclick="generateCredentialPassword();"><i class="fas fa-random"></i></button>
					</span>
				</div>
				<span class="error"></span>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><i class="fas fa-plus-circle"></i> <?=translate('update')?></button>
					<button type="button" class="btn btn-default modal-dismiss"><?=translate('close')?></button>
				</div>
			</div>
		</footer>
		<?php echo form_close(); ?>
	</section>
</div>

<script type="text/javascript">
	function editCredential(el) {
		var $m = $('#credentialModal');
		$m.find('.error').html('');
		$m.find('.form-group').removeClass('has-error');
		$('#cred_id').val($(el).data('id'));
		$('#cred_username').val($(el).attr('data-username'));
		$('#cred_password').val('');
		$('#cred_name').text($(el).attr('data-name'));
		mfp_modal('#credentialModal');
	}

	function generateCredentialPassword() {
		var chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		var out = '';
		var rnd = new Uint32Array(8);
		window.crypto.getRandomValues(rnd);
		for (var i = 0; i < rnd.length; i++) {
			out += chars.charAt(rnd[i] % chars.length);
		}
		$('#cred_password').val(out);
	}
</script>
