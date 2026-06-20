<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Hero entête de cocon — section sous le header (pages silo).
 *
 * [wpcursor name="hero-entete-cocon"]
 * [wpcursor name="hero-entete-cocon" title="…" form_id="42"]
 * [wpcursor name="hero-entete-cocon" form_shortcode="[gravityform id=\"1\" title=\"false\"]"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-hero-entete-cocon-' . wp_rand(1000, 999999);

$heading_tag = isset($props['heading_tag']) ? strtolower((string) $props['heading_tag']) : 'h1';
if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'p'], true)) {
	$heading_tag = 'h1';
}

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$badge           = $prop_str($props, 'badge', 'Prépa Sciences Po, à Paris ou en ligne – ScPo Paris & IEP Concours commun');
$title           = $prop_str($props, 'title', 'Intégrez Sciences Po avec Cours Thalès');
$intro           = $prop_str($props, 'intro', 'En première et en terminale, nous vous accompagnons dès le lycée pour vous aider à réussir vos admissions et à vous préparer aux exigences des concours.');
$show_reviews    = ! isset($props['show_reviews']) || (string) $props['show_reviews'] !== 'no';
$reviews_rating  = $prop_str($props, 'reviews_rating', '4.8');
$reviews_count   = $prop_str($props, 'reviews_count', '565 avis');
$reviews_url     = isset($props['reviews_url']) ? esc_url((string) $props['reviews_url']) : '';
$stat1_value     = $prop_str($props, 'stat1_value', '68%');
$stat1_label     = $prop_str($props, 'stat1_label', 'D\'admis à Science Po Paris');
$stat2_value     = $prop_str($props, 'stat2_value', '68%');
$stat2_label     = $prop_str($props, 'stat2_label', 'D\'admis à Science Po Paris');
$form_title      = $prop_str($props, 'form_title', 'Trouvez la prépa qui vous correspond');
$select1_label   = $prop_str($props, 'select1_label', 'Quel est votre niveau ?');
$select1_ph      = $prop_str($props, 'select1_placeholder', 'Sélectionnez votre niveau');
$select2_label   = $prop_str($props, 'select2_label', 'Quel est votre objectif ?');
$select2_ph      = $prop_str($props, 'select2_placeholder', 'Sélectionnez votre objectif');
$form_action_url = isset($props['form_action_url']) ? esc_url((string) $props['form_action_url']) : '';
$form_id         = isset($props['form_id']) ? absint($props['form_id']) : 0;
$form_shortcode  = $prop_str($props, 'form_shortcode');

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
$phone           = $prop_str($props, 'phone', '01 42 05 41 36');
$phone_url       = isset($props['phone_url']) && trim((string) $props['phone_url']) !== ''
	? esc_url((string) $props['phone_url'])
	: 'tel:' . preg_replace('/\D+/', '', $phone);
$phone_caption   = $prop_str($props, 'phone_caption', 'Nous sommes à votre écoute');
$custom_html     = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css      = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$stats = [];
if ($stat1_value !== '' && $stat1_label !== '') {
	$stats[] = ['value' => $stat1_value, 'label' => $stat1_label];
}
if ($stat2_value !== '' && $stat2_label !== '') {
	$stats[] = ['value' => $stat2_value, 'label' => $stat2_label];
}

$render_stat = static function (array $stat): void {
	?>
	<div class="wpcursor-hero-entete-cocon__stat">
		<div class="wpcursor-hero-entete-cocon__stat-badge" aria-hidden="true">
			<svg class="wpcursor-hero-entete-cocon__stat-wreath" viewBox="0 0 80 80" focusable="false" aria-hidden="true">
				<ellipse cx="40" cy="40" rx="34" ry="34" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.35"/>
				<path d="M40 8c-4 6-10 8-14 6 2 8 0 14-4 18 8-2 14 0 18 4-6-4-8-10-6-14 6 4 12 2 16-2-8 2-14-2-18-6 4 6 10 8 14 6-2-8 0-14 4-18-8 2-14 0-18-4 6 4 8 10 6 14-6-4-12-2-16 2 8-2 14 2 18 6-4-6-10-8-14-6z" fill="none" stroke="currentColor" stroke-width="1.2" opacity="0.5"/>
			</svg>
			<span class="wpcursor-hero-entete-cocon__stat-value"><?php echo esc_html($stat['value']); ?></span>
		</div>
		<p class="wpcursor-hero-entete-cocon__stat-label"><?php echo esc_html($stat['label']); ?></p>
	</div>
	<?php
};
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-hero-entete-cocon"
	data-wpcursor="hero-entete-cocon"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-hero-entete-cocon__shell">
		<div class="wpcursor-hero-entete-cocon__bg">
			<div class="wpcursor-hero-entete-cocon__inner">
				<div class="wpcursor-hero-entete-cocon__grid<?php echo $stats !== [] ? ' wpcursor-hero-entete-cocon__grid--has-stats' : ''; ?>">
					<div class="wpcursor-hero-entete-cocon__content">
						<?php if ($badge !== '') : ?>
							<p class="wpcursor-hero-entete-cocon__badge"><?php echo esc_html($badge); ?></p>
						<?php endif; ?>

						<?php if ($title !== '') : ?>
							<<?php echo esc_html($heading_tag); ?> class="wpcursor-hero-entete-cocon__title"><?php echo esc_html($title); ?></<?php echo esc_html($heading_tag); ?>>
						<?php endif; ?>

						<?php if ($intro !== '') : ?>
							<p class="wpcursor-hero-entete-cocon__intro"><?php echo esc_html($intro); ?></p>
						<?php endif; ?>

						<?php if ($show_reviews) : ?>
							<?php
							$reviews_tag   = $reviews_url !== '' ? 'a' : 'div';
							$reviews_attrs = $reviews_url !== '' ? ' href="' . esc_url($reviews_url) . '" rel="noopener noreferrer" target="_blank"' : '';
							?>
							<<?php echo esc_html($reviews_tag); ?> class="wpcursor-hero-entete-cocon__reviews"<?php echo $reviews_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attrs built with esc_url ?>>
								<span class="wpcursor-hero-entete-cocon__reviews-google" aria-hidden="true">
									<svg viewBox="0 0 24 24" width="22" height="22" focusable="false" aria-hidden="true">
										<path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.56c2.08-1.92 3.28-4.74 3.28-8.1z"/>
										<path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.56-2.77c-.98.66-2.23 1.06-3.72 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
										<path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
										<path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
									</svg>
								</span>
								<span class="wpcursor-hero-entete-cocon__reviews-rating"><?php echo esc_html($reviews_rating); ?></span>
								<span class="wpcursor-hero-entete-cocon__reviews-stars" aria-hidden="true">★★★★★</span>
								<span class="wpcursor-hero-entete-cocon__reviews-count"><?php echo esc_html($reviews_count); ?></span>
							</<?php echo esc_html($reviews_tag); ?>>
						<?php endif; ?>
					</div>

					<div class="wpcursor-hero-entete-cocon__pre-form-divider" aria-hidden="true"></div>

					<div class="wpcursor-hero-entete-cocon__aside">
						<div class="wpcursor-hero-entete-cocon__card-stack">
							<div class="wpcursor-hero-entete-cocon__card-layer wpcursor-hero-entete-cocon__card-layer--3" aria-hidden="true"></div>
							<div class="wpcursor-hero-entete-cocon__card-layer wpcursor-hero-entete-cocon__card-layer--2" aria-hidden="true"></div>
							<div class="wpcursor-hero-entete-cocon__card">
								<?php if ($form_title !== '') : ?>
									<h2 class="wpcursor-hero-entete-cocon__form-title"><?php echo esc_html($form_title); ?></h2>
								<?php endif; ?>

								<?php if ($form_embed_markup !== '') : ?>
									<div class="wpcursor-hero-entete-cocon__form-embed">
										<?php echo $form_embed_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output ?>
									</div>
								<?php else : ?>
									<form
										class="wpcursor-hero-entete-cocon__form"
										<?php echo $form_action_url !== '' ? 'action="' . esc_url($form_action_url) . '"' : ''; ?>
										method="get"
									>
										<div class="wpcursor-hero-entete-cocon__field">
											<label class="wpcursor-hero-entete-cocon__field-label" for="<?php echo esc_attr($instance_id . '-niveau'); ?>">
												<?php echo esc_html($select1_label); ?>
											</label>
											<div class="wpcursor-hero-entete-cocon__select-wrap">
												<select id="<?php echo esc_attr($instance_id . '-niveau'); ?>" name="niveau" class="wpcursor-hero-entete-cocon__select">
													<option value=""><?php echo esc_html($select1_ph); ?></option>
													<option value="seconde">Seconde</option>
													<option value="premiere">Première</option>
													<option value="terminale">Terminale</option>
													<option value="post-bac">Post-bac</option>
												</select>
											</div>
										</div>
										<div class="wpcursor-hero-entete-cocon__field">
											<label class="wpcursor-hero-entete-cocon__field-label" for="<?php echo esc_attr($instance_id . '-objectif'); ?>">
												<?php echo esc_html($select2_label); ?>
											</label>
											<div class="wpcursor-hero-entete-cocon__select-wrap">
												<select id="<?php echo esc_attr($instance_id . '-objectif'); ?>" name="objectif" class="wpcursor-hero-entete-cocon__select">
													<option value=""><?php echo esc_html($select2_ph); ?></option>
													<option value="sciences-po-paris">Sciences Po Paris</option>
													<option value="iep">Concours commun IEP</option>
													<option value="les-deux">ScPo Paris & IEP</option>
												</select>
											</div>
										</div>
									</form>
								<?php endif; ?>
							</div>
						</div>

						<?php if ($phone !== '') : ?>
							<div class="wpcursor-hero-entete-cocon__phone-block">
								<a class="wpcursor-hero-entete-cocon__phone-link" href="<?php echo esc_url($phone_url); ?>">
									<span class="wpcursor-hero-entete-cocon__phone-icon" aria-hidden="true">
										<svg viewBox="0 0 24 24" width="20" height="20" focusable="false" aria-hidden="true">
											<path fill="currentColor" d="M6.6 10.8c1.5 2.9 3.7 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1v3.5c0 .6-.4 1-1 1C10.1 21 3 13.9 3 5c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/>
										</svg>
									</span>
									<span class="wpcursor-hero-entete-cocon__phone-number"><?php echo esc_html($phone); ?></span>
								</a>
								<?php if ($phone_caption !== '') : ?>
									<p class="wpcursor-hero-entete-cocon__phone-caption"><?php echo esc_html($phone_caption); ?></p>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>

					<?php if ($stats !== []) : ?>
						<div class="wpcursor-hero-entete-cocon__stats-slot">
							<div class="wpcursor-hero-entete-cocon__divider" aria-hidden="true"></div>
							<div class="wpcursor-hero-entete-cocon__stats">
								<?php foreach ($stats as $stat) : ?>
									<?php $render_stat($stat); ?>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-hero-entete-cocon__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
