<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Manifeste JSON pour le générateur admin et le module Divi (champs dynamiques).
 */
final class WPCursor_Generator_Manifest {

	/**
	 * @return array<string, array{label: string, props: array<string, array<string, mixed>>, has_template: bool, preview_nonce: string}>
	 */
	public static function build(): array {
		$inv   = WPCursor_Component_Loader::inventory();
		$slugs = array_values(array_unique(array_column($inv, 'slug')));
		sort($slugs, SORT_STRING);

		$out = [];
		foreach ($slugs as $slug) {
			$defaults   = WPCursor_Component_Loader::get_divi_template_defaults($slug);
			$label      = $slug;
			$props_spec = [];

			$pack = WPCursor_Component_Schema::load_for_slug($slug);
			if ($pack['state'] === WPCursor_Component_Schema::STATE_OK && is_array($pack['parsed'])) {
				if (! empty($pack['parsed']['label'])) {
					$label = (string) $pack['parsed']['label'];
				}
				if (! empty($pack['parsed']['props']) && is_array($pack['parsed']['props'])) {
					foreach ($pack['parsed']['props'] as $pname => $spec) {
						if (! is_string($pname) || ! is_array($spec)) {
							continue;
						}
						$pname = strtolower($pname);
						$type  = isset($spec['type']) ? strtolower((string) $spec['type']) : 'string';
						$vals  = [];
						if (isset($spec['values']) && is_array($spec['values'])) {
							foreach ($spec['values'] as $v) {
								if (is_string($v) || is_numeric($v)) {
									$vals[] = (string) $v;
								}
							}
						}
						$props_spec[ $pname ] = [
							'type'     => $type,
							'required' => ! empty($spec['required']),
							'values'   => $vals,
							'default'  => $defaults[ $pname ] ?? '',
							'selector' => isset($spec['css_selector']) ? sanitize_text_field((string) $spec['css_selector']) : '',
						];
					}
				}
			}

			foreach ($defaults as $pname => $value) {
				if (! isset($props_spec[ $pname ])) {
					$props_spec[ $pname ] = [
						'type'     => 'string',
						'required' => false,
						'values'   => [],
						'default'  => (string) $value,
						'selector' => '',
					];
				}
			}

			$template_path = WPCURSOR_PATH . 'components/' . $slug . '/divi-template.txt';
			$out[ $slug ]  = [
				'label'         => $label,
				'props'         => $props_spec,
				'has_template'  => is_readable($template_path),
				'preview_nonce' => wp_create_nonce('wpcursor_preview'),
			];
		}

		return $out;
	}
}
