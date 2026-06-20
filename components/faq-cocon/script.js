/**
 * FAQ tête de cocon — accordéon.
 */
(function () {
	'use strict';

	function closeItem(item) {
		var trigger = item.querySelector('.wpcursor-faq-cocon__trigger');
		var panel = item.querySelector('.wpcursor-faq-cocon__panel');
		if (!trigger || !panel) {
			return;
		}
		trigger.setAttribute('aria-expanded', 'false');
		item.classList.remove('is-open');
		panel.setAttribute('hidden', 'hidden');
	}

	function openItem(item) {
		var trigger = item.querySelector('.wpcursor-faq-cocon__trigger');
		var panel = item.querySelector('.wpcursor-faq-cocon__panel');
		if (!trigger || !panel) {
			return;
		}
		trigger.setAttribute('aria-expanded', 'true');
		item.classList.add('is-open');
		panel.removeAttribute('hidden');
	}

	function initAccordion(root) {
		var allowMultiple = root.getAttribute('data-allow-multiple') === 'yes';
		var items = root.querySelectorAll('.wpcursor-faq-cocon__item');

		items.forEach(function (item) {
			var trigger = item.querySelector('.wpcursor-faq-cocon__trigger');
			if (!trigger) {
				return;
			}

			trigger.addEventListener('click', function () {
				var isOpen = item.classList.contains('is-open');

				if (!allowMultiple) {
					items.forEach(function (other) {
						if (other !== item) {
							closeItem(other);
						}
					});
				}

				if (isOpen) {
					closeItem(item);
				} else {
					openItem(item);
				}
			});
		});
	}

	function boot() {
		document.querySelectorAll('.wpcursor-faq-cocon').forEach(initAccordion);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
