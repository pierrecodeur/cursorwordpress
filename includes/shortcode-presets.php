<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Bibliothèque de variantes shortcode : même composant (design), plusieurs jeux de textes nommés.
 * N’altère pas le rendu front — stockage admin uniquement (option wpcursor_shortcode_presets_v1).
 */
final class WPCursor_Shortcode_Presets {

	public const OPTION_KEY = 'wpcursor_shortcode_presets_v1';

	/**
	 * @return list<array{id: string, component_slug: string, label: string, shortcode: string, props_summary: string, notes: string, created: string, updated: string}>
	 */
	public static function all(): array {
		$raw = get_option(self::OPTION_KEY, []);
		if (! is_array($raw)) {
			return [];
		}
		$out = [];
		foreach ($raw as $row) {
			if (! is_array($row)) {
				continue;
			}
			$norm = self::normalize_row($row);
			if ($norm !== null) {
				$out[] = $norm;
			}
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
	 * @return list<array<string, mixed>>
	 */
	public static function for_component(string $slug): array {
		$slug = sanitize_key($slug);
		if ($slug === '') {
			return [];
		}

		return array_values(array_filter(self::all(), static function (array $row) use ($slug): bool {
			return $row['component_slug'] === $slug;
		}));
	}

	public static function get(string $id): ?array {
		$id = sanitize_key($id);
		if ($id === '') {
			return null;
		}
		foreach (self::all() as $row) {
			if ($row['id'] === $id) {
				return $row;
			}
		}

		return null;
	}

	/**
	 * @return array{id: string, component_slug: string, label: string, shortcode: string, props_summary: string, notes: string, created: string, updated: string}|null
	 */
	public static function save(
		string $component_slug,
		string $label,
		string $shortcode,
		string $notes = '',
		?string $id = null
	): ?array {
		$component_slug = sanitize_key($component_slug);
		$label          = sanitize_text_field($label);
		$shortcode      = trim($shortcode);
		$notes          = sanitize_textarea_field($notes);

		if ($component_slug === '' || $label === '' || $shortcode === '') {
			return null;
		}
		if (! preg_match('/\[wpcursor\b/i', $shortcode) && ! preg_match('/\[site_component\b/i', $shortcode)) {
			return null;
		}

		$now  = gmdate('Y-m-d\TH:i:s\Z');
		$rows = self::raw_rows();
		$id   = $id !== null ? sanitize_key($id) : '';

		if ($id !== '') {
			$found = false;
			foreach ($rows as $i => $row) {
				if (! is_array($row) || ($row['id'] ?? '') !== $id) {
					continue;
				}
				$rows[ $i ] = [
					'id'              => $id,
					'component_slug'  => $component_slug,
					'label'           => $label,
					'shortcode'       => $shortcode,
					'props_summary'   => self::build_props_summary($shortcode),
					'notes'           => $notes,
					'created'         => is_string($row['created'] ?? null) ? $row['created'] : $now,
					'updated'         => $now,
				];
				$found = true;
				break;
			}
			if (! $found) {
				return null;
			}
		} else {
			$id     = self::new_id();
			$rows[] = [
				'id'              => $id,
				'component_slug'  => $component_slug,
				'label'           => $label,
				'shortcode'       => $shortcode,
				'props_summary'   => self::build_props_summary($shortcode),
				'notes'           => $notes,
				'created'         => $now,
				'updated'         => $now,
			];
		}

		update_option(self::OPTION_KEY, $rows, false);

		return self::get($id);
	}

	public static function delete(string $id): bool {
		$id = sanitize_key($id);
		if ($id === '') {
			return false;
		}
		$rows    = self::raw_rows();
		$before  = count($rows);
		$rows    = array_values(array_filter($rows, static function ($row) use ($id) {
			return ! is_array($row) || ($row['id'] ?? '') !== $id;
		}));
		if (count($rows) === $before) {
			return false;
		}
		update_option(self::OPTION_KEY, $rows, false);

		return true;
	}

	/**
	 * @return array<string, list<array<string, mixed>>>
	 */
	public static function grouped_by_component(): array {
		$groups = [];
		foreach (self::all() as $row) {
			$slug = $row['component_slug'];
			if (! isset($groups[ $slug ])) {
				$groups[ $slug ] = [];
			}
			$groups[ $slug ][] = $row;
		}
		ksort($groups, SORT_STRING);

		return $groups;
	}

	private static function new_id(): string {
		return 'var_' . substr(md5(uniqid('', true)), 0, 12);
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	private static function raw_rows(): array {
		$raw = get_option(self::OPTION_KEY, []);
		return is_array($raw) ? $raw : [];
	}

	/**
	 * @param array<string, mixed> $row
	 * @return array{id: string, component_slug: string, label: string, shortcode: string, props_summary: string, notes: string, created: string, updated: string}|null
	 */
	private static function normalize_row(array $row): ?array {
		$id   = isset($row['id']) ? sanitize_key((string) $row['id']) : '';
		$slug = isset($row['component_slug']) ? sanitize_key((string) $row['component_slug']) : '';
		$label = isset($row['label']) ? sanitize_text_field((string) $row['label']) : '';
		$shortcode = isset($row['shortcode']) ? trim((string) $row['shortcode']) : '';
		if ($id === '' || $slug === '' || $label === '' || $shortcode === '') {
			return null;
		}

		return [
			'id'             => $id,
			'component_slug' => $slug,
			'label'          => $label,
			'shortcode'      => $shortcode,
			'props_summary'  => isset($row['props_summary'])
				? sanitize_text_field((string) $row['props_summary'])
				: self::build_props_summary($shortcode),
			'notes'          => isset($row['notes']) ? sanitize_textarea_field((string) $row['notes']) : '',
			'created'        => is_string($row['created'] ?? null) ? $row['created'] : '',
			'updated'        => is_string($row['updated'] ?? null) ? $row['updated'] : '',
		];
	}

	public static function build_props_summary(string $shortcode): string {
		$shortcode = trim($shortcode);
		if (preg_match('/\[wpcursor[^\]]*\](.*?)\[\/wpcursor\]/is', $shortcode, $m)) {
			$props = WPCursor_Component_Loader::parse_props_text(trim($m[1]));
		} else {
			$props = [];
			if (preg_match_all('/\s([a-z0-9_-]+)="([^"]*)"/i', $shortcode, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					if (strtolower($match[1]) !== 'name') {
						$props[ strtolower($match[1]) ] = $match[2];
					}
				}
			}
		}
		$hints = [];
		foreach (['heading', 'title', 'text', 'quote', 'item1', 'problem1'] as $key) {
			if (! empty($props[ $key ])) {
				$val = wp_strip_all_tags((string) $props[ $key ]);
				if (strlen($val) > 48) {
					$val = substr($val, 0, 45) . '…';
				}
				$hints[] = $key . '=' . $val;
			}
		}
		if ($hints === []) {
			$keys = array_keys($props);
			sort($keys);
			return implode(', ', array_slice($keys, 0, 4));
		}

		return implode(' · ', array_slice($hints, 0, 2));
	}
}
