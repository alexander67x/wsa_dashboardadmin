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
    supervisor \
    tzdata

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
COPY deploy/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENV APP_ENV=production \
    APP_DEBUG=true \
    APP_URL=http://localhost:8081 \
    DB_CONNECTION=mysql \
    DB_HOST=mysql \
    DB_PORT=3306 \
    DB_DATABASE=wsa_dashboardadmin \
    DB_USERNAME=wsa \
    DB_PASSWORD=wsa_secret \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    APP_RUN_SEEDERS=false \
    TZ=America/La_Paz

RUN ln -snf /usr/share/zoneinfo/America/La_Paz /etc/localtime \
    && echo "America/La_Paz" > /etc/timezone

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
