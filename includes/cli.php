<?php

if (!defined('ABSPATH')) {
	exit;
}

if (! class_exists('WP_CLI')) {
	return;
}

/**
 * Commandes WP‑CLI : `wp wpcursor …`
 *
 * ## EXAMPLES
 *
 *     wp wpcursor list
 *     wp wpcursor usage
 *     wp wpcursor usage hero-home
 *     wp wpcursor diagnose
 *     wp wpcursor render hero-home --variant=dark
 *     wp wpcursor flush-cache
 *     wp wpcursor context hero-home
 *     wp wpcursor context-site
 *     wp wpcursor validate
 *     wp wpcursor validate hero-home
 *
 */
final class WPCursor_CLI_Command {

	/**
	 * Liste les composants détectés dans components/.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpcursor components
	 *     wp wpcursor list
	 *
	 * @subcommand components
	 */
	public function components( $args, $assoc_args ): void {
		self::output_inventory();
	}

	/**
	 * Alias de `components` — liste les slugs et chemins.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpcursor list
	 */
	public function list( $args, $assoc_args ): void {
		self::output_inventory();
	}

	private static function output_inventory(): void {
		$rows = WPCursor_Component_Loader::inventory();
		if ($rows === []) {
			WP_CLI::warning('Aucun composant trouvé dans components/.');
			return;
		}
		$items = [];
		foreach ($rows as $r) {
			$items[] = [
				'slug' => $r['slug'],
				'type' => $r['type'],
				'path' => $r['path'],
			];
		}
		WP_CLI\Utils\format_items('table', $items, [ 'slug', 'type', 'path' ]);
		WP_CLI::success(sprintf('%d entrée(s).', count($rows)));
	}

	/**
	 * Affiche où les shortcodes sont utilisés (post_content + postmeta).
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<n>]
	 * : Nombre max de lignes après tri (défaut 200, max 500). Utiliser **all** pour tout afficher (scan lourd).
	 *
	 * [--offset=<n>]
	 * : Décalage pagination (défaut 0).
	 *
	 * [<slug>]
	 * : Filtrer les lignes qui utilisent ce composant.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpcursor usage
	 *     wp wpcursor usage hero-home
	 */
	public function usage( $args, $assoc_args ): void {
		WP_CLI::log('Scan en cours (post_content + postmeta)…');

		$unlimited = isset($assoc_args['limit']) && (string) $assoc_args['limit'] === 'all';
		$limit     = $unlimited
			? null
			: max(
				1,
				min(
					WPCursor_Usage_Scan::SCAN_ROW_LIMIT_MAX,
					isset($assoc_args['limit']) ? (int) $assoc_args['limit'] : WPCursor_Usage_Scan::SCAN_ROW_LIMIT_DEFAULT
				)
			);
		$offset = isset($assoc_args['offset']) ? max(0, (int) $assoc_args['offset']) : 0;

		$scan = WPCursor_Usage_Scan::scan_detailed($unlimited ? null : $limit, $offset);
		$rows = $scan['rows'];

		if (isset($args[0]) && $args[0] !== '') {
			$filter = strtolower(sanitize_key($args[0]));
			$rows   = array_values(
				array_filter(
					$rows,
					static function ( $r ) use ( $filter ) {
						return in_array($filter, $r['components'], true);
					}
				)
			);
		}

		if ($rows === []) {
			WP_CLI::warning(
				isset($args[0]) && $args[0] !== ''
					? 'Aucune occurrence trouvée pour ce filtre.'
					: 'Aucune occurrence trouvée.'
			);
			WP_CLI::log(sprintf('Meta hits (indicatif global) : %d', (int) $scan['meta_hits']));
			return;
		}

		$items = [];
		foreach ($rows as $r) {
			$items[] = [
				'post_id'      => (string) $r['post_id'],
				'title'        => (string) $r['title'],
				'post_type'    => (string) $r['type'],
				'status'       => (string) $r['status'],
				'components'   => implode(', ', $r['components']),
				'sources'      => implode(' + ', $r['sources']),
			];
		}
		WP_CLI\Utils\format_items(
			'table',
			$items,
			[ 'post_id', 'title', 'post_type', 'status', 'components', 'sources' ]
		);
		WP_CLI::log(
			sprintf(
				'Meta hits (indicatif global) : %d — total_rows après tri : %d — limit : %s — offset : %d — cap DISTINCT meta : %d',
				(int) $scan['meta_hits'],
				(int) $scan['total_rows'],
				$scan['limit'] === null ? 'all' : (string) $scan['limit'],
				(int) $scan['offset'],
				(int) $scan['meta_distinct_cap']
			)
		);
		WP_CLI::success(sprintf('%d entrée(s) dans cette page.', count($rows)));
	}

	/**
	 * Affiche un diagnostic environnement / plugin.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpcursor diagnose
	 */
	/**
	 * Valide composants (component.json, PHP, garde ABSPATH, assets, contrat vs usages dans le contenu).
	 *
	 * [<slug>]
	 * : Limiter à un composant.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpcursor validate
	 *     wp wpcursor validate hero-home
	 */
	public function validate( $args, $assoc_args ): void {
		$filter = null;
		if (isset($args[0]) && $args[0] !== '') {
			$filter = strtolower(sanitize_key($args[0]));
			if ($filter === '') {
				WP_CLI::error('Slug invalide.');
			}
		}

		WP_CLI::log('Validation WP Cursor (scan utilisation inclus, peut prendre quelques secondes)…');
		$results   = WPCursor_Validate::validate_slugs($filter);
		$has_error = false;
		$has_warn  = false;

		foreach ($results as $slug => $pack ) {
			$lvl = isset($pack['level']) ? strtoupper((string) $pack['level']) : 'OK';
			WP_CLI::log(sprintf('%s %s', $lvl, $slug));
			foreach ($pack['messages'] ?? [] as $msg ) {
				WP_CLI::log('     — ' . $msg);
			}
			if (($pack['level'] ?? '') === WPCursor_Validate::LEVEL_ERROR) {
				$has_error = true;
			}
			if (($pack['level'] ?? '') === WPCursor_Validate::LEVEL_WARN) {
				$has_warn = true;
			}
		}

		$sum = WPCursor_Validate::summarize($results);
		WP_CLI::log(sprintf('Résumé : OK=%d WARN=%d ERROR=%d', $sum['ok'], $sum['warn'], $sum['error']));

		if ($has_error) {
			WP_CLI::error('Validation : au moins une erreur.', 1);
		}
		if ($has_warn) {
			WP_CLI::warning('Validation terminée avec avertissements.');
		} else {
			WP_CLI::success('Validation OK.');
		}
	}

	public function diagnose( $args, $assoc_args ): void {
		WP_CLI::log('PHP            : ' . PHP_VERSION);
		WP_CLI::log('WordPress      : ' . get_bloginfo('version'));
		WP_CLI::log('Thème actif    : ' . wp_get_theme()->get_stylesheet());
		WP_CLI::log('Parent         : ' . wp_get_theme()->get_template());
		WP_CLI::log('Divi           : ' . (wp_get_theme()->get_template() === 'Divi' || wp_get_theme()->get_stylesheet() === 'Divi' ? 'oui (template/enfant)' : 'non garanti'));
		WP_CLI::log('WooCommerce    : ' . (class_exists('WooCommerce') ? 'oui' : 'non'));
		WP_CLI::log('WP_DEBUG       : ' . (defined('WP_DEBUG') && WP_DEBUG ? 'true' : 'false'));
		WP_CLI::log('DISALLOW_FILE_EDIT : ' . (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ? 'true' : 'false'));
		WP_CLI::log('Plugin path    : ' . WPCURSOR_PATH);
		$w = is_writable(WPCURSOR_PATH . 'components');
		WP_CLI::log('components/ writable : ' . ($w ? 'oui' : 'non'));
		$n = count(WPCursor_Component_Loader::inventory());
		WP_CLI::log('Composants (lignes inventaire) : ' . $n);
		WP_CLI::success('Diagnostic terminé.');
	}

	/**
	 * Rend le HTML du composant sur stdout (props optionnelles en arguments nommés).
	 *
	 * ## OPTIONS
	 *
	 * [--<prop>=<value>]
	 * : Attributs shortcode (même filtrage WordPress que sur le site : texte / URL selon le nom de la clé).
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpcursor render example
	 *     wp wpcursor render hero-home --variant=dark
	 *
	 * @when after_wp_load
	 */
	public function render( $args, $assoc_args ): void {
		if (! isset($args[0]) || $args[0] === '') {
			WP_CLI::error('Indique un slug : wp wpcursor render mon-slug');
		}
		$slug = strtolower(sanitize_key($args[0]));
		if ($slug === '') {
			WP_CLI::error('Slug invalide.');
		}
		$skip = [ 'path', 'url', 'http', 'context', 'user', 'skip-plugins', 'skip-themes', 'require' ];
		$props = [];
		foreach ($assoc_args as $k => $v) {
			if (in_array(strtolower((string) $k), $skip, true)) {
				continue;
			}
			$props[ (string) $k ] = is_array($v) ? '' : (string) $v;
		}
		$props = WPCursor_Component_Loader::sanitize_props($props);
		$html  = WPCursor_Component_Loader::render($slug, $props, WPCURSOR_RUNTIME_CLI);
		foreach (WPCursor_Component_Loader::consume_last_render_schema_issues() as $msg) {
			WP_CLI::warning($msg);
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sortie CLI brute pour redirection / debug.
		echo $html;
	}

	/**
	 * Bloc « début de session » : résumé site + composants + usages + chemins + extrait AGENTS.md.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpcursor context-site
	 */
	public function context_site( $args, $assoc_args ): void {
		WP_CLI::log('Scan utilisation (post_content + postmeta)…');
		$scan = WPCursor_Usage_Scan::scan_detailed(null, 0);

		$lines   = [];
		$lines[] = '=== Contexte site (WP Cursor) ===';
		$lines[] = 'Site : ' . home_url('/');
		$lines[] = 'WordPress : ' . get_bloginfo('version');
		$lines[] = 'PHP : ' . PHP_VERSION;
		$lines[] = 'Thème actif : ' . wp_get_theme()->get_stylesheet();
		$lines[] = 'Divi : ' . (wp_get_theme()->get_template() === 'Divi' || wp_get_theme()->get_stylesheet() === 'Divi' ? 'oui (template ou enfant)' : 'non détecté comme tel');
		$lines[] = 'WooCommerce : ' . (class_exists('WooCommerce') ? 'oui' : 'non');
		$lines[] = '';
		$lines[] = 'Composants disponibles :';
		foreach (WPCursor_Component_Loader::inventory() as $r) {
			$lines[] = '- ' . $r['slug'] . ' [' . $r['type'] . '] ' . $r['path'];
		}
		$lines[] = '';
		$lines[] = 'Contenus utilisant [wpcursor] / [site_component] (' . (int) $scan['total_rows'] . ' entrées après tri) :';
		if ($scan['rows'] === []) {
			$lines[] = '- (aucun)';
		}
		foreach ($scan['rows'] as $r) {
			$lines[] = '- ' . $r['title'] . ', ID ' . $r['post_id'] . ' — composants : ' . implode(', ', $r['components']);
		}
		$lines[] = '';
		$lines[] = 'Chemins importants :';
		$lines[] = '- ABSPATH : ' . ABSPATH;
		$lines[] = '- Plugin WP Cursor : ' . WPCURSOR_PATH;
		$lines[] = '- Components : ' . WPCURSOR_PATH . 'components/';
		$lines[] = '';

		$agents_paths = [
			trailingslashit(ABSPATH) . 'REPO/AGENTS.md',
			trailingslashit(dirname(ABSPATH)) . 'REPO/AGENTS.md',
			trailingslashit(dirname(WPCURSOR_PATH, 3)) . 'REPO/AGENTS.md',
		];
		$agents_file = '';
		foreach ($agents_paths as $p) {
			if (is_readable($p)) {
				$agents_file = $p;
				break;
			}
		}
		$lines[] = 'Règles AGENTS.md :';
		if ($agents_file !== '') {
			$raw_agents = file_get_contents($agents_file);
			$lines[] = 'Fichier : ' . $agents_file;
			if ($raw_agents !== false) {
				$snippet = strlen($raw_agents) > 12000 ? substr($raw_agents, 0, 12000) . "\n… [tronqué]" : $raw_agents;
				$lines[] = $snippet;
			}
		} else {
			$lines[] = '(REPO/AGENTS.md introuvable aux chemins connus — ouvre le dépôt ou REPO/AGENTS.md)';
			$lines[] = 'Résumé : WooCommerce via CRUD uniquement ; capability manage_wpcursor ; ABSPATH dans les PHP ; échappement composants ; WP_CURSOR_PLUGIN.txt pour détail.';
		}

		$lines[] = '';
		$lines[] = 'Logs debug plugin : define(\'WP_DEBUG_LOG\', true); et/ou define(\'WPCURSOR_DEBUG\', true); → préfixe [WP Cursor] dans debug.log';

		WP_CLI::log(implode("\n", $lines));
		WP_CLI::success('Bloc site prêt à coller dans Cursor.');
	}

	/**
	 * Vide le cache transient du scan « Utilisation sur le site » (admin).
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpcursor flush-cache
	 */
	public function flush_cache( $args, $assoc_args ): void {
		delete_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
		WP_CLI::success('Cache du scan utilisation vidé (' . WPCURSOR_USAGE_SCAN_TRANSIENT . ').');
	}

	/**
	 * Affiche un bloc texte prêt à coller dans Cursor (fichier, shortcode, pages utilisant le composant).
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpcursor context hero-home
	 *
	 * @when after_wp_load
	 */
	public function context( $args, $assoc_args ): void {
		if (! isset($args[0]) || $args[0] === '') {
			WP_CLI::error('Indique un slug : wp wpcursor context hero-home');
		}
		$slug = strtolower(sanitize_key($args[0]));
		if ($slug === '') {
			WP_CLI::error('Slug invalide.');
		}

		$resolved = WPCursor_Component_Loader::resolve_component_file($slug);
		if ($resolved === null) {
			WP_CLI::error(sprintf('Composant introuvable : %s', $slug));
		}

		$rel = ltrim(str_replace('\\', '/', str_replace(ABSPATH, '', $resolved['file'])), '/');

		WP_CLI::log('Scan des usages…');
		$scan = WPCursor_Usage_Scan::scan_detailed(null, 0);

		$lines   = [];
		$lines[] = 'Composant : ' . $slug;
		$lines[] = 'Fichier : ' . $rel;
		$lines[] = 'Shortcode : [wpcursor name="' . $slug . '"]';
		$lines[] = 'Utilisé sur :';

		$found = false;
		foreach ($scan['rows'] as $r) {
			if (in_array($slug, $r['components'], true)) {
				$found   = true;
				$lines[] = '- ' . $r['title'] . ', ID ' . $r['post_id'];
			}
		}

		if (! $found) {
			$lines[] = '- (aucune occurrence dans le scan complet)';
		}

		WP_CLI::log(implode("\n", $lines));
		WP_CLI::success('Bloc prêt à copier.');
	}
}

WP_CLI::add_command('wpcursor', 'WPCursor_CLI_Command');
