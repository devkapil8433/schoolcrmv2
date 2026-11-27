/**
 * Question editor on-screen keyboard — English + Hindi (Devanagari).
 */
(function (window, $) {
	'use strict';

	var oskShift = false;
	var oskLang = 'en';

	var EN_ROWS = [
		['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '='],
		['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', '[', ']'],
		['a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', ';', "'"],
		['z', 'x', 'c', 'v', 'b', 'n', 'm', ',', '.', '/']
	];
	var EN_SHIFT = [
		['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+'],
		['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', '{', '}'],
		['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', ':', '"'],
		['Z', 'X', 'C', 'V', 'B', 'N', 'M', '<', '>', '?']
	];

	var HI_VOWELS = ['अ', 'आ', 'इ', 'ई', 'उ', 'ऊ', 'ए', 'ऐ', 'ओ', 'औ', 'अं', 'अः'];
	var HI_MATRAS = ['ा', 'ि', 'ी', 'ु', 'ू', 'े', 'ै', 'ो', 'ौ', 'ं', 'ँ', 'ः', '्', '़'];
	var HI_CONS = [
		['क', 'ख', 'ग', 'घ', 'ङ', 'च', 'छ', 'ज', 'झ', 'ञ'],
		['ट', 'ठ', 'ड', 'ढ', 'ण', 'त', 'थ', 'द', 'ध', 'न'],
		['प', 'फ', 'ब', 'भ', 'म', 'य', 'र', 'ल', 'व', 'श'],
		['ष', 'स', 'ह', 'क्ष', 'त्र', 'ज्ञ', 'श्र', 'ड़', 'ढ़', 'फ़']
	];
	var HI_NUMS = ['१', '२', '३', '४', '५', '६', '७', '८', '९', '०', '।', '॥'];
	var HI_PUNCT = ['-', ',', '.', '?', '!', ':', ';', '"', "'", '(', ')', '…'];

	function currentLang() {
		if (typeof window.getQuestionEditorLanguage === 'function') {
			return window.getQuestionEditorLanguage() === 'hi' ? 'hi' : 'en';
		}
		return oskLang;
	}

	function ensureOskStyles() {
		if ($('#qi-osk-style').length) {
			return;
		}
		$('head').append(
			'<style id="qi-osk-style">'
			+ '#qiOskPanel{position:fixed;left:50%;bottom:16px;top:auto;right:auto;transform:translateX(-50%);z-index:12000;'
			+ 'width:min(920px,96vw);min-width:320px;min-height:180px;max-width:98vw;max-height:90vh;'
			+ '--qi-key-h:36px;--qi-key-min:34px;--qi-key-font:15px;--qi-key-gap:5px;--qi-space-min:220px;'
			+ '--qi-bg:#0f172a;--qi-fg:#e2e8f0;--qi-muted:#94a3b8;--qi-border:rgba(148,163,184,.25);'
			+ '--qi-key-bg:#1e293b;--qi-key-fg:#f8fafc;--qi-key-hover:#334155;--qi-key-active:#0088cc;'
			+ '--qi-matra:#134e4a;--qi-shadow:0 18px 50px rgba(15,23,42,.45);--qi-resize:#64748b;'
			+ '--qi-btn-bg:#1e293b;--qi-btn-fg:#e2e8f0;--qi-btn-border:#334155;'
			+ 'background:var(--qi-bg);color:var(--qi-fg);border-radius:14px;box-shadow:var(--qi-shadow);'
			+ 'border:1px solid var(--qi-border);padding:12px 14px 18px;box-sizing:border-box;overflow:auto;}'
			+ '#qiOskPanel.qi-osk-light{'
			+ '--qi-bg:#f8fafc;--qi-fg:#0f172a;--qi-muted:#64748b;--qi-border:rgba(15,23,42,.12);'
			+ '--qi-key-bg:#ffffff;--qi-key-fg:#0f172a;--qi-key-hover:#e2e8f0;--qi-key-active:#0088cc;'
			+ '--qi-matra:#ccfbf1;--qi-shadow:0 18px 50px rgba(15,23,42,.18);--qi-resize:#94a3b8;'
			+ '--qi-btn-bg:#fff;--qi-btn-fg:#0f172a;--qi-btn-border:#cbd5e1;}'
			+ '#qiOskPanel.qi-osk-moved{transform:none;}'
			+ '#qiOskPanel .qi-osk-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;flex-wrap:wrap;'
			+ 'cursor:move;touch-action:none;user-select:none;padding:4px 2px 8px;border-bottom:1px solid var(--qi-border);}'
			+ '#qiOskPanel .qi-osk-title{font-weight:700;font-size:13px;display:flex;align-items:center;gap:8px;}'
			+ '#qiOskPanel .qi-osk-drag-hint{font-size:11px;font-weight:500;color:var(--qi-muted);}'
			+ '#qiOskPanel .qi-osk-actions{display:flex;gap:6px;flex-wrap:wrap;cursor:default;}'
			+ '#qiOskPanel .qi-osk-actions .btn{padding:3px 10px;cursor:pointer;'
			+ 'background:var(--qi-btn-bg);color:var(--qi-btn-fg);border-color:var(--qi-btn-border);}'
			+ '#qiOskPanel .qi-osk-actions .btn-danger{background:#dc2626;border-color:#dc2626;color:#fff;}'
			+ '#qiOskPanel .qi-osk-lang.active{background:#0088cc;border-color:#0088cc;color:#fff;}'
			+ '#qiOskPanel .qi-osk-lang.hi.active{background:#0f766e;border-color:#0f766e;}'
			+ '#qiOskPanel #qiOskShift.active{background:#0088cc;border-color:#0088cc;color:#fff;}'
			+ '#qiOskPanel.qi-osk-dragging,#qiOskPanel.qi-osk-resizing{opacity:.92;}'
			+ '#qiOskResize{position:absolute;right:4px;bottom:4px;width:18px;height:18px;cursor:nwse-resize;touch-action:none;'
			+ 'border-radius:4px;background:linear-gradient(135deg,transparent 50%,var(--qi-resize) 50%,var(--qi-resize) 60%,transparent 60%,'
			+ 'transparent 70%,var(--qi-resize) 70%,var(--qi-resize) 80%,transparent 80%);opacity:.85;}'
			+ '#qiOskResize:hover{opacity:1;}'
			+ '#qiOskKeys{user-select:none;}'
			+ '#qiOskKeys .qi-osk-row{display:flex;flex-wrap:wrap;gap:var(--qi-key-gap);justify-content:center;margin-bottom:6px;}'
			+ '#qiOskKeys .qi-osk-key{min-width:var(--qi-key-min);height:var(--qi-key-h);padding:0 8px;border:1px solid var(--qi-border);border-radius:8px;'
			+ 'background:var(--qi-key-bg);color:var(--qi-key-fg);font-size:var(--qi-key-font);font-weight:600;line-height:var(--qi-key-h);cursor:pointer;}'
			+ '#qiOskKeys .qi-osk-key:hover{background:var(--qi-key-hover);}'
			+ '#qiOskKeys .qi-osk-key:active{background:var(--qi-key-active);color:#fff;border-color:var(--qi-key-active);}'
			+ '#qiOskKeys .qi-osk-key.wide{min-width:calc(var(--qi-key-min) * 2.1);font-size:calc(var(--qi-key-font) * 0.85);}'
			+ '#qiOskKeys .qi-osk-key.space{min-width:var(--qi-space-min);}'
			+ '#qiOskKeys .qi-osk-key.matra{background:var(--qi-matra);}'
			+ '#qiOskPanel.qi-osk-light #qiOskKeys .qi-osk-key.matra{color:#0f766e;}'
			+ '#qiOskKeys .qi-osk-label{width:100%;text-align:left;font-size:11px;color:var(--qi-muted);margin:4px 0 2px 4px;}'
			+ '#qiOskKeys.qi-osk-hi .qi-osk-key{font-family:"Noto Sans Devanagari","Nirmala UI",Mangal,sans-serif;}'
			+ '</style>'
		);
	}

	function ensureOskPanel() {
		if ($('#qiOskPanel').length) {
			return;
		}
		ensureOskStyles();
		if (typeof window.ensureHindiWebFont !== 'function') {
			// load Devanagari web font for keyboard labels if not already present
			if (!$('#qi-hindi-webfont').length) {
				$('head').append('<link id="qi-hindi-webfont" rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;600;700&display=swap">');
			}
		}
		var html = ''
			+ '<div id="qiOskPanel" style="display:none;">'
			+ '  <div class="qi-osk-head" id="qiOskDragHandle" title="Drag to move">'
			+ '    <div class="qi-osk-title"><i class="fas fa-up-down-left-right"></i> <span>On-screen keyboard</span>'
			+ '      <span class="qi-osk-drag-hint">Drag to move · corner to resize</span></div>'
			+ '    <div class="qi-osk-actions">'
			+ '      <button type="button" class="btn btn-default btn-xs qi-osk-lang" data-lang="en">EN</button>'
			+ '      <button type="button" class="btn btn-default btn-xs qi-osk-lang hi" data-lang="hi">हिं</button>'
			+ '      <button type="button" class="btn btn-default btn-xs" id="qiOskShift">Shift</button>'
			+ '      <button type="button" class="btn btn-default btn-xs" id="qiOskTheme" title="Toggle dark / light"><i class="fas fa-sun"></i></button>'
			+ '      <button type="button" class="btn btn-default btn-xs" id="qiOskResetPos" title="Reset position & size"><i class="fas fa-crosshairs"></i></button>'
			+ '      <button type="button" class="btn btn-danger btn-xs" id="qiOskClose"><i class="fas fa-times"></i></button>'
			+ '    </div>'
			+ '  </div>'
			+ '  <div id="qiOskKeys"></div>'
			+ '  <div id="qiOskResize" title="Drag to resize"></div>'
			+ '</div>';
		$('body').append(html);
		applyOskTheme(loadOskTheme());
		bindOskDrag();
		bindOskResize();

		$(document).on('click', '#qiOskKeys .qi-osk-key', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var action = $(this).data('action');
			var ch = $(this).data('char');
			if (action === 'backspace') {
				oskBackspace();
			} else if (action === 'enter') {
				oskInsert('\n');
			} else if (action === 'space') {
				oskInsert(' ');
			} else if (action === 'shift') {
				oskShift = !oskShift;
				$('#qiOskShift').toggleClass('active', oskShift);
				renderOskKeys();
			} else if (typeof ch === 'string') {
				oskInsert(ch);
				if (oskShift && oskLang === 'en') {
					oskShift = false;
					$('#qiOskShift').removeClass('active');
					renderOskKeys();
				}
			}
		});

		$(document).on('click', '#qiOskPanel .qi-osk-lang', function (e) {
			e.preventDefault();
			var lang = $(this).data('lang') === 'hi' ? 'hi' : 'en';
			oskLang = lang;
			oskShift = false;
			$('#qiOskShift').removeClass('active').toggle(lang === 'en');
			if (typeof window.setQuestionEditorLanguage === 'function') {
				window.setQuestionEditorLanguage(lang);
			}
			renderOskKeys();
		});

		$('#qiOskShift').on('click', function () {
			if (oskLang !== 'en') {
				return;
			}
			oskShift = !oskShift;
			$(this).toggleClass('active', oskShift);
			renderOskKeys();
		});

		$('#qiOskClose').on('click', function () {
			hideQuestionOsk();
		});

		$('#qiOskResetPos').on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			resetOskPosition();
		});

		$('#qiOskTheme').on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var next = $('#qiOskPanel').hasClass('qi-osk-light') ? 'dark' : 'light';
			applyOskTheme(next);
		});
	}

	function loadOskTheme() {
		try {
			var t = window.localStorage.getItem('qi_osk_theme');
			return t === 'light' ? 'light' : 'dark';
		} catch (e) {
			return 'dark';
		}
	}

	function saveOskTheme(theme) {
		try {
			window.localStorage.setItem('qi_osk_theme', theme === 'light' ? 'light' : 'dark');
		} catch (e) { /* ignore */ }
	}

	function applyOskTheme(theme) {
		var light = theme === 'light';
		var $panel = $('#qiOskPanel');
		$panel.toggleClass('qi-osk-light', light);
		var $btn = $('#qiOskTheme');
		if ($btn.length) {
			$btn.attr('title', light ? 'Switch to dark mode' : 'Switch to light mode')
				.html(light ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>');
		}
		saveOskTheme(light ? 'light' : 'dark');
	}

	function saveOskPosition(left, top) {
		try {
			var prev = loadOskLayout() || {};
			prev.left = left;
			prev.top = top;
			window.localStorage.setItem('qi_osk_layout', JSON.stringify(prev));
		} catch (e) { /* ignore */ }
	}

	function saveOskSize(width, height) {
		try {
			var prev = loadOskLayout() || {};
			prev.width = width;
			prev.height = height;
			window.localStorage.setItem('qi_osk_layout', JSON.stringify(prev));
		} catch (e) { /* ignore */ }
	}

	function loadOskLayout() {
		try {
			var raw = window.localStorage.getItem('qi_osk_layout');
			if (!raw) {
				// migrate old key
				raw = window.localStorage.getItem('qi_osk_pos');
			}
			if (!raw) {
				return null;
			}
			return JSON.parse(raw);
		} catch (e) {
			return null;
		}
	}

	function loadOskPosition() {
		var pos = loadOskLayout();
		if (!pos || typeof pos.left !== 'number' || typeof pos.top !== 'number') {
			return null;
		}
		return { left: pos.left, top: pos.top };
	}

	function clampOskPosition(left, top, $panel) {
		var w = $panel.outerWidth() || 320;
		var h = $panel.outerHeight() || 200;
		var maxL = Math.max(8, window.innerWidth - w - 8);
		var maxT = Math.max(8, window.innerHeight - h - 8);
		left = Math.min(Math.max(8, left), maxL);
		top = Math.min(Math.max(8, top), maxT);
		return { left: left, top: top };
	}

	function clampOskSize(width, height) {
		var minW = 320;
		var minH = 180;
		var maxW = Math.max(minW, window.innerWidth - 16);
		var maxH = Math.max(minH, window.innerHeight - 16);
		width = Math.min(Math.max(minW, width), maxW);
		height = Math.min(Math.max(minH, height), maxH);
		return { width: width, height: height };
	}

	function applyOskKeyScale(width) {
		var base = 900;
		var scale = Math.min(1.35, Math.max(0.72, width / base));
		$('#qiOskPanel').css({
			'--qi-key-h': Math.round(36 * scale) + 'px',
			'--qi-key-min': Math.round(34 * scale) + 'px',
			'--qi-key-font': Math.round(15 * scale) + 'px',
			'--qi-key-gap': Math.max(3, Math.round(5 * scale)) + 'px',
			'--qi-space-min': Math.round(220 * scale) + 'px'
		});
	}

	function applyOskSize(width, height) {
		var $panel = $('#qiOskPanel');
		if (!$panel.length) {
			return;
		}
		var size = clampOskSize(width, height);
		$panel.css({
			width: size.width + 'px',
			height: size.height + 'px',
			maxWidth: '98vw',
			maxHeight: '90vh'
		});
		applyOskKeyScale(size.width);
		saveOskSize(size.width, size.height);
		// keep on-screen after resize
		if ($panel.hasClass('qi-osk-moved')) {
			var left = parseFloat($panel.css('left')) || 8;
			var top = parseFloat($panel.css('top')) || 8;
			applyOskPosition(left, top);
		}
	}

	function applyOskPosition(left, top) {
		var $panel = $('#qiOskPanel');
		if (!$panel.length) {
			return;
		}
		var pos = clampOskPosition(left, top, $panel);
		$panel.addClass('qi-osk-moved').css({
			left: pos.left + 'px',
			top: pos.top + 'px',
			right: 'auto',
			bottom: 'auto',
			transform: 'none'
		});
		saveOskPosition(pos.left, pos.top);
	}

	function resetOskPosition() {
		try {
			window.localStorage.removeItem('qi_osk_layout');
			window.localStorage.removeItem('qi_osk_pos');
		} catch (e) { /* ignore */ }
		$('#qiOskPanel').removeClass('qi-osk-moved qi-osk-dragging qi-osk-resizing').css({
			left: '50%',
			top: 'auto',
			right: 'auto',
			bottom: '16px',
			transform: 'translateX(-50%)',
			width: '',
			height: '',
			'--qi-key-h': '36px',
			'--qi-key-min': '34px',
			'--qi-key-font': '15px',
			'--qi-key-gap': '5px',
			'--qi-space-min': '220px'
		});
	}

	function restoreOskPosition() {
		var layout = loadOskLayout();
		if (layout && typeof layout.width === 'number') {
			applyOskSize(layout.width, typeof layout.height === 'number' ? layout.height : Math.round(layout.width * 0.55));
		}
		if (layout && typeof layout.left === 'number' && typeof layout.top === 'number') {
			applyOskPosition(layout.left, layout.top);
			return;
		}
		if (!layout || typeof layout.width !== 'number') {
			resetOskPosition();
		}
	}

	function bindOskDrag() {
		var dragging = false;
		var startX = 0;
		var startY = 0;
		var origLeft = 0;
		var origTop = 0;

		function pointerDown(e) {
			var $t = $(e.target);
			if ($t.closest('.qi-osk-actions').length) {
				return;
			}
			var $panel = $('#qiOskPanel');
			if (!$panel.is(':visible')) {
				return;
			}
			var point = e.type.indexOf('touch') === 0
				? (e.originalEvent.touches[0] || e.originalEvent.changedTouches[0])
				: e;
			if (!point) {
				return;
			}
			e.preventDefault();
			dragging = true;
			$panel.addClass('qi-osk-dragging');

			if (!$panel.hasClass('qi-osk-moved')) {
				var rect = $panel[0].getBoundingClientRect();
				$panel.addClass('qi-osk-moved').css({
					left: rect.left + 'px',
					top: rect.top + 'px',
					right: 'auto',
					bottom: 'auto',
					transform: 'none'
				});
			}

			startX = point.clientX;
			startY = point.clientY;
			origLeft = parseFloat($panel.css('left')) || 0;
			origTop = parseFloat($panel.css('top')) || 0;
		}

		function pointerMove(e) {
			if (!dragging) {
				return;
			}
			var point = e.type.indexOf('touch') === 0
				? (e.originalEvent.touches[0] || e.originalEvent.changedTouches[0])
				: e;
			if (!point) {
				return;
			}
			e.preventDefault();
			applyOskPosition(origLeft + (point.clientX - startX), origTop + (point.clientY - startY));
		}

		function pointerUp() {
			if (!dragging) {
				return;
			}
			dragging = false;
			$('#qiOskPanel').removeClass('qi-osk-dragging');
		}

		$(document).on('mousedown.qiOskDrag', '#qiOskDragHandle', pointerDown);
		$(document).on('touchstart.qiOskDrag', '#qiOskDragHandle', pointerDown);
		$(document).on('mousemove.qiOskDrag touchmove.qiOskDrag', pointerMove);
		$(document).on('mouseup.qiOskDrag touchend.qiOskDrag touchcancel.qiOskDrag', pointerUp);
	}

	function bindOskResize() {
		var resizing = false;
		var startX = 0;
		var startY = 0;
		var origW = 0;
		var origH = 0;

		function pinPanelBeforeResize($panel) {
			if ($panel.hasClass('qi-osk-moved')) {
				return;
			}
			var rect = $panel[0].getBoundingClientRect();
			$panel.addClass('qi-osk-moved').css({
				left: rect.left + 'px',
				top: rect.top + 'px',
				right: 'auto',
				bottom: 'auto',
				transform: 'none'
			});
		}

		function pointerDown(e) {
			var $panel = $('#qiOskPanel');
			if (!$panel.is(':visible')) {
				return;
			}
			var point = e.type.indexOf('touch') === 0
				? (e.originalEvent.touches[0] || e.originalEvent.changedTouches[0])
				: e;
			if (!point) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();
			resizing = true;
			pinPanelBeforeResize($panel);
			$panel.addClass('qi-osk-resizing');
			startX = point.clientX;
			startY = point.clientY;
			origW = $panel.outerWidth();
			origH = $panel.outerHeight();
		}

		function pointerMove(e) {
			if (!resizing) {
				return;
			}
			var point = e.type.indexOf('touch') === 0
				? (e.originalEvent.touches[0] || e.originalEvent.changedTouches[0])
				: e;
			if (!point) {
				return;
			}
			e.preventDefault();
			applyOskSize(origW + (point.clientX - startX), origH + (point.clientY - startY));
		}

		function pointerUp() {
			if (!resizing) {
				return;
			}
			resizing = false;
			$('#qiOskPanel').removeClass('qi-osk-resizing');
		}

		$(document).on('mousedown.qiOskResize', '#qiOskResize', pointerDown);
		$(document).on('touchstart.qiOskResize', '#qiOskResize', pointerDown);
		$(document).on('mousemove.qiOskResize touchmove.qiOskResize', pointerMove);
		$(document).on('mouseup.qiOskResize touchend.qiOskResize touchcancel.qiOskResize', pointerUp);
	}

	function keyBtn(ch, cls) {
		cls = cls || '';
		return '<button type="button" class="qi-osk-key ' + cls + '" data-char="' + escapeAttr(ch) + '">' + escapeHtml(ch) + '</button>';
	}

	function actionBtn(action, label, cls) {
		return '<button type="button" class="qi-osk-key wide ' + (cls || '') + '" data-action="' + action + '">' + escapeHtml(label) + '</button>';
	}

	function escapeHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function escapeAttr(str) {
		return escapeHtml(str).replace(/'/g, '&#39;');
	}

	function renderOskKeys() {
		var $box = $('#qiOskKeys');
		if (!$box.length) {
			return;
		}
		$('#qiOskPanel .qi-osk-lang').removeClass('active');
		$('#qiOskPanel .qi-osk-lang[data-lang="' + oskLang + '"]').addClass('active');
		$box.toggleClass('qi-osk-hi', oskLang === 'hi');
		$('#qiOskShift').toggle(oskLang === 'en');

		var html = '';
		if (oskLang === 'en') {
			var rows = oskShift ? EN_SHIFT : EN_ROWS;
			for (var r = 0; r < rows.length; r++) {
				html += '<div class="qi-osk-row">';
				for (var c = 0; c < rows[r].length; c++) {
					html += keyBtn(rows[r][c]);
				}
				html += '</div>';
			}
			html += '<div class="qi-osk-row">'
				+ actionBtn('shift', oskShift ? 'SHIFT ON' : 'Shift')
				+ actionBtn('space', 'Space', 'space')
				+ actionBtn('backspace', '⌫')
				+ actionBtn('enter', 'Enter')
				+ '</div>';
		} else {
			html += '<div class="qi-osk-label">स्वर (Vowels)</div><div class="qi-osk-row">';
			for (var v = 0; v < HI_VOWELS.length; v++) {
				html += keyBtn(HI_VOWELS[v]);
			}
			html += '</div>';
			html += '<div class="qi-osk-label">मात्रा (Matras)</div><div class="qi-osk-row">';
			for (var m = 0; m < HI_MATRAS.length; m++) {
				html += keyBtn(HI_MATRAS[m], 'matra');
			}
			html += '</div>';
			html += '<div class="qi-osk-label">व्यंजन (Consonants)</div>';
			for (var hr = 0; hr < HI_CONS.length; hr++) {
				html += '<div class="qi-osk-row">';
				for (var hc = 0; hc < HI_CONS[hr].length; hc++) {
					html += keyBtn(HI_CONS[hr][hc]);
				}
				html += '</div>';
			}
			html += '<div class="qi-osk-label">संख्या / विराम</div><div class="qi-osk-row">';
			for (var n = 0; n < HI_NUMS.length; n++) {
				html += keyBtn(HI_NUMS[n]);
			}
			html += '</div><div class="qi-osk-row">';
			for (var p = 0; p < HI_PUNCT.length; p++) {
				html += keyBtn(HI_PUNCT[p]);
			}
			html += '</div>';
			html += '<div class="qi-osk-row">'
				+ actionBtn('space', 'Space', 'space')
				+ actionBtn('backspace', '⌫')
				+ actionBtn('enter', 'Enter')
				+ '</div>';
		}
		$box.html(html);
	}

	function getActiveTarget() {
		if (window.onlineExamImagePickerState && onlineExamImagePickerState.activeEditor
			&& onlineExamImagePickerState.activeEditor.length
			&& typeof onlineExamImagePickerState.activeEditor.summernote === 'function') {
			return { type: 'summernote', $el: onlineExamImagePickerState.activeEditor };
		}
		var $ae = $(document.activeElement);
		if ($ae.is('textarea, input[type="text"]')) {
			return { type: 'input', $el: $ae };
		}
		var $ans = $('.qt-form-panel:visible textarea[name="answer"], .tab-pane.active textarea[name="answer"]').filter(':visible').first();
		if ($ans.length) {
			return { type: 'input', $el: $ans };
		}
		var $note = $('.qt-form-panel:visible .question_note, .tab-pane.active .question_note').filter(function () {
			return $(this).next('.note-editor').is(':visible');
		}).first();
		if ($note.length && typeof $note.summernote === 'function') {
			return { type: 'summernote', $el: $note };
		}
		return null;
	}

	function oskInsert(text) {
		var target = getActiveTarget();
		if (!target) {
			return;
		}
		if (target.type === 'summernote') {
			if (text === '\n') {
				target.$el.summernote('pasteHTML', '<br>');
			} else if (typeof target.$el.summernote === 'function') {
				try {
					target.$el.summernote('insertText', text);
				} catch (e1) {
					try {
						target.$el.summernote('editor.insertText', text);
					} catch (e2) {
						target.$el.summernote('pasteHTML', escapeHtml(text));
					}
				}
			}
			return;
		}
		var el = target.$el.get(0);
		var start = el.selectionStart || 0;
		var end = el.selectionEnd || 0;
		var val = el.value || '';
		el.value = val.slice(0, start) + text + val.slice(end);
		var pos = start + text.length;
		el.setSelectionRange(pos, pos);
		target.$el.trigger('input').focus();
	}

	function oskBackspace() {
		var target = getActiveTarget();
		if (!target) {
			return;
		}
		if (target.type === 'summernote') {
			document.execCommand('delete', false, null);
			return;
		}
		var el = target.$el.get(0);
		var start = el.selectionStart || 0;
		var end = el.selectionEnd || 0;
		var val = el.value || '';
		if (start === end && start > 0) {
			el.value = val.slice(0, start - 1) + val.slice(end);
			el.setSelectionRange(start - 1, start - 1);
		} else if (start !== end) {
			el.value = val.slice(0, start) + val.slice(end);
			el.setSelectionRange(start, start);
		}
		target.$el.trigger('input').focus();
	}

	function showQuestionOsk() {
		ensureOskPanel();
		oskLang = currentLang();
		oskShift = false;
		$('#qiOskShift').removeClass('active');
		renderOskKeys();
		$('#qiOskPanel').fadeIn(120);
		restoreOskPosition();
		$('.note-btn-osk').addClass('active');
	}

	function hideQuestionOsk() {
		$('#qiOskPanel').fadeOut(120);
		$('.note-btn-osk').removeClass('active');
	}

	function toggleQuestionOsk() {
		ensureOskPanel();
		if ($('#qiOskPanel').is(':visible')) {
			hideQuestionOsk();
		} else {
			showQuestionOsk();
		}
	}

	// Keep OSK language in sync when toolbar EN/हिं changes
	var _setLang = window.setQuestionEditorLanguage;
	window.setQuestionEditorLanguage = function (lang) {
		if (typeof _setLang === 'function') {
			_setLang(lang);
		}
		oskLang = (lang === 'hi' || lang === 'hindi') ? 'hi' : 'en';
		if ($('#qiOskPanel').is(':visible')) {
			oskShift = false;
			$('#qiOskShift').removeClass('active').toggle(oskLang === 'en');
			renderOskKeys();
		}
	};

	window.showQuestionOsk = showQuestionOsk;
	window.hideQuestionOsk = hideQuestionOsk;
	window.toggleQuestionOsk = toggleQuestionOsk;
})(window, jQuery);
