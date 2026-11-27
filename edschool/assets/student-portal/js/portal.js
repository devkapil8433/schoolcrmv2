(function($) {
    'use strict';

    var sidebar = document.getElementById('portal-sidebar');
    var overlay = document.getElementById('portal-sidebar-overlay');
    var openBtn = document.getElementById('portal-sidebar-open');
    var closeBtn = document.getElementById('portal-sidebar-close');

    function openSidebar() {
        if (sidebar) sidebar.classList.remove('-translate-x-full');
        if (overlay) overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.add('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    if (openBtn) openBtn.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Dropdown menus
    $(document).on('click', '[data-portal-dropdown-trigger]', function(e) {
        e.stopPropagation();
        var wrap = $(this).closest('[data-portal-dropdown-wrap]');
        var menu = wrap.find('[data-portal-dropdown]');
        $('[data-portal-dropdown]').not(menu).addClass('hidden');
        menu.toggleClass('hidden');
    });

    $(document).on('click', function() {
        $('[data-portal-dropdown]').addClass('hidden');
    });

    // Sidebar nav toggles
    $(document).on('click', '[data-portal-nav-toggle]', function() {
        var target = $('#' + $(this).data('target'));
        target.toggleClass('hidden');
        $(this).find('[data-portal-chevron]').toggleClass('rotate-180');
    });

    // Portal tabs (replaces Bootstrap tabs)
    var tabActiveClasses = 'text-primary border-primary';
    $(document).on('click', '[data-portal-tab]', function(e) {
        e.preventDefault();
        var tabId = $(this).data('portal-tab');
        var group = $(this).closest('[data-portal-tab-group]');
        group.find('[data-portal-tab]').removeClass('text-primary border-primary');
        $(this).addClass('text-primary border-primary');
        group.find('[data-portal-tab-pane]').addClass('hidden');
        group.find('[data-portal-tab-pane="' + tabId + '"]').removeClass('hidden');
    });

    // Bootstrap tab shim for legacy markup
    function initPortalTabs() {
        $('.tabs-custom .tab-content .tab-pane').each(function() {
            if (!$(this).hasClass('active')) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    }

    initPortalTabs();

    $(document).on('click', 'a[data-toggle="tab"]', function(e) {
        e.preventDefault();
        var $link = $(this);
        var href = $link.attr('href');
        var $tabContent = $link.closest('.tabs-custom, .portal-tabs-wrap').find('.tab-content').first();
        $link.closest('ul').find('li').removeClass('active');
        $link.parent('li').addClass('active');
        $tabContent.find('.tab-pane').removeClass('active').hide();
        if (href && href.charAt(0) === '#') {
            $tabContent.find(href).addClass('active').show();
        }
        $link.trigger('shown.bs.tab');
    });

    // Modal dismiss
    $(document).on('click', '.modal-dismiss, [data-portal-modal-close]', function() {
        $.magnificPopup.close();
    });

    // Init select2 with portal styling
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

})(jQuery);
