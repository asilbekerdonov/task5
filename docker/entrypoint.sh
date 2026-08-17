#!/bin/sh
set -e

echo "🚀 Starting Movie Store container..."

# Генерация APP_KEY при первом старте
if [ ! -f /var/www/html/.env ]; then
    echo "📝 Creating .env from .env.example"
    cp /var/www/html/.env.example /var/www/html/.env
    php /var/www/html/artisan key:generate --force
    echo "✅ APP_KEY generated"
fi

# Создаём необходимые директории
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage

# Очистка и оптимизация кэша
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

echo "✅ Configuration complete"

exec "$@"