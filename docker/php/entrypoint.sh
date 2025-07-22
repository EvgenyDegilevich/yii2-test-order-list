#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! mysqladmin ping -h"$DB_HOST" --silent; do
    sleep 1
done
echo "MySQL is ready!"

# Set proper permissions
chown -R www-data:www-data /var/www/html/runtime /var/www/html/web/assets
chmod -R 775 /var/www/html/runtime /var/www/html/web/assets

echo "Starting PHP-FPM..."
exec "$@"