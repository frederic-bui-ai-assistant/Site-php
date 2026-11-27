# Site PHP + MySQL (liste de tâches)

Petit site en PHP pur avec une base MySQL. Il contient :

- `index.php` : le site (ajouter, terminer, supprimer des tâches)
- `migrate.php` : la migration (crée la base et la table)
- `README.md` : ce fichier

## Prérequis

- PHP 8.0+ avec l'extension `pdo_mysql`
- Un serveur MySQL ou MariaDB démarré

Vérifier l'extension : `php -m | grep pdo_mysql`

## Configuration

Par défaut, le projet utilise :

| Variable  | Valeur par défaut |
|-----------|-------------------|
| `DB_HOST` | `127.0.0.1`       |
| `DB_PORT` | `3306`            |
| `DB_NAME` | `simple_site`     |
| `DB_USER` | `root`            |
| `DB_PASS` | *(vide)*          |

Pour les changer, définissez les variables d'environnement avant de lancer les commandes :

```bash
export DB_USER=monuser
export DB_PASS=monmotdepasse
```

## 1. Lancer la migration

Depuis le dossier du projet :

```bash
php migrate.php
```

Résultat attendu : `Migration terminée : base 'simple_site', table 'tasks' prête.`

La migration peut être relancée sans risque (elle utilise `IF NOT EXISTS`).

## 2. Lancer le site

```bash
php -S localhost:8000
```

Puis ouvrir <http://localhost:8000> dans le navigateur.

## Dépannage

- **`could not find driver`** : installer/activer `pdo_mysql` (ex. `sudo apt install php-mysql`).
- **`Access denied`** : vérifier `DB_USER` / `DB_PASS`.
- **`Connection refused`** : vérifier que MySQL est démarré et que `DB_HOST` / `DB_PORT` sont corrects.
