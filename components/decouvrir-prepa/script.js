/**
 * Découvrir nos prépas — onglets.
 */
(function () {
	'use strict';

	function initTabs(root) {
		if (!root.classList.contains('wpcursor-decouvrir-prepa--tabs')) {
			return;
		}

		var tabs = root.querySelectorAll('.wpcursor-decouvrir-prepa__tab');
		var panels = root.querySelectorAll('.wpcursor-decouvrir-prepa__panel');
		if (!tabs.length || !panels.length) {
			return;
		}

		function activate(index) {
			var i = Math.max(0, Math.min(index, tabs.length - 1));
			tabs.forEach(function (tab, t) {
				var active = t === i;
				tab.classList.toggle('is-active', active);
				tab.setAttribute('aria-selected', active ? 'true' : 'false');
			});
			panels.forEach(function (panel, p) {
				if (p === i) {
					panel.removeAttribute('hidden');
				} else {
					panel.setAttribute('hidden', 'hidden');
				}
			});
		}

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var index = parseInt(tab.getAttribute('data-tab-index'), 10);
				if (!isNaN(index)) {
					activate(index);
				}
			});
		});
	}

	function boot() {
		document.querySelectorAll('.wpcursor-decouvrir-prepa--tabs').forEach(initTabs);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
