<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Hero accueil — page d’accueil (badge, titres, stats, avis, image, CTA RDV).
 *
 * [wpcursor name="hero-accueil"]
 * [wpcursor name="hero-accueil" image_url="https://…" cta_url="/contact/"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-hero-accueil-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$heading_tag = isset($props['heading_tag']) ? strtolower((string) $props['heading_tag']) : 'h1';
if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'p'], true)) {
	$heading_tag = 'h1';
}

$subheading_tag = isset($props['subheading_tag']) ? strtolower((string) $props['subheading_tag']) : 'h2';
if (! in_array($subheading_tag, ['h1', 'h2', 'h3', 'h4', 'p'], true)) {
	$subheading_tag = 'h2';
}

$badge              = $prop_str($props, 'badge', 'Cours Thalès, préparation au BAC et aux concours dès le lycée');
$title              = $prop_str($props, 'title', 'Cours Thalès');
$subtitle           = $prop_str($props, 'subtitle', 'Transformons vos efforts en réussite');
$intro              = $prop_str($props, 'intro', 'Depuis 17 ans Cours Thalès prépare aux cursus les plus sélectifs dès le lycée : Médecine, Sciences Po, CPGE, Concours Ingénieur ou commerce post-bac.');
$title_font_size    = $prop_str($props, 'title_font_size');
$subtitle_font_size = $prop_str($props, 'subtitle_font_size');
$intro_font_size    = $prop_str($props, 'intro_font_size');
$show_reviews       = ! isset($props['show_reviews']) || (string) $props['show_reviews'] !== 'no';
$reviews_rating     = $prop_str($props, 'reviews_rating', '4.8');
$reviews_count      = $prop_str($props, 'reviews_count', '565 avis');
$reviews_url        = isset($props['reviews_url']) ? esc_url((string) $props['reviews_url']) : '';
$image_url          = isset($props['image_url']) ? esc_url((string) $props['image_url']) : '';
$image_alt          = $prop_str($props, 'image_alt', 'Élèves en cours chez Cours Thalès');
$cta_text           = $prop_str($props, 'cta_text', 'Prendre RDV avec un conseiller');
$cta_url            = isset($props['cta_url']) ? esc_url((string) $props['cta_url']) : '';
$show_bottom_chevron = ! isset($props['show_bottom_chevron']) || (string) $props['show_bottom_chevron'] !== 'no';
$custom_html        = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css         = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$stat_defaults = [
	['value' => '68%', 'label' => 'D\'admis à Science Po Paris'],
	['value' => '74%', 'label' => 'D\'admis à Science Po Paris'],
	['value' => '68%', 'label' => 'D\'admis à Science Po Paris'],
	['value' => '74%', 'label' => 'D\'admis à Science Po Paris'],
];

$stats = [];
for ($i = 1; $i <= 4; $i++) {
	$value = $prop_str($props, 'stat' . $i . '_value', $stat_defaults[ $i - 1 ]['value']);
	$label = $prop_str($props, 'stat' . $i . '_label', $stat_defaults[ $i - 1 ]['label']);
	if ($value === '' && $label === '') {
		continue;
	}
	$stats[] = ['value' => $value, 'label' => $label];
}

$section_style = '';
$style_vars    = [];
if ($title_font_size !== '') {
	$style_vars[] = '--wpcursor-hero-accueil-title-size:' . $title_font_size;
}
if ($subtitle_font_size !== '') {
	$style_vars[] = '--wpcursor-hero-accueil-subtitle-size:' . $subtitle_font_size;
}
if ($intro_font_size !== '') {
	$style_vars[] = '--wpcursor-hero-accueil-intro-size:' . $intro_font_size;
}
if ($style_vars !== []) {
	$section_style = ' style="' . esc_attr(implode(';', $style_vars)) . '"';
}

$render_stat = static function (array $stat): void {
	?>
	<div class="wpcursor-hero-accueil__stat">
		<div class="wpcursor-hero-accueil__stat-badge" aria-hidden="true">
			<svg class="wpcursor-hero-accueil__stat-wreath" viewBox="0 0 80 80" focusable="false" aria-hidden="true">
				<ellipse cx="40" cy="40" rx="34" ry="34" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.45"/>
				<path d="M40 8c-4 6-10 8-14 6 2 8 0 14-4 18 8-2 14 0 18 4-6-4-8-10-6-14 6 4 12 2 16-2-8 2-14-2-18-6 4 6 10 8 14 6-2-8 0-14 4-18-8 2-14 0-18-4 6 4 8 10 6 14-6-4-12-2-16 2 8-2 14 2 18 6-4-6-10-8-14-6z" fill="none" stroke="currentColor" stroke-width="1.2" opacity="0.65"/>
			</svg>
			<span class="wpcursor-hero-accueil__stat-value"><?php echo esc_html($stat['value']); ?></span>
		</div>
		<p class="wpcursor-hero-accueil__stat-label"><?php echo esc_html($stat['label']); ?></p>
	</div>
	<?php
};

$section_classes = ['wpcursor-component', 'wpcursor-hero-accueil'];
if ($show_bottom_chevron) {
	$section_classes[] = 'wpcursor-hero-accueil--chevron';
}
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="<?php echo esc_attr(implode(' ', $section_classes)); ?>"
	data-wpcursor="hero-accueil"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
	<?php echo $section_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr sur les variables. ?>
>
	<div class="wpcursor-hero-accueil__shell">
		<div class="wpcursor-hero-accueil__inner">
			<div class="wpcursor-hero-accueil__grid">
				<div class="wpcursor-hero-accueil__content">
					<?php if ($badge !== '') : ?>
						<p class="wpcursor-hero-accueil__badge"><?php echo esc_html($badge); ?></p>
					<?php endif; ?>

					<?php if ($title !== '') : ?>
						<<?php echo esc_html($heading_tag); ?> class="wpcursor-hero-accueil__title"><?php echo esc_html($title); ?></<?php echo esc_html($heading_tag); ?>>
					<?php endif; ?>

					<?php if ($subtitle !== '') : ?>
						<<?php echo esc_html($subheading_tag); ?> class="wpcursor-hero-accueil__subtitle"><?php echo esc_html($subtitle); ?></<?php echo esc_html($subheading_tag); ?>>
					<?php endif; ?>

					<?php if ($intro !== '') : ?>
						<p class="wpcursor-hero-accueil__intro"><?php echo esc_html($intro); ?></p>
					<?php endif; ?>

					<?php if ($stats !== []) : ?>
						<div class="wpcursor-hero-accueil__stats">
							<?php foreach ($stats as $stat) : ?>
								<?php $render_stat($stat); ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ($show_reviews) : ?>
						<?php
						$reviews_tag   = $reviews_url !== '' ? 'a' : 'div';
						$reviews_attrs = $reviews_url !== '' ? ' href="' . esc_url($reviews_url) . '" rel="noopener noreferrer" target="_blank"' : '';
						?>
						<<?php echo esc_html($reviews_tag); ?> class="wpcursor-hero-accueil__reviews"<?php echo $reviews_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<span class="wpcursor-hero-accueil__reviews-google" aria-hidden="true">
								<svg viewBox="0 0 24 24" width="22" height="22" focusable="false" aria-hidden="true">
									<path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.56c2.08-1.92 3.28-4.74 3.28-8.1z"/>
									<path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.56-2.77c-.98.66-2.23 1.06-3.72 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
									<path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
									<path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
								</svg>
							</span>
							<span class="wpcursor-hero-accueil__reviews-rating"><?php echo esc_html($reviews_rating); ?></span>
							<span class="wpcursor-hero-accueil__reviews-stars" aria-hidden="true">★★★★★</span>
							<span class="wpcursor-hero-accueil__reviews-count"><?php echo esc_html($reviews_count); ?></span>
						</<?php echo esc_html($reviews_tag); ?>>
					<?php endif; ?>
				</div>

				<div class="wpcursor-hero-accueil__aside">
					<div class="wpcursor-hero-accueil__media-stack">
						<?php if ($image_url !== '') : ?>
							<img
								class="wpcursor-hero-accueil__photo"
								src="<?php echo esc_url($image_url); ?>"
								alt="<?php echo esc_attr($image_alt); ?>"
								loading="eager"
								decoding="async"
								sizes="(max-width: 960px) 100vw, 480px"
							/>
						<?php else : ?>
							<div class="wpcursor-hero-accueil__photo wpcursor-hero-accueil__photo--placeholder" aria-hidden="true"></div>
						<?php endif; ?>
						<div class="wpcursor-hero-accueil__media-layer wpcursor-hero-accueil__media-layer--1" aria-hidden="true"></div>
						<div class="wpcursor-hero-accueil__media-layer wpcursor-hero-accueil__media-layer--2" aria-hidden="true"></div>
						<div class="wpcursor-hero-accueil__media-layer wpcursor-hero-accueil__media-layer--3" aria-hidden="true"></div>
					</div>

					<?php if ($cta_text !== '') : ?>
						<?php if ($cta_url !== '') : ?>
							<a class="wpcursor-hero-accueil__cta" href="<?php echo esc_url($cta_url); ?>">
								<span class="wpcursor-hero-accueil__cta-icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" width="22" height="22" focusable="false" aria-hidden="true">
										<path fill="currentColor" d="M6.6 10.8c1.5 2.9 3.7 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1v3.5c0 .6-.4 1-1 1C10.1 21 3 13.9 3 5c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/>
									</svg>
								</span>
								<span><?php echo esc_html($cta_text); ?></span>
							</a>
						<?php else : ?>
							<span class="wpcursor-hero-accueil__cta wpcursor-hero-accueil__cta--static">
								<span class="wpcursor-hero-accueil__cta-icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" width="22" height="22" focusable="false" aria-hidden="true">
										<path fill="currentColor" d="M6.6 10.8c1.5 2.9 3.7 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1v3.5c0 .6-.4 1-1 1C10.1 21 3 13.9 3 5c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/>
									</svg>
								</span>
								<span><?php echo esc_html($cta_text); ?></span>
							</span>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-hero-accueil__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
