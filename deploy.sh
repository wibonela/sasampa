#!/bin/bash
set -e

# ===========================================
# SASAMPA POS - SAFE DEPLOYMENT SCRIPT
# ===========================================
# This script safely deploys updates from GitHub
# while protecting your production database and files.
#
# Features:
# - Full backup before deployment (DB, .env, uploads)
# - Automatic rollback on failure
# - Health check after deployment
# - Backup retention (keeps last 10)
# ===========================================

# Configuration
APP_DIR="/var/www/sasampa"
BACKUP_DIR="/var/backups/sasampa"
DATE=$(date +%Y%m%d_%H%M%S)
LOG_FILE="$BACKUP_DIR/deploy_$DATE.log"
KEEP_BACKUPS=10

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_step() {
    echo -e "${BLUE}[$1]${NC} $2"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Function to handle errors and rollback
handle_error() {
    print_error "Deployment failed at step: $1"
    print_warning "Attempting automatic rollback..."

    # Restore database if backup exists
    if [ -f "$BACKUP_DIR/database_$DATE.sqlite" ]; then
        cp "$BACKUP_DIR/database_$DATE.sqlite" "$APP_DIR/database/database.sqlite"
        chown www-data:www-data "$APP_DIR/database/database.sqlite" 2>/dev/null || true
        chmod 644 "$APP_DIR/database/database.sqlite" 2>/dev/null || true
        print_success "Database restored from backup"
    fi

    # Restore .env if backup exists
    if [ -f "$BACKUP_DIR/env_$DATE.backup" ]; then
        cp "$BACKUP_DIR/env_$DATE.backup" "$APP_DIR/.env"
        print_success ".env restored from backup"
    fi

    echo ""
    print_error "Deployment rolled back. Check the log: $LOG_FILE"
    exit 1
}

# Trap errors
trap 'handle_error "$CURRENT_STEP"' ERR

echo ""
echo "==========================================="
echo -e "   ${BLUE}SASAMPA SAFE DEPLOYMENT${NC}"
echo "==========================================="
echo "Started at: $(date)"
echo "Backup ID:  $DATE"
echo ""

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Start logging
exec > >(tee -a "$LOG_FILE") 2>&1

# ===========================================
# STEP 1: PRE-DEPLOYMENT CHECKS
# ===========================================
CURRENT_STEP="Pre-deployment checks"
print_step "1/8" "Running pre-deployment checks..."

cd $APP_DIR

# Check if we can reach GitHub
if ! git ls-remote --exit-code origin main > /dev/null 2>&1; then
    print_error "Cannot reach GitHub repository"
    exit 1
fi
print_success "GitHub connection OK"

# Check disk space (need at least 500MB free)
DISK_FREE=$(df -m "$APP_DIR" | awk 'NR==2 {print $4}')
if [ "$DISK_FREE" -lt 500 ]; then
    print_error "Low disk space: ${DISK_FREE}MB free (need 500MB minimum)"
    exit 1
fi
print_success "Disk space OK (${DISK_FREE}MB free)"

# Show what will be deployed
echo ""
echo "Changes to be deployed:"
echo "------------------------"
git fetch origin main --quiet
LOCAL_HEAD=$(git rev-parse HEAD)
REMOTE_HEAD=$(git rev-parse origin/main)

if [ "$LOCAL_HEAD" = "$REMOTE_HEAD" ]; then
    print_warning "No new changes to deploy"
    echo ""
    read -p "Continue anyway? (y/n): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Deployment cancelled."
        exit 0
    fi
else
    git log --oneline HEAD..origin/main | head -20
    COMMIT_COUNT=$(git rev-list --count HEAD..origin/main)
    echo ""
    print_success "$COMMIT_COUNT new commit(s) to deploy"
fi
echo ""

# ===========================================
# STEP 2: CREATE FULL BACKUP
# ===========================================
CURRENT_STEP="Backup creation"
print_step "2/8" "Creating full backup..."

# Backup database
if [ -f "$APP_DIR/database/database.sqlite" ]; then
    cp "$APP_DIR/database/database.sqlite" "$BACKUP_DIR/database_$DATE.sqlite"
    DB_SIZE=$(ls -lh "$BACKUP_DIR/database_$DATE.sqlite" | awk '{print $5}')
    print_success "Database backed up ($DB_SIZE)"
else
    print_warning "No database found to backup"
fi

# Backup .env
if [ -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env" "$BACKUP_DIR/env_$DATE.backup"
    print_success ".env backed up"
fi

# Backup uploaded files (storage/app/public)
if [ -d "$APP_DIR/storage/app/public" ] && [ "$(ls -A $APP_DIR/storage/app/public 2>/dev/null)" ]; then
    tar -czf "$BACKUP_DIR/uploads_$DATE.tar.gz" -C "$APP_DIR/storage/app" public 2>/dev/null || true
    if [ -f "$BACKUP_DIR/uploads_$DATE.tar.gz" ]; then
        UPLOAD_SIZE=$(ls -lh "$BACKUP_DIR/uploads_$DATE.tar.gz" | awk '{print $5}')
        print_success "Uploads backed up ($UPLOAD_SIZE)"
    fi
else
    print_warning "No uploads to backup"
fi
echo ""

# ===========================================
# STEP 3: ENABLE MAINTENANCE MODE
# ===========================================
CURRENT_STEP="Maintenance mode"
print_step "3/8" "Enabling maintenance mode..."
php artisan down --retry=60 --refresh=15 || true
print_success "Maintenance mode enabled"
echo ""

# ===========================================
# STEP 4: PULL LATEST CODE
# ===========================================
CURRENT_STEP="Git pull"
print_step "4/8" "Pulling latest code from GitHub..."
git reset --hard origin/main
CURRENT_COMMIT=$(git rev-parse --short HEAD)
print_success "Code updated to commit: $CURRENT_COMMIT"
echo ""

# ===========================================
# STEP 5: INSTALL DEPENDENCIES
# ===========================================
CURRENT_STEP="Dependencies"
print_step "5/8" "Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet
print_success "Dependencies installed"
echo ""

# ===========================================
# STEP 6: RUN MIGRATIONS
# ===========================================
CURRENT_STEP="Migrations"
print_step "6/8" "Running database migrations..."
php artisan migrate --force
print_success "Migrations complete"
echo ""

# ===========================================
# STEP 7: REBUILD CACHES
# ===========================================
CURRENT_STEP="Cache rebuild"
print_step "7/8" "Rebuilding caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Caches rebuilt"
echo ""

# ===========================================
# STEP 8: FINALIZE
# ===========================================
CURRENT_STEP="Finalization"
print_step "8/8" "Finalizing deployment..."

# Set permissions
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR/storage
chmod -R 755 $APP_DIR/bootstrap/cache
chmod 644 $APP_DIR/database/database.sqlite 2>/dev/null || true

# Disable maintenance mode
php artisan up
print_success "Maintenance mode disabled"

# ===========================================
# POST-DEPLOYMENT HEALTH CHECK
# ===========================================
echo ""
print_step "✓" "Running health check..."

# Check if app responds
if command -v curl &> /dev/null; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost --max-time 10 2>/dev/null || echo "000")
    if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
        print_success "Application responding (HTTP $HTTP_CODE)"
    else
        print_warning "Application returned HTTP $HTTP_CODE"
    fi
fi

# Check database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" 2>/dev/null | grep -q "OK" && \
    print_success "Database connection OK" || \
    print_warning "Could not verify database connection"

# ===========================================
# CLEANUP OLD BACKUPS
# ===========================================
echo ""
print_step "~" "Cleaning up old backups (keeping last $KEEP_BACKUPS)..."

# Remove old database backups
ls -1t $BACKUP_DIR/database_*.sqlite 2>/dev/null | tail -n +$((KEEP_BACKUPS + 1)) | xargs -r rm -f
# Remove old env backups
ls -1t $BACKUP_DIR/env_*.backup 2>/dev/null | tail -n +$((KEEP_BACKUPS + 1)) | xargs -r rm -f
# Remove old upload backups
ls -1t $BACKUP_DIR/uploads_*.tar.gz 2>/dev/null | tail -n +$((KEEP_BACKUPS + 1)) | xargs -r rm -f
# Remove old logs
ls -1t $BACKUP_DIR/deploy_*.log 2>/dev/null | tail -n +$((KEEP_BACKUPS + 1)) | xargs -r rm -f

BACKUP_COUNT=$(ls -1 $BACKUP_DIR/database_*.sqlite 2>/dev/null | wc -l)
print_success "Cleanup complete ($BACKUP_COUNT backups retained)"

# ===========================================
# DEPLOYMENT SUMMARY
# ===========================================
echo ""
echo "==========================================="
echo -e "   ${GREEN}DEPLOYMENT SUCCESSFUL${NC}"
echo "==========================================="
echo "Finished at: $(date)"
echo "Commit:      $CURRENT_COMMIT"
echo "Backup ID:   $DATE"
echo "Log file:    $LOG_FILE"
echo ""
echo "Backups created:"
echo "  - $BACKUP_DIR/database_$DATE.sqlite"
echo "  - $BACKUP_DIR/env_$DATE.backup"
[ -f "$BACKUP_DIR/uploads_$DATE.tar.gz" ] && echo "  - $BACKUP_DIR/uploads_$DATE.tar.gz"
echo ""
echo "If something is wrong, run:"
echo "  sudo /var/www/sasampa/rollback.sh"
echo ""
