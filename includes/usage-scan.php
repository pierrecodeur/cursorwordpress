<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Scan détaillé des shortcodes [wpcursor] / [site_component] (admin + WP‑CLI + REST).
 *
 * Pagination résultat : voir SCAN_ROW_LIMIT_* ; découverte postmeta limitée à META_DISTINCT_POST_CAP lignes SQL DISTINCT.
 */
final class WPCursor_Usage_Scan {

	public const SCAN_ROW_LIMIT_DEFAULT = 200;

	public const SCAN_ROW_LIMIT_MAX = 500;

	/** Limite SQL DISTINCT post_id dans postmeta (charge serveur sur très gros sites). */
	public const META_DISTINCT_POST_CAP = 500;

	/**
	 * @param int|null $limit Nombre max de lignes après tri (null = aucune limite ; défaut {@see SCAN_ROW_LIMIT_DEFAULT}, plafond {@see SCAN_ROW_LIMIT_MAX} si entier).
	 * @param int      $offset Décalage après tri par date de modification décroissante (sans limite : tout prendre après offset).
	 *
	 * @return array{rows: list<array<string, mixed>>, meta_hits: int, total_rows: int, limit: int|null, offset: int, meta_distinct_cap: int}
	 */
	public static function scan_detailed(?int $limit = self::SCAN_ROW_LIMIT_DEFAULT, int $offset = 0): array {
		global $wpdb;

		$ids = [];

		$sql = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			WHERE post_status IN ('publish','draft','private','pending','future')
			AND post_type NOT IN ('revision','nav_menu_item')
			AND (
				post_content LIKE %s OR post_content LIKE %s
			)",
			'%[wpcursor%',
			'%[site_component%'
		);
		foreach ($wpdb->get_col($sql) ?: [] as $id) {
			$ids[(int) $id] = true;
		}

		$meta_sql = $wpdb->prepare(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta}
			WHERE meta_value LIKE %s OR meta_value LIKE %s
			LIMIT %d",
			'%[wpcursor%',
			'%[site_component%',
			self::META_DISTINCT_POST_CAP
		);
		foreach ($wpdb->get_col($meta_sql) ?: [] as $mid) {
			$ids[(int) $mid] = true;
		}

		$meta_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				WHERE meta_value LIKE %s OR meta_value LIKE %s",
				'%[wpcursor%',
				'%[site_component%'
			)
		);

		$rows = [];
		foreach (array_keys($ids) as $post_id) {
			$post = get_post($post_id);
			if (! $post) {
				continue;
			}

			$from_c     = self::extract_shortcodes_from_text($post->post_content);
			$in_content = $from_c['names'] !== [] || $from_c['samples'] !== [];

			$meta_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_key, meta_value FROM {$wpdb->postmeta}
					WHERE post_id = %d
					AND (meta_value LIKE %s OR meta_value LIKE %s)",
					$post_id,
					'%[wpcursor%',
					'%[site_component%'
				)
			);

			$from_m = ['names' => [], 'samples' => [], 'meta_keys' => [], 'instances' => []];
			foreach ($meta_rows ?: [] as $mr) {
				$ex                    = self::extract_shortcodes_from_text($mr->meta_value);
				$from_m['names']       = array_merge($from_m['names'], $ex['names']);
				$from_m['samples']     = array_merge($from_m['samples'], $ex['samples']);
				$from_m['instances']   = array_merge($from_m['instances'], $ex['instances']);
				$from_m['meta_keys'][] = (string) $mr->meta_key;
			}
			$from_m['names']   = array_unique($from_m['names']);
			$from_m['samples'] = array_unique($from_m['samples']);
			$in_meta           = $from_m['names'] !== [] || $from_m['samples'] !== [];

			if (! $in_content && ! $in_meta) {
				continue;
			}

			$sources = [];
			if ($in_content) {
				$sources[] = 'post_content';
			}
			if ($in_meta) {
				$sources[] = 'post_meta';
			}

			$names      = array_unique(array_merge($from_c['names'], $from_m['names']));
			$samples    = array_slice(array_unique(array_merge($from_c['samples'], $from_m['samples'])), 0, 8);
			$instances  = array_merge($from_c['instances'] ?? [], $from_m['instances'] ?? []);

			$rows[] = [
				'post_id'           => (int) $post->ID,
				'title'             => get_the_title($post) ?: __('(sans titre)', 'wpcursor'),
				'type'              => $post->post_type,
				'status'            => $post->post_status,
				'modified'          => wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($post->post_modified_gmt . ' UTC')),
				'edit_link'         => get_edit_post_link($post->ID, ''),
				'permalink'         => get_permalink($post->ID) ?: '',
				'sources'           => $sources,
				'components'        => $names,
				'shortcode_samples' => $samples,
				'instances'         => $instances,
				'meta_keys_sample'  => array_slice(array_unique($from_m['meta_keys']), 0, 5),
			];
		}

		usort(
			$rows,
			static function ($a, $b) {
				return strcmp($b['modified'], $a['modified']);
			}
		);

		$total        = count($rows);
		$eff_offset   = max(0, $offset);
		$eff_limit    = null;

		if ($limit === null) {
			$slice = $eff_offset > 0 ? array_slice($rows, $eff_offset) : $rows;
		} else {
			$eff_limit = max(1, min(self::SCAN_ROW_LIMIT_MAX, $limit));
			$slice     = array_slice($rows, $eff_offset, $eff_limit);
		}

		return [
			'rows'               => $slice,
			'meta_hits'          => $meta_count,
			'total_rows'         => $total,
			'limit'              => $limit === null ? null : $eff_limit,
			'offset'             => $eff_offset,
			'meta_distinct_cap'  => self::META_DISTINCT_POST_CAP,
		];
	}

	/**
	 * @return array{names: list<string>, samples: list<string>, instances: list<array{shortcode: string, slug: string, props: array<string, string>, fingerprint: string}>}
	 */
	public static function extract_shortcodes_from_text(string $text): array {
		$instances = self::extract_shortcode_instances_from_text($text);
		$names     = [];
		$samples   = [];
		foreach ($instances as $inst) {
			$samples[] = $inst['shortcode'];
			if ($inst['slug'] !== '') {
				$names[] = $inst['slug'];
			}
		}

		return [
			'names'     => array_values(array_unique($names)),
			'samples'   => array_values(array_unique($samples)),
			'instances' => $instances,
		];
	}

	/**
	 * Extrait chaque shortcode complet (bloc ou une ligne) avec props normalisées.
	 *
	 * @return list<array{shortcode: string, slug: string, props: array<string, string>, fingerprint: string}>
	 */
	public static function extract_shortcode_instances_from_text(string $text): array {
		if ($text === '') {
			return [];
		}
		$instances = [];
		$consumed  = [];

		if (preg_match_all('/\[wpcursor\b[^\]]*\](.*?)\[\/wpcursor\]/is', $text, $blocks, PREG_OFFSET_CAPTURE)) {
			foreach ($blocks[0] as $i => $match) {
				$full    = $match[0];
				$offset  = $match[1];
				$consumed[] = [$offset, $offset + strlen($full)];
				$opening = $full;
				if (preg_match('/\[wpcursor\b([^\]]*)\]/i', $full, $om)) {
					$opening = $om[0];
				}
				$slug  = self::extract_shortcode_name_attr($opening);
				$body  = isset($blocks[1][ $i ][0]) ? trim($blocks[1][ $i ][0]) : '';
				$props = WPCursor_Component_Loader::parse_props_text($body);
				$props = array_merge(self::parse_shortcode_inline_attrs($opening), $props);
				if ($slug === '') {
					continue;
				}
				$instances[] = [
					'shortcode'   => $full,
					'slug'        => $slug,
					'props'       => $props,
					'fingerprint' => self::fingerprint($slug, $props),
				];
			}
		}

		if (preg_match_all('/\[(?:wpcursor|site_component)\s+([^\]]+)\]/i', $text, $inlines, PREG_OFFSET_CAPTURE)) {
			foreach ($inlines[0] as $i => $match) {
				$full   = $match[0];
				$offset = $match[1];
				$inside = false;
				foreach ($consumed as $range) {
					if ($offset >= $range[0] && $offset < $range[1]) {
						$inside = true;
						break;
					}
				}
				if ($inside) {
					continue;
				}
				$attrs = $inlines[1][ $i ][0];
				$slug  = self::extract_shortcode_name_attr($full);
				if ($slug === '') {
					continue;
				}
				$props = self::parse_shortcode_inline_attrs($full);
				$instances[] = [
					'shortcode'   => $full,
					'slug'        => $slug,
					'props'       => $props,
					'fingerprint' => self::fingerprint($slug, $props),
				];
			}
		}

		return $instances;
	}

	/**
	 * @return array<string, string>
	 */
	public static function parse_shortcode_inline_attrs(string $shortcode): array {
		$props = [];
		if (preg_match_all('/\b([a-z][a-z0-9_-]{0,63})\s*=\s*["\']([^"\']*)["\']/i', $shortcode, $m, PREG_SET_ORDER)) {
			foreach ($m as $pair) {
				$key = strtolower($pair[1]);
				if ($key !== 'name') {
					$props[ $key ] = $pair[2];
				}
			}
		}

		return WPCursor_Component_Loader::sanitize_props($props);
	}

	public static function fingerprint(string $component_slug, array $props): string {
		$component_slug = sanitize_key($component_slug);
		$norm           = [];
		foreach ($props as $k => $v) {
			$k = strtolower((string) $k);
			if ($k === 'name') {
				continue;
			}
			$norm[ $k ] = trim(wp_strip_all_tags((string) $v));
		}
		ksort($norm, SORT_STRING);

		return hash('sha256', $component_slug . '|' . wp_json_encode($norm));
	}

	public static function extract_shortcode_name_attr(string $shortcode): string {
		if (preg_match('/\bname\s*=\s*["\']([^"\']+)["\']/i', $shortcode, $m)) {
			return strtolower(sanitize_key($m[1]));
		}
		return '';
	}

	/**
	 * Attributs du shortcode hors `name` (pour croisement avec component.json).
	 *
	 * @return list<string>
	 */
	public static function extract_shortcode_prop_keys(string $shortcode): array {
		$keys = [];
		if (preg_match_all('/\b([a-z][a-z0-9_-]{0,63})\s*=\s*["\'][^"\']*["\']/i', $shortcode, $m)) {
			foreach ($m[1] as $k) {
				$k = strtolower((string) $k);
				if ($k !== 'name') {
					$keys[ $k ] = true;
				}
			}
		}
		return array_keys($keys);
	}

	/**
	 * @param array{rows?: list<array<string,mixed>>} $scan
	 *
	 * @return array<string, list<string>> slug → liste de clés d’attributs distinctes
	 */
	public static function usage_attribute_keys_by_component(array $scan): array {
		$out = [];
		foreach ($scan['rows'] ?? [] as $row) {
			foreach ($row['shortcode_samples'] ?? [] as $sample) {
				$slug = self::extract_shortcode_name_attr((string) $sample);
				if ($slug === '') {
					continue;
				}
				if (! isset($out[ $slug ])) {
					$out[ $slug ] = [];
				}
				foreach (self::extract_shortcode_prop_keys((string) $sample) as $key) {
					$out[ $slug ][ $key ] = true;
				}
			}
		}
		$result = [];
		foreach ($out as $slug => $map) {
			$k           = array_keys($map);
			sort($k, SORT_STRING);
			$result[ $slug ] = $k;
		}
		return $result;
	}
}

/**
 * Vide le cache transient du scan Utilisation (ré-affichage à jour immédiat).
 */
function wpcursor_flush_usage_cache(): void {
	if (! defined('WPCURSOR_USAGE_SCAN_TRANSIENT')) {
		return;
	}
	delete_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
}

add_action('save_post', 'wpcursor_flush_usage_cache');
add_action('deleted_post', 'wpcursor_flush_usage_cache');
add_action('added_post_meta', 'wpcursor_flush_usage_cache');
add_action('updated_post_meta', 'wpcursor_flush_usage_cache');
add_action('deleted_post_meta', 'wpcursor_flush_usage_cache');
