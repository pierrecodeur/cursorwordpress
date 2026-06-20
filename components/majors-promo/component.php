<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Majors de promo — cartes classement (badge laurier + nom + filière / établissement).
 *
 * [wpcursor name="majors-promo"]
 * Cartes : card1_*, card2_*, … (rank, name, specialty, institution, badge_url).
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-majors-promo-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$badge_for_rank = static function (string $rank) use ($prop_str): string {
	$uploads = trailingslashit(content_url('uploads'));
	$map     = [
		'1re' => $uploads . '2026/03/resultats-concours-14-1.png',
		'2e'  => $uploads . '2026/03/resultats-concours-13-1.png',
	];
	$rank = strtolower($rank);
	return $map[ $rank ] ?? $map['1re'];
};

$heading_tag = isset($props['heading_tag']) ? strtolower((string) $props['heading_tag']) : 'h2';
if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'p'], true)) {
	$heading_tag = 'h2';
}

$section_title = $prop_str(
	$props,
	'section_title',
	'Plusieurs majors de promo ont suivi notre prépa'
);
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$default_cards = [
	1 => [
		'rank'        => '1re',
		'name'        => 'Laetitia O',
		'specialty'   => 'Médecine',
		'institution' => 'Université Catholique de Lille',
	],
	2 => [
		'rank'        => '1re',
		'name'        => 'Anais K',
		'specialty'   => 'Médecine',
		'institution' => 'Université Antilles-Guyane',
	],
	3 => [
		'rank'        => '1re',
		'name'        => 'Lou G',
		'specialty'   => 'Médecine',
		'institution' => 'Université de Corse',
	],
	4 => [
		'rank'        => '2e',
		'name'        => 'Evanshi K',
		'specialty'   => 'Médecine',
		'institution' => 'Sorbonne Paris Nord',
	],
];

$cards = [];
for ($i = 1; $i <= 12; $i++) {
	$prefix   = 'card' . $i . '_';
	$defaults = $default_cards[ $i ] ?? [
		'rank'        => '1re',
		'name'        => '',
		'specialty'   => 'Médecine',
		'institution' => '',
	];

	$name        = $prop_str($props, $prefix . 'name', $defaults['name']);
	$specialty   = $prop_str($props, $prefix . 'specialty', $defaults['specialty']);
	$institution = $prop_str($props, $prefix . 'institution', $defaults['institution']);
	$rank        = $prop_str($props, $prefix . 'rank', $defaults['rank']);
	$badge_alt   = $prop_str($props, $prefix . 'badge_alt', 'Résultats prépa concours');

	$has_prop = false;
	foreach ($props as $key => $value) {
		if (is_string($key) && strpos($key, $prefix) === 0 && trim((string) $value) !== '') {
			$has_prop = true;
			break;
		}
	}

	if ($name === '' && $institution === '' && ! isset($default_cards[ $i ]) && ! $has_prop) {
		continue;
	}

	$badge_url = isset($props[ $prefix . 'badge_url' ]) ? esc_url((string) $props[ $prefix . 'badge_url' ]) : '';
	if ($badge_url === '') {
		$badge_url = esc_url($badge_for_rank($rank));
	}

	$cards[] = [
		'name'        => $name,
		'specialty'   => $specialty,
		'institution' => $institution,
		'badge_url'   => $badge_url,
		'badge_alt'   => $badge_alt,
		'rank'        => $rank,
	];
}

if ($cards === []) {
	$cards = array_values(
		array_map(
			static function (array $defaults) use ($badge_for_rank): array {
				return [
					'name'        => $defaults['name'],
					'specialty'   => $defaults['specialty'],
					'institution' => $defaults['institution'],
					'badge_url'   => esc_url($badge_for_rank($defaults['rank'])),
					'badge_alt'   => 'Résultats prépa concours',
					'rank'        => $defaults['rank'],
				];
			},
			$default_cards
		)
	);
}
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-majors-promo"
	data-wpcursor="majors-promo"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-majors-promo__inner">
		<?php if ($section_title !== '') : ?>
			<<?php echo esc_html($heading_tag); ?> class="wpcursor-majors-promo__heading"><?php echo esc_html($section_title); ?></<?php echo esc_html($heading_tag); ?>>
		<?php endif; ?>

		<div class="wpcursor-majors-promo__grid">
			<?php foreach ($cards as $card) : ?>
				<article class="wpcursor-majors-promo__card">
					<?php if ($card['badge_url'] !== '') : ?>
						<img
							class="wpcursor-majors-promo__badge"
							src="<?php echo esc_url($card['badge_url']); ?>"
							alt="<?php echo esc_attr($card['badge_alt']); ?>"
							width="200"
							height="150"
							loading="lazy"
							decoding="async"
						/>
					<?php endif; ?>

					<div class="wpcursor-majors-promo__body">
						<?php if ($card['name'] !== '') : ?>
							<p class="wpcursor-majors-promo__name"><em><?php echo esc_html($card['name']); ?></em></p>
						<?php endif; ?>

						<?php if ($card['specialty'] !== '' || $card['institution'] !== '') : ?>
							<p class="wpcursor-majors-promo__detail">
								<?php if ($card['specialty'] !== '') : ?>
									<strong><?php echo esc_html($card['specialty']); ?></strong>
								<?php endif; ?>
								<?php if ($card['specialty'] !== '' && $card['institution'] !== '') : ?>
									<span class="wpcursor-majors-promo__sep" aria-hidden="true"> – </span>
								<?php endif; ?>
								<?php if ($card['institution'] !== '') : ?>
									<span class="wpcursor-majors-promo__institution"><?php echo esc_html($card['institution']); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-majors-promo__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
