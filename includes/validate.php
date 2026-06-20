<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Validation globale ou par slug : fichiers, component.json, contrat vs usage.
 */
final class WPCursor_Validate {

	public const LEVEL_OK    = 'ok';
	public const LEVEL_WARN  = 'warn';
	public const LEVEL_ERROR = 'error';

	/**
	 * @param string|null $slug_filter slug unique ou null = tous les slugs inventoriés
	 *
	 * @return array<string, array{level:string, messages:list<string>}>
	 */
	public static function validate_slugs( ?string $slug_filter = null ): array {
		$scan = WPCursor_Usage_Scan::scan_detailed(null, 0);

		$inv   = WPCursor_Component_Loader::inventory();
		$slugs = [];
		foreach ($inv as $row) {
			$slugs[ $row['slug'] ] = true;
		}
		$slug_list = array_keys($slugs);
		sort($slug_list, SORT_STRING);

		if ($slug_filter !== null && $slug_filter !== '') {
			$f = sanitize_key($slug_filter);
			if ($f === '' || ! isset($slugs[ $f ])) {
				return [
					$f !== '' ? $f : '_invalid_' => [
						'level'    => self::LEVEL_ERROR,
						'messages' => [ 'Slug inconnu ou absent de l’inventaire components/' ],
					],
				];
			}
			$slug_list = [ $f ];
		}

		$out = [];
		foreach ($slug_list as $slug) {
			$out[ $slug ] = self::validate_one_slug($slug, $scan);
		}
		return $out;
	}

	/**
	 * @param array{rows: list<array<string,mixed>>, meta_hits?: int} $scan
	 *
	 * @return array{level:string, messages:list<string>}
	 */
	private static function validate_one_slug( string $slug, array $scan ): array {
		$messages = [];
		$level    = self::LEVEL_OK;

		$resolved = WPCursor_Component_Loader::resolve_component_file($slug);
		if ($resolved === null) {
			return [
				'level'    => self::LEVEL_ERROR,
				'messages' => [ 'Fichier PHP composant introuvable (component.php ou slug.php).' ],
			];
		}

		$php_path = $resolved['file'];
		$mode     = $resolved['mode'];

		if (! self::file_has_abspath_guard($php_path)) {
			$messages[] = 'Garde ABSPATH absente ou non détectée dans les ~1200 premiers octets du PHP.';
			$level      = self::worst_level($level, self::LEVEL_WARN);
		}

		$component_dir = dirname($php_path);
		$css_path      = $component_dir . '/style.css';
		$js_path       = $component_dir . '/script.js';

		if ($mode === 'modern') {
			$messages[] = 'style.css : ' . ( is_readable($css_path) ? 'présent' : 'absent' );
			$messages[] = 'script.js : ' . ( is_readable($js_path) ? 'présent' : 'absent' );
		} else {
			$messages[] = 'Assets dossier : N/A (composant legacy à la racine de components/).';
		}

		/** @var list<string>|null null = pas de contrat à appliquer aux usages */
		$contract_keys = null;

		if ($mode === 'modern') {
			$json_path = $component_dir . '/component.json';
			if (! is_readable($json_path)) {
				$messages[] = 'Aucun component.json — contrat optionnel non défini.';
				$level      = self::worst_level($level, self::LEVEL_WARN);
			} else {
				$raw = file_get_contents($json_path);
				if ($raw === false) {
					$messages[] = 'component.json illisible.';
					return self::finalize_level(self::LEVEL_ERROR, $messages);
				}
				$data = json_decode($raw, true);
				if (! is_array($data)) {
					$messages[] = 'component.json : JSON invalide.';
					return self::finalize_level(self::LEVEL_ERROR, $messages);
				}

				if (isset($data['updated'])) {
					$u = $data['updated'];
					if (! is_string($u) || ! preg_match('/^\d{4}-\d{2}-\d{2}/', trim($u))
						|| strtotime(substr(trim($u), 0, 10) . ' 12:00:00 UTC') === false) {
						$messages[] = 'Champ updated : attendu début de chaîne AAAA-MM-JJ valide.';
						$level      = self::worst_level($level, self::LEVEL_WARN);
					}
				}

				$loaded = WPCursor_Component_Schema::load_for_slug($slug);
				if ($loaded['state'] === WPCursor_Component_Schema::STATE_INVALID_JSON) {
					$messages[] = 'component.json : schéma invalide (' . (string) ( $loaded['error'] ?? 'erreur' ) . ').';
					return self::finalize_level(self::LEVEL_ERROR, $messages);
				}

				if ($loaded['state'] === WPCursor_Component_Schema::STATE_OK && isset($loaded['parsed']['props']) && is_array($loaded['parsed']['props'])) {
					$struct_errs = self::validate_props_specs($loaded['parsed']['props']);
					foreach ($struct_errs as $e) {
						$messages[] = $e;
						$level      = self::worst_level($level, self::LEVEL_ERROR);
					}
					// Contrat présent : liste des clés déclarées (vide = aucune prop autorisée → usages signalés).
					$contract_keys = array_keys($loaded['parsed']['props']);
				}
			}
		} else {
			$messages[] = 'component.json : N/A pour un fichier legacy slug.php (pas de dossier dédié).';
		}

		if ($contract_keys !== null) {
			foreach (self::usage_contract_warnings($slug, $contract_keys, $scan) as $w ) {
				$messages[] = $w;
				$level      = self::worst_level($level, self::LEVEL_WARN);
			}
		}

		return [
			'level'    => $level,
			'messages' => $messages,
		];
	}

	/**
	 * @param array<string, mixed> $props_specs
	 *
	 * @return list<string>
	 */
	private static function validate_props_specs( array $props_specs ): array {
		$errs = [];
		foreach ($props_specs as $pname => $spec) {
			if (! is_array($spec)) {
				$errs[] = sprintf('Prop "%s" : la définition doit être un objet.', (string) $pname);
				continue;
			}
			if (array_key_exists('required', $spec) && ! is_bool($spec['required'])) {
				$errs[] = sprintf('Prop "%s" : required doit être un booléen (true/false).', (string) $pname);
				continue;
			}
			if (! isset($spec['type']) || ! is_string($spec['type']) || trim($spec['type']) === '') {
				$errs[] = sprintf('Prop "%s" : champ type obligatoire (string|url|enum).', (string) $pname);
				continue;
			}
			$type = strtolower(trim((string) $spec['type']));
			if (! in_array($type, ['string', 'url', 'enum'], true)) {
				$errs[] = sprintf('Prop "%s" : type "%s" inconnu (attendu string|url|enum).', (string) $pname, $type);
				continue;
			}
			if ($type === 'enum') {
				if (! isset($spec['values']) || ! is_array($spec['values']) || $spec['values'] === []) {
					$errs[] = sprintf('Prop "%s" : enum sans tableau values non vide.', (string) $pname);
				}
			}
		}
		return $errs;
	}

	/**
	 * @param list<string> $allowed_prop_keys
	 * @param array{rows: list<array<string,mixed>>} $scan
	 *
	 * @return list<string>
	 */
	private static function usage_contract_warnings( string $slug, array $allowed_prop_keys, array $scan ): array {
		// Tableau vide = aucune prop déclarée : toute prop dans les shortcodes est hors contrat.
		$allowed = array_fill_keys($allowed_prop_keys, true);
		/** @var array<string, array<string, true>> $by_prop */
		$by_prop = [];

		foreach ($scan['rows'] ?? [] as $row) {
			foreach ($row['shortcode_samples'] ?? [] as $sample) {
				if (WPCursor_Usage_Scan::extract_shortcode_name_attr((string) $sample) !== $slug) {
					continue;
				}
				foreach (WPCursor_Usage_Scan::extract_shortcode_prop_keys((string) $sample) as $key ) {
					if (! isset($allowed[ $key ] )) {
						$place = sprintf('« %s » (ID %d)', (string) $row['title'], (int) $row['post_id']);
						if (! isset($by_prop[ $key ] )) {
							$by_prop[ $key ] = [];
						}
						$by_prop[ $key ][ $place ] = true;
					}
				}
			}
		}

		$out = [];
		foreach ($by_prop as $prop => $places ) {
			$out[] = sprintf(
				'prop "%s" utilisée sur %s mais absente de component.json',
				$prop,
				implode(', ', array_keys($places))
			);
		}
		return $out;
	}

	private static function finalize_level( string $level, array $messages ): array {
		return [
			'level'    => $level,
			'messages' => $messages,
		];
	}

	private static function worst_level( string $a, string $b ): string {
		$rank = [
			self::LEVEL_OK    => 0,
			self::LEVEL_WARN  => 1,
			self::LEVEL_ERROR => 2,
		];
		return ( $rank[ $b ] ?? 0 ) > ( $rank[ $a ] ?? 0 ) ? $b : $a;
	}

	private static function file_has_abspath_guard( string $path ): bool {
		if (! is_readable($path)) {
			return false;
		}
		$head = file_get_contents($path, false, null, 0, 1200);
		if ($head === false ) {
			return false;
		}
		return strpos($head, 'ABSPATH') !== false
			&& (bool) preg_match('/defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)/', $head);
	}

	/**
	 * Résumé agrégé pour l’API REST (compte par niveau).
	 *
	 * @param array<string, array{level:string, messages:list<string>}> $results
	 *
	 * @return array{ok:int,warn:int,error:int,slugs:list<string>}
	 */
	public static function summarize( array $results ): array {
		$c = [
			self::LEVEL_OK    => 0,
			self::LEVEL_WARN  => 0,
			self::LEVEL_ERROR => 0,
		];
		foreach ($results as $pack ) {
			$lvl       = isset($pack['level']) ? (string) $pack['level'] : self::LEVEL_OK;
			if (! isset($c[ $lvl ] )) {
				$lvl = self::LEVEL_OK;
			}
			$c[ $lvl ] = $c[ $lvl ] + 1;
		}
		return [
			'ok'    => $c[ self::LEVEL_OK ],
			'warn'  => $c[ self::LEVEL_WARN ],
			'error' => $c[ self::LEVEL_ERROR ],
			'slugs' => array_keys($results),
		];
	}
}
