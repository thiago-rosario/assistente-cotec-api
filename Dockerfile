FROM composer:2 AS composer_deps

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .

RUN composer dump-autoload \
    --classmap-authoritative \
    --no-dev \
    --no-interaction

FROM node:22-bookworm-slim AS frontend_assets

WORKDIR /var/www/html

COPY package.json package-lock.json ./

RUN --mount=type=cache,target=/root/.npm \
    npm ci \
        --fetch-retries=5 \
        --fetch-retry-mintimeout=20000 \
        --fetch-retry-maxtimeout=120000 \
        --fetch-timeout=600000

COPY . .
COPY --from=composer_deps /var/www/html/vendor ./vendor

RUN npm run build

FROM php:8.4-fpm-bookworm AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install intl opcache pcntl pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .
COPY --from=composer_deps /var/www/html/vendor ./vendor
COPY --from=composer_deps /var/www/html/bootstrap/cache ./bootstrap/cache
COPY --from=frontend_assets /var/www/html/public/build ./public/build
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

FROM nginx:1.27-alpine AS nginx

WORKDIR /var/www/html

COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public ./public

RUN mkdir -p /var/www/html/storage/app/public \
    && rm -f /var/www/html/public/storage \
    && ln -s /var/www/html/storage/app/public /var/www/html/public/storage
