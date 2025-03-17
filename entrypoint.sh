#!/bin/sh
set -e

cd /var/www/html

# Ensure wait-for-it.sh is executable and present
if [ ! -f /var/www/html/wait-for-it.sh ]; then
    wget -O /var/www/html/wait-for-it.sh https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh
fi
chmod +x /var/www/html/wait-for-it.sh

# Wait for MySQL with explicit TCP check
echo "Waiting for MySQL..."
/var/www/html/wait-for-it.sh mysql:3306 --timeout=60 --strict -- echo "MySQL is up!"

# Debug network connectivity
echo "Testing MySQL host resolution and port..."
ping -c 4 mysql || echo "Ping failed!"
nc -zv mysql 3306 || echo "Port 3306 not reachable!"

echo "Installing Composer dependencies..."
composer install --no-interaction --optimize-autoloader

echo "Generating Laravel application key..."
php artisan key:generate --force

# php artisan vendor:publish --provider "OwenIt\Auditing\AuditingServiceProvider" --tag="config"
# ech "configuring audit package"

# Debug database connection explicitly
echo "Checking database connection..."
php artisan config:clear

echo "Checking database connection..."
php artisan db:show || echo "Database connection check failed, proceeding anyway..."

echo "Running database migrations..."
php artisan migrate --force

echo "Seeding Data"
if ! php artisan db:seed; then
  echo "Seeding failed, but continuing..."
fi

echo "Starting Supervisord..."
exec "$@"