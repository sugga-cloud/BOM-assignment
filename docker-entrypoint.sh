#!/bin/sh
set -e

cd /var/www/html


# Ensure SQLite database file exists
if [ ! -d database ]; then
  mkdir -p database
fi
if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
fi

# Generate app key if missing
if ! grep -q '^APP_KEY=' .env || [ -z "$(grep '^APP_KEY=' .env | cut -d '=' -f2)" ]; then
  php artisan key:generate --ansi
fi

# Install PHP dependencies if missing
if [ ! -f vendor/autoload.php ]; then
  composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader
fi

# Run migrations and seed database every time on startup
php artisan migrate:fresh
php artisan db:seed
exec php artisan serve --host=0.0.0.0 --port=8000
