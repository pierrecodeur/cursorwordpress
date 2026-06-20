<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Bandeau d'étiquettes défilantes (double rangée, badges pill).
 *
 * [wpcursor name="etiquettes-defilantes"]
 * [wpcursor name="etiquettes-defilantes" item1="17 ans d'expérience" item2="Petits effectifs" speed="normal"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';
$instance_id = 'wpcursor-etiquettes-defilantes-' . wp_rand(1000, 999999);

$speed = isset($props['speed']) ? strtolower((string) $props['speed']) : 'normal';
if (! in_array($speed, ['slow', 'normal', 'fast'], true)) {
	$speed = 'normal';
}

$show_icon = ! isset($props['show_icon']) || (string) $props['show_icon'] !== 'no';

$defaults = [
	'item1' => '17 ans d\'expérience',
	'item2' => 'Préparation concours',
	'item3' => 'Petits effectifs',
	'item4' => 'Accompagnement personnalisé',
];

$items = [];
foreach ($props as $key => $value) {
	if (is_string($key) && preg_match('/^item([0-9]{1,3})$/', $key, $m)) {
		$idx = (int) $m[1];
		$txt = trim((string) $value);
		if ($txt !== '') {
			$items[ $idx ] = $txt;
		}
	}
}

if ($items === []) {
	foreach ($defaults as $key => $text) {
		$prop_val = isset($props[ $key ]) ? trim((string) $props[ $key ]) : '';
		$items[]  = $prop_val !== '' ? $prop_val : $text;
	}
} else {
	ksort($items, SORT_NUMERIC);
	$items = array_values($items);
}

/** Duplique les libellés pour un bandeau plus long (défilement visible même avec peu d'items). */
$marquee_labels = $items;
$min_marquee_count = max(8, count($items) * 2);
while (count($marquee_labels) < $min_marquee_count) {
	$marquee_labels = array_merge($marquee_labels, $items);
}

$is_preview = ($runtime === 'admin_preview');

/**
 * @param callable(string): void $render_badge
 */
$render_track = static function (array $labels, string $row_class, callable $render_badge): void {
	if ($labels === []) {
		return;
	}
	?>
	<div class="wpcursor-etiquettes-defilantes__viewport <?php echo esc_attr($row_class); ?>">
		<div class="wpcursor-etiquettes-defilantes__track" aria-hidden="true">
			<div class="wpcursor-etiquettes-defilantes__group">
				<?php foreach ($labels as $label) : ?>
					<?php $render_badge($label); ?>
				<?php endforeach; ?>
			</div>
			<div class="wpcursor-etiquettes-defilantes__group" aria-hidden="true">
				<?php foreach ($labels as $label) : ?>
					<?php $render_badge($label); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
};

$render_badge = static function (string $label) use ($show_icon): void {
	?>
	<span class="wpcursor-etiquettes-defilantes__badge">
		<?php if ($show_icon) : ?>
			<span class="wpcursor-etiquettes-defilantes__icon" aria-hidden="true"></span>
		<?php endif; ?>
		<span class="wpcursor-etiquettes-defilantes__label"><?php echo esc_html($label); ?></span>
	</span>
	<?php
};
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-etiquettes-defilantes wpcursor-etiquettes-defilantes--speed-<?php echo esc_attr($speed); ?><?php echo $show_icon ? '' : ' wpcursor-etiquettes-defilantes--no-icon'; ?><?php echo $is_preview ? ' wpcursor-etiquettes-defilantes--preview' : ''; ?>"
	data-wpcursor="etiquettes-defilantes"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
	aria-label="<?php esc_attr_e('Points forts', 'wpcursor'); ?>"
>
	<div class="wpcursor-etiquettes-defilantes__rows">
		<?php $render_track($marquee_labels, 'wpcursor-etiquettes-defilantes__viewport--row1', $render_badge); ?>
		<?php $render_track($marquee_labels, 'wpcursor-etiquettes-defilantes__viewport--row2', $render_badge); ?>
	</div>

	<ul class="wpcursor-etiquettes-defilantes__sr-only">
		<?php foreach ($items as $label) : ?>
			<li><?php echo esc_html($label); ?></li>
		<?php endforeach; ?>
	</ul>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-etiquettes-defilantes__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
