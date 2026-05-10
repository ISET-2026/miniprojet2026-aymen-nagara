# RecipeHub (Symfony 7.4)

Projet Symfony : catalogue de recettes, API Platform (préfixe `/api`), sécurité, favoris session, filtres Doctrine, mails, téléversement d’images, données de démo, commande stats et tests PHPUnit.

Prérequis : **PHP 8.2+**, Composer 2.x, extensions standard Symfony (dont `pdo_sqlite`, `pdo_mysql`). En production ou en dev avec MySQL, créez une base **`recette_lotfi`** (voir `DATABASE_URL` dans `.env`) puis :

```bash
composer install
php bin/console doctrine:schema:create
php bin/console doctrine:fixtures:load --no-interaction
```

Ou en développement alternatif SQLite, adaptez `DATABASE_URL`.

## Config mail

Pour recevoir les notifications nouvelle recette, configurez un DSN SMTP (par ex. Mailtrap dans `MAILER_DSN`) et optionnellement `NOTIFICATION_EMAIL` dans `.env`.

## Comptes de démo (fixtures)

- Administrateur : `admin@recipehub.com` — mot de passe `admin123`
- Cuisinier : `chef@recipehub.com` — mot de passe `chef123`

## Tests

L’environnement `test` utilise SQLite en mémoire (`config/packages/test/doctrine.yaml`). Lancez PHPUnit avec PHP 8.2+ depuis la racine du projet :

```bash
vendor/bin/phpunit
```

## Schéma BDD et migrations

Le schéma est piloté par les attributs Doctrine. Vous pouvez utiliser `doctrine:migrations:diff` puis migrer après la première passe. Sans migrations, `doctrine:schema:create` convient comme point de départ.
