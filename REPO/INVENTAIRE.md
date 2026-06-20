# Inventaire technique

## Contexte

- Type: installation WordPress complete.
- Racine projet: `httpdocs/divi`.
- Objectif: maintenir un socle fiable pour maintenance, migration et rollback.

## Structure principale

- `wp-admin/`
- `wp-includes/`
- `wp-content/`
- `wp-config.php`

## Theme

- Theme parent detecte: `wp-content/themes/Divi`
- Theme enfant detecte: `wp-content/themes/divi-child` (template parent: Divi)

## Plugins custom

- `wp-content/plugins/wpcursor/` : WP Cursor — shortcodes `[wpcursor name="..."]` chargeant des partials PHP dans `components/` (versionnement Git / Cursor).

## Performance et cache

- WP Rocket present (`wp-content/plugins/wp-rocket/`)
- Drop-in cache actif (`wp-content/advanced-cache.php`)
- Dossier cache present (`wp-content/cache/wp-rocket/`)

## Configuration importante (sans secrets)

- `wp-config.php` contient:
  - des identifiants base de donnees;
  - des cles/salts WordPress;
  - des parametres de performance (`WP_CACHE`, memoire, revisions);
  - un routage conditionnel selon `HTTP_HOST`;
  - `DISABLE_WP_CRON` a `true`.

## Risques techniques a surveiller

- Secrets presents dans la configuration: ne jamais exposer, rotation recommandee en cas de fuite.
- Caches volumineux: a exclure des sauvegardes longues duree.
- Configuration dependante de l'environnement (`HTTP_HOST`): risque de mauvaise base cible.
- `DISABLE_WP_CRON`: exige un cron systeme correctement configure.
