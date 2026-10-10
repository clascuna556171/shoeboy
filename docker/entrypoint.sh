#!/bin/sh
set -e

echo "==> Initializing The Shoe Boy application..."

# Discover packages & optimize Laravel for production
php artisan package:discover --ansi || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations
echo "==> Running database migrations..."
php artisan migrate --force || true

# Automatically seed default accounts & catalog if the database is newly created
echo "==> Checking if initial seed data is required..."
php artisan tinker --execute="if (\App\Models\User::count() === 0) { echo 'Seeding initial database...\n'; \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]); echo 'Seeding completed.\n'; }" || true

echo "==> Ready! Starting web services..."
