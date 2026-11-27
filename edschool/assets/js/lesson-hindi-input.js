/**
 * Lesson Hindi input — works on Mac and Windows.
 * Phonetic transliteration (English → Hindi) + OS keyboard tips.
 */
(function (window, $) {
	'use strict';

	var lessonHiControl = null;
	var googleLoading = false;
	var boundToggle = false;

	function isMac() {
		var p = (navigator.platform || '') + ' ' + (navigator.userAgent || '');
		return /Mac|iPhone|iPad|iPod/i.test(p);
	}

	function shortcutLabel() {
		return isMac() ? 'Ctrl+G / ⌘G' : 'Ctrl+G';
	}

	function updateShortcutHints() {
		var label = shortcutLabel();
		$('.lesson-hi-shortcut').text('(' + label + ')');
	}

	function loadGoogleJsapi(cb) {
		if (window.google && google.load) {
			cb();
			return;
		}
		if (googleLoading) {
			var tries = 0;
			var t = setInterval(function () {
				tries++;
				if (window.google && google.load) {
					clearInterval(t);
					cb();
				} else if (tries > 40) {
					clearInterval(t);
				}
			}, 150);
			return;
		}
		googleLoading = true;
		$.getScript('https://www.google.com/jsapi')
			.done(function () {
				cb();
			})
			.fail(function () {
				googleLoading = false;
			});
	}

	function enableLessonTransliteration() {
		if (!(window.google && google.elements && google.elements.transliteration)) {
			return;
		}
		var options = {
			sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
			destinationLanguage: [google.elements.transliteration.LanguageCode.HINDI],
			// Contol key works on both Windows and Mac browsers
			shortcutKey: 'ctrl+g',
			transliterationEnabled: true
		};
		if (!lessonHiControl) {
			lessonHiControl = new google.elements.transliteration.TransliterationControl(options);
			lessonHiControl.makeTransliteratable(['lesson_no', 'lesson_name']);
		} else if (typeof lessonHiControl.enableTransliteration === 'function') {
			lessonHiControl.enableTransliteration();
		}
	}

	function initLessonHindiInput(enable) {
		$('.lesson-hi-input').attr('lang', enable ? 'hi' : 'en');
		if (!enable) {
			if (lessonHiControl && typeof lessonHiControl.disableTransliteration === 'function') {
				lessonHiControl.disableTransliteration();
			}
			return;
		}
		if (window.google && google.elements && google.elements.transliteration) {
			enableLessonTransliteration();
			return;
		}
		loadGoogleJsapi(function () {
			if (!(window.google && google.load)) {
				return;
			}
			google.load('elements', '1', {
				packages: 'transliteration',
				callback: enableLessonTransliteration
			});
		});
	}

	function bindMacCmdGToggle() {
		if (boundToggle) {
			return;
		}
		boundToggle = true;
		$(document).on('keydown.lessonHindi', function (e) {
			// Mac: ⌘G should also toggle (Google API uses Ctrl+G)
			var isToggle = (e.ctrlKey || e.metaKey) && !e.altKey && !e.shiftKey && (e.key === 'g' || e.key === 'G' || e.keyCode === 71);
			if (!isToggle) {
				return;
			}
			var $chk = $('#lesson_hindi_input');
			if (!$chk.length) {
				return;
			}
			// Only intercept when focus is in lesson fields or checkbox area
			var $t = $(e.target);
			if (!$t.is('.lesson-hi-input, #lesson_hindi_input') && !$t.closest('.lesson-hi-wrap').length) {
				return;
			}
			e.preventDefault();
			if (lessonHiControl && typeof lessonHiControl.toggleTransliteration === 'function') {
				lessonHiControl.toggleTransliteration();
				var on = typeof lessonHiControl.isTransliterationEnabled === 'function'
					? lessonHiControl.isTransliterationEnabled()
					: $chk.is(':checked');
				$chk.prop('checked', !!on);
				$('.lesson-hi-input').attr('lang', on ? 'hi' : 'en');
			} else {
				$chk.prop('checked', !$chk.is(':checked')).trigger('change');
			}
		});
	}

	function bootLessonHindiInput() {
		if (!$('#lesson_hindi_input').length) {
			return;
		}
		updateShortcutHints();
		bindMacCmdGToggle();
		initLessonHindiInput($('#lesson_hindi_input').is(':checked'));
		$('#lesson_hindi_input').off('change.lessonHindi').on('change.lessonHindi', function () {
			initLessonHindiInput($(this).is(':checked'));
		});
	}

	window.initLessonHindiInput = initLessonHindiInput;
	window.bootLessonHindiInput = bootLessonHindiInput;

	$(bootLessonHindiInput);
})(window, jQuery);
