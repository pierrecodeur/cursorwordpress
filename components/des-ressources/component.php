<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Des ressources — liens + Spotify / audio.
 *
 * [wpcursor name="des-ressources"]
 * [wpcursor name="des-ressources" spotify_url="https://open.spotify.com/episode/…" spotify_vignette_url="https://…"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-des-ressources-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

/**
 * @return string URL embed Spotify ou chaîne vide.
 */
$build_spotify_embed = static function (string $spotify_url, string $spotify_embed_url): string {
	if ($spotify_embed_url !== '') {
		return esc_url($spotify_embed_url);
	}
	$spotify_url = trim($spotify_url);
	if ($spotify_url === '') {
		return '';
	}
	if (preg_match('#open\.spotify\.com/embed/(episode|track|show|playlist)/([a-zA-Z0-9]+)#i', $spotify_url, $m)) {
		return 'https://open.spotify.com/embed/' . $m[1] . '/' . $m[2] . '?theme=0';
	}
	if (preg_match('#open\.spotify\.com/(episode|track|show|playlist)/([a-zA-Z0-9]+)#i', $spotify_url, $m)) {
		return 'https://open.spotify.com/embed/' . $m[1] . '/' . $m[2] . '?theme=0';
	}
	return '';
};

$section_title = $prop_str(
	$props,
	'section_title',
	'Des ressources pour accompagner les élèves vers la réussite en IEP'
);
$intro_text = $prop_str(
	$props,
	'intro_text',
	'La Prépa Sciences Po Cours Thalès propose également des dossiers en libre accès pour aider les Lycéens qui souhaitent intégrer un Institut d\'Études Politiques :'
);
$spotify_url       = isset($props['spotify_url']) ? esc_url((string) $props['spotify_url']) : '';
$spotify_embed_in  = isset($props['spotify_embed_url']) ? esc_url((string) $props['spotify_embed_url']) : '';
$spotify_embed     = $build_spotify_embed($spotify_url, $spotify_embed_in);
$spotify_vignette  = isset($props['spotify_vignette_url']) ? esc_url((string) $props['spotify_vignette_url']) : '';
$spotify_vignette_alt = $prop_str($props, 'spotify_vignette_alt', 'Podcast Cours Thalès — Leçons d\'avenir');
$spotify_title     = $prop_str($props, 'spotify_title', 'La success story de Marie, élève à Sciences Po Paris');
$spotify_meta      = $prop_str($props, 'spotify_meta', 'Leçons d\'avenir • Podcast Cours Thalès');
$spotify_save      = $prop_str($props, 'spotify_save_label', 'Enregistrer sur Spotify');
$audio_url         = isset($props['audio_url']) ? esc_url((string) $props['audio_url']) : '';
$custom_html       = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css        = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$resource_defaults = [
	'Quelles spécialités pour faire Sciences Po ?',
	'La lettre de motivation pour Sciences Po',
	'Comment réussir l\'épreuve d\'histoire du concours commun ?',
	'Les épreuves orales des IEP : conseils et méthode',
	'Parcoursup et Sciences Po : les bonnes pratiques',
	'Culture générale : par où commencer ?',
	'Les différents concours pour intégrer un IEP',
	'Sciences Po Paris : procédure d\'admission',
	'Préparer le Grand Oral du bac en prépa Sciences Po',
	'Stage en Première : est-ce utile pour Sciences Po ?',
	'Les erreurs à éviter dans son dossier Parcoursup',
];

$max_resource = 0;
foreach (array_keys($props) as $prop_key) {
	if (is_string($prop_key) && preg_match('/^resource(\d+)_text$/', $prop_key, $m)) {
		$max_resource = max($max_resource, (int) $m[1]);
	}
}
if ($max_resource === 0) {
	$max_resource = count($resource_defaults);
}

$resources = [];
for ($i = 1; $i <= $max_resource; $i++) {
	$text = $prop_str($props, 'resource' . $i . '_text', $resource_defaults[ $i - 1 ] ?? '');
	$url  = isset($props[ 'resource' . $i . '_url' ]) ? esc_url((string) $props[ 'resource' . $i . '_url' ]) : '';

	if ($text === '') {
		continue;
	}

	$resources[] = [
		'text' => $text,
		'url'  => $url,
	];
}

$has_media = $spotify_embed !== '' || $audio_url !== '' || $spotify_vignette !== '';
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-des-ressources"
	data-wpcursor="des-ressources"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
>
	<div class="wpcursor-des-ressources__inner">
		<?php if ($section_title !== '') : ?>
			<h2 class="wpcursor-des-ressources__heading"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>

		<?php if ($intro_text !== '') : ?>
			<p class="wpcursor-des-ressources__intro"><?php echo esc_html($intro_text); ?></p>
		<?php endif; ?>

		<div class="wpcursor-des-ressources__grid">
			<?php if ($resources !== []) : ?>
				<ul class="wpcursor-des-ressources__links">
					<?php foreach ($resources as $resource) : ?>
						<li class="wpcursor-des-ressources__link-item">
							<?php if ($resource['url'] !== '') : ?>
								<a class="wpcursor-des-ressources__link" href="<?php echo esc_url($resource['url']); ?>">
									<?php echo esc_html($resource['text']); ?>
								</a>
							<?php else : ?>
								<span class="wpcursor-des-ressources__link wpcursor-des-ressources__link--static">
									<?php echo esc_html($resource['text']); ?>
								</span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ($has_media) : ?>
				<div class="wpcursor-des-ressources__player-card">
					<div class="wpcursor-des-ressources__player-brand" aria-hidden="true">
						<span class="wpcursor-des-ressources__spotify-logo"></span>
					</div>

					<?php if ($spotify_vignette !== '') : ?>
						<div class="wpcursor-des-ressources__vignette-wrap">
							<img
								class="wpcursor-des-ressources__spotify-vignette"
								src="<?php echo esc_url($spotify_vignette); ?>"
								alt="<?php echo esc_attr($spotify_vignette_alt); ?>"
								loading="lazy"
								decoding="async"
							/>
						</div>
					<?php else : ?>
						<div class="wpcursor-des-ressources__vignette-wrap wpcursor-des-ressources__vignette-wrap--placeholder" aria-hidden="true">
							<p class="wpcursor-des-ressources__vignette-placeholder-title">Cours Thalès</p>
							<p class="wpcursor-des-ressources__vignette-placeholder-sub">Leçons d'avenir — Podcast</p>
						</div>
					<?php endif; ?>

					<div class="wpcursor-des-ressources__player-meta">
						<?php if ($spotify_title !== '') : ?>
							<p class="wpcursor-des-ressources__spotify-title"><?php echo esc_html($spotify_title); ?></p>
						<?php endif; ?>
						<?php if ($spotify_meta !== '') : ?>
							<p class="wpcursor-des-ressources__spotify-meta"><?php echo esc_html($spotify_meta); ?></p>
						<?php endif; ?>
						<?php if ($spotify_save !== '' && $spotify_url !== '') : ?>
							<a class="wpcursor-des-ressources__spotify-save" href="<?php echo esc_url($spotify_url); ?>" target="_blank" rel="noopener noreferrer">
								<span class="wpcursor-des-ressources__spotify-save-icon" aria-hidden="true">+</span>
								<?php echo esc_html($spotify_save); ?>
							</a>
						<?php elseif ($spotify_save !== '') : ?>
							<span class="wpcursor-des-ressources__spotify-save wpcursor-des-ressources__spotify-save--static">
								<span class="wpcursor-des-ressources__spotify-save-icon" aria-hidden="true">+</span>
								<?php echo esc_html($spotify_save); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="wpcursor-des-ressources__player-audio">
						<?php if ($spotify_embed !== '') : ?>
							<iframe
								class="wpcursor-des-ressources__spotify-iframe"
								src="<?php echo esc_url($spotify_embed); ?>"
								title="<?php echo esc_attr($spotify_title !== '' ? $spotify_title : 'Lecteur Spotify'); ?>"
								allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
								loading="lazy"
							></iframe>
						<?php elseif ($audio_url !== '') : ?>
							<audio class="wpcursor-des-ressources__audio" controls preload="none">
								<source src="<?php echo esc_url($audio_url); ?>" />
							</audio>
						<?php else : ?>
							<p class="wpcursor-des-ressources__player-hint">
								<?php esc_html_e('Renseignez spotify_url ou audio_url pour activer le lecteur.', 'wpcursor'); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-des-ressources__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
