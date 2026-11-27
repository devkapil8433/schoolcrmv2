<?php $page_title = translate('reset_password'); $this->load->view('authentication/_auth_open'); ?>

<div class="sign-hader">
    <img src="<?php echo $this->application_model->getBranchImage(!empty($branch_id) ? $branch_id : '', 'logo'); ?>" height="48" alt="" class="sign-logo-sm">
    <h2><?php echo translate('reset_password'); ?></h2>
    <p class="sign-subtitle"><?php echo html_escape($global_config['institute_name']); ?></p>
</div>

<form method="post" accept-charset="utf-8" class="auth-form">
    <?php echo $this->app_lib->generateCSRF(); ?>

    <div class="form-group<?php echo form_error('password') ? ' has-error' : ''; ?>">
        <label class="field-label">New Password</label>
        <div class="input-group-icon">
            <span class="input-group-addon"><span class="icon"><i class="fas fa-key"></i></span></span>
            <input type="password" class="form-control" name="password" placeholder="New Password">
        </div>
        <?php if (form_error('password')) { ?><span class="error"><?php echo form_error('password'); ?></span><?php } ?>
    </div>

    <div class="form-group<?php echo form_error('c_password') ? ' has-error' : ''; ?>">
        <label class="field-label">Confirm New Password</label>
        <div class="input-group-icon">
            <span class="input-group-addon"><span class="icon"><i class="fas fa-key"></i></span></span>
            <input type="password" class="form-control" name="c_password" placeholder="Confirm New Password">
        </div>
        <?php if (form_error('c_password')) { ?><span class="error"><?php echo form_error('c_password'); ?></span><?php } ?>
    </div>

    <button type="submit" id="btn_submit" class="btn btn-login btn-block btn-round">
        <span class="btn-text"><i class="far fa-check-circle"></i> Confirm</span>
        <span class="btn-loader"><i class="fas fa-spinner fa-spin"></i></span>
    </button>

    <?php if (!empty($global_config['footer_text'])) { ?>
    <div class="sign-footer"><p><?php echo $global_config['footer_text']; ?></p></div>
    <?php } ?>
</form>

<?php $this->load->view('authentication/_auth_close'); ?>
