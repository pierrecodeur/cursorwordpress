<?php
/**
 * Désinstallation du plugin WP Cursor.
 *
 * Nettoie uniquement les données techniques du site : options `wpcursor_*`, transients `wpcursor_*`,
 * et retire **manage_wpcursor** des rôles WordPress « connus » (core + WooCommerce si présents).
 *
 * Ne supprime **jamais** :
 * - le dossier **components/** ni les PHP/CSS/JS qu’il contient ;
 * - **CHANGELOG.md** ni **OPERATIONS.md** ;
 * - aucun rôle utilisateur (seules des capabilities sont retirées là où le plugin les avait typiquement ajoutées).
 *
 * Les **rôles personnalisés** qui auraient reçu **manage_wpcursor** manuellement **conservent** cette capability
 * après désinstallation : à retirer à la main dans les réglages du site si besoin.
 *
 * @package WPCursor
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

/**
 * Rôles dont on retire manage_wpcursor (évite de toucher aux rôles métier inconnus du plugin).
 *
 * @return list<string>
 */
function wpcursor_uninstall_roles_to_strip_cap(): array {
	$core = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
	foreach (['shop_manager', 'customer'] as $woo) {
		if (get_role($woo)) {
			$core[] = $woo;
		}
	}
	return array_values(array_unique($core));
}

/**
 * Supprime les données du plugin pour le site courant (blog).
 */
function wpcursor_plugin_uninstall_cleanup(): void {
	global $wpdb;

	// Options au préfixe wpcursor_ (ex. wpcursor_capabilities_v1).
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- désinstallation unique.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like('wpcursor_') . '%'
		)
	);

	// Transients nommés wpcursor_* (y compris timeouts).
	$like     = $wpdb->esc_like('_transient_wpcursor_') . '%';
	$like_to  = $wpdb->esc_like('_transient_timeout_wpcursor_') . '%';
	$like_st  = $wpdb->esc_like('_site_transient_wpcursor_') . '%';
	$like_sto = $wpdb->esc_like('_site_transient_timeout_wpcursor_') . '%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
			$like,
			$like_to,
			$like_st,
			$like_sto
		)
	);

	$cap = 'manage_wpcursor';
	foreach (wpcursor_uninstall_roles_to_strip_cap() as $role_name) {
		$role = get_role($role_name);
		if ($role && $role->has_cap($cap)) {
			$role->remove_cap($cap);
		}
	}
}

if (is_multisite()) {
	$site_ids = get_sites(['fields' => 'ids', 'number' => 0]);
	foreach ($site_ids as $site_id) {
		switch_to_blog((int) $site_id);
		wpcursor_plugin_uninstall_cleanup();
		restore_current_blog();
	}
} else {
	wpcursor_plugin_uninstall_cleanup();
}
