<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Module Divi Builder : champs natifs par composant (Visual Builder React).
 */
class WPCursor_ET_Builder_Module extends ET_Builder_Module {

	public function init(): void {
		$this->name        = esc_html__('WP Cursor', 'wpcursor');
		$this->plural      = esc_html__('WP Cursor', 'wpcursor');
		$this->slug        = 'et_pb_wpcursor';
		$this->vb_support  = 'on';
		$this->folder_name = 'wpcursor';

		$toggles = [
			'main_content' => esc_html__('Composant', 'wpcursor'),
		];
		foreach (WPCursor_Divi_Module_Fields::tab_toggles() as $id => $meta) {
			$toggles[ $id ] = $meta['title'];
		}

		$this->settings_modal_toggles = [
			'general' => [
				'toggles' => $toggles,
			],
			'advanced' => [
				'toggles' => [
					'width' => [
						'title'    => et_builder_i18n('Sizing'),
						'priority' => 65,
					],
				],
			],
		];

		$this->advanced_fields = [
			'margin_padding' => [
				'css' => [
					'important' => ['custom_margin'],
				],
			],
			'fonts'          => false,
			'text'           => false,
			'button'         => false,
			'link_options'   => false,
		];
	}

	/**
	 * @return array<string, string>
	 */
	private function component_select_options(): array {
		$options = [
			'' => esc_html__('— Choisir un composant —', 'wpcursor'),
		];
		$labels = get_option('wpcursor_component_labels', []);
		if (! is_array($labels)) {
			$labels = [];
		}
		$seen = [];
		foreach (WPCursor_Component_Loader::inventory() as $row) {
			$slug = isset($row['slug']) ? sanitize_key((string) $row['slug']) : '';
			if ($slug === '' || isset($seen[ $slug ])) {
				continue;
			}
			$seen[ $slug ] = true;
			$label         = isset($labels[ $slug ]) && is_string($labels[ $slug ]) && $labels[ $slug ] !== ''
				? $labels[ $slug ]
				: $slug;
			$loaded        = WPCursor_Component_Schema::load_for_slug($slug);
			if ($loaded['state'] === WPCursor_Component_Schema::STATE_OK
				&& is_array($loaded['parsed'])
				&& ! empty($loaded['parsed']['label'])
			) {
				$label = (string) $loaded['parsed']['label'];
			}
			$options[ $slug ] = $label . ' (' . $slug . ')';
		}
		ksort($options, SORT_STRING);
		if (isset($options[''])) {
			$empty = $options[''];
			unset($options['']);
			$options = ['' => $empty] + $options;
		}

		return $options;
	}

	public function get_fields(): array {
		$generator_url = admin_url('admin.php?page=wpcursor&tab=generator');

		$fields = [
			'component_slug' => [
				'label'           => esc_html__('Composant', 'wpcursor'),
				'type'            => 'select',
				'option_category' => 'basic_option',
				'options'         => $this->component_select_options(),
				'description'     => wp_kses_post(
					sprintf(
						/* translators: %s: admin generator URL */
						__(
							'Choisissez le bloc, puis remplissez les onglets ci-dessous (Titres, Image, etc.). Vous pouvez aussi utiliser le <a href="%s" target="_blank" rel="noopener">générateur WP Cursor</a> dans un autre onglet.',
							'wpcursor'
						),
						esc_url($generator_url)
					)
				),
				'toggle_slug'     => 'main_content',
				'default'         => '',
			],
			'props_content'  => [
				'label'           => esc_html__('Sauvegarde legacy', 'wpcursor'),
				'type'            => 'hidden',
				'option_category' => 'basic_option',
				'default'         => '',
			],
		];

		return array_merge($fields, WPCursor_Divi_Module_Fields::build_prop_fields());
	}

	/**
	 * @param array<string, mixed> $attrs
	 */
	public function render($attrs, $content = null, $render_slug = ''): string {
		$slug = isset($this->props['component_slug'])
			? sanitize_key((string) $this->props['component_slug'])
			: '';

		$in_builder = function_exists('et_fb_is_enabled') && et_fb_is_enabled();
		$can_hint   = is_user_logged_in() && current_user_can(WPCURSOR_CAP);

		if ($slug === '') {
			if ($in_builder || $can_hint) {
				return sprintf(
					'<div class="et_pb_wpcursor et_pb_wpcursor--empty">%s</div>',
					esc_html__('WP Cursor : sélectionnez un composant dans les réglages du module.', 'wpcursor')
				);
			}

			return '';
		}

		$props = WPCursor_Divi_Module_Fields::collect_props_from_module($this->props, $slug);
		$inner = WPCursor_Component_Loader::render($slug, $props, WPCURSOR_RUNTIME_FRONT);

		$issues = WPCursor_Component_Loader::consume_last_render_schema_issues();
		if ($issues !== [] && ($in_builder || $can_hint)) {
			$inner .= sprintf(
				'<div class="et_pb_wpcursor-schema-hint" style="margin-top:8px;padding:8px 12px;background:#fff3cd;border-left:4px solid #dba617;font-size:13px;">%s</div>',
				esc_html(implode(' ', $issues))
			);
		}

		return sprintf(
			'<div%1$s class="%2$s et_pb_wpcursor">%3$s</div>',
			$this->module_id(),
			esc_attr($this->module_classname($render_slug)),
			$inner
		);
	}
}
