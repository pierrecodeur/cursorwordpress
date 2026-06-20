<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Résolution des fichiers composant, sanitisation des props, enqueue CSS/JS.
 */
final class WPCursor_Component_Loader {

	/** @var array<string, true> */
	private static array $assets_registered = [];

	/** @var list<string>|null Dernier rendu : messages schéma (consumé par consume_last_render_schema_issues). */
	private static ?array $last_render_schema_issues = null;

	/**
	 * Parse options depuis une chaîne (lignes clé=valeur, JSON, ou commentaires #).
	 *
	 * @return array<string, string>
	 */
	public static function parse_props_text(string $raw): array {
		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}
		if (strlen($raw) > 0 && $raw[0] === '{') {
			$j = json_decode($raw, true);
			if (is_array($j)) {
				$flat = [];
				foreach ($j as $k => $v) {
					if (is_string($k) && ( is_string($v) || is_numeric($v) )) {
						$flat[ (string) $k ] = (string) $v;
					}
				}
				return self::sanitize_props($flat);
			}
			return [];
		}
		$pairs = [];
		foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
			$line = trim($line);
			if ($line === '' || $line[0] === '#') {
				continue;
			}
			if (preg_match('/^([a-z0-9_-]{1,64})\s*=\s*(.*)$/i', $line, $m)) {
				$pairs[ strtolower($m[1]) ] = $m[2];
			}
		}
		return self::sanitize_props($pairs);
	}

	public static function sanitize_props(array $raw): array {
		$out = [];
		foreach ($raw as $key => $value) {
			if ($key === 'name') {
				continue;
			}
			$key = strtolower((string) $key);
			if (! preg_match('/^[a-z0-9_-]{1,64}$/', $key)) {
				continue;
			}
			if (is_array($value)) {
				continue;
			}
			$value = (string) $value;
			if (preg_match('/_url$/', $key) || $key === 'url' || $key === 'href' || $key === 'link') {
				$out[ $key ] = esc_url_raw($value);
			} elseif (strpos($key, 'html') !== false) {
				$out[ $key ] = wp_kses_post($value);
			} elseif (strpos($key, 'css') !== false || strpos($key, 'style') !== false || strpos($key, 'code') !== false) {
				$out[ $key ] = sanitize_textarea_field($value);
			} else {
				$out[ $key ] = sanitize_text_field($value);
			}
		}
		return $out;
	}

	/**
	 * @return array{file: string, mode: 'modern'|'legacy'}|null
	 */
	public static function resolve_component_file(string $name): ?array {
		if ($name === '' || ! preg_match('/^[a-z0-9_-]+$/i', $name)) {
			return null;
		}

		$base = realpath(WPCURSOR_PATH . 'components');
		if ($base === false) {
			return null;
		}

		$modern = $base . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'component.php';
		$legacy = $base . DIRECTORY_SEPARATOR . $name . '.php';

		$resolved_modern = is_readable($modern) ? realpath($modern) : false;
		$resolved_legacy = is_readable($legacy) ? realpath($legacy) : false;

		$prefix = $base . DIRECTORY_SEPARATOR;

		if ($resolved_modern !== false && self::path_under_base($resolved_modern, $prefix)) {
			return ['file' => $resolved_modern, 'mode' => 'modern'];
		}

		if ($resolved_legacy !== false && self::path_under_base($resolved_legacy, $prefix)) {
			return ['file' => $resolved_legacy, 'mode' => 'legacy'];
		}

		return null;
	}

	private static function path_under_base(string $file, string $base_prefix): bool {
		$norm_file = str_replace('\\', '/', $file);
		$norm_base = str_replace('\\', '/', $base_prefix);
		return strpos($norm_file, $norm_base) === 0;
	}

	public static function enqueue_assets(string $name): void {
		if (isset(self::$assets_registered[ $name ])) {
			return;
		}
		self::$assets_registered[ $name ] = true;

		$dir_fs = WPCURSOR_PATH . 'components/' . $name;
		$dir_url = WPCURSOR_URL . 'components/' . rawurlencode($name) . '/';

		$css = $dir_fs . '/style.css';
		if (is_readable($css)) {
			wp_enqueue_style(
				'wpcursor-cpt-' . sanitize_key($name),
				$dir_url . 'style.css',
				[],
				(string) filemtime($css)
			);
		}

		$js = $dir_fs . '/script.js';
		if (is_readable($js)) {
			wp_enqueue_script(
				'wpcursor-cpt-' . sanitize_key($name),
				$dir_url . 'script.js',
				[],
				(string) filemtime($js),
				true
			);
		}
	}

	/**
	 * Inventaire des composants pour admin / WP‑CLI.
	 *
	 * @return list<array{slug:string,type:string,path:string}>
	 */
	public static function inventory(): array {
		$base = realpath(WPCURSOR_PATH . 'components');
		$rows = [];
		if ($base === false) {
			return [];
		}

		foreach (glob($base . '/*.php') ?: [] as $file) {
			$slug = basename($file, '.php');
			if ($slug === '') {
				continue;
			}
			$rows[] = [
				'slug' => $slug,
				'type' => 'legacy',
				'path' => 'components/' . $slug . '.php',
			];
		}

		foreach (glob($base . '/*/component.php') ?: [] as $file) {
			$slug = basename(dirname($file));
			if ($slug === '') {
				continue;
			}
			$rows[] = [
				'slug' => $slug,
				'type' => 'modern',
				'path' => 'components/' . $slug . '/component.php',
			];
		}

		usort(
			$rows,
			static function ($a, $b) {
				$c = strcmp($a['slug'], $b['slug']);
				return $c !== 0 ? $c : strcmp($a['type'], $b['type']);
			}
		);

		return $rows;
	}

	/**
	 * @param string $runtime Une des constantes WPCURSOR_RUNTIME_* — front | admin_preview | cli.
	 */
	public static function render(string $name, array $props, string $runtime = 'front'): string {
		self::$last_render_schema_issues = null;

		$allowed = [ WPCURSOR_RUNTIME_FRONT, WPCURSOR_RUNTIME_ADMIN_PREVIEW, WPCURSOR_RUNTIME_CLI ];
		if (! in_array($runtime, $allowed, true)) {
			$runtime = WPCURSOR_RUNTIME_FRONT;
		}

		$resolved = self::resolve_component_file($name);
		if ($resolved === null) {
			wpcursor_log(sprintf('composant %s introuvable', $name), ['slug' => $name, 'runtime' => $runtime]);
			if (is_user_logged_in() && current_user_can(WPCURSOR_CAP)) {
				return '<!-- WP Cursor : composant introuvable : ' . esc_html($name) . ' -->';
			}
			return '';
		}

		self::$last_render_schema_issues = WPCursor_Component_Schema::validate_props($name, $props);

		$props_for_include = $props;
		if ($runtime === WPCURSOR_RUNTIME_FRONT) {
			$props_for_include = WPCursor_Component_Schema::filter_props_to_contract($name, $props);
		}

		self::enqueue_assets($name);

		$wpcursor_context = [
			'name'    => $name,
			'props'   => $props_for_include,
			'mode'    => $resolved['mode'],
			'runtime' => $runtime,
		];

		ob_start();
		/** @noinspection PhpIncludeInspection */
		include $resolved['file'];
		return (string) ob_get_clean();
	}

	/**
	 * Valeurs d’exemple depuis components/{slug}/divi-template.txt (corps shortcode ou lignes clé=valeur).
	 *
	 * @return array<string, string>
	 */
	public static function get_divi_template_defaults(string $slug): array {
		$slug = sanitize_key($slug);
		if ($slug === '') {
			return [];
		}
		$path = WPCURSOR_PATH . 'components/' . $slug . '/divi-template.txt';
		if (! is_readable($path)) {
			return [];
		}
		$raw = file_get_contents($path);
		if ($raw === false || $raw === '') {
			return [];
		}
		if (preg_match('/\[wpcursor[^\]]*\](.*?)\[\/wpcursor\]/is', $raw, $m)) {
			return self::parse_props_text(trim($m[1]));
		}

		return self::parse_props_text($raw);
	}

	/**
	 * Lignes clé=valeur pour textarea Divi / générateur.
	 *
	 * @param array<string, string> $props
	 */
	public static function format_props_lines(array $props): string {
		if ($props === []) {
			return '';
		}
		$lines = [];
		foreach ($props as $key => $value) {
			$lines[] = $key . '=' . $value;
		}

		return implode("\n", $lines);
	}

	/**
	 * @return list<string>
	 */
	public static function consume_last_render_schema_issues(): array {
		$x = self::$last_render_schema_issues ?? [];
		self::$last_render_schema_issues = null;
		return $x;
	}
}
