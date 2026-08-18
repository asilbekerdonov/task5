#!/bin/sh
set -e

echo "🚀 Starting Movie Store container..."

# 1. Создаём .env если его нет
if [ ! -f /var/www/html/.env ]; then
    echo "📝 Creating .env from .env.example"
    cp /var/www/html/.env.example /var/www/html/.env
fi

# 2. Генерируем APP_KEY если он пустой или отсутствует
if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
    echo "🔑 Generating APP_KEY"
    php /var/www/html/artisan key:generate --force
fi

# 3. Очищаем старый кэш
php /var/www/html/artisan config:clear
php /var/www/html/artisan route:clear
php /var/www/html/artisan view:clear

# 4. Создаём директории
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage

# 5. Кэшируем ПОСЛЕ генерации ключа
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

echo "✅ Configuration complete"

exec "$@"
