<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Une question sur la prépa — contact email / téléphone + Gravity Forms.
 *
 * [wpcursor name="question-prepa"]
 * [wpcursor name="question-prepa" form_id="42" form_id_phone="43"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-question-prepa-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$render_gf_form = static function (int $form_id, string $form_shortcode): string {
	if ($form_shortcode !== '') {
		return do_shortcode($form_shortcode);
	}
	if ($form_id > 0) {
		return do_shortcode(
			sprintf(
				'[gravityform id="%d" title="false" description="false" ajax="true"]',
				$form_id
			)
		);
	}
	return '';
};

$section_title      = $prop_str($props, 'section_title', 'Une question sur notre préparation Sciences Po ? Ecrivez-nous !');
$show_toggle        = ! isset($props['show_toggle']) || (string) $props['show_toggle'] !== 'no';
$toggle_label_email = $prop_str($props, 'toggle_label_email', 'Par Email');
$toggle_label_phone = $prop_str($props, 'toggle_label_phone', 'Par téléphone');
$default_contact    = isset($props['default_contact']) && (string) $props['default_contact'] === 'phone' ? 'phone' : 'email';
$card_title         = $prop_str($props, 'card_title', 'Formulaire de demande de rappel');
$form_id            = isset($props['form_id']) ? absint($props['form_id']) : 0;
$form_id_phone      = isset($props['form_id_phone']) ? absint($props['form_id_phone']) : 0;
$form_shortcode     = $prop_str($props, 'form_shortcode');
$form_shortcode_phone = $prop_str($props, 'form_shortcode_phone');
$legal_text         = $prop_str(
	$props,
	'legal_text',
	'En soumettant ce formulaire, vous acceptez que Cours Thalès utilise vos données pour vous recontacter dans le cadre de votre projet de prépa. Vous pouvez vous désinscrire à tout moment.'
);
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$form_email_markup = $render_gf_form($form_id, $form_shortcode);
$form_phone_markup = $render_gf_form($form_id_phone, $form_shortcode_phone);

$benefit_defaults = [
	'Échange gratuit et sans engagement',
	'Réponse personnalisée à tes questions',
	'Conseils concrets pour ton profil',
	'Aucun démarchage commercial, juste des infos claires',
	'Confidentialité garantie de tes coordonnées',
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

$has_any_form = $form_email_markup !== '' || $form_phone_markup !== '';
$is_preview   = ($runtime === 'admin_preview');
$active_contact = $default_contact;
if (! $show_toggle) {
	$active_contact = 'email';
}

$section_classes = ['wpcursor-component', 'wpcursor-question-prepa'];
if ($show_toggle) {
	$section_classes[] = 'wpcursor-question-prepa--tabs';
}
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="<?php echo esc_attr(implode(' ', $section_classes)); ?>"
	data-wpcursor="question-prepa"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
	data-default-contact="<?php echo esc_attr($active_contact); ?>"
>
	<div class="wpcursor-question-prepa__inner">
		<?php if ($section_title !== '') : ?>
			<h2 class="wpcursor-question-prepa__heading"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>

		<?php if ($show_toggle) : ?>
			<div class="wpcursor-question-prepa__toggle" role="tablist" aria-label="<?php esc_attr_e('Mode de contact', 'wpcursor'); ?>">
				<button
					type="button"
					class="wpcursor-question-prepa__toggle-btn wpcursor-question-prepa__toggle-btn--email<?php echo $active_contact === 'email' ? ' is-active' : ''; ?>"
					role="tab"
					id="<?php echo esc_attr($instance_id . '-tab-email'); ?>"
					aria-selected="<?php echo $active_contact === 'email' ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr($instance_id . '-panel-email'); ?>"
					data-contact="email"
				>
					<?php echo esc_html($toggle_label_email); ?>
				</button>
				<button
					type="button"
					class="wpcursor-question-prepa__toggle-btn wpcursor-question-prepa__toggle-btn--phone<?php echo $active_contact === 'phone' ? ' is-active' : ''; ?>"
					role="tab"
					id="<?php echo esc_attr($instance_id . '-tab-phone'); ?>"
					aria-selected="<?php echo $active_contact === 'phone' ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr($instance_id . '-panel-phone'); ?>"
					data-contact="phone"
				>
					<?php echo esc_html($toggle_label_phone); ?>
				</button>
			</div>
		<?php endif; ?>

		<div class="wpcursor-question-prepa__card">
			<div class="wpcursor-question-prepa__card-accent" aria-hidden="true">
				<span class="wpcursor-question-prepa__card-accent-left"></span>
				<span class="wpcursor-question-prepa__card-accent-right"></span>
			</div>

			<?php if ($card_title !== '') : ?>
				<h3 class="wpcursor-question-prepa__card-title"><?php echo esc_html($card_title); ?></h3>
			<?php endif; ?>

			<?php if ($benefits !== []) : ?>
				<div class="wpcursor-question-prepa__benefits">
					<ul class="wpcursor-question-prepa__benefits-row wpcursor-question-prepa__benefits-row--top unstyled">
						<?php foreach (array_slice($benefits, 0, 3) as $benefit) : ?>
							<li class="wpcursor-question-prepa__benefit">
								<span class="wpcursor-question-prepa__benefit-icon" aria-hidden="true"></span>
								<span><?php echo esc_html($benefit); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php if (count($benefits) > 3) : ?>
						<ul class="wpcursor-question-prepa__benefits-row wpcursor-question-prepa__benefits-row--bottom unstyled">
							<?php foreach (array_slice($benefits, 3) as $benefit) : ?>
								<li class="wpcursor-question-prepa__benefit">
									<span class="wpcursor-question-prepa__benefit-icon" aria-hidden="true"></span>
									<span><?php echo esc_html($benefit); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="wpcursor-question-prepa__panels">
				<div
					class="wpcursor-question-prepa__panel wpcursor-question-prepa__panel--email"
					id="<?php echo esc_attr($instance_id . '-panel-email'); ?>"
					role="tabpanel"
					aria-labelledby="<?php echo esc_attr($instance_id . '-tab-email'); ?>"
					<?php echo ( $show_toggle && $active_contact !== 'email' ) ? ' hidden' : ''; ?>
				>
					<?php if ($form_email_markup !== '') : ?>
						<div class="wpcursor-question-prepa__form-embed wpcursor-question-prepa__form-embed--email">
							<?php echo $form_email_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output ?>
						</div>
					<?php else : ?>
						<form class="wpcursor-question-prepa__form-demo" aria-hidden="true">
							<div class="wpcursor-question-prepa__form-row">
								<div class="wpcursor-question-prepa__field">
									<input type="text" class="wpcursor-question-prepa__input" placeholder="Prénom" disabled />
								</div>
								<div class="wpcursor-question-prepa__field">
									<input type="text" class="wpcursor-question-prepa__input" placeholder="Nom" disabled />
								</div>
							</div>
							<div class="wpcursor-question-prepa__form-row">
								<div class="wpcursor-question-prepa__field">
									<input type="email" class="wpcursor-question-prepa__input" placeholder="Email" disabled />
								</div>
								<div class="wpcursor-question-prepa__field">
									<input type="tel" class="wpcursor-question-prepa__input" placeholder="Téléphone" disabled />
								</div>
							</div>
							<div class="wpcursor-question-prepa__field">
								<select class="wpcursor-question-prepa__input wpcursor-question-prepa__select" disabled>
									<option>Vous êtes</option>
								</select>
							</div>
							<span class="wpcursor-question-prepa__cta-demo">Suivant</span>
						</form>
						<?php if (! $has_any_form && $is_preview) : ?>
							<p class="wpcursor-question-prepa__form-hint">
								<?php esc_html_e('Renseignez form_id (email) et/ou form_id_phone dans le shortcode pour afficher Gravity Forms.', 'wpcursor'); ?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<?php if ($show_toggle) : ?>
					<div
						class="wpcursor-question-prepa__panel wpcursor-question-prepa__panel--phone"
						id="<?php echo esc_attr($instance_id . '-panel-phone'); ?>"
						role="tabpanel"
						aria-labelledby="<?php echo esc_attr($instance_id . '-tab-phone'); ?>"
						<?php echo $active_contact !== 'phone' ? ' hidden' : ''; ?>
					>
						<?php if ($form_phone_markup !== '') : ?>
							<div class="wpcursor-question-prepa__form-embed wpcursor-question-prepa__form-embed--phone">
								<?php echo $form_phone_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output ?>
							</div>
						<?php else : ?>
							<form class="wpcursor-question-prepa__form-demo" aria-hidden="true">
								<div class="wpcursor-question-prepa__form-row">
									<div class="wpcursor-question-prepa__field">
										<input type="text" class="wpcursor-question-prepa__input" placeholder="Prénom" disabled />
									</div>
									<div class="wpcursor-question-prepa__field">
										<input type="text" class="wpcursor-question-prepa__input" placeholder="Nom" disabled />
									</div>
								</div>
								<div class="wpcursor-question-prepa__form-row">
									<div class="wpcursor-question-prepa__field">
										<input type="email" class="wpcursor-question-prepa__input" placeholder="Email" disabled />
									</div>
									<div class="wpcursor-question-prepa__field">
										<input type="tel" class="wpcursor-question-prepa__input" placeholder="Téléphone" disabled />
									</div>
								</div>
								<div class="wpcursor-question-prepa__field">
									<select class="wpcursor-question-prepa__input wpcursor-question-prepa__select" disabled>
										<option>Vous êtes</option>
									</select>
								</div>
								<span class="wpcursor-question-prepa__cta-demo">Suivant</span>
							</form>
							<?php if (! $has_any_form && $is_preview) : ?>
								<p class="wpcursor-question-prepa__form-hint">
									<?php esc_html_e('Renseignez form_id_phone dans le shortcode pour le formulaire téléphone.', 'wpcursor'); ?>
								</p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ($legal_text !== '' && $has_any_form) : ?>
				<p class="wpcursor-question-prepa__legal"><?php echo esc_html($legal_text); ?></p>
			<?php elseif ($legal_text !== '' && ! $has_any_form) : ?>
				<p class="wpcursor-question-prepa__legal wpcursor-question-prepa__legal--demo"><?php echo esc_html($legal_text); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-question-prepa__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
