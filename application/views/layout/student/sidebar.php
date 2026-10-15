<?php
$navLink = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-white';
$navActive = 'bg-slate-800 text-white border-l-4 border-primary pl-2';
$navChildLink = $navLink . ' text-sm py-2';
$navChild = 'ml-4 mt-1 space-y-0.5 border-l border-slate-700 pl-3';
?>
<aside id="portal-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col">
    <div class="flex items-center gap-3 h-16 px-5 border-b border-slate-800 shrink-0">
        <a href="<?php echo base_url('dashboard'); ?>" class="flex items-center gap-3 min-w-0">
            <img src="<?php echo $this->application_model->getBranchImage(get_loggedin_branch_id(), 'logo-small'); ?>" alt="Logo" class="h-9 w-auto shrink-0">
            <span class="text-white font-semibold text-sm truncate"><?php echo html_escape($global_config['institute_name']); ?></span>
        </a>
        <button type="button" id="portal-sidebar-close" class="ml-auto lg:hidden text-slate-400 hover:text-white p-1" aria-label="Close menu">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" id="portal-nav">
        <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Main</p>

        <?php if (is_student_loggedin()) { ?>
        <a href="<?php echo base_url('dashboard'); ?>" class="<?php echo $navLink . (($sub_page == 'userrole/dashboard') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-th-large w-5 text-center"></i>
            <span><?php echo translate('dashboard'); ?></span>
        </a>
        <?php } elseif (is_parent_loggedin()) { ?>
        <div>
            <button type="button" class="<?php echo $navLink; ?> w-full" data-portal-nav-toggle data-target="nav-dashboard"<?php echo ($main_menu == 'dashboard') ? ' aria-expanded="true"' : ''; ?>>
                <i class="fas fa-th-large w-5 text-center"></i>
                <span class="flex-1 text-left"><?php echo translate('dashboard'); ?></span>
                <i class="fas fa-chevron-down text-xs transition-transform<?php echo ($main_menu == 'dashboard') ? ' rotate-180' : ''; ?>" data-portal-chevron></i>
            </button>
            <div id="nav-dashboard" class="<?php echo $navChild . (($main_menu == 'dashboard') ? '' : ' hidden'); ?>">
                <a href="<?php echo base_url('parents/my_children'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/dashboard' && empty(get_activeChildren_id())) ? ' ' . $navActive : ''); ?>">
                    <i class="fab fa-slideshare w-5 text-center"></i>
                    <span><?php echo translate('my_children'); ?></span>
                </a>
                <?php if (!empty(get_activeChildren_id())) { ?>
                <a href="<?php echo base_url('dashboard'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/dashboard') ? ' ' . $navActive : ''); ?>">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i>
                    <span><?php echo translate('dashboard'); ?></span>
                </a>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

        <?php if (is_student_loggedin()) { ?>
        <a href="<?php echo base_url('profile'); ?>" class="<?php echo $navLink . (($main_menu == 'profile') ? ' ' . $navActive : ''); ?>">
            <i class="far fa-user-circle w-5 text-center"></i>
            <span><?php echo translate('profile'); ?></span>
        </a>
        <?php } ?>

        <?php if ((is_parent_loggedin() && !empty(get_activeChildren_id())) || is_student_loggedin()) { ?>

        <a href="<?php echo base_url('userrole/teacher'); ?>" class="<?php echo $navLink . (($main_menu == 'teachers') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-chalkboard-teacher w-5 text-center"></i>
            <span><?php echo translate('teachers'); ?></span>
        </a>

        <div>
            <button type="button" class="<?php echo $navLink . (($main_menu == 'academic') ? ' ' . $navActive : ''); ?> w-full" data-portal-nav-toggle data-target="nav-academic">
                <i class="fas fa-graduation-cap w-5 text-center"></i>
                <span class="flex-1 text-left"><?php echo translate('academic'); ?></span>
                <i class="fas fa-chevron-down text-xs transition-transform<?php echo ($main_menu == 'academic') ? ' rotate-180' : ''; ?>" data-portal-chevron></i>
            </button>
            <div id="nav-academic" class="<?php echo $navChild . (($main_menu == 'academic') ? '' : ' hidden'); ?>">
                <a href="<?php echo base_url('userrole/subject'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/subject') ? ' ' . $navActive : ''); ?>">
                    <i class="fas fa-book-reader w-5 text-center"></i>
                    <span><?php echo translate('subject'); ?></span>
                </a>
                <a href="<?php echo base_url('userrole/class_schedule'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/class_schedule') ? ' ' . $navActive : ''); ?>">
                    <i class="fas fa-calendar-alt w-5 text-center"></i>
                    <span><?php echo translate('class') . ' ' . translate('schedule'); ?></span>
                </a>
            </div>
        </div>

        <?php if (is_student_loggedin()) { ?>
        <a href="<?php echo base_url('userrole/live_class'); ?>" class="<?php echo $navLink . (($main_menu == 'live_class') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-video w-5 text-center"></i>
            <span><?php echo translate('live_class_rooms'); ?></span>
        </a>
        <?php } ?>

        <a href="<?php echo base_url('userrole/leave_request'); ?>" class="<?php echo $navLink . (($main_menu == 'leave') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-file-alt w-5 text-center"></i>
            <span><?php echo translate('leave_application'); ?></span>
        </a>

        <a href="<?php echo base_url('userrole/attachments'); ?>" class="<?php echo $navLink . (($main_menu == 'attachments') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-cloud-upload-alt w-5 text-center"></i>
            <span><?php echo translate('attachments_book'); ?></span>
        </a>

        <a href="<?php echo base_url('userrole/homework'); ?>" class="<?php echo $navLink . (($main_menu == 'homework') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-book w-5 text-center"></i>
            <span><?php echo translate('homework'); ?></span>
        </a>

        <a href="<?php echo base_url('userrole/today_work'); ?>" class="<?php echo $navLink . (($main_menu == 'today_work') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-clipboard-list w-5 text-center"></i>
            <span>Today's Work</span>
        </a>

        <div>
            <button type="button" class="<?php echo $navLink . (($main_menu == 'exam') ? ' ' . $navActive : ''); ?> w-full" data-portal-nav-toggle data-target="nav-exam">
                <i class="fas fa-file-signature w-5 text-center"></i>
                <span class="flex-1 text-left"><?php echo translate('exam_master'); ?></span>
                <i class="fas fa-chevron-down text-xs transition-transform<?php echo ($main_menu == 'exam') ? ' rotate-180' : ''; ?>" data-portal-chevron></i>
            </button>
            <div id="nav-exam" class="<?php echo $navChild . (($main_menu == 'exam') ? '' : ' hidden'); ?>">
                <a href="<?php echo base_url('userrole/exam_schedule'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/exam_schedule') ? ' ' . $navActive : ''); ?>">
                    <i class="fas fa-calendar w-5 text-center"></i>
                    <span><?php echo translate('exam') . ' ' . translate('schedule'); ?></span>
                </a>
                <a href="<?php echo base_url('userrole/report_card'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/report_card') ? ' ' . $navActive : ''); ?>">
                    <i class="fas fa-marker w-5 text-center"></i>
                    <span><?php echo translate('report_card'); ?></span>
                </a>
            </div>
        </div>

        <?php if (is_student_loggedin()) { ?>
        <a href="<?php echo base_url('userrole/online_exam'); ?>" class="<?php echo $navLink . (($main_menu == 'onlineexam') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-laptop w-5 text-center"></i>
            <span><?php echo translate('online_exam'); ?></span>
        </a>
        <?php } ?>

        <div>
            <button type="button" class="<?php echo $navLink . (($main_menu == 'supervision') ? ' ' . $navActive : ''); ?> w-full" data-portal-nav-toggle data-target="nav-supervision">
                <i class="fas fa-eye w-5 text-center"></i>
                <span class="flex-1 text-left"><?php echo translate('supervision'); ?></span>
                <i class="fas fa-chevron-down text-xs transition-transform<?php echo ($main_menu == 'supervision') ? ' rotate-180' : ''; ?>" data-portal-chevron></i>
            </button>
            <div id="nav-supervision" class="<?php echo $navChild . (($main_menu == 'supervision') ? '' : ' hidden'); ?>">
                <a href="<?php echo base_url('userrole/hostels'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/hostels') ? ' ' . $navActive : ''); ?>">
                    <i class="fas fa-hotel w-5 text-center"></i>
                    <span><?php echo translate('hostel'); ?></span>
                </a>
                <a href="<?php echo base_url('userrole/route'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/transport_route') ? ' ' . $navActive : ''); ?>">
                    <i class="fas fa-bus w-5 text-center"></i>
                    <span><?php echo translate('transport'); ?></span>
                </a>
            </div>
        </div>

        <?php
        $getAttendanceType = $this->app_lib->getAttendanceType();
        if ($getAttendanceType == 2 || $getAttendanceType == 0) { ?>
        <a href="<?php echo base_url('userrole/attendance'); ?>" class="<?php echo $navLink . (($main_menu == 'attendance' && $sub_page == 'userrole/attendance') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-chart-bar w-5 text-center"></i>
            <span><?php echo translate('attendance'); ?></span>
        </a>
        <?php }
        if ($getAttendanceType == 2 || $getAttendanceType == 1) { ?>
        <a href="<?php echo base_url('userrole/subject_wise_attendance'); ?>" class="<?php echo $navLink . (($sub_page == 'userrole/subject_wise_attendance') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-chart-line w-5 text-center"></i>
            <span><?php echo translate('period') . ' ' . translate('attendance'); ?></span>
        </a>
        <?php } ?>

        <div>
            <button type="button" class="<?php echo $navLink . (($main_menu == 'library') ? ' ' . $navActive : ''); ?> w-full" data-portal-nav-toggle data-target="nav-library">
                <i class="fas fa-book-open w-5 text-center"></i>
                <span class="flex-1 text-left"><?php echo translate('library'); ?></span>
                <i class="fas fa-chevron-down text-xs transition-transform<?php echo ($main_menu == 'library') ? ' rotate-180' : ''; ?>" data-portal-chevron></i>
            </button>
            <div id="nav-library" class="<?php echo $navChild . (($main_menu == 'library') ? '' : ' hidden'); ?>">
                <a href="<?php echo base_url('userrole/book'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/book') ? ' ' . $navActive : ''); ?>">
                    <i class="fas fa-list w-5 text-center"></i>
                    <span><?php echo translate('books') . ' ' . translate('list'); ?></span>
                </a>
                <a href="<?php echo base_url('userrole/book_request'); ?>" class="<?php echo $navChildLink . (($sub_page == 'userrole/book_request') ? ' ' . $navActive : ''); ?>">
                    <i class="fas fa-bookmark w-5 text-center"></i>
                    <span>Issued Book</span>
                </a>
            </div>
        </div>

        <a href="<?php echo base_url('userrole/event'); ?>" class="<?php echo $navLink . (($sub_page == 'userrole/event') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-calendar-check w-5 text-center"></i>
            <span><?php echo translate('events'); ?></span>
        </a>

        <a href="<?php echo base_url('userrole/invoice'); ?>" class="<?php echo $navLink . (($main_menu == 'fees') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-calculator w-5 text-center"></i>
            <span><?php echo translate('fees_history'); ?></span>
        </a>

        <a href="<?php echo base_url('communication/mailbox/inbox'); ?>" class="<?php echo $navLink . (($main_menu == 'message') ? ' ' . $navActive : ''); ?>">
            <i class="fas fa-envelope w-5 text-center"></i>
            <span><?php echo translate('message'); ?></span>
        </a>

        <?php } ?>
    </nav>
</aside>
