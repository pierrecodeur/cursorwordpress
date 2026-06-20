# Consignes pour Cursor et assistants IA (WP Cursor)

Utiliser ce fichier comme **référence courte** ; le détail technique reste dans `WP_CURSOR_PLUGIN.txt` et le code du plugin.

## Ce qu’est WP Cursor

- Plugin WordPress maison : **`wp-content/plugins/wpcursor/`**.
- Il expose des **composants PHP versionnés** via shortcodes **`[wpcursor name="slug" …]`** (alias **`[site_component]`**).
- **Ce n’est pas** l’application Cursor : **pas d’API Cursor**, **pas de chat IA** dans le tableau de bord WordPress.

## Rôles

- **Divi** : mise en page et contenu éditorial en base (`post_content`, etc.).
- **WP Cursor** : fichiers PHP dans `components/`, logique réutilisable, admin d’inventaire et de diagnostic.

## Sécurité et bonnes pratiques

- **Capability** : accès menu et actions sensibles → **`manage_wpcursor`** (pas seulement `manage_options`).
- **Chaque fichier PHP** du plugin et des composants : garde **`if (!defined('ABSPATH')) { exit; }`** en tête.
- **Rendu HTML dans les composants** : tout échapper (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`, etc.) — voir `wp-content/plugins/wpcursor/components/README.md`.

## Contenu Divi / bases

- Pour modifier du texte ou du HTML géré par Divi, identifier le bon **`post_id`** (souvent confusion page parent / enfant).

## WooCommerce (si le plugin ou un composant y touche un jour)

WP Cursor **ne remplace pas** WooCommerce. Si une évolution manipule des données WooCommerce :

- **Ne jamais** modifier commandes, produits, clients ou métas liées en **SQL direct**.
- Utiliser les **APIs et objets CRUD** WooCommerce : `WC_Order`, `WC_Product`, `WC_Customer`, stores / repositories exposés par WooCommerce, ou l’**API REST WooCommerce** selon le besoin.
- Avec **HPOS** (commandes en tables dédiées) : **ne pas** interroger ou modifier les tables HPOS à la main ; passer par les abstractions WooCommerce (data stores, CRUD), comme le recommande la documentation officielle.

## Fichiers de suivi (ne pas confondre)

| Fichier | Rôle |
|--------|------|
| `wp-content/plugins/wpcursor/CHANGELOG.md` | Versions techniques (affiché dans l’admin). |
| `wp-content/plugins/wpcursor/OPERATIONS.md` | Historique des livraisons (sections `##`) ; inclure **Commit** et **Rollback** pour traçabilité (voir modèle en tête du fichier). |
| `REPO/ROADMAP.md` | **Futur** uniquement. |
| `REPO/README.md` | Vue d’ensemble du dépôt REPO + état actuel du plugin. |

## WP‑CLI (SSH)

Référence : **`REPO/WP_CURSOR_PLUGIN.txt`** — section *COMMANDES WP‑CLI*. En résumé :  
`wp wpcursor list`, `usage` [slug], `diagnose`, **`validate`** [slug], `render`, `flush-cache`, `context` slug, **`context-site`**.

## API REST (lecture seule)

- Préfixe : **`/wp-json/wpcursor/v1/`** — uniquement **GET** ; pas d’écriture commandes/pages/base.
- Chaque route a un **`permission_callback`** : compte connecté + capability **`manage_wpcursor`** (sinon 401/403).
- Voir **`WP_CURSOR_PLUGIN.txt`** pour la liste des chemins (**components**, **usage**, **diagnostic**, **context/{slug}**).

## Debug et contexte rendu

- **`wpcursor_log($message, $context)`** : préfixe **`[WP Cursor]`** dans `debug.log` si `WP_DEBUG_LOG` + `WP_DEBUG`, ou **`define('WPCURSOR_DEBUG', true);`** dans `wp-config.php`.
- Dans les composants, **`$wpcursor_context['runtime']`** vaut **`front`**, **`admin_preview`** ou **`cli`** — pour désactiver tracking / animations hors front.
- **`components/{slug}/component.json`** : schéma props optionnel (voir `components/README.md`) ; erreurs loguées et résumées dans l’admin (**Contrat / usage**).

## Internationalisation (léger)

- Text domain **`wpcursor`** : chaînes visibles dans l’admin passent par **`esc_html__()` / `esc_html_e()`** (et équivalents) avec ce domaine.
- **`load_plugin_textdomain`** est appelé dans `wpcursor.php` ; les fichiers `.mo` peuvent être placés dans **`wp-content/plugins/wpcursor/languages/`** si tu traduis pour un client.

Ne pas committer de secrets (mots de passe, clés API) dans la documentation.
