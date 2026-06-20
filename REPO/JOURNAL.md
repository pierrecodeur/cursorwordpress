# Journal des interventions

## Format d'entree

```md
### YYYY-MM-DD HH:MM (TZ) - Auteur
- Objectif:
- Actions:
  - ...
- Fichiers modifies:
  - ...
- Verification:
  - ...
- Impact/risque:
  - ...
- Rollback:
  - snapshot/backup utilise: ...
```

---

### 2026-05-09 16:07 (UTC+2) - Agent
- Objectif: initialiser une base documentaire pour comprendre l'instance WordPress Divi et preparer les rollbacks.
- Actions:
  - cartographie de la structure WordPress (coeur, theme, plugins, cache);
  - identification des composants critiques (Divi/divi-child, WP Rocket, drop-in cache);
  - creation du dossier `guide` et des fichiers standards d'exploitation.
- Fichiers modifies:
  - `guide/README.md`
  - `guide/INVENTAIRE.md`
  - `guide/ROLLBACK.md`
  - `guide/JOURNAL.md`
- Verification:
  - coherence de la structure documentaire;
  - absence volontaire de secrets en clair dans les fichiers `guide`.
- Impact/risque:
  - aucun impact runtime (documentation uniquement).
- Rollback:
  - non necessaire pour cette intervention (pas de code applicatif modifie).

### 2026-05-09 16:10 (UTC+2) - Agent
- Objectif: renommer le dossier documentaire de `guide` vers `REPO`.
- Actions:
  - recreation des fichiers documentaires sous `REPO/`;
  - conservation du contenu et de l'historique.
- Fichiers modifies:
  - `REPO/README.md`
  - `REPO/INVENTAIRE.md`
  - `REPO/ROLLBACK.md`
  - `REPO/JOURNAL.md`
- Verification:
  - presence des 4 fichiers dans `REPO/`.
- Impact/risque:
  - aucun impact runtime (documentation uniquement).
- Rollback:
  - possible en recopiant les fichiers vers `guide/` si necessaire.

### 2026-05-09 16:15 (UTC+2) - Agent
- Objectif: preparer une intervention sur `style.css` sans toucher au fichier source.
- Actions:
  - localisation du fichier actif `wp-content/themes/divi-child/style.css`;
  - creation d'un backup horodate dans `REPO/backups/`;
  - verification d'integrite par hash SHA-256 (source = backup);
  - pre-analyse de qualite (presence de blocs imbriques SCSS-like, repetitions);
  - redaction d'un plan de refactor non execute.
- Fichiers modifies:
  - `REPO/backups/style.css.backup-20260509-1615.css`
  - `REPO/STYLECSS_PREPARATION.md`
  - `REPO/JOURNAL.md`
- Verification:
  - hash identique entre source et backup;
  - aucune modification de `wp-content/themes/divi-child/style.css`.
- Impact/risque:
  - aucun impact runtime (backup + documentation uniquement).
- Rollback:
  - restaurer `REPO/backups/style.css.backup-20260509-1615.css` vers `wp-content/themes/divi-child/style.css`.

---

### 2026-05-10 — WP Cursor v1.7.2 (cache usage auto, uninstall ciblé, pagination scan)
- Transient scan invalidé sur save/metas ; uninstall retire la cap. sur rôles connus seulement ; REST/CLI **limit/offset** ; doc preview.

### 2026-05-10 — WP Cursor v1.7.1 (README component.json + schéma strict + languages/)
- `components/README.md` (modèle + mode strict) ; type obligatoire / enum / required booléen ; `description` ; front filtre les props hors contrat ; dossier `languages/`.

### 2026-05-10 — WP Cursor v1.7.0 (REST read-only + wp wpcursor validate)
- REST `wp-json/wpcursor/v1/*` ; commande `validate` ; doc mise à jour.

### 2026-05-10 — WP Cursor v1.6.0 (runtime, logs, component.json, context-site)
- Objectif: debug traçable, distinction front/preview/cli, contrat JSON optionnel, export contexte site pour Cursor.
- Fichiers principaux: `includes/logger.php`, `component-schema.php`, évolutions `component-loader`, `admin`, `usage-scan`, `cli`, `wpcursor.php`, doc REPO.

### 2026-05-10 — WP Cursor v1.5.1 (OPERATIONS commit / rollback visibles)
- Objectif: format de livraison traçable avec rollback ; affichage renforcé dans l’admin.
- Fichiers: `wp-content/plugins/wpcursor/OPERATIONS.md`, `includes/admin.php`, doc REPO, version **1.5.1**.

### 2026-05-10 — WP Cursor v1.5.0 (CLI + prévisualisation)
- Objectif: commandes WP‑CLI documentées et alignées workflow SSH/Cursor ; onglet admin prévisualisation sécurisé.
- Actions:
  - ajout `includes/usage-scan.php`, extension `includes/cli.php` (list, usage, render, flush-cache, context, etc.) ;
  - onglet **Prévisualiser** dans `admin.php` ; version plugin **1.5.0** ;
  - mise à jour `WP_CURSOR_PLUGIN.txt`, `CHANGELOG.md`, `ROADMAP.md`, `README.md`, `AGENTS.md`.
- Fichiers modifiés: plugin `wpcursor/` (voir CHANGELOG), fichiers REPO ci‑dessus.

### 2026-05-10 — Documentation WP Cursor (REPO)
- Objectif: séparer état actuel, roadmap future et consignes IA pour éviter les contradictions (V1.3 vs V1.4).
- Actions:
  - création `REPO/ROADMAP.md` et `REPO/AGENTS.md` ;
  - mise à jour `REPO/README.md` (section plugin V1.4 + tableau des docs) ;
  - réécriture fin de `REPO/WP_CURSOR_PLUGIN.txt` (suppression ancienne roadmap, renvois vers les nouveaux fichiers).
- Fichiers modifiés ou créés:
  - `REPO/README.md`, `REPO/ROADMAP.md`, `REPO/AGENTS.md`, `REPO/WP_CURSOR_PLUGIN.txt`
  - `wp-content/plugins/wpcursor/CHANGELOG.md`
- Vérification: cohérence des chemins relatifs depuis `REPO/` vers `wp-content/plugins/wpcursor/`.
