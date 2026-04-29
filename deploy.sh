#!/bin/bash
# ═══════════════════════════════════════════════════════════════
# Wafra Gulf — Production Deploy Script
# Usage:
#   bash deploy.sh          → full deploy
#   bash deploy.sh --check  → check status only (no deploy)
#   bash deploy.sh --seed   → run seeder after migrate
#   bash deploy.sh --rollback → rollback last migration
# ═══════════════════════════════════════════════════════════════

set -euo pipefail

# ── Colors ────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; BOLD='\033[1m'; NC='\033[0m'

ok()   { echo -e "${GREEN}  ✅ $1${NC}"; }
fail() { echo -e "${RED}  ❌ $1${NC}"; exit 1; }
warn() { echo -e "${YELLOW}  ⚠️  $1${NC}"; }
info() { echo -e "${BLUE}  ℹ  $1${NC}"; }
step() { echo -e "\n${BOLD}── $1${NC}"; }

START_TIME=$(date +%s)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo ""
echo -e "${BOLD}╔══════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║  🏦 وفرة الخليجية — Deploy Script       ║${NC}"
echo -e "${BOLD}╚══════════════════════════════════════════╝${NC}"
echo -e "  Directory : ${SCRIPT_DIR}"
echo -e "  Date      : $(date '+%Y-%m-%d %H:%M:%S')"
echo -e "  User      : $(whoami)"
echo ""

# ── Parse arguments ───────────────────────────────────────────
CHECK_ONLY=false
RUN_SEEDER=false
ROLLBACK=false

for arg in "$@"; do
  case $arg in
    --check)    CHECK_ONLY=true ;;
    --seed)     RUN_SEEDER=true ;;
    --rollback) ROLLBACK=true ;;
  esac
done

# ── Pre-flight checks ─────────────────────────────────────────
step "PRE-FLIGHT CHECKS"

# PHP version
PHP_VER=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "0.0")
if php -r "exit(version_compare(PHP_VERSION,'8.1','<') ? 1 : 0);" 2>/dev/null; then
  ok "PHP ${PHP_VER} ✓"
else
  fail "PHP 8.1+ required (found ${PHP_VER})"
fi

# Composer
command -v composer &>/dev/null && ok "Composer found" || fail "Composer not found"

# Git
command -v git &>/dev/null && ok "Git found" || fail "Git not found"

# .env exists
[ -f "${SCRIPT_DIR}/.env" ] && ok ".env exists" || fail ".env not found — copy .env.example and configure"

# Check DB connection
php artisan db:show --no-interaction &>/dev/null && ok "Database connection OK" || fail "Cannot connect to database — check .env DB settings"

# Check storage is writable
[ -w "${SCRIPT_DIR}/storage" ] && ok "Storage directory writable" || fail "storage/ not writable — run: chmod -R 775 storage"
[ -w "${SCRIPT_DIR}/bootstrap/cache" ] && ok "Bootstrap cache writable" || fail "bootstrap/cache/ not writable"

# Check APP_KEY
APP_KEY=$(grep "^APP_KEY=" "${SCRIPT_DIR}/.env" | cut -d'=' -f2)
[ -n "$APP_KEY" ] && ok "APP_KEY set" || warn "APP_KEY empty — run: php artisan key:generate"

if $CHECK_ONLY; then
  info "Check only mode — no changes made"
  exit 0
fi

if $ROLLBACK; then
  step "ROLLBACK"
  warn "Rolling back last migration..."
  php artisan migrate:rollback --step=1 --force
  ok "Rollback complete"
  exit 0
fi

# ── 1. Pull latest code ───────────────────────────────────────
step "1. GIT PULL"

# Check for uncommitted local changes
if ! git diff --quiet HEAD 2>/dev/null; then
  warn "Local uncommitted changes detected — stashing..."
  git stash
fi

BEFORE=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
git pull origin master 2>&1 | tail -3
AFTER=$(git rev-parse HEAD 2>/dev/null || echo "unknown")

if [ "$BEFORE" = "$AFTER" ]; then
  info "Already up to date ($(git log -1 --format='%h %s'))"
else
  ok "Updated: ${BEFORE:0:7} → ${AFTER:0:7}"
  info "$(git log --oneline HEAD~1..HEAD)"
fi

# ── 2. PHP Dependencies ───────────────────────────────────────
step "2. COMPOSER INSTALL"

composer install \
  --no-dev \
  --no-interaction \
  --optimize-autoloader \
  --quiet

ok "Dependencies installed"

# ── 3. Clear old caches BEFORE migrate ───────────────────────
step "3. CLEAR CACHE (pre-migrate)"

php artisan config:clear  --quiet && ok "Config cache cleared"
php artisan route:clear   --quiet && ok "Route cache cleared"
php artisan view:clear    --quiet && ok "View cache cleared"
php artisan cache:clear   --quiet && ok "Application cache cleared"
php artisan event:clear   --quiet 2>/dev/null || true

# ── 4. Run migrations ─────────────────────────────────────────
step "4. MIGRATIONS"

# Show pending migrations
PENDING=$(php artisan migrate:status --no-interaction 2>/dev/null | grep "Pending" | wc -l || echo "0")
info "Pending migrations: ${PENDING}"

if [ "$PENDING" -gt "0" ]; then
  php artisan migrate --force --no-interaction
  ok "Migrations completed"
else
  ok "No pending migrations"
fi

# Seed if requested
if $RUN_SEEDER; then
  warn "Running seeder (--seed flag passed)..."
  php artisan db:seed --force --no-interaction
  ok "Seeder completed"
fi

# ── 5. Storage link ───────────────────────────────────────────
step "5. STORAGE LINK"
php artisan storage:link --force --quiet 2>/dev/null || true
ok "Storage symlink ready"

# ── 6. Optimize for production ────────────────────────────────
step "6. OPTIMIZE"

# Only cache in production
APP_ENV=$(grep "^APP_ENV=" "${SCRIPT_DIR}/.env" | cut -d'=' -f2 | tr -d '"'"'" || echo "production")

if [ "$APP_ENV" = "production" ]; then
  php artisan config:cache  --quiet && ok "Config cached"
  php artisan route:cache   --quiet && ok "Routes cached"
  php artisan view:cache    --quiet && ok "Views cached"
  php artisan event:cache   --quiet 2>/dev/null || true
else
  warn "Skipping cache in non-production env (${APP_ENV})"
fi

# Composer autoload optimize
composer dump-autoload --optimize --quiet
ok "Autoload optimized"

# ── 7. Queue restart (if using queues) ───────────────────────
step "7. QUEUE"
if php artisan queue:restart --quiet 2>/dev/null; then
  ok "Queue workers restarted"
else
  info "No queue workers running (skip)"
fi

# ── 8. Health check ───────────────────────────────────────────
step "8. HEALTH CHECK"

# Check app responds
APP_URL=$(grep "^APP_URL=" "${SCRIPT_DIR}/.env" | cut -d'=' -f2 | tr -d '"'"'" || echo "")
if [ -n "$APP_URL" ]; then
  HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "${APP_URL}/api/cards/stats" 2>/dev/null || echo "000")
  if [ "$HTTP_CODE" = "200" ]; then
    ok "App responding HTTP ${HTTP_CODE}"
  else
    warn "App returned HTTP ${HTTP_CODE} (may be normal during deploy)"
  fi
else
  info "APP_URL not set — skipping HTTP check"
fi

# Check migration status
php artisan migrate:status --no-interaction 2>/dev/null | grep -c "Ran" | xargs -I{} info "Migrations ran: {}"

# ── Summary ───────────────────────────────────────────────────
END_TIME=$(date +%s)
ELAPSED=$((END_TIME - START_TIME))

echo ""
echo -e "${BOLD}╔══════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║  ✅ Deploy Complete                      ║${NC}"
echo -e "${BOLD}╚══════════════════════════════════════════╝${NC}"
echo -e "  Time     : ${ELAPSED}s"
echo -e "  Commit   : $(git log -1 --format='%h — %s' 2>/dev/null || echo 'unknown')"
echo -e "  PHP      : ${PHP_VER}"
echo -e "  Env      : ${APP_ENV}"
echo ""
