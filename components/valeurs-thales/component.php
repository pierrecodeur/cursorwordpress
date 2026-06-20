<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Valeurs Thalès — grille de 4 valeurs avec icônes et liens.
 *
 * [wpcursor name="valeurs-thales"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-valeurs-thales-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}

	return $default;
};

$prop_url = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return esc_url((string) $props[ $key ]);
	}

	return $default;
};

$allowed_svg = [
	'svg'      => [
		'class'       => true,
		'viewBox'     => true,
		'viewbox'     => true,
		'fill'        => true,
		'xmlns'       => true,
		'aria-hidden' => true,
		'focusable'   => true,
	],
	'path'     => [
		'd'               => true,
		'fill'            => true,
		'stroke'          => true,
		'stroke-width'    => true,
		'stroke-linecap'  => true,
		'stroke-linejoin' => true,
	],
	'circle'   => [
		'cx'           => true,
		'cy'           => true,
		'r'            => true,
		'fill'         => true,
		'stroke'       => true,
		'stroke-width' => true,
	],
	'polyline' => [
		'points'          => true,
		'fill'            => true,
		'stroke'          => true,
		'stroke-width'    => true,
		'stroke-linecap'  => true,
		'stroke-linejoin' => true,
	],
];

$render_icon = static function (string $icon_type): string {
	switch ($icon_type) {
		case 'professors':
			return '<svg class="wpcursor-valeurs-thales__svg" viewBox="0 0 96 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="48" cy="25" r="14" fill="#fff" stroke="currentColor" stroke-width="4"/><path d="M25 66c0-14 10-24 23-24s23 10 23 24" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="24" cy="20" r="11" fill="#fff" stroke="currentColor" stroke-width="4"/><path d="M6 55c1-11 9-18 19-18 7 0 12 3 16 8" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linecap="round"/><circle cx="72" cy="20" r="11" fill="#fff" stroke="currentColor" stroke-width="4"/><path d="M55 45c4-5 9-8 16-8 10 0 18 7 19 18" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>';

		case 'books':
			return '<svg class="wpcursor-valeurs-thales__svg" viewBox="0 0 96 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M16 14 52 3l28 10-36 12-28-11Z" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><path d="M16 14v13l28 11 36-12V13" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><path d="M16 28v13l28 11 36-12V27" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><path d="M16 42v13l28 11 36-12V41" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/></svg>';

		case 'cap':
			return '<svg class="wpcursor-valeurs-thales__svg" viewBox="0 0 96 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M8 25 48 7l40 18-40 17L8 25Z" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><path d="M30 39v12c7 7 29 7 36 0V39" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M78 30v21" stroke="currentColor" stroke-width="4" stroke-linecap="round"/><path d="M78 51c-4 5-4 10 0 15 4-5 4-10 0-15Z" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><path d="M45 22 66 27" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>';

		case 'head-gear':
		default:
			return '<svg class="wpcursor-valeurs-thales__svg" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M18 68c-5 0-7-2-7-7V47c0-4-3-6-5-8 5-2 6-7 6-13C12 12 23 4 36 4c12 0 22 8 24 20 1 8 4 14 9 19-4 1-8 2-11 2v11c0 6-4 10-10 10H18Z" fill="#fff" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><circle cx="35" cy="30" r="9" fill="#fff" stroke="currentColor" stroke-width="4"/><path d="M35 14v6M35 40v6M19 30h6M45 30h6M24 19l4 4M46 19l-4 4M24 41l4-4M46 41l-4-4" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>';
	}
};

$item_defaults = [
	[
		'title'     => 'Exigence et bienveillance',
		'icon_type' => 'head-gear',
	],
	[
		'title'     => 'Professeurs agrégés, normaliens, colleurs',
		'icon_type' => 'professors',
	],
	[
		'title'     => 'En présentiel ou en visio',
		'icon_type' => 'books',
	],
	[
		'title'     => 'Des résultats exceptionnels',
		'icon_type' => 'cap',
	],
];

$items = [];
for ($i = 1; $i <= 4; $i++) {
	$defaults = $item_defaults[ $i - 1 ];
	$title    = $prop_str($props, 'item' . $i . '_title', $defaults['title']);

	if ($title === '') {
		continue;
	}

	$items[] = [
		'title'          => $title,
		'icon_type'      => sanitize_key($prop_str($props, 'item' . $i . '_icon_type', $defaults['icon_type'])),
		'icon_image_url' => $prop_url($props, 'item' . $i . '_icon_image_url'),
		'icon_alt'       => $prop_str($props, 'item' . $i . '_icon_alt', ''),
		'link_text'      => $prop_str($props, 'item' . $i . '_link_text', 'En savoir plus'),
		'link_url'       => $prop_url($props, 'item' . $i . '_link_url'),
	];
}

$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-valeurs-thales"
	data-wpcursor="valeurs-thales"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<?php if ($items !== []) : ?>
		<div class="wpcursor-valeurs-thales__grid">
			<?php foreach ($items as $item) : ?>
				<article class="wpcursor-valeurs-thales__item">
					<div class="wpcursor-valeurs-thales__icon-wrap" aria-hidden="<?php echo esc_attr($item['icon_alt'] === '' ? 'true' : 'false'); ?>">
						<span class="wpcursor-valeurs-thales__icon-shadow"></span>
						<?php if ($item['icon_image_url'] !== '') : ?>
							<img
								class="wpcursor-valeurs-thales__icon-img"
								src="<?php echo esc_url($item['icon_image_url']); ?>"
								alt="<?php echo esc_attr($item['icon_alt']); ?>"
								loading="lazy"
							/>
						<?php else : ?>
							<?php echo wp_kses($render_icon($item['icon_type']), $allowed_svg); ?>
						<?php endif; ?>
					</div>

					<h3 class="wpcursor-valeurs-thales__title"><?php echo esc_html($item['title']); ?></h3>

					<?php if ($item['link_text'] !== '' && $item['link_url'] !== '') : ?>
						<a class="wpcursor-valeurs-thales__link" href="<?php echo esc_url($item['link_url']); ?>">
							<?php echo esc_html($item['link_text']); ?>
						</a>
					<?php elseif ($item['link_text'] !== '') : ?>
						<span class="wpcursor-valeurs-thales__link wpcursor-valeurs-thales__link--static">
							<?php echo esc_html($item['link_text']); ?>
						</span>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-valeurs-thales__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
