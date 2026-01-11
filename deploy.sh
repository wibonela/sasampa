#!/bin/bash
set -e

# ===========================================
# SASAMPA POS - SAFE DEPLOYMENT SCRIPT
# ===========================================
# This script safely deploys updates from GitHub
# while protecting your production database.
# ===========================================

# Configuration
APP_DIR="/var/www/sasampa"
BACKUP_DIR="/var/backups/sasampa"
DATE=$(date +%Y%m%d_%H%M%S)

echo ""
echo "==========================================="
echo "   SASAMPA SAFE DEPLOYMENT"
echo "==========================================="
echo "Started at: $(date)"
echo ""

# Step 1: Create backup BEFORE anything else
echo "[1/6] Creating backup..."
mkdir -p $BACKUP_DIR
if [ -f "$APP_DIR/database/database.sqlite" ]; then
    cp "$APP_DIR/database/database.sqlite" "$BACKUP_DIR/database_$DATE.sqlite"
    echo "       Database backed up to: database_$DATE.sqlite"
else
    echo "       Warning: No database found to backup"
fi
if [ -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env" "$BACKUP_DIR/env_$DATE.backup"
    echo "       Environment backed up to: env_$DATE.backup"
fi
echo ""

# Step 2: Pull latest code from GitHub
echo "[2/6] Pulling latest code from GitHub..."
cd $APP_DIR
git fetch origin
git reset --hard origin/main
echo ""

# Step 3: Install/update dependencies
echo "[3/6] Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo ""

# Step 4: Run migrations (additive only - NEVER fresh)
echo "[4/6] Running migrations..."
php artisan migrate --force
echo ""

# Step 5: Clear and rebuild caches
echo "[5/6] Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo ""

# Step 6: Set permissions
echo "[6/6] Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR/storage
chmod -R 755 $APP_DIR/bootstrap/cache
chmod 644 $APP_DIR/database/database.sqlite 2>/dev/null || true
echo ""

echo "==========================================="
echo "   DEPLOYMENT COMPLETE"
echo "==========================================="
echo "Finished at: $(date)"
echo ""
echo "Backup location: $BACKUP_DIR/database_$DATE.sqlite"
echo ""
echo "If something is wrong, run:"
echo "  sudo /var/www/sasampa/rollback.sh"
echo ""
