#!/bin/sh
set -e

echo "📌 Ejecutando comandos de inicialización..."

# Crear enlace a /storage (ignora error si ya existe)
php artisan storage:link || true

# Migraciones (solo si la DB está accesible)
php artisan migrate --force || true

# Forzar debug si se requiere ver errores (default true aquí; puedes override con APP_DEBUG=false)
export APP_DEBUG=${APP_DEBUG:-true}

# Limpiar caches
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "🚀 Iniciando Laravel..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
