<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Nos professeurs — cartes portrait avec citation.
 *
 * [wpcursor name="nos-professeurs"]
 * [wpcursor name="nos-professeurs" teacher1_image_url="https://…" teacher1_name="Thomas"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-nos-professeurs-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$section_title = $prop_str(
	$props,
	'section_title',
	'Nos professeurs agrégés, normaliens et jury d\'admission de la prépa Sciences po :'
);
$custom_html = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css  = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$default_quote = 'Les résultats, c\'est la seule promesse qui compte.';
$default_name  = 'Thomas';

$teachers = [];
for ($i = 1; $i <= 4; $i++) {
	$prefix = 'teacher' . $i . '_';
	$name   = $prop_str($props, $prefix . 'name', $default_name);
	$quote  = $prop_str($props, $prefix . 'quote', '« ' . $default_quote . ' »');
	$image  = isset($props[ $prefix . 'image_url' ]) ? esc_url((string) $props[ $prefix . 'image_url' ]) : '';
	$alt    = $prop_str($props, $prefix . 'image_alt', $name !== '' ? $name : 'Professeur');

	$has_content = $name !== '' || $quote !== '' || $image !== '';
	if (! $has_content && $i > 1) {
		continue;
	}

	$teachers[] = [
		'name'      => $name,
		'quote'     => $quote,
		'image_url' => $image,
		'image_alt' => $alt,
	];
}

if ($teachers === []) {
	$teachers[] = [
		'name'      => $default_name,
		'quote'     => '« ' . $default_quote . ' »',
		'image_url' => '',
		'image_alt' => $default_name,
	];
}
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-nos-professeurs"
	data-wpcursor="nos-professeurs"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-nos-professeurs__inner">
		<?php if ($section_title !== '') : ?>
			<h2 class="wpcursor-nos-professeurs__heading"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>

		<div class="wpcursor-nos-professeurs__grid">
			<?php foreach ($teachers as $teacher) : ?>
				<article class="wpcursor-nos-professeurs__card">
					<?php if ($teacher['image_url'] !== '') : ?>
						<img
							class="wpcursor-nos-professeurs__photo"
							src="<?php echo esc_url($teacher['image_url']); ?>"
							alt="<?php echo esc_attr($teacher['image_alt']); ?>"
							loading="lazy"
							decoding="async"
						/>
					<?php else : ?>
						<div class="wpcursor-nos-professeurs__photo wpcursor-nos-professeurs__photo--placeholder" aria-hidden="true"></div>
					<?php endif; ?>

					<div class="wpcursor-nos-professeurs__overlay">
						<?php if ($teacher['quote'] !== '') : ?>
							<p class="wpcursor-nos-professeurs__quote"><?php echo esc_html($teacher['quote']); ?></p>
						<?php endif; ?>
						<?php if ($teacher['name'] !== '') : ?>
							<p class="wpcursor-nos-professeurs__name"><?php echo esc_html($teacher['name']); ?></p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-nos-professeurs__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
