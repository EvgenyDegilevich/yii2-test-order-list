FROM composer:2 AS vendor
WORKDIR /app
COPY application/composer.json application/composer.lock* ./
RUN composer install --optimize-autoloader --no-interaction --prefer-dist || echo "No composer files found"

FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
        git \
        curl \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libgmp-dev \
        default-mysql-client \
        dos2unix \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        xml \
        bcmath \
        gmp \
    && pecl install xdebug-3.3.1 \
    && docker-php-ext-enable xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy vendor dependencies from composer stage
COPY --from=vendor /app/vendor /var/www/html/vendor

# Copy application files
COPY application /var/www/html

# Convert line endings for shell scripts and config files
RUN find /var/www/html \
        -path /var/www/html/vendor -prune -o \
        -type f \( -name '*.sh' -o -name '*.conf' \) -print0 \
        | xargs -0 dos2unix -q || true

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/runtime /var/www/html/web/assets \
    && chown -R www-data:www-data /var/www/html/runtime /var/www/html/web/assets \
    && chmod -R 775 /var/www/html/runtime /var/www/html/web/assets

WORKDIR /var/www/html

# Copy entrypoint script
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN dos2unix /entrypoint.sh && chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]