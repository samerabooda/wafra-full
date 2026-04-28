#!/bin/bash
# ═══════════════════════════════════════════════════════════════
# Wafra Gulf — Production Deployment Script
# Run this ONCE after uploading files to server
# Usage: bash deploy.sh
# ═══════════════════════════════════════════════════════════════

set -e
echo "🚀 Wafra Gulf — Deployment Script"
echo "=================================="

# 1. Install dependencies (no dev)
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 2. Copy .env if not exists
if [ ! -f .env ]; then
    echo "📋 Creating .env from .env.example..."
    cp .env.example .env
    echo "⚠️  IMPORTANT: Edit .env with your actual DB credentials and APP_KEY!"
    php artisan key:generate
else
    echo "✅ .env already exists"
fi

# 3. Run migrations + seed
echo "🗄️  Running migrations..."
php artisan migrate --force

echo "🌱 Seeding database..."
php artisan db:seed --force

# 4. Clear and cache everything for production
echo "⚡ Optimizing for production..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache
php artisan event:clear
php artisan optimize

# 5. Set correct file permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 644 storage/logs
find storage -type d -exec chmod 755 {} \;
find storage -type f -exec chmod 644 {} \;

# 6. Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link 2>/dev/null || echo "Storage link already exists"

echo ""
echo "✅ Deployment complete!"
echo ""
echo "📋 CHECKLIST:"
echo "  1. Edit .env → set DB_*, APP_URL, SESSION_DOMAIN"
echo "  2. Set APP_DEBUG=false in .env"
echo "  3. Set SESSION_DRIVER=database in .env"
echo "  4. Default login: finance@wafragulf.com / Wafra@2026!"
echo "  5. Change password immediately after first login"
