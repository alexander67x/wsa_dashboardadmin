# syntax=docker/dockerfile:1

FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci --no-audit --progress=false
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    zlib-dev \
    mariadb-connector-c-dev \
    nginx \
    supervisor

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql intl mbstring zip gd opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY . .
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader

COPY --from=frontend /app/public/build ./public/build

# Opcional: precacheo, no detiene el build si falta .env
RUN php artisan config:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true

RUN mkdir -p /var/log/supervisor

RUN chown -R www-data:www-data storage bootstrap/cache

COPY deploy/nginx.conf /etc/nginx/nginx.conf
COPY deploy/supervisord.conf /etc/supervisord.conf

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=https://alwswsa.shop \
    DB_CONNECTION=mysql \
    DB_HOST=mysql \
    DB_PORT=3306 \
    DB_DATABASE=app \
    DB_USERNAME=app \
    DB_PASSWORD=secret

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
