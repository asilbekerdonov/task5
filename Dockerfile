# ============================================
# STAGE 1: Build frontend assets
# ============================================
FROM node:22-alpine AS frontend-builder

WORKDIR /build

COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts || npm install --ignore-scripts

COPY resources/ resources/
COPY vite.config.js ./
COPY tsconfig.json ./

RUN npm run build

# ============================================
# STAGE 2: Install PHP dependencies
# ============================================
FROM composer:2 AS composer-builder

WORKDIR /build

COPY composer.json composer.lock* ./

# --no-scripts обязателен: post-autoload-dump пытается вызвать artisan,
# которого нет в этой стадии (копируется только composer.json/lock)
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

# ============================================
# STAGE 3: PHP-FPM runtime
# ============================================
FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

RUN docker-php-ext-install \
    intl \
    zip \
    pcntl \
    bcmath \
    opcache

WORKDIR /var/www/html

# Копируем код приложения
COPY . .

# Копируем vendor из composer-builder (вместе с оптимизированным autoload)
COPY --from=composer-builder /build/vendor ./vendor

# Копируем собранный frontend
COPY --from=frontend-builder /build/public/build ./public/build

# Выполняем package:discover — теперь artisan существует
RUN php artisan package:discover --ansi

# Создаём директории для логов
RUN mkdir -p /var/log/php /var/log/nginx /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Копируем конфиги
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8282

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
