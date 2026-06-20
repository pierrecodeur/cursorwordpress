<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Composant test-wp-cursor-ping3.
 *
 * Shortcode : [wpcursor name="test-wp-cursor-ping3"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$label   = isset($props['label']) && $props['label'] !== '' ? (string) $props['label'] : 'OK';
$variant = isset($props['variant']) ? sanitize_key((string) $props['variant']) : 'default';

if (!in_array($variant, ['default', 'compact'], true)) {
	$variant = 'default';
}
?>
<div class="wpcursor-component wpcursor-test-wp-cursor-ping3 wpcursor-test-wp-cursor-ping3--<?php echo esc_attr($variant); ?>" data-wpcursor="test-wp-cursor-ping3" data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>">
	<p class="wpcursor-test-wp-cursor-ping3__label"><?php echo esc_html($label); ?></p>
</div>
