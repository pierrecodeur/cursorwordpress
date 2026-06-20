<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * FAQ tête de cocon — accordéon deux colonnes.
 *
 * [wpcursor name="faq-cocon"]
 * [wpcursor name="faq-cocon" faq1_question="…" faq1_answer="…" faq2_question="…"]
 */

$cx      = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$runtime = isset($cx['runtime']) ? (string) $cx['runtime'] : 'front';
$props   = isset($cx['props']) && is_array($cx['props']) ? $cx['props'] : [];
$instance_id = 'wpcursor-faq-cocon-' . wp_rand(1000, 999999);

$prop_str = static function (array $props, string $key, string $default = ''): string {
	if (isset($props[ $key ]) && trim((string) $props[ $key ]) !== '') {
		return trim((string) $props[ $key ]);
	}
	return $default;
};

$section_title = $prop_str(
	$props,
	'section_title',
	'Questions les plus fréquentes sur notre Prépa Sciences Po'
);
$cta_text            = $prop_str($props, 'cta_text', 'S\'inscrire à la prépa');
$cta_url             = isset($props['cta_url']) ? esc_url((string) $props['cta_url']) : '';
$allow_multiple_open = isset($props['allow_multiple_open']) && (string) $props['allow_multiple_open'] === 'yes';
$custom_html         = isset($props['custom_html']) ? (string) $props['custom_html'] : '';
$custom_css          = isset($props['custom_css']) ? (string) $props['custom_css'] : '';

$faq_defaults = [
	[
		'question' => 'À quoi sert-il de faire une prépa alors qu\'il n\'y pas de concours écrit pour Sciences Po Paris ?',
		'answer'   => 'La prépa vous aide à construire un dossier solide, à vous préparer aux épreuves orales et à développer la méthode et la culture générale attendues par Sciences Po.',
	],
	[
		'question' => 'Quels sont les attendus et les pré-requis pour suivre la prépa Sciences Po ?',
		'answer'   => 'Une motivation forte pour les sciences humaines et sociales, un bon niveau scolaire et l\'envie de travailler régulièrement sont les principaux pré-requis.',
	],
	[
		'question' => 'Faut-il commencer la prépa Sciences Po en première ou en terminale ?',
		'answer'   => 'Les deux parcours sont possibles : la prépa en première permet d\'anticiper Parcoursup et le bac ; en terminale, elle intensifie la préparation au concours et aux oraux.',
	],
	[
		'question' => 'La prépa Sciences Po prépare-t-elle également au Bac ?',
		'answer'   => 'Oui, notre programme intègre une préparation au bac de français et un renforcement des matières clés du lycée.',
	],
	[
		'question' => 'Quelle est la durée et le rythme des cours ?',
		'answer'   => 'Les cours ont lieu principalement le week-end, avec un accompagnement en ligne pour travailler à son rythme entre les sessions.',
	],
	[
		'question' => 'Proposez-vous un accompagnement Parcoursup ?',
		'answer'   => 'Oui, un coaching Parcoursup est inclus pour vous aider à rédiger vos vœux, vos lettres de motivation et à préparer les entretiens.',
	],
	[
		'question' => 'Peut-on suivre la prépa à distance ?',
		'answer'   => 'Oui, nos préparations en visio interactive permettent de suivre les cours en direct depuis chez soi.',
	],
	[
		'question' => 'Y a-t-il des concours blancs pendant l\'année ?',
		'answer'   => 'Des concours blancs et des exercices de préparation sont proposés pour vous entraîner dans des conditions proches du réel.',
	],
	[
		'question' => 'Quel est l\'effectif par classe ?',
		'answer'   => 'Les classes sont limitées à un effectif réduit pour garantir un suivi personnalisé de chaque élève.',
	],
	[
		'question' => 'Comment s\'inscrire à la prépa Sciences Po Cours Thalès ?',
		'answer'   => 'Remplissez le formulaire d\'inscription en ligne ou contactez-nous : nous vous orienterons vers la formule la plus adaptée à votre profil.',
	],
];

$max_faq_index = 0;
foreach (array_keys($props) as $prop_key) {
	if (is_string($prop_key) && preg_match('/^faq(\d+)_question$/', $prop_key, $m)) {
		$max_faq_index = max($max_faq_index, (int) $m[1]);
	}
}
if ($max_faq_index === 0) {
	$max_faq_index = count($faq_defaults);
}

$faqs = [];
for ($i = 1; $i <= $max_faq_index; $i++) {
	$q_key = 'faq' . $i . '_question';
	$a_key = 'faq' . $i . '_answer';
	$default = $faq_defaults[ $i - 1 ] ?? ['question' => '', 'answer' => ''];

	$question = $prop_str($props, $q_key, $default['question']);
	$answer   = $prop_str($props, $a_key, $default['answer']);

	if ($question === '') {
		continue;
	}

	$faqs[] = [
		'id'       => $instance_id . '-faq-' . $i,
		'question' => $question,
		'answer'   => $answer,
	];
}

if ($faqs === []) {
	return;
}

$half   = (int) ceil(count($faqs) / 2);
$col_left  = array_slice($faqs, 0, $half);
$col_right = array_slice($faqs, $half);

/**
 * @param array{id: string, question: string, answer: string} $item
 */
$render_item = static function (array $item): void {
	?>
	<div class="wpcursor-faq-cocon__item">
		<h3 class="wpcursor-faq-cocon__item-heading">
			<button
				type="button"
				class="wpcursor-faq-cocon__trigger"
				id="<?php echo esc_attr($item['id'] . '-trigger'); ?>"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr($item['id'] . '-panel'); ?>"
			>
				<span class="wpcursor-faq-cocon__question-text"><?php echo esc_html($item['question']); ?></span>
				<span class="wpcursor-faq-cocon__icon" aria-hidden="true"></span>
			</button>
		</h3>
		<div
			class="wpcursor-faq-cocon__panel"
			id="<?php echo esc_attr($item['id'] . '-panel'); ?>"
			role="region"
			aria-labelledby="<?php echo esc_attr($item['id'] . '-trigger'); ?>"
			hidden
		>
			<?php if ($item['answer'] !== '') : ?>
				<div class="wpcursor-faq-cocon__answer">
					<p><?php echo esc_html($item['answer']); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
};
?>
<section
	id="<?php echo esc_attr($instance_id); ?>"
	class="wpcursor-component wpcursor-faq-cocon"
	data-wpcursor="faq-cocon"
	data-wpcursor-runtime="<?php echo esc_attr($runtime); ?>"
	data-allow-multiple="<?php echo $allow_multiple_open ? 'yes' : 'no'; ?>"
>
	<div class="wpcursor-faq-cocon__inner">
		<?php if ($section_title !== '') : ?>
			<h2 class="wpcursor-faq-cocon__heading"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>

		<?php if ($cta_text !== '' && $cta_url !== '') : ?>
			<div class="wpcursor-faq-cocon__cta-wrap">
				<a class="wpcursor-faq-cocon__cta" href="<?php echo esc_url($cta_url); ?>">
					<?php echo esc_html($cta_text); ?>
				</a>
			</div>
		<?php elseif ($cta_text !== '') : ?>
			<div class="wpcursor-faq-cocon__cta-wrap">
				<span class="wpcursor-faq-cocon__cta wpcursor-faq-cocon__cta--static">
					<?php echo esc_html($cta_text); ?>
				</span>
			</div>
		<?php endif; ?>

		<div class="wpcursor-faq-cocon__grid">
			<?php for ($i = 0; $i < $half; $i++) : ?>
				<div class="wpcursor-faq-cocon__row">
					<?php if (isset($col_left[ $i ])) : ?>
						<?php $render_item($col_left[ $i ]); ?>
					<?php endif; ?>
					<?php if (isset($col_right[ $i ])) : ?>
						<?php $render_item($col_right[ $i ]); ?>
					<?php endif; ?>
				</div>
			<?php endfor; ?>
		</div>
	</div>

	<?php if ($custom_html !== '') : ?>
		<div class="wpcursor-faq-cocon__custom-html">
			<?php echo wp_kses_post($custom_html); ?>
		</div>
	<?php endif; ?>
</section>
<?php if ($custom_css !== '') : ?>
	<style id="<?php echo esc_attr($instance_id . '-custom-css'); ?>">
		<?php echo esc_html($custom_css); ?>
	</style>
<?php endif; ?>
