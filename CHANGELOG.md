# Journal WP Cursor

Les entrées ci-dessous peuvent être complétées à chaque intervention (Cursor / équipe). Le menu **WP Cursor** dans l’admin WordPress affiche ce fichier tel quel.

## 2026-06-03

- **v1.9.1** : **Répertoire shortcodes** unifié — recherche rapide (nom, texte, page, composant) ; fusion variantes bibliothèque + shortcodes détectés sur le site ; colonne **Pages** avec indicateur statut, liens **Éditer** / **Voir** ; badges Bibliothèque / Sur le site / Non publié ; scan complet des shortcodes bloc `[wpcursor]…[/wpcursor]`.
- **v1.9.0** : **Bibliothèque de variantes shortcode** — onglet **Variantes shortcode** : enregistrer des shortcodes nommés par composant (ex. « Pierre Bis 1 », « Pierre — page contact ») ; tableau par composant, copier / modifier / supprimer ; enregistrement depuis le **Générateur** ; chargement d’une variante dans le générateur. Aucun changement au rendu front `[wpcursor]`.
- **v1.8.5** : module Divi — champs **natifs** par composant (onglets Titres, Image, Problématiques, Profil 2/3, Avancé…) compatibles Visual Builder React ; fin du champ « mode expert » visible ; `props_content` en hidden (legacy).
- **v1.8.4** : générateur shortcode (admin + Divi) — interface à **onglets** (Titres, Image, Problématiques, Résultats, Citation, Profil 2/3, Avancé…) + **grille** responsive ; moins de défilement ; module partagé `generator-fields-ui.js`.
- **v1.8.3** : module Divi — formulaire dynamique comme le **Générateur shortcode** (champs depuis `component.json`, valeurs `divi-template.txt`, enum, URL image + médiathèque, couleurs CSS, section Avancé) ; zone texte brute repliée ; manifeste partagé `WPCursor_Generator_Manifest`.
- **v1.8.2** : module Divi Builder natif **WP Cursor** (`et_pb_wpcursor`) — sélection du composant + propriétés clé=valeur, rendu via `WPCursor_Component_Loader` (sans module Code) ; dossier **WP Cursor** dans l’insertion de modules ; Visual Builder : bouton **Charger le modèle Divi** (REST `divi_template_lines`) ; `get_divi_template_defaults()` / `format_props_lines()` centralisés dans le chargeur.

## 2026-05-28

- **v1.8.1** : générateur — bouton **Choisir dans la médiathèque** sur les champs image (`image_url`, `*_image_url`) ; aperçu miniature ; remplissage auto de `image_alt` si vide ; même aide en mode **Custom shortcode total** (insertion `image_url`).
- **v1.8.0** : générateur shortcode enrichi : mode **Assistant actuel** + mode **Custom shortcode total** ; bouton **Copier modèle Divi** ; bloc **Avancé (HTML/CSS/code)** repliable ; aide **CSS + HTML** dans le mode total (insertion auto `custom_css` / `custom_html` dans le shortcode).
- **v1.8.0** : composant **`accueil-admission`** ajouté (carrousel témoignages, navigation flèches + bullets, props `heading_tag` / `subheading_tag`, `custom_html`, `custom_css`, templates Divi, CSS/JS dédiés).
- **v1.8.0** : composant **`mission-liste`** étendu : `heading_tag`, `custom_html`, `custom_css`, support dynamique de `itemN` (au-delà de `item1..item3`), template Divi mis à jour.
- **v1.8.0** : composant **`coucou-edouard`** harmonisé avec les nouveaux standards (`text`, `custom_html`, `custom_css`, template Divi).
- **v1.8.0** : générateur admin générique : affichage de la **classe/sélecteur CSS** à côté des champs (`css_selector` dans `component.json`), picker couleur texte, génération de CSS auto en `custom_css`, bouton “ajouter un item” pour props répétables `itemN`.
- **v1.8.0** : sanitisation props renforcée dans `component-loader.php` : clés `*html*` filtrées via `wp_kses_post`, clés `*css*|*style*|*code*` via `sanitize_textarea_field` (tout en conservant `url` et `string` existants).
- **v1.8.0** : documentation composants enrichie (`components/README.md`) avec conventions `css_selector` et props répétables `item1..itemN` pour portabilité des futurs composants.

## 2026-05-10

- **v1.7.2** : invalidation auto du transient scan Utilisation (`save_post`, `deleted_post`, metas) ; **`uninstall.php`** retire **manage_wpcursor** seulement sur les rôles core (+ Woo si présents), sans supprimer rôles ni **components/** ni **CHANGELOG/OPERATIONS** ; pagination scan documentée et appliquée à **GET …/usage** (`limit` défaut **200**, max **500**, `offset`, pas de `page`) ; même limites en CLI **`wp wpcursor usage [--limit][--offset|--limit=all]`** ; doc prévisualisation admin + exemple runtime dans **components/README.md** et composant **example**.
- **v1.7.1** : `components/README.md` (conventions **component.json**) ; schéma plus strict (**type** obligatoire, **enum.values** non vide, **required** booléen), champ **description** dans le manifeste / REST ; front : props hors contrat **ignorées** dans `$wpcursor_context` + journalisation ; avertissements schéma visibles en prévisualisation admin et `wp wpcursor render` ; dossier **languages/** pour traductions.
- **v1.7.0** : API REST **lecture seule** `wpcursor/v1` (**permission_callback** `manage_wpcursor`) : `GET …/components`, `…/components/{slug}`, `…/usage`, `…/diagnostic`, `…/context/{slug}` ; WP‑CLI **`wp wpcursor validate`** [slug] (component.json, ABSPATH, assets, contrat vs usages).
- **v1.6.1** : **component.json** — champs **label**, **version**, **updated** ; ligne récap sous le slug dans l’admin (ex. « Hero — v1.0.0 — modifié le … »).
- **v1.6.0** : **`wpcursor_log()`** ; **`$wpcursor_context['runtime']`** (`front` \| `admin_preview` \| `cli`) ; **`component.json`** + **`WPCursor_Component_Schema`** (validation + colonne admin **Contrat / usage**) ; **`wp wpcursor context-site`** ; doc mise à jour.
- **v1.5.2** : politique **WooCommerce** dans **AGENTS.md** / **WP_CURSOR_PLUGIN.txt** (CRUD uniquement, pas de SQL / HPOS direct) ; **`uninstall.php`** (options `wpcursor_*`, transients, **`manage_wpcursor`**, multisite ; **ne touche pas** à `components/`) ; **`load_plugin_textdomain`** + **`languages/`** pour i18n standard WordPress.
- **v1.5.1** : **OPERATIONS.md** — format structuré recommandé (Status, Composants, Pages, Tests, **Commit**, **Rollback**) ; préambule affiché dans l’admin ; encadrés visuels pour Commit / Rollback.
- **v1.5.0** : WP‑CLI documentée et étendue — **`wp wpcursor list`**, **`usage`** (filtre slug optionnel), **`diagnose`**, **`render`** (props `--cle=valeur`), **`flush-cache`**, **`context`** (bloc copier-coller Cursor) ; classe **`WPCursor_Usage_Scan`** (`usage-scan.php`) ; onglet admin **Prévisualiser** (`tab=preview`, nonce, props de test, assets **wpcursor-cpt-***, alertes PHP admin).
- Documentation REPO : **README.md** (état V1.4), **ROADMAP.md** (futur uniquement), **AGENTS.md** (IA) ; **WP_CURSOR_PLUGIN.txt** nettoyé (suppression de l’ancienne feuille de route V1.3 / items déjà livrés).
- Garde **ABSPATH** normalisée sur tous les PHP du plugin ; exemple déplacé en **components/example/component.php** ; doc **components/README.md** + WP_CURSOR_PLUGIN.txt.
- v1.4.1 : capacité dédiée **`manage_wpcursor`** (remplace `manage_options` pour le menu et les garde-fous) ; attribution au rôle **administrator** à l’activation et migration au premier chargement pour les sites déjà actifs.
- v1.4.0 : chargeur **components/{slug}/component.php** (prioritaire) ou **slug.php** (legacy) ; variable **`$wpcursor_context`** (`name`, `props`, `mode`) ; **props** du shortcode sanitées ; **enqueue** optionnel `style.css` / `script.js` par dossier composant ; onglet **Diagnostic** ; onglet **Utilisation** enrichi (sources post/meta, composants extraits, lien front, **Copier contexte Cursor**) ; cache scan `wpcursor_usage_scan_v2` ; **WP‑CLI** `wp wpcursor components` et `wp wpcursor diagnose`.
- v1.3.0 : onglet **Opérations effectuées** — lecture de `OPERATIONS.md`, tri du plus récent au plus ancien (historique des demandes Cursor à documenter dans ce fichier).
- v1.2.0 : onglet **Thème enfant** — liste des fichiers du thème actif (dates, taille), aperçu des **dernières lignes de style.css**, liens vers l’éditeur de thème WordPress si autorisé ; encadré sur l’impossibilité d’intégrer le chat Cursor dans l’admin.
- v1.1.0 : page d’admin (menu **WP Cursor**) : onglets Composants, Utilisation sur le site, Journal (`CHANGELOG.md`).
- v1.0.0 : shortcodes `[wpcursor]` / `[site_component]`, dossier `components/`.
