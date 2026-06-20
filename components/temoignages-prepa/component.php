<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Témoignages prépa — carrousel vidéo/image + citation.
 *
 * [wpcursor name="temoignages-prepa"]
 * Slides : slide1_*, slide2_*, slide3_* … (video_url, video_etiquette, quote, author, image_url).
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-temoignages-prepa-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

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

$section_title       = $prop_str($props, 'section_title', 'Une question sur notre préparation Sciences Po ? Ecrivez-nous !');
$cta_text            = $prop_str($props, 'cta_text', 'Rejoindre la prépa');
$cta_url             = isset($props['cta_url']) ? esc_url((string) $props['cta_url']) : '';
$logo_url            = isset($props['logo_url']) ? esc_url((string) $props['logo_url']) : '';
$logo_alt            = $prop_str($props, 'logo_alt', 'Cours Thalès');
$show_bottom_chevron = ! isset($props['show_bottom_chevron']) || (string) $props['show_bottom_chevron'] !== 'no';
$custom_html         = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css          = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$slide1_defaults = [
	'video_url'        => '',
	'video_etiquette'  => 'Nos anciens élèves témoignent',
	'video_caption'    => 'Prépa Sciences Po en Première et en Terminale',
	'image_url'        => '',
	'image_alt'        => 'Témoignage élève Cours Thalès',
	'quote'            => 'Cela m\'a apporté deux choses sur les deux années où je l\'ai faite. La première année, cela a affirmé ma motivation et cela m\'a aussi permis de me familiariser avec les procédures d\'admission et après en Terminale cela m\'a vraiment permis de prendre confiance en moi…',
	'author'           => 'Marie – Stage Sciences Po',
];

/**
 * @param array<string, mixed> $props
 * @param array<string, string> $defaults
 *
 * @return array<string, string>|null
 */
$build_slide = static function (array $props, string $prefix, array $defaults, callable $prop_str, callable $extract_youtube_id): ?array {
	$get = static function (string $key) use ($props, $prefix, $defaults, $prop_str): string {
		return $prop_str($props, $prefix . $key, $defaults[ $key ] ?? '');
	};

	$video_url = isset($props[ $prefix . 'video_url' ]) ? esc_url((string) $props[ $prefix . 'video_url' ]) : '';
	$image_url = isset($props[ $prefix . 'image_url' ]) ? esc_url((string) $props[ $prefix . 'image_url' ]) : '';
	$quote     = $get('quote');
	$author    = $get('author');

	if ($video_url === '' && $image_url === '' && $quote === '' && $author === '') {
		return null;
	}

	$video_id  = $extract_youtube_id($video_url);
	$embed_src = $video_id !== ''
		? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($video_id) . '?rel=0'
		: '';

	return [
		'video_url'       => $video_url,
		'video_id'        => $video_id,
		'embed_src'       => $embed_src,
		'video_etiquette' => $get('video_etiquette'),
		'video_caption'   => $get('video_caption'),
		'image_url'       => $image_url,
		'image_alt'       => $get('image_alt'),
		'quote'           => $quote,
		'author'          => $author,
	];
};

$max_slide_index = 1;
foreach (array_keys($props) as $prop_key) {
	if (is_string($prop_key) && preg_match('/^slide(\d+)_/', $prop_key, $m)) {
		$max_slide_index = max($max_slide_index, (int) $m[1]);
	}
}

$slides = [];
for ($i = 1; $i <= $max_slide_index; $i++) {
	$prefix   = 'slide' . $i . '_';
	$defaults = $i === 1 ? $slide1_defaults : array_fill_keys(array_keys($slide1_defaults), '');
	$slide    = $build_slide($props, $prefix, $defaults, $prop_str, $extract_youtube_id);
	if ($slide !== null) {
		$slides[] = $slide;
	}
}

if ($slides === []) {
	return;
}

$multi = count($slides) > 1;
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-temoignages-prepa<?php echo $multi ? ' wpcursor-temoignages-prepa--carousel' : ''; ?><?php echo $show_bottom_chevron ? ' wpcursor-temoignages-prepa--chevron' : ''; ?>"
	data-wpcursor="temoignages-prepa"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
	<?php echo $multi ? ' data-slide-count="' . esc_attr((string) count($slides)) . '"' : ''; ?>
>
	<div class="wpcursor-temoignages-prepa__inner">
		<?php if ($section_title !== '') : ?>
			<h2 class="wpcursor-temoignages-prepa__heading"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>

		<div class="wpcursor-temoignages-prepa__frame">
			<?php if ($multi) : ?>
				<button type="button" class="wpcursor-temoignages-prepa__nav wpcursor-temoignages-prepa__nav--prev" data-action="prev" aria-label="<?php esc_attr_e('Témoignage précédent', 'wpcursor'); ?>">
					<span aria-hidden="true"></span>
				</button>
			<?php endif; ?>

			<div class="wpcursor-temoignages-prepa__viewport">
				<?php foreach ($slides as $index => $slide) : ?>
					<article
						class="wpcursor-temoignages-prepa__slide"
						data-slide-index="<?php echo esc_attr((string) $index); ?>"
						<?php echo ( $multi && $index > 0 ) ? ' hidden' : ''; ?>
					>
						<div class="wpcursor-temoignages-prepa__grid">
							<div class="wpcursor-temoignages-prepa__media">
								<div class="wpcursor-temoignages-prepa__media-wrap">
									<?php if ($slide['embed_src'] !== '') : ?>
										<div class="wpcursor-temoignages-prepa__video-ratio">
											<iframe
												class="wpcursor-temoignages-prepa__iframe"
												src="<?php echo esc_url($slide['embed_src']); ?>"
												title="<?php echo esc_attr($slide['video_etiquette'] !== '' ? $slide['video_etiquette'] : $slide['image_alt']); ?>"
												allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
												allowfullscreen
												loading="lazy"
											></iframe>
										</div>
									<?php elseif ($slide['image_url'] !== '') : ?>
										<img
											class="wpcursor-temoignages-prepa__photo"
											src="<?php echo esc_url($slide['image_url']); ?>"
											alt="<?php echo esc_attr($slide['image_alt']); ?>"
											loading="lazy"
											decoding="async"
										/>
									<?php else : ?>
										<div class="wpcursor-temoignages-prepa__media-placeholder" aria-hidden="true">
											<span class="wpcursor-temoignages-prepa__play-icon"></span>
										</div>
									<?php endif; ?>

									<?php if ($slide['video_etiquette'] !== '') : ?>
										<p class="wpcursor-temoignages-prepa__etiquette"><?php echo esc_html($slide['video_etiquette']); ?></p>
									<?php endif; ?>

									<?php if ($slide['video_caption'] !== '') : ?>
										<p class="wpcursor-temoignages-prepa__caption"><?php echo esc_html($slide['video_caption']); ?></p>
									<?php endif; ?>

									<?php if ($logo_url !== '') : ?>
										<img
											class="wpcursor-temoignages-prepa__logo"
											src="<?php echo esc_url($logo_url); ?>"
											alt="<?php echo esc_attr($logo_alt); ?>"
											loading="lazy"
											decoding="async"
										/>
									<?php else : ?>
										<p class="wpcursor-temoignages-prepa__logo-text" aria-hidden="true">CoursThalès</p>
									<?php endif; ?>
								</div>
							</div>

							<div class="wpcursor-temoignages-prepa__content">
								<?php if ($slide['quote'] !== '') : ?>
									<blockquote class="wpcursor-temoignages-prepa__quote">
										<p><?php echo esc_html($slide['quote']); ?></p>
									</blockquote>
								<?php endif; ?>

								<?php if ($slide['author'] !== '') : ?>
									<p class="wpcursor-temoignages-prepa__author"><?php echo esc_html($slide['author']); ?></p>
								<?php endif; ?>

								<?php if ($cta_text !== '' && $cta_url !== '') : ?>
									<a class="wpcursor-temoignages-prepa__cta" href="<?php echo esc_url($cta_url); ?>">
										<?php echo esc_html($cta_text); ?>
									</a>
								<?php elseif ($cta_text !== '') : ?>
									<span class="wpcursor-temoignages-prepa__cta wpcursor-temoignages-prepa__cta--static">
										<?php echo esc_html($cta_text); ?>
									</span>
								<?php endif; ?>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<?php if ($multi) : ?>
				<button type="button" class="wpcursor-temoignages-prepa__nav wpcursor-temoignages-prepa__nav--next" data-action="next" aria-label="<?php esc_attr_e('Témoignage suivant', 'wpcursor'); ?>">
					<span aria-hidden="true"></span>
				</button>
			<?php endif; ?>
		</div>

		<?php if ($multi) : ?>
			<div class="wpcursor-temoignages-prepa__pagination" role="tablist" aria-label="<?php esc_attr_e('Choisir un témoignage', 'wpcursor'); ?>">
				<?php foreach ($slides as $index => $slide) : ?>
					<button
						type="button"
						class="wpcursor-temoignages-prepa__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
						role="tab"
						data-slide-to="<?php echo esc_attr((string) $index); ?>"
						aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
						aria-label="<?php echo esc_attr(sprintf(/* translators: %d: slide number */ __('Témoignage %d', 'wpcursor'), $index + 1)); ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-temoignages-prepa__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
