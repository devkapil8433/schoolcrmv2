/**
 * Question editor language switch — English / Hindi phonetic typing.
 * On Hindi: checks system Devanagari fonts, auto-loads web font, shows install help if needed.
 */
(function (window, $) {
	'use strict';

	var qHiControl = null;
	var googleLoading = false;
	var boundToggle = false;
	var idSeq = 0;
	var currentLang = 'en'; // 'en' | 'hi'
	var webFontLoading = false;
	var webFontReady = false;
	var fontGuideShown = false;
	var SYSTEM_HINDI_FONTS = [
		'Nirmala UI',
		'Mangal',
		'Kokila',
		'Aparajita',
		'Arial Unicode MS',
		'Devanagari Sangam MN',
		'Kohinoor Devanagari',
		'ITF Devanagari',
		'Noto Sans Devanagari'
	];

	function isMac() {
		var p = (navigator.platform || '') + ' ' + (navigator.userAgent || '');
		return /Mac|iPhone|iPad|iPod/i.test(p);
	}

	function isWindows() {
		return /Win/i.test(navigator.platform || '') || /Windows/i.test(navigator.userAgent || '');
	}

	function syncUi() {
		var hi = currentLang === 'hi';
		$('.note-btn-lang-en').toggleClass('active', !hi);
		$('.note-btn-lang-hi').toggleClass('active', hi);
		scopeRoot().find('.note-editable, .question-hi-input, textarea[name="answer"]')
			.attr('lang', hi ? 'hi' : 'en')
			.toggleClass('qi-hindi-font', hi);
	}

	function ensureHindiFontStyles() {
		if ($('#qi-lang-switch-style').length) {
			return;
		}
		$('head').append(
			'<style id="qi-lang-switch-style">'
			+ '.note-btn-lang-en,.note-btn-lang-hi{min-width:34px;font-weight:700;font-size:12px;}'
			+ '.note-btn-lang-en.active,.note-btn-lang-hi.active{background:#0088cc!important;color:#fff!important;border-color:#0088cc!important;}'
			+ '.note-btn-lang-hi.active{background:#0f766e!important;border-color:#0f766e!important;}'
			+ '.note-editable.qi-hindi-font,.question-hi-input.qi-hindi-font,textarea[name="answer"].qi-hindi-font,'
			+ '.note-editable[lang="hi"],.question-hi-input[lang="hi"]{'
			+ 'font-family:"Noto Sans Devanagari","Nirmala UI",Mangal,"Kohinoor Devanagari","Devanagari Sangam MN",sans-serif!important;'
			+ '}'
			+ '</style>'
		);
	}

	/**
	 * Detect if a font family is available on this device.
	 */
	function isFontAvailable(fontName) {
		if (!fontName) {
			return false;
		}
		if (document.fonts && typeof document.fonts.check === 'function') {
			try {
				if (document.fonts.check('16px "' + fontName + '"')) {
					return true;
				}
			} catch (e) { /* ignore */ }
		}
		// Canvas fallback width test
		var canvas = document.createElement('canvas');
		var ctx = canvas.getContext('2d');
		if (!ctx) {
			return false;
		}
		var sample = 'नमस्तेअआइईउऊ';
		ctx.font = '72px monospace';
		var base = ctx.measureText(sample).width;
		ctx.font = '72px "' + fontName + '", monospace';
		var withFont = ctx.measureText(sample).width;
		return Math.abs(withFont - base) > 0.5;
	}

	function detectSystemHindiFonts() {
		var found = [];
		for (var i = 0; i < SYSTEM_HINDI_FONTS.length; i++) {
			if (isFontAvailable(SYSTEM_HINDI_FONTS[i])) {
				found.push(SYSTEM_HINDI_FONTS[i]);
			}
		}
		return found;
	}

	function canRenderDevanagari() {
		var canvas = document.createElement('canvas');
		var ctx = canvas.getContext('2d');
		if (!ctx) {
			return false;
		}
		// Tofu / missing glyph often collapses width for many Devanagari chars
		var sample = 'क्षज्ञश्र';
		ctx.font = '48px sans-serif';
		var w = ctx.measureText(sample).width;
		return w > 40;
	}

	function ensureHindiWebFont(cb) {
		cb = cb || function () {};
		if (webFontReady) {
			cb(true);
			return;
		}
		if ($('#qi-hindi-webfont').length) {
			waitForWebFont(cb);
			return;
		}
		webFontLoading = true;
		$('head').append(
			'<link id="qi-hindi-webfont" rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;600;700&display=swap">'
		);
		waitForWebFont(cb);
	}

	function waitForWebFont(cb) {
		var done = function (ok) {
			webFontLoading = false;
			webFontReady = !!ok;
			cb(!!ok);
		};
		if (document.fonts && document.fonts.load) {
			document.fonts.load('16px "Noto Sans Devanagari"').then(function () {
				done(document.fonts.check('16px "Noto Sans Devanagari"'));
			}).catch(function () {
				// Still apply family; browser may fall back
				setTimeout(function () { done(true); }, 800);
			});
			return;
		}
		setTimeout(function () { done(true); }, 1000);
	}

	function installStepsHtml() {
		if (isMac()) {
			return ''
				+ '<ol style="padding-left:18px;margin:8px 0;">'
				+ '<li>Open <strong>System Settings → Keyboard → Input Sources</strong></li>'
				+ '<li>Click <strong>+</strong> → search <strong>Hindi</strong></li>'
				+ '<li>Add <strong>Hindi - QWERTY</strong> or <strong>Hindi - Transliteration</strong></li>'
				+ '<li>Switch input from menu bar (or <kbd>Ctrl</kbd>+<kbd>Space</kbd>)</li>'
				+ '</ol>'
				+ '<p class="text-muted" style="font-size:12px;">macOS usually includes Devanagari fonts (Kohinoor / Devanagari Sangam).</p>';
		}
		if (isWindows()) {
			return ''
				+ '<ol style="padding-left:18px;margin:8px 0;">'
				+ '<li>Open <strong>Settings → Time &amp; Language → Language &amp; region</strong></li>'
				+ '<li>Click <strong>Add a language</strong> → install <strong>Hindi</strong></li>'
				+ '<li>Under Hindi → <strong>Language options</strong> → add keyboard <strong>Hindi Phonetic</strong> / Inscript</li>'
				+ '<li>Fonts: Hindi pack installs <strong>Nirmala UI</strong> / <strong>Mangal</strong></li>'
				+ '<li>Optional: download <a href="https://fonts.google.com/noto/specimen/Noto+Sans+Devanagari" target="_blank" rel="noopener">Noto Sans Devanagari</a> and install (.ttf)</li>'
				+ '</ol>';
		}
		return ''
			+ '<ol style="padding-left:18px;margin:8px 0;">'
			+ '<li>Install a Devanagari font such as <strong>Noto Sans Devanagari</strong></li>'
			+ '<li>Enable Hindi input method in your OS language settings</li>'
			+ '<li>Restart the browser after installing fonts</li>'
			+ '</ol>';
	}

	function ensureHindiFontGuideModal() {
		if ($('#qiHindiFontModal').length) {
			return;
		}
		var html = ''
			+ '<div class="modal fade" id="qiHindiFontModal" tabindex="-1" role="dialog" aria-hidden="true">'
			+ '  <div class="modal-dialog" role="document">'
			+ '    <div class="modal-content">'
			+ '      <div class="modal-header">'
			+ '        <button type="button" class="close" data-dismiss="modal">&times;</button>'
			+ '        <h4 class="modal-title"><i class="fas fa-font"></i> Hindi font check</h4>'
			+ '      </div>'
			+ '      <div class="modal-body">'
			+ '        <div id="qiHindiFontStatus" class="alert alert-info" style="margin-bottom:12px;"></div>'
			+ '        <p><strong>Auto (this browser):</strong> We load <em>Noto Sans Devanagari</em> web font so Hindi displays correctly even without system fonts.</p>'
			+ '        <p class="mb-sm"><strong>System fonts found:</strong> <span id="qiHindiFontList">—</span></p>'
			+ '        <div id="qiHindiFontStepsWrap">'
			+ '          <p><strong>Install on this computer (optional):</strong></p>'
			+ '          <div id="qiHindiFontSteps"></div>'
			+ '        </div>'
			+ '        <p class="text-muted" style="font-size:12px;margin-bottom:0;">Browsers cannot install OS fonts automatically for security. Typing works with phonetic mode (English → Hindi) without a Hindi keyboard.</p>'
			+ '      </div>'
			+ '      <div class="modal-footer">'
			+ '        <label class="pull-left" style="font-weight:normal;margin-top:6px;"><input type="checkbox" id="qiHindiFontDontShow"> Don\'t show again</label>'
			+ '        <button type="button" class="btn btn-primary" data-dismiss="modal">Continue typing</button>'
			+ '      </div>'
			+ '    </div>'
			+ '  </div>'
			+ '</div>';
		$('body').append(html);
	}

	function showHindiFontGuide(force) {
		var skip = false;
		try {
			skip = window.localStorage.getItem('qi_hindi_font_guide_skip') === '1';
		} catch (e) { /* ignore */ }
		if (!force && (skip || fontGuideShown)) {
			return;
		}
		ensureHindiFontGuideModal();
		var found = detectSystemHindiFonts();
		var renderOk = canRenderDevanagari() || webFontReady || found.length > 0;
		var $status = $('#qiHindiFontStatus');
		if (webFontReady || found.length) {
			$status.removeClass('alert-warning alert-danger').addClass('alert-success')
				.html('<i class="fas fa-check-circle"></i> Hindi is ready. '
					+ (webFontReady ? 'Web font loaded automatically. ' : '')
					+ (found.length ? 'System font(s) available.' : 'Using web font for display.'));
			$('#qiHindiFontStepsWrap').toggle(!found.length);
		} else {
			$status.removeClass('alert-success alert-danger').addClass('alert-warning')
				.html('<i class="fas fa-exclamation-triangle"></i> No clear Hindi system font detected. Loading web font…');
			$('#qiHindiFontStepsWrap').show();
		}
		$('#qiHindiFontList').text(found.length ? found.join(', ') : 'None detected');
		$('#qiHindiFontSteps').html(installStepsHtml());
		$('#qiHindiFontModal').modal('show');
		fontGuideShown = true;

		$('#qiHindiFontDontShow').off('change.qiFont').on('change.qiFont', function () {
			try {
				if ($(this).is(':checked')) {
					window.localStorage.setItem('qi_hindi_font_guide_skip', '1');
				} else {
					window.localStorage.removeItem('qi_hindi_font_guide_skip');
				}
			} catch (e) { /* ignore */ }
		});
	}

	function prepareHindiFonts(showGuide) {
		ensureHindiFontStyles();
		ensureHindiWebFont(function () {
			syncUi();
			if (showGuide) {
				var found = detectSystemHindiFonts();
				// Show install help when system Hindi fonts are missing (web font still auto-applied)
				if (!found.length) {
					showHindiFontGuide(false);
				}
			}
		});
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

	function scopeRoot() {
		var $panel = $('.qt-form-panel:visible').first();
		if ($panel.length) {
			return $panel;
		}
		$panel = $('.tab-pane.active').first();
		if ($panel.length) {
			return $panel;
		}
		return $(document.body);
	}

	function collectTargetIds() {
		var $root = scopeRoot();
		var ids = [];

		$root.find('.note-editable').each(function () {
			if (!$(this).is(':visible')) {
				return;
			}
			var id = this.id;
			if (!id) {
				idSeq += 1;
				id = 'qi_hi_ed_' + idSeq;
				this.id = id;
			}
			ids.push(id);
		});

		$root.find('textarea.question-hi-input, input.question-hi-input, textarea[name="answer"]').each(function () {
			if ($(this).hasClass('question_note') || $(this).next('.note-editor').length) {
				return;
			}
			if (!$(this).is(':visible')) {
				return;
			}
			var id = this.id;
			if (!id) {
				idSeq += 1;
				id = 'qi_hi_ta_' + idSeq;
				this.id = id;
			}
			ids.push(id);
			$(this).addClass('question-hi-input');
		});

		return ids;
	}

	function enableQuestionTransliteration() {
		if (!(window.google && google.elements && google.elements.transliteration)) {
			return;
		}
		var ids = collectTargetIds();
		if (!ids.length) {
			return;
		}
		var options = {
			sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
			destinationLanguage: [google.elements.transliteration.LanguageCode.HINDI],
			shortcutKey: 'ctrl+g',
			transliterationEnabled: true
		};
		qHiControl = new google.elements.transliteration.TransliterationControl(options);
		try {
			qHiControl.makeTransliteratable(ids);
		} catch (e) {
			// ignore
		}
	}

	function initQuestionHindiInput(enable, opts) {
		opts = opts || {};
		currentLang = enable ? 'hi' : 'en';
		syncUi();
		if (!enable) {
			if (qHiControl && typeof qHiControl.disableTransliteration === 'function') {
				qHiControl.disableTransliteration();
			}
			return;
		}
		prepareHindiFonts(opts.checkFont !== false);
		if (window.google && google.elements && google.elements.transliteration) {
			enableQuestionTransliteration();
			return;
		}
		loadGoogleJsapi(function () {
			if (!(window.google && google.load)) {
				return;
			}
			google.load('elements', '1', {
				packages: 'transliteration',
				callback: enableQuestionTransliteration
			});
		});
	}

	function setQuestionEditorLanguage(lang) {
		lang = (lang === 'hi' || lang === 'hindi') ? 'hi' : 'en';
		initQuestionHindiInput(lang === 'hi', { checkFont: lang === 'hi' });
	}

	function getQuestionEditorLanguage() {
		return currentLang;
	}

	function toggleQuestionEditorLanguage() {
		setQuestionEditorLanguage(currentLang === 'hi' ? 'en' : 'hi');
	}

	function refreshQuestionHindiInput() {
		initQuestionHindiInput(currentLang === 'hi', { checkFont: false });
		syncUi();
	}

	function bindMacCmdGToggle() {
		if (boundToggle) {
			return;
		}
		boundToggle = true;
		$(document).on('keydown.questionHindi', function (e) {
			var isToggle = (e.ctrlKey || e.metaKey) && !e.altKey && !e.shiftKey && (e.key === 'g' || e.key === 'G' || e.keyCode === 71);
			if (!isToggle) {
				return;
			}
			var $t = $(e.target);
			if (
				!$t.is('.question-hi-input, .note-btn-lang-en, .note-btn-lang-hi') &&
				!$t.closest('.note-editable, .note-editor').length
			) {
				return;
			}
			e.preventDefault();
			toggleQuestionEditorLanguage();
		});
	}

	function bootQuestionHindiInput() {
		ensureHindiFontStyles();
		bindMacCmdGToggle();
		setTimeout(function () {
			initQuestionHindiInput(currentLang === 'hi', { checkFont: false });
			syncUi();
		}, 300);
	}

	window.initQuestionHindiInput = initQuestionHindiInput;
	window.refreshQuestionHindiInput = refreshQuestionHindiInput;
	window.bootQuestionHindiInput = bootQuestionHindiInput;
	window.setQuestionEditorLanguage = setQuestionEditorLanguage;
	window.getQuestionEditorLanguage = getQuestionEditorLanguage;
	window.toggleQuestionEditorLanguage = toggleQuestionEditorLanguage;
	window.showHindiFontGuide = function () { showHindiFontGuide(true); };

	$(bootQuestionHindiInput);
})(window, jQuery);
