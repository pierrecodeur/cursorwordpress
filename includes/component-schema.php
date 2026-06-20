<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Fichier optionnel : components/{slug}/component.json
 *
 * Métadonnées optionnelles : label, description, version (semver), updated (AAAA-MM-JJ).
 *
 * Voir aussi components/README.md (modèle officiel et conventions).
 */
final class WPCursor_Component_Schema {

	/** @var array<string, array{state:string,parsed:?array<string,mixed>,error:?string}> */
	private static array $load_cache = [];

	public const STATE_OK           = 'ok';
	public const STATE_NO_SCHEMA   = 'no_schema';
	public const STATE_INVALID_JSON = 'invalid_json';

	/**
	 * @return array{state:string,parsed:?array<string,mixed>,error:?string}
	 */
	public static function load_for_slug(string $slug): array {
		$slug = sanitize_key($slug);
		if ($slug === '') {
			return ['state' => self::STATE_NO_SCHEMA, 'parsed' => null, 'error' => null];
		}
		if (isset(self::$load_cache[ $slug ])) {
			return self::$load_cache[ $slug ];
		}
		$path = WPCURSOR_PATH . 'components/' . $slug . '/component.json';
		if (! is_readable($path)) {
			$r = ['state' => self::STATE_NO_SCHEMA, 'parsed' => null, 'error' => null];
			self::$load_cache[ $slug ] = $r;
			return $r;
		}
		$raw = file_get_contents($path);
		if ($raw === false) {
			$r = ['state' => self::STATE_INVALID_JSON, 'parsed' => null, 'error' => 'read_failed'];
			self::$load_cache[ $slug ] = $r;
			return $r;
		}
		$data = json_decode($raw, true);
		if (! is_array($data)) {
			wpcursor_log('component.json invalide', ['slug' => $slug, 'json_error' => json_last_error_msg()]);
			$r = ['state' => self::STATE_INVALID_JSON, 'parsed' => null, 'error' => json_last_error_msg()];
			self::$load_cache[ $slug ] = $r;
			return $r;
		}

		$manifest = self::parse_manifest_fields($data);

		$norm_props = [];
		if (isset($data['props']) && is_array($data['props'])) {
			foreach ($data['props'] as $pname => $spec) {
				if (is_string($pname)) {
					$norm_props[ strtolower($pname) ] = $spec;
				}
			}
		}

		foreach ($norm_props as $pname => $spec) {
			if (! is_string($pname) || ! preg_match('/^[a-z0-9_-]{1,64}$/', strtolower($pname))) {
				wpcursor_log('component.json : clé prop invalide', ['slug' => $slug, 'prop' => (string) $pname]);
				$r = ['state' => self::STATE_INVALID_JSON, 'parsed' => null, 'error' => 'invalid_prop_key'];
				self::$load_cache[ $slug ] = $r;
				return $r;
			}
			if (! is_array($spec)) {
				$r = ['state' => self::STATE_INVALID_JSON, 'parsed' => null, 'error' => 'prop_spec_not_object'];
				self::$load_cache[ $slug ] = $r;
				return $r;
			}
			if (array_key_exists('required', $spec) && ! is_bool($spec['required'])) {
				$r = ['state' => self::STATE_INVALID_JSON, 'parsed' => null, 'error' => 'required_not_bool'];
				self::$load_cache[ $slug ] = $r;
				return $r;
			}
			if (! isset($spec['type']) || ! is_string($spec['type']) || trim($spec['type']) === '') {
				$r = ['state' => self::STATE_INVALID_JSON, 'parsed' => null, 'error' => 'type_missing'];
				self::$load_cache[ $slug ] = $r;
				return $r;
			}
			$type = strtolower(trim((string) $spec['type']));
			if (! in_array($type, ['string', 'url', 'enum'], true)) {
				wpcursor_log('component.json : type inconnu', ['slug' => $slug, 'prop' => $pname, 'type' => $type]);
				$r = ['state' => self::STATE_INVALID_JSON, 'parsed' => null, 'error' => 'unknown_type'];
				self::$load_cache[ $slug ] = $r;
				return $r;
			}
			if ($type === 'enum' && (! isset($spec['values']) || ! is_array($spec['values']) || $spec['values'] === [])) {
				$r = ['state' => self::STATE_INVALID_JSON, 'parsed' => null, 'error' => 'enum_without_values'];
				self::$load_cache[ $slug ] = $r;
				return $r;
			}
		}

		$parsed = array_merge(
			$manifest,
			['props' => $norm_props]
		);
		$r = ['state' => self::STATE_OK, 'parsed' => $parsed, 'error' => null];
		self::$load_cache[ $slug ] = $r;
		return $r;
	}

	/**
	 * Champs réservés hors `props` (non interprétés comme attributs shortcode).
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array{label:string,description:string,version:string,updated:string}
	 */
	private static function parse_manifest_fields(array $data): array {
		$label = '';
		if (isset($data['label']) && ( is_string($data['label']) || is_numeric($data['label']) )) {
			$label = sanitize_text_field((string) $data['label']);
			if (function_exists('mb_substr')) {
				$label = mb_substr($label, 0, 200);
			} else {
				$label = substr($label, 0, 200);
			}
		}

		$description = '';
		if (isset($data['description']) && ( is_string($data['description']) || is_numeric($data['description']) )) {
			$description = sanitize_textarea_field((string) $data['description']);
			if (function_exists('mb_substr')) {
				$description = mb_substr($description, 0, 2000);
			} else {
				$description = substr($description, 0, 2000);
			}
		}

		$version = '';
		if (isset($data['version']) && ( is_string($data['version']) || is_numeric($data['version']) )) {
			$version = preg_replace('/[^0-9A-Za-z.\-+]/', '', (string) $data['version']);
			$version = substr($version, 0, 32);
		}

		$updated = '';
		if (isset($data['updated']) && is_string($data['updated'])) {
			$t = trim($data['updated']);
			if (preg_match('/^\d{4}-\d{2}-\d{2}/', $t, $m)) {
				$try = strtotime($m[0] . ' 12:00:00 UTC');
				if ($try !== false) {
					$updated = $m[0];
				}
			}
		}

		return [
			'label'       => $label,
			'description' => $description,
			'version'     => $version,
			'updated'     => $updated,
		];
	}

	/**
	 * Ligne lisible pour l’admin : libellé — version — date (component.json ou fichier).
	 */
	public static function admin_manifest_line(string $slug, int $file_mtime): string {
		$loaded = self::load_for_slug($slug);
		$chunks = [];

		if ($loaded['state'] === self::STATE_OK && is_array($loaded['parsed'])) {
			$p = $loaded['parsed'];
			if (! empty($p['label'])) {
				$chunks[] = (string) $p['label'];
			}
			if (! empty($p['version'])) {
				$chunks[] = 'v' . (string) $p['version'];
			}
			if (! empty($p['updated'])) {
				$ts = strtotime($p['updated'] . ' 12:00:00 UTC');
				if ($ts !== false) {
					$chunks[] = sprintf(
						/* translators: %s: formatted date */
						__('modifié le %s', 'wpcursor'),
						wp_date((string) get_option('date_format'), $ts)
					);
				}
			}
		}

		if ($chunks !== []) {
			return implode(' — ', $chunks);
		}

		if ($file_mtime > 0) {
			return sprintf(
				/* translators: %s: formatted datetime */
				__('Fichier — modifié le %s', 'wpcursor'),
				wp_date((string) get_option('date_format') . ' ' . (string) get_option('time_format'), $file_mtime)
			);
		}

		return '';
	}

	/**
	 * Messages de validation runtime (après sanitisation loader), sans écriture dans les logs.
	 *
	 * @param array<string, string> $props Props telles qu’injectées dans le shortcode / la prévisualisation.
	 *
	 * @return list<string> Messages courts pour admin / CLI / logs.
	 */
	public static function collect_prop_issues(string $slug, array $props): array {
		$loaded = self::load_for_slug($slug);
		if ($loaded['state'] !== self::STATE_OK || $loaded['parsed'] === null) {
			return [];
		}
		$schema_props = isset($loaded['parsed']['props']) && is_array($loaded['parsed']['props'])
			? $loaded['parsed']['props']
			: [];
		$errors = [];

		foreach ($props as $key => $value) {
			if (! isset($schema_props[ $key ])) {
				if ($schema_props !== []) {
					$errors[] = sprintf('prop "%s" inconnue du schéma', $key);
				}
				continue;
			}
			$spec = $schema_props[ $key ];
			$type = isset($spec['type']) ? strtolower(trim((string) $spec['type'])) : '';

			if ($type === 'enum') {
				$vals = isset($spec['values']) && is_array($spec['values']) ? $spec['values'] : [];
				$allowed = array_map('strval', $vals);
				if (! in_array((string) $value, $allowed, true)) {
					$errors[] = sprintf('prop "%s" : valeur enum invalide', $key);
				}
			}
		}

		foreach ($schema_props as $pname => $spec) {
			if (! is_array($spec)) {
				continue;
			}
			$req = ! empty($spec['required']);
			if ($req && (! isset($props[ $pname ]) || $props[ $pname ] === '')) {
				$errors[] = sprintf('prop "%s" requise manquante', $pname);
			}
		}

		return $errors;
	}

	/**
	 * @param list<string> $issues Retour de collect_prop_issues().
	 */
	private static function log_prop_issues(string $slug, array $issues): void {
		foreach ($issues as $msg) {
			wpcursor_log($msg, ['slug' => $slug]);
		}
	}

	/**
	 * Valide les props runtime et journalise chaque message (debug.log si activé).
	 *
	 * @param array<string, string> $props
	 *
	 * @return list<string>
	 */
	public static function validate_props(string $slug, array $props): array {
		$issues = self::collect_prop_issues($slug, $props);
		self::log_prop_issues($slug, $issues);
		return $issues;
	}

	/**
	 * Mode strict (front) : ne garde que les clés déclarées dans component.json lorsque le contrat liste au moins une prop.
	 *
	 * @param array<string, string> $props
	 *
	 * @return array<string, string>
	 */
	public static function filter_props_to_contract(string $slug, array $props): array {
		$loaded = self::load_for_slug($slug);
		if ($loaded['state'] !== self::STATE_OK || $loaded['parsed'] === null) {
			return $props;
		}
		$schema_props = isset($loaded['parsed']['props']) && is_array($loaded['parsed']['props'])
			? $loaded['parsed']['props']
			: [];
		if ($schema_props === []) {
			return $props;
		}
		$out = [];
		foreach ($props as $k => $v) {
			if (isset($schema_props[ $k ])) {
				$out[ $k ] = $v;
			}
		}
		return $out;
	}

	/**
	 * Compare les clés vues dans les shortcodes du scan avec le schéma.
	 *
	 * @param list<string> $usage_keys Attributs hors `name`.
	 *
	 * @return list<string>
	 */
	public static function validate_usage_keys(string $slug, array $usage_keys): array {
		$loaded = self::load_for_slug($slug);
		if ($loaded['state'] !== self::STATE_OK || $loaded['parsed'] === null) {
			return [];
		}
		$schema_props = isset($loaded['parsed']['props']) && is_array($loaded['parsed']['props'])
			? $loaded['parsed']['props']
			: [];
		if ($schema_props === []) {
			return [];
		}
		$errors = [];
		$allowed = array_map('strtolower', array_keys($schema_props));
		foreach ($usage_keys as $k) {
			$k = strtolower((string) $k);
			if ($k === '' || $k === 'name') {
				continue;
			}
			if (! in_array($k, $allowed, true)) {
				$errors[] = sprintf('usage : prop "%s" inconnue dans component.json', $k);
			}
		}
		return $errors;
	}

	/**
	 * Résumé pour colonne admin.
	 *
	 * @param list<string> $usage_errors
	 */
	public static function admin_status_label(string $slug, array $usage_errors): string {
		$loaded = self::load_for_slug($slug);
		if ($loaded['state'] === self::STATE_INVALID_JSON) {
			return 'component.json invalide';
		}
		if ($loaded['state'] === self::STATE_NO_SCHEMA) {
			return $usage_errors !== [] ? implode('; ', $usage_errors) : 'OK (sans component.json)';
		}
		if ($usage_errors !== []) {
			return implode('; ', $usage_errors);
		}
		return 'OK';
	}
}
