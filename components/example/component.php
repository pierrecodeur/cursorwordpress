<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Composant d’exemple — à dupliquer / renommer le dossier.
 *
 * Shortcode : [wpcursor name="example"]
 * Avec props optionnelles : [wpcursor name="example" ma_cle="valeur"]
 *
 * Variables fournies par le plugin :
 *   $wpcursor_context = [
 *     'name' => string,
 *     'props' => array,
 *     'mode' => 'modern'|'legacy',
 *     'runtime' => 'front'|'admin_preview'|'cli'
 *   ]
 * Évite animations / tracking si runtime !== 'front' (prévisualisation admin ou CLI).
 */

$cx         = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime    = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$is_preview = ($runtime === 'admin_preview');
$variant    = isset($cx['props']['variant']) ? sanitize_key((string) $cx['props']['variant']) : '';

if (! $is_preview) {
	// Exemple : tracking / analytics / scripts lourds uniquement sur le front réel.
}
?>
<div class="wpcursor-component wpcursor-example<?php echo $variant !== '' ? ' wpcursor-example--' . esc_attr($variant) : ''; ?>" data-wpcursor="example" data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>">
	<p><?php esc_html_e('WP Cursor : composant « example » actif. Remplace ce fichier par ton propre composant.', 'wpcursor'); ?></p>
</div>
