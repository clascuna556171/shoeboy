# The Shoe Boy

Order & Inventory Management System — Laravel 13 + SQLite + Vite/Tailwind + Alpine.js.

## Requirements
- PHP 8.3+ (with `pdo_sqlite`, `zip`, `mbstring`, `openssl`)
- Composer 2
- Node.js 20.19+/22.12+ and npm
- Git

## Setup (Windows)

```
git clone https://github.com/clascuna556171/shoeboy.git
cd shoeboy
composer install
copy .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open http://127.0.0.1:8000

## Demo logins
- Owner: `admin@theshoeboy.com` / `password`
- Staff: `staff@theshoeboy.com` / `password`

For development with hot reload: `composer dev`
