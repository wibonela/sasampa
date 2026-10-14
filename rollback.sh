#!/bin/bash

# ===========================================
# SASAMPA POS - FULL ROLLBACK SCRIPT
# ===========================================
# Use this to restore from a backup if something
# goes wrong after deployment.
#
# Can restore:
# - Database
# - Environment file (.env)
# - Uploaded files
# ===========================================

BACKUP_DIR="${BACKUP_DIR:-/var/backups/sasampa}"
APP_DIR="${APP_DIR:-/var/www/sasampa}"

# Shared database helpers (SQLite today, PostgreSQL after cutover)
source "$APP_DIR/db-lib.sh"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo "==========================================="
echo -e "   ${BLUE}SASAMPA ROLLBACK UTILITY${NC}"
echo "==========================================="
echo ""

# Check if backup directory exists
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${RED}Error: Backup directory not found at $BACKUP_DIR${NC}"
    exit 1
fi

# List available backups
echo "Available backups (newest first):"
echo "-------------------------------------------"

# Get unique backup dates
BACKUP_DATES=$(db_backup_ids 20)

if [ -z "$BACKUP_DATES" ]; then
    echo -e "${RED}No backups found!${NC}"
    exit 1
fi

for DATE in $BACKUP_DATES; do
    DB_FILE=$(db_backup_file "$DATE")
    ENV_FILE="$BACKUP_DIR/env_$DATE.backup"
    UPLOAD_FILE="$BACKUP_DIR/uploads_$DATE.tar.gz"

    # Get file info
    if [ -n "$DB_FILE" ]; then
        DB_SIZE=$(ls -lh "$DB_FILE" | awk '{print $5}')
        # Format the date nicely
        YEAR=${DATE:0:4}
        MONTH=${DATE:4:2}
        DAY=${DATE:6:2}
        HOUR=${DATE:9:2}
        MIN=${DATE:11:2}
        SEC=${DATE:13:2}

        echo -n "  $DATE"
        echo -n " | DB: ${DB_SIZE} (${DB_FILE##*.})"

        [ -f "$ENV_FILE" ] && echo -n " | .env: yes" || echo -n " | .env: no"
        [ -f "$UPLOAD_FILE" ] && echo -n " | uploads: yes" || echo -n " | uploads: no"

        echo " | ${YEAR}-${MONTH}-${DAY} ${HOUR}:${MIN}"
    fi
done

echo ""
echo "-------------------------------------------"
echo ""

# Ask what to restore
echo "What do you want to restore?"
echo "  1) Database only"
echo "  2) Database + .env"
echo "  3) Database + .env + uploads (full rollback)"
echo "  4) Cancel"
echo ""
read -p "Choice [1-4]: " RESTORE_CHOICE

if [ "$RESTORE_CHOICE" = "4" ] || [ -z "$RESTORE_CHOICE" ]; then
    echo "Rollback cancelled."
    exit 0
fi

echo ""
read -p "Enter the backup ID (e.g., 20260122_143052): " BACKUP_ID

# Validate input
if [ -z "$BACKUP_ID" ]; then
    echo -e "${RED}Error: No backup ID provided${NC}"
    exit 1
fi

# Check if files exist
DB_FILE=$(db_backup_file "$BACKUP_ID")
ENV_FILE="$BACKUP_DIR/env_$BACKUP_ID.backup"
UPLOAD_FILE="$BACKUP_DIR/uploads_$BACKUP_ID.tar.gz"

if [ -z "$DB_FILE" ]; then
    echo -e "${RED}Error: Database backup not found for ID: $BACKUP_ID${NC}"
    exit 1
fi

# A SQLite backup cannot be restored into a PostgreSQL setup (or the other way round)
# without also restoring the matching .env, so make that explicit.
BACKUP_KIND="${DB_FILE##*.}"
CURRENT_KIND="$(db_backup_ext)"
if [ "$BACKUP_KIND" != "$CURRENT_KIND" ]; then
    echo -e "${YELLOW}This backup is a .$BACKUP_KIND but the app currently uses .$CURRENT_KIND.${NC}"
    if [ "$RESTORE_CHOICE" -lt 2 ] || [ ! -f "$ENV_FILE" ]; then
        echo -e "${RED}Choose option 2 or 3 (restores the matching .env too) and make sure env_$BACKUP_ID.backup exists.${NC}"
        exit 1
    fi
fi

# Confirm restoration
echo ""
echo -e "${YELLOW}WARNING: This will replace current data!${NC}"
echo ""
echo "Files to restore:"
echo "  - Database: $DB_FILE"
[ "$RESTORE_CHOICE" -ge 2 ] && [ -f "$ENV_FILE" ] && echo "  - Environment: $ENV_FILE"
[ "$RESTORE_CHOICE" -ge 3 ] && [ -f "$UPLOAD_FILE" ] && echo "  - Uploads: $UPLOAD_FILE"
echo ""

read -p "Are you sure? Type 'yes' to confirm: " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Rollback cancelled."
    exit 0
fi

echo ""
echo -e "${BLUE}Starting rollback...${NC}"
echo ""

# Create a backup of current state before rollback
CURRENT_DATE=$(date +%Y%m%d_%H%M%S)
echo -e "${BLUE}[1/4]${NC} Backing up current state..."

PRE_FILE=$(db_backup "$BACKUP_DIR/database_${CURRENT_DATE}_pre_rollback") || PRE_FILE=""
if [ -n "$PRE_FILE" ]; then
    echo -e "${GREEN}✓${NC} Current database backed up ($(basename "$PRE_FILE"))"
else
    echo -e "${RED}Could not back up the current database - aborting before changing anything.${NC}"
    exit 1
fi

if [ -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env" "$BACKUP_DIR/env_${CURRENT_DATE}_pre_rollback.backup"
    echo -e "${GREEN}✓${NC} Current .env backed up"
fi

# Enable maintenance mode
echo ""
echo -e "${BLUE}[2/4]${NC} Enabling maintenance mode..."
cd $APP_DIR
php artisan down --retry=60 2>/dev/null || true
echo -e "${GREEN}✓${NC} Maintenance mode enabled"

# Restore database
echo ""
echo -e "${BLUE}[3/4]${NC} Restoring database..."
# Restore .env first when switching database type, so the restore targets the right database
if [ "$BACKUP_KIND" != "$CURRENT_KIND" ] && [ -f "$ENV_FILE" ]; then
    cp "$ENV_FILE" "$APP_DIR/.env"
    php artisan config:clear 2>/dev/null || true
fi
if db_restore "$DB_FILE"; then
    echo -e "${GREEN}✓${NC} Database restored"
else
    echo -e "${RED}Database restore FAILED. Your pre-rollback copy is $PRE_FILE${NC}"
    php artisan up 2>/dev/null || true
    exit 1
fi

# Restore .env if requested
if [ "$RESTORE_CHOICE" -ge 2 ] && [ -f "$ENV_FILE" ]; then
    echo ""
    echo -e "${BLUE}[3.1/4]${NC} Restoring .env..."
    cp "$ENV_FILE" "$APP_DIR/.env"
    echo -e "${GREEN}✓${NC} .env restored"
fi

# Restore uploads if requested
if [ "$RESTORE_CHOICE" -ge 3 ] && [ -f "$UPLOAD_FILE" ]; then
    echo ""
    echo -e "${BLUE}[3.2/4]${NC} Restoring uploads..."
    tar -xzf "$UPLOAD_FILE" -C "$APP_DIR/storage/app/"
    chown -R www-data:www-data "$APP_DIR/storage/app/public"
    echo -e "${GREEN}✓${NC} Uploads restored"
fi

# Clear caches and disable maintenance mode
echo ""
echo -e "${BLUE}[4/4]${NC} Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
echo -e "${GREEN}✓${NC} Caches cleared"

echo ""
echo -e "${BLUE}Disabling maintenance mode...${NC}"
php artisan up 2>/dev/null || true
echo -e "${GREEN}✓${NC} Maintenance mode disabled"

echo ""
echo "==========================================="
echo -e "   ${GREEN}ROLLBACK COMPLETE${NC}"
echo "==========================================="
echo ""
echo "Restored from backup: $BACKUP_ID"
echo ""
echo "Pre-rollback backups saved:"
echo "  - $PRE_FILE"
[ "$RESTORE_CHOICE" -ge 2 ] && echo "  - $BACKUP_DIR/env_${CURRENT_DATE}_pre_rollback.backup"
echo ""
echo "If you need to undo this rollback, use backup ID:"
echo "  ${CURRENT_DATE}_pre_rollback"
echo ""
