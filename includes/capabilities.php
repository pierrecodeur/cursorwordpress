<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Capacité dédiée : évite de réserver le plugin aux seuls comptes avec manage_options.
 *
 * @see WPCURSOR_CAP dans wpcursor.php
 */
function wpcursor_register_capabilities(): void {
	$role = get_role('administrator');
	if ($role && ! $role->has_cap(WPCURSOR_CAP)) {
		$role->add_cap(WPCURSOR_CAP);
	}
}

/**
 * Mise à jour depuis une ancienne version sans réactivation du plugin : applique la capacité une fois.
 */
function wpcursor_maybe_bootstrap_capabilities(): void {
	if (get_option('wpcursor_capabilities_v1')) {
		return;
	}
	wpcursor_register_capabilities();
	update_option('wpcursor_capabilities_v1', '1');
}

add_action('plugins_loaded', 'wpcursor_maybe_bootstrap_capabilities', 5);
