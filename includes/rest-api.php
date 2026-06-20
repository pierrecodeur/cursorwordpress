<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * API REST lecture seule — namespace wpcursor/v1.
 * Chaque route définit un permission_callback explicite (capability manage_wpcursor).
 */
final class WPCursor_REST_API {

	public const NS = 'wpcursor/v1';

	public static function register_routes(): void {
		register_rest_route(
			self::NS,
			'/components',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [self::class, 'route_components'],
				'permission_callback' => [self::class, 'permission_manage'],
			]
		);

		register_rest_route(
			self::NS,
			'/components/(?P<slug>[a-z0-9_-]+)',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [self::class, 'route_component_single'],
				'permission_callback' => [self::class, 'permission_manage'],
				'args'                => [
					'slug' => [
						'description' => 'Identifiant du composant',
						'type'        => 'string',
						'required'    => true,
					],
				],
			]
		);

		register_rest_route(
			self::NS,
			'/usage',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [self::class, 'route_usage'],
				'permission_callback' => [self::class, 'permission_manage'],
				'args'                => [
					'limit'  => [
						'description' => __( 'Nombre max de lignes après tri (défaut 200, max 500).', 'wpcursor' ),
						'type'        => 'integer',
						'default'     => WPCursor_Usage_Scan::SCAN_ROW_LIMIT_DEFAULT,
						'minimum'     => 1,
						'maximum'     => WPCursor_Usage_Scan::SCAN_ROW_LIMIT_MAX,
					],
					'offset' => [
						'description' => __( 'Décalage pagination (pas de paramètre « page », utiliser offset).', 'wpcursor' ),
						'type'        => 'integer',
						'default'     => 0,
						'minimum'     => 0,
					],
				],
			]
		);

		register_rest_route(
			self::NS,
			'/diagnostic',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [self::class, 'route_diagnostic'],
				'permission_callback' => [self::class, 'permission_manage'],
			]
		);

		register_rest_route(
			self::NS,
			'/context/(?P<slug>[a-z0-9_-]+)',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [self::class, 'route_context'],
				'permission_callback' => [self::class, 'permission_manage'],
				'args'                => [
					'slug' => [
						'description' => 'Identifiant du composant',
						'type'        => 'string',
						'required'    => true,
					],
				],
			]
		);
	}

	/**
	 * @return bool|\WP_Error
	 */
	public static function permission_manage() {
		if (! is_user_logged_in()) {
			return new \WP_Error(
				'wpcursor_rest_forbidden',
				__( 'Authentification requise.', 'wpcursor' ),
				['status' => 401]
			);
		}
		if (! current_user_can(WPCURSOR_CAP)) {
			return new \WP_Error(
				'wpcursor_rest_forbidden',
				__( 'Droit manage_wpcursor requis.', 'wpcursor' ),
				['status' => 403]
			);
		}
		return true;
	}

	public static function route_components( \WP_REST_Request $request ) {
		$slugs = [];
		foreach (WPCursor_Component_Loader::inventory() as $row) {
			$slugs[ $row['slug'] ] = true;
		}
		$list = array_keys($slugs);
		sort($list, SORT_STRING);

		$items = [];
		foreach ($list as $slug) {
			$items[] = self::component_payload($slug);
		}

		return rest_ensure_response(
			[
				'plugin_version' => WPCURSOR_VERSION,
				'components'     => $items,
			]
		);
	}

	public static function route_component_single( \WP_REST_Request $request ) {
		$slug = sanitize_key((string) $request['slug']);
		if ($slug === '' || ! self::slug_in_inventory($slug)) {
			return new \WP_Error('wpcursor_not_found', __( 'Composant inconnu.', 'wpcursor' ), ['status' => 404]);
		}
		return rest_ensure_response(self::component_payload($slug));
	}

	public static function route_usage( \WP_REST_Request $request ) {
		$limit  = (int) $request->get_param('limit');
		$offset = (int) $request->get_param('offset');
		$limit  = max(1, min(WPCursor_Usage_Scan::SCAN_ROW_LIMIT_MAX, $limit));
		$offset = max(0, $offset);

		$scan = WPCursor_Usage_Scan::scan_detailed($limit, $offset);

		return rest_ensure_response(
			[
				'plugin_version' => WPCURSOR_VERSION,
				'scan'           => $scan,
				'pagination_help' => [
					'default_limit' => WPCursor_Usage_Scan::SCAN_ROW_LIMIT_DEFAULT,
					'max_limit'     => WPCursor_Usage_Scan::SCAN_ROW_LIMIT_MAX,
					'offset'        => __( 'Utiliser ?offset= avec ?limit= ; pas de « page ».', 'wpcursor' ),
				],
			]
		);
	}

	public static function route_diagnostic( \WP_REST_Request $request ) {
		return rest_ensure_response(
			[
				'plugin_version' => WPCURSOR_VERSION,
				'diagnostic'     => self::diagnostic_array(),
			]
		);
	}

	public static function route_context( \WP_REST_Request $request ) {
		$slug = sanitize_key((string) $request['slug']);
		if ($slug === '') {
			return new \WP_Error('wpcursor_bad_slug', __( 'Slug invalide.', 'wpcursor' ), ['status' => 400]);
		}

		$resolved = WPCursor_Component_Loader::resolve_component_file($slug);
		if ($resolved === null) {
			return new \WP_Error('wpcursor_not_found', __( 'Composant introuvable.', 'wpcursor' ), ['status' => 404]);
		}

		$rel_path = ltrim(str_replace('\\', '/', str_replace(ABSPATH, '', $resolved['file'])), '/');
		$scan     = WPCursor_Usage_Scan::scan_detailed(null, 0);

		$used_on = [];
		foreach ($scan['rows'] as $r) {
			if (in_array($slug, $r['components'], true)) {
				$used_on[] = [
					'post_id'    => (int) $r['post_id'],
					'title'      => (string) $r['title'],
					'post_type'  => (string) $r['type'],
					'edit_link'  => (string) $r['edit_link'],
					'permalink'  => (string) $r['permalink'],
				];
			}
		}

		return rest_ensure_response(
			[
				'plugin_version' => WPCURSOR_VERSION,
				'slug'           => $slug,
				'file_relative'  => $rel_path,
				'mode'           => $resolved['mode'],
				'shortcode'      => '[wpcursor name="' . $slug . '"]',
				'used_on'        => $used_on,
				'scan_note'      => __( 'Liste issue d’un scan complet des occurrences (pas la pagination REST /usage). La découverte postmeta DISTINCT reste plafonnée (voir scan.meta_distinct_cap dans /usage).', 'wpcursor' ),
			]
		);
	}

	private static function slug_in_inventory( string $slug ): bool {
		foreach (WPCursor_Component_Loader::inventory() as $row) {
			if ($row['slug'] === $slug) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function component_payload( string $slug ): array {
		$rows = [];
		foreach (WPCursor_Component_Loader::inventory() as $row) {
			if ($row['slug'] === $slug) {
				$rows[] = $row;
			}
		}

		$resolved = WPCursor_Component_Loader::resolve_component_file($slug);
		$rel      = $resolved
			? ltrim(str_replace('\\', '/', str_replace(ABSPATH, '', $resolved['file'])), '/')
			: null;
		$payload  = [
			'slug'               => $slug,
			'inventory_rows'     => $rows,
			'resolved_file_abs'  => $resolved ? str_replace('\\', '/', $resolved['file']) : null,
			'resolved_file_rel'  => $rel,
			'mode'               => $resolved['mode'] ?? null,
			'manifest_line'      => WPCursor_Component_Schema::admin_manifest_line(
				$slug,
				self::slug_max_mtime($slug)
			),
		];

		$loaded = WPCursor_Component_Schema::load_for_slug($slug);
		$payload['schema_state'] = $loaded['state'];
		if ($loaded['state'] === WPCursor_Component_Schema::STATE_OK && $loaded['parsed'] !== null) {
			$p                     = $loaded['parsed'];
			$payload['manifest']   = [
				'label'       => $p['label'] ?? '',
				'description' => $p['description'] ?? '',
				'version'     => $p['version'] ?? '',
				'updated'     => $p['updated'] ?? '',
			];
			$payload['props_schema'] = $p['props'] ?? [];
		} else {
			$payload['manifest']     = null;
			$payload['props_schema'] = null;
		}

		$defaults = WPCursor_Component_Loader::get_divi_template_defaults($slug);
		$payload['divi_template_defaults'] = $defaults;
		$payload['divi_template_lines']    = WPCursor_Component_Loader::format_props_lines($defaults);

		return $payload;
	}

	private static function slug_max_mtime( string $slug ): int {
		$m = 0;
		foreach (WPCursor_Component_Loader::inventory() as $row) {
			if ($row['slug'] !== $slug) {
				continue;
			}
			$p = WPCURSOR_PATH . ltrim($row['path'], '/');
			if (is_readable($p)) {
				$m = max($m, (int) filemtime($p));
			}
		}
		return $m;
	}

	/**
	 * @return array<string, string>
	 */
	private static function diagnostic_array(): array {
		$theme = wp_get_theme();
		$inv   = WPCursor_Component_Loader::inventory();
		$comp_n = count(array_unique(array_column($inv, 'slug')));
		$git    = file_exists(ABSPATH . '.git/config');
		$scan   = get_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
		$usage_n = is_array($scan) && isset($scan['rows']) ? count($scan['rows']) : 0;

		$log_line = ( defined('WPCURSOR_DEBUG') && WPCURSOR_DEBUG )
			? 'WPCURSOR_DEBUG actif'
			: (
				( defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('WP_DEBUG') && WP_DEBUG )
					? 'WP_DEBUG_LOG + WP_DEBUG actifs'
					: 'inactifs'
			);

		return [
			'php'                       => PHP_VERSION,
			'wordpress'                 => get_bloginfo('version'),
			'theme_stylesheet'          => $theme->get_stylesheet(),
			'theme_template'            => $theme->get_template(),
			'divi_detected'             => ( $theme->get_template() === 'Divi' || $theme->get_stylesheet() === 'Divi' ) ? 'yes' : 'no',
			'woocommerce'               => class_exists('WooCommerce') ? 'yes' : 'no',
			'wp_debug'                  => ( defined('WP_DEBUG') && WP_DEBUG ) ? 'true' : 'false',
			'disallow_file_edit'        => ( defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ) ? 'true' : 'false',
			'plugin_path'               => WPCURSOR_PATH,
			'components_writable'       => is_writable(WPCURSOR_PATH . 'components') ? 'yes' : 'no',
			'git_config_abspath'        => $git ? 'present' : 'absent',
			'distinct_components'       => (string) $comp_n,
			'usage_scan_cache_entries'  => (string) $usage_n,
			'wpcursor_logs'             => $log_line,
		];
	}
}

add_action('rest_api_init', [WPCursor_REST_API::class, 'register_routes']);
