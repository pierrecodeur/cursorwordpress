<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Pourquoi suivre une prépa — 3 arguments + CTA.
 *
 * [wpcursor name="pourquoi-prepa"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-pourquoi-prepa-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$section_title = $prop_str(
	$props,
	'section_title',
	'Pourquoi suivre une prépa Sciences Po dès le Lycée ?'
);
$cta_text = $prop_str($props, 'cta_text', 'S\'inscrire à la prépa');
$cta_url  = isset($props['cta_url']) ? esc_url((string) $props['cta_url']) : '';
$footer_link_text = $prop_str($props, 'footer_link_text', 'En savoir plus');
$footer_link_url  = isset($props['footer_link_url']) ? esc_url((string) $props['footer_link_url']) : '';
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$card_defaults = [
	[
		'eyebrow' => 'MOINS DE 10%',
		'title'   => 'Une sélectivité extrême',
		'body'    => 'Chaque Sciences Po voit son taux de sélection varier entre 8 % et 11 % selon les procédures d\'admission. Cela signifie que seuls les dossiers les plus solides et les plus préparés ont une chance de se démarquer.',
	],
	[
		'eyebrow' => 'MÉTHODE & RIGUEUR',
		'title'   => 'Des épreuves exigeantes',
		'body'    => 'Les procédures d\'admission incluent des dissertations sur des questions contemporaines, des analyses de documents et un Grand Oral. Ces épreuves demandent une méthode de travail rigoureuse, une solide culture générale et une capacité à articuler sa réflexion avec clarté et cohérence.',
	],
	[
		'eyebrow' => 'NIVEAU DE SÉLECTIVITÉ',
		'title'   => 'Une compétition nationale',
		'body'    => 'Près de 30 000 candidats présentent chaque année un dossier pour les différents IEP, pour un nombre de places resté stable autour de 2 500. Les candidats les plus motivés, informés et préparés sont ceux qui font les meilleurs choix stratégiques et anticipent efficacement les différentes procédures.',
	],
];

$cards = [];
for ($i = 1; $i <= 3; $i++) {
	$defaults = $card_defaults[ $i - 1 ];
	$cards[]  = [
		'number'  => (string) $i,
		'eyebrow' => $prop_str($props, 'card' . $i . '_eyebrow', $defaults['eyebrow']),
		'title'   => $prop_str($props, 'card' . $i . '_title', $defaults['title']),
		'body'    => $prop_str($props, 'card' . $i . '_body', $defaults['body']),
	];
}
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-pourquoi-prepa"
	data-wpcursor="pourquoi-prepa"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-pourquoi-prepa__inner">
		<header class="wpcursor-pourquoi-prepa__header">
			<?php if ($section_title !== '') : ?>
				<h2 class="wpcursor-pourquoi-prepa__heading"><?php echo esc_html($section_title); ?></h2>
			<?php endif; ?>

			<?php if ($cta_text !== '' && $cta_url !== '') : ?>
				<a class="wpcursor-pourquoi-prepa__cta-top" href="<?php echo esc_url($cta_url); ?>">
					<?php echo esc_html($cta_text); ?>
				</a>
			<?php elseif ($cta_text !== '') : ?>
				<span class="wpcursor-pourquoi-prepa__cta-top wpcursor-pourquoi-prepa__cta-top--static">
					<?php echo esc_html($cta_text); ?>
				</span>
			<?php endif; ?>
		</header>

		<div class="wpcursor-pourquoi-prepa__grid">
			<?php foreach ($cards as $card) : ?>
				<article class="wpcursor-pourquoi-prepa__card">
					<div class="wpcursor-pourquoi-prepa__card-num" aria-hidden="true">
						<?php echo esc_html($card['number']); ?>
					</div>
					<?php if ($card['eyebrow'] !== '') : ?>
						<p class="wpcursor-pourquoi-prepa__card-eyebrow"><?php echo esc_html($card['eyebrow']); ?></p>
					<?php endif; ?>
					<?php if ($card['title'] !== '') : ?>
						<h3 class="wpcursor-pourquoi-prepa__card-title"><?php echo esc_html($card['title']); ?></h3>
					<?php endif; ?>
					<?php if ($card['body'] !== '') : ?>
						<p class="wpcursor-pourquoi-prepa__card-body"><?php echo esc_html($card['body']); ?></p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ($footer_link_text !== '' && $footer_link_url !== '') : ?>
			<footer class="wpcursor-pourquoi-prepa__footer">
				<a class="wpcursor-pourquoi-prepa__footer-link" href="<?php echo esc_url($footer_link_url); ?>">
					<span><?php echo esc_html($footer_link_text); ?></span>
					<span class="wpcursor-pourquoi-prepa__footer-icon" aria-hidden="true">
						<svg viewBox="0 0 16 16" focusable="false" aria-hidden="true">
							<path d="M5.5 2.5H2.5V5.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M10.5 2.5h3v3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M2.5 10.5v3h3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M13.5 10.5v3h-3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</span>
				</a>
			</footer>
		<?php elseif ($footer_link_text !== '') : ?>
			<footer class="wpcursor-pourquoi-prepa__footer">
				<span class="wpcursor-pourquoi-prepa__footer-link wpcursor-pourquoi-prepa__footer-link--static">
					<span><?php echo esc_html($footer_link_text); ?></span>
					<span class="wpcursor-pourquoi-prepa__footer-icon" aria-hidden="true">
						<svg viewBox="0 0 16 16" focusable="false" aria-hidden="true">
							<path d="M5.5 2.5H2.5V5.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M10.5 2.5h3v3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M2.5 10.5v3h3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M13.5 10.5v3h-3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</span>
				</span>
			</footer>
		<?php endif; ?>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-pourquoi-prepa__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
