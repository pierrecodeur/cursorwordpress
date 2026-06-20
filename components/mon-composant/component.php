<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Composant titre + texte.
 *
 * Shortcode : [wpcursor name="mon-composant"]
 * Props : title, heading_level, text, style, align.
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';

$title = isset($props['title']) ? trim(wp_strip_all_tags((string) $props['title'])) : '';
if ($title === '') {
	$title = 'Un accompagnement vers la reussite du projet d’etude superieure de chaque eleve';
}

$text = isset($props['text']) ? trim((string) $props['text']) : '';
if ($text === '') {
	$text = 'Cours Thales propose des dossiers pour aider les lyceens a faire les bons choix d’orientation et reussir leur scolarite.';
}

$allowed_heading_levels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
$heading_level          = isset($props['heading_level']) ? sanitize_key((string) $props['heading_level']) : 'h2';
if (! in_array($heading_level, $allowed_heading_levels, true)) {
	$heading_level = 'h2';
}

$allowed_styles = ['simple', 'accent', 'card'];
$style          = isset($props['style']) ? sanitize_key((string) $props['style']) : 'simple';
if (! in_array($style, $allowed_styles, true)) {
	$style = 'simple';
}

$allowed_alignments = ['left', 'center'];
$align              = isset($props['align']) ? sanitize_key((string) $props['align']) : 'left';
if (! in_array($align, $allowed_alignments, true)) {
	$align = 'left';
}

$classes = [
	'wpcursor-component',
	'wpcursor-mon-composant',
	'wpcursor-mon-composant--style-' . $style,
	'wpcursor-mon-composant--align-' . $align,
];
?>
<section class="<?php echo esc_attr(implode(' ', $classes)); ?>" data-wpcursor="mon-composant" data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>">
	<<?php echo esc_html($heading_level); ?> class="wpcursor-mon-composant__title"><?php echo esc_html($title); ?></<?php echo esc_html($heading_level); ?>>
	<p class="wpcursor-mon-composant__text"><?php echo nl2br(esc_html($text)); ?></p>
</section>
