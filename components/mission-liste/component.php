<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Section mission — liste à puces (mise en forme fixe, textes via shortcode).
 *
 * Shortcode minimal (textes par défaut) :
 *   [wpcursor name="mission-liste"]
 *
 * Shortcode personnalisé :
 *   [wpcursor name="mission-liste" title="…" intro="…" item1="…" item2="…" item3="…"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$heading_tag = isset($props['heading_tag']) ? strtolower((string) $props['heading_tag']) : 'h2';
if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'p'], true)) {
	$heading_tag = 'h2';
}
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';
$instance_id = 'wpcursor-mission-liste-' . wp_rand(1000, 999999);

$defaults = [
	'title' => 'La mission de Cours Thalès : accompagner avec bienveillance vers l\'excellence',
	'intro' => 'Un élève motivé doit pouvoir compter sur une multitude d\'outils pour atteindre son objectif : cours et supports de haut niveau, méthodes, expertises… il s\'agit de savoir transmettre ces petites (et grandes) choses qui font la différence entre un bon élève et un élève qui réussit.',
	'item1' => 'Garantir un juste équilibre entre efficacité, bienveillance et exigence pour que chacune de nos préparations permette d\'atteindre le meilleur niveau possible. Pour cela tous nos cours sont dispensés avec des effectifs réduits pour permettre aux enseignants de s\'adapter aux besoins des élèves et d\'être disponibles. Nos tuteurs tissent un lien de proximité et jouent le rôle de conseiller et de soutien moral pour les élèves.',
	'item2' => 'Offrir une tranquillité d\'esprit que ce soit aux élèves en leur permettant d\'avoir confiance en eux en se sentant préparés, et aux familles qui nous confie leur enfant, en les informant et en les accompagnant.',
	'item3' => 'Partager une expertise pour transmettre les « règles du jeu » qui feront la différence (méthodes, codes et usages…). La sélectivité des filières auxquelles nous préparons nécessite d\'y être extrêmement bien préparé et de connaître les « trucs & astuces » de l\'intérieur. Nos professeurs sont tous experts des filières auxquelles ils préparent.',
];

$title = isset($props['title']) && $props['title'] !== '' ? (string) $props['title'] : $defaults['title'];
$intro = isset($props['intro']) && $props['intro'] !== '' ? (string) $props['intro'] : $defaults['intro'];

$items = [];
foreach ($props as $key => $value) {
	if (is_string($key) && preg_match('/^item([0-9]{1,3})$/', $key, $m)) {
		$idx           = (int) $m[1];
		$items[ $idx ] = (string) $value;
	}
}
if ($items === []) {
	foreach (['item1', 'item2', 'item3'] as $key) {
		$text = isset($props[ $key ]) && $props[ $key ] !== '' ? (string) $props[ $key ] : $defaults[ $key ];
		if ($text !== '') {
			$items[] = $text;
		}
	}
} else {
	ksort($items, SORT_NUMERIC);
	$items = array_values(
		array_filter(
			$items,
			static fn( string $txt ): bool => trim($txt) !== ''
		)
	);
}
?>
<section id="<?php echo esc_attr($instance_id); ?>" class="wpcursor-component wpcursor-mission-liste" data-wpcursor="mission-liste" data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>">
	<div class="wpcursor-mission-liste__outer">
		<?php if ($title !== '') : ?>
			<<?php echo esc_html($heading_tag); ?> class="wpcursor-mission-liste__title"><?php echo esc_html($title); ?></<?php echo esc_html($heading_tag); ?>>
		<?php endif; ?>

		<div class="wpcursor-mission-liste__card">
			<?php if ($intro !== '') : ?>
				<p class="wpcursor-mission-liste__intro"><?php echo esc_html($intro); ?></p>
			<?php endif; ?>

			<?php if ($items !== []) : ?>
				<ul class="wpcursor-mission-liste__list">
					<?php foreach ($items as $item_text) : ?>
						<li class="wpcursor-mission-liste__item">
							<span class="wpcursor-mission-liste__icon" aria-hidden="true"></span>
							<p class="wpcursor-mission-liste__text"><?php echo esc_html($item_text); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ($custom_html !== '') : ?>
				<div class="wpcursor-mission-liste__custom-html">
					<?php echo wp_kses_post($custom_html); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
