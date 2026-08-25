# Ecommerce

Petit projet e-commerce PHP (XAMPP). Instructions rapides :

Prérequis
- PHP 8.0+, MySQL, Apache (XAMPP recommandé)

Installation
1. Copier `.env.example` en `.env` et renseigner les variables DB (hôte, nom, utilisateur, mot de passe).
2. Importer la base de données (fichier SQL fourni si disponible).
3. Placer le projet dans `htdocs` (XAMPP) et démarrer Apache/MySQL.

Tests & vérifications
- Vérifier la syntaxe PHP : `php -l <file.php>` ou via la CI fournie.
- Scripts de smoke tests (local uniquement) :
  - `php scripts/smoke_cart_test.php`
  - `php scripts/smoke_admin_login.php`
  - `php scripts/smoke_checkout.php`

CI
 - Un workflow GitHub Actions basique est présent dans `.github/workflows/ci.yml` pour lint PHP.

Contribution
- Ouvrez une branche, committez et proposez une PR vers `main`.
# E-Commerce (Local)

Ce dépôt est une boutique e-commerce PHP minimaliste.

## Installation locale (XAMPP)

1. Copier le projet dans votre dossier web (`htdocs`).
2. Copier `.env.example` en `.env` et adapter les valeurs (base de données, SMTP si besoin).

```bash
cp .env.example .env
```

3. Créer la base de données et importer le schéma (fichier SQL fourni si applicable).
4. Si nécessaire, installez les dépendances Composer (si ajoutées plus tard) :

```bash
composer install
```

## Scripts d'aide

- Créer un administrateur en CLI :

```bash
php scripts/create_admin.php admin@example.com secret Admin Prenom
```

- Smoke tests (panier) :

```bash
php scripts/smoke_cart_test.php
```

- Smoke test checkout :

```bash
php scripts/smoke_checkout.php
```

- Smoke tests catégories et produits :

```bash
php scripts/smoke_categories_crud.php
php scripts/smoke_product_delete.php
```

## Tests

Actuellement des scripts CLI de vérification existent dans `scripts/`.

## Déploiement / CI

Je peux ajouter un workflow GitHub Actions pour exécuter le linter PHP et les tests si vous le souhaitez.

