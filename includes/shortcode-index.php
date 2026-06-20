<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Répertoire unifié : variantes enregistrées + shortcodes détectés sur le site, avec pages.
 */
final class WPCursor_Shortcode_Index {

	/**
	 * @return list<array{
	 *   id: string,
	 *   kind: string,
	 *   label: string,
	 *   component_slug: string,
	 *   props_summary: string,
	 *   shortcode: string,
	 *   fingerprint: string,
	 *   preset_id: string,
	 *   on_site: bool,
	 *   in_library: bool,
	 *   pages: list<array{post_id: int, title: string, status: string, type: string, edit_link: string, permalink: string}>
	 * }>
	 */
	public static function build(?array $scan = null): array {
		if ($scan === null) {
			$cached = get_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
			if (is_array($cached) && ! empty($cached['rows'])) {
				$scan = $cached;
			} else {
				$scan = WPCursor_Usage_Scan::scan_detailed(null);
			}
		}

		/** @var array<string, array<string, mixed>> $by_fp */
		$by_fp = [];

		foreach ($scan['rows'] ?? [] as $row) {
			$page = [
				'post_id'   => (int) ($row['post_id'] ?? 0),
				'title'     => (string) ($row['title'] ?? ''),
				'status'    => (string) ($row['status'] ?? ''),
				'type'      => (string) ($row['type'] ?? ''),
				'edit_link' => (string) ($row['edit_link'] ?? ''),
				'permalink' => (string) ($row['permalink'] ?? ''),
			];
			foreach ($row['instances'] ?? [] as $inst) {
				if (! is_array($inst) || empty($inst['slug'])) {
					continue;
				}
				$fp = (string) ($inst['fingerprint'] ?? '');
				if ($fp === '') {
					$fp = WPCursor_Usage_Scan::fingerprint(
						(string) $inst['slug'],
						is_array($inst['props'] ?? null) ? $inst['props'] : []
					);
				}
				if (! isset($by_fp[ $fp ])) {
					$by_fp[ $fp ] = [
						'fingerprint'     => $fp,
						'component_slug'  => (string) $inst['slug'],
						'shortcode'       => (string) ($inst['shortcode'] ?? ''),
						'props_summary'   => WPCursor_Shortcode_Presets::build_props_summary((string) ($inst['shortcode'] ?? '')),
						'preset_id'       => '',
						'label'           => '',
						'kind'            => 'live',
						'in_library'      => false,
						'on_site'         => true,
						'pages'           => [],
						'page_ids'        => [],
					];
				}
				$pid = $page['post_id'];
				if ($pid > 0 && ! in_array($pid, $by_fp[ $fp ]['page_ids'], true)) {
					$by_fp[ $fp ]['page_ids'][] = $pid;
					$by_fp[ $fp ]['pages'][]    = $page;
				}
			}
		}

		foreach (WPCursor_Shortcode_Presets::all() as $preset) {
			$props = self::props_from_shortcode($preset['shortcode']);
			$fp    = WPCursor_Usage_Scan::fingerprint($preset['component_slug'], $props);

			if (isset($by_fp[ $fp ])) {
				$by_fp[ $fp ]['preset_id']  = $preset['id'];
				$by_fp[ $fp ]['label']      = $preset['label'];
				$by_fp[ $fp ]['kind']       = 'preset';
				$by_fp[ $fp ]['in_library'] = true;
				if ($by_fp[ $fp ]['shortcode'] === '' || strlen($preset['shortcode']) > strlen($by_fp[ $fp ]['shortcode'])) {
					$by_fp[ $fp ]['shortcode'] = $preset['shortcode'];
				}
			} else {
				$by_fp[ $fp ] = [
					'fingerprint'     => $fp,
					'component_slug'  => $preset['component_slug'],
					'shortcode'       => $preset['shortcode'],
					'props_summary'   => $preset['props_summary'],
					'preset_id'       => $preset['id'],
					'label'           => $preset['label'],
					'kind'            => 'preset',
					'in_library'      => true,
					'on_site'         => false,
					'pages'           => [],
					'page_ids'        => [],
				];
			}
		}

		$out = [];
		foreach ($by_fp as $fp => $entry) {
			unset($entry['page_ids']);
			if ($entry['label'] === '') {
				$entry['label'] = self::auto_label($entry['component_slug'], $entry['props_summary']);
			}
			if ($entry['kind'] === 'live' && $entry['in_library']) {
				$entry['kind'] = 'preset';
			}
			$entry['id'] = $entry['preset_id'] !== '' ? $entry['preset_id'] : 'live_' . substr($fp, 0, 12);
			$out[]       = $entry;
		}

		usort($out, static function (array $a, array $b): int {
			$c = strcmp($a['component_slug'], $b['component_slug']);
			if ($c !== 0) {
				return $c;
			}
			return strcasecmp($a['label'], $b['label']);
		});

		return $out;
	}

	/**
	 * @param list<array<string, mixed>> $entries
	 * @return list<array<string, mixed>>
	 */
	public static function filter(array $entries, string $query, string $component_slug = ''): array {
		$query = mb_strtolower(trim($query));
		$component_slug = sanitize_key($component_slug);

		return array_values(array_filter($entries, static function (array $e) use ($query, $component_slug): bool {
			if ($component_slug !== '' && ($e['component_slug'] ?? '') !== $component_slug) {
				return false;
			}
			if ($query === '') {
				return true;
			}
			$hay = mb_strtolower(
				($e['label'] ?? '') . ' '
				. ($e['component_slug'] ?? '') . ' '
				. ($e['props_summary'] ?? '') . ' '
				. ($e['shortcode'] ?? '')
			);
			foreach ($e['pages'] ?? [] as $p) {
				$hay .= ' ' . mb_strtolower((string) ($p['title'] ?? ''));
			}

			return mb_strpos($hay, $query) !== false;
		}));
	}

	/**
	 * @return array<string, string>
	 */
	private static function props_from_shortcode(string $shortcode): array {
		if (preg_match('/\[wpcursor[^\]]*\](.*?)\[\/wpcursor\]/is', $shortcode, $m)) {
			return WPCursor_Component_Loader::parse_props_text(trim($m[1]));
		}

		return WPCursor_Usage_Scan::parse_shortcode_inline_attrs($shortcode);
	}

	private static function auto_label(string $slug, string $summary): string {
		if ($summary !== '') {
			return sprintf(
				/* translators: 1: component slug, 2: props excerpt */
				__('%1$s — %2$s', 'wpcursor'),
				$slug,
				$summary
			);
		}

		return sprintf(
			/* translators: %s: component slug */
			__('%s (sur le site)', 'wpcursor'),
			$slug
		);
	}
}
