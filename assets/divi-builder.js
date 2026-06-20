/**
 * Module Divi WP Cursor : formulaire à onglets (WPCursorGeneratorUI) + sync props_content.
 */
(function ($) {
	'use strict';

	var cfg = window.wpcursorDivi || {};
	var manifest = cfg.manifest || {};
	var syncing = false;
	var panelState = new WeakMap();

	function parsePropsLines(text) {
		var props = {};
		String(text || '')
			.split(/\r\n|\r|\n/)
			.forEach(function (line) {
				line = line.trim();
				if (!line || line[0] === '#') {
					return;
				}
				var m = line.match(/^([a-z0-9_-]{1,64})\s*=\s*(.*)$/i);
				if (m) {
					props[m[1].toLowerCase()] = m[2];
				}
			});
		return props;
	}

	function formatPropsLines(props) {
		if (!window.WPCursorGeneratorUI) {
			return '';
		}
		var lines = [];
		Object.keys(props)
			.sort()
			.forEach(function (k) {
				var v = String(props[k] || '').trim();
				if (!v || k === 'name') {
					return;
				}
				lines.push(k + '=' + v);
			});
		return lines.join('\n');
	}

	function collectProps($root) {
		return window.WPCursorGeneratorUI
			? WPCursorGeneratorUI.collectProps($root)
			: {};
	}

	function syncToTextarea($panel, $uiRoot) {
		var $ta = findPropsTextarea($panel);
		if (!$ta.length) {
			return;
		}
		syncing = true;
		$ta.val(formatPropsLines(collectProps($uiRoot))).trigger('input').trigger('change');
		syncing = false;
	}

	function seedFromTextarea(slug, raw) {
		var seed = parsePropsLines(raw);
		if (slug && manifest[slug] && manifest[slug].props) {
			Object.keys(manifest[slug].props).forEach(function (k) {
				if (seed[k] == null || seed[k] === '') {
					var d = manifest[slug].props[k].default;
					if (d) {
						seed[k] = String(d);
					}
				}
			});
		}
		return seed;
	}

	function applySeedToUi($uiRoot, seed) {
		if (!$uiRoot || !seed) {
			return;
		}
		Object.keys(seed).forEach(function (key) {
			$uiRoot.find('[data-prop="' + key + '"]').val(seed[key]);
		});
		if (seed.custom_css && window.WPCursorGeneratorUI) {
			WPCursorGeneratorUI.applyColorRulesFromCss($uiRoot, seed.custom_css);
		}
	}

	function findPropsTextarea($panel) {
		var $ta = $panel.find('textarea[data-option_id="props_content"]');
		if ($ta.length) {
			return $ta;
		}
		return $panel.find('textarea').filter(function () {
			var n = (this.name || '') + (this.id || '') + (this.className || '');
			return n.indexOf('props_content') !== -1;
		});
	}

	function findSlugSelect($panel) {
		var $sel = $panel.find('select[data-option_id="component_slug"]');
		if ($sel.length) {
			return $sel;
		}
		$sel = $panel.find('select').filter(function () {
			var n = (this.name || '') + (this.id || '') + (this.className || '');
			return n.indexOf('component_slug') !== -1;
		});
		if ($sel.length) {
			return $sel;
		}
		return $('select').filter(function () {
			var n = (this.name || '') + (this.id || '') + (this.className || '');
			return n.indexOf('component_slug') !== -1;
		});
	}

	function findPanelForSlug($slug) {
		var $row = $slug.closest(
			'.et-fb-settings-option, .et-pb-option, .et-pb-option-container, .et-fb-settings-options, .et_pb_modal_settings, .et-fb-modal'
		);
		if ($row.length) {
			return $row.closest('.et-fb-settings-options, .et_pb_modal_settings, .et-fb-modal, form');
		}
		return $slug.closest('.et-fb-settings-options, .et_pb_modal_settings, form');
	}

	function wrapRawPropsField($panel) {
		var $ta = findPropsTextarea($panel);
		if (!$ta.length || $ta.closest('.wpcursor-divi-props-raw-wrap').length) {
			return $ta;
		}
		var $row = $ta.closest('.et-pb-option, .et-fb-option, .et-pb-option-container');
		if (!$row.length) {
			$row = $ta.parent();
		}
		$row.addClass('wpcursor-divi-props-raw-wrap wpcursor-divi-props-raw--collapsed');
		if (!$row.find('.wpcursor-divi-raw-toggle').length) {
			$row.append(
				$('<a href="#" class="wpcursor-divi-raw-toggle"></a>').text(
					cfg.i18n && cfg.i18n.showRaw
						? cfg.i18n.showRaw
						: 'Afficher le mode texte brut'
				)
			);
			$row.find('.wpcursor-divi-raw-toggle').on('click', function (e) {
				e.preventDefault();
				$row.toggleClass('wpcursor-divi-props-raw--collapsed');
			});
		}
		return $ta;
	}

	function mountGuidedForm($panel) {
		var $slug = findSlugSelect($panel);
		if (!$slug.length) {
			return;
		}
		$panel = findPanelForSlug($slug);
		if (!$panel.length) {
			$panel = $(document.body);
		}
		$panel.attr('data-wpcursor-panel', '1');

		var state = panelState.get($panel[0]);
		if (!state) {
			state = { slug: '', mounted: false };
			panelState.set($panel[0], state);
		}

		var slug = String($slug.val() || '');
		var $ta = wrapRawPropsField($panel);

		var $guided = $panel.find('.wpcursor-divi-guided');
		if (!$guided.length) {
			$guided = $('<div class="wpcursor-divi-guided wpcursor-generator-fields"></div>');
			if (cfg.i18n && cfg.i18n.guidedIntro) {
				$guided.append($('<p class="wpcursor-divi-guided-intro"></p>').text(cfg.i18n.guidedIntro));
			}
			var $slugRow = $slug.closest(
				'.et-fb-settings-option, .et-pb-option, .et-pb-option-container, .et-fb-option'
			);
			if ($slugRow.length) {
				$slugRow.after($guided);
			} else {
				$slug.after($guided);
			}
		}

		if (!window.WPCursorGeneratorUI) {
			if (!$guided.find('.wpcursor-divi-no-ui').length) {
				$guided.append(
					$('<p class="wpcursor-divi-no-ui description"></p>').text(
						cfg.i18n && cfg.i18n.noUi ? cfg.i18n.noUi : ''
					)
				);
			}
			return;
		}

		$panel.addClass('wpcursor-has-guided');

		var raw = $ta.val();
		if (slug !== state.slug || !state.mounted) {
			var seed = seedFromTextarea(slug, raw);
			WPCursorGeneratorUI.render({
				$container: $guided,
				slug: slug,
				manifest: manifest,
				idPrefix: 'wpcursor-divi-' + slug + '-',
				templateHint:
					cfg.i18n && cfg.i18n.templateDetected ? cfg.i18n.templateDetected : '',
				onFieldChange: function () {
					syncToTextarea($panel, $guided);
				},
				onMediaPick: true,
				mediaButtonLabel: cfg.i18n && cfg.i18n.mediaPick ? cfg.i18n.mediaPick : 'Médiathèque',
			});
			applySeedToUi($guided, seed);
			syncToTextarea($panel, $guided);
			state.slug = slug;
			state.mounted = true;
		}

		$slug.off('change.wpcursorDivi').on('change.wpcursorDivi', function () {
			var newSlug = String($slug.val() || '');
			var seedOnChange = {};
			if (newSlug && manifest[newSlug] && manifest[newSlug].props) {
				Object.keys(manifest[newSlug].props).forEach(function (k) {
					var d = manifest[newSlug].props[k].default;
					if (d) {
						seedOnChange[k] = String(d);
					}
				});
			}
			syncing = true;
			$ta.val(formatPropsLines(seedOnChange));
			syncing = false;
			state.slug = '';
			state.mounted = false;
			mountGuidedForm($panel);
		});

		$ta.off('input.wpcursorDiviSync').on('input.wpcursorDiviSync', function () {
			if (syncing) {
				return;
			}
			applySeedToUi($guided, seedFromTextarea(String($slug.val() || ''), $ta.val()));
			syncToTextarea($panel, $guided);
		});
	}

	function scanPanels() {
		var $slugs = $('select').filter(function () {
			var n = (this.name || '') + (this.id || '') + (this.className || '');
			return n.indexOf('component_slug') !== -1;
		});
		if (!$slugs.length) {
			$('.et-fb-settings-options, .et_pb_modal_settings').each(function () {
				mountGuidedForm($(this));
			});
			return;
		}
		$slugs.each(function () {
			mountGuidedForm(findPanelForSlug($(this)));
		});
	}

	$(document).on('click', '.wpcursor-divi-guided .wpcursor-gen-media-pick', function (e) {
		e.preventDefault();
		if (typeof wp === 'undefined' || !wp.media) {
			return;
		}
		var targetId = $(this).attr('data-target');
		var altProp = $(this).attr('data-alt-prop') || '';
		var $input = $('#' + targetId);
		var frame = wp.media({
			title: 'Choisir une image',
			button: { text: 'Utiliser' },
			library: { type: 'image' },
			multiple: false,
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			$input.val(att.url || '').trigger('input');
			if (window.WPCursorGeneratorUI) {
				WPCursorGeneratorUI.updateMediaPreview($input.closest('.wpcursor-gen-field'), att.url);
			}
			if (altProp) {
				var $alt = $input.closest('.wpcursor-divi-guided').find('[data-prop="' + altProp + '"]');
				var alt = (att.alt || att.title || '').trim();
				if ($alt.length && alt && !String($alt.val() || '').trim()) {
					$alt.val(alt).trigger('input');
				}
			}
		});
		frame.open();
	});

	$(function () {
		if (!Object.keys(manifest).length || !window.WPCursorGeneratorUI) {
			return;
		}
		scanPanels();
		var observer = new MutationObserver(function () {
			window.requestAnimationFrame(scanPanels);
		});
		if (document.body) {
			observer.observe(document.body, { childList: true, subtree: true });
		}
	});
})(jQuery);
