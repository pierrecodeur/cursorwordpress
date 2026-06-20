<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Enregistrement du module Divi Builder et scripts Visual Builder.
 */
final class WPCursor_Divi_Integration {

	private static bool $module_registered = false;

	public static function init(): void {
		add_action('et_builder_ready', [self::class, 'register_module']);
		add_action('et_fb_enqueue_assets', [self::class, 'enqueue_visual_builder_assets'], 25);
		add_action('admin_enqueue_scripts', [self::class, 'enqueue_backend_builder_assets'], 25);
	}

	public static function register_module(): void {
		if (self::$module_registered || ! class_exists('ET_Builder_Module', false)) {
			return;
		}
		require_once WPCURSOR_PATH . 'includes/divi-module-fields.php';
		require_once WPCURSOR_PATH . 'includes/divi-module.php';
		if (! class_exists('WPCursor_ET_Builder_Module', false)) {
			return;
		}
		new WPCursor_ET_Builder_Module();
		self::$module_registered = true;
	}

	public static function enqueue_visual_builder_assets(): void {
		self::enqueue_builder_assets();
	}

	public static function enqueue_backend_builder_assets(): void {
		if (! is_admin() || ! self::is_divi_builder_screen()) {
			return;
		}
		self::enqueue_builder_assets();
	}

	private static function is_divi_builder_screen(): bool {
		if (function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled()) {
			return true;
		}
		// Backend Builder (BBB) : page avec et_pb activé.
		if (isset($_GET['et_pb']) && sanitize_text_field(wp_unslash($_GET['et_pb'])) !== '') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		return false;
	}

	private static function enqueue_builder_assets(): void {
		if (! current_user_can(WPCURSOR_CAP)) {
			return;
		}
		$js = WPCURSOR_PATH . 'assets/divi-builder.js';
		$css = WPCURSOR_PATH . 'assets/divi-builder.css';
		if (! is_readable($js)) {
			return;
		}
		wp_enqueue_media();
		if (is_readable($css)) {
			wp_enqueue_style(
				'wpcursor-divi-builder',
				WPCURSOR_URL . 'assets/divi-builder.css',
				[],
				WPCURSOR_VERSION
			);
		}
		wp_enqueue_script(
			'wpcursor-generator-fields-ui',
			WPCURSOR_URL . 'assets/generator-fields-ui.js',
			['jquery'],
			WPCURSOR_VERSION,
			true
		);
		wp_enqueue_script(
			'wpcursor-divi-builder',
			WPCURSOR_URL . 'assets/divi-builder.js',
			['jquery', 'wpcursor-generator-fields-ui'],
			WPCURSOR_VERSION,
			true
		);
		wp_localize_script(
			'wpcursor-divi-builder',
			'wpcursorDivi',
			[
				'restRoot'      => esc_url_raw(rest_url(WPCursor_REST_API::NS . '/')),
				'nonce'         => wp_create_nonce('wp_rest'),
				'generatorBase' => admin_url('admin.php?page=wpcursor&tab=generator&component='),
				'manifest'      => WPCursor_Generator_Manifest::build(),
				'i18n'          => [
					'pickComponent'    => __('Choisissez un composant pour afficher les champs.', 'wpcursor'),
					'templateDetected' => __('Modèle Divi — valeurs depuis divi-template.txt', 'wpcursor'),
					'noSchema'         => __('Aucun champ défini dans component.json.', 'wpcursor'),
					'advanced'         => __('Avancé (HTML / CSS / code)', 'wpcursor'),
					'mediaPick'        => __('Choisir dans la médiathèque', 'wpcursor'),
					'noMedia'          => __('Médiathèque indisponible.', 'wpcursor'),
					'guidedIntro'      => __('Remplissez les champs ci-dessous (comme dans WP Cursor → Générateur shortcode). Enregistrez avec la coche verte : Divi sauvegarde tout automatiquement.', 'wpcursor'),
					'showRaw'          => __('Afficher le mode expert (clé=valeur)', 'wpcursor'),
					'hideRaw'          => __('Masquer le mode expert', 'wpcursor'),
					'noUi'             => __('Formulaire non chargé : rechargez la page (Ctrl+F5) ou utilisez WP Cursor → Générateur shortcode dans l’admin.', 'wpcursor'),
				],
			]
		);
	}
}

WPCursor_Divi_Integration::init();
