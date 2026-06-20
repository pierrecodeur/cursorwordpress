<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Découvrir nos prépas — section à onglets (tarif + détail).
 *
 * [wpcursor name="decouvrir-prepa"]
 * Onglets 2–4 : tab2_label, tab2_price_amount, tab2_feature1, etc.
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-decouvrir-prepa-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$section_title = $prop_str($props, 'section_title', 'Découvrez nos prépas Sciences Po :');
$default_tab   = isset($props['default_tab']) ? (string) $props['default_tab'] : '1';
if (! in_array($default_tab, ['1', '2', '3', '4'], true)) {
	$default_tab = '1';
}
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$tab1_defaults = [
	'label'             => 'Prépa Sciences po (première)',
	'price_prefix'      => 'À PARTIR',
	'price_amount'      => '1990€',
	'price_subtitle'    => 'Prepa Sciences Po (Première)',
	'feature1'          => 'Formule week-end + accompagnement Parcoursup',
	'feature2'          => 'Préparation intensive au Bac de français incluse',
	'feature3'          => 'Office hours et coaching individualisé disponible',
	'feature4'          => 'Classes à effectif réduit (20 élèves maximum)',
	'feature5'          => 'E-learning 24h/24 et 7j/7',
	'card_link_text'    => 'En savoir plus',
	'card_link_url'     => '',
	'badge1'            => 'Plus de 17 ans d\'expérience',
	'badge2'            => 'La référence pour Sciences po',
	'intro_lead'        => 'La prépa Sciences Po Première de Cours Thalès accompagne les élèves de première dans la construction d\'un dossier Parcoursup solide.',
	'intro_body'        => 'Un programme structuré pour découvrir Sciences Po, renforcer le niveau scolaire et aborder sereinement les épreuves à venir.',
	'highlight_text'    => 'Une formule pensée pour les lycéens de première : cours le week-end, suivi Parcoursup et préparation au bac de français intégrée.',
	'cta_text'          => 'S\'inscrire à la prépa',
	'cta_url'           => '',
	'detail_link_text'  => 'En savoir plus',
	'detail_link_url'   => '',
];

/**
 * @param array<string, string> $props
 * @param array<string, string> $defaults
 *
 * @return array<string, mixed>|null
 */
$build_tab = static function (array $props, string $prefix, array $defaults, string $fallback_label = ''): ?array {
	$get = static function (string $key) use ($props, $prefix, $defaults): string {
		$prop_key = $prefix . $key;
		if (isset($props[ $prop_key ]) && trim((string) $props[ $prop_key ]) !== '') {
			return trim((string) $props[ $prop_key ]);
		}
		return isset($defaults[ $key ]) ? (string) $defaults[ $key ] : '';
	};

	$label = $get('label');
	if ($label === '' && $fallback_label !== '') {
		$label = $fallback_label;
	}
	if ($label === '') {
		return null;
	}

	$features = [];
	foreach (['feature1', 'feature2', 'feature3', 'feature4', 'feature5'] as $fk) {
		$text = $get($fk);
		if ($text !== '') {
			$features[] = $text;
		}
	}

	return [
		'label'            => $label,
		'price_prefix'     => $get('price_prefix'),
		'price_amount'     => $get('price_amount'),
		'price_subtitle'   => $get('price_subtitle'),
		'features'         => $features,
		'card_link_text'   => $get('card_link_text'),
		'card_link_url'    => $get('card_link_url'),
		'badge1'           => $get('badge1'),
		'badge2'           => $get('badge2'),
		'intro_lead'       => $get('intro_lead'),
		'intro_body'       => $get('intro_body'),
		'highlight_text'   => $get('highlight_text'),
		'cta_text'         => $get('cta_text'),
		'cta_url'          => $get('cta_url'),
		'detail_link_text' => $get('detail_link_text'),
		'detail_link_url'  => $get('detail_link_url'),
	];
};

$tabs = [];
$tab1 = $build_tab(
	$props,
	'tab1_',
	array_merge($tab1_defaults, ['label' => '']),
	$prop_str($props, 'tab1_label', $tab1_defaults['label'])
);
if ($tab1 !== null) {
	$tabs[] = $tab1;
}

foreach (['tab2_', 'tab3_', 'tab4_'] as $prefix) {
	$num = (int) substr($prefix, 3, 1);
	$label_key = 'tab' . $num . '_label';
	$fallback  = $prop_str($props, $label_key);
	$tab       = $build_tab($props, $prefix, array_fill_keys(array_keys($tab1_defaults), ''), $fallback);
	if ($tab !== null) {
		$tabs[] = $tab;
	}
}

if ($tabs === []) {
	$tabs[] = $tab1_defaults;
}

$active_index = max(0, min(count($tabs) - 1, (int) $default_tab - 1));
$tabs_id      = $instance_id . '-tabs';
$has_tabs     = count($tabs) > 1;

/**
 * @param array<string, mixed> $tab
 */
$render_panel = static function (array $tab, int $index, bool $is_active) use ($instance_id): void {
	$panel_id = $instance_id . '-panel-' . $index;
	?>
	<div
		id="<?php echo esc_attr($panel_id); ?>"
		class="wpcursor-decouvrir-prepa__panel"
		role="tabpanel"
		tabindex="0"
		aria-labelledby="<?php echo esc_attr($instance_id . '-tab-' . $index); ?>"
		<?php echo $is_active ? '' : ' hidden'; ?>
	>
		<div class="wpcursor-decouvrir-prepa__panel-grid">
			<div class="wpcursor-decouvrir-prepa__pricing">
				<div class="wpcursor-decouvrir-prepa__pricing-inner">
					<?php if ($tab['price_prefix'] !== '') : ?>
						<p class="wpcursor-decouvrir-prepa__price-prefix"><?php echo esc_html($tab['price_prefix']); ?></p>
					<?php endif; ?>
					<?php if ($tab['price_amount'] !== '') : ?>
						<p class="wpcursor-decouvrir-prepa__price-amount"><?php echo esc_html($tab['price_amount']); ?></p>
					<?php endif; ?>
					<?php if ($tab['price_subtitle'] !== '') : ?>
						<p class="wpcursor-decouvrir-prepa__price-subtitle"><?php echo esc_html($tab['price_subtitle']); ?></p>
					<?php endif; ?>

					<?php if ($tab['features'] !== []) : ?>
						<div class="wpcursor-decouvrir-prepa__price-divider" aria-hidden="true"></div>
						<ul class="wpcursor-decouvrir-prepa__features">
							<?php foreach ($tab['features'] as $feature) : ?>
								<li>
									<span class="wpcursor-decouvrir-prepa__feature-icon" aria-hidden="true"></span>
									<span><?php echo esc_html($feature); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ($tab['card_link_text'] !== '' && $tab['card_link_url'] !== '') : ?>
						<a class="wpcursor-decouvrir-prepa__card-link" href="<?php echo esc_url($tab['card_link_url']); ?>">
							<?php echo esc_html($tab['card_link_text']); ?>
						</a>
					<?php elseif ($tab['card_link_text'] !== '') : ?>
						<span class="wpcursor-decouvrir-prepa__card-link"><?php echo esc_html($tab['card_link_text']); ?></span>
					<?php endif; ?>
				</div>
				<div class="wpcursor-decouvrir-prepa__pricing-chevron" aria-hidden="true"></div>
			</div>

			<div class="wpcursor-decouvrir-prepa__detail">
				<?php if ($tab['badge1'] !== '' || $tab['badge2'] !== '') : ?>
					<div class="wpcursor-decouvrir-prepa__badges">
						<?php foreach (['badge1', 'badge2'] as $bk) : ?>
							<?php if ($tab[ $bk ] !== '') : ?>
								<span class="wpcursor-decouvrir-prepa__badge">
									<span class="wpcursor-decouvrir-prepa__badge-icon" aria-hidden="true"></span>
									<span><?php echo esc_html($tab[ $bk ]); ?></span>
								</span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ($tab['intro_lead'] !== '') : ?>
					<p class="wpcursor-decouvrir-prepa__intro-lead"><?php echo esc_html($tab['intro_lead']); ?></p>
				<?php endif; ?>
				<?php if ($tab['intro_body'] !== '') : ?>
					<p class="wpcursor-decouvrir-prepa__intro-body"><?php echo esc_html($tab['intro_body']); ?></p>
				<?php endif; ?>
				<?php if ($tab['highlight_text'] !== '') : ?>
					<div class="wpcursor-decouvrir-prepa__highlight">
						<p><?php echo esc_html($tab['highlight_text']); ?></p>
					</div>
				<?php endif; ?>

				<div class="wpcursor-decouvrir-prepa__actions">
					<?php if ($tab['cta_text'] !== '' && $tab['cta_url'] !== '') : ?>
						<a class="wpcursor-decouvrir-prepa__cta" href="<?php echo esc_url($tab['cta_url']); ?>">
							<?php echo esc_html($tab['cta_text']); ?>
						</a>
					<?php elseif ($tab['cta_text'] !== '') : ?>
						<span class="wpcursor-decouvrir-prepa__cta wpcursor-decouvrir-prepa__cta--static">
							<?php echo esc_html($tab['cta_text']); ?>
						</span>
					<?php endif; ?>

					<?php if ($tab['detail_link_text'] !== '' && $tab['detail_link_url'] !== '') : ?>
						<a class="wpcursor-decouvrir-prepa__detail-link" href="<?php echo esc_url($tab['detail_link_url']); ?>">
							<?php echo esc_html($tab['detail_link_text']); ?>
						</a>
					<?php elseif ($tab['detail_link_text'] !== '') : ?>
						<span class="wpcursor-decouvrir-prepa__detail-link"><?php echo esc_html($tab['detail_link_text']); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
};
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-decouvrir-prepa<?php echo $has_tabs ? ' wpcursor-decouvrir-prepa--tabs' : ''; ?>"
	data-wpcursor="decouvrir-prepa"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-decouvrir-prepa__inner">
		<?php if ($section_title !== '') : ?>
			<h2 class="wpcursor-decouvrir-prepa__heading"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>

		<?php if ($has_tabs) : ?>
			<div class="wpcursor-decouvrir-prepa__tabbar-wrap">
				<div class="wpcursor-decouvrir-prepa__tabbar" role="tablist" aria-label="<?php esc_attr_e('Nos prépas', 'wpcursor'); ?>" id="<?php echo esc_attr($tabs_id); ?>">
					<?php foreach ($tabs as $i => $tab) : ?>
						<button
							type="button"
							class="wpcursor-decouvrir-prepa__tab<?php echo $i === $active_index ? ' is-active' : ''; ?>"
							id="<?php echo esc_attr($instance_id . '-tab-' . $i); ?>"
							role="tab"
							aria-selected="<?php echo $i === $active_index ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr($instance_id . '-panel-' . $i); ?>"
							data-tab-index="<?php echo esc_attr((string) $i); ?>"
						>
							<?php echo esc_html($tab['label']); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="wpcursor-decouvrir-prepa__panels">
			<?php foreach ($tabs as $i => $tab) : ?>
				<?php $render_panel($tab, $i, $i === $active_index); ?>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-decouvrir-prepa__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
