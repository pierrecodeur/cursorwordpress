<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Historique local "type Git" sans Git :
 * - crée REPO/ à l'installation,
 * - prend des snapshots des fichiers composants modifiés,
 * - permet un rollback fichier par fichier.
 */
final class WPCursor_Repo_History {
	private const OPTION_INDEX  = 'wpcursor_repo_index_v1';
	private const OPTION_EVENTS = 'wpcursor_repo_events_v1';
	private const OPTION_READY  = 'wpcursor_repo_install_ready_v1';
	private const EVENTS_LIMIT  = 250;

	public static function bootstrap(): void {
		self::ensure_repo_layout();
		self::ensure_install_seed();

		if (is_admin()) {
			add_action('admin_init', [self::class, 'capture_changes']);
		}
	}

	public static function run_install_setup(): void {
		self::ensure_repo_layout();
		self::ensure_install_seed();
		self::capture_changes();
	}

	/**
	 * @return array<string, string>
	 */
	public static function admin_summary(): array {
		return [
			'repo_root'            => self::repo_root_dir(),
			'history_dir'          => self::history_dir(),
			'plugin_backups_dir'   => self::repo_backups_plugin_dir(),
			'theme_backups_dir'    => self::repo_backups_theme_dir(),
			'latest_plugin_backup' => self::latest_backup_stamp(self::repo_backups_plugin_dir()),
			'latest_theme_backup'  => self::latest_backup_stamp(self::repo_backups_theme_dir()),
		];
	}

	public static function ensure_repo_layout(): void {
		$repo = self::repo_root_dir();
		if (! is_dir($repo)) {
			wp_mkdir_p($repo);
		}
		if (! is_dir(self::repo_backups_dir())) {
			wp_mkdir_p(self::repo_backups_dir());
		}
		if (! is_dir(self::repo_backups_plugin_dir())) {
			wp_mkdir_p(self::repo_backups_plugin_dir());
		}
		if (! is_dir(self::repo_backups_theme_dir())) {
			wp_mkdir_p(self::repo_backups_theme_dir());
		}
		if (! is_dir(self::repo_plugin_notes_dir())) {
			wp_mkdir_p(self::repo_plugin_notes_dir());
		}
		if (! is_dir(self::repo_theme_notes_dir())) {
			wp_mkdir_p(self::repo_theme_notes_dir());
		}
		if (! is_dir(self::history_dir())) {
			wp_mkdir_p(self::history_dir());
		}
		if (! is_dir(self::snapshots_dir())) {
			wp_mkdir_p(self::snapshots_dir());
		}
		self::ensure_repo_files();
	}

	public static function capture_changes(): void {
		// Évite un scan filesystem trop fréquent.
		$last = (int) get_transient('wpcursor_repo_capture_ts');
		if ($last > 0 && (time() - $last) < 30) {
			return;
		}
		set_transient('wpcursor_repo_capture_ts', time(), MINUTE_IN_SECONDS);

		$inventory = WPCursor_Component_Loader::inventory();
		$current   = [];

		foreach ($inventory as $row) {
			$relative = ltrim((string) $row['path'], '/');
			$abs      = WPCURSOR_PATH . $relative;
			if (! is_readable($abs) || ! is_file($abs)) {
				continue;
			}

			$hash = sha1_file($abs);
			if (! is_string($hash) || $hash === '') {
				continue;
			}

			$current[ $relative ] = [
				'hash' => $hash,
				'mtime' => (int) filemtime($abs),
				'slug' => (string) $row['slug'],
			];
		}

		$previous = get_option(self::OPTION_INDEX, []);
		if (! is_array($previous)) {
			$previous = [];
		}

		$events = self::get_events();
		$now    = time();

		foreach ($current as $relative => $meta) {
			$prev = $previous[ $relative ] ?? null;
			$changed = ! is_array($prev) || ! isset($prev['hash']) || (string) $prev['hash'] !== (string) $meta['hash'];
			if (! $changed) {
				continue;
			}

			$snapshot_rel = self::create_snapshot($relative, $now);
			if ($snapshot_rel === '') {
				continue;
			}

			$events[] = [
				'id'          => wp_generate_uuid4(),
				'ts'          => $now,
				'action'      => is_array($prev) ? 'updated' : 'created',
				'slug'        => (string) $meta['slug'],
				'path'        => $relative,
				'hash'        => (string) $meta['hash'],
				'prev_hash'   => is_array($prev) && isset($prev['hash']) ? (string) $prev['hash'] : '',
				'snapshot'    => $snapshot_rel,
			];
		}

		foreach ($previous as $relative => $prev_meta) {
			if (isset($current[ $relative ])) {
				continue;
			}
			$events[] = [
				'id'        => wp_generate_uuid4(),
				'ts'        => $now,
				'action'    => 'deleted',
				'slug'      => is_array($prev_meta) && isset($prev_meta['slug']) ? (string) $prev_meta['slug'] : '',
				'path'      => (string) $relative,
				'hash'      => '',
				'prev_hash' => is_array($prev_meta) && isset($prev_meta['hash']) ? (string) $prev_meta['hash'] : '',
				'snapshot'  => '',
			];
		}

		update_option(self::OPTION_INDEX, $current, false);
		update_option(self::OPTION_EVENTS, self::trim_events($events), false);
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	public static function get_events(): array {
		$events = get_option(self::OPTION_EVENTS, []);
		return is_array($events) ? array_values($events) : [];
	}

	public static function rollback_event(string $event_id): bool {
		$event_id = trim($event_id);
		if ($event_id === '') {
			return false;
		}
		$events = self::get_events();
		foreach ($events as $event) {
			if (! is_array($event) || (($event['id'] ?? '') !== $event_id)) {
				continue;
			}
			$snapshot_rel = isset($event['snapshot']) ? (string) $event['snapshot'] : '';
			$target_rel   = isset($event['path']) ? (string) $event['path'] : '';
			if ($snapshot_rel === '' || $target_rel === '') {
				return false;
			}
			$snapshot_abs = self::history_dir() . ltrim($snapshot_rel, '/');
			$target_abs   = WPCURSOR_PATH . ltrim($target_rel, '/');
			$target_dir   = dirname($target_abs);
			if (! is_readable($snapshot_abs)) {
				return false;
			}
			if (! is_dir($target_dir)) {
				wp_mkdir_p($target_dir);
			}
			$ok = copy($snapshot_abs, $target_abs);
			if (! $ok) {
				return false;
			}
			self::capture_changes();
			return true;
		}
		return false;
	}

	private static function create_snapshot(string $relative_file, int $ts): string {
		$source = WPCURSOR_PATH . ltrim($relative_file, '/');
		if (! is_readable($source)) {
			return '';
		}
		$stamp      = gmdate('Ymd-His', $ts);
		$folder_rel = 'snapshots/' . $stamp . '/' . trim(dirname($relative_file), '/');
		$dest_dir   = self::history_dir() . $folder_rel;
		if (! is_dir($dest_dir)) {
			wp_mkdir_p($dest_dir);
		}
		$dest_file = trailingslashit($dest_dir) . basename($relative_file);
		if (! copy($source, $dest_file)) {
			return '';
		}
		return $folder_rel . '/' . basename($relative_file);
	}

	private static function repo_root_dir(): string {
		return trailingslashit(WPCURSOR_PATH) . 'REPO';
	}

	private static function repo_backups_dir(): string {
		return trailingslashit(self::repo_root_dir()) . 'backups/';
	}

	private static function repo_backups_plugin_dir(): string {
		return trailingslashit(self::repo_backups_dir()) . 'plugin/';
	}

	private static function repo_backups_theme_dir(): string {
		return trailingslashit(self::repo_backups_dir()) . 'theme/';
	}

	private static function repo_plugin_notes_dir(): string {
		return trailingslashit(self::repo_root_dir()) . 'plugin/';
	}

	private static function repo_theme_notes_dir(): string {
		return trailingslashit(self::repo_root_dir()) . 'theme/';
	}

	private static function history_dir(): string {
		return trailingslashit(self::repo_root_dir()) . 'wpcursor-history/';
	}

	private static function snapshots_dir(): string {
		return trailingslashit(self::history_dir()) . 'snapshots/';
	}

	/**
	 * @param list<array<string,mixed>> $events
	 *
	 * @return list<array<string,mixed>>
	 */
	private static function trim_events(array $events): array {
		if (count($events) <= self::EVENTS_LIMIT) {
			return $events;
		}
		return array_slice($events, -self::EVENTS_LIMIT);
	}

	private static function ensure_install_seed(): void {
		$ready = get_option(self::OPTION_READY, '0');
		if ($ready === '1') {
			return;
		}

		self::seed_docs_from_site_repo();
		self::create_install_backups();

		update_option(self::OPTION_READY, '1', false);
	}

	private static function ensure_repo_files(): void {
		$htaccess = trailingslashit(self::repo_root_dir()) . '.htaccess';
		if (! file_exists($htaccess)) {
			$content = "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
			file_put_contents($htaccess, $content);
		}

		$readme = trailingslashit(self::repo_root_dir()) . 'README.md';
		if (! file_exists($readme)) {
			$txt = "# REPO WP Cursor\n\nDossier local généré par le plugin à l'installation.\n- backups/plugin : sauvegarde initiale du plugin\n- backups/theme : sauvegarde initiale du thème enfant\n- wpcursor-history : historique des composants et rollbacks\n";
			file_put_contents($readme, $txt);
		}

		$plugin_readme = trailingslashit(self::repo_plugin_notes_dir()) . 'README.md';
		if (! file_exists($plugin_readme)) {
			file_put_contents($plugin_readme, "Sauvegardes et notes liées au plugin WP Cursor.\n");
		}

		$theme_readme = trailingslashit(self::repo_theme_notes_dir()) . 'README.md';
		if (! file_exists($theme_readme)) {
			file_put_contents($theme_readme, "Sauvegardes et notes liées au thème enfant actif.\n");
		}
	}

	private static function seed_docs_from_site_repo(): void {
		$site_repo = trailingslashit(ABSPATH) . 'REPO/';
		if (! is_dir($site_repo)) {
			return;
		}
		$files = [
			'WP_CURSOR_PLUGIN.txt',
			'AGENTS.md',
			'INVENTAIRE.md',
			'JOURNAL.md',
			'ROLLBACK.md',
			'STYLECSS_PREPARATION.md',
			'ROADMAP.md',
			'README.md',
		];
		foreach ($files as $name) {
			$src = $site_repo . $name;
			$dst = trailingslashit(self::repo_root_dir()) . $name;
			if (! is_readable($src) || file_exists($dst)) {
				continue;
			}
			copy($src, $dst);
		}
	}

	private static function create_install_backups(): void {
		$stamp = gmdate('Ymd-His');
		$plugin_target = trailingslashit(self::repo_backups_plugin_dir()) . $stamp . '/';
		$theme_target  = trailingslashit(self::repo_backups_theme_dir()) . $stamp . '/';

		self::copy_tree(WPCURSOR_PATH, $plugin_target, ['REPO', '.git', 'node_modules', 'vendor']);

		$theme_root = get_stylesheet_directory();
		if (is_dir($theme_root)) {
			self::copy_tree($theme_root, $theme_target, ['.git', 'node_modules', 'vendor']);
		}
	}

	/**
	 * @param list<string> $exclude_dirs
	 */
	private static function copy_tree(string $source_root, string $target_root, array $exclude_dirs): void {
		if (! is_dir($source_root)) {
			return;
		}
		if (! is_dir($target_root)) {
			wp_mkdir_p($target_root);
		}

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($source_root, FilesystemIterator::SKIP_DOTS),
				RecursiveIteratorIterator::SELF_FIRST
			);
		} catch (Throwable $e) {
			return;
		}

		$source_root = trailingslashit(realpath($source_root) ?: $source_root);
		foreach ($iterator as $item) {
			$src = $item->getPathname();
			$rel = ltrim(str_replace('\\', '/', substr($src, strlen($source_root))), '/');
			if ($rel === '') {
				continue;
			}

			$skip = false;
			foreach ($exclude_dirs as $ex) {
				$ex = trim((string) $ex, '/');
				if ($ex !== '' && (strpos($rel, $ex . '/') === 0 || $rel === $ex)) {
					$skip = true;
					break;
				}
			}
			if ($skip) {
				continue;
			}

			$dst = trailingslashit($target_root) . $rel;
			if ($item->isDir()) {
				if (! is_dir($dst)) {
					wp_mkdir_p($dst);
				}
				continue;
			}
			$dst_dir = dirname($dst);
			if (! is_dir($dst_dir)) {
				wp_mkdir_p($dst_dir);
			}
			copy($src, $dst);
		}
	}

	private static function latest_backup_stamp(string $dir): string {
		if (! is_dir($dir)) {
			return '';
		}
		$dirs = glob(trailingslashit($dir) . '*', GLOB_ONLYDIR);
		if (! is_array($dirs) || $dirs === []) {
			return '';
		}
		usort(
			$dirs,
			static function ($a, $b) {
				return strcmp((string) $b, (string) $a);
			}
		);
		$latest = basename((string) $dirs[0]);
		return $latest !== '' ? $latest : '';
	}
}
