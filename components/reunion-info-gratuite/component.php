<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Réunion d'information gratuite — formulaire quiz + détails événement.
 *
 * [wpcursor name="reunion-info-gratuite"]
 * [wpcursor name="reunion-info-gratuite" form_id="42" cta_url="https://…"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-reunion-info-gratuite-' . wp_rand(1000, 999999);

$heading_tag = isset($props['heading_tag']) ? strtolower((string) $props['heading_tag']) : 'h2';
if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'p'], true)) {
	$heading_tag = 'h2';
}

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$form_title  = $prop_str($props, 'form_title', 'Trouvez la prépa qui vous correspond');
$form_id     = isset($props['form_id']) ? absint($props['form_id']) : 0;
$form_shortcode = $prop_str($props, 'form_shortcode');
$eyebrow     = $prop_str($props, 'eyebrow', 'Une question ?');
$title       = $prop_str($props, 'title', 'Réunion d\'information gratuite :');
$subtitle    = $prop_str($props, 'subtitle', 'Comprendre et intégrer Sciences Po');
$event_date  = $prop_str($props, 'event_date', 'Jeudi 6 novembre à 18h30');
$event_location = $prop_str($props, 'event_location', 'En visioconférence');
$event_duration = $prop_str($props, 'event_duration', 'Pendant 60min');
$program_title = $prop_str($props, 'program_title', 'Au programme :');
$cta_text    = $prop_str($props, 'cta_text', 'S\'inscrire à la réunion d\'information');
$cta_url     = isset($props['cta_url']) ? esc_url((string) $props['cta_url']) : '';
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$form_embed_markup = '';
if ($form_shortcode !== '') {
	$form_embed_markup = do_shortcode($form_shortcode);
} elseif ($form_id > 0) {
	$form_embed_markup = do_shortcode(
		sprintf(
			'[gravityform id="%d" title="false" description="false" ajax="true"]',
			$form_id
		)
	);
}

$is_preview = ($runtime === 'admin_preview');

$programs = [];
foreach ($props as $key => $value) {
	if (is_string($key) && preg_match('/^program([0-9]{1,3})$/', $key, $m)) {
		$txt = trim((string) $value);
		if ($txt !== '') {
			$programs[ (int) $m[1] ] = $txt;
		}
	}
}
if ($programs === []) {
	$defaults = [
		'Qu\'est ce que Sciences Po ?',
		'Comprendre ce qu\'est « l\'esprit Sciences Po »',
		'Les attendus du dossier de candidature à Sciences Po Paris, Bordeaux et Grenoble',
		'Les épreuves du Concours Commun des IEP',
		'Les méthodes exclusives de Cours Thalès pour réussir les admissions',
	];
	foreach ($defaults as $i => $text) {
		$key = 'program' . ( $i + 1 );
		$programs[ $i + 1 ] = isset($props[ $key ]) && trim((string) $props[ $key ]) !== ''
			? trim((string) $props[ $key ])
			: $text;
	}
} else {
	ksort($programs, SORT_NUMERIC);
	$programs = array_values($programs);
}

$meta_items = array_values(
	array_filter(
		[
			['icon' => 'calendar', 'text' => $event_date],
			['icon' => 'location', 'text' => $event_location],
			['icon' => 'duration', 'text' => $event_duration],
		],
		static fn( array $row ): bool => $row['text'] !== ''
	)
);
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-reunion-info-gratuite"
	data-wpcursor="reunion-info-gratuite"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-reunion-info-gratuite__inner">
		<div class="wpcursor-reunion-info-gratuite__grid">
			<div class="wpcursor-reunion-info-gratuite__form-col">
				<div class="wpcursor-reunion-info-gratuite__card-stack">
					<div class="wpcursor-reunion-info-gratuite__card-layer wpcursor-reunion-info-gratuite__card-layer--3" aria-hidden="true"></div>
					<div class="wpcursor-reunion-info-gratuite__card-layer wpcursor-reunion-info-gratuite__card-layer--2" aria-hidden="true"></div>
					<div class="wpcursor-reunion-info-gratuite__card">
						<?php if ($form_title !== '') : ?>
							<h2 class="wpcursor-reunion-info-gratuite__form-title"><?php echo esc_html($form_title); ?></h2>
						<?php endif; ?>

						<?php if ($form_embed_markup !== '') : ?>
							<div class="wpcursor-reunion-info-gratuite__form-embed">
								<?php echo $form_embed_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output ?>
							</div>
						<?php else : ?>
							<div class="wpcursor-reunion-info-gratuite__form-demo" aria-hidden="true">
								<div class="wpcursor-reunion-info-gratuite__progress" role="presentation">
									<span class="wpcursor-reunion-info-gratuite__progress-step is-active"></span>
									<span class="wpcursor-reunion-info-gratuite__progress-step"></span>
									<span class="wpcursor-reunion-info-gratuite__progress-step"></span>
								</div>
								<div class="wpcursor-reunion-info-gratuite__question-row">
									<p class="wpcursor-reunion-info-gratuite__question">Quel est votre niveau ?</p>
									<span class="wpcursor-reunion-info-gratuite__step">Question 1/2</span>
								</div>
								<ul class="wpcursor-reunion-info-gratuite__options">
									<li class="wpcursor-reunion-info-gratuite__option is-selected">Première</li>
									<li class="wpcursor-reunion-info-gratuite__option">Terminale</li>
									<li class="wpcursor-reunion-info-gratuite__option">Bac+1</li>
									<li class="wpcursor-reunion-info-gratuite__option">Autres</li>
								</ul>
							</div>
							<?php if ($is_preview) : ?>
								<p class="wpcursor-reunion-info-gratuite__form-hint">
									<?php esc_html_e('Renseignez form_id dans le shortcode pour afficher le formulaire Gravity Forms.', 'wpcursor'); ?>
								</p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="wpcursor-reunion-info-gratuite__content">
				<?php if ($eyebrow !== '') : ?>
					<p class="wpcursor-reunion-info-gratuite__eyebrow"><?php echo esc_html($eyebrow); ?></p>
				<?php endif; ?>

				<?php if ($title !== '') : ?>
					<<?php echo esc_html($heading_tag); ?> class="wpcursor-reunion-info-gratuite__title"><?php echo esc_html($title); ?></<?php echo esc_html($heading_tag); ?>>
				<?php endif; ?>

				<?php if ($subtitle !== '') : ?>
					<p class="wpcursor-reunion-info-gratuite__subtitle"><?php echo esc_html($subtitle); ?></p>
				<?php endif; ?>

				<?php if ($meta_items !== []) : ?>
					<div class="wpcursor-reunion-info-gratuite__divider" aria-hidden="true"></div>
					<ul class="wpcursor-reunion-info-gratuite__meta">
						<?php foreach ($meta_items as $item) : ?>
							<li class="wpcursor-reunion-info-gratuite__meta-item wpcursor-reunion-info-gratuite__meta-item--<?php echo esc_attr($item['icon']); ?>">
								<span class="wpcursor-reunion-info-gratuite__meta-icon" aria-hidden="true"></span>
								<span class="wpcursor-reunion-info-gratuite__meta-text"><?php echo esc_html($item['text']); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ($programs !== []) : ?>
					<div class="wpcursor-reunion-info-gratuite__divider" aria-hidden="true"></div>
					<?php if ($program_title !== '') : ?>
						<p class="wpcursor-reunion-info-gratuite__program-title"><?php echo esc_html($program_title); ?></p>
					<?php endif; ?>
					<ul class="wpcursor-reunion-info-gratuite__program-list">
						<?php foreach ($programs as $program_text) : ?>
							<li><?php echo esc_html($program_text); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ($cta_text !== '' && $cta_url !== '') : ?>
					<a class="wpcursor-reunion-info-gratuite__cta" href="<?php echo esc_url($cta_url); ?>">
						<?php echo esc_html($cta_text); ?>
					</a>
				<?php elseif ($cta_text !== '') : ?>
					<span class="wpcursor-reunion-info-gratuite__cta wpcursor-reunion-info-gratuite__cta--static">
						<?php echo esc_html($cta_text); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-reunion-info-gratuite__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
