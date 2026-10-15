(function($) {
    'use strict';

    $('#togglePassword').on('click', function() {
        var input = $('#login-password');
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#loginForm, #forgotForm').on('submit', function() {
        var btn = $(this).find('#btn_submit');
        btn.prop('disabled', true).addClass('opacity-75');
    });

    if (typeof login_slider_images !== 'undefined' && login_slider_images.length > 0) {
        $('.auth-brand-panel .absolute.inset-0').backstretch(login_slider_images, { duration: 5000, fade: 750 });
    }
})(jQuery);
