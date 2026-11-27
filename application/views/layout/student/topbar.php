<header class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6">
        <div class="flex items-center gap-3">
            <button type="button" id="portal-sidebar-open" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Open menu">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <a href="<?php echo base_url('dashboard'); ?>" class="lg:hidden">
                <img src="<?php echo $this->application_model->getBranchImage(get_loggedin_branch_id(), 'logo-small'); ?>" alt="Logo" class="h-8">
            </a>
        </div>

        <div class="flex items-center gap-1 sm:gap-2">
            <?php if ($this->session->userdata('impersonate_backup')) { ?>
            <a href="<?php echo base_url('authentication/exit_impersonate'); ?>" class="p-2 rounded-lg text-amber-500 hover:bg-amber-50" title="<?php echo translate('return_to_admin'); ?>">
                <i class="fas fa-user-shield"></i>
            </a>
            <?php } ?>

            <?php
            if (is_student_loggedin()) {
                $this->db->select('enroll.id,class.name as class_name,section.name as section_name');
                $this->db->from('enroll');
                $this->db->join('class', 'class.id = enroll.class_id', 'inner');
                $this->db->join('section', 'section.id = enroll.section_id', 'inner');
                $this->db->where('enroll.student_id', get_loggedin_user_id());
                $this->db->where('enroll.session_id', get_session_id());
                $this->db->order_by('enroll.id', 'asc');
                $multiClass = $this->db->get()->result();
                if (count($multiClass) > 1) {
                    if ($this->session->has_userdata('enrollID')) {
                        $currentClass = $this->session->userdata('enrollID');
                    } else {
                        $currentClass = get_global_setting('translation');
                    }
            ?>
            <div class="relative" data-portal-dropdown-wrap>
                <button type="button" data-portal-dropdown-trigger class="p-2 rounded-lg text-slate-600 hover:bg-slate-100" title="<?php echo translate('switch_class'); ?>">
                    <i class="fas fa-retweet"></i>
                </button>
                <div data-portal-dropdown class="absolute right-0 mt-2 w-72 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 py-1 z-50 hidden">
                    <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase border-b border-slate-100"><?php echo translate('switch_class'); ?></div>
                    <?php foreach ($multiClass as $class) { ?>
                    <a href="<?php echo base_url('userrole/switchClass/' . $class->id); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors justify-between">
                        <?php echo html_escape($class->class_name . ' (' . $class->section_name . ')'); ?>
                        <?php if ($class->id == $currentClass) { ?><i class="fas fa-check text-primary"></i><?php } ?>
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php }
            } ?>

            <?php
            if (is_student_loggedin() || is_parent_loggedin()) {
                $student_id = $this->session->userdata('student_id');
                if (!empty($student_id)) {
                    $this->db->select('schoolyear.*')->from('schoolyear');
                    $this->db->join('enroll', 'schoolyear.id = enroll.session_id');
                    $this->db->where('enroll.student_id', $student_id);
                    $this->db->group_by('enroll.session_id');
                    $this->db->order_by('schoolyear.id');
                    $get_session = $this->db->get()->result();
                }
            } else {
                $get_session = $this->db->get('schoolyear')->result();
            }
            if (!empty($get_session)) {
            ?>
            <div class="relative hidden sm:block" data-portal-dropdown-wrap>
                <button type="button" data-portal-dropdown-trigger class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm text-slate-600 hover:bg-slate-100 border border-slate-200">
                    <i class="far fa-calendar-alt"></i>
                    <span class="hidden md:inline"><?php echo translate('academic_session'); ?></span>
                </button>
                <div data-portal-dropdown class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 py-1 z-50 hidden">
                    <?php foreach ($get_session as $session) { ?>
                    <a href="<?php echo base_url('sessions/set_academic/' . $session->id); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors justify-between">
                        <?php echo html_escape($session->school_year); ?>
                        <?php if (get_session_id() == $session->id) { ?><i class="fas fa-check text-primary"></i><?php } ?>
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <div class="relative" data-portal-dropdown-wrap>
                <button type="button" data-portal-dropdown-trigger class="p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <i class="far fa-flag"></i>
                </button>
                <div data-portal-dropdown class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 py-1 z-50 hidden">
                    <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase border-b border-slate-100"><?php echo translate('language'); ?></div>
                    <?php
                    $set_lang = $this->session->has_userdata('set_lang') ? $this->session->userdata('set_lang') : get_global_setting('translation');
                    $languages = $this->db->select('id,lang_field,name')->where('status', 1)->get('language_list')->result();
                    foreach ($languages as $lang) {
                    ?>
                    <a href="<?php echo base_url('translations/set_language/' . html_escape($lang->lang_field)); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors justify-between">
                        <span class="flex items-center gap-2">
                            <img class="w-5 h-5 rounded" src="<?php echo $this->application_model->getLangImage($lang->id); ?>" alt="">
                            <?php echo ucfirst(html_escape($lang->name)); ?>
                        </span>
                        <?php if ($set_lang == $lang->lang_field) { ?><i class="fas fa-check text-primary"></i><?php } ?>
                    </a>
                    <?php } ?>
                </div>
            </div>

            <div class="relative" data-portal-dropdown-wrap>
                <button type="button" data-portal-dropdown-trigger class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 relative">
                    <i class="far fa-bell"></i>
                    <?php
                    $unreadMessage = $this->application_model->unread_message_alert();
                    if (count($unreadMessage) > 0) {
                        echo '<span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">' . count($unreadMessage) . '</span>';
                    }
                    ?>
                </button>
                <div data-portal-dropdown class="absolute right-0 mt-2 w-72 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 py-1 z-50 hidden">
                    <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase border-b border-slate-100"><?php echo translate('message'); ?></div>
                    <div class="max-h-64 overflow-y-auto">
                        <?php if (count($unreadMessage) > 0) {
                            foreach ($unreadMessage as $message) { ?>
                        <a href="<?php echo base_url('communication/mailbox/read?type=' . $message['msg_type'] . '&id=' . $message['id']); ?>" class="flex items-start gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                            <img src="<?php echo $message['message_details']['imgPath']; ?>" class="w-10 h-10 rounded-full shrink-0" alt="">
                            <div class="min-w-0">
                                <p class="font-medium text-slate-900 truncate"><?php echo html_escape($message['message_details']['userName']); ?></p>
                                <p class="text-xs text-slate-500 truncate"><?php echo mb_strimwidth(strip_tags($message['body']), 0, 40, '...'); ?></p>
                            </div>
                        </a>
                        <?php }
                        } else { ?>
                        <p class="px-4 py-6 text-sm text-slate-500 text-center">You do not have any new messages</p>
                        <?php } ?>
                    </div>
                    <div class="border-t border-slate-100 px-4 py-2 text-right">
                        <a href="<?php echo base_url('communication/mailbox/inbox'); ?>" class="text-sm text-primary hover:underline">All Messages</a>
                    </div>
                </div>
            </div>

            <div class="relative ml-1" data-portal-dropdown-wrap>
                <button type="button" data-portal-dropdown-trigger class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100">
                    <img src="<?php echo get_image_url(get_loggedin_user_type(), $this->session->userdata('logger_photo')); ?>" alt="" class="w-9 h-9 rounded-full object-cover ring-2 ring-slate-200">
                </button>
                <div data-portal-dropdown class="absolute right-0 mt-2 w-72 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 py-1 z-50 hidden">
                    <div class="px-4 py-3 border-b border-slate-100">
                        <p class="font-semibold text-slate-900"><?php echo html_escape($this->session->userdata('name')); ?></p>
                        <p class="text-xs text-slate-500"><?php echo ucfirst(loggedin_role_name()); ?></p>
                    </div>
                    <a href="<?php echo base_url('profile'); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors"><i class="fas fa-user-shield w-4"></i> <?php echo translate('profile'); ?></a>
                    <a href="<?php echo base_url('profile/password'); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors"><i class="fas fa-key w-4"></i> <?php echo translate('reset_password'); ?></a>
                    <a href="<?php echo base_url('communication/mailbox/inbox'); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors"><i class="far fa-envelope w-4"></i> <?php echo translate('mailbox'); ?></a>
                    <div class="border-t border-slate-100 mt-1 pt-1">
                        <p class="px-4 py-1 text-xs uppercase text-slate-400"><?php echo translate('switch_account'); ?></p>
                        <?php foreach (get_linked_accounts() as $linkedID => $linkedAcc) { ?>
                        <a href="<?php echo base_url('authentication/switch_account/' . (int) $linkedID); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors"><i class="fas fa-user-friends w-4"></i> <?php echo html_escape($linkedAcc['name']); ?> <span class="text-xs text-slate-400">(<?php echo html_escape(ucfirst($linkedAcc['role'])); ?>)</span></a>
                        <?php } ?>
                        <a href="<?php echo base_url('authentication?add_account=1'); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors"><i class="fas fa-user-plus w-4"></i> <?php echo translate('add_another_account'); ?></a>
                    </div>
                    <div class="border-t border-slate-100 mt-1 pt-1">
                        <a href="<?php echo base_url('authentication/logout'); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-slate-50 transition-colors"><i class="fas fa-sign-out-alt w-4"></i> <?php echo translate('logout'); ?></a>
                        <?php if (get_linked_accounts()) { ?>
                        <a href="<?php echo base_url('authentication/logout?all=1'); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-slate-50 transition-colors"><i class="fas fa-power-off w-4"></i> <?php echo translate('logout_all_accounts'); ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
