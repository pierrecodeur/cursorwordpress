# Preparation `style.css` (sans modification)

## Fichier cible

- Actif: `wp-content/themes/divi-child/style.css`
- Taille: 189994 bytes
- Lignes: 8409

## Backup realise avant toute modification

- Copie de sauvegarde:
  - `REPO/backups/style.css.backup-20260509-1615.css`
- Empreinte SHA-256 source:
  - `25eaf3f71934c55a67b419e5d0c00b6a32cc8b8b0d117a81ae6cee44b9f1502f`
- Empreinte SHA-256 backup:
  - `25eaf3f71934c55a67b419e5d0c00b6a32cc8b8b0d117a81ae6cee44b9f1502f`

Conclusion: backup binaire identique au fichier source.

## Etat "git"

- Le dossier `httpdocs/divi` n'est pas un depot Git (`.git` absent).
- Donc impossible de faire un commit/revert Git natif ici sans initialisation prealable.
- Le backup horodate ci-dessus sert de point de retour immediat.

## Constat technique (phase de preparation)

- Le fichier contient des blocs SCSS-like non compiles (imbrication directe):
  - exemples: `.chiac_divi_accordions_item { .chiac-header { ... } }`
  - pseudo-selecteurs imbriques: `&:hover`
- Ce format est invalide pour du CSS natif et peut expliquer les erreurs dans l'editeur de theme WordPress.
- Presence de repetitons notables:
  - 52 selecteurs repetes (estimation automatique);
  - la famille `.chiac_divi_accordions...` est dupliquee de nombreuses fois.

## Plan propose (sans execution pour le moment)

1. Geler une version de reference (deja fait via backup).
2. Extraire les blocs imbriques SCSS-like et les convertir en CSS plat valide.
3. Factoriser les regles communes en classes utilitaires ou blocs communs:
   - exemple: styles repetes des variantes d'accordeons (couleur, bordure, hover).
4. Segmenter le fichier en sections stables:
   - `01-tokens` (variables),
   - `02-base`,
   - `03-layout`,
   - `04-components`,
   - `05-overrides-divi`,
   - `99-hotfix`.
5. Ajouter une convention de contribution (entetes de section + ordre des proprietes).
6. Valider syntaxe CSS avant mise en ligne (lint + test visuel pages critiques).

## Regle pour la suite

- Tant qu'une passe de refactor n'est pas lancee explicitement, ne pas modifier `wp-content/themes/divi-child/style.css`.
