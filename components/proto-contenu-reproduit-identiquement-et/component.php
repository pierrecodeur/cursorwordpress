<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * proto-contenu-reproduit-identiquement-et — Section « Our Locations ».
 *
 * Reproduction fidèle d'une section de contenu type « nos boutiques » :
 * titre + sous-titre centrés, puis grille de cartes lieu (galerie photo,
 * titre, adresse, téléphone, horaires, note en étoiles).
 *
 * [wpcursor name="proto-contenu-reproduit-identiquement-et"]
 * [wpcursor name="proto-contenu-reproduit-identiquement-et" location1_title="…" location1_image1_url="https://…"]
 *
 * $wpcursor_context = [ 'name', 'props', 'mode', 'runtime' ]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];

$instance_id = 'wpcursor-proto-contenu-reproduit-identiquement-et-' . wp_rand(1000, 999999);

$fallback_image = defined('WPCURSOR_URL')
	? WPCURSOR_URL . 'assets/images/manquante.webp'
	: 'assets/images/manquante.webp';

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$section_title    = $prop_str($props, 'section_title', 'Our Locations');
$section_subtitle = $prop_str($props, 'section_subtitle', 'Visit one of our two Jerusalem locations for an exceptional wine shopping experience.');
$custom_html      = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css       = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$location_defaults = [
	[
		'title'   => 'Wine Lord City - Ben Sira',
		'address' => 'Ben Sira St 26, Jerusalem',
		'phone'   => '+972 2-992-6770',
		'hours'   => 'Open until 20:00',
		'rating'  => '5.0',
		'reviews' => '5 reviews',
	],
	[
		'title'   => 'Wine Lord City - Kaduri',
		'address' => 'HaRav Yitshak Kaduri St 2, Jerusalem',
		'phone'   => '+972 2-992-6770',
		'hours'   => 'Open until 20:00',
		'rating'  => '5.0',
		'reviews' => '8 reviews',
	],
];

// Combien de lieux ? On scanne location{n}_ dans les props, sinon défaut = 2.
$max_index = 0;
foreach (array_keys($props) as $prop_key) {
	if (is_string($prop_key) && preg_match('/^location(\d+)_/', $prop_key, $m)) {
		$max_index = max($max_index, (int) $m[1]);
	}
}
if ($max_index === 0) {
	$max_index = count($location_defaults);
}

// Collecte des URLs de galerie pour un lieu (location{n}_image{k}_url).
$collect_gallery = static function (array $props, int $index, string $fallback): array {
	$prefix = 'location' . $index . '_';
	$images = [];
	$max_img = 0;
	foreach (array_keys($props) as $prop_key) {
		if (is_string($prop_key) && preg_match('/^' . preg_quote($prefix, '/') . 'image(\d+)_url$/', $prop_key, $mm)) {
			$max_img = max($max_img, (int) $mm[1]);
		}
	}
	if ($max_img === 0) {
		$max_img = 4; // galerie par défaut : placeholders
	}
	for ($k = 1; $k <= $max_img; $k++) {
		$url = isset($props[ $prefix . 'image' . $k . '_url' ]) ? esc_url((string) $props[ $prefix . 'image' . $k . '_url' ]) : '';
		if ($url === '') {
			$url = esc_url($fallback);
		}
		if ($url !== '') {
			$images[] = $url;
		}
	}
	return $images;
};

$locations = [];
for ($i = 1; $i <= $max_index; $i++) {
	$prefix  = 'location' . $i . '_';
	$default = $location_defaults[ $i - 1 ] ?? [
		'title'   => '',
		'address' => '',
		'phone'   => '',
		'hours'   => '',
		'rating'  => '',
		'reviews' => '',
	];

	$title   = $prop_str($props, $prefix . 'title', $default['title']);
	$address = $prop_str($props, $prefix . 'address', $default['address']);
	$phone   = $prop_str($props, $prefix . 'phone', $default['phone']);
	$hours   = $prop_str($props, $prefix . 'hours', $default['hours']);
	$rating  = $prop_str($props, $prefix . 'rating', $default['rating']);
	$reviews = $prop_str($props, $prefix . 'reviews', $default['reviews']);
	$alt     = $prop_str($props, $prefix . 'image_alt', $title);

	if ($title === '' && $address === '' && $phone === '' && $hours === '') {
		continue;
	}

	$locations[] = [
		'title'   => $title,
		'address' => $address,
		'phone'   => $phone,
		'hours'   => $hours,
		'rating'  => $rating,
		'reviews' => $reviews,
		'alt'     => $alt,
		'gallery' => $collect_gallery($props, $i, $fallback_image),
	];
}

if ($locations === []) {
	return;
}

// Icônes SVG (décoratives).
$icon_pin   = '<svg class="wpcursor-proto-contenu-reproduit-identiquement-et__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 2a7 7 0 0 0-7 7c0 4.5 7 13 7 13s7-8.5 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg>';
$icon_phone = '<svg class="wpcursor-proto-contenu-reproduit-identiquement-et__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.3.2 2.5.57 3.6a1 1 0 0 1-.25 1l-2.22 2.2Z"/></svg>';
$icon_clock = '<svg class="wpcursor-proto-contenu-reproduit-identiquement-et__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
$icon_star  = '<svg class="wpcursor-proto-contenu-reproduit-identiquement-et__star" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.9 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9L12 2.5Z"/></svg>';
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-proto-contenu-reproduit-identiquement-et"
	data-wpcursor="proto-contenu-reproduit-identiquement-et"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-proto-contenu-reproduit-identiquement-et__inner">
		<?php if ($section_title !== '' || $section_subtitle !== '') : ?>
			<header class="wpcursor-proto-contenu-reproduit-identiquement-et__header">
				<?php if ($section_title !== '') : ?>
					<h2 class="wpcursor-proto-contenu-reproduit-identiquement-et__heading"><?php echo esc_html($section_title); ?></h2>
				<?php endif; ?>
				<?php if ($section_subtitle !== '') : ?>
					<p class="wpcursor-proto-contenu-reproduit-identiquement-et__subheading"><?php echo esc_html($section_subtitle); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<div class="wpcursor-proto-contenu-reproduit-identiquement-et__grid">
			<?php foreach ($locations as $location) : ?>
				<article class="wpcursor-proto-contenu-reproduit-identiquement-et__card">
					<?php if ($location['gallery'] !== []) : ?>
						<div class="wpcursor-proto-contenu-reproduit-identiquement-et__gallery">
							<div class="wpcursor-proto-contenu-reproduit-identiquement-et__gallery-track">
								<?php foreach ($location['gallery'] as $image_url) : ?>
									<img
										class="wpcursor-proto-contenu-reproduit-identiquement-et__photo"
										src="<?php echo esc_url($image_url); ?>"
										alt="<?php echo esc_attr($location['alt']); ?>"
										loading="lazy"
										decoding="async"
									/>
								<?php endforeach; ?>
							</div>
							<span class="wpcursor-proto-contenu-reproduit-identiquement-et__gallery-next" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M9 6l6 6-6 6"/></svg>
							</span>
						</div>
					<?php endif; ?>

					<div class="wpcursor-proto-contenu-reproduit-identiquement-et__body">
						<?php if ($location['title'] !== '') : ?>
							<h3 class="wpcursor-proto-contenu-reproduit-identiquement-et__card-title"><?php echo esc_html($location['title']); ?></h3>
						<?php endif; ?>

						<ul class="wpcursor-proto-contenu-reproduit-identiquement-et__info">
							<?php if ($location['address'] !== '') : ?>
								<li class="wpcursor-proto-contenu-reproduit-identiquement-et__info-row">
									<?php echo $icon_pin; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
									<span class="wpcursor-proto-contenu-reproduit-identiquement-et__info-text"><?php echo esc_html($location['address']); ?></span>
								</li>
							<?php endif; ?>
							<?php if ($location['phone'] !== '') : ?>
								<li class="wpcursor-proto-contenu-reproduit-identiquement-et__info-row">
									<?php echo $icon_phone; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
									<span class="wpcursor-proto-contenu-reproduit-identiquement-et__info-text"><?php echo esc_html($location['phone']); ?></span>
								</li>
							<?php endif; ?>
							<?php if ($location['hours'] !== '') : ?>
								<li class="wpcursor-proto-contenu-reproduit-identiquement-et__info-row">
									<?php echo $icon_clock; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
									<span class="wpcursor-proto-contenu-reproduit-identiquement-et__info-text"><?php echo esc_html($location['hours']); ?></span>
								</li>
							<?php endif; ?>
						</ul>

						<?php if ($location['rating'] !== '' || $location['reviews'] !== '') : ?>
							<div class="wpcursor-proto-contenu-reproduit-identiquement-et__rating">
								<span class="wpcursor-proto-contenu-reproduit-identiquement-et__stars" aria-hidden="true">
									<?php for ($s = 0; $s < 5; $s++) {
										echo $icon_star; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique.
									} ?>
								</span>
								<span class="wpcursor-proto-contenu-reproduit-identiquement-et__rating-text">
									<?php if ($location['rating'] !== '') : ?>
										<span class="wpcursor-proto-contenu-reproduit-identiquement-et__rating-value"><?php echo esc_html($location['rating']); ?></span>
									<?php endif; ?>
									<?php if ($location['reviews'] !== '') : ?>
										<span class="wpcursor-proto-contenu-reproduit-identiquement-et__rating-count">(<?php echo esc_html($location['reviews']); ?>)</span>
									<?php endif; ?>
								</span>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ($custom_html !== '') : ?>
			<div class="wpcursor-proto-contenu-reproduit-identiquement-et__custom-html">
				<?php echo wp_kses_post($custom_html); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
