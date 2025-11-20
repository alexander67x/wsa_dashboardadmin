# syntax=docker/dockerfile:1

### 1. Construcción del frontend (Vite)
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci --no-audit --progress=false

COPY resources ./resources
COPY vite.config.js ./
RUN npm run build


### 2. PHP + Nginx + Supervisor
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    bash curl git icu-dev libpng-dev libjpeg-turbo-dev \
    libwebp-dev freetype-dev libzip-dev oniguruma-dev \
    zlib-dev mariadb-connector-c-dev nginx supervisor

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql intl mbstring zip gd opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY --from=frontend /app/public/build ./public/build

RUN php artisan config:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true

RUN chown -R www-data:www-data storage bootstrap/cache

COPY deploy/nginx.conf /etc/nginx/nginx.conf
COPY deploy/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
