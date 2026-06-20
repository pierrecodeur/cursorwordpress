# Composants WP Cursor

## Accès direct interdit (ABSPATH)

Chaque fichier PHP exécutable (`component.php`, ou encore **`slug.php` en mode legacy**) doit commencer **immédiatement après** `<?php` par la garde recommandée par WordPress :

```php
if (!defined('ABSPATH')) {
	exit;
}
```

Cela évite qu’un fichier du plugin soit invoqué directement par URL sans passer par le cœur WordPress.

## Règle obligatoire : tout HTML doit être échappé à la sortie

Le chargeur du plugin sécurise les chemins et le chargement des fichiers. **La sécurité du HTML affiché dépend entièrement du code dans ton composant** (`component.php` ou fichier legacy `slug.php`).

**Toute donnée affichée dans la page doit passer par une fonction d’échappement WordPress adaptée au contexte.**

Ne fais **jamais** confiance aux props du shortcode ni aux variables utilisateur : elles sont à traiter comme du texte brut jusqu’à échappement explicite.

### À faire

| Contexte | Fonction (exemples) |
|----------|----------------------|
| Texte dans du HTML (titres, paragraphes, boutons) | `esc_html( $texte )` |
| Attributs HTML (`class`, `id`, `data-*`, etc.) | `esc_attr( $valeur )` |
| URLs (`href`, `src`, `action`, etc.) | `esc_url( $url )` |
| Petit bloc HTML contrôlé (contenu riche limité) | `wp_kses_post( $html )` ou `wp_kses()` avec tags autorisés |

Exemples corrects :

```php
echo esc_html( $title );
echo esc_attr( $class );
echo esc_url( $url );
echo wp_kses_post( $html_limite );
```

### À ne pas faire

Ne pas imprimer directement des props ou des variables non échappées :

```php
// INTERDIT — risque XSS
echo $props['title'];
echo $title;
```

Utilise toujours la fonction qui correspond au **contexte d’affichage** (texte, attribut, URL, HTML filtré).

---

## Contexte PHP : `$wpcursor_context`

Le fichier inclus reçoit :

| Clé | Description |
|-----|-------------|
| `name` | Slug du composant |
| `props` | Attributs du shortcode filtrés par WordPress (voir **mode strict** ci‑dessous) |
| `mode` | `modern` (dossier) ou `legacy` (fichier racine) |
| `runtime` | `front` (site réel), `admin_preview` (onglet Prévisualiser), `cli` (`wp wpcursor render`) |

### Prévisualisation admin (`admin_preview`)

L’onglet **Prévisualiser** du plugin est réservé aux comptes **`manage_wpcursor`** ; l’URL inclut un **`_wpnonce`** obligatoire. Ce n’est pas une page publique : pas d’indexation par les moteurs, pas de rendu accessible sans session admin. Les erreurs PHP **notice/warning** capturées lors du rendu sont affichées **uniquement** dans cet écran.

**À faire dans tes composants** : ne pas lancer tracking / analytics / appels externes coûteux quand le runtime n’est pas `front`. Exemple :

```php
$cx         = isset($wpcursor_context) && is_array($wpcursor_context) ? $wpcursor_context : [];
$is_preview = (($cx['runtime'] ?? '') === 'admin_preview');

if (! $is_preview) {
	// tracking, animation lourde, API externe, etc.
}
```

---

## `component.json` (optionnel) — conventions

Fichier : `components/{slug}/component.json`. Il décrit le composant pour l’admin, l’API REST, la validation CLI et le contrat des props.

### Modèle officiel

```json
{
  "label": "Hero accueil",
  "description": "Bloc principal de la home",
  "version": "1.0.0",
  "updated": "2026-05-10",
  "props": {
    "title": {
      "type": "string",
      "required": false,
      "default": "Titre"
    },
    "button_url": {
      "type": "url",
      "required": false
    },
    "variant": {
      "type": "enum",
      "values": ["light", "dark"],
      "default": "light"
    }
  }
}
```

### Champs racine

| Champ | Obligatoire | Description |
|-------|-------------|-------------|
| `label` | Non | Libellé lisible (tronqué ~200 caractères en admin). |
| `description` | Non | Texte libre pour documenter le bloc (tronqué ~2000 caractères ; exposé notamment via REST). |
| `version` | Non | Chaîne courte (ex. semver), affichée en **v…** dans la liste admin. |
| `updated` | Non | Date au format **`AAAA-MM-JJ`** en début de chaîne ; utilisée pour l’affichage « modifié le … ». |
| `props` | Non | Objet décrivant les attributs du shortcode autorisés. |

Sans **label** / **version** / **updated**, une ligne de secours affiche la date/heure de modification du fichier PHP.

### Règles pour chaque entrée de `props`

1. **`type` obligatoire** — une des valeurs : `string`, `url`, `enum`.
2. Si **`type` est `enum`** : la clé **`values`** est obligatoire et doit être un **tableau non vide** de valeurs possibles (chaînes).
3. **`required`** : si la clé est présente, la valeur doit être un **booléen** JSON (`true` ou `false`). Sinon le fichier est rejeté comme schéma invalide.
4. **`default`** : **optionnel**, purement documentaire pour l’instant : le moteur **n’injecte pas** automatiquement cette valeur dans `$wpcursor_context['props']` au rendu (tu peux t’en servir comme convention d’équipe ou pour un outil externe).
5. **`css_selector`** : **optionnel**, recommandé pour le générateur admin. Permet d’afficher à côté du champ la classe/sélecteur CSS correspondant (et le picker couleur texte). Exemple : `"css_selector": ".wpcursor-mission-liste__title"`.

Les noms de props sont normalisés en **minuscules** ; autorisés : `a-z`, `0-9`, `_`, `-` (1 à 64 caractères).

### Props répétables (listes)

Pour les listes éditables depuis l’admin, utilise un schéma de clés numérotées : `item1`, `item2`, `item3`, etc.  
Le générateur peut ajouter dynamiquement des entrées supplémentaires (`item4`, `item5`...) si le composant supporte ce pattern côté PHP.

Types supportés à l’exécution : **`string`**, **`url`**, **`enum`** (avec `values`).

### Mode strict : props inconnues vs contrat

Quand **`props` contient au moins une clé déclarée**, le plugin applique un **contrat** :

| Contexte | Comportement |
|----------|----------------|
| **Front** (`runtime === front`) | Toute prop passée au shortcode qui **n’est pas** dans `component.json` est **journalisée** (`wpcursor_log` / `debug.log` si activé) et **retirée** du tableau **`$wpcursor_context['props']`** avant inclusion du PHP du composant (elle n’est donc pas utilisable dans le template). |
| **Admin — Prévisualiser** | Les options / propriétés restent **complètes** dans le formulaire et le JSON affiché ; les écarts au schéma sont listés dans un **encadré d’avertissement**. |
| **CLI** (`wp wpcursor render`) | Comme l’admin : pas de filtrage des clés dans la sortie HTML issue du rendu, mais **`WP_CLI::warning`** pour chaque message de schéma. |

Si **`props` est vide** (`{}`), aucune prop n’est déclarée : le plugin **ne considère pas** qu’un contrat liste les clés autorisées → pas de rejet ni filtrage front des clés « inconnues » (comportement « hors contrat »). Dans ce cas, **`wp wpcursor validate`** signale tout attribut vu dans les shortcodes du site comme hors contrat si tu souhaites documenter le composant plus tard.

Pour la structure des dossiers (`slug/component.php` vs `slug.php`), voir aussi `REPO/WP_CURSOR_PLUGIN.txt`.

---

## Logs debug

PHP : avec `WP_DEBUG_LOG` + `WP_DEBUG`, ou `define('WPCURSOR_DEBUG', true);` dans `wp-config.php`, le helper **`wpcursor_log('message', ['cle'=>'valeur'])`** écrit dans `debug.log` avec le préfixe **`[WP Cursor]`** (composant introuvable, enum invalide, prop hors contrat, etc.).
