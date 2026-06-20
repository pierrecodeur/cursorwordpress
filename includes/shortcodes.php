<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Shortcodes du plugin WP Cursor.
 */
final class WPCursor_Shortcodes {

	public static function register(): void {
		add_shortcode('wpcursor', [self::class, 'render_component']);
		/** @deprecated Utiliser [wpcursor] — conservé pour compatibilité avec les maquettes d’exemple. */
		add_shortcode('site_component', [self::class, 'render_component']);
	}

	/**
	 * [wpcursor name="slug" prop="valeur" …]
	 * Ou avec contenu éditable (recommandé pour Divi / clients) :
	 * [wpcursor name="slug"]
	 * title=Mon titre
	 * intro=Mon texte
	 * [/wpcursor]
	 *
	 * Charge components/slug/component.php (prioritaire) ou components/slug.php (legacy).
	 * Dans le fichier inclus : variable $wpcursor_context = [ 'name', 'props', 'mode' ].
	 */
	public static function render_component( $atts, $content = null, $tag = '' ): string {
		$atts = is_array($atts) ? $atts : [];

		$name = isset($atts['name']) ? strtolower(trim((string) $atts['name'])) : '';
		unset($atts['name']);

		if ($name === '' || ! preg_match('/^[a-z0-9_-]+$/', $name)) {
			return '';
		}

		$from_content = '';
		if ($content !== null) {
			$from_content = trim((string) $content);
		}

		$props_content = $from_content !== '' ? WPCursor_Component_Loader::parse_props_text($from_content) : [];
		$props_atts    = WPCursor_Component_Loader::sanitize_props($atts);
		$props         = array_merge($props_content, $props_atts);

		return WPCursor_Component_Loader::render($name, $props, WPCURSOR_RUNTIME_FRONT);
	}
}
