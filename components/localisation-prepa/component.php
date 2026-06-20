<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Bannière localisation prépa — cartes lieux.
 *
 * [wpcursor name="localisation-prepa"]
 * [wpcursor name="localisation-prepa" location1_image_url="https://…" location1_title="Stanislas"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-localisation-prepa-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$section_title = $prop_str(
	$props,
	'section_title',
	'Notre Prépa IEP est disponible à Paris ou à distance'
);
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$location_defaults = [
	[
		'title'    => 'Stanislas',
		'subtitle' => 'Lycée - 6e arrondissement de Paris',
		'alt'      => 'Lycée Stanislas, Paris 6e',
	],
	[
		'title'    => 'Fénelon Sainte-Marie',
		'subtitle' => 'Lycée - 8e arrondissement de Paris',
		'alt'      => 'Lycée Fénelon Sainte-Marie, Paris 8e',
	],
	[
		'title'    => 'Saint-Thomas d\'Aquin',
		'subtitle' => 'Lycée - 7e arrondissement de Paris',
		'alt'      => 'Lycée Saint-Thomas d\'Aquin, Paris 7e',
	],
	[
		'title'    => 'Depuis chez vous',
		'subtitle' => 'En visioconférence',
		'alt'      => 'Prépa en visioconférence',
	],
];

$max_index = 0;
foreach (array_keys($props) as $prop_key) {
	if (is_string($prop_key) && preg_match('/^location(\d+)_/', $prop_key, $m)) {
		$max_index = max($max_index, (int) $m[1]);
	}
}
if ($max_index === 0) {
	$max_index = count($location_defaults);
}

$locations = [];
for ($i = 1; $i <= $max_index; $i++) {
	$prefix  = 'location' . $i . '_';
	$default = $location_defaults[ $i - 1 ] ?? ['title' => '', 'subtitle' => '', 'alt' => ''];

	$title    = $prop_str($props, $prefix . 'title', $default['title']);
	$subtitle = $prop_str($props, $prefix . 'subtitle', $default['subtitle']);
	$image    = isset($props[ $prefix . 'image_url' ]) ? esc_url((string) $props[ $prefix . 'image_url' ]) : '';
	$alt      = $prop_str($props, $prefix . 'image_alt', $default['alt'] !== '' ? $default['alt'] : $title);

	if ($title === '' && $subtitle === '' && $image === '') {
		continue;
	}

	$locations[] = [
		'title'     => $title,
		'subtitle'  => $subtitle,
		'image_url' => $image,
		'image_alt' => $alt,
	];
}

if ($locations === []) {
	return;
}
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-localisation-prepa"
	data-wpcursor="localisation-prepa"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-localisation-prepa__inner">
		<?php if ($section_title !== '') : ?>
			<h2 class="wpcursor-localisation-prepa__heading"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>

		<div class="wpcursor-localisation-prepa__grid">
			<?php foreach ($locations as $location) : ?>
				<article class="wpcursor-localisation-prepa__card">
					<?php if ($location['image_url'] !== '') : ?>
						<img
							class="wpcursor-localisation-prepa__photo"
							src="<?php echo esc_url($location['image_url']); ?>"
							alt="<?php echo esc_attr($location['image_alt']); ?>"
							loading="lazy"
							decoding="async"
						/>
					<?php else : ?>
						<div class="wpcursor-localisation-prepa__photo wpcursor-localisation-prepa__photo--placeholder" aria-hidden="true"></div>
					<?php endif; ?>

					<div class="wpcursor-localisation-prepa__overlay">
						<?php if ($location['title'] !== '') : ?>
							<p class="wpcursor-localisation-prepa__card-title"><?php echo esc_html($location['title']); ?></p>
						<?php endif; ?>
						<?php if ($location['subtitle'] !== '') : ?>
							<p class="wpcursor-localisation-prepa__card-subtitle"><?php echo esc_html($location['subtitle']); ?></p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-localisation-prepa__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
