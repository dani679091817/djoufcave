# Djoufinter

Plateforme e-commerce Djouf Inter basee sur Laravel + Bagisto.

## Apercu

Djoufinter est une boutique en ligne dediee a la vente de boissons, avec:
- catalogue produits
- gestion des stocks
- tunnel de commande
- espace client
- panneau d'administration
- notifications e-mail

## Prerequis

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+
- Extensions PHP requises par Laravel/Bagisto

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configurer ensuite les variables dans `.env`:
- `APP_NAME`
- `APP_URL`
- `DB_*`
- `MAIL_*`

Puis lancer les migrations:

```bash
php artisan migrate --seed
```

## Lancement en local

```bash
php artisan serve
npm run dev
```

## Cache et maintenance

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Marque

- Nom projet: Djoufinter
- Marque visible: Djouf Inter
- Branding principal: logo Djouf Inter

## Licence

Ce projet est base sur Bagisto (ecosysteme Laravel). Verifier les licences des dependances avant redistribution commerciale.
