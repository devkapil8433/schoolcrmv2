<?php $page_title = translate('password_restoration'); $this->load->view('authentication/_auth_open'); ?>

<div class="sign-hader">
    <img src="<?php echo $this->application_model->getBranchImage($branch_id, 'logo'); ?>" height="48" alt="" class="sign-logo-sm">
    <h2><?php echo translate('password_restoration'); ?></h2>
    <p class="sign-subtitle">Enter your email and you will receive reset instructions</p>
</div>

<?php
if ($this->session->flashdata('reset_res')) {
    if ($this->session->flashdata('reset_res') == 'true') {
        echo '<div class="alert-msg">Password reset email sent successfully. Check email</div>';
    } elseif ($this->session->flashdata('reset_res') == 'false') {
        echo '<div class="alert-msg danger">You entered the wrong email address</div>';
    }
}
?>

<?php echo form_open($this->uri->uri_string(), array('id' => 'forgotForm', 'class' => 'auth-form')); ?>

<div class="form-group<?php echo form_error('username') ? ' has-error' : ''; ?>">
    <label class="field-label" for="forgot-username"><?php echo translate('username'); ?></label>
    <div class="input-group-icon">
        <span class="input-group-addon"><span class="icon"><i class="far fa-user"></i></span></span>
        <input type="text" class="form-control" id="forgot-username" name="username" value="<?php echo set_value('username'); ?>" autocomplete="username" placeholder="<?php echo translate('username'); ?>">
    </div>
    <?php if (form_error('username')) { ?><span class="error"><?php echo form_error('username'); ?></span><?php } ?>
</div>

<button type="submit" id="btn_submit" class="btn btn-login btn-block btn-round">
    <span class="btn-text"><i class="far fa-paper-plane"></i> <?php echo translate('forgot'); ?></span>
    <span class="btn-loader"><i class="fas fa-spinner fa-spin"></i></span>
</button>

<div class="text-center" style="margin-top: 20px;">
    <a href="<?php echo base_url("{$this->authentication_model->getSegment(1)}authentication"); ?>" class="forgot-link"><i class="fas fa-long-arrow-alt-left"></i> <?php echo translate('back_to_login'); ?></a>
</div>

<p class="secure-note"><i class="fas fa-shield-alt"></i> <?php echo translate('secure_encrypted_connection'); ?></p>

<?php if (!empty($global_config['footer_text'])) { ?>
<div class="sign-footer"><p><?php echo $global_config['footer_text']; ?></p></div>
<?php } ?>

<?php echo form_close(); ?>
<?php $this->load->view('authentication/_auth_close'); ?>
