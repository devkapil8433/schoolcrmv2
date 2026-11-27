<section class="panel">
    <div data-portal-tab-group>
        <div >
            <a href="#list" data-portal-tab="list" class=".5 -mb-px">
                <i class="fas fa-unlock-alt"></i> <?php echo translate('change') . ' ' . translate('password'); ?>
            </a>
            <a href="#login" data-portal-tab="login" class=".5 -mb-px">
                <i class="fas fa-user-lock"></i> <?php echo translate('login') . ' ' . translate('username'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div data-portal-tab-pane="list">
                <?php echo form_open($this->uri->uri_string(), array('class' => 'max-w-xl frm-submit')); ?>
                <div >
                    <label class="control-label"><?php echo translate('current_password'); ?> <span class="required">*</span></label>
                    <input type="password" class="form-control" name="current_password" value="<?php echo set_value('current_password'); ?>">
                    <?php if (form_error('current_password')) { ?><p ><?php echo form_error('current_password'); ?></p><?php } ?>
                </div>
                <div >
                    <label class="control-label"><?php echo translate('new_password'); ?> <span class="required">*</span></label>
                    <input type="password" class="form-control" name="new_password" value="<?php echo set_value('new_password'); ?>">
                    <?php if (form_error('new_password')) { ?><p ><?php echo form_error('new_password'); ?></p><?php } ?>
                </div>
                <div >
                    <label class="control-label"><?php echo translate('confirm_password'); ?> <span class="required">*</span></label>
                    <input type="password" class="form-control" name="confirm_password" value="<?php echo set_value('confirm_password'); ?>">
                    <?php if (form_error('confirm_password')) { ?><p ><?php echo form_error('confirm_password'); ?></p><?php } ?>
                </div>
                <button type="submit" class="btn btn-default" data-loading-text="Processing"><i class="fas fa-key"></i> <?php echo translate('update'); ?></button>
                <?php echo form_close(); ?>
            </div>
            <div data-portal-tab-pane="login" class="hidden">
                <?php
                $username = $this->db->select('username')->where('id', get_loggedin_id())->get('login_credential')->row()->username;
                echo form_open('profile/username_change', array('class' => 'max-w-xl frm-submit'));
                ?>
                <div >
                    <label class="control-label"><?php echo translate('username'); ?> <span class="required">*</span></label>
                    <input type="text" class="form-control" name="username" value="<?php echo set_value('username', $username); ?>">
                    <span class="error"></span>
                </div>
                <button type="submit" class="btn btn-default" data-loading-text="Processing"><i class="fas fa-key"></i> <?php echo translate('update'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</section>
