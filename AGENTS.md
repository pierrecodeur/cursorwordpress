# AGENTS.md

Ce dépôt est le plugin WordPress **WP Cursor** (`wpcursor`). Pour les consignes produit/code, voir `REPO/AGENTS.md` et `REPO/WP_CURSOR_PLUGIN.txt`.

## Cursor Cloud specific instructions

Ce dépôt n'est qu'un **plugin WordPress** : il n'y a ni gestionnaire de paquets, ni étape de build, ni suite de tests automatisés. Pour l'exécuter il faut un hôte WordPress, déjà provisionné dans le snapshot de la VM (hors dépôt).

- **Hôte WordPress** : installé dans `~/wp` (WordPress + WP-CLI), base de données **SQLite** via le drop-in `~/wp/wp-content/db.php` — donc **aucun service MySQL à démarrer**. Le dépôt `/workspace` est monté dans WordPress via le lien symbolique `~/wp/wp-content/plugins/wpcursor -> /workspace` (recréé par l'update script).
- **Lancer le site (dev)** : `cd ~/wp && wp server --host=0.0.0.0 --port=8088` (serveur PHP intégré). Front : `http://localhost:8088/`. Admin : `http://localhost:8088/wp-login.php` avec `admin` / `admin123`. Le menu **WP Cursor** apparaît dans l'admin (capability `manage_wpcursor`).
- **Les commandes WP-CLI se lancent depuis `~/wp`**, pas depuis `/workspace`. Ex. : `wp wpcursor list`, `wp wpcursor diagnose`, `wp wpcursor validate`. Liste complète dans `REPO/WP_CURSOR_PLUGIN.txt` (section *COMMANDES WP-CLI*).
- **Lint** : `php -l` sur les fichiers PHP (pas de PHPCS/PHPStan configuré). **Tests** : pas de suite automatisée — utiliser `wp wpcursor validate` (component.json, garde ABSPATH, assets) comme harnais de vérification.
- **Rendu d'un composant** : utiliser le shortcode `[wpcursor name="slug"]` dans une page, ou l'onglet **Prévisualiser** de l'admin. ⚠️ `wp wpcursor render <slug>` échoue (« invalid synopsis part `[--<prop>=<value>]` » → « Too many positional arguments ») à cause d'une particularité de parsing de synopsis WP-CLI ; préférer le shortcode / l'onglet Prévisualiser.
- **Divi non installé** : `wp wpcursor diagnose` affiche `Divi : non garanti` — c'est attendu. Le cœur du plugin (shortcodes, admin, CLI) fonctionne sans Divi ; seules les fonctions liées au module Divi ne sont pas exerçables tant que le thème Divi n'est pas présent.
- **Logs debug** : `WP_DEBUG`, `WP_DEBUG_LOG` et `WPCURSOR_DEBUG` sont activés dans `~/wp/wp-config.php` → messages préfixés `[WP Cursor]` dans `~/wp/wp-content/debug.log`.
