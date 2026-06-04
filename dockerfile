FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM node:20-alpine AS frontend
WORKDIR /app
COPY . .
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi && npm run build

FROM php:8.3-cli-bookworm AS app
WORKDIR /var/www/html

ARG WWWUSER=1000
ARG WWWGROUP=1000

RUN pecl channel-update pecl.php.net 
RUN apt-get update && apt-get install -y --no-install-recommends \
      git curl unzip \
      libzip-dev libonig-dev libxml2-dev libcurl4-openssl-dev zlib1g-dev \
      libbrotli-dev libpq-dev \
      build-essential autoconf pkg-config libssl-dev \
    && docker-php-ext-install \
      pdo_mysql pdo_pgsql mbstring zip bcmath pcntl sockets curl xml opcache \
    && pecl install swoole \
    && docker-php-ext-enable swoole \
    && groupadd --force -g ${WWWGROUP} sail \
    && useradd -ms /bin/bash --no-user-group -g ${WWWGROUP} -u ${WWWUSER} sail \
    && rm -rf /var/lib/apt/lists/*

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p bootstrap/cache \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

RUN chown -R www-data:www-data storage bootstrap/cache
RUN chown -R sail:sail storage bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]