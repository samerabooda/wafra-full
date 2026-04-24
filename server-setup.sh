#!/bin/bash
# =============================================================
# Wafra Gulf - cPanel First-Time Server Setup Script
# Run this ONCE via SSH after creating the GitHub repo
# Usage: bash server-setup.sh
# =============================================================

set -e

REPO_URL="https://github.com/YOUR_USERNAME/wafra-full.git"
PROJECT_DIR="$HOME/wafra-full"

echo "==> [1/6] Cloning repository..."
git clone "$REPO_URL" "$PROJECT_DIR"
cd "$PROJECT_DIR"

echo "==> [2/6] Installing dependencies (production only)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> [3/6] Setting up environment file..."
cp .env.production .env
echo ""
echo "  >>> IMPORTANT: Edit .env now with your real DB credentials <<<"
echo "  >>> Run: nano .env"
echo ""
read -p "Press ENTER after editing .env to continue..."

echo "==> [4/6] Generating application key..."
php artisan key:generate --no-interaction

echo "==> [5/6] Running migrations..."
php artisan migrate --force --no-interaction

echo "==> [6/6] Setting permissions and optimizing..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod +x artisan
php artisan storage:link --no-interaction
php artisan optimize

echo ""
echo "✓ Server setup complete!"
echo ""
echo "Next steps in cPanel:"
echo "  1. Go to Domains > yourdomain.com"
echo "  2. Set Document Root to: wafra-full/public"
echo "  3. Visit your domain to verify it works"
