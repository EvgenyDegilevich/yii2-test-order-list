#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! mysqladmin ping -h"$DB_HOST" --silent; do
    sleep 1
done
echo "MySQL is ready!"

# Check if vendor directory exists and install dependencies if needed
if [ ! -d "/var/www/html/vendor" ] || [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html
    if [ -f "composer.json" ]; then
        composer install --optimize-autoloader --no-interaction --prefer-dist
        echo "Composer dependencies installed successfully!"
    else
        echo "Warning: composer.json not found!"
    fi
fi

# Create necessary directories if they don't exist
mkdir -p /var/www/html/runtime /var/www/html/web/assets

# Run Yii2 migrations if yii console exists
if [ -f "/var/www/html/yii" ]; then
    echo "Running Yii2 migrations..."
    cd /var/www/html
    
    # Make yii console executable
    chmod +x ./yii
    
    # Wait a bit more for MySQL to be fully ready
    sleep 5
    
    # Run migrations
    if php yii migrate --interactive=0; then
        echo "✅ Migrations applied successfully!"
    else 
        echo "⚠️  Failed to run migrations, but continuing..."
        echo "You may need to run 'php yii migrate' manually"
    fi
else
    echo "Warning: yii console not found, skipping migrations"
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/runtime /var/www/html/web/assets
chmod -R 775 /var/www/html/runtime /var/www/html/web/assets

# Ensure vendor permissions are correct
if [ -d "/var/www/html/vendor" ]; then
    chown -R www-data:www-data /var/www/html/vendor
fi

echo "Starting PHP-FPM..."
exec "$@"