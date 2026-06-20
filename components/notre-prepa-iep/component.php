<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Notre Prépa IEP — lieux Paris et à distance.
 *
 * [wpcursor name="notre-prepa-iep"]
 * [wpcursor name="notre-prepa-iep" card1_image_url="https://…" card1_title="Stanislas"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-notre-prepa-iep-' . wp_rand(1000, 999999);

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

$card_defaults = [
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
	if (is_string($prop_key) && preg_match('/^card(\d+)_/', $prop_key, $m)) {
		$max_index = max($max_index, (int) $m[1]);
	}
}
if ($max_index === 0) {
	$max_index = count($card_defaults);
}

$cards = [];
for ($i = 1; $i <= $max_index; $i++) {
	$prefix  = 'card' . $i . '_';
	$default = $card_defaults[ $i - 1 ] ?? ['title' => '', 'subtitle' => '', 'alt' => ''];

	$title    = $prop_str($props, $prefix . 'title', $default['title']);
	$subtitle = $prop_str($props, $prefix . 'subtitle', $default['subtitle']);
	$image    = isset($props[ $prefix . 'image_url' ]) ? esc_url((string) $props[ $prefix . 'image_url' ]) : '';
	$alt      = $prop_str($props, $prefix . 'image_alt', $default['alt'] !== '' ? $default['alt'] : $title);
	$url      = isset($props[ $prefix . 'url' ]) ? esc_url((string) $props[ $prefix . 'url' ]) : '';

	if ($title === '' && $subtitle === '' && $image === '') {
		continue;
	}

	$cards[] = [
		'title'     => $title,
		'subtitle'  => $subtitle,
		'image_url' => $image,
		'image_alt' => $alt,
		'url'       => $url,
	];
}

if ($cards === []) {
	return;
}
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-notre-prepa-iep"
	data-wpcursor="notre-prepa-iep"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-notre-prepa-iep__inner">
		<?php if ($section_title !== '') : ?>
			<h2 class="wpcursor-notre-prepa-iep__heading"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>

		<div class="wpcursor-notre-prepa-iep__grid">
			<?php foreach ($cards as $card) : ?>
				<?php
				$tag        = $card['url'] !== '' ? 'a' : 'article';
				$tag_attrs  = $card['url'] !== ''
					? ' href="' . esc_url($card['url']) . '" class="wpcursor-notre-prepa-iep__card wpcursor-notre-prepa-iep__card--link"'
					: ' class="wpcursor-notre-prepa-iep__card"';
				?>
				<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- tag contrôlé. ?><?php echo $tag_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributs échappés. ?>>
					<?php if ($card['image_url'] !== '') : ?>
						<img
							class="wpcursor-notre-prepa-iep__photo"
							src="<?php echo esc_url($card['image_url']); ?>"
							alt="<?php echo esc_attr($card['image_alt']); ?>"
							loading="lazy"
							decoding="async"
							sizes="(max-width: 767px) 100vw, (max-width: 960px) 50vw, 480px"
						/>
					<?php else : ?>
						<div class="wpcursor-notre-prepa-iep__photo wpcursor-notre-prepa-iep__photo--placeholder" aria-hidden="true"></div>
					<?php endif; ?>

					<div class="wpcursor-notre-prepa-iep__overlay">
						<?php if ($card['title'] !== '') : ?>
							<p class="wpcursor-notre-prepa-iep__card-title"><?php echo esc_html($card['title']); ?></p>
						<?php endif; ?>
						<?php if ($card['subtitle'] !== '') : ?>
							<p class="wpcursor-notre-prepa-iep__card-subtitle"><?php echo esc_html($card['subtitle']); ?></p>
						<?php endif; ?>
					</div>
				</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-notre-prepa-iep__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
