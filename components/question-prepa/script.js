/**
 * Une question sur la prépa — bascule email / téléphone.
 */
(function () {
	'use strict';

	function initContactToggle(root) {
		if (!root.classList.contains('wpcursor-question-prepa--tabs')) {
			return;
		}

		var tabs = root.querySelectorAll('.wpcursor-question-prepa__toggle-btn');
		var panels = root.querySelectorAll('.wpcursor-question-prepa__panel');
		if (!tabs.length || !panels.length) {
			return;
		}

		function activate(contact) {
			tabs.forEach(function (tab) {
				var active = tab.getAttribute('data-contact') === contact;
				tab.classList.toggle('is-active', active);
				tab.setAttribute('aria-selected', active ? 'true' : 'false');
			});
			panels.forEach(function (panel) {
				var match = panel.classList.contains('wpcursor-question-prepa__panel--' + contact);
				if (match) {
					panel.removeAttribute('hidden');
				} else {
					panel.setAttribute('hidden', 'hidden');
				}
			});
		}

		var defaultContact = root.getAttribute('data-default-contact') || 'email';
		activate(defaultContact);

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var contact = tab.getAttribute('data-contact');
				if (contact) {
					activate(contact);
				}
			});
		});
	}

	function boot() {
		document.querySelectorAll('.wpcursor-question-prepa--tabs').forEach(initContactToggle);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
