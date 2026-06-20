<?php
/**
 * Plugin Name: WP Cursor
 * Description: Shortcodes et points d’extension pour composants PHP versionnés (Cursor / Git). Divi garde la mise en page ; les blocs complexes vivent dans components/.
 * Version: 1.9.1
 * Author: Cours Thales
 * Text Domain: wpcursor
 * Domain Path: /languages
 * Requires at least: 6.0
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
	exit;
}

define('WPCURSOR_VERSION', '1.9.1');
define('WPCURSOR_MAIN_FILE', __FILE__);
define('WPCURSOR_PATH', plugin_dir_path(__FILE__));
define('WPCURSOR_URL', plugin_dir_url(__FILE__));
/** Capacité pour accéder au menu et aux réglages WP Cursor (attribuable à un rôle custom). */
define('WPCURSOR_CAP', 'manage_wpcursor');

/** Contexte rendu : front réel, prévisualisation admin, WP‑CLI render. */
define('WPCURSOR_RUNTIME_FRONT', 'front');
define('WPCURSOR_RUNTIME_ADMIN_PREVIEW', 'admin_preview');
define('WPCURSOR_RUNTIME_CLI', 'cli');

/** Transient du scan Utilisation (onglet admin + invalidation auto après saves / metas). */
define('WPCURSOR_USAGE_SCAN_TRANSIENT', 'wpcursor_usage_scan_v2');

require_once WPCURSOR_PATH . 'includes/logger.php';
require_once WPCURSOR_PATH . 'includes/capabilities.php';

add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain(
			'wpcursor',
			false,
			dirname(plugin_basename(WPCURSOR_MAIN_FILE)) . '/languages'
		);
	},
	0
);

register_activation_hook(
	WPCURSOR_MAIN_FILE,
	static function (): void {
		wpcursor_register_capabilities();
		WPCursor_Repo_History::run_install_setup();
	}
);

require_once WPCURSOR_PATH . 'includes/component-schema.php';
require_once WPCURSOR_PATH . 'includes/component-loader.php';
require_once WPCURSOR_PATH . 'includes/generator-manifest.php';
require_once WPCURSOR_PATH . 'includes/repo-history.php';
require_once WPCURSOR_PATH . 'includes/usage-scan.php';
require_once WPCURSOR_PATH . 'includes/validate.php';
require_once WPCURSOR_PATH . 'includes/rest-api.php';
require_once WPCURSOR_PATH . 'includes/shortcodes.php';
require_once WPCURSOR_PATH . 'includes/shortcode-presets.php';
require_once WPCURSOR_PATH . 'includes/shortcode-index.php';
require_once WPCURSOR_PATH . 'includes/divi-integration.php';
require_once WPCURSOR_PATH . 'includes/admin.php';
WPCursor_Repo_History::bootstrap();

add_action('init', static function () {
	WPCursor_Shortcodes::register();
});

if (is_admin()) {
	WPCursor_Admin::init();
	add_filter(
		'plugin_action_links_' . plugin_basename(WPCURSOR_MAIN_FILE),
		static function (array $links): array {
			$repo_url = admin_url('admin.php?page=wpcursor&tab=history');
			$settings_link = '<a href="' . esc_url(admin_url('admin.php?page=wpcursor')) . '">' . esc_html__('Réglages', 'wpcursor') . '</a>';
			$repo_link     = '<a href="' . esc_url($repo_url) . '">' . esc_html__('Ouvrir REPO', 'wpcursor') . '</a>';
			array_unshift($links, $settings_link, $repo_link);
			return $links;
		}
	);
}

if (defined('WP_CLI') && WP_CLI && class_exists('WP_CLI')) {
	require_once WPCURSOR_PATH . 'includes/cli.php';
}
