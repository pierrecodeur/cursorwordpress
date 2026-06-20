/**
 * UI générateur WP Cursor : onglets + grille (admin + Divi).
 */
(function (window, $) {
	'use strict';

	var TAB_DEFS = {
		headings: { label: 'Titres', order: 10 },
		media: { label: 'Image', order: 20 },
		problems: { label: 'Problématiques', order: 30 },
		results: { label: 'Résultats', order: 40 },
		quote: { label: 'Citation', order: 50 },
		main: { label: 'Contenu', order: 60 },
		items: { label: 'Liste', order: 70 },
		slide2: { label: 'Profil 2', order: 80 },
		slide3: { label: 'Profil 3', order: 81 },
		slide4: { label: 'Profil 4', order: 82 },
		slide5: { label: 'Profil 5', order: 83 },
		other: { label: 'Autres', order: 90 },
		advanced: { label: 'Avancé', order: 100 },
	};

	function propLabel(key) {
		return String(key).replace(/_/g, ' ').replace(/\b\w/g, function (c) {
			return c.toUpperCase();
		});
	}

	function isLongText(key) {
		return /intro|item|text|quote|body|description|content|html|paragraph|message|css|style|code|problem|result/i.test(
			String(key)
		);
	}

	function isAdvanced(key) {
		return /html|css|style|code/i.test(String(key || ''));
	}

	function isImageField(key, type) {
		var k = String(key || '').toLowerCase();
		if (type === 'url' && /image|photo|avatar|thumb|logo|banniere|banner/.test(k)) {
			return true;
		}
		return /image_url$|_image_url$|^image$/.test(k);
	}

	function altKeyForImage(key) {
		var k = String(key || '');
		if (k === 'image_url') {
			return 'image_alt';
		}
		if (/_image_url$/.test(k)) {
			return k.replace(/_url$/, '_alt');
		}
		return '';
	}

	function classifyKey(key) {
		if (isAdvanced(key)) {
			return 'advanced';
		}
		var slide = String(key).match(/^slide(\d+)_/i);
		if (slide) {
			return 'slide' + slide[1];
		}
		if (/image/i.test(key)) {
			return 'media';
		}
		if (/^problem\d+$/i.test(key) || /^problems_/i.test(key)) {
			return 'problems';
		}
		if (/^result\d+$/i.test(key) || /^results_/i.test(key)) {
			return 'results';
		}
		if (/^quote$/i.test(key)) {
			return 'quote';
		}
		if (/heading|subheading|_tag$/i.test(key)) {
			return 'headings';
		}
		if (/^item\d+$/i.test(key)) {
			return 'items';
		}
		return 'main';
	}

	function updateMediaPreview($wrap, url) {
		var $img = $wrap.find('.wpcursor-gen-media-preview');
		if (!url) {
			$img.remove();
			return;
		}
		if (!$img.length) {
			$img = $('<img class="wpcursor-gen-media-preview" alt="">');
			$wrap.append($img);
		}
		$img.attr('src', url);
	}

	function buildFieldWrap(key, spec, opts) {
		var def = spec.default != null ? String(spec.default) : '';
		var req = !!spec.required;
		var selector = spec.selector != null ? String(spec.selector) : '';
		var idPrefix = opts.idPrefix || 'wpcursor-gen-';
		var fieldId = idPrefix + key;

		var $wrap = $('<div class="wpcursor-gen-field"></div>');
		if (isLongText(key) || key === 'quote' || /custom_/.test(key)) {
			$wrap.addClass('wpcursor-gen-field--wide');
		}

		var $label = $('<label></label>').attr('for', fieldId).text(propLabel(key));
		if (req) {
			$label.append(' <span class="wpcursor-gen-required">*</span>');
		}
		$wrap.append($label);
		if (selector) {
			$wrap.append(
				$('<p class="wpcursor-gen-help"></p>').html(
					'Classe CSS : <code>' + selector + '</code>'
				)
			);
		}

		var type = String(spec.type || 'string');
		var $input;
		if (type === 'enum' && spec.values && spec.values.length) {
			$input = $('<select></select>').attr('id', fieldId).attr('data-prop', key);
			spec.values.forEach(function (val) {
				$input.append($('<option></option>').attr('value', val).text(val));
			});
			if (def && spec.values.indexOf(def) === -1) {
				$input.prepend($('<option></option>').attr('value', def).text(def));
			}
			$input.val(def || spec.values[0] || '');
		} else if (isLongText(key)) {
			$input = $('<textarea rows="3"></textarea>').attr('id', fieldId).attr('data-prop', key).val(def);
		} else if (type === 'url') {
			$input = $('<input type="url">').attr('id', fieldId).attr('data-prop', key).val(def);
			if (isImageField(key, type) && opts.onMediaPick) {
				var altKey = altKeyForImage(key);
				var $mediaRow = $('<div class="wpcursor-gen-media-row"></div>');
				$mediaRow.append($input);
				$mediaRow.append(
					$('<button type="button" class="button button-small wpcursor-gen-media-pick"></button>')
						.attr('data-target', fieldId)
						.attr('data-alt-prop', altKey)
						.text(opts.mediaButtonLabel || 'Choisir dans la médiathèque')
				);
				$wrap.append($mediaRow);
				if (def) {
					updateMediaPreview($wrap, def);
				}
				$input = null;
			}
		} else {
			$input = $('<input type="text">').attr('id', fieldId).attr('data-prop', key).val(def);
		}
		if ($input) {
			$wrap.append($input);
		}
		if (selector && !isAdvanced(key)) {
			var $colorRow = $('<div class="wpcursor-gen-color-row"></div>');
			$colorRow.append($('<label></label>').text('Couleur texte'));
			$colorRow.append(
				$('<input type="color" class="wpcursor-gen-color">')
					.attr('data-selector', selector)
					.val('#06113a')
			);
			$wrap.append($colorRow);
		}

		return $wrap;
	}

	function collectProps($root) {
		var props = {};
		$root.find('[data-prop]').each(function () {
			var key = String($(this).attr('data-prop') || '');
			if (!key) {
				return;
			}
			props[key] = String($(this).val() || '');
		});
		var colorRules = [];
		$root.find('.wpcursor-gen-color[data-selector]').each(function () {
			var selector = String($(this).attr('data-selector') || '').trim();
			var color = String($(this).val() || '').trim();
			if (selector && color) {
				colorRules.push(selector + '{color:' + color + ';}');
			}
		});
		if (colorRules.length) {
			var baseCss = String(props.custom_css || '');
			baseCss = baseCss
				.replace(/\/\*\s*auto_colors:start\s*\*\/[\s\S]*?\/\*\s*auto_colors:end\s*\*/g, '')
				.trim();
			var autoCss =
				'/* auto_colors:start */\n' + colorRules.join('\n') + '\n/* auto_colors:end */';
			props.custom_css = (baseCss ? baseCss + '\n\n' : '') + autoCss;
		}
		return props;
	}

	function applyColorRulesFromCss($root, css) {
		var block = String(css || '').match(
			/\/\*\s*auto_colors:start\s*\*\/([\s\S]*?)\/\*\s*auto_colors:end\s*\*/
		);
		if (!block) {
			return;
		}
		block[1].split('\n').forEach(function (line) {
			var m = line.match(/([^{]+)\{[^}]*color:\s*([^;]+)/i);
			if (m) {
				$root
					.find('.wpcursor-gen-color[data-selector="' + m[1].trim() + '"]')
					.val(m[2].trim());
			}
		});
	}

	function render(config) {
		var $container = config.$container;
		var slug = config.slug;
		var manifest = config.manifest || {};
		var opts = config;
		$container.empty();

		if (!slug || !manifest[slug]) {
			return { ok: false };
		}

		var entry = manifest[slug];
		var propsSpec = entry.props || {};
		var keys = Object.keys(propsSpec).sort();

		if (config.$meta && config.$meta.length) {
			config.$meta.hide().empty();
			if (entry.has_template) {
				config.$meta
					.html(
						'<span class="dashicons dashicons-media-text" aria-hidden="true"></span> ' +
							(config.templateHint ||
								'Modèle Divi détecté — valeurs préremplies depuis divi-template.txt')
					)
					.show();
			}
		}

		if (!keys.length) {
			return { ok: false, empty: true };
		}

		var buckets = {};
		var repeatableBases = {};

		keys.forEach(function (key) {
			var tab = classifyKey(key);
			if (!buckets[tab]) {
				buckets[tab] = [];
			}
			buckets[tab].push(key);

			var repeatMatch = key.match(/^(.*?)(\d+)$/);
			if (repeatMatch && /item$/i.test(repeatMatch[1])) {
				var base = repeatMatch[1];
				var idx = parseInt(repeatMatch[2], 10);
				if (!isNaN(idx)) {
					if (!repeatableBases[base] || idx > repeatableBases[base].max) {
						repeatableBases[base] = { max: idx, spec: propsSpec[key] || {} };
					}
				}
			}
		});

		var tabIds = Object.keys(buckets).sort(function (a, b) {
			var oa = TAB_DEFS[a] ? TAB_DEFS[a].order : 99;
			var ob = TAB_DEFS[b] ? TAB_DEFS[b].order : 99;
			return oa - ob;
		});

		var $shell = $('<div class="wpcursor-gen-tabbed"></div>');
		var $nav = $('<div class="wpcursor-gen-tabs" role="tablist"></div>');
		var $panels = $('<div class="wpcursor-gen-tab-panels"></div>');
		var firstTab = tabIds[0];

		tabIds.forEach(function (tabId, index) {
			var def = TAB_DEFS[tabId] || { label: tabId, order: 99 };
			var count = buckets[tabId].length;
			var isActive = tabId === firstTab;
			var $btn = $('<button type="button" class="wpcursor-gen-tab"></button>')
				.attr('role', 'tab')
				.attr('data-tab', tabId)
				.attr('aria-selected', isActive ? 'true' : 'false')
				.toggleClass('is-active', isActive)
				.html(def.label + ' <span class="wpcursor-gen-tab-count">' + count + '</span>');
			$nav.append($btn);

			var $panel = $('<div class="wpcursor-gen-tab-panel"></div>')
				.attr('role', 'tabpanel')
				.attr('data-tab-panel', tabId)
				.toggleClass('is-active', isActive);
			var $grid = $('<div class="wpcursor-gen-fields-grid"></div>');

			buckets[tabId].forEach(function (key) {
				$grid.append(buildFieldWrap(key, propsSpec[key] || {}, opts));
			});

			if (tabId === 'items') {
				Object.keys(repeatableBases).forEach(function (base) {
					var info = repeatableBases[base];
					var $btnAdd = $('<button type="button" class="button button-small"></button>').text(
						'+ Ajouter ' + base + (info.max + 1)
					);
					var $line = $('<p class="wpcursor-gen-add-item-row"></p>').append($btnAdd);
					$grid.append($line);
					$btnAdd.on('click', function (e) {
						e.preventDefault();
						info.max += 1;
						var newKey = base + info.max;
						var $newWrap = buildFieldWrap(
							newKey,
							info.spec || { type: 'string' },
							opts
						);
						$line.before($newWrap);
						$btnAdd.text('+ Ajouter ' + base + (info.max + 1));
						if (opts.onFieldChange) {
							opts.onFieldChange();
						}
					});
				});
			}

			$panel.append($grid);
			$panels.append($panel);
		});

		$shell.append($nav).append($panels);
		$container.append($shell);

		if (propsSpec.custom_css && propsSpec.custom_css.default) {
			applyColorRulesFromCss($container, propsSpec.custom_css.default);
		}

		$nav.on('click', '.wpcursor-gen-tab', function (e) {
			e.preventDefault();
			var tab = $(this).attr('data-tab');
			$nav.find('.wpcursor-gen-tab').removeClass('is-active').attr('aria-selected', 'false');
			$(this).addClass('is-active').attr('aria-selected', 'true');
			$panels.find('.wpcursor-gen-tab-panel').removeClass('is-active');
			$panels.find('[data-tab-panel="' + tab + '"]').addClass('is-active');
		});

		$container.on('input change', '[data-prop], .wpcursor-gen-color', function () {
			if (opts.onFieldChange) {
				opts.onFieldChange();
			}
		});

		return { ok: true, $root: $shell };
	}

	window.WPCursorGeneratorUI = {
		propLabel: propLabel,
		isLongText: isLongText,
		isAdvanced: isAdvanced,
		isImageField: isImageField,
		altKeyForImage: altKeyForImage,
		classifyKey: classifyKey,
		buildFieldWrap: buildFieldWrap,
		collectProps: collectProps,
		updateMediaPreview: updateMediaPreview,
		applyColorRulesFromCss: applyColorRulesFromCss,
		render: render,
	};
})(window, jQuery);
