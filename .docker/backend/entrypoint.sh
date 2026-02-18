#!/bin/bash
set -e

cd /var/www/html

# Copy .env from example if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Install Composer dependencies if vendor is missing
if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Generate application key if not set
if ! grep -q '^APP_KEY=base64:' .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Wait for PostgreSQL to be ready
echo "Waiting for PostgreSQL..."
until pg_isready -h "${DB_HOST:-pgsql}" -U "${DB_USERNAME:-postgres}" -q 2>/dev/null; do
    sleep 2
done
echo "PostgreSQL is ready."

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed database (uses updateOrCreate so safe to re-run)
echo "Seeding database..."
php artisan db:seed --force

echo "Starting Laravel development server..."
exec php artisan serve --host=0.0.0.0 --port=80
