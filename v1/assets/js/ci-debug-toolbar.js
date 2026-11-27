/**
 * Jitendra Debugger toolbar interactions (EdSchool MY_Profiler).
 * Includes CI4-style "Views on page" overlays.
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	function readCookie(name) {
		var nameEQ = name + '=';
		var parts = document.cookie.split(';');
		for (var i = 0; i < parts.length; i++) {
			var c = parts[i];
			while (c.charAt(0) === ' ') c = c.substring(1);
			if (c.indexOf(nameEQ) === 0) {
				return decodeURIComponent(c.substring(nameEQ.length));
			}
		}
		return null;
	}

	function createCookie(name, value, days) {
		var expires = '';
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = '; expires=' + date.toUTCString();
		}
		document.cookie = name + '=' + encodeURIComponent(value || '') + expires + '; path=/; SameSite=Lax';
	}

	/**
	 * CI4 Debug Toolbar view hints — wraps DEBUG-VIEW comments in labeled boxes.
	 */
	function createViewsHintsController(toggleBtn) {
		var nodeList = [];

		var getComments = function () {
			var nodes = [];
			var result = {};
			var xpathResults = document.evaluate(
				"//comment()[starts-with(., ' DEBUG-VIEW')]",
				document,
				null,
				XPathResult.ANY_TYPE,
				null
			);
			var nextNode = xpathResults.iterateNext();
			while (nextNode) {
				nodes.push(nextNode);
				nextNode = xpathResults.iterateNext();
			}

			for (var i = 0; i < nodes.length; ++i) {
				var path = nodes[i].nodeValue.substring(18, nodes[i].nodeValue.length - 1);
				if (nodes[i].nodeValue[12] === 'S') {
					result[path] = [nodes[i], null];
				} else if (result[path]) {
					result[path][1] = nodes[i];
				}
			}
			return result;
		};

		var getParentNode = function (node, targetNode) {
			if (node.parentNode === null) return null;
			if (node.parentNode !== targetNode) return getParentNode(node.parentNode, targetNode);
			return node;
		};

		var INVALID_ELEMENTS = ['NOSCRIPT', 'SCRIPT', 'STYLE'];
		var OUTER_ELEMENTS = ['HTML', 'BODY', 'HEAD'];

		var getValidElementInner = function (node, reverse) {
			if (node === null) return null;
			if (OUTER_ELEMENTS.indexOf(node.nodeName) !== -1) {
				for (var i = 0; i < document.body.children.length; ++i) {
					var index = reverse ? document.body.children.length - (i + 1) : i;
					var element = document.body.children[index];
					if (INVALID_ELEMENTS.indexOf(element.nodeName) !== -1) continue;
					if (element.id === 'toolbarContainer') continue;
					return [element, reverse];
				}
				return null;
			}
			while (node !== null && INVALID_ELEMENTS.indexOf(node.nodeName) !== -1) {
				node = reverse ? node.previousElementSibling : node.nextElementSibling;
			}
			if (node === null) return null;
			return [node, reverse];
		};

		var getValidElement = function (nodeElement) {
			if (nodeElement) {
				if (nodeElement.nextElementSibling !== null) {
					return getValidElementInner(nodeElement.nextElementSibling, false)
						|| getValidElementInner(nodeElement.previousElementSibling, true);
				}
				if (nodeElement.previousElementSibling !== null) {
					return getValidElementInner(nodeElement.previousElementSibling, true);
				}
			}
			return null;
		};

		function setToggleState(active) {
			if (!toggleBtn) return;
			toggleBtn.classList.toggle('is-active', !!active);
			toggleBtn.setAttribute('aria-pressed', active ? 'true' : 'false');
			toggleBtn.textContent = active ? 'Views on page ✓' : 'Views on page';
		}

		function showHints() {
			var sortedComments = getComments();

			for (var key in sortedComments) {
				if (!Object.prototype.hasOwnProperty.call(sortedComments, key)) continue;
				var startElement = getValidElement(sortedComments[key][0]);
				var endElement = getValidElement(sortedComments[key][1]);
				if (startElement === null || endElement === null) continue;

				var jointParent = getParentNode(endElement[0], startElement[0].parentNode);
				if (jointParent === null) {
					jointParent = getParentNode(startElement[0], endElement[0].parentNode);
					if (jointParent === null) continue;
					startElement[0] = jointParent;
				} else {
					endElement[0] = jointParent;
				}

				var debugDiv = document.createElement('div');
				var debugPath = document.createElement('div');
				var childArray = startElement[0].parentNode.childNodes;
				var parent = startElement[0].parentNode;
				var start;
				var end;

				debugDiv.classList.add('debug-view', 'show-view');
				debugPath.classList.add('debug-view-path');
				debugPath.innerText = key;
				debugDiv.appendChild(debugPath);

				for (var i = 0; i < childArray.length; ++i) {
					if (
						childArray[i] === sortedComments[key][1] ||
						childArray[i] === sortedComments[key][0] ||
						childArray[i] === startElement[0]
					) {
						start = i;
						if (childArray[i] === sortedComments[key][0]) start++;
						break;
					}
				}
				if (startElement[1]) start++;

				for (var j = start; j < childArray.length; ++j) {
					if (childArray[j] === endElement[0]) {
						end = j;
					} else if (childArray[j] === sortedComments[key][1]) {
						end = j;
						break;
					}
				}

				var number = end - start;
				if (endElement[1]) number++;
				for (var k = 0; k < number; ++k) {
					if (INVALID_ELEMENTS.indexOf(childArray[start].nodeName) !== -1) {
						start++;
						continue;
					}
					debugDiv.appendChild(childArray[start]);
				}

				nodeList.push(parent.insertBefore(debugDiv, childArray[start]));
			}

			createCookie('debug-view', 'show', 365);
			setToggleState(true);
		}

		function hideHints() {
			for (var i = 0; i < nodeList.length; ++i) {
				var index;
				for (var j = 0; j < nodeList[i].parentNode.childNodes.length; ++j) {
					if (nodeList[i].parentNode.childNodes[j] === nodeList[i]) {
						index = j;
						break;
					}
				}
				while (nodeList[i].childNodes.length !== 1) {
					nodeList[i].parentNode.insertBefore(
						nodeList[i].childNodes[1],
						nodeList[i].parentNode.childNodes[index].nextSibling
					);
					index++;
				}
				nodeList[i].parentNode.removeChild(nodeList[i]);
			}
			nodeList.length = 0;
			createCookie('debug-view', '', -1);
			setToggleState(false);
		}

		function toggle() {
			if (readCookie('debug-view')) {
				hideHints();
			} else {
				showHints();
			}
		}

		if (toggleBtn) {
			toggleBtn.addEventListener('click', function (ev) {
				ev.preventDefault();
				ev.stopPropagation();
				toggle();
			});
		}

		if (readCookie('debug-view')) {
			showHints();
		} else {
			setToggleState(false);
		}

		return { toggle: toggle, show: showHints, hide: hideHints };
	}

	ready(function () {
		var root = document.getElementById('toolbarContainer');
		if (!root) return;

		var icon = document.getElementById('ciDebugToolbarIcon');
		var bar = document.getElementById('debug-bar');
		var closeBtn = document.getElementById('ciDebugToolbarClose');
		var viewsToggle = document.getElementById('ciDebugViewsToggle');
		var tabs = root.querySelectorAll('.ci-debug-toolbar__tab');
		var panels = root.querySelectorAll('.ci-debug-toolbar__panel');
		var storageKey = 'edschool_ci_debug_toolbar';

		createViewsHintsController(viewsToggle);

		function setOpen(open) {
			root.setAttribute('data-open', open ? '1' : '0');
			if (bar) bar.hidden = !open;
			if (icon) icon.hidden = !!open;
			try {
				localStorage.setItem(storageKey, open ? '1' : '0');
			} catch (e) { /* ignore */ }
		}

		function activateTab(id) {
			var i;
			for (i = 0; i < tabs.length; i++) {
				tabs[i].classList.toggle('is-active', tabs[i].getAttribute('data-tab') === id);
			}
			for (i = 0; i < panels.length; i++) {
				panels[i].hidden = panels[i].getAttribute('data-panel') !== id;
			}
			try {
				localStorage.setItem(storageKey + '_tab', id);
			} catch (e2) { /* ignore */ }
		}

		if (icon) {
			icon.addEventListener('click', function () {
				setOpen(true);
				var preferred = null;
				try { preferred = localStorage.getItem(storageKey + '_tab'); } catch (e3) { /* ignore */ }
				activateTab(preferred || 'timeline');
			});
		}

		if (closeBtn) {
			closeBtn.addEventListener('click', function () {
				setOpen(false);
			});
		}

		for (var t = 0; t < tabs.length; t++) {
			tabs[t].addEventListener('click', function (ev) {
				var id = ev.currentTarget.getAttribute('data-tab');
				if (!id) return;
				setOpen(true);
				activateTab(id);
			});
		}

		var openPref = '0';
		try { openPref = localStorage.getItem(storageKey) || '0'; } catch (e4) { /* ignore */ }
		if (openPref === '1') {
			setOpen(true);
			var tabPref = 'timeline';
			try { tabPref = localStorage.getItem(storageKey + '_tab') || 'timeline'; } catch (e5) { /* ignore */ }
			activateTab(tabPref);
		} else {
			setOpen(false);
		}
	});
})();
