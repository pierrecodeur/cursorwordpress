<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Accueil — admission : témoignage(s) élève admis (carrousel optionnel).
 *
 * [wpcursor name="accueil-admission"]
 * Slides 2 et 3 : préfixer les clés par slide2_ et slide3_ (voir divi-template.txt).
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$heading_tag = isset($props['heading_tag']) ? strtolower((string) $props['heading_tag']) : 'h2';
if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'p'], true)) {
	$heading_tag = 'h2';
}
$subheading_tag = isset($props['subheading_tag']) ? strtolower((string) $props['subheading_tag']) : 'h3';
if (! in_array($subheading_tag, ['h2', 'h3', 'h4', 'p'], true)) {
	$subheading_tag = 'h3';
}
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';
$instance_id = 'wpcursor-accueil-admission-' . wp_rand(1000, 999999);

$slide_defaults = [
	'' => [
		'image_url'       => '',
		'image_alt'       => 'Portrait de l\'élève',
		'heading'         => 'Marie, Admise à Sciences Po',
		'problems_title'  => 'Problématiques & difficultés rencontrées dans son parcours pour Sciences Po :',
		'problem1'        => 'Méconnaissance des attentes du concours et du rythme de travail exigé en prépa.',
		'problem2'        => 'Difficultés à structurer ses révisions et à gérer le stress avant les épreuves.',
		'problem3'        => 'Besoin de méthode pour la dissertation, l\'épreuve d\'histoire et la culture générale.',
		'results_title'   => 'Résultats et réussites après la prépa Sciences Po Cours Thalès :',
		'result1'         => 'Admise à Sciences Po Paris en 2023',
		'result2'         => 'Progression régulière aux concours blancs tout au long de l\'année',
		'quote'           => '« Cours Thalès m\'a donné une méthode claire et un accompagnement humain. J\'ai gagné en confiance et j\'ai abordé le concours sereinement. »',
	],
];

/**
 * @param array<string, string> $props
 * @param array<string, string> $defaults
 *
 * @return array<string, string>|null
 */
$build_slide = static function (array $props, string $prefix, array $defaults): ?array {
	$get = static function (string $key) use ($props, $prefix, $defaults): string {
		$prop_key = $prefix . $key;
		if (isset($props[ $prop_key ]) && $props[ $prop_key ] !== '') {
			return (string) $props[ $prop_key ];
		}
		return isset($defaults[ $key ]) ? (string) $defaults[ $key ] : '';
	};

	$heading = $get('heading');
	if ($heading === '' && $get('image_url') === '' && $get('quote') === '') {
		return null;
	}

	$problems = [];
	foreach (['problem1', 'problem2', 'problem3'] as $pk) {
		$text = $get($pk);
		if ($text !== '') {
			$problems[] = $text;
		}
	}

	$results = [];
	foreach (['result1', 'result2'] as $rk) {
		$text = $get($rk);
		if ($text !== '') {
			$results[] = $text;
		}
	}

	return [
		'image_url'      => $get('image_url'),
		'image_alt'      => $get('image_alt'),
		'heading'        => $heading,
		'problems_title' => $get('problems_title'),
		'problems'       => $problems,
		'results_title'  => $get('results_title'),
		'results'        => $results,
		'quote'          => $get('quote'),
	];
};

$slides = [];
foreach (['', 'slide2_', 'slide3_'] as $prefix) {
	if ($prefix === '') {
		$defaults = $slide_defaults[''];
	} else {
		$defaults = array_fill_keys(array_keys($slide_defaults['']), '');
	}
	$slide = $build_slide($props, $prefix, $defaults);
	if ($slide !== null) {
		$slides[] = $slide;
	}
}

if ($slides === []) {
	return;
}

$multi = count($slides) > 1;
?>
<section
	class="wpcursor-component wpcursor-accueil-admission<?php echo $multi ? ' wpcursor-accueil-admission--carousel' : ''; ?>"
	id="<?php echo esc_attr($instance_id); ?>"
	data-wpcursor="accueil-admission"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
	<?php echo $multi ? ' data-slide-count="' . esc_attr((string) count($slides)) . '"' : ''; ?>
>
	<div class="wpcursor-accueil-admission__frame">
		<?php if ($multi) : ?>
			<button type="button" class="wpcursor-accueil-admission__nav wpcursor-accueil-admission__nav--prev" data-action="prev" aria-label="<?php esc_attr_e('Témoignage précédent', 'wpcursor'); ?>">
				<span aria-hidden="true">&lsaquo;</span>
			</button>
		<?php endif; ?>

		<div class="wpcursor-accueil-admission__viewport">
			<?php foreach ($slides as $index => $slide) : ?>
				<article
					class="wpcursor-accueil-admission__slide"
					data-slide-index="<?php echo esc_attr((string) $index); ?>"
					<?php echo ( $multi && $index > 0 ) ? ' hidden' : ''; ?>
				>
					<div class="wpcursor-accueil-admission__card">
						<div class="wpcursor-accueil-admission__media">
							<?php if ($slide['image_url'] !== '') : ?>
								<img
									class="wpcursor-accueil-admission__photo"
									src="<?php echo esc_url($slide['image_url']); ?>"
									alt="<?php echo esc_attr($slide['image_alt']); ?>"
									loading="lazy"
									decoding="async"
								/>
							<?php else : ?>
								<div class="wpcursor-accueil-admission__photo wpcursor-accueil-admission__photo--placeholder" role="img" aria-label="<?php echo esc_attr($slide['image_alt']); ?>"></div>
							<?php endif; ?>
						</div>

						<div class="wpcursor-accueil-admission__body">
							<?php if ($slide['heading'] !== '') : ?>
								<<?php echo esc_html($heading_tag); ?> class="wpcursor-accueil-admission__heading"><?php echo esc_html($slide['heading']); ?></<?php echo esc_html($heading_tag); ?>>
							<?php endif; ?>

							<?php if ($slide['problems_title'] !== '' || $slide['problems'] !== []) : ?>
								<div class="wpcursor-accueil-admission__block">
									<?php if ($slide['problems_title'] !== '') : ?>
										<<?php echo esc_html($subheading_tag); ?> class="wpcursor-accueil-admission__subheading"><?php echo esc_html($slide['problems_title']); ?></<?php echo esc_html($subheading_tag); ?>>
									<?php endif; ?>
									<?php if ($slide['problems'] !== []) : ?>
										<ul class="wpcursor-accueil-admission__list">
											<?php foreach ($slide['problems'] as $problem) : ?>
												<li><?php echo esc_html($problem); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ($slide['results_title'] !== '' || $slide['results'] !== []) : ?>
								<div class="wpcursor-accueil-admission__block wpcursor-accueil-admission__block--results">
									<?php if ($slide['results_title'] !== '') : ?>
										<<?php echo esc_html($subheading_tag); ?> class="wpcursor-accueil-admission__subheading"><?php echo esc_html($slide['results_title']); ?></<?php echo esc_html($subheading_tag); ?>>
									<?php endif; ?>
									<?php if ($slide['results'] !== []) : ?>
										<ul class="wpcursor-accueil-admission__badges">
											<?php foreach ($slide['results'] as $result) : ?>
												<li class="wpcursor-accueil-admission__badge">
													<span class="wpcursor-accueil-admission__badge-icon" aria-hidden="true"></span>
													<span class="wpcursor-accueil-admission__badge-text"><?php echo esc_html($result); ?></span>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ($slide['quote'] !== '') : ?>
								<blockquote class="wpcursor-accueil-admission__quote">
									<p><?php echo esc_html($slide['quote']); ?></p>
								</blockquote>
							<?php endif; ?>
							<?php if ($custom_html !== '') : ?>
								<div class="wpcursor-accueil-admission__custom-html">
									<?php echo wp_kses_post($custom_html); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ($multi) : ?>
			<button type="button" class="wpcursor-accueil-admission__nav wpcursor-accueil-admission__nav--next" data-action="next" aria-label="<?php esc_attr_e('Témoignage suivant', 'wpcursor'); ?>">
				<span aria-hidden="true">&rsaquo;</span>
			</button>
		<?php endif; ?>
	</div>

	<?php if ($multi) : ?>
		<div class="wpcursor-accueil-admission__pagination" role="tablist" aria-label="<?php esc_attr_e('Choisir un témoignage', 'wpcursor'); ?>">
			<?php foreach ($slides as $index => $slide) : ?>
				<button
					type="button"
					class="wpcursor-accueil-admission__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
					role="tab"
					data-slide-to="<?php echo esc_attr((string) $index); ?>"
					aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
					aria-label="<?php echo esc_attr(sprintf(/* translators: %d: slide number */ __('Témoignage %d', 'wpcursor'), $index + 1)); ?>"
				>
					<span class="screen-reader-text"><?php echo esc_html(sprintf(__('Témoignage %d', 'wpcursor'), $index + 1)); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
