/**
 * Carrousel témoignages — Accueil admission (uniquement si plusieurs slides).
 */
(function () {
	'use strict';

	function initCarousel(root) {
		var slides = root.querySelectorAll('.wpcursor-accueil-admission__slide');
		if (!slides.length || slides.length < 2) {
			return;
		}

		var current = 0;

		var dots = root.querySelectorAll('.wpcursor-accueil-admission__dot');

		function show(index) {
			current = (index + slides.length) % slides.length;
			slides.forEach(function (slide, i) {
				if (i === current) {
					slide.removeAttribute('hidden');
				} else {
					slide.setAttribute('hidden', 'hidden');
				}
			});
			dots.forEach(function (dot, i) {
				var active = i === current;
				dot.classList.toggle('is-active', active);
				dot.setAttribute('aria-selected', active ? 'true' : 'false');
			});
		}

		var prev = root.querySelector('[data-action="prev"]');
		var next = root.querySelector('[data-action="next"]');

		if (prev) {
			prev.addEventListener('click', function () {
				show(current - 1);
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				show(current + 1);
			});
		}

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var target = parseInt(dot.getAttribute('data-slide-to'), 10);
				if (!isNaN(target)) {
					show(target);
				}
			});
		});

		show(0);
	}

	function boot() {
		document.querySelectorAll('.wpcursor-accueil-admission--carousel').forEach(initCarousel);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
