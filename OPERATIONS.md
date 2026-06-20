# Opérations effectuées

Ce fichier alimente l’onglet **Opérations effectuées** dans WP Cursor. WordPress ne reçoit pas l’historique du chat Cursor automatiquement.

## Règle de rédaction

À **chaque livraison** qui répond à une demande (Cursor ou à la main), ajouter une entrée `## YYYY-MM-DD — Titre` **au début du bloc d’entrées datées** (juste après ce préambule), avec autant de détail que nécessaire.

## 2026-06-18 — GRW : avis Google filtrés par page (cocons)

Status: livré

Fichiers:
- wp-content/mu-plugins/cours-thales-grw-contextual.php (nouveau — filtre par URL / slug de page)
- wp-content/plugins/widget-google-reviews/includes/core/class-core.php (hook `grw_feed_data`)

Règles:
- `/prepa-medecine/` → mots-clés : médecine, pass
- `/post-bac-ingenieur/` → ingénieur, avenir, puissance, advance, geipi
- `/post-bac-commerce/` → concours accès, sésame, commerce
- `/prepa-sciences-po/` → sciences po, science po, iep, concours commun
- Autres pages → tous les avis (comportement inchangé)
- Pages enfants d’un cocon → même filtre que le parent

Pages:
- Widget `[grw id=4492058]` sur les cocons concernés

Tests:
- `/prepa-medecine/` : « Basé sur 3 avis », slider médecine uniquement
- Vider cache WP Rocket après déploiement
- `/post-bac-ingenieur/` et `/post-bac-commerce/` : pas de widget GRW actuellement (seulement badge footer BRB)

Commit:
- (à renseigner)

Rollback:
- Supprimer `mu-plugins/cours-thales-grw-contextual.php`
- Retirer `apply_filters('grw_feed_data', …)` dans `class-core.php`

## 2026-06-18 — question-prepa : correction puces parasites sur la liste d’avantages

Status: livré

Composants:
- wp-content/plugins/wpcursor/components/question-prepa/component.php (classe `unstyled` sur les listes)
- wp-content/plugins/wpcursor/components/question-prepa/style.css (neutralisation `::before` thème Divi child, reset `padding-left`, centrage mobile)

Pages:
- Toute page utilisant le composant **question-prepa** (ex. cocon prépa Sciences Po)

Tests:
- Desktop : coches seules, sans puces noires superposées
- Mobile : liste avantages lisible, alignement corrigé
- Vider le cache WP Rocket si CSS minifié en cache

Commit:
- (à renseigner)

Rollback:
- Restaurer depuis snapshot `REPO/wpcursor-history/snapshots/20260618-133253/components/question-prepa/component.php` (version avant correctif) ou version antérieure dans le même dossier snapshots

---

## 2026-06-18 — notre-prepa-iep : création du composant + optimisation mobile

Status: livré

Composants:
- wp-content/plugins/wpcursor/components/notre-prepa-iep/component.php (nouveau)
- wp-content/plugins/wpcursor/components/notre-prepa-iep/style.css (grille 2×2 desktop, 1 colonne ≤767px, fond gris, cartes paysage)
- wp-content/plugins/wpcursor/components/notre-prepa-iep/component.json (nouveau)
- wp-content/plugins/wpcursor/components/notre-prepa-iep/divi-template.txt (nouveau)

Pages:
- À intégrer via `[wpcursor name="notre-prepa-iep"]` ou module Divi WP Cursor

Tests:
- Grille 2×2 desktop (Stanislas, Fénelon, Saint-Thomas, visio)
- Mobile : 1 colonne, textes lisibles, `sizes` sur images
- Renseigner `cardN_image_url` dans la médiathèque

Commit:
- (à renseigner)

Rollback:
- Supprimer le dossier `components/notre-prepa-iep/` ou utiliser le composant existant `localisation-prepa` (variante 4 colonnes)

---

## 2026-06-18 — Lot Cursor : composants cocon prépa Sciences Po (itérations)

Status: livré (snapshots REPO entre 00:01 et 04:43 UTC)

Composants (fichiers `component.php` modifiés — voir onglet **Historique** WP Cursor pour rollback fichier par fichier):
- wp-content/plugins/wpcursor/components/hero-entete-cocon/
- wp-content/plugins/wpcursor/components/reunion-info-gratuite/
- wp-content/plugins/wpcursor/components/decouvrir-prepa/
- wp-content/plugins/wpcursor/components/nos-professeurs/
- wp-content/plugins/wpcursor/components/guide-admission/
- wp-content/plugins/wpcursor/components/pourquoi-prepa/
- wp-content/plugins/wpcursor/components/points-forts-prepa/
- wp-content/plugins/wpcursor/components/question-prepa/ (itérations antérieures au correctif puces)
- wp-content/plugins/wpcursor/components/temoignages-prepa/
- wp-content/plugins/wpcursor/components/faq-cocon/
- wp-content/plugins/wpcursor/components/localisation-prepa/
- wp-content/plugins/wpcursor/components/des-ressources/
- wp-content/plugins/wpcursor/components/etiquettes-defilantes/

Pages:
- Pages cocon prépa Sciences Po (shortcodes `[wpcursor]` / module Divi WP Cursor)

Tests:
- Parcours desktop + mobile sur chaque section du cocon
- Prévisualisation admin WP Cursor (onglet Prévisualiser)

Commit:
- (à renseigner)

Rollback:
- Onglet admin **Historique** → bouton Rollback par événement ; ou snapshots datés dans `REPO/wpcursor-history/snapshots/20260618-*` et `20260617-*`

---

## 2026-06-17 — hero-entete-cocon et etiquettes-defilantes (début lot cocon)

Status: livré

Composants:
- wp-content/plugins/wpcursor/components/hero-entete-cocon/component.php
- wp-content/plugins/wpcursor/components/etiquettes-defilantes/component.php

Pages:
- En-tête et bandeau étiquettes du cocon prépa

Tests:
- Rendu hero + bandeau défilant

Commit:
- (à renseigner)

Rollback:
- Snapshots `REPO/wpcursor-history/snapshots/20260617-233737` et `20260617-234730` / `20260617-234903`

---

## 2026-05-28 — Composants accueil-admission, mission-liste, coucou-edouard (itérations)

Status: livré

Composants:
- wp-content/plugins/wpcursor/components/accueil-admission/component.php (+ style.css, script.js, component.json, divi-template.txt)
- wp-content/plugins/wpcursor/components/mission-liste/component.php (+ assets)
- wp-content/plugins/wpcursor/components/coucou-edouard/component.php (+ assets)

Pages:
- Pages d’accueil / admission et tests composants

Tests:
- Carrousel accueil-admission, liste mission-liste, composant exemple coucou-edouard

Commit:
- (non commité — voir entrée du 2026-05-28 générateur shortcode)

Rollback:
- Snapshots `REPO/wpcursor-history/snapshots/20260528-*` et `20260526-*`

---

## 2026-05-26 — Premières itérations composants (accueil-admission, mission-liste, coucou-edouard)

Status: livré

Composants:
- wp-content/plugins/wpcursor/components/coucou-edouard/component.php
- wp-content/plugins/wpcursor/components/mission-liste/component.php
- wp-content/plugins/wpcursor/components/accueil-admission/component.php

Pages:
- Développement / tests initiaux WP Cursor

Tests:
- Prévisualisation admin

Commit:
- (à renseigner)

Rollback:
- Snapshots `REPO/wpcursor-history/snapshots/20260526-*`

---

## 2026-06-03 — Module Divi WP Cursor (v1.8.2)

Status: livré

Composants:
- wp-content/plugins/wpcursor/includes/divi-module.php
- wp-content/plugins/wpcursor/includes/divi-integration.php
- wp-content/plugins/wpcursor/assets/divi-builder.js
- wp-content/plugins/wpcursor/includes/component-loader.php (defaults Divi centralisés)
- wp-content/plugins/wpcursor/includes/rest-api.php (`divi_template_lines` sur GET …/components/{slug})
- wp-content/plugins/wpcursor/wpcursor.php (v1.8.2)

Pages:
- Toute page Divi : ajouter le module **WP Cursor** (catégorie WP Cursor) à la place d’un module Code + shortcode.

Tests:
- Visual Builder : module visible, sélection composant, aperçu rendu
- Front : même rendu qu’un shortcode `[wpcursor]` équivalent
- Bouton **Charger le modèle Divi** (utilisateur `manage_wpcursor`)

Commit:
- (à renseigner après commit Git)

Rollback:
- Désactiver le module sur les pages concernées ou revenir au shortcode dans un module Code ; `git checkout <ref>~1 -- wp-content/plugins/wpcursor/`

---

### Format conseillé (très utile si tu reviens sur le site des mois après)

Chaque entrée peut suivre cette structure — les sections **`Commit:`** et **`Rollback:`** sont **fortement recommandées** ; l’admin les affiche dans des encadrés visibles.

```
## YYYY-MM-DD — Titre court de la livraison

Status: livré | en cours | annulé

Composants:
- wp-content/plugins/wpcursor/components/mon-slug/component.php
- wp-content/plugins/wpcursor/components/mon-slug/style.css

Pages:
- Nom de la page, ID 123

Tests:
- Desktop OK
- Mobile OK
- Divi OK

Commit:
- abc123def789

Rollback:
- git checkout abc123def789~1 -- wp-content/plugins/wpcursor/components/mon-slug/
```

**Rollback — exemples utiles :**

- Revenir au commit précédent pour tout le dépôt : `git checkout abc123def789~1`
- Ne restaurer qu’un dossier composant :  
  `git checkout abc123def789~1 -- wp-content/plugins/wpcursor/components/hero-home/`
- Restaurer un seul fichier depuis la version précédente :  
  `git checkout abc123def789~1 -- wp-content/plugins/wpcursor/components/hero-home/component.php`

Si tu n’as pas encore commité, indique au minimum **`Commit:`** (vide ou « à faire ») et une ligne **`Rollback:`** décrivant comment annuler à la main (fichier de backup, copie, etc.).

**Sans sections structurées**, un paragraphe libre sous le titre reste accepté ; l’admin l’affiche comme avant.

---

## 2026-05-28 — Générateur shortcode avancé + standard multi-composants (HTML/CSS, classes CSS, items dynamiques)

Status: livré

Composants:
- wp-content/plugins/wpcursor/includes/admin.php
- wp-content/plugins/wpcursor/includes/component-loader.php
- wp-content/plugins/wpcursor/components/mission-liste/component.php
- wp-content/plugins/wpcursor/components/mission-liste/component.json
- wp-content/plugins/wpcursor/components/mission-liste/divi-template.txt
- wp-content/plugins/wpcursor/components/accueil-admission/component.php
- wp-content/plugins/wpcursor/components/accueil-admission/component.json
- wp-content/plugins/wpcursor/components/accueil-admission/style.css
- wp-content/plugins/wpcursor/components/accueil-admission/script.js
- wp-content/plugins/wpcursor/components/accueil-admission/divi-template.txt
- wp-content/plugins/wpcursor/components/coucou-edouard/component.php
- wp-content/plugins/wpcursor/components/coucou-edouard/component.json
- wp-content/plugins/wpcursor/components/coucou-edouard/divi-template.txt
- wp-content/plugins/wpcursor/components/README.md
- wp-content/plugins/wpcursor/CHANGELOG.md
- REPO/WP_CURSOR_PLUGIN.txt

Pages:
- Admin plugin WP Cursor (onglets Générateur shortcode / Prévisualiser / Journal de log / Opérations)

Tests:
- PHP lint OK (`admin.php`, `component-loader.php`, `mission-liste/component.php`, `accueil-admission/component.php`, `coucou-edouard/component.php`)
- Lints IDE: aucun nouveau warning/error
- Prévisualisation admin: rendu `mission-liste` et `accueil-admission` validé visuellement

Commit:
- (non commité dans ce contexte ; changements présents dans l’arbre de travail)

Rollback:
- Restaurer les fichiers modifiés depuis HEAD~1 (si git présent), exemple :
  `git checkout HEAD~1 -- wp-content/plugins/wpcursor/includes/admin.php wp-content/plugins/wpcursor/includes/component-loader.php wp-content/plugins/wpcursor/components/mission-liste wp-content/plugins/wpcursor/components/accueil-admission wp-content/plugins/wpcursor/components/coucou-edouard wp-content/plugins/wpcursor/components/README.md wp-content/plugins/wpcursor/CHANGELOG.md REPO/WP_CURSOR_PLUGIN.txt`

## 2026-05-10 — Convention OPERATIONS : sections Commit / Rollback visibles dans l’admin

Demande : rendre le **rollback** traçable dans chaque livraison. Actions : préambule « Format conseillé » ci‑dessus ; onglet Opérations : encadrés pour **Commit** et **Rollback** ; préambule du fichier affiché dans un panneau repliable sous WordPress.

Status: livré

Composants:
- wp-content/plugins/wpcursor/OPERATIONS.md
- wp-content/plugins/wpcursor/includes/admin.php

Pages:
- (admin plugin uniquement)

Tests:
- Affichage liste OK

Commit:
- (voir dépôt Git au moment du déploiement)

Rollback:
- git checkout HEAD~1 -- wp-content/plugins/wpcursor/OPERATIONS.md wp-content/plugins/wpcursor/includes/admin.php

## 2026-05-10 — WP Cursor v1.4.0 : chargeur composants, props, assets, diagnostic, usage enrichi, WP‑CLI

Implémentation : `includes/component-loader.php` (résolution legacy/modern, sanitisation props, enqueue CSS/JS par dossier, inventaire) ; shortcodes avec attributs → `$wpcursor_context` ; admin : onglet **Diagnostic**, utilisation détaillée + bouton **Copier contexte Cursor**, transient scan v2 ; `includes/cli.php` (`wp wpcursor components`, `wp wpcursor diagnose`). `CHANGELOG.md` mis à jour.

## 2026-05-10 — Avis feuille de route WP Cursor V2+ (doc REPO)

Synthèse des améliorations proposées (contrat component.json, props shortcode, enqueue assets, diagnostic admin, usage enrichi, preview, WP‑CLI, OPERATIONS structuré) et prioritisation — ajoutée dans `REPO/WP_CURSOR_PLUGIN.txt` section « Feuille de route V2+ ».

## 2026-05-10 — Documentation REPO : WP Cursor (remplace MODULE_ACCORDIONS_PLUS.txt)

Demande : documenter le plugin **WP Cursor** (pas le module Divi Accordions). Action : nouveau fichier `REPO/WP_CURSOR_PLUGIN.txt` (structure, shortcodes, admin, OPERATIONS/CHANGELOG, limites, améliorations) ; suppression de `REPO/MODULE_ACCORDIONS_PLUS.txt` ; entrée ajoutée dans `REPO/README.md`.

## 2026-05-10 — Accordéon Première : `<p style="text-align:justify">` en base (page parent)

Le texte des accordéons est stocké dans la page parent **Stage intensif en Première** (ID `4407450`), pas dans la page enfant `/lycee/premiere/` — le CSS sur `page-id-4426228` ne pouvait pas cibler le bon HTML. Mise à jour du `post_content` : après chaque `[chiac_divi_accordions_item …]`, remplacement de `<p>` par `<p style="text-align: justify;">` pour les 3 items (comme dans l’éditeur Divi « Code source »). Suppression de l’enqueue CSS dédié devenu inutile.

## 2026-05-10 — Page Première : justification accordéon via fichier CSS dédié + enqueue

Les règles en fin de `style.css` du child ne s’appliquaient pas fiabilité (CSS invalide plus haut dans le fichier, TinyMCE « align left » sur les `<p>`). Nouveau fichier `divi-child/assets/css/page-premiere-accordeon.css` + `wp_enqueue_scripts` priorité 999 sur `is_page(4426228)` avec version `filemtime`.

## 2026-05-10 — Page /lycee/premiere/ : correction justification accordéon (Divi)

Les textes ne se justifiaient pas : règles CSS renforcées avec `!important` et sélecteurs `.chiac_divi_accordions.style-2` / `.style-2.blanc` pour passer après les styles Divi colonne / module.

## 2026-05-10 — Page /lycee/premiere/ : texte des accordéons justifié

Demande : sur [Première](https://www.cours-thales.fr/lycee/premiere/), dans l’accordéon sous « Pourquoi faire un stage en Première ? », justifier les paragraphes des trois onglets, pas les titres. Action : règles CSS dans `wp-content/themes/divi-child/style.css` pour `body.page-id-4426228 .chiac_divi_accordions .chiac-content` (module chiac — contenu seul, pas `.chiac-header`).

## 2026-05-10 — Page « Réussir sa rentrée en Première » : mois du calendrier en majuscules

Demande : sur [Réussir sa rentrée en Première](https://www.cours-thales.fr/lycee/premiere/reussir-rentree/), dans **Calendrier des épreuves communes**, afficher **JANVIER**, **AVRIL**, **JUIN** (titres des sous-sections). Action : remplacement des balises `<h4>janvier</h4>`, `<h4>avril</h4>`, `<h4>juin</h4>` par la version majuscule dans le contenu de la page WordPress (ID `4412148`, slug `tout-ce-quil-faut-savoir-pour-reussir-sa-rentree-en-premiere-2`). Penser à vider le cache WP Rocket si la page reste en ancienne version.

## 2026-05-10 — Lister systématiquement dans OPERATIONS.md chaque demande du chat

Demande utilisateur : à chaque fois, documenter dans ce fichier les demandes faites dans le chat. Convention enregistrée dans l’intro du fichier ; cette entrée officialise l’engagement pour les prochaines livraisons.

## 2026-05-10 — Opérations effectuées : historique des demandes dans l’admin

Ajout de l’onglet dédié lecture de ce fichier (`OPERATIONS.md`), liste triée du **plus récent au plus ancien**.

## 2026-05-10 — Thème enfant : fichiers du child theme et fin de style.css

Onglet **Thème enfant** : tableau des fichiers (php, css, js…) avec date de modification, aperçu des dernières lignes de `style.css`, liens vers l’éditeur de thème si autorisé ; encadré expliquant que le chat Cursor ne peut pas s’afficher dans WordPress.

## 2026-05-10 — Page d’administration WP Cursor

Onglets Composants, Utilisation sur le site (scan des shortcodes), affichage du `CHANGELOG.md` du plugin.

## 2026-05-10 — Création du plugin WP Cursor

Shortcodes `[wpcursor]` et `[site_component]`, dossier `components/` avec exemple, sécurisation des includes.

## 2026-05-10 — Documentation REPO et protection HTTP

Dossiers `REPO/theme` et `REPO/plugin`, règles `.htaccess` pour bloquer l’accès web à `REPO/` et aux dossiers `.git` / VCS.
