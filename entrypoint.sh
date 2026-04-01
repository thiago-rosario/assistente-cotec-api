#!/bin/sh

set -e

cd /var/www/html

mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

if [ ! -L public/storage ]; then
    rm -rf public/storage
    ln -s /var/www/html/storage/app/public public/storage
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --graceful --no-interaction
fi

if [ "${APP_ENV:-local}" = "production" ]; then
    php artisan optimize --no-interaction
fi

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
