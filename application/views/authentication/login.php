<?php $page_title = translate('login'); $this->load->view('authentication/_auth_open'); ?>

<div class="sign-hader">
    <img src="<?php echo $this->application_model->getBranchImage($branch_id, 'logo'); ?>" height="48" alt="" class="sign-logo-sm">
    <h2><?php echo translate('login'); ?></h2>
    <p class="sign-subtitle"><?php echo translate('sign_in_to_continue'); ?></p>
</div>

<?php if (is_loggedin()) { ?>
<p class="sign-subtitle" style="margin-bottom:12px;">
    <i class="fas fa-user-plus"></i> <?php echo translate('add_another_account'); ?> &middot;
    <a href="<?php echo base_url('dashboard'); ?>"><?php echo translate('back_to_dashboard'); ?></a>
</p>
<?php } ?>

<?php
$active_role = set_value('login_role');
if (empty($active_role)) {
    $active_role = $this->session->flashdata('login_role');
}
if (empty($active_role) || !in_array($active_role, array('superadmin', 'admin', 'teacher', 'student', 'accountant'))) {
    $active_role = 'admin';
}
$login_tabs = array(
    'superadmin' => array('label' => translate('super_admin'), 'icon' => 'fas fa-user-tie'),
    'admin' => array('label' => translate('admin'), 'icon' => 'fas fa-user-shield'),
    'teacher' => array('label' => translate('teacher'), 'icon' => 'fas fa-chalkboard-teacher'),
    'student' => array('label' => translate('student'), 'icon' => 'fas fa-user-graduate'),
    'accountant' => array('label' => translate('accountant'), 'icon' => 'fas fa-calculator'),
);
?>
<div class="auth-role-tabs" role="tablist">
    <?php foreach ($login_tabs as $tab_key => $tab) { ?>
    <button type="button" class="auth-role-tab<?php echo $active_role === $tab_key ? ' active' : ''; ?>" data-role="<?php echo $tab_key; ?>" role="tab" aria-selected="<?php echo $active_role === $tab_key ? 'true' : 'false'; ?>">
        <i class="<?php echo $tab['icon']; ?>"></i>
        <span><?php echo $tab['label']; ?></span>
    </button>
    <?php } ?>
</div>

<?php echo form_open($this->uri->uri_string(), array('id' => 'loginForm', 'class' => 'auth-form')); ?>

<input type="hidden" name="login_role" id="login_role" value="<?php echo html_escape($active_role); ?>">

<div class="form-group<?php echo form_error('email') ? ' has-error' : ''; ?>">
    <label class="field-label" for="login-email"><?php echo translate('username'); ?></label>
    <div class="input-group-icon">
        <span class="input-group-addon"><span class="icon"><i class="far fa-user"></i></span></span>
        <input type="text" class="form-control" id="login-email" name="email" value="<?php echo set_value('email'); ?>" placeholder="<?php echo translate('username'); ?>" autocomplete="username">
    </div>
    <?php if (form_error('email')) { ?><span class="error"><?php echo form_error('email'); ?></span><?php } ?>
</div>

<div class="form-group<?php echo form_error('password') ? ' has-error' : ''; ?>">
    <label class="field-label" for="login-password"><?php echo translate('password'); ?></label>
    <div class="input-group-icon input-group-password">
        <span class="input-group-addon"><span class="icon"><i class="fas fa-lock"></i></span></span>
        <input type="password" class="form-control" id="login-password" name="password" placeholder="<?php echo translate('password'); ?>" autocomplete="current-password">
        <button type="button" id="togglePassword" class="password-toggle" aria-label="Toggle password"><i class="far fa-eye"></i></button>
    </div>
    <?php if (form_error('password')) { ?><span class="error"><?php echo form_error('password'); ?></span><?php } ?>
</div>

<div class="forgot-text checkbox-replace">
    <label class="i-checks">
        <input type="checkbox" name="remember" id="remember">
        <i></i> <?php echo translate('remember'); ?>
    </label>
    <a href="<?php echo base_url("{$this->authentication_model->getSegment(1)}forgot"); ?>" class="forgot-link"><?php echo translate('lose_your_password'); ?></a>
</div>

<button type="submit" id="btn_submit" class="btn btn-login btn-block btn-round">
    <span class="btn-text"><i class="fas fa-sign-in-alt"></i> <?php echo translate('login'); ?></span>
    <span class="btn-loader"><i class="fas fa-spinner fa-spin"></i></span>
</button>

<p class="secure-note"><i class="fas fa-shield-alt"></i> <?php echo translate('secure_encrypted_connection'); ?></p>

<p class="secure-note">
    <a href="#" id="newTabLogin" target="_blank" rel="noopener" style="display:none;"><i class="fas fa-external-link-alt"></i> <?php echo translate('open_new_tab_to_login_as_another_user'); ?></a>
</p>
<script>
(function () {
    var h = location.hostname;
    if (h === 'localhost' || h.slice(-10) === '.localhost' || h === '127.0.0.1') {
        var a = document.getElementById('newTabLogin');
        // every *.localhost name has its own cookie jar, so a fresh tab can hold a different login
        var sub = 't' + Date.now().toString(36) + '.localhost';
        a.href = location.protocol + '//' + sub + (location.port ? ':' + location.port : '') + '<?php echo parse_url(base_url('authentication'), PHP_URL_PATH); ?>';
        a.style.display = 'inline';
    }
})();
</script>

<?php if (!empty($global_config['footer_text'])) { ?>
<div class="sign-footer"><p><?php echo $global_config['footer_text']; ?></p></div>
<?php } ?>

<?php echo form_close(); ?>
<?php $this->load->view('authentication/_auth_close'); ?>
