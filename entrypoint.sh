#!/bin/sh
set -e

echo "📌 Ejecutando comandos de inicialización..."

# Crear enlace a /storage (ignora error si ya existe)
php artisan storage:link || true

# Migraciones (solo si la DB está accesible)
php artisan migrate --force || true

# Limpiar caches
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "---- LARAVEL LOG ----"
cat storage/logs/laravel.log || true
echo "----------------------"

echo "🚀 Iniciando Laravel..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
