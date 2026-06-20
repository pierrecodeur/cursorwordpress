<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Active les logs fichier sans activer tout WP_DEBUG : dans wp-config.php —
 * define('WPCURSOR_DEBUG', true);
 */
function wpcursor_should_log(): bool {
	if (defined('WPCURSOR_DEBUG') && WPCURSOR_DEBUG) {
		return true;
	}
	return defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('WP_DEBUG') && WP_DEBUG;
}

/**
 * Écrit une ligne dans le log PHP (souvent wp-content/debug.log si WP_DEBUG_LOG).
 *
 * Préfixe systématique : [WP Cursor]
 *
 * @param array<string, mixed> $context Données JSON compactes en fin de ligne (optionnel).
 */
function wpcursor_log(string $message, array $context = []): void {
	if (! wpcursor_should_log()) {
		return;
	}
	$line = '[WP Cursor] ' . $message;
	if ($context !== []) {
		$line .= ' ' . wp_json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}
	error_log($line);
}
