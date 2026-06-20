<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Champs Divi Builder natifs (show_if par composant) — compatibles Visual Builder React.
 */
final class WPCursor_Divi_Module_Fields {

	public const PROP_PREFIX = 'wpc_prop_';

	/** @var array<string, array{title: string, priority: int}> */
	private static array $tab_toggles = [
		'wpc_tab_headings' => ['title' => 'Titres', 'priority' => 12],
		'wpc_tab_media'    => ['title' => 'Image', 'priority' => 14],
		'wpc_tab_problems' => ['title' => 'Problématiques', 'priority' => 16],
		'wpc_tab_results'  => ['title' => 'Résultats', 'priority' => 18],
		'wpc_tab_quote'    => ['title' => 'Citation', 'priority' => 20],
		'wpc_tab_main'     => ['title' => 'Contenu', 'priority' => 22],
		'wpc_tab_items'    => ['title' => 'Liste', 'priority' => 24],
		'wpc_tab_slide2'   => ['title' => 'Profil 2', 'priority' => 26],
		'wpc_tab_slide3'   => ['title' => 'Profil 3', 'priority' => 27],
		'wpc_tab_slide4'   => ['title' => 'Profil 4', 'priority' => 28],
		'wpc_tab_slide5'   => ['title' => 'Profil 5', 'priority' => 29],
		'wpc_tab_other'    => ['title' => 'Autres', 'priority' => 40],
		'wpc_tab_advanced' => ['title' => 'Avancé (HTML/CSS)', 'priority' => 50],
	];

	public static function tab_toggles(): array {
		$out = [];
		foreach (self::$tab_toggles as $slug => $meta) {
			$out[ $slug ] = [
				'title'    => esc_html__($meta['title'], 'wpcursor'),
				'priority' => $meta['priority'],
			];
		}

		return $out;
	}

	public static function field_id_for(string $component_slug, string $prop_key): string {
		$slug = sanitize_key(str_replace('-', '_', $component_slug));
		$key  = sanitize_key($prop_key);

		return self::PROP_PREFIX . $slug . '_' . $key;
	}

	public static function classify_prop_key(string $key): string {
		if (preg_match('/html|css|style|code/i', $key)) {
			return 'wpc_tab_advanced';
		}
		if (preg_match('/^slide(\d+)_/i', $key, $m)) {
			$tab = 'wpc_tab_slide' . $m[1];
			return isset(self::$tab_toggles[ $tab ]) ? $tab : 'wpc_tab_other';
		}
		if (preg_match('/image/i', $key)) {
			return 'wpc_tab_media';
		}
		if (preg_match('/^problem\d+$|^problems_/i', $key)) {
			return 'wpc_tab_problems';
		}
		if (preg_match('/^result\d+$|^results_/i', $key)) {
			return 'wpc_tab_results';
		}
		if ($key === 'quote') {
			return 'wpc_tab_quote';
		}
		if (preg_match('/heading|subheading|_tag$/i', $key)) {
			return 'wpc_tab_headings';
		}
		if (preg_match('/^item\d+$/i', $key)) {
			return 'wpc_tab_items';
		}

		return 'wpc_tab_main';
	}

	/**
	 * @param array<string, mixed> $spec
	 * @return array<string, mixed>
	 */
	private static function prop_to_divi_field(
		string $component_slug,
		string $prop_key,
		array $spec
	): array {
		$type    = isset($spec['type']) ? strtolower((string) $spec['type']) : 'string';
		$default = isset($spec['default']) ? (string) $spec['default'] : '';
		$label   = ucwords(str_replace('_', ' ', $prop_key));
		$field   = [
			'label'           => $label,
			'option_category' => 'basic_option',
			'toggle_slug'     => self::classify_prop_key($prop_key),
			'show_if'         => [
				'component_slug' => $component_slug,
			],
			'default'         => $default,
		];

		$desc = [];
		if (! empty($spec['selector'])) {
			$desc[] = 'CSS : ' . $spec['selector'];
		}
		if (! empty($spec['required'])) {
			$desc[] = __('Recommandé', 'wpcursor');
		}
		if ($desc !== []) {
			$field['description'] = implode(' — ', $desc);
		}

		if ($type === 'enum' && ! empty($spec['values']) && is_array($spec['values'])) {
			$options = [];
			foreach ($spec['values'] as $v) {
				$options[ (string) $v ] = (string) $v;
			}
			$field['type']    = 'select';
			$field['options'] = $options;
		} elseif ($type === 'url' && preg_match('/image/i', $prop_key)) {
			$field['type']               = 'upload';
			$field['upload_button_text'] = esc_attr__('Choisir une image', 'wpcursor');
			$field['choose_text']        = esc_attr__('Choisir', 'wpcursor');
			$field['update_text']        = esc_attr__('Utiliser', 'wpcursor');
			$field['hide_metadata']     = true;
		} elseif ($type === 'url') {
			$field['type'] = 'text';
		} elseif (preg_match('/intro|item|text|quote|body|description|content|html|paragraph|message|css|style|code|problem|result/i', $prop_key)) {
			$field['type'] = 'textarea';
		} else {
			$field['type'] = 'text';
		}

		return $field;
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public static function build_prop_fields(): array {
		$fields   = [];
		$manifest = WPCursor_Generator_Manifest::build();

		foreach ($manifest as $slug => $entry) {
			if (! is_string($slug) || $slug === '' || ! is_array($entry['props'] ?? null)) {
				continue;
			}
			foreach ($entry['props'] as $prop_key => $spec) {
				if (! is_string($prop_key) || ! is_array($spec)) {
					continue;
				}
				$field_id           = self::field_id_for($slug, $prop_key);
				$fields[ $field_id ] = self::prop_to_divi_field($slug, $prop_key, $spec);
			}
		}

		return $fields;
	}

	/**
	 * Lit les props depuis les attributs du module Divi (champs wpc_prop_*).
	 *
	 * @param array<string, mixed> $module_props
	 * @return array<string, string>
	 */
	public static function collect_props_from_module(array $module_props, string $component_slug): array {
		$component_slug = sanitize_key($component_slug);
		if ($component_slug === '') {
			return [];
		}
		$prefix = self::PROP_PREFIX . sanitize_key(str_replace('-', '_', $component_slug)) . '_';
		$out    = [];

		foreach ($module_props as $key => $value) {
			if (! is_string($key) || strpos($key, $prefix) !== 0) {
				continue;
			}
			$prop_key = substr($key, strlen($prefix));
			if ($prop_key !== '' && is_scalar($value)) {
				$out[ $prop_key ] = (string) $value;
			}
		}

		if ($out !== []) {
			return WPCursor_Component_Loader::sanitize_props($out);
		}

		$raw = isset($module_props['props_content']) ? (string) $module_props['props_content'] : '';
		if ($raw !== '') {
			return WPCursor_Component_Loader::parse_props_text($raw);
		}

		return [];
	}
}
