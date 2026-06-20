<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Page d’administration : composants, usages dans le contenu, CHANGELOG.
 */
final class WPCursor_Admin {

	public static function init(): void {
		add_action('admin_menu', [self::class, 'register_menu']);
		add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);
		add_action('admin_init', [self::class, 'handle_presets_post']);
	}

	public static function register_menu(): void {
		add_menu_page(
			__('WP Cursor', 'wpcursor'),
			__('WP Cursor', 'wpcursor'),
			WPCURSOR_CAP,
			'wpcursor',
			[self::class, 'render_page'],
			'dashicons-editor-code',
			58
		);
	}

	public static function render_page(): void {
		if (! current_user_can(WPCURSOR_CAP)) {
			return;
		}

		$tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'components';
		if (! in_array($tab, ['components', 'catalog', 'history', 'usage', 'presets', 'preview', 'generator', 'diagnostic', 'changelog', 'operations', 'theme'], true)) {
			$tab = 'components';
		}

		$base_url = admin_url('admin.php?page=wpcursor');
		?>
		<div class="wrap wpcursor-admin">
			<h1><?php echo esc_html__('WP Cursor', 'wpcursor'); ?></h1>
			<p class="description">
				<?php esc_html_e('Composants PHP versionnés et shortcodes. Tu peux en ajouter autant que nécessaire : chaque fichier dans components/ correspond à un bloc utilisable partout dans Divi (module Code, Texte, en-têtes de section, etc.).', 'wpcursor'); ?>
			</p>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_url($base_url . '&tab=components'); ?>" class="nav-tab <?php echo $tab === 'components' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Composants', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=catalog'); ?>" class="nav-tab <?php echo $tab === 'catalog' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Liste complète', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=preview'); ?>" class="nav-tab <?php echo $tab === 'preview' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Prévisualiser', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=generator'); ?>" class="nav-tab <?php echo $tab === 'generator' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Générateur shortcode', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=presets'); ?>" class="nav-tab <?php echo $tab === 'presets' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Répertoire shortcodes', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=history'); ?>" class="nav-tab <?php echo $tab === 'history' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Historique local', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=operations'); ?>" class="nav-tab <?php echo $tab === 'operations' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Opérations', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=usage'); ?>" class="nav-tab <?php echo $tab === 'usage' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Shortcodes', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=theme'); ?>" class="nav-tab <?php echo $tab === 'theme' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Thème enfant', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=diagnostic'); ?>" class="nav-tab <?php echo $tab === 'diagnostic' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Diagnostic', 'wpcursor'); ?>
				</a>
				<a href="<?php echo esc_url($base_url . '&tab=changelog'); ?>" class="nav-tab <?php echo $tab === 'changelog' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e('Journal de log', 'wpcursor'); ?>
				</a>
			</h2>

			<?php
			switch ($tab) {
				case 'history':
					self::render_tab_history();
					break;
				case 'catalog':
					self::render_tab_catalog();
					break;
				case 'usage':
					self::render_tab_usage();
					break;
				case 'preview':
					self::render_tab_preview();
					break;
				case 'generator':
					self::render_tab_generator();
					break;
				case 'presets':
					self::render_tab_presets();
					break;
				case 'diagnostic':
					self::render_tab_diagnostic();
					break;
				case 'changelog':
					self::render_tab_changelog();
					break;
				case 'operations':
					self::render_tab_operations();
					break;
				case 'theme':
					self::render_tab_theme();
					break;
				default:
					self::render_tab_components();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Guide d’installation (SSH + Cursor) affiché sur l’onglet principal Composants.
	 */
	private static function render_ssh_cursor_onboarding(): void {
		$config_example = <<<'TXT'
Host monserveurxxxx
    HostName 51.xxx.xxx.xxx
    User username
    Port 22
TXT;
		?>
		<details class="wpcursor-onboarding-details">
			<summary class="wpcursor-onboarding-summary" role="button">
				<span class="dashicons dashicons-admin-tools" aria-hidden="true"></span>
				<span class="wpcursor-onboarding-summary-label"><?php esc_html_e('Installation : travailler sur le serveur avec Cursor (SSH)', 'wpcursor'); ?></span>
				<span class="wpcursor-onboarding-chevron" aria-hidden="true"></span>
				<span class="wpcursor-onboarding-hint"><?php esc_html_e('cliquer pour ouvrir', 'wpcursor'); ?></span>
			</summary>
			<div class="wpcursor-onboarding">
				<p class="description" style="margin-top:0;">
					<?php esc_html_e('Le chat Cursor ne s’intègre pas dans WordPress : l’édition et les demandes à l’IA se font depuis Cursor sur ton PC, connecté au serveur en SSH.', 'wpcursor'); ?>
				</p>
				<ol class="wpcursor-onboarding-steps">
				<li>
					<?php esc_html_e('Crée un accès SSH sur ton serveur ou ton hébergement (utilisateur, mot de passe ou clé, port éventuel — souvent 22).', 'wpcursor'); ?>
				</li>
				<li>
					<?php esc_html_e('Sur ton PC, crée ou modifie le fichier de config SSH :', 'wpcursor'); ?>
					<code class="wpcursor-code-inline">~/.ssh/config</code>
					<?php esc_html_e('(Windows : utilisateur → .ssh → fichier « config » sans extension) avec par exemple :', 'wpcursor'); ?>
					<pre class="wpcursor-onboarding-pre"><?php echo esc_html($config_example); ?></pre>
				</li>
				<li>
					<?php esc_html_e('Dans le même dossier (.ssh), crée un fichier texte (.txt) qui reprend les mêmes infos (hôte, IP, utilisateur, port) pour t’en servir de référence lors de la connexion depuis Cursor.', 'wpcursor'); ?>
				</li>
				<li>
					<?php esc_html_e('Télécharge et installe Cursor pour Windows / macOS / Linux, puis installe l’extension « Remote - SSH » (ou équivalent Remote SSH dans Cursor).', 'wpcursor'); ?>
				</li>
				<li>
					<?php esc_html_e('Relance Cursor si besoin, ouvre la connexion SSH, choisis ta configuration ou ton fichier texte selon ce que propose l’interface, puis connecte-toi : tu navigues alors dans les fichiers du serveur comme en local.', 'wpcursor'); ?>
				</li>
				<li>
					<?php esc_html_e('Ouvre le dossier du site (souvent le répertoire contenant wp-content) et demande à Cursor de lire la documentation du plugin :', 'wpcursor'); ?>
					<code>wp-content/plugins/wpcursor/</code>
					<?php esc_html_e('et', 'wpcursor'); ?>
					<code>REPO/WP_CURSOR_PLUGIN.txt</code>
					<?php esc_html_e('à la racine du projet si présent, pour respecter les règles et conventions du projet.', 'wpcursor'); ?>
				</li>
				</ol>
			</div>
		</details>
		<?php
	}

	private static function render_shortcode_copy_cell(string $slug): void {
		$shortcode = '[wpcursor name="' . $slug . '"]';
		?>
		<code
			class="wpcursor-shortcode-copy"
			role="button"
			tabindex="0"
			title="<?php esc_attr_e('Cliquer pour copier le shortcode', 'wpcursor'); ?>"
			data-shortcode="<?php echo esc_attr($shortcode); ?>"
		><?php echo esc_html($shortcode); ?></code>
		<?php
	}

	private static function render_tab_components(): void {
		$inv = WPCursor_Component_Loader::inventory();
		$group = [];
		foreach ($inv as $row) {
			$slug = $row['slug'];
			if (! isset($group[ $slug ])) {
				$group[ $slug ] = ['types' => [], 'mtime' => 0, 'paths' => []];
			}
			$group[ $slug ]['types'][] = $row['type'];
			$group[ $slug ]['paths'][] = $row['path'];
			$p = WPCURSOR_PATH . ltrim($row['path'], '/');
			if (is_readable($p)) {
				$group[ $slug ]['mtime'] = max($group[ $slug ]['mtime'], filemtime($p));
			}
		}
		ksort($group);

		$cached_usage = get_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
		$usage_scan   = is_array($cached_usage) ? $cached_usage : ['rows' => []];
		$usage_keys   = WPCursor_Usage_Scan::usage_attribute_keys_by_component($usage_scan);
		$labels       = get_option('wpcursor_component_labels', []);
		if (! is_array($labels)) {
			$labels = [];
		}
		$labels = array_filter($labels, 'is_string');
		?>
		<h3><?php esc_html_e('Fichiers disponibles', 'wpcursor'); ?></h3>
		<p class="description"><?php esc_html_e('Cliquez sur un shortcode (colonne Shortcode) pour le copier.', 'wpcursor'); ?></p>
		<div class="wpcursor-info-box">
			<p class="wpcursor-info-title" style="margin:0 0 6px;">
				<span class="dashicons dashicons-info" aria-hidden="true"></span>
				<strong><?php esc_html_e('Formats pris en charge', 'wpcursor'); ?></strong>
			</p>
			<p style="margin:0;">
				<?php esc_html_e('Format legacy : components/slug.php — format dossier : components/slug/component.php (+ component.json optionnel, style.css / script.js). Contrat / usage : component.json + analyse des shortcodes du dernier scan (onglet Shortcodes — cache 5 min).', 'wpcursor'); ?>
			</p>
		</div>
		<?php if ($group === []) : ?>
			<p><?php esc_html_e('Aucun composant dans components/.', 'wpcursor'); ?></p>
		<?php else : ?>
			<p>
				<label for="wpcursor-components-search" style="display:inline-block;min-width:170px;"><strong><?php esc_html_e('Recherche composant', 'wpcursor'); ?></strong></label>
				<input
					type="search"
					id="wpcursor-components-search"
					class="regular-text wpcursor-component-search"
					placeholder="<?php esc_attr_e('Slug, nom affiché ou libellé…', 'wpcursor'); ?>"
					autocomplete="off"
				/>
			</p>
			<table class="widefat striped wpcursor-components-table">
				<thead>
					<tr>
						<th><?php esc_html_e('Composant', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Type', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Contrat / usage', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Shortcode', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Prévisualiser', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Dernière modification', 'wpcursor'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($group as $slug => $info) : ?>
						<?php
						$uk            = $usage_keys[ $slug ] ?? [];
						$u_errs        = WPCursor_Component_Schema::validate_usage_keys($slug, $uk);
						$status        = WPCursor_Component_Schema::admin_status_label($slug, $u_errs);
						$manifest_line = WPCursor_Component_Schema::admin_manifest_line($slug, (int) $info['mtime']);
						$display_name  = isset($labels[ $slug ]) ? (string) $labels[ $slug ] : '';
						$search_blob   = strtolower(
							$slug . ' ' . $display_name . ' ' . $manifest_line . ' ' . $status . ' ' . implode(' ', array_unique($info['types']))
						);
						?>
						<tr class="wpcursor-component-row" data-search="<?php echo esc_attr($search_blob); ?>">
							<td>
								<code><?php echo esc_html($slug); ?></code>
								<?php
								if ($manifest_line !== '') :
									?>
									<br /><span class="description"><?php echo esc_html($manifest_line); ?></span>
								<?php endif; ?>
								<?php if ($display_name !== '' && $display_name !== $slug) : ?>
									<br /><span class="description"><?php echo esc_html(sprintf(/* translators: %s: display label */ __('Nom affiché : %s', 'wpcursor'), $display_name)); ?></span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html(implode(' + ', array_unique($info['types']))); ?></td>
							<td><?php echo esc_html($status); ?></td>
							<td>
								<?php self::render_shortcode_copy_cell($slug); ?>
								<br />
								<a href="<?php echo esc_url(admin_url('admin.php?page=wpcursor&tab=generator&component=' . rawurlencode($slug))); ?>">
									<?php esc_html_e('Générer →', 'wpcursor'); ?>
								</a>
							</td>
							<td>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpcursor&tab=preview&component=' . rawurlencode($slug)), 'wpcursor_preview')); ?>">
									<?php esc_html_e('Ouvrir', 'wpcursor'); ?>
								</a>
							</td>
							<td>
								<?php
								echo $info['mtime'] > 0
									? esc_html(
										wp_date(
											get_option('date_format') . ' ' . get_option('time_format'),
											$info['mtime']
										)
									)
									: '—';
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="wpcursor-warning-box">
				<div class="wpcursor-warning-title">
					<span class="dashicons dashicons-warning" aria-hidden="true"></span>
					<strong><?php esc_html_e('Attributs optionnels', 'wpcursor'); ?></strong>
				</div>
				<p style="margin:6px 0 0;">
					<code>[wpcursor name="slug" cle="valeur"]</code>
					<?php esc_html_e('(clés alphanum + tirets ; le texte est filtré par WordPress pour en retirer le code indésirable ; pour les clés *_url, url, href ou link, la valeur est vérifiée comme URL autorisée).', 'wpcursor'); ?>
				</p>
			</div>
		<?php endif; ?>
		<?php self::render_ssh_cursor_onboarding(); ?>
		<?php
	}

	private static function render_tab_catalog(): void {
		$inv   = WPCursor_Component_Loader::inventory();
		$group = [];

		foreach ($inv as $row) {
			$slug = (string) $row['slug'];
			if ($slug === '') {
				continue;
			}
			if (! isset($group[ $slug ])) {
				$group[ $slug ] = [
					'types' => [],
					'mtime' => 0,
				];
			}

			$group[ $slug ]['types'][] = (string) $row['type'];
			$p                         = WPCURSOR_PATH . ltrim((string) $row['path'], '/');
			if (is_readable($p)) {
				$group[ $slug ]['mtime'] = max((int) $group[ $slug ]['mtime'], (int) filemtime($p));
			}
		}

		ksort($group);
		$labels = get_option('wpcursor_component_labels', []);
		if (! is_array($labels)) {
			$labels = [];
		}
		$labels = array_filter($labels, 'is_string');

		$saved_notice = false;
		$saved_slug   = '';
		$saved_label  = '';
		if (isset($_POST['wpcursor_save_label_single']) && check_admin_referer('wpcursor_save_component_label_single')) {
			$slug = isset($_POST['wpcursor_component_slug']) ? sanitize_key(wp_unslash($_POST['wpcursor_component_slug'])) : '';
			$name = isset($_POST['wpcursor_component_label']) ? sanitize_text_field(wp_unslash($_POST['wpcursor_component_label'])) : '';
			$name = trim($name);

			if ($slug !== '' && isset($group[ $slug ])) {
				if ($name === '') {
					unset($labels[ $slug ]);
				} else {
					$labels[ $slug ] = mb_substr($name, 0, 80);
				}
				update_option('wpcursor_component_labels', $labels, false);
				$saved_notice = true;
				$saved_slug   = $slug;
				$saved_label  = isset($labels[ $slug ]) ? (string) $labels[ $slug ] : '';
			}
		}
		?>
		<h3><?php esc_html_e('Tous les composants créés', 'wpcursor'); ?></h3>
		<p class="description">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: number of components */
					__('%d composant(s) détecté(s) dans components/. Cliquez sur un shortcode pour le copier. Utilise "Visualiser" pour la prévisualisation.', 'wpcursor'),
					count($group)
				)
			);
			?>
		</p>
		<div class="wpcursor-info-box" style="margin-bottom:16px;">
			<p class="wpcursor-info-title" style="margin:0 0 6px;">
				<span class="dashicons dashicons-info" aria-hidden="true"></span>
				<strong><?php esc_html_e('Slug vs nom affiché', 'wpcursor'); ?></strong>
			</p>
			<p style="margin:0;">
				<?php esc_html_e('La colonne « Slug » et le shortcode utilisent toujours l’identifiant du dossier (ex. mission-liste). Le « Nom affiché » sert uniquement à vous repérer ici — il ne change pas Divi ni les pages déjà publiées. Pour utiliser accueil-missions dans le shortcode, il faudrait renommer le dossier components/ et mettre à jour toutes les pages (opération avancée).', 'wpcursor'); ?>
			</p>
		</div>
		<?php if ($saved_notice) : ?>
			<div class="notice notice-success inline">
				<p>
					<?php
					if ($saved_label !== '' && $saved_label !== $saved_slug) {
						echo esc_html(
							sprintf(
								/* translators: 1: display label, 2: slug */
								__('Nom affiché enregistré : « %1$s » (slug inchangé : %2$s).', 'wpcursor'),
								$saved_label,
								$saved_slug
							)
						);
					} else {
						echo esc_html(sprintf(__('Nom affiché enregistré pour le slug « %s ».', 'wpcursor'), $saved_slug));
					}
					?>
					<br />
					<code class="wpcursor-code-inline"><?php echo esc_html('[wpcursor name="' . $saved_slug . '"]'); ?></code>
					<?php esc_html_e('— ce shortcode sur le site ne change pas.', 'wpcursor'); ?>
				</p>
			</div>
		<?php endif; ?>
		<?php if ($group === []) : ?>
			<p><?php esc_html_e('Aucun composant dans components/.', 'wpcursor'); ?></p>
		<?php else : ?>
			<p>
				<label for="wpcursor-catalog-search" style="display:inline-block;min-width:170px;"><strong><?php esc_html_e('Recherche composant', 'wpcursor'); ?></strong></label>
				<input
					type="search"
					id="wpcursor-catalog-search"
					class="regular-text wpcursor-component-search"
					placeholder="<?php esc_attr_e('Nom affiché ou slug…', 'wpcursor'); ?>"
					autocomplete="off"
				/>
			</p>
			<table class="widefat striped wpcursor-catalog-table">
				<thead>
					<tr>
						<th><?php esc_html_e('Slug (technique)', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Nom affiché (admin)', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Type', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Shortcode', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Visualiser', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Dernière modification', 'wpcursor'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($group as $slug => $info) : ?>
						<?php $display_name = isset($labels[ $slug ]) ? (string) $labels[ $slug ] : ''; ?>
						<tr class="wpcursor-catalog-row" data-search="<?php echo esc_attr(strtolower($slug . ' ' . $display_name)); ?>">
							<td>
								<code><?php echo esc_html($slug); ?></code>
								<?php if ($display_name !== '' && $display_name !== $slug) : ?>
									<br /><span class="description"><?php esc_html_e('Dossier :', 'wpcursor'); ?> <code>components/<?php echo esc_html($slug); ?>/</code></span>
								<?php endif; ?>
							</td>
							<td>
								<form method="post" action="" class="wpcursor-inline-label-form">
									<?php wp_nonce_field('wpcursor_save_component_label_single'); ?>
									<input type="hidden" name="wpcursor_component_slug" value="<?php echo esc_attr($slug); ?>" />
									<div class="wpcursor-label-view">
										<span><?php echo $display_name !== '' ? esc_html($display_name) : '—'; ?></span>
										<button type="button" class="button button-small wpcursor-label-edit-toggle"><?php esc_html_e('Modifier', 'wpcursor'); ?></button>
									</div>
									<div class="wpcursor-label-edit" style="display:none;">
										<input
											type="text"
											class="regular-text"
											name="wpcursor_component_label"
											value="<?php echo esc_attr($display_name); ?>"
											placeholder="<?php esc_attr_e('Libellé admin seulement (pas le slug)', 'wpcursor'); ?>"
											maxlength="80"
										/>
										<button type="submit" name="wpcursor_save_label_single" class="button button-primary button-small" value="1">
											<?php esc_html_e('Enregistrer', 'wpcursor'); ?>
										</button>
										<button type="button" class="button button-small wpcursor-label-cancel"><?php esc_html_e('Annuler', 'wpcursor'); ?></button>
									</div>
								</form>
							</td>
							<td><?php echo esc_html(implode(' + ', array_unique($info['types']))); ?></td>
							<td><?php self::render_shortcode_copy_cell($slug); ?></td>
							<td>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpcursor&tab=preview&component=' . rawurlencode($slug)), 'wpcursor_preview')); ?>">
									<?php esc_html_e('Visualiser', 'wpcursor'); ?>
								</a>
							</td>
							<td>
								<?php
								echo (int) $info['mtime'] > 0
									? esc_html(
										wp_date(
											get_option('date_format') . ' ' . get_option('time_format'),
											(int) $info['mtime']
										)
									)
									: '—';
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php
	}

	private static function render_tab_history(): void {
		WPCursor_Repo_History::capture_changes();
		$summary = WPCursor_Repo_History::admin_summary();

		$rolled_back = false;
		if (isset($_POST['wpcursor_history_rollback']) && check_admin_referer('wpcursor_history_rollback')) {
			$event_id    = isset($_POST['wpcursor_event_id']) ? sanitize_text_field(wp_unslash($_POST['wpcursor_event_id'])) : '';
			$rolled_back = WPCursor_Repo_History::rollback_event($event_id);
		}

		$events = WPCursor_Repo_History::get_events();
		usort(
			$events,
			static function ($a, $b) {
				$ta = isset($a['ts']) ? (int) $a['ts'] : 0;
				$tb = isset($b['ts']) ? (int) $b['ts'] : 0;
				return $tb <=> $ta;
			}
		);
		?>
		<h3><?php esc_html_e('Historique local type Git', 'wpcursor'); ?></h3>
		<div class="wpcursor-info-box">
			<p class="wpcursor-info-title" style="margin:0 0 6px;">
				<span class="dashicons dashicons-info" aria-hidden="true"></span>
				<strong><?php esc_html_e('Mode de fonctionnement', 'wpcursor'); ?></strong>
			</p>
			<p style="margin:0;">
				<?php esc_html_e('Le plugin crée automatiquement REPO/wpcursor-history, surveille les changements dans components/ et enregistre des snapshots. Tu peux revenir en arrière ligne par ligne avec le bouton Rollback.', 'wpcursor'); ?>
			</p>
		</div>
		<div class="wpcursor-info-box">
			<p class="wpcursor-info-title" style="margin:0 0 6px;">
				<span class="dashicons dashicons-database" aria-hidden="true"></span>
				<strong><?php esc_html_e('Chemins et sauvegardes', 'wpcursor'); ?></strong>
			</p>
			<p style="margin:0 0 6px;"><strong><?php esc_html_e('REPO plugin :', 'wpcursor'); ?></strong> <code><?php echo esc_html($summary['repo_root']); ?></code></p>
			<p style="margin:0 0 6px;"><strong><?php esc_html_e('Historique :', 'wpcursor'); ?></strong> <code><?php echo esc_html($summary['history_dir']); ?></code></p>
			<p style="margin:0 0 6px;"><strong><?php esc_html_e('Backups plugin :', 'wpcursor'); ?></strong> <code><?php echo esc_html($summary['plugin_backups_dir']); ?></code></p>
			<p style="margin:0 0 6px;"><strong><?php esc_html_e('Backups thème :', 'wpcursor'); ?></strong> <code><?php echo esc_html($summary['theme_backups_dir']); ?></code></p>
			<p style="margin:0;">
				<strong><?php esc_html_e('Derniers backups :', 'wpcursor'); ?></strong>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: plugin stamp, 2: theme stamp */
						__('plugin=%1$s, thème=%2$s', 'wpcursor'),
						$summary['latest_plugin_backup'] !== '' ? $summary['latest_plugin_backup'] : '—',
						$summary['latest_theme_backup'] !== '' ? $summary['latest_theme_backup'] : '—'
					)
				);
				?>
			</p>
		</div>
		<?php if ($rolled_back) : ?>
			<div class="notice notice-success inline"><p><?php esc_html_e('Rollback appliqué avec succès.', 'wpcursor'); ?></p></div>
		<?php endif; ?>
		<?php if ($events === []) : ?>
			<p><?php esc_html_e('Aucun changement détecté pour le moment.', 'wpcursor'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Date', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Action', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Composant', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Fichier', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Hash', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Rollback', 'wpcursor'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($events as $e) : ?>
						<?php
						$action = isset($e['action']) ? (string) $e['action'] : '';
						$hash   = isset($e['hash']) ? (string) $e['hash'] : '';
						$label  = $action === 'created' ? 'créé' : ($action === 'updated' ? 'modifié' : 'supprimé');
						?>
						<tr>
							<td>
								<?php
								echo esc_html(
									wp_date(
										get_option('date_format') . ' ' . get_option('time_format'),
										isset($e['ts']) ? (int) $e['ts'] : 0
									)
								);
								?>
							</td>
							<td><?php echo esc_html($label); ?></td>
							<td><code><?php echo esc_html(isset($e['slug']) ? (string) $e['slug'] : ''); ?></code></td>
							<td><code><?php echo esc_html(isset($e['path']) ? (string) $e['path'] : ''); ?></code></td>
							<td><code><?php echo esc_html($hash !== '' ? substr($hash, 0, 12) : '—'); ?></code></td>
							<td>
								<?php if (! empty($e['snapshot'])) : ?>
									<form method="post" action="">
										<?php wp_nonce_field('wpcursor_history_rollback'); ?>
										<input type="hidden" name="wpcursor_event_id" value="<?php echo esc_attr(isset($e['id']) ? (string) $e['id'] : ''); ?>" />
										<button
											type="submit"
											name="wpcursor_history_rollback"
											class="button button-small"
											value="1"
											onclick="return confirm('<?php echo esc_js(__('Confirmer le rollback de cette version ?', 'wpcursor')); ?>');"
										>
											<?php esc_html_e('Rollback', 'wpcursor'); ?>
										</button>
									</form>
								<?php else : ?>
									<span class="description">—</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php
	}

	private static function render_tab_usage(): void {
		if (isset($_POST['wpcursor_scan']) && check_admin_referer('wpcursor_scan_usage')) {
			delete_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
		}

		$cached = get_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
		$run    = isset($_POST['wpcursor_scan']) || $cached === false;

		if ($run) {
			$cached = WPCursor_Usage_Scan::scan_detailed();
			set_transient(WPCURSOR_USAGE_SCAN_TRANSIENT, $cached, 5 * MINUTE_IN_SECONDS);
		}

		$results = is_array($cached) ? $cached : ['rows' => [], 'meta_hits' => 0, 'total_rows' => 0, 'limit' => null, 'offset' => 0];
		?>
		<h3><?php esc_html_e('Où le shortcode apparaît', 'wpcursor'); ?></h3>
		<p class="description">
			<?php esc_html_e('Recherche dans post_content et postmeta. Composants extraits du shortcode. Cache 5 minutes (invalidé automatiquement après enregistrement ou mise à jour de contenu / metas). Au plus 200 entrées affichées par défaut (voir aussi REST pagination). Bouton « Copier contexte Cursor ».', 'wpcursor'); ?>
		</p>

		<form method="post" action="">
			<?php wp_nonce_field('wpcursor_scan_usage'); ?>
			<p>
				<button type="submit" name="wpcursor_scan" class="button button-primary" value="1">
					<?php esc_html_e('Analyser / actualiser', 'wpcursor'); ?>
				</button>
			</p>
		</form>

		<?php if (! empty($results['rows'])) : ?>
			<table class="widefat striped wpcursor-usage-table">
				<thead>
					<tr>
						<th><?php esc_html_e('ID', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Titre', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Type', 'wpcursor'); ?></th>
						<th><?php esc_html_e('État', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Source', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Composants', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Liens', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Cursor', 'wpcursor'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($results['rows'] as $p) : ?>
						<tr>
							<td><?php echo (int) $p['post_id']; ?></td>
							<td><?php echo esc_html($p['title']); ?></td>
							<td><?php echo esc_html($p['type']); ?></td>
							<td><?php echo esc_html($p['status']); ?></td>
							<td><?php echo esc_html(implode(' + ', $p['sources'])); ?></td>
							<td><code><?php echo esc_html(implode(', ', $p['components'])); ?></code></td>
							<td>
								<?php if (! empty($p['edit_link'])) : ?>
									<a href="<?php echo esc_url($p['edit_link']); ?>"><?php esc_html_e('Éditer', 'wpcursor'); ?></a>
								<?php endif; ?>
								<?php if (! empty($p['permalink'])) : ?>
									<br /><a href="<?php echo esc_url($p['permalink']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Voir', 'wpcursor'); ?></a>
								<?php endif; ?>
							</td>
							<td>
								<button type="button" class="button wpcursor-copy-ctx" data-context="<?php echo esc_attr(wp_json_encode(self::build_cursor_context_payload($p))); ?>">
									<?php esc_html_e('Copier contexte', 'wpcursor'); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: meta hit count, 2: rows shown, 3: total candidates after sort */
						__('Meta contenant le shortcode (indicatif global) : %1$d — affichées : %2$d sur %3$d (tri par date de modification ; découverte meta DISTINCT limitée à %4$d posts).', 'wpcursor'),
						(int) $results['meta_hits'],
						count($results['rows']),
						isset($results['total_rows']) ? (int) $results['total_rows'] : count($results['rows']),
						isset($results['meta_distinct_cap']) ? (int) $results['meta_distinct_cap'] : WPCursor_Usage_Scan::META_DISTINCT_POST_CAP
					)
				);
				?>
			</p>
		<?php else : ?>
			<p><?php esc_html_e('Aucune occurrence trouvée. Utilise [wpcursor name="…"] dans un module Divi.', 'wpcursor'); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Prévisualisation admin only : nonce, capability manage_wpcursor, pas d’accès public.
	 */
	private static function render_tab_preview(): void {
		$inv      = WPCursor_Component_Loader::inventory();
		$slugs    = array_values(array_unique(array_column($inv, 'slug')));
		sort($slugs, SORT_STRING);

		$base_url = admin_url('admin.php?page=wpcursor&tab=preview');

		$component = '';
		$props_txt = '';
		$show      = false;

		if (isset($_POST['wpcursor_preview_submit'], $_POST['_wpnonce'])
			&& wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpcursor_preview_form')) {
			$component = isset($_POST['wpcursor_preview_component'])
				? sanitize_key(wp_unslash($_POST['wpcursor_preview_component']))
				: '';
			$props_txt = isset($_POST['wpcursor_preview_props'])
				? wp_unslash((string) $_POST['wpcursor_preview_props'])
				: '';
			$show = $component !== '';
		} elseif (isset($_GET['component'], $_GET['_wpnonce'])) {
			$nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
			if (wp_verify_nonce($nonce, 'wpcursor_preview')) {
				$component = sanitize_key(wp_unslash($_GET['component']));
				if (isset($_GET['preview_props'])) {
					$props_txt = wp_unslash((string) $_GET['preview_props']);
				}
				$show = $component !== '';
			}
		}

		if ($show && ! in_array($component, $slugs, true)) {
			echo '<div class="notice notice-error"><p>' . esc_html__('Composant inconnu ou fichier absent.', 'wpcursor') . '</p></div>';
			$show = false;
		}

		$html_out           = '';
		$asset_lines       = [];
		$err_lines         = [];
		$schema_warn_lines = [];

		$parsed_props = [];
		if ($show) {
			$parsed_props = self::parse_preview_props($props_txt);

			global $wp_scripts, $wp_styles;
			$before_js = ( is_object($wp_scripts) && isset($wp_scripts->queue) ) ? $wp_scripts->queue : [];
			$before_css = ( is_object($wp_styles) && isset($wp_styles->queue) ) ? $wp_styles->queue : [];

			$errs = [];
			set_error_handler(
				static function ( int $errno, string $errstr, string $errfile, int $errline ) use ( &$errs ): bool {
					if (! in_array($errno, [ E_WARNING, E_NOTICE, E_USER_NOTICE, E_USER_WARNING ], true)) {
						return false;
					}
					$errs[] = $errstr . ' (' . basename($errfile) . ':' . $errline . ')';
					return true;
				}
			);

			$html_out = WPCursor_Component_Loader::render($component, $parsed_props, WPCURSOR_RUNTIME_ADMIN_PREVIEW);

			restore_error_handler();

			$err_lines = $errs;

			$schema_warn_lines = WPCursor_Component_Loader::consume_last_render_schema_issues();

			if (is_object($wp_scripts) && isset($wp_scripts->queue)) {
				foreach (array_diff($wp_scripts->queue, $before_js) as $h) {
					if (strpos((string) $h, 'wpcursor-cpt-') === 0 && isset($wp_scripts->registered[ $h ])) {
						$o                                = $wp_scripts->registered[ $h ];
						$asset_lines[]                    = 'script: ' . $h . ' → ' . ( isset($o->src) ? $o->src : '' );
					}
				}
			}
			if (is_object($wp_styles) && isset($wp_styles->queue)) {
				foreach (array_diff($wp_styles->queue, $before_css) as $h) {
					if (strpos((string) $h, 'wpcursor-cpt-') === 0 && isset($wp_styles->registered[ $h ])) {
						$o                                = $wp_styles->registered[ $h ];
						$asset_lines[]                    = 'style : ' . $h . ' → ' . ( isset($o->src) ? $o->src : '' );
					}
				}
			}
		}

		$asset_library = self::get_component_asset_library();
		?>
		<h3><?php esc_html_e('Prévisualiser un composant', 'wpcursor'); ?></h3>
		<p class="description">
			<?php esc_html_e('Réservé aux comptes avec manage_wpcursor. URL avec nonce : admin.php?page=wpcursor&tab=preview&component=slug&_wpnonce=… — aucun front public.', 'wpcursor'); ?>
		</p>
		<div class="wpcursor-info-box" style="margin-bottom:16px;">
			<p class="wpcursor-info-title" style="margin:0 0 6px;">
				<span class="dashicons dashicons-edit" aria-hidden="true"></span>
				<strong><?php esc_html_e('Pour Divi / clients : modifier les textes sans attributs compliqués', 'wpcursor'); ?></strong>
			</p>
			<p style="margin:0;">
				<?php esc_html_e('Dans un module Code Divi, utilise le format entre balises : une ligne par champ (title=, intro=, item1=…). Les fichiers divi-template.txt dans chaque composant contiennent un modèle à copier.', 'wpcursor'); ?>
			</p>
		</div>

		<div class="wpcursor-info-box" style="margin-bottom:18px;">
			<p class="wpcursor-info-title" style="margin:0 0 8px;">
				<span class="dashicons dashicons-media-code" aria-hidden="true"></span>
				<strong><?php esc_html_e('Librairie CSS / JS (par composant)', 'wpcursor'); ?></strong>
			</p>
			<p class="description" style="margin:0 0 10px;">
				<?php esc_html_e('Chaque composant peut avoir son propre style.css et script.js dans components/slug/. Ils sont chargés automatiquement uniquement quand le shortcode est affiché sur une page (pas besoin du style.css du thème).', 'wpcursor'); ?>
			</p>
			<?php if ($asset_library === []) : ?>
				<p><?php esc_html_e('Aucun composant détecté.', 'wpcursor'); ?></p>
			<?php else : ?>
				<table class="widefat striped wpcursor-asset-library-table">
					<thead>
						<tr>
							<th><?php esc_html_e('Composant', 'wpcursor'); ?></th>
							<th><?php esc_html_e('CSS', 'wpcursor'); ?></th>
							<th><?php esc_html_e('JS', 'wpcursor'); ?></th>
							<th><?php esc_html_e('Fichiers', 'wpcursor'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($asset_library as $row) : ?>
							<tr>
								<td><code><?php echo esc_html($row['slug']); ?></code></td>
								<td><?php echo $row['has_css'] ? '✓' : '—'; ?></td>
								<td><?php echo $row['has_js'] ? '✓' : '—'; ?></td>
								<td><code style="font-size:11px;"><?php echo esc_html($row['paths_label']); ?></code></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<form method="post" action="<?php echo esc_url($base_url); ?>" class="wpcursor-preview-form">
			<?php wp_nonce_field('wpcursor_preview_form'); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="wpcursor_preview_component"><?php esc_html_e('Composant', 'wpcursor'); ?></label></th>
					<td>
						<select name="wpcursor_preview_component" id="wpcursor_preview_component">
							<option value=""><?php esc_html_e('— Choisir —', 'wpcursor'); ?></option>
							<?php foreach ($slugs as $s) : ?>
								<option value="<?php echo esc_attr($s); ?>" <?php selected($component, $s); ?>><?php echo esc_html($s); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpcursor_preview_props"><?php esc_html_e('Options / propriétés', 'wpcursor'); ?></label>
					</th>
					<td>
						<textarea name="wpcursor_preview_props" id="wpcursor_preview_props" rows="5" class="large-text code" placeholder="variant=dark"><?php echo esc_textarea($props_txt); ?></textarea>
						<p class="description">
							<?php esc_html_e('Une paire clé=valeur par ligne, ou un objet JSON. Comme sur le site : filtre texte WordPress pour les chaînes, contrôle des adresses web pour les clés qui servent de lien (selon le nom de la clé).', 'wpcursor'); ?>
						</p>
					</td>
				</tr>
			</table>
			<p>
				<button type="submit" name="wpcursor_preview_submit" class="button button-primary" value="1">
					<?php esc_html_e('Afficher la prévisualisation', 'wpcursor'); ?>
				</button>
			</p>
		</form>

		<?php if ($show) : ?>
			<hr />
			<h4><?php esc_html_e('Options / propriétés utilisées pour ce rendu', 'wpcursor'); ?></h4>
			<p class="description"><?php esc_html_e('Valeurs réellement transmises au composant après nettoyage (même règles que décrites sous le formulaire).', 'wpcursor'); ?></p>
			<pre class="wpcursor-preview-props"><?php echo esc_html(wp_json_encode($parsed_props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>

			<?php if ($schema_warn_lines !== []) : ?>
				<div class="notice notice-warning"><p><strong><?php esc_html_e('Contrat component.json (aperçu)', 'wpcursor'); ?></strong></p>
					<ul>
						<?php foreach ($schema_warn_lines as $sw ) : ?>
							<li><?php echo esc_html($sw); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<h4><?php esc_html_e('Assets enregistrés pour ce rendu (handles wpcursor-cpt-*)', 'wpcursor'); ?></h4>
			<?php if ($asset_lines !== []) : ?>
				<ul class="ul-disc">
					<?php foreach ($asset_lines as $line) : ?>
						<li><code><?php echo esc_html($line); ?></code></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="description"><?php esc_html_e('Aucun style/script composant supplémentaire (pas de style.css / script.js ou déjà chargés).', 'wpcursor'); ?></p>
			<?php endif; ?>

			<?php if ($err_lines !== []) : ?>
				<div class="notice notice-warning"><p><strong><?php esc_html_e('Avertissements PHP (admin uniquement)', 'wpcursor'); ?></strong></p>
					<ul>
						<?php foreach ($err_lines as $e) : ?>
							<li><?php echo esc_html($e); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<h4><?php esc_html_e('Rendu HTML', 'wpcursor'); ?></h4>
			<div class="wpcursor-preview-sandbox" style="border:1px solid #ccd0d4;padding:12px;background:#fff;max-width:100%;overflow:auto;">
				<?php echo $html_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- prévisualisation du composant tel que rendu ; admins manage_wpcursor uniquement. ?>
			</div>

			<?php self::render_component_source_files($component); ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Inventaire des assets optionnels par slug (style.css / script.js).
	 *
	 * @return list<array{slug:string,has_css:bool,has_js:bool,paths_label:string}>
	 */
	private static function get_component_asset_library(): array {
		$slugs = [];
		foreach (WPCursor_Component_Loader::inventory() as $row) {
			$slug = (string) $row['slug'];
			if ($slug !== '') {
				$slugs[ $slug ] = true;
			}
		}
		ksort($slugs);

		$out = [];
		foreach (array_keys($slugs) as $slug) {
			$base     = trailingslashit(WPCURSOR_PATH . 'components/' . $slug);
			$has_css  = is_readable($base . 'style.css');
			$has_js   = is_readable($base . 'script.js');
			$paths    = [ 'component.php' ];
			if (is_readable($base . 'component.json')) {
				$paths[] = 'component.json';
			}
			if ($has_css) {
				$paths[] = 'style.css';
			}
			if ($has_js) {
				$paths[] = 'script.js';
			}
			$out[] = [
				'slug'        => $slug,
				'has_css'     => $has_css,
				'has_js'      => $has_js,
				'paths_label' => 'components/' . $slug . '/' . implode(', ', $paths),
			];
		}
		return $out;
	}

	/**
	 * Affiche le code source PHP / JSON / CSS / JS du composant (lecture seule).
	 */
	private static function render_component_source_files(string $slug): void {
		$slug = sanitize_key($slug);
		if ($slug === '') {
			return;
		}

		$resolved = WPCursor_Component_Loader::resolve_component_file($slug);
		$base     = trailingslashit(WPCURSOR_PATH . 'components/' . $slug);

		$candidates = [];
		if ($resolved !== null && is_readable($resolved['file'])) {
			$rel = 'components/' . $slug . '/component.php';
			if ($resolved['mode'] === 'legacy') {
				$rel = 'components/' . $slug . '.php';
			}
			$candidates[] = [
				'label' => $rel,
				'path'  => $resolved['file'],
			];
		}
		foreach (
			[
				'divi-template.txt' => $base . 'divi-template.txt',
				'component.json'    => $base . 'component.json',
				'style.css'         => $base . 'style.css',
				'script.js'         => $base . 'script.js',
			] as $name => $path
		) {
			if (is_readable($path)) {
				$label = 'components/' . $slug . '/' . $name;
				if ($name === 'component.json' || $name === 'style.css' || $name === 'script.js') {
					$candidates[] = [
						'label' => $label,
						'path'  => $path,
					];
				}
			}
		}

		?>
		<hr />
		<h4><?php esc_html_e('Code source du composant', 'wpcursor'); ?></h4>
		<p class="description">
			<?php esc_html_e('Fichiers lus sur le serveur (lecture seule). Le CSS est chargé sur le site via wp_enqueue_style quand le shortcode est rendu — pas besoin de le copier dans le style.css du thème.', 'wpcursor'); ?>
		</p>
		<?php
		if ($candidates === []) {
			echo '<p>' . esc_html__('Aucun fichier lisible pour ce composant.', 'wpcursor') . '</p>';
			return;
		}

		foreach ($candidates as $file) {
			$raw = file_get_contents($file['path']);
			if ($raw === false) {
				continue;
			}
			$size = filesize($file['path']);
			$mtime = filemtime($file['path']);
			?>
			<details class="wpcursor-source-details" style="margin:0 0 10px;border:1px solid #d7dde5;border-radius:8px;background:#fff;">
				<summary style="cursor:pointer;padding:10px 12px;font-weight:600;">
					<code><?php echo esc_html($file['label']); ?></code>
					<span class="description" style="font-weight:400;margin-left:8px;">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: file size, 2: formatted date */
								__('(%1$s — modifié le %2$s)', 'wpcursor'),
								size_format($size !== false ? (int) $size : 0),
								wp_date(get_option('date_format') . ' ' . get_option('time_format'), $mtime !== false ? (int) $mtime : 0)
							)
						);
						?>
					</span>
				</summary>
				<div style="padding:0 12px 12px;">
					<pre class="wpcursor-source-pre"><?php echo esc_html($raw); ?></pre>
				</div>
			</details>
			<?php
		}
	}

	/**
	 * Manifeste JSON pour le générateur admin (props + valeurs par défaut du modèle Divi).
	 *
	 * @return array<string, array{label:string,props:array<string,array{type:string,required:bool,values:list<string>,default:string}>,has_template:bool,preview_nonce:string}>
	 */
	private static function get_shortcode_generator_manifest(): array {
		return WPCursor_Generator_Manifest::build();
	}

	public static function handle_presets_post(): void {
		if (! is_admin() || ! current_user_can(WPCURSOR_CAP)) {
			return;
		}
		$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		if ($page !== 'wpcursor') {
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['wpcursor_preset_action'])) {
			return;
		}
		check_admin_referer('wpcursor_presets');

		$action = sanitize_key(wp_unslash($_POST['wpcursor_preset_action']));
		$redirect = admin_url('admin.php?page=wpcursor&tab=presets');

		if ($action === 'save') {
			$id     = isset($_POST['preset_id']) ? sanitize_key(wp_unslash($_POST['preset_id'])) : '';
			$slug   = isset($_POST['component_slug']) ? sanitize_key(wp_unslash($_POST['component_slug'])) : '';
			$label  = isset($_POST['preset_label']) ? wp_unslash($_POST['preset_label']) : '';
			$code   = isset($_POST['preset_shortcode']) ? wp_unslash($_POST['preset_shortcode']) : '';
			$notes  = isset($_POST['preset_notes']) ? wp_unslash($_POST['preset_notes']) : '';
			$saved  = WPCursor_Shortcode_Presets::save(
				$slug,
				$label,
				$code,
				$notes,
				$id !== '' ? $id : null
			);
			if ($saved) {
				$redirect = add_query_arg(
					['saved' => '1', 'component' => $slug],
					$redirect
				);
			} else {
				$redirect = add_query_arg('error', '1', $redirect);
			}
		} elseif ($action === 'delete') {
			$id = isset($_POST['preset_id']) ? sanitize_key(wp_unslash($_POST['preset_id'])) : '';
			if ($id !== '' && WPCursor_Shortcode_Presets::delete($id)) {
				$redirect = add_query_arg('deleted', '1', $redirect);
			} else {
				$redirect = add_query_arg('error', '1', $redirect);
			}
		}

		wp_safe_redirect($redirect);
		exit;
	}

	private static function render_tab_presets(): void {
		if (isset($_POST['wpcursor_rescan_index']) && check_admin_referer('wpcursor_presets_rescan')) {
			delete_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
		}

		$cached = get_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
		if (! is_array($cached) || empty($cached['rows'])) {
			$cached = WPCursor_Usage_Scan::scan_detailed(null);
			set_transient(WPCURSOR_USAGE_SCAN_TRANSIENT, $cached, 5 * MINUTE_IN_SECONDS);
		}

		$filter_slug = isset($_GET['component']) ? sanitize_key(wp_unslash($_GET['component'])) : '';
		$search_q    = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
		$edit_id     = isset($_GET['edit']) ? sanitize_key(wp_unslash($_GET['edit'])) : '';
		$inv_slugs   = array_values(array_unique(array_column(WPCursor_Component_Loader::inventory(), 'slug')));
		sort($inv_slugs, SORT_STRING);
		$labels      = get_option('wpcursor_component_labels', []);
		if (! is_array($labels)) {
			$labels = [];
		}
		$edit_row = $edit_id !== '' ? WPCursor_Shortcode_Presets::get($edit_id) : null;
		$base_url = admin_url('admin.php?page=wpcursor&tab=presets');

		$index    = WPCursor_Shortcode_Index::build($cached);
		$entries  = WPCursor_Shortcode_Index::filter($index, $search_q, $filter_slug);
		$on_site  = count(array_filter($index, static fn(array $e): bool => ! empty($e['on_site'])));
		$in_lib   = count(array_filter($index, static fn(array $e): bool => ! empty($e['in_library'])));

		if (isset($_GET['saved'])) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Variante enregistrée.', 'wpcursor') . '</p></div>';
		}
		if (isset($_GET['deleted'])) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Variante supprimée.', 'wpcursor') . '</p></div>';
		}
		if (isset($_GET['error'])) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Action impossible — vérifiez le nom et le shortcode.', 'wpcursor') . '</p></div>';
		}
		?>
		<h3><?php esc_html_e('Répertoire shortcodes', 'wpcursor'); ?></h3>
		<div class="wpcursor-info-box" style="margin-bottom:18px;">
			<p style="margin:0;">
				<?php esc_html_e('Retrouvez en un coup d’œil chaque shortcode ou variante : textes, composant de design, et pages où il est publié (liens Éditer / Voir). La recherche porte sur le nom, le composant, les textes et les titres de pages.', 'wpcursor'); ?>
			</p>
		</div>

		<p class="description" style="margin:0 0 12px;">
			<?php
			echo esc_html(sprintf(
				/* translators: 1: total entries, 2: on site, 3: in library */
				__('%1$d entrée(s) — %2$d sur le site — %3$d en bibliothèque', 'wpcursor'),
				count($index),
				$on_site,
				$in_lib
			));
			?>
		</p>

		<form method="get" class="wpcursor-presets-filter" style="margin:0 0 12px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
			<input type="hidden" name="page" value="wpcursor" />
			<input type="hidden" name="tab" value="presets" />
			<p style="margin:0;">
				<label for="wpcursor-repo-search"><strong><?php esc_html_e('Recherche rapide', 'wpcursor'); ?></strong></label><br />
				<input type="search" id="wpcursor-repo-search" name="q" class="regular-text" value="<?php echo esc_attr($search_q); ?>"
					placeholder="<?php esc_attr_e('Nom, texte, page, composant…', 'wpcursor'); ?>" />
			</p>
			<p style="margin:0;">
				<label for="wpcursor-presets-filter-slug"><?php esc_html_e('Composant', 'wpcursor'); ?></label><br />
				<select id="wpcursor-presets-filter-slug" name="component">
					<option value=""><?php esc_html_e('Tous', 'wpcursor'); ?></option>
					<?php
					foreach ($inv_slugs as $slug) {
						$lab = isset($labels[ $slug ]) && is_string($labels[ $slug ]) ? $labels[ $slug ] : $slug;
						printf(
							'<option value="%1$s" %2$s>%3$s (%1$s)</option>',
							esc_attr($slug),
							selected($filter_slug, $slug, false),
							esc_html($lab)
						);
					}
					?>
				</select>
			</p>
			<p style="margin:0;">
				<button type="submit" class="button button-primary"><?php esc_html_e('Filtrer', 'wpcursor'); ?></button>
				<a class="button" href="<?php echo esc_url($base_url); ?>"><?php esc_html_e('Réinitialiser', 'wpcursor'); ?></a>
			</p>
			<p style="margin:0;margin-left:auto;">
				<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=wpcursor&tab=generator')); ?>">
					<?php esc_html_e('+ Générateur', 'wpcursor'); ?>
				</a>
			</p>
		</form>

		<form method="post" style="margin:0 0 16px;">
			<?php wp_nonce_field('wpcursor_presets_rescan'); ?>
			<button type="submit" name="wpcursor_rescan_index" class="button" value="1">
				<?php esc_html_e('Actualiser les pages (scan site)', 'wpcursor'); ?>
			</button>
			<span class="description"><?php esc_html_e('Recalcule les liens vers les pages — cache 5 min sinon.', 'wpcursor'); ?></span>
		</form>

		<details class="wpcursor-presets-form-wrap" <?php echo $edit_row ? 'open' : ''; ?> style="margin:0 0 20px;padding:12px 14px;border:1px solid #d7dde5;border-radius:10px;background:#fbfcfe;">
			<summary style="cursor:pointer;font-weight:700;">
				<?php echo $edit_row ? esc_html__('Modifier la variante en bibliothèque', 'wpcursor') : esc_html__('Enregistrer une variante (manuel)', 'wpcursor'); ?>
			</summary>
			<form method="post" style="margin-top:12px;">
				<?php wp_nonce_field('wpcursor_presets'); ?>
				<input type="hidden" name="wpcursor_preset_action" value="save" />
				<?php if ($edit_row) : ?>
					<input type="hidden" name="preset_id" value="<?php echo esc_attr($edit_row['id']); ?>" />
				<?php endif; ?>
				<table class="form-table">
					<tr>
						<th><label for="wpcursor-preset-slug"><?php esc_html_e('Composant (design)', 'wpcursor'); ?></label></th>
						<td>
							<select name="component_slug" id="wpcursor-preset-slug" required>
								<?php
								foreach ($inv_slugs as $slug) {
									$sel = $edit_row ? $edit_row['component_slug'] : $filter_slug;
									printf('<option value="%1$s" %2$s>%1$s</option>', esc_attr($slug), selected($sel, $slug, false));
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="wpcursor-preset-label"><?php esc_html_e('Nom de la variante', 'wpcursor'); ?></label></th>
						<td>
							<input type="text" class="regular-text" name="preset_label" id="wpcursor-preset-label" required
								placeholder="<?php esc_attr_e('Ex. Pierre — page admission', 'wpcursor'); ?>"
								value="<?php echo $edit_row ? esc_attr($edit_row['label']) : ''; ?>" />
						</td>
					</tr>
					<tr>
						<th><label for="wpcursor-preset-shortcode"><?php esc_html_e('Shortcode', 'wpcursor'); ?></label></th>
						<td>
							<textarea name="preset_shortcode" id="wpcursor-preset-shortcode" class="large-text code" rows="8" required><?php
								echo $edit_row ? esc_textarea($edit_row['shortcode']) : '';
							?></textarea>
						</td>
					</tr>
					<tr>
						<th><label for="wpcursor-preset-notes"><?php esc_html_e('Note', 'wpcursor'); ?></label></th>
						<td>
							<input type="text" class="large-text" name="preset_notes" id="wpcursor-preset-notes"
								value="<?php echo $edit_row ? esc_attr($edit_row['notes']) : ''; ?>" />
						</td>
					</tr>
				</table>
				<p>
					<button type="submit" class="button button-primary"><?php esc_html_e('Enregistrer', 'wpcursor'); ?></button>
					<?php if ($edit_row) : ?>
						<a class="button" href="<?php echo esc_url($base_url); ?>"><?php esc_html_e('Annuler', 'wpcursor'); ?></a>
					<?php endif; ?>
				</p>
			</form>
		</details>

		<?php if ($entries === []) : ?>
			<p><?php esc_html_e('Aucun shortcode trouvé avec ces critères. Lancez un scan ou créez une variante dans le générateur.', 'wpcursor'); ?></p>
		<?php else : ?>
			<table class="widefat striped wpcursor-repo-table">
				<thead>
					<tr>
						<th><?php esc_html_e('Statut', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Nom / aperçu', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Composant', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Pages', 'wpcursor'); ?></th>
						<th><?php esc_html_e('Actions', 'wpcursor'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $entry) : ?>
						<tr>
							<td class="wpcursor-repo-status">
								<?php if (! empty($entry['in_library'])) : ?>
									<span class="wpcursor-badge wpcursor-badge--lib" title="<?php esc_attr_e('Enregistré en bibliothèque', 'wpcursor'); ?>"><?php esc_html_e('Bibliothèque', 'wpcursor'); ?></span>
								<?php endif; ?>
								<?php if (! empty($entry['on_site'])) : ?>
									<span class="wpcursor-badge wpcursor-badge--live" title="<?php esc_attr_e('Détecté sur le site', 'wpcursor'); ?>"><?php esc_html_e('Sur le site', 'wpcursor'); ?></span>
								<?php elseif (! empty($entry['in_library'])) : ?>
									<span class="wpcursor-badge wpcursor-badge--orphan" title="<?php esc_attr_e('Pas encore détecté sur une page', 'wpcursor'); ?>"><?php esc_html_e('Non publié', 'wpcursor'); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<strong><?php echo esc_html($entry['label']); ?></strong>
								<br /><code class="wpcursor-repo-summary"><?php echo esc_html($entry['props_summary']); ?></code>
							</td>
							<td><code><?php echo esc_html($entry['component_slug']); ?></code></td>
							<td class="wpcursor-repo-pages">
								<?php if (empty($entry['pages'])) : ?>
									<span class="description">—</span>
								<?php else : ?>
									<ul class="wpcursor-page-list">
										<?php foreach ($entry['pages'] as $page) : ?>
											<li>
												<span class="wpcursor-page-status wpcursor-page-status--<?php echo esc_attr($page['status']); ?>" title="<?php echo esc_attr($page['status']); ?>"></span>
												<strong><?php echo esc_html($page['title']); ?></strong>
												<span class="description">#<?php echo (int) $page['post_id']; ?> · <?php echo esc_html($page['type']); ?></span>
												<span class="wpcursor-page-links">
													<?php if ($page['edit_link'] !== '') : ?>
														<a href="<?php echo esc_url($page['edit_link']); ?>"><?php esc_html_e('Éditer', 'wpcursor'); ?></a>
													<?php endif; ?>
													<?php if ($page['permalink'] !== '') : ?>
														<a href="<?php echo esc_url($page['permalink']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Voir', 'wpcursor'); ?></a>
													<?php endif; ?>
												</span>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</td>
							<td class="wpcursor-repo-actions">
								<button type="button" class="button button-small wpcursor-shortcode-copy" data-shortcode="<?php echo esc_attr($entry['shortcode']); ?>">
									<?php esc_html_e('Copier', 'wpcursor'); ?>
								</button>
								<?php if ($entry['preset_id'] !== '') : ?>
									<a class="button button-small" href="<?php echo esc_url($base_url . '&edit=' . rawurlencode($entry['preset_id'])); ?>"><?php esc_html_e('Modifier', 'wpcursor'); ?></a>
									<a class="button button-small" href="<?php echo esc_url(admin_url('admin.php?page=wpcursor&tab=generator&component=' . rawurlencode($entry['component_slug']) . '&preset=' . rawurlencode($entry['preset_id']))); ?>"><?php esc_html_e('Générateur', 'wpcursor'); ?></a>
									<form method="post" style="display:inline;" onsubmit="return confirm('<?php echo esc_js(__('Supprimer cette variante de la bibliothèque ?', 'wpcursor')); ?>');">
										<?php wp_nonce_field('wpcursor_presets'); ?>
										<input type="hidden" name="wpcursor_preset_action" value="delete" />
										<input type="hidden" name="preset_id" value="<?php echo esc_attr($entry['preset_id']); ?>" />
										<button type="submit" class="button button-small button-link-delete"><?php esc_html_e('Suppr.', 'wpcursor'); ?></button>
									</form>
								<?php else : ?>
									<a class="button button-small" href="<?php echo esc_url($base_url . '&component=' . rawurlencode($entry['component_slug']) . '#wpcursor-preset-shortcode'); ?>"><?php esc_html_e('Enregistrer', 'wpcursor'); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php
	}

	/**
	 * Valeurs d’exemple extraites de components/{slug}/divi-template.txt.
	 *
	 * @return array<string, string>
	 */
	private static function get_divi_template_defaults(string $slug): array {
		return WPCursor_Component_Loader::get_divi_template_defaults($slug);
	}

	private static function render_tab_generator(): void {
		$inv        = WPCursor_Component_Loader::inventory();
		$slugs      = array_values(array_unique(array_column($inv, 'slug')));
		sort($slugs, SORT_STRING);
		$pref_slug  = isset($_GET['component']) ? sanitize_key(wp_unslash($_GET['component'])) : '';
		$pref_preset = isset($_GET['preset']) ? sanitize_key(wp_unslash($_GET['preset'])) : '';
		$load_preset = $pref_preset !== '' ? WPCursor_Shortcode_Presets::get($pref_preset) : null;
		if ($load_preset && $pref_slug === '') {
			$pref_slug = $load_preset['component_slug'];
		}
		$component_presets = $pref_slug !== '' ? WPCursor_Shortcode_Presets::for_component($pref_slug) : [];
		$load_props        = [];
		if ($load_preset) {
			$raw = $load_preset['shortcode'];
			if (preg_match('/\[wpcursor[^\]]*\](.*?)\[\/wpcursor\]/is', $raw, $m)) {
				$load_props = WPCursor_Component_Loader::parse_props_text(trim($m[1]));
			}
		}
		$base_url   = admin_url('admin.php?page=wpcursor&tab=generator');
		$preview_url = admin_url('admin.php?page=wpcursor&tab=preview');
		$presets_url = admin_url('admin.php?page=wpcursor&tab=presets');
		?>
		<h3><?php esc_html_e('Générateur de shortcode', 'wpcursor'); ?></h3>
		<p class="description">
			<?php esc_html_e('Choisissez un composant existant, remplissez les champs définis dans component.json (ou le modèle Divi), puis copiez le shortcode — sans créer de doublon dans components/.', 'wpcursor'); ?>
		</p>

		<div class="wpcursor-info-box" style="margin-bottom:18px;">
			<p class="wpcursor-info-title" style="margin:0 0 6px;">
				<span class="dashicons dashicons-admin-tools" aria-hidden="true"></span>
				<strong><?php esc_html_e('Un composant = une mise en forme', 'wpcursor'); ?></strong>
			</p>
			<p style="margin:0;">
				<?php esc_html_e('Les textes différents par page passent par les champs ci-dessous (format Divi recommandé). Pour une nouvelle mise en page, créez le composant dans Cursor puis revenez ici.', 'wpcursor'); ?>
			</p>
		</div>

		<?php if ($slugs === []) : ?>
			<p><?php esc_html_e('Aucun composant détecté dans components/.', 'wpcursor'); ?></p>
		<?php else : ?>
			<div class="wpcursor-generator-wrap">
				<table class="form-table wpcursor-generator-form">
					<tr>
						<th scope="row">
							<label for="wpcursor-gen-slug"><?php esc_html_e('Composant', 'wpcursor'); ?></label>
						</th>
						<td>
							<select id="wpcursor-gen-slug" class="regular-text">
								<option value=""><?php esc_html_e('— Choisir un composant —', 'wpcursor'); ?></option>
								<?php
								foreach ($slugs as $slug) {
									$pack  = WPCursor_Component_Schema::load_for_slug($slug);
									$label = $slug;
									if ($pack['state'] === WPCursor_Component_Schema::STATE_OK && is_array($pack['parsed']) && ! empty($pack['parsed']['label'])) {
										$label = (string) $pack['parsed']['label'];
									}
									printf(
										'<option value="%1$s"%3$s>%2$s (%1$s)</option>',
										esc_attr($slug),
										esc_html($label),
										selected($pref_slug, $slug, false)
									);
								}
								?>
							</select>
							<?php if ($component_presets !== []) : ?>
								<p class="description" style="margin-top:8px;">
									<label for="wpcursor-gen-load-preset"><?php esc_html_e('Variantes enregistrées pour ce composant', 'wpcursor'); ?></label><br />
									<select id="wpcursor-gen-load-preset" class="regular-text">
										<option value=""><?php esc_html_e('— Charger une variante —', 'wpcursor'); ?></option>
										<?php foreach ($component_presets as $pv) : ?>
											<option value="<?php echo esc_attr($pv['id']); ?>" <?php selected($pref_preset, $pv['id']); ?>>
												<?php echo esc_html($pv['label']); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<a href="<?php echo esc_url($presets_url . '&component=' . rawurlencode($pref_slug)); ?>" style="margin-left:8px;">
										<?php esc_html_e('Voir toutes →', 'wpcursor'); ?>
									</a>
								</p>
							<?php endif; ?>
							<?php if (is_readable(WPCURSOR_PATH . 'components/README.md')) : ?>
								<p class="description">
									<?php
									printf(
										/* translators: %s: path */
										esc_html__('Schéma des champs : components/{slug}/component.json — modèle client : divi-template.txt', 'wpcursor')
									);
									?>
								</p>
							<?php endif; ?>
						</td>
					</tr>
				</table>

				<div class="wpcursor-generator-mode-tabs" role="tablist" aria-label="<?php esc_attr_e('Mode générateur shortcode', 'wpcursor'); ?>">
					<button type="button" class="button button-primary wpcursor-gen-mode-btn is-active" data-mode="guided" role="tab" aria-selected="true">
						<?php esc_html_e('Assistant actuel', 'wpcursor'); ?>
					</button>
					<button type="button" class="button wpcursor-gen-mode-btn" data-mode="total" role="tab" aria-selected="false">
						<?php esc_html_e('Custom shortcode total', 'wpcursor'); ?>
					</button>
				</div>

				<div id="wpcursor-gen-guided-panel">
					<div id="wpcursor-gen-meta" class="description" style="display:none;margin:-4px 0 12px;"></div>
					<div id="wpcursor-gen-fields" class="wpcursor-generator-fields"></div>
					<p id="wpcursor-gen-no-props" class="description" style="display:none;">
						<?php esc_html_e('Ce composant n’a pas de champs documentés : le shortcode minimal suffit.', 'wpcursor'); ?>
					</p>
				</div>

				<div id="wpcursor-gen-total-panel" style="display:none;">
					<p class="description" style="margin:8px 0;">
						<?php esc_html_e('Mode libre : écrivez le shortcode complet (avec HTML/CSS dans les champs prévus par le composant, ex: custom_html, custom_css).', 'wpcursor'); ?>
					</p>
					<div class="wpcursor-generator-total-helper">
						<div class="wpcursor-generator-total-helper-row">
							<label for="wpcursor-gen-total-css"><strong><?php esc_html_e('Champ CSS', 'wpcursor'); ?></strong></label>
							<textarea id="wpcursor-gen-total-css" class="large-text" rows="3" placeholder=".wpcursor-accueil-admission__heading{color:#8b2942;}"></textarea>
						</div>
						<div class="wpcursor-generator-total-helper-row">
							<label for="wpcursor-gen-total-image-url"><strong><?php esc_html_e('URL image (médiathèque)', 'wpcursor'); ?></strong></label>
							<div class="wpcursor-gen-media-row">
								<input type="url" id="wpcursor-gen-total-image-url" class="regular-text" placeholder="https://…" />
								<button type="button" class="button wpcursor-gen-media-pick" data-target="wpcursor-gen-total-image-url">
									<?php esc_html_e('Choisir dans la médiathèque', 'wpcursor'); ?>
								</button>
							</div>
							<p style="margin:6px 0 0;">
								<button type="button" class="button button-small" id="wpcursor-gen-total-insert-image">
									<?php esc_html_e('Insérer image_url dans le shortcode', 'wpcursor'); ?>
								</button>
							</p>
						</div>
						<div class="wpcursor-generator-total-helper-row">
							<label for="wpcursor-gen-total-html"><strong><?php esc_html_e('Champ HTML', 'wpcursor'); ?></strong></label>
							<textarea id="wpcursor-gen-total-html" class="large-text code" rows="3" placeholder="<strong>Texte libre</strong>"></textarea>
						</div>
						<p style="margin:8px 0 10px;">
							<button type="button" class="button" id="wpcursor-gen-total-apply-fields"><?php esc_html_e('Insérer CSS + HTML dans le shortcode', 'wpcursor'); ?></button>
						</p>
					</div>
					<textarea id="wpcursor-gen-total-raw" class="large-text code" rows="12" placeholder='[wpcursor name="accueil-admission"]&#10;heading_tag=h2&#10;heading=Marie, Admise à Sciences Po&#10;custom_html=<strong>Texte libre</strong>&#10;custom_css=.wpcursor-accueil-admission__heading{color:#8b2942;}&#10;[/wpcursor]'></textarea>
				</div>

				<fieldset id="wpcursor-gen-format-wrap" class="wpcursor-generator-format" style="margin:14px 0;">
					<legend class="screen-reader-text"><?php esc_html_e('Format du shortcode', 'wpcursor'); ?></legend>
					<label style="margin-right:16px;">
						<input type="radio" name="wpcursor_gen_format" value="block" checked="checked" />
						<?php esc_html_e('Format Divi (entre balises, recommandé)', 'wpcursor'); ?>
					</label>
					<label>
						<input type="radio" name="wpcursor_gen_format" value="inline" />
						<?php esc_html_e('Une seule ligne (attributs)', 'wpcursor'); ?>
					</label>
				</fieldset>

				<p class="wpcursor-generator-actions">
					<button type="button" class="button button-primary" id="wpcursor-gen-build">
						<?php esc_html_e('Mettre à jour le shortcode', 'wpcursor'); ?>
					</button>
					<button type="button" class="button" id="wpcursor-gen-copy" disabled="disabled">
						<?php esc_html_e('Copier', 'wpcursor'); ?>
					</button>
					<button type="button" class="button" id="wpcursor-gen-copy-template">
						<?php esc_html_e('Copier modèle Divi', 'wpcursor'); ?>
					</button>
					<a href="#" class="button" id="wpcursor-gen-preview-link" style="display:none;">
						<?php esc_html_e('Prévisualiser', 'wpcursor'); ?>
					</a>
					<button type="button" class="button" id="wpcursor-gen-reset" style="display:none;">
						<?php esc_html_e('Réinitialiser (modèle)', 'wpcursor'); ?>
					</button>
				</p>

				<pre id="wpcursor-gen-output" class="wpcursor-generator-output" hidden="hidden" aria-live="polite"></pre>

				<details class="wpcursor-gen-save-preset" style="margin:16px 0;padding:12px 14px;border:1px solid #d7dde5;border-radius:10px;background:#f6f9fc;">
					<summary style="cursor:pointer;font-weight:700;">
						<?php esc_html_e('Enregistrer comme variante (bibliothèque)', 'wpcursor'); ?>
					</summary>
					<p class="description" style="margin:10px 0;">
						<?php esc_html_e('Donnez un nom lisible (ex. Pierre — page accueil, Pierre Bis 2) pour retrouver ce shortcode plus tard sans chercher dans les pages.', 'wpcursor'); ?>
					</p>
					<form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpcursor&tab=presets')); ?>" id="wpcursor-gen-save-preset-form">
						<?php wp_nonce_field('wpcursor_presets'); ?>
						<input type="hidden" name="wpcursor_preset_action" value="save" />
						<input type="hidden" name="component_slug" id="wpcursor-gen-save-slug" value="<?php echo esc_attr($pref_slug); ?>" />
						<input type="hidden" name="preset_shortcode" id="wpcursor-gen-save-shortcode" value="" />
						<p>
							<label for="wpcursor-gen-save-label"><strong><?php esc_html_e('Nom de la variante', 'wpcursor'); ?></strong></label><br />
							<input type="text" class="regular-text" name="preset_label" id="wpcursor-gen-save-label" required
								placeholder="<?php esc_attr_e('Ex. Marie — témoignage Sciences Po', 'wpcursor'); ?>"
								value="<?php echo $load_preset ? esc_attr($load_preset['label']) : ''; ?>" />
						</p>
						<p>
							<label for="wpcursor-gen-save-notes"><?php esc_html_e('Note (optionnel)', 'wpcursor'); ?></label><br />
							<input type="text" class="large-text" name="preset_notes" id="wpcursor-gen-save-notes"
								value="<?php echo $load_preset ? esc_attr($load_preset['notes']) : ''; ?>" />
						</p>
						<?php if ($load_preset) : ?>
							<input type="hidden" name="preset_id" value="<?php echo esc_attr($load_preset['id']); ?>" />
						<?php endif; ?>
						<button type="submit" class="button button-primary"><?php esc_html_e('Enregistrer dans Variantes shortcode', 'wpcursor'); ?></button>
					</form>
				</details>
			</div>
			<script>
				window.wpcursorGeneratorBaseUrl = <?php echo wp_json_encode($base_url); ?>;
				window.wpcursorGeneratorPreviewUrl = <?php echo wp_json_encode($preview_url); ?>;
				window.wpcursorPresetsByComponent = <?php echo wp_json_encode(WPCursor_Shortcode_Presets::grouped_by_component()); ?>;
				window.wpcursorLoadPresetProps = <?php echo wp_json_encode($load_props); ?>;
			</script>
		<?php endif; ?>
		<?php
	}

	/**
	 * @return array<string, string>
	 */
	private static function parse_preview_props(string $raw): array {
		return WPCursor_Component_Loader::parse_props_text($raw);
	}

	/**
	 * @param array<string, mixed> $p
	 *
	 * @return array<string, mixed>
	 */
	private static function build_cursor_context_payload(array $p): array {
		$files = [];
		foreach ($p['components'] as $slug) {
			$slug = preg_replace('/[^a-z0-9_-]/', '', (string) $slug);
			if ($slug === '') {
				continue;
			}
			$files[] = 'wp-content/plugins/wpcursor/components/' . $slug . '/component.php';
			$files[] = 'wp-content/plugins/wpcursor/components/' . $slug . '.php';
		}
		$files = array_values(array_unique($files));

		return [
			'title'       => $p['title'],
			'post_id'     => $p['post_id'],
			'post_type'   => $p['type'],
			'status'      => $p['status'],
			'sources'     => $p['sources'],
			'components'  => $p['components'],
			'shortcodes'  => $p['shortcode_samples'],
			'url_edit'    => $p['edit_link'],
			'url_front'   => $p['permalink'],
			'plugin_files'=> $files,
		];
	}

	public static function enqueue_admin_assets(string $hook): void {
		if ($hook !== 'toplevel_page_wpcursor') {
			return;
		}
		wp_register_style('wpcursor-admin-inline', false, [], '1.0.0');
		wp_enqueue_style('wpcursor-admin-inline');
		$css = <<<'CSS'
body.toplevel_page_wpcursor #wpbody-content{
  padding-right:16px;
}
.wpcursor-admin{
  max-width:none;
  width:100%;
  box-sizing:border-box;
}
.wpcursor-admin .nav-tab-wrapper{
  display:flex;
  flex-wrap:nowrap;
  gap:0;
  overflow-x:auto;
  overflow-y:hidden;
  -webkit-overflow-scrolling:touch;
  scrollbar-width:thin;
  margin-bottom:14px;
  padding-bottom:2px;
}
.wpcursor-admin .nav-tab{
  flex:0 0 auto;
  white-space:nowrap;
}
.wpcursor-admin .description{color:#5f6b7a}
.wpcursor-admin .wpcursor-info-box{
  background:#f0f6ff;
  border:1px solid #c9dcff;
  border-left:4px solid #2271b1;
  border-radius:10px;
  padding:12px 14px;
  margin:10px 0 14px;
  color:#1f2937;
}
.wpcursor-admin .wpcursor-info-title{
  display:flex;
  align-items:center;
  gap:6px;
}
.wpcursor-admin .wpcursor-info-title .dashicons{
  color:#1d5f93;
}
.wpcursor-admin .wpcursor-warning-box{
  background:#fff8e5;
  border:1px solid #f0d79f;
  border-left:4px solid #dba617;
  border-radius:10px;
  padding:12px 14px;
  margin:12px 0 8px;
  color:#423100;
}
.wpcursor-admin .wpcursor-warning-title{
  display:flex;
  align-items:center;
  gap:6px;
}
.wpcursor-admin .wpcursor-warning-title .dashicons{
  color:#b07800;
}
.wpcursor-admin .nav-tab{
  border-radius:8px 8px 0 0;
  padding:7px 11px;
  font-size:13px;
  font-weight:600;
}
.wpcursor-admin .nav-tab-active{
  background:#fff;
  border-bottom:1px solid #fff;
}
.wpcursor-admin h3{
  margin-top:18px;
  margin-bottom:8px;
  font-size:18px;
}
.wpcursor-admin .widefat{
  border:1px solid #d7dde5;
  border-radius:10px;
  overflow:hidden;
  box-shadow:0 1px 2px rgba(0,0,0,.03);
}
.wpcursor-admin .widefat thead th{
  background:#f7f9fc;
  color:#243447;
  font-weight:700;
}
.wpcursor-admin .widefat td,
.wpcursor-admin .widefat th{
  padding:10px 12px;
  vertical-align:middle;
}
.wpcursor-admin code{
  background:#f3f5f7;
  border:1px solid #e0e6ed;
  border-radius:6px;
  padding:2px 6px;
}
.wpcursor-admin .button{
  border-radius:8px;
}
.wpcursor-admin .button.button-primary{
  box-shadow:none;
}
#wpcursor-catalog-search,
#wpcursor-components-search{
  min-width:320px;
  border-radius:8px;
}
.wpcursor-inline-label-form{
  margin:0;
}
.wpcursor-inline-label-form .wpcursor-label-view{
  display:flex;
  align-items:center;
  gap:8px;
}
.wpcursor-inline-label-form .wpcursor-label-edit{
  display:flex;
  align-items:center;
  gap:8px;
  flex-wrap:wrap;
}
.wpcursor-inline-label-form input[type="text"]{
  min-width:220px;
  border-radius:8px;
}
.wpcursor-preview-form .form-table th{
  width:220px;
}
.wpcursor-preview-sandbox{
  border-radius:10px;
  box-shadow:inset 0 0 0 1px #e8ecf1;
}
.wpcursor-admin .wpcursor-onboarding-details{
  margin:36px 0 28px;
  border:1px solid #c8d0db;
  border-left:4px solid #2271b1;
  border-radius:12px;
  background:linear-gradient(180deg,#fbfcfe 0%,#f4f7fb 100%);
  box-shadow:0 1px 3px rgba(0,0,0,.05);
  overflow:hidden;
}
.wpcursor-admin .wpcursor-onboarding-summary{
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:10px 14px;
  cursor:pointer;
  list-style:none;
  padding:16px 18px;
  font-weight:600;
  font-size:15px;
  margin:0;
  outline:none;
  transition:background .15s ease;
}
.wpcursor-admin .wpcursor-onboarding-summary:hover{
  background:rgba(34,113,177,.06);
}
.wpcursor-admin .wpcursor-onboarding-summary:focus-visible{
  box-shadow:inset 0 0 0 2px #2271b1;
}
.wpcursor-admin .wpcursor-onboarding-summary::-webkit-details-marker{display:none}
.wpcursor-admin .wpcursor-onboarding-summary .dashicons{font-size:20px;width:20px;height:20px;color:#2271b1}
.wpcursor-admin .wpcursor-onboarding-summary-label{
  flex:1 1 220px;
  line-height:1.35;
  color:#1d2327;
}
.wpcursor-admin .wpcursor-onboarding-chevron{
  display:inline-block;
  width:8px;
  height:8px;
  border-right:2px solid #646970;
  border-bottom:2px solid #646970;
  transform:rotate(-45deg);
  transition:transform .2s ease, border-color .15s ease;
  flex-shrink:0;
  margin-top:2px;
}
.wpcursor-admin .wpcursor-onboarding-details[open] .wpcursor-onboarding-chevron{
  transform:rotate(45deg);
  margin-top:-2px;
  border-color:#2271b1;
}
.wpcursor-admin .wpcursor-onboarding-hint{
  font-weight:500;
  font-size:12px;
  letter-spacing:.02em;
  text-transform:uppercase;
  color:#646970;
  padding:4px 10px;
  border-radius:999px;
  background:rgba(255,255,255,.75);
  border:1px solid #dcdcde;
}
.wpcursor-admin .wpcursor-onboarding-details[open] .wpcursor-onboarding-hint{
  color:#2271b1;
  border-color:#c5d9ed;
  background:rgba(34,113,177,.08);
}
.wpcursor-admin .wpcursor-onboarding{
  margin:0;
  padding:8px 20px 22px;
  border:none;
  border-radius:0;
  background:transparent;
}
.wpcursor-admin .wpcursor-onboarding > .description{
  margin-bottom:14px !important;
  line-height:1.6;
}
.wpcursor-admin .wpcursor-onboarding-details[open] .wpcursor-onboarding-summary{
  border-bottom:1px solid #d7dde5;
  background:#fff;
}
.wpcursor-admin .wpcursor-onboarding-details[open] .wpcursor-onboarding-summary:hover{
  background:#fafbfc;
}
.wpcursor-admin .wpcursor-onboarding-steps{
  margin:6px 0 0;
  padding-left:1.5em;
}
.wpcursor-admin .wpcursor-onboarding-steps li{
  margin:0 0 16px;
  padding-left:2px;
  line-height:1.65;
  color:#2c3338;
}
.wpcursor-admin .wpcursor-onboarding-steps li:last-child{
  margin-bottom:0;
}
.wpcursor-admin .wpcursor-onboarding-pre{
  display:block;
  margin:12px 0 0;
  padding:14px 16px;
  background:#1e2936;
  color:#e8eef5;
  border-radius:8px;
  font-size:12.5px;
  line-height:1.5;
  overflow:auto;
  max-width:100%;
  border:1px solid #0f1419;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.05);
}
.wpcursor-admin .wpcursor-code-inline{
  white-space:nowrap;
}
.wpcursor-admin .wpcursor-source-pre{
  margin:0;
  padding:12px 14px;
  background:#1e2936;
  color:#e8eef5;
  border-radius:8px;
  font-size:12px;
  line-height:1.45;
  overflow:auto;
  max-height:420px;
  white-space:pre-wrap;
}
.wpcursor-admin .wpcursor-asset-library-table code{
  font-size:11px;
}
.wpcursor-admin .wpcursor-shortcode-copy{
  cursor:pointer;
  transition:background .15s ease, box-shadow .15s ease;
}
.wpcursor-admin .wpcursor-shortcode-copy:hover,
.wpcursor-admin .wpcursor-shortcode-copy:focus{
  background:#e8f2fc;
  box-shadow:0 0 0 1px #2271b1;
  outline:none;
}
.wpcursor-admin .wpcursor-shortcode-copy.wpcursor-shortcode-copied{
  background:#e6f4ea;
  box-shadow:0 0 0 1px #1e8e3e;
}
.wpcursor-generator-fields{
  max-width:100%;
  margin:8px 0 0;
}
.wpcursor-gen-tabbed{
  margin:4px 0 0;
}
.wpcursor-gen-tabs{
  display:flex;
  flex-wrap:wrap;
  gap:6px;
  margin:0 0 12px;
  padding:6px;
  background:linear-gradient(180deg,#eef2f7 0%,#e8edf3 100%);
  border-radius:14px;
  border:1px solid #d7dde5;
}
.wpcursor-gen-tab{
  appearance:none;
  border:none;
  background:transparent;
  padding:9px 16px;
  border-radius:10px;
  font-weight:600;
  font-size:13px;
  color:#3c434a;
  cursor:pointer;
  transition:background .15s ease, color .15s ease, box-shadow .15s ease;
  line-height:1.3;
}
.wpcursor-gen-tab:hover{
  color:#1d4ed8;
  background:rgba(255,255,255,.55);
}
.wpcursor-gen-tab.is-active{
  background:#fff;
  color:#1d4ed8;
  box-shadow:0 2px 8px rgba(29,78,216,.12);
}
.wpcursor-gen-tab-count{
  display:inline-block;
  min-width:1.4em;
  margin-left:4px;
  padding:1px 6px;
  font-size:11px;
  font-weight:700;
  border-radius:999px;
  background:rgba(29,78,216,.1);
  color:#1d4ed8;
  vertical-align:middle;
}
.wpcursor-gen-tab-panel{
  display:none;
  padding:16px 18px;
  background:#fff;
  border:1px solid #e2e8f0;
  border-radius:14px;
  box-shadow:0 1px 3px rgba(15,23,42,.04);
  max-height:min(560px,68vh);
  overflow-y:auto;
  overflow-x:hidden;
}
.wpcursor-gen-tab-panel.is-active{
  display:block;
}
.wpcursor-gen-fields-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
  gap:16px 22px;
  align-items:start;
}
.wpcursor-gen-field--wide{
  grid-column:1 / -1;
}
.wpcursor-generator-fields .wpcursor-gen-field{
  margin:0;
}
.wpcursor-generator-fields .wpcursor-gen-help{
  margin:4px 0 6px;
  color:#5f6b7a;
  font-size:12px;
}
.wpcursor-generator-fields .wpcursor-gen-color-row{
  display:flex;
  align-items:center;
  gap:8px;
  margin:6px 0 0;
}
.wpcursor-generator-fields .wpcursor-gen-color{
  width:42px;
  height:32px;
  border-radius:8px;
  padding:2px;
}
.wpcursor-generator-fields .wpcursor-gen-advanced{
  margin-top:6px;
  border:1px solid #d7dde5;
  border-radius:10px;
  padding:8px 12px 12px;
  background:#fbfcfe;
}
.wpcursor-generator-fields .wpcursor-gen-advanced > summary{
  cursor:pointer;
  font-weight:700;
  padding:4px 0;
}
.wpcursor-generator-fields .wpcursor-gen-advanced > summary::marker{
  color:#2271b1;
}
.wpcursor-generator-fields label{
  display:block;
  font-weight:600;
  margin-bottom:4px;
}
.wpcursor-generator-fields .wpcursor-gen-required{
  color:#b32d2e;
  font-weight:700;
}
.wpcursor-generator-fields input[type="text"],
.wpcursor-generator-fields input[type="url"],
.wpcursor-generator-fields select,
.wpcursor-generator-fields textarea{
  width:100%;
  max-width:100%;
  border-radius:8px;
}
.wpcursor-generator-fields textarea{
  min-height:88px;
  line-height:1.45;
}
.wpcursor-generator-mode-tabs{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  margin:12px 0 10px;
}
.wpcursor-gen-mode-btn[aria-selected="false"]{
  opacity:.92;
}
#wpcursor-gen-total-raw{
  border-radius:8px;
  width:100%;
  max-width:100%;
  font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}
.wpcursor-generator-total-helper{
  margin:6px 0 10px;
  padding:10px 12px;
  border:1px solid #d7dde5;
  border-radius:8px;
  background:#fbfcfe;
}
.wpcursor-generator-total-helper-row{
  margin:0 0 10px;
}
.wpcursor-generator-total-helper-row textarea{
  width:100%;
  max-width:100%;
  border-radius:8px;
}
.wpcursor-gen-media-row{
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  gap:8px;
  margin-top:4px;
}
.wpcursor-gen-media-row input[type="url"],
.wpcursor-gen-media-row input[type="text"]{
  flex:1 1 280px;
  min-width:200px;
  border-radius:8px;
}
.wpcursor-gen-media-preview{
  display:block;
  margin-top:8px;
  max-width:160px;
  max-height:120px;
  border-radius:8px;
  border:1px solid #d7dde5;
  object-fit:cover;
}
.wpcursor-generator-output{
  margin:12px 0 0;
  padding:14px 16px;
  background:#1e2936;
  color:#e8eef5;
  border-radius:10px;
  font-size:12.5px;
  line-height:1.5;
  white-space:pre-wrap;
  word-break:break-word;
  max-width:100%;
  border:1px solid #0f1419;
  cursor:pointer;
}
.wpcursor-generator-output:hover{
  box-shadow:0 0 0 1px #2271b1;
}
.wpcursor-generator-actions{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  align-items:center;
}
.wpcursor-badge{
  display:inline-block;
  margin:0 4px 4px 0;
  padding:2px 8px;
  border-radius:999px;
  font-size:11px;
  font-weight:600;
  line-height:1.5;
}
.wpcursor-badge--lib{background:#e8f2fc;color:#1d4ed8;}
.wpcursor-badge--live{background:#e6f4ea;color:#137333;}
.wpcursor-badge--orphan{background:#f3f4f6;color:#646970;}
.wpcursor-repo-summary{font-size:11px;color:#50575e;}
.wpcursor-page-list{margin:0;padding:0;list-style:none;}
.wpcursor-page-list li{margin:0 0 8px;padding:0;}
.wpcursor-page-status{
  display:inline-block;
  width:8px;height:8px;border-radius:50%;
  margin-right:4px;vertical-align:middle;
}
.wpcursor-page-status--publish{background:#00a32a;}
.wpcursor-page-status--draft{background:#dba617;}
.wpcursor-page-status--private{background:#787c82;}
.wpcursor-page-links{margin-left:6px;}
.wpcursor-page-links a{margin-right:6px;}
.wpcursor-repo-actions .button{margin:0 4px 4px 0;}
CSS;
		wp_add_inline_style('wpcursor-admin-inline', $css);
		wp_enqueue_script('jquery');
		wp_enqueue_media();

		$tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'components';
		if ($tab === 'generator') {
			wp_enqueue_script(
				'wpcursor-generator-fields-ui',
				WPCURSOR_URL . 'assets/generator-fields-ui.js',
				['jquery'],
				WPCURSOR_VERSION,
				true
			);
			wp_add_inline_script(
				'wpcursor-generator-fields-ui',
				'var wpcursorGeneratorManifest = ' . wp_json_encode(self::get_shortcode_generator_manifest()) . ';',
				'before'
			);
		}
		$js = <<<'JS'
jQuery(function($) {
  function wpcursorCopyText(text, doneMsg) {
    if (!text) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function() {
        if (doneMsg) window.alert(doneMsg);
      });
      return;
    }
    var ta = $('<textarea>').val(text).appendTo('body').select();
    try {
      document.execCommand('copy');
      if (doneMsg) window.alert(doneMsg);
    } finally {
      ta.remove();
    }
  }

  function wpcursorCopyShortcode($el) {
    var text = String($el.attr('data-shortcode') || $el.text() || '').trim();
    if (!text) return;
    wpcursorCopyText(text, 'Shortcode copié dans le presse-papiers.');
    $el.addClass('wpcursor-shortcode-copied');
    window.setTimeout(function() { $el.removeClass('wpcursor-shortcode-copied'); }, 1200);
  }

  $(document).on('click', '.wpcursor-shortcode-copy', function(e) {
    e.preventDefault();
    wpcursorCopyShortcode($(this));
  });

  $(document).on('keydown', '.wpcursor-shortcode-copy', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      wpcursorCopyShortcode($(this));
    }
  });

  $(document).on('click', '.wpcursor-copy-ctx', function() {
    var raw = $(this).attr('data-context');
    if (!raw) return;
    var d;
    try { d = JSON.parse(raw); } catch (e) { return; }
    var lines = [];
    lines.push('=== Contexte Cursor (WP Cursor) ===');
    lines.push('Page : ' + (d.title || ''));
    lines.push('post_id : ' + (d.post_id || ''));
    lines.push('post_type : ' + (d.post_type || ''));
    lines.push('statut : ' + (d.status || ''));
    lines.push('Sources : ' + ((d.sources || []).join(' + ')));
    lines.push('Composants : ' + ((d.components || []).join(', ')));
    lines.push('URL édition : ' + (d.url_edit || ''));
    lines.push('URL front : ' + (d.url_front || ''));
    if (d.shortcodes && d.shortcodes.length) {
      lines.push('Shortcodes (extraits) :');
      d.shortcodes.forEach(function(s) { lines.push(s); });
    }
    if (d.plugin_files && d.plugin_files.length) {
      lines.push('Fichiers plugin possibles :');
      d.plugin_files.forEach(function(f) { lines.push('- ' + f); });
    }
    lines.push('Objectif : modifier les composants listés sans casser le layout Divi sur cette page.');
    var text = lines.join('\n');
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function() {
        window.alert('Contexte copié dans le presse-papiers.');
      });
    } else {
      var ta = $('<textarea>').val(text).appendTo('body').select();
      try { document.execCommand('copy'); window.alert('Contexte copié.'); } finally { ta.remove(); }
    }
  });

  $(document).on('input', '#wpcursor-catalog-search, #wpcursor-components-search', function() {
    var q = String($(this).val() || '').toLowerCase().trim();
    var rowSelector = $(this).attr('id') === 'wpcursor-components-search'
      ? '.wpcursor-components-table .wpcursor-component-row'
      : '.wpcursor-catalog-table .wpcursor-catalog-row';
    $(rowSelector).each(function() {
      var text = String($(this).attr('data-search') || '');
      $(this).toggle(q === '' || text.indexOf(q) !== -1);
    });
  });

  $(document).on('click', '.wpcursor-label-edit-toggle', function() {
    var $form = $(this).closest('.wpcursor-inline-label-form');
    $form.find('.wpcursor-label-view').hide();
    $form.find('.wpcursor-label-edit').show().find('input[name="wpcursor_component_label"]').trigger('focus');
  });

  $(document).on('click', '.wpcursor-label-cancel', function() {
    var $form = $(this).closest('.wpcursor-inline-label-form');
    $form.find('.wpcursor-label-edit').hide();
    $form.find('.wpcursor-label-view').show();
  });

  if (typeof wpcursorGeneratorManifest !== 'undefined') {
    var $genSlug = $('#wpcursor-gen-slug');
    var $genModeBtn = $('.wpcursor-gen-mode-btn');
    var $genGuidedPanel = $('#wpcursor-gen-guided-panel');
    var $genTotalPanel = $('#wpcursor-gen-total-panel');
    var $genTotalCss = $('#wpcursor-gen-total-css');
    var $genTotalHtml = $('#wpcursor-gen-total-html');
    var $genTotalApplyFields = $('#wpcursor-gen-total-apply-fields');
    var $genTotalRaw = $('#wpcursor-gen-total-raw');
    var $genFormatWrap = $('#wpcursor-gen-format-wrap');
    var $genFields = $('#wpcursor-gen-fields');
    var $genMeta = $('#wpcursor-gen-meta');
    var $genNoProps = $('#wpcursor-gen-no-props');
    var $genOut = $('#wpcursor-gen-output');
    var $genCopy = $('#wpcursor-gen-copy');
    var $genCopyTemplate = $('#wpcursor-gen-copy-template');
    var $genPreview = $('#wpcursor-gen-preview-link');
    var $genReset = $('#wpcursor-gen-reset');
    var lastGenerated = '';

    function wpcursorGenOpenMediaPicker(targetInputId, altPropKey) {
      if (typeof wp === 'undefined' || !wp.media) {
        window.alert('Médiathèque indisponible. Rechargez la page admin.');
        return;
      }
      var frame = wp.media({
        title: 'Choisir une image',
        button: { text: 'Utiliser cette image' },
        library: { type: 'image' },
        multiple: false
      });
      frame.on('select', function() {
        var att = frame.state().get('selection').first().toJSON();
        var url = att.url || '';
        var $input = $('#' + targetInputId);
        $input.val(url).trigger('input');
        if (window.WPCursorGeneratorUI) {
          WPCursorGeneratorUI.updateMediaPreview($input.closest('.wpcursor-gen-field, .wpcursor-generator-total-helper-row'), url);
        }
        if (altPropKey) {
          var alt = (att.alt || att.title || '').trim();
          if (alt) {
            var $alt = $genFields.find('[data-prop="' + altPropKey + '"]');
            if ($alt.length && !String($alt.val() || '').trim()) {
              $alt.val(alt).trigger('input');
            }
          }
        }
      });
      frame.open();
    }

    function wpcursorGenActiveMode() {
      var m = String($genModeBtn.filter('.is-active').attr('data-mode') || 'guided');
      return m === 'total' ? 'total' : 'guided';
    }

    function wpcursorGenApplyMode(mode) {
      var active = mode === 'total' ? 'total' : 'guided';
      $genModeBtn.removeClass('button-primary is-active').attr('aria-selected', 'false');
      $genModeBtn.filter('[data-mode="' + active + '"]').addClass('button-primary is-active').attr('aria-selected', 'true');
      var total = active === 'total';
      $genGuidedPanel.toggle(!total);
      $genTotalPanel.toggle(total);
      $genFormatWrap.toggle(!total);
      $genPreview.toggle(!total && !!lastGenerated);
    }

    function wpcursorGenFormat() {
      var v = $('input[name="wpcursor_gen_format"]:checked').val();
      return v === 'inline' ? 'inline' : 'block';
    }

    function wpcursorGenCollectProps() {
      return window.WPCursorGeneratorUI
        ? WPCursorGeneratorUI.collectProps($genFields)
        : {};
    }

    function wpcursorGenBuildShortcode(slug, props, format) {
      var pairs = [];
      Object.keys(props).forEach(function(k) {
        var v = String(props[k] || '').trim();
        if (!v || k === 'name') return;
        pairs.push({ key: k, val: v });
      });
      if (format === 'inline') {
        var attrs = pairs.map(function(p) {
          return p.key + '="' + p.val.replace(/\\/g, '\\\\').replace(/"/g, '\\"') + '"';
        });
        return '[wpcursor name="' + slug + '"' + (attrs.length ? ' ' + attrs.join(' ') : '') + ']';
      }
      if (!pairs.length) {
        return '[wpcursor name="' + slug + '"]';
      }
      var body = pairs.map(function(p) { return p.key + '=' + p.val; }).join('\n');
      return '[wpcursor name="' + slug + '"]\n' + body + '\n[/wpcursor]';
    }

    function wpcursorGenBuildTemplateFromDefaults(slug) {
      if (!slug || !wpcursorGeneratorManifest[slug]) return '';
      var entry = wpcursorGeneratorManifest[slug];
      var props = entry.props || {};
      var keys = Object.keys(props).sort();
      var lines = ['[wpcursor name="' + slug + '"]'];
      keys.forEach(function(key) {
        var spec = props[key] || {};
        var def = spec.default != null ? String(spec.default) : '';
        if (def.trim()) {
          lines.push(key + '=' + def);
        }
      });
      lines.push('[/wpcursor]');
      return lines.join('\n');
    }

    function wpcursorGenUpsertBodyLine(raw, key, value) {
      var text = String(raw || '');
      var cleanValue = String(value || '').replace(/\r?\n/g, ' ').trim();
      var pattern = new RegExp('(^|\\n)' + key + '=.*(?=\\n|$)', 'm');
      if (cleanValue === '') {
        text = text.replace(pattern, '').replace(/\n{3,}/g, '\n\n').trim();
        return text;
      }
      var line = key + '=' + cleanValue;
      if (pattern.test(text)) {
        return text.replace(pattern, function(m, p1) { return p1 + line; });
      }
      var close = '[/wpcursor]';
      var idx = text.lastIndexOf(close);
      if (idx !== -1) {
        var before = text.slice(0, idx).replace(/\s+$/, '');
        var after = text.slice(idx);
        return before + '\n' + line + '\n' + after;
      }
      return text + (text.trim() ? '\n' : '') + line;
    }

    function wpcursorGenRenderFields(slug) {
      $genFields.empty();
      $genMeta.hide().empty();
      $genNoProps.hide();
      $genOut.attr('hidden', 'hidden').text('');
      $genCopy.prop('disabled', true);
      $genPreview.hide();
      $genReset.hide();
      lastGenerated = '';

      if (!slug || !wpcursorGeneratorManifest[slug] || !window.WPCursorGeneratorUI) return;

      var entry = wpcursorGeneratorManifest[slug];
      var result = WPCursorGeneratorUI.render({
        $container: $genFields,
        slug: slug,
        manifest: wpcursorGeneratorManifest,
        idPrefix: 'wpcursor-gen-',
        $meta: $genMeta,
        onFieldChange: wpcursorGenUpdateOutput,
        onMediaPick: true,
        mediaButtonLabel: 'Choisir dans la médiathèque'
      });

      if (result.empty) {
        $genNoProps.show();
        wpcursorGenUpdateOutput();
        return;
      }

      if (entry.props && entry.props.custom_css && entry.props.custom_css.default) {
        WPCursorGeneratorUI.applyColorRulesFromCss($genFields, entry.props.custom_css.default);
      }

      $genReset.show();
      wpcursorGenUpdateOutput();

      if (window.wpcursorLoadPresetProps && typeof window.wpcursorLoadPresetProps === 'object') {
        Object.keys(window.wpcursorLoadPresetProps).forEach(function(k) {
          $genFields.find('[data-prop="' + k + '"]').val(window.wpcursorLoadPresetProps[k]);
        });
        if (window.WPCursorGeneratorUI && window.wpcursorLoadPresetProps.custom_css) {
          WPCursorGeneratorUI.applyColorRulesFromCss($genFields, window.wpcursorLoadPresetProps.custom_css);
        }
        window.wpcursorLoadPresetProps = null;
        wpcursorGenUpdateOutput();
      }
    }

    function wpcursorGenUpdateOutput() {
      var slug = String($genSlug.val() || '');
      if (!slug) {
        $genOut.attr('hidden', 'hidden').text('');
        $genCopy.prop('disabled', true);
        $genPreview.hide();
        return;
      }
      var mode = wpcursorGenActiveMode();
      var text = '';
      if (mode === 'total') {
        text = String($genTotalRaw.val() || '').trim();
      } else {
        text = wpcursorGenBuildShortcode(slug, wpcursorGenCollectProps(), wpcursorGenFormat());
      }
      lastGenerated = text;
      $genOut.removeAttr('hidden').text(text);
      $genCopy.prop('disabled', !text);

      if (mode === 'total') {
        $genPreview.hide();
        return;
      }

      var entry = wpcursorGeneratorManifest[slug];
      if (entry && entry.preview_nonce && window.wpcursorGeneratorPreviewUrl) {
        var propsTxt = '';
        var props = wpcursorGenCollectProps();
        Object.keys(props).forEach(function(k) {
          var v = String(props[k] || '').trim();
          if (v) propsTxt += k + '=' + v + '\n';
        });
        var url = window.wpcursorGeneratorPreviewUrl
          + '&component=' + encodeURIComponent(slug)
          + '&_wpnonce=' + encodeURIComponent(entry.preview_nonce);
        if (propsTxt.trim()) {
          url += '&preview_props=' + encodeURIComponent(propsTxt.trim());
        }
        $genPreview.attr('href', url).show();
      }
    }

    $genSlug.on('change', function() {
      var slug = String($(this).val() || '');
      if (window.wpcursorGeneratorBaseUrl && slug && window.history && window.history.replaceState) {
        window.history.replaceState(null, '', window.wpcursorGeneratorBaseUrl + '&component=' + encodeURIComponent(slug));
      }
      wpcursorGenRenderFields(slug);
      wpcursorGenRefreshPresetDropdown(slug);
      $('#wpcursor-gen-save-slug').val(slug);
    });

    function wpcursorGenRefreshPresetDropdown(slug) {
      var $sel = $('#wpcursor-gen-load-preset');
      if (!$sel.length) return;
      var groups = window.wpcursorPresetsByComponent || {};
      var list = groups[slug] || [];
      $sel.empty().append($('<option value="">').text('— Charger une variante —'));
      list.forEach(function(row) {
        $sel.append($('<option>').attr('value', row.id).text(row.label));
      });
      $sel.closest('p.description').toggle(list.length > 0);
    }

    $('#wpcursor-gen-load-preset').on('change', function() {
      var id = String($(this).val() || '');
      var slug = String($genSlug.val() || '');
      if (!id || !slug || !window.wpcursorGeneratorBaseUrl) return;
      window.location.href = window.wpcursorGeneratorBaseUrl + '&component=' + encodeURIComponent(slug) + '&preset=' + encodeURIComponent(id);
    });

    $('#wpcursor-gen-save-preset-form').on('submit', function() {
      var slug = String($genSlug.val() || '');
      var code = String(lastGenerated || $genOut.text() || '').trim();
      if (!slug || !code) {
        window.alert('Générez d’abord le shortcode (Mettre à jour le shortcode).');
        return false;
      }
      $('#wpcursor-gen-save-slug').val(slug);
      $('#wpcursor-gen-save-shortcode').val(code);
      return true;
    });

    $genFields.on('input change', '[data-prop]', function() {
      wpcursorGenUpdateOutput();
    });
    $genTotalRaw.on('input', function() {
      if (wpcursorGenActiveMode() === 'total') {
        wpcursorGenUpdateOutput();
      }
    });
    $(document).on('click', '.wpcursor-gen-media-pick', function(e) {
      e.preventDefault();
      var target = String($(this).attr('data-target') || '');
      var altProp = String($(this).attr('data-alt-prop') || '');
      if (!target) return;
      wpcursorGenOpenMediaPicker(target, altProp);
    });

    $('#wpcursor-gen-total-insert-image').on('click', function(e) {
      e.preventDefault();
      var url = String($('#wpcursor-gen-total-image-url').val() || '').trim();
      if (!url) return;
      var raw = String($genTotalRaw.val() || '').trim();
      if (!raw) {
        var slug = String($genSlug.val() || '');
        if (slug) {
          raw = '[wpcursor name="' + slug + '"]\n[/wpcursor]';
        }
      }
      raw = wpcursorGenUpsertBodyLine(raw, 'image_url', url);
      $genTotalRaw.val(raw);
      wpcursorGenUpdateOutput();
    });

    $genTotalApplyFields.on('click', function(e) {
      e.preventDefault();
      var raw = String($genTotalRaw.val() || '').trim();
      if (!raw) {
        var slug = String($genSlug.val() || '');
        if (slug) {
          raw = '[wpcursor name="' + slug + '"]\n[/wpcursor]';
        }
      }
      raw = wpcursorGenUpsertBodyLine(raw, 'custom_css', String($genTotalCss.val() || ''));
      raw = wpcursorGenUpsertBodyLine(raw, 'custom_html', String($genTotalHtml.val() || ''));
      $genTotalRaw.val(raw);
      wpcursorGenUpdateOutput();
    });
    $genModeBtn.on('click', function(e) {
      e.preventDefault();
      wpcursorGenApplyMode(String($(this).attr('data-mode') || 'guided'));
      wpcursorGenUpdateOutput();
    });

    $('input[name="wpcursor_gen_format"]').on('change', function() {
      wpcursorGenUpdateOutput();
    });

    $('#wpcursor-gen-build').on('click', function(e) {
      e.preventDefault();
      wpcursorGenUpdateOutput();
    });

    $('#wpcursor-gen-copy').on('click', function(e) {
      e.preventDefault();
      if (!lastGenerated) return;
      wpcursorCopyText(lastGenerated, 'Shortcode copié dans le presse-papiers.');
    });
    $genCopyTemplate.on('click', function(e) {
      e.preventDefault();
      var slug = String($genSlug.val() || '');
      if (!slug) return;
      var template = wpcursorGenBuildTemplateFromDefaults(slug);
      if (!template) return;
      wpcursorCopyText(template, 'Modèle Divi copié dans le presse-papiers.');
    });

    $genOut.on('click', function() {
      if (!lastGenerated) return;
      wpcursorCopyText(lastGenerated, 'Shortcode copié dans le presse-papiers.');
    });

    $('#wpcursor-gen-reset').on('click', function(e) {
      e.preventDefault();
      var slug = String($genSlug.val() || '');
      wpcursorGenRenderFields(slug);
    });

    if ($genSlug.val()) {
      wpcursorGenRenderFields(String($genSlug.val()));
    }
    wpcursorGenApplyMode('guided');
  }
});
JS;
		if ($tab === 'generator') {
			wp_add_inline_script('wpcursor-generator-fields-ui', $js);
		} else {
			wp_add_inline_script('jquery', $js);
		}
	}

	private static function render_tab_diagnostic(): void {
		$theme  = wp_get_theme();
		$inv    = WPCursor_Component_Loader::inventory();
		$comp_n = count(array_unique(array_column($inv, 'slug')));
		$git    = file_exists(ABSPATH . '.git/config');
		$scan   = get_transient(WPCURSOR_USAGE_SCAN_TRANSIENT);
		$usage_n = is_array($scan) && isset($scan['rows']) ? count($scan['rows']) : 0;

		$rows = [
			[ 'PHP', PHP_VERSION ],
			[ 'WordPress', get_bloginfo('version') ],
			[ 'Thème actif (stylesheet)', $theme->get_stylesheet() ],
			[ 'Thème parent (template)', $theme->get_template() ],
			[ 'Divi (template ou enfant)', ( $theme->get_template() === 'Divi' || $theme->get_stylesheet() === 'Divi' ) ? 'oui' : 'non détecté comme Divi' ],
			[ 'WooCommerce', class_exists('WooCommerce') ? 'oui' : 'non' ],
			[ 'WP_DEBUG', ( defined('WP_DEBUG') && WP_DEBUG ) ? 'true' : 'false' ],
			[ 'DISALLOW_FILE_EDIT', ( defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ) ? 'true' : 'false' ],
			[ 'Chemin plugin WP Cursor', WPCURSOR_PATH ],
			[ 'Dossier components/ accessible en écriture', is_writable(WPCURSOR_PATH . 'components') ? 'oui' : 'non' ],
			[ 'Fichier .git/config à la racine web (indicatif)', $git ? 'présent — vérifier qu’il n’est pas servi en HTTP' : 'non trouvé sous ABSPATH' ],
			[ 'Nombre de composants (slugs distincts)', (string) $comp_n ],
			[ 'Entrées dernier scan utilisation (cache)', $usage_n ? (string) $usage_n : '0 (lancer analyse)' ],
			[
				'Logs plugin ([WP Cursor] dans debug.log)',
				( defined('WPCURSOR_DEBUG') && WPCURSOR_DEBUG )
					? 'WPCURSOR_DEBUG actif'
					: (
						( defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('WP_DEBUG') && WP_DEBUG )
							? 'WP_DEBUG_LOG + WP_DEBUG actifs'
							: 'inactifs — ajouter WPCURSOR_DEBUG ou WP_DEBUG_LOG dans wp-config.php'
					),
			],
		];

		echo '<h3>' . esc_html__('Diagnostic', 'wpcursor') . '</h3>';
		echo '<table class="widefat striped"><tbody>';
		foreach ($rows as $r) {
			echo '<tr><th scope="row" style="width:280px;">' . esc_html($r[0]) . '</th><td>' . esc_html($r[1]) . '</td></tr>';
		}
		echo '</tbody></table>';
		echo '<p class="description">' . esc_html__('WP‑CLI : wp wpcursor list | usage | diagnose | validate | render | flush-cache | context | context-site — voir WP_CURSOR_PLUGIN.txt.', 'wpcursor') . '</p>';
		echo '<p class="description">' . esc_html__('REST lecture seule : /wp-json/wpcursor/v1/… — GET uniquement, capability manage_wpcursor.', 'wpcursor') . '</p>';
	}

	/**
	 * Thème enfant : liste des fichiers, aperçu fin de style.css, liens éditeur.
	 */
	private static function render_tab_theme(): void {
		$child_root = get_stylesheet_directory();
		$can_edit   = current_user_can('edit_themes')
			&& (! defined('DISALLOW_FILE_EDIT') || ! DISALLOW_FILE_EDIT);

		self::render_theme_cursor_notice();

		if (! is_dir($child_root)) {
			echo '<p>' . esc_html__('Répertoire du thème enfant introuvable.', 'wpcursor') . '</p>';
			return;
		}

		$files = self::collect_child_theme_files($child_root);
		if ($files === []) {
			echo '<p>' . esc_html__('Aucun fichier PHP/CSS/JS détecté dans le thème enfant.', 'wpcursor') . '</p>';
			return;
		}

		$recent    = array_slice($files, 0, 18);
		$all_alpha = $files;
		usort($all_alpha, static function ($a, $b) {
			return strcmp($a['relative'], $b['relative']);
		});

		self::render_theme_style_preview($child_root);

		?>
		<h3><?php esc_html_e('Fichiers modifiés récemment', 'wpcursor'); ?></h3>
		<p class="description">
			<?php esc_html_e('Tri par date de modification du fichier sur le serveur (comme un « dernier touché »). Pour savoir qui a changé quoi, utilise Git ou REPO/JOURNAL.md.', 'wpcursor'); ?>
		</p>
		<?php self::render_theme_file_table($recent, $can_edit, true); ?>

		<h3><?php esc_html_e('Tous les fichiers suivis', 'wpcursor'); ?></h3>
		<p class="description">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: file count */
					__('Extensions suivies : php, css, js, scss, less, json — %d fichier(s). Les dossiers .git, node_modules et vendor sont ignorés.', 'wpcursor'),
					count($files)
				)
			);
			?>
		</p>
		<?php self::render_theme_file_table($all_alpha, $can_edit, false); ?>
		<?php
	}

	private static function render_theme_cursor_notice(): void {
		?>
		<div class="notice notice-info inline" style="margin:12px 0;padding:12px;">
			<p style="margin:.5em 0;">
				<strong><?php esc_html_e('Chat Cursor dans WordPress', 'wpcursor'); ?></strong><br />
				<?php esc_html_e('Il n’existe pas d’API officielle pour afficher le chat Cursor dans l’admin WordPress. Pour modifier le code avec l’IA Cursor, ouvre le projet en SSH (Cursor / dossier du thème ou du plugin) comme tu fais déjà depuis ton poste.', 'wpcursor'); ?>
			</p>
			<p style="margin:.5em 0;">
				<?php esc_html_e('Cet écran te donne une vue lecture seule + dates de modification ; l’édition reste dans l’éditeur de fichiers du thème ou dans Cursor.', 'wpcursor'); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * @return list<array{relative:string,mtime:int,size:int}>
	 */
	private static function collect_child_theme_files(string $root): array {
		$root = realpath($root);
		if ($root === false) {
			return [];
		}

		$allowed_ext = ['php', 'css', 'js', 'scss', 'less', 'json'];
		$skip_dirs   = ['.git', 'node_modules', 'vendor', '.svn'];
		$out         = [];

		try {
			$dir_iterator = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
			$iterator     = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
			foreach ($iterator as $file) {
				if (! $file->isFile()) {
					continue;
				}
				$path = $file->getPathname();
				foreach ($skip_dirs as $skip) {
					if (strpos($path, DIRECTORY_SEPARATOR . $skip . DIRECTORY_SEPARATOR) !== false) {
						continue 2;
					}
				}
				$ext = strtolower($file->getExtension());
				if (! in_array($ext, $allowed_ext, true)) {
					continue;
				}
				$rel = ltrim(substr($path, strlen($root)), DIRECTORY_SEPARATOR);
				if ($rel === '') {
					continue;
				}
				$out[] = [
					'relative' => str_replace(DIRECTORY_SEPARATOR, '/', $rel),
					'mtime'    => $file->getMTime(),
					'size'     => $file->getSize(),
				];
			}
		} catch (Throwable $e) {
			return [];
		}

		usort($out, static function ($a, $b) {
			return $b['mtime'] <=> $a['mtime'];
		});

		return array_slice($out, 0, 400);
	}

	/**
	 * @param list<array{relative:string,mtime:int,size:int}> $files
	 */
	private static function render_theme_file_table(array $files, bool $can_edit, bool $compact): void {
		$stylesheet = get_stylesheet();
		?>
		<table class="widefat striped <?php echo $compact ? '' : 'wpcursor-theme-all'; ?>">
			<thead>
				<tr>
					<th><?php esc_html_e('Fichier', 'wpcursor'); ?></th>
					<th><?php esc_html_e('Taille', 'wpcursor'); ?></th>
					<th><?php esc_html_e('Dernière modification', 'wpcursor'); ?></th>
					<th><?php esc_html_e('Éditeur de thème', 'wpcursor'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($files as $row) : ?>
					<tr>
						<td><code><?php echo esc_html($row['relative']); ?></code></td>
						<td><?php echo esc_html(size_format($row['size'])); ?></td>
						<td>
							<?php
							echo esc_html(
								wp_date(
									get_option('date_format') . ' ' . get_option('time_format'),
									$row['mtime']
								)
							);
							?>
						</td>
						<td>
							<?php if ($can_edit) : ?>
								<a href="<?php echo esc_url(self::theme_editor_url($row['relative'], $stylesheet)); ?>">
									<?php esc_html_e('Ouvrir', 'wpcursor'); ?>
								</a>
							<?php else : ?>
								<span class="description"><?php esc_html_e('Édition désactivée', 'wpcursor'); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		if (! $can_edit) {
			echo '<p class="description">' . esc_html__('L’éditeur de fichiers du thème est désactivé (DISALLOW_FILE_EDIT) ou vos droits ne suffisent pas (edit_themes). Utilise Cursor en SSH ou réactive l’éditeur avec précaution.', 'wpcursor') . '</p>';
		}
	}

	private static function theme_editor_url(string $relative, string $stylesheet): string {
		return admin_url(
			'theme-editor.php?file=' . rawurlencode($relative) . '&theme=' . rawurlencode($stylesheet)
		);
	}

	private static function render_theme_style_preview(string $child_root): void {
		$style = trailingslashit($child_root) . 'style.css';
		?>
		<h3><?php esc_html_e('style.css — aperçu (fin du fichier)', 'wpcursor'); ?></h3>
		<p class="description">
			<?php esc_html_e('Souvent les ajouts manuels sont en bas du fichier (comme tes commentaires « ajout … date »). Ce bloc montre les dernières lignes — lecture seulement.', 'wpcursor'); ?>
		</p>
		<?php
		if (! is_readable($style)) {
			echo '<p>' . esc_html__('style.css introuvable ou non lisible.', 'wpcursor') . '</p>';
			return;
		}

		$raw = file_get_contents($style);
		if ($raw === false) {
			echo '<p>' . esc_html__('Impossible de lire style.css.', 'wpcursor') . '</p>';
			return;
		}

		$lines    = preg_split("/\r\n|\n|\r/", $raw);
		$total    = count($lines);
		$take     = min(120, $total);
		$tail     = array_slice($lines, -$take);
		$start_ln = max(1, $total - $take + 1);

		$mtime = filemtime($style);
		echo '<p><strong>' . esc_html__('Fichier :', 'wpcursor') . '</strong> <code>style.css</code> — ';
		echo esc_html(
			sprintf(
				/* translators: 1: line count, 2: formatted date */
				__('%1$d lignes — modifié le %2$s', 'wpcursor'),
				$total,
				wp_date(get_option('date_format') . ' ' . get_option('time_format'), $mtime)
			)
		);
		echo '</p>';

		$numbered = '';
		$ln       = $start_ln;
		foreach ($tail as $line) {
			$numbered .= sprintf("%6d\t%s\n", $ln, $line);
			$ln++;
		}
		?>
		<div style="background:#1e1e1e;color:#d4d4d4;border:1px solid #c3c4c7;padding:12px;max-height:420px;overflow:auto;font-family:Consolas,Monaco,monospace;font-size:12px;line-height:1.45;">
			<pre style="margin:0;white-space:pre-wrap;"><?php echo esc_html($numbered); ?></pre>
		</div>
		<?php
	}

	/**
	 * Historique des demandes (fichier OPERATIONS.md, alimenté manuellement ou par Cursor).
	 */
	private static function render_tab_operations(): void {
		$file = WPCURSOR_PATH . 'OPERATIONS.md';
		?>
		<h3><?php esc_html_e('Opérations effectuées', 'wpcursor'); ?></h3>
		<p class="description">
			<?php esc_html_e('Ce journal provient du fichier OPERATIONS.md dans le plugin. Le chat Cursor n’envoie rien au serveur tout seul : après chaque demande ici, on ajoute une entrée (ou Cursor le fait à la livraison). Tri : date la plus récente en premier ; à date égale, l’ordre du fichier compte. Utilise les sections Commit / Rollback (voir modèle ci‑dessous) pour garder une trace d’annulation.', 'wpcursor'); ?>
		</p>
		<?php
		if (! is_readable($file)) {
			echo '<p>' . esc_html__('Fichier OPERATIONS.md introuvable. Crée wp-content/plugins/wpcursor/OPERATIONS.md', 'wpcursor') . '</p>';
			return;
		}
		$raw = file_get_contents($file);
		if ($raw === false) {
			echo '<p>' . esc_html__('Impossible de lire OPERATIONS.md.', 'wpcursor') . '</p>';
			return;
		}

		$preamble = self::extract_operations_preamble($raw);
		if ($preamble !== '') {
			?>
			<details open style="margin:0 0 16px;border:1px solid #c3c4c7;background:#f6f7f7;padding:12px 16px;border-radius:4px;">
				<summary style="cursor:pointer;font-weight:600;">
					<?php esc_html_e('Instructions, format conseillé et préambule OPERATIONS.md', 'wpcursor'); ?>
				</summary>
				<pre style="white-space:pre-wrap;font-size:12px;line-height:1.45;margin:12px 0 0;max-height:45vh;overflow:auto;background:#fff;padding:10px;border:1px solid #dcdcde;border-radius:2px;"><?php echo esc_html($preamble); ?></pre>
			</details>
			<?php
		}

		$entries = self::parse_operations_markdown($raw);
		if ($entries === []) {
			echo '<p>' . esc_html__('Aucune entrée au format « ## AAAA-MM-JJ — Titre » trouvée. Voir le modèle en tête de OPERATIONS.md.', 'wpcursor') . '</p>';
			return;
		}

		echo '<ol class="wpcursor-operations" style="list-style:none;margin:0;padding:0;">';
		foreach ($entries as $e) {
			?>
			<li style="border:1px solid #c3c4c7;background:#fff;padding:12px 16px;margin:0 0 12px;border-radius:4px;">
				<p style="margin:0 0 8px;">
					<strong style="color:#1d2327;"><?php echo esc_html($e['date_label']); ?></strong>
					<?php if ($e['title'] !== '') : ?>
						— <span><?php echo esc_html($e['title']); ?></span>
					<?php endif; ?>
				</p>
				<?php if ($e['body'] !== '') : ?>
					<div class="wpcursor-operation-body" style="margin:0;font-size:14px;line-height:1.5;">
						<?php echo self::format_operation_body_html($e['body']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML construit avec esc_html / wp_kses dans la méthode. ?>
					</div>
				<?php endif; ?>
			</li>
			<?php
		}
		echo '</ol>';
		?>
		<p class="description">
			<?php esc_html_e('Fichier source :', 'wpcursor'); ?>
			<code>wp-content/plugins/wpcursor/OPERATIONS.md</code>
		</p>
		<?php
	}

	/**
	 * Texte avant la première entrée datée `## YYYY-MM-DD` (instructions + modèle).
	 */
	private static function extract_operations_preamble(string $raw): string {
		$raw = str_replace("\r\n", "\n", $raw);
		if (preg_match('/^(.*?)(?=^## \d{4}-\d{2}-\d{2}\s)/ms', $raw, $m)) {
			return trim($m[1]);
		}
		return '';
	}

	/**
	 * Détecte les sections Status / Composants / … / Commit / Rollback pour mise en forme admin.
	 *
	 * @return list<array{type:string,lines:list<string>}>
	 */
	private static function split_operation_body_into_blocks(string $body): array {
		$body  = trim($body);
		$lines = preg_split('/\r\n|\r|\n/', $body);
		if ($lines === false) {
			return [['type' => 'intro', 'lines' => []]];
		}

		$blocks  = [];
		$current = [
			'type'  => 'intro',
			'lines' => [],
		];

		foreach ($lines as $line) {
			if (preg_match('/^(Status|Composants|Pages|Tests|Commit|Rollback):\s*(.*)$/iu', $line, $m)) {
				if ($current['lines'] !== [] || $current['type'] !== 'intro') {
					$blocks[] = $current;
				}
				$current = [
					'type'  => strtolower($m[1]),
					'lines' => $m[2] !== '' ? [ $m[2] ] : [],
				];
				continue;
			}
			$current['lines'][] = $line;
		}

		if ($current['lines'] !== [] || $current['type'] !== 'intro') {
			$blocks[] = $current;
		}

		return $blocks !== [] ? $blocks : [['type' => 'intro', 'lines' => []]];
	}

	/**
	 * Rendu HTML du corps d’entrée : encadrés pour Commit et Rollback.
	 */
	private static function format_operation_body_html(string $body): string {
		$body = trim($body);
		if ($body === '') {
			return '';
		}

		$blocks = self::split_operation_body_into_blocks($body);
		if (count($blocks) === 1 && $blocks[0]['type'] === 'intro') {
			return wp_kses_post(wpautop(esc_html(implode("\n", $blocks[0]['lines']))));
		}

		$labels = [
			'status'     => 'Status',
			'composants' => 'Composants',
			'pages'      => 'Pages',
			'tests'      => 'Tests',
			'commit'     => 'Commit',
			'rollback'   => 'Rollback',
		];

		$html = '';
		foreach ($blocks as $block) {
			$type = $block['type'];
			$text = trim(implode("\n", $block['lines']));
			if ($text === '' && $type !== 'commit' && $type !== 'rollback') {
				continue;
			}

			$safe_text = esc_html($text);

			if ($type === 'rollback') {
				$html .= '<div class="notice notice-warning inline" style="margin:10px 0;padding:10px 12px;border-left-width:4px;"><p style="margin:0 0 6px;"><strong>' . esc_html__('Rollback', 'wpcursor') . '</strong></p>';
				$html .= '<div style="font-family:Consolas,Monaco,monospace;font-size:13px;line-height:1.45;white-space:pre-wrap;margin:0;">' . $safe_text . '</div></div>';
				continue;
			}

			if ($type === 'commit') {
				$html .= '<div class="notice notice-info inline" style="margin:10px 0;padding:10px 12px;border-left-width:4px;"><p style="margin:0 0 6px;"><strong>' . esc_html__('Commit', 'wpcursor') . '</strong></p>';
				$html .= '<div style="font-family:Consolas,Monaco,monospace;font-size:13px;line-height:1.45;white-space:pre-wrap;margin:0;">' . $safe_text . '</div></div>';
				continue;
			}

			if ($type === 'intro') {
				$html .= '<div class="wpcursor-op-intro">' . wp_kses_post(wpautop($safe_text)) . '</div>';
				continue;
			}

			$label = isset($labels[ $type ]) ? $labels[ $type ] : ucfirst($type);
			$html .= '<div class="wpcursor-op-section" style="margin:10px 0;">';
			$html .= '<p style="margin:0 0 4px;"><strong>' . esc_html($label) . '</strong></p>';
			$html .= '<div>' . wp_kses_post(wpautop($safe_text)) . '</div>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * @return list<array{ts:int,order:int,date_label:string,title:string,body:string}>
	 */
	private static function parse_operations_markdown(string $raw): array {
		$raw   = str_replace("\r\n", "\n", $raw);
		$parts = preg_split('/\n(?=## )/', $raw);
		$out   = [];
		$order = 0;

		foreach ($parts as $part) {
			$part = trim($part);
			if ($part === '' || strpos($part, '## ') !== 0) {
				continue;
			}
			$part  = substr($part, 3);
			$break = strpos($part, "\n");
			if ($break === false) {
				$headline = trim($part);
				$body     = '';
			} else {
				$headline = trim(substr($part, 0, $break));
				$body     = trim(substr($part, $break + 1));
			}

			$parsed = self::parse_operation_headline($headline);
			if ($parsed === null) {
				continue;
			}

			$out[] = [
				'ts'         => $parsed['ts'],
				'order'      => $order,
				'date_label' => $parsed['date_label'],
				'title'      => $parsed['title'],
				'body'       => $body,
			];
			$order++;
		}

		usort(
			$out,
			static function ($a, $b) {
				if ($a['ts'] !== $b['ts']) {
					return $b['ts'] <=> $a['ts'];
				}
				return $a['order'] <=> $b['order'];
			}
		);

		return $out;
	}

	/**
	 * @return ?array{ts:int,date_label:string,title:string}
	 */
	private static function parse_operation_headline(string $line): ?array {
		$line = trim($line);
		if (! preg_match('/^(\d{4}-\d{2}-\d{2})(?:\s+(\d{2}:\d{2}))?\s*[—\-]\s*(.+)$/u', $line, $m)) {
			return null;
		}

		$date_ymd = $m[1];
		$time_hm  = isset($m[2]) && $m[2] !== '' ? $m[2] : '';
		$title    = trim($m[3]);

		$ts = self::operation_ts($date_ymd, $time_hm);
		if ($ts === null) {
			return null;
		}

		return [
			'ts'         => $ts,
			'date_label' => self::format_operation_date_label($date_ymd, $time_hm, $ts),
			'title'      => $title,
		];
	}

	private static function operation_ts(string $ymd, string $hm): ?int {
		$with_time = $hm !== '' ? $ymd . ' ' . $hm : $ymd . ' 12:00';
		$ts        = strtotime($with_time);
		if ($ts === false) {
			return null;
		}
		return $ts;
	}

	private static function format_operation_date_label(string $ymd, string $hm, int $ts): string {
		$date_fmt = (string) get_option('date_format');
		if ($hm !== '') {
			return wp_date($date_fmt . ' ' . (string) get_option('time_format'), $ts);
		}
		return wp_date($date_fmt, $ts);
	}

	private static function render_tab_changelog(): void {
		$file = WPCURSOR_PATH . 'CHANGELOG.md';
		?>
		<h3><?php esc_html_e('CHANGELOG.md (dans le plugin)', 'wpcursor'); ?></h3>
		<p class="description">
			<?php esc_html_e('Cursor ou l’équipe met à jour ce fichier dans le dépôt à chaque livraison notable. Ce n’est pas une trace automatique Git : pour l’historique détaillé, utilise Git / REPO/JOURNAL.md côté serveur.', 'wpcursor'); ?>
		</p>
		<?php
		if (! is_readable($file)) {
			echo '<p>' . esc_html__('Fichier CHANGELOG.md introuvable ou non lisible.', 'wpcursor') . '</p>';
			return;
		}
		$raw = file_get_contents($file);
		if ($raw === false) {
			echo '<p>' . esc_html__('Impossible de lire CHANGELOG.md.', 'wpcursor') . '</p>';
			return;
		}
		?>
		<div style="background:#fff;border:1px solid #c3c4c7;padding:12px;max-height:70vh;overflow:auto;">
			<pre style="white-space:pre-wrap;margin:0;font-size:13px;"><?php echo esc_html($raw); ?></pre>
		</div>
		<?php
	}
}
