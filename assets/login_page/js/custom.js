(function($) {
	'use strict';

	function getLoginSliderImages() {
		if (typeof login_slider_images !== 'undefined' && login_slider_images.length) {
			return login_slider_images;
		}
		return [
			base_url + 'uploads/login_image/slider_1.jpg',
			base_url + 'uploads/login_image/slider_2.jpg',
			base_url + 'uploads/login_image/slider_3.jpg',
		];
	}

	$(document).ready(function() {
		var sliderImages = getLoginSliderImages();
		var $backdrop = $('#authBackdrop');

		if (sliderImages.length && $backdrop.length) {
			$backdrop.backstretch(sliderImages, { duration: 4000, fade: 900 });
		} else if (sliderImages.length) {
			$.backstretch(sliderImages, { duration: 4000, fade: 900 });
		}

		var $passwordInput = $('#login-password');
		var $toggleBtn = $('#togglePassword');

		if ($toggleBtn.length && $passwordInput.length) {
			$toggleBtn.on('click', function() {
				var isPassword = $passwordInput.attr('type') === 'password';
				$passwordInput.attr('type', isPassword ? 'text' : 'password');
				$(this).find('i').toggleClass('fa-eye fa-eye-slash');
			});
		}

		var $loginForm = $('#loginForm');
		var $submitBtn = $('#btn_submit');

		if ($loginForm.length && $submitBtn.length) {
			$loginForm.on('submit', function() {
				if (!$submitBtn.hasClass('is-loading')) {
					$submitBtn.addClass('is-loading');
				}
			});
		}

		$('.form-control').on('focus', function() {
			$(this).closest('.form-group').addClass('is-focused');
		}).on('blur', function() {
			$(this).closest('.form-group').removeClass('is-focused');
		});
	});

})(jQuery);
