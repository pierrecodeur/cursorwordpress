<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Composant « Mon composant » — titre, texte et image optionnelle.
 *
 * [wpcursor name="mon-composant"]
 * [wpcursor name="mon-composant" image_url="https://…" image_position="right" title="Bonjour"]
 *
 * Props :
 *   title          : titre du bloc
 *   text           : paragraphe d'introduction
 *   image_url      : URL de l'image (médiathèque) ; si vide, aucune image n'est affichée
 *   image_alt      : texte alternatif de l'image
 *   image_position : left | right | top (défaut left)
 *   custom_html    : bloc HTML additionnel filtré (wp_kses_post)
 *   custom_css     : CSS additionnel propre à l'instance
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-mon-composant-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$title     = $prop_str($props, 'title', 'Mon composant');
$text      = $prop_str($props, 'text');
$image_url = isset($props['image_url']) ? esc_url((string) $props['image_url']) : '';
$image_alt = $prop_str($props, 'image_alt');

$image_position = isset($props['image_position']) ? strtolower((string) $props['image_position']) : 'left';
if (! in_array($image_position, ['left', 'right', 'top'], true)) {
	$image_position = 'left';
}

$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$has_image = ($image_url !== '');

$block_classes = ['wpcursor-component', 'wpcursor-mon-composant'];
if ($has_image) {
	$block_classes[] = 'wpcursor-mon-composant--has-image';
	$block_classes[] = 'wpcursor-mon-composant--image-' . $image_position;
}
?>
<div
	id="<?php echo esc_attr($instance_id); ?>"
	class="<?php echo esc_attr(implode(' ', $block_classes)); ?>"
	data-wpcursor="mon-composant"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-mon-composant__inner">
		<?php if ($has_image) : ?>
			<figure class="wpcursor-mon-composant__media">
				<img
					class="wpcursor-mon-composant__image"
					src="<?php echo esc_url($image_url); ?>"
					alt="<?php echo esc_attr($image_alt); ?>"
					loading="lazy"
					decoding="async"
				/>
			</figure>
		<?php endif; ?>

		<div class="wpcursor-mon-composant__body">
			<?php if ($title !== '') : ?>
				<h2 class="wpcursor-mon-composant__title"><?php echo esc_html($title); ?></h2>
			<?php endif; ?>

			<?php if ($text !== '') : ?>
				<p class="wpcursor-mon-composant__text"><?php echo esc_html($text); ?></p>
			<?php endif; ?>

			<?php if ($custom_html !== '') : ?>
				<div class="wpcursor-mon-composant__custom-html"><?php echo wp_kses_post($custom_html); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>"><?php echo esc_html($custom_css); ?></style>
<?php endif; ?>
