<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Guide admission — lead magnet avec formulaire.
 *
 * [wpcursor name="guide-admission"]
 * [wpcursor name="guide-admission" form_id="42" guide_image_url="https://…"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-guide-admission-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$section_title = $prop_str(
	$props,
	'section_title',
	'Pour recevoir votre petit guide d\'admission à Sciences Po, remplissez le formulaire suivant'
);
$guide_image_url = isset($props['guide_image_url']) ? esc_url((string) $props['guide_image_url']) : '';
$guide_image_alt = $prop_str($props, 'guide_image_alt', 'Le petit guide Sciences Po — la ressource complète');
$form_id         = isset($props['form_id']) ? absint($props['form_id']) : 0;
$form_shortcode  = $prop_str($props, 'form_shortcode');
$legal_text      = $prop_str(
	$props,
	'legal_text',
	'En soumettant ce formulaire, vous acceptez que Cours Thalès utilise vos données pour vous envoyer le guide et vous contacter dans le cadre de votre projet de prépa. Vous pouvez vous désinscrire à tout moment.'
);
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

$benefit_defaults = [
	'Présentation de notre Prépa',
	'Présentation des IEP, des concours, des campus',
	'Des fiches méthodos',
	'Des fiches méthodos',
	'Checklist de préparation et plein d\'autres bonus !',
];

$benefits = [];
foreach ($props as $key => $value) {
	if (is_string($key) && preg_match('/^benefit([0-9]{1,3})$/', $key, $m)) {
		$txt = trim((string) $value);
		if ($txt !== '') {
			$benefits[ (int) $m[1] ] = $txt;
		}
	}
}
if ($benefits === []) {
	foreach ($benefit_defaults as $i => $text) {
		$key = 'benefit' . ( $i + 1 );
		$benefits[ $i + 1 ] = isset($props[ $key ]) && trim((string) $props[ $key ]) !== ''
			? trim((string) $props[ $key ])
			: $text;
	}
} else {
	ksort($benefits, SORT_NUMERIC);
	$benefits = array_values($benefits);
}
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-guide-admission"
	data-wpcursor="guide-admission"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-guide-admission__inner">
		<?php if ($section_title !== '') : ?>
			<h2 class="wpcursor-guide-admission__heading"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>

		<?php if ($benefits !== []) : ?>
			<ul class="wpcursor-guide-admission__benefits">
				<?php foreach ($benefits as $benefit) : ?>
					<li class="wpcursor-guide-admission__benefit">
						<span class="wpcursor-guide-admission__benefit-icon" aria-hidden="true"></span>
						<span><?php echo esc_html($benefit); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="wpcursor-guide-admission__grid">
			<div class="wpcursor-guide-admission__visual">
				<div class="wpcursor-guide-admission__visual-frame">
					<?php if ($guide_image_url !== '') : ?>
						<img
							class="wpcursor-guide-admission__guide-image"
							src="<?php echo esc_url($guide_image_url); ?>"
							alt="<?php echo esc_attr($guide_image_alt); ?>"
							loading="lazy"
							decoding="async"
						/>
					<?php else : ?>
						<div class="wpcursor-guide-admission__guide-placeholder" aria-hidden="true">
							<p class="wpcursor-guide-admission__guide-placeholder-title">LE PETIT GUIDE SCIENCES PO</p>
							<p class="wpcursor-guide-admission__guide-placeholder-sub">LA RESSOURCE COMPLÈTE</p>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="wpcursor-guide-admission__form-col">
				<?php if ($form_embed_markup !== '') : ?>
					<div class="wpcursor-guide-admission__form-embed">
						<?php echo $form_embed_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output ?>
					</div>
				<?php else : ?>
					<form class="wpcursor-guide-admission__form-demo" aria-hidden="true">
						<div class="wpcursor-guide-admission__form-row">
							<div class="wpcursor-guide-admission__field">
								<input type="text" class="wpcursor-guide-admission__input" placeholder="Prénom" disabled />
							</div>
							<div class="wpcursor-guide-admission__field">
								<input type="text" class="wpcursor-guide-admission__input" placeholder="Nom" disabled />
							</div>
						</div>
						<div class="wpcursor-guide-admission__field">
							<input type="email" class="wpcursor-guide-admission__input" placeholder="Email" disabled />
						</div>
						<div class="wpcursor-guide-admission__field">
							<input type="text" class="wpcursor-guide-admission__input" placeholder="Classe" disabled />
						</div>
						<span class="wpcursor-guide-admission__cta-demo">Télécharger le guide</span>
					</form>
					<p class="wpcursor-guide-admission__form-hint">
						<?php esc_html_e('Renseignez form_id dans le shortcode pour afficher le formulaire Gravity Forms.', 'wpcursor'); ?>
					</p>
				<?php endif; ?>

				<?php if ($legal_text !== '' && $form_embed_markup !== '') : ?>
					<p class="wpcursor-guide-admission__legal"><?php echo esc_html($legal_text); ?></p>
				<?php elseif ($legal_text !== '' && $form_embed_markup === '') : ?>
					<p class="wpcursor-guide-admission__legal wpcursor-guide-admission__legal--demo"><?php echo esc_html($legal_text); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-guide-admission__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
