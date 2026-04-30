#!/bin/bash
# ══════════════════════════════════════════════════════════════
# Wafra Gulf — First-Time Server Setup
# Run ONCE after cloning: bash server-setup.sh
# ══════════════════════════════════════════════════════════════

set -e
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✅ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠️  $1${NC}"; }
fail() { echo -e "${RED}❌ $1${NC}"; exit 1; }

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║  🏦 Wafra Gulf — Server Setup            ║"
echo "╚══════════════════════════════════════════╝"
echo ""

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# ── Step 1: PHP composer install ─────────────────────────────
echo "📦 Installing PHP dependencies..."
/usr/local/bin/php /usr/local/bin/composer install \
    --no-dev --optimize-autoloader --no-interaction || fail "Composer failed"
ok "Composer done"

# ── Step 2: .env file ─────────────────────────────────────────
if [ ! -f ".env" ]; then
    cp .env.example .env
    warn ".env created from .env.example — please edit DB settings!"
else
    ok ".env exists"
fi

# ── Step 3: App key ───────────────────────────────────────────
/usr/local/bin/php artisan key:generate --force --no-interaction
ok "App key generated"

# ── Step 4: Fix permissions ───────────────────────────────────
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod 644 .htaccess
chmod 644 public/.htaccess
chmod 644 public/index.php
ok "Permissions fixed"

# ── Step 5: Run migrations ────────────────────────────────────
/usr/local/bin/php artisan migrate --force --no-interaction
ok "Migrations done"

# ── Step 6: Seed database ─────────────────────────────────────
read -p "Seed the database with test data? (y/N): " seed
if [ "$seed" = "y" ] || [ "$seed" = "Y" ]; then
    /usr/local/bin/php artisan db:seed --force --no-interaction
    ok "Database seeded"
fi

# ── Step 7: Storage symlink ───────────────────────────────────
/usr/local/bin/php artisan storage:link --force
ok "Storage symlink created"

# ── Step 8: Optimize ─────────────────────────────────────────
/usr/local/bin/php artisan optimize:clear
/usr/local/bin/php artisan optimize
ok "Optimized for production"

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║  ✅ Setup Complete!                       ║"
echo "║  Visit: https://system-wafragulf.online  ║"
echo "╚══════════════════════════════════════════╝"
