#!/bin/bash
# =============================================================
# Wafra Gulf - cPanel Deployment Script
# Run this script via SSH after uploading the project files
# Usage: bash deploy.sh
# =============================================================

set -e

echo ">>> [1/7] Setting file permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod +x artisan

echo ">>> [2/7] Installing production dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

echo ">>> [3/7] Generating application key (if not set)..."
php artisan key:generate --no-interaction

echo ">>> [4/7] Running database migrations..."
php artisan migrate --force --no-interaction

echo ">>> [5/7] Creating storage symlink..."
php artisan storage:link --no-interaction

echo ">>> [6/7] Optimizing for production..."
php artisan optimize

echo ">>> [7/7] Clearing old caches..."
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo ""
echo "✓ Deployment complete!"
echo "  Make sure .env is configured with your production settings."
