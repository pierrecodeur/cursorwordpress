# Procedure de rollback

## 1) Preparation (avant changement)

1. Passer le site en maintenance si necessaire.
2. Sauvegarder la base de donnees.
3. Sauvegarder les fichiers critiques:
   - `wp-content/themes/`
   - `wp-content/plugins/`
   - `wp-content/uploads/`
   - `wp-config.php`
4. Noter l'horodatage de snapshot dans `JOURNAL.md`.

## 2) Verification rapide post-deploiement

- Page d'accueil et pages critiques accessibles.
- Connexion admin fonctionnelle.
- Formulaires critiques (ex: Gravity Forms) operationnels.
- Cache purge et regeneration effectuees.
- Taches cron confirmees (car `DISABLE_WP_CRON` est active).

## 3) Rollback applicatif

1. Restaurer les fichiers sauvegardes (`themes`, `plugins`, `uploads`, `wp-config.php`) depuis le snapshot valide.
2. Restaurer la base de donnees associee au meme snapshot.
3. Purger les caches:
   - cache WP Rocket;
   - tout CDN ou proxy en amont.
4. Verifier URLs, permaliens, et pages critiques.

## 4) Commandes type (adapter a l'environnement)

```bash
# Sauvegarde base
mysqldump -h <db_host> -u <db_user> -p'<db_pass>' <db_name> > backup-YYYYmmdd-HHMM.sql

# Archive contenu applicatif critique
tar -czf files-YYYYmmdd-HHMM.tar.gz wp-content/themes wp-content/plugins wp-content/uploads wp-config.php
```

Ne pas versionner de sauvegardes SQL ou archives contenant des secrets dans Git.
