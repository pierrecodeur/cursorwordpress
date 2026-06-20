<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Points forts de la prépa — vidéo YouTube + liste + CTA.
 *
 * [wpcursor name="points-forts-prepa"]
 * [wpcursor name="points-forts-prepa" video_url="https://youtu.be/…" video_etiquette="La Prépa Sciences Po Cours Thalès"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-points-forts-prepa-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

/**
 * @return string ID YouTube ou chaîne vide.
 */
$extract_youtube_id = static function (string $url): string {
	$url = trim($url);
	if ($url === '') {
		return '';
	}
	if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $m)) {
		return $m[1];
	}
	return '';
};

/**
 * @return array{0: string, 1: string} [partie emphase, reste]
 */
$split_point = static function (string $text): array {
	$text = trim($text);
	if ($text === '') {
		return ['', ''];
	}
	$pos = strpos($text, ',');
	if ($pos === false) {
		return [$text, ''];
	}
	return [trim(substr($text, 0, $pos + 1)), trim(substr($text, $pos + 1))];
};

$section_title    = $prop_str($props, 'section_title', 'Les points forts de la Prépa');
$section_subtitle = $prop_str($props, 'section_subtitle', 'Sciences Po de Cours Thalès');
$video_url        = isset($props['video_url']) ? esc_url((string) $props['video_url']) : '';
$video_id         = $extract_youtube_id($video_url);
$video_etiquette  = $prop_str($props, 'video_etiquette', 'La Prépa Sciences Po Cours Thalès');
$show_etiquette   = ! isset($props['show_etiquette']) || (string) $props['show_etiquette'] !== 'no';
$video_caption    = $prop_str($props, 'video_caption', 'Découvrez en vidéo notre approche pour intégrer Sciences Po.');
$cta_text         = $prop_str($props, 'cta_text', 'S\'inscrire à la prépa');
$cta_url          = isset($props['cta_url']) ? esc_url((string) $props['cta_url']) : '';
$custom_html      = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css       = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$point_defaults = [
	'Nos méthodes pédagogiques innovantes, garanties d\'une réussite scolaire et universitaire',
	'Des supports de cours préparés par les meilleurs professeurs',
	'Des exercices de préparation inclus (Bac blanc et Concours blanc)',
	'Notre coaching Parcoursup pour passer cette phase stressante en toute sérénité',
	'Une plateforme digitale de e-learning pour travailler et s\'entraîner à son rythme',
	'Nos préparations en visio interactive : des cours en direct',
];

$points = [];
for ($i = 1; $i <= 6; $i++) {
	$key = 'point' . $i;
	$txt = $prop_str($props, $key, $point_defaults[ $i - 1 ] ?? '');
	if ($txt !== '') {
		$points[] = $txt;
	}
}

$embed_src = $video_id !== ''
	? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($video_id) . '?rel=0'
	: '';
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-points-forts-prepa"
	data-wpcursor="points-forts-prepa"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-points-forts-prepa__inner">
		<header class="wpcursor-points-forts-prepa__header">
			<?php if ($section_title !== '') : ?>
				<h2 class="wpcursor-points-forts-prepa__heading">
					<span class="wpcursor-points-forts-prepa__heading-main"><?php echo esc_html($section_title); ?></span>
					<?php if ($section_subtitle !== '') : ?>
						<span class="wpcursor-points-forts-prepa__heading-sub"><?php echo esc_html($section_subtitle); ?></span>
					<?php endif; ?>
				</h2>
			<?php endif; ?>
		</header>

		<div class="wpcursor-points-forts-prepa__grid">
			<div class="wpcursor-points-forts-prepa__media">
				<div class="wpcursor-points-forts-prepa__video-wrap">
					<?php if ($show_etiquette && $video_etiquette !== '') : ?>
						<p class="wpcursor-points-forts-prepa__video-etiquette"><?php echo esc_html($video_etiquette); ?></p>
					<?php endif; ?>

					<?php if ($embed_src !== '') : ?>
						<div class="wpcursor-points-forts-prepa__video-ratio">
							<iframe
								class="wpcursor-points-forts-prepa__iframe"
								src="<?php echo esc_url($embed_src); ?>"
								title="<?php echo esc_attr($video_etiquette !== '' ? $video_etiquette : 'Vidéo Cours Thalès'); ?>"
								allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
								allowfullscreen
								loading="lazy"
							></iframe>
						</div>
					<?php else : ?>
						<div class="wpcursor-points-forts-prepa__video-placeholder" aria-hidden="true">
							<span class="wpcursor-points-forts-prepa__play-icon"></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($video_caption !== '') : ?>
					<p class="wpcursor-points-forts-prepa__video-caption"><?php echo esc_html($video_caption); ?></p>
				<?php endif; ?>
			</div>

			<?php if ($points !== []) : ?>
				<ul class="wpcursor-points-forts-prepa__points">
					<?php foreach ($points as $point_text) : ?>
						<?php [$emphasis, $rest] = $split_point($point_text); ?>
						<li class="wpcursor-points-forts-prepa__point">
							<span class="wpcursor-points-forts-prepa__point-icon" aria-hidden="true"></span>
							<span class="wpcursor-points-forts-prepa__point-text">
								<?php if ($emphasis !== '' && $rest !== '') : ?>
									<strong><?php echo esc_html($emphasis); ?></strong>
									<?php echo esc_html(' ' . $rest); ?>
								<?php elseif ($emphasis !== '') : ?>
									<strong><?php echo esc_html($emphasis); ?></strong>
								<?php endif; ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php if ($cta_text !== '' && $cta_url !== '') : ?>
			<div class="wpcursor-points-forts-prepa__footer">
				<a class="wpcursor-points-forts-prepa__cta" href="<?php echo esc_url($cta_url); ?>">
					<?php echo esc_html($cta_text); ?>
				</a>
			</div>
		<?php elseif ($cta_text !== '') : ?>
			<div class="wpcursor-points-forts-prepa__footer">
				<span class="wpcursor-points-forts-prepa__cta wpcursor-points-forts-prepa__cta--static">
					<?php echo esc_html($cta_text); ?>
				</span>
			</div>
		<?php endif; ?>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-points-forts-prepa__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
