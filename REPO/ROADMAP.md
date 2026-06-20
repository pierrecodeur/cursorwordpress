# Feuille de route WP Cursor

Ce fichier décrit **uniquement des améliorations futures** — pas ce qui est déjà livré.  
**État actuel du plugin :** V1.7.x (voir [README.md](README.md), section plugin WP Cursor).

Les entrées ci‑dessous restent volontairement à prioriser selon effort, risque et valeur.

## Court terme (faible risque)

- **Assets globaux du plugin** : enqueue conditionnel de CSS/JS communs si le nombre de composants augmente (sans remplacer l’enqueue par composant déjà en place).
- **Schéma component.json** : enrichissements (types nombre/booléen, messages d’erreur plus précis, fichier `.pot` pour chaînes admin).

## Moyen terme

- **Props du shortcode** : renforcer la validation runtime (ex. rejeter les props inconnues au lieu de seulement logger) — derrière option ou version majeure pour éviter les régressions.
- **OPERATIONS.md enrichi** : champs structurés optionnels (ex. statut, post IDs) avec migration douce pour ne pas invalider les entrées historiques.

## Plus tard / si l’équipe grossit

- **REST API ou endpoints admin** réservés aux outils externes (optionnel, hors périmètre courant).
- **WP‑CLI** : exports JSON / CI au‑delà de **context-site**.
- **Registre des composants utilisés sur la page** en amont du rendu : optimisation pour puristes perf (éviter enregistrements tardifs d’assets si besoin).

## Rappels hors roadmap produit

- Texte stocké par **Divi** dans la base : toujours viser le bon **post_id** (piège fréquent page parent / enfant).
- Secrets : ne jamais les commiter dans la doc ou les fichiers de suivi du plugin.
