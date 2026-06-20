<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Composant « Coucou Edouard ».
 *
 * Shortcode : [wpcursor name="coucou-edouard"]
 * Variante claire : [wpcursor name="coucou-edouard" variant="light"]
 * ou variant="claire"
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$variant = isset($props['variant']) ? sanitize_key((string) $props['variant']) : 'light';
$text    = isset($props['text']) && $props['text'] !== '' ? (string) $props['text'] : 'coucou Edouard';
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';
$instance_id = 'wpcursor-coucou-edouard-' . wp_rand(1000, 999999);

if ($variant === 'claire') {
	$variant = 'light';
}
if ($variant !== 'light') {
	$variant = 'light';
}
?>
<div id="<?php echo esc_attr($instance_id); ?>" class="wpcursor-component wpcursor-coucou-edouard wpcursor-coucou-edouard--<?php echo esc_attr($variant); ?>" data-wpcursor="coucou-edouard" data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>">
	<p class="wpcursor-coucou-edouard__text"><?php echo esc_html($text); ?></p>
	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-coucou-edouard__custom-html"><?php echo wp_kses_post($custom_html); ?></div>
	<?php endif; ?>
</div>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>"><?php echo esc_html($custom_css); ?></style>
<?php endif; ?>
