#!/bin/bash

# ===========================================
# SASAMPA POS - DATABASE ROLLBACK SCRIPT
# ===========================================
# Use this to restore from a backup if something
# goes wrong after deployment.
# ===========================================

BACKUP_DIR="/var/backups/sasampa"
APP_DIR="/var/www/sasampa"

echo ""
echo "==========================================="
echo "   SASAMPA DATABASE ROLLBACK"
echo "==========================================="
echo ""

# Check if backup directory exists
if [ ! -d "$BACKUP_DIR" ]; then
    echo "Error: Backup directory not found at $BACKUP_DIR"
    exit 1
fi

# List available backups
echo "Available database backups (newest first):"
echo "-------------------------------------------"
ls -1t $BACKUP_DIR/database_*.sqlite 2>/dev/null | head -20 | while read file; do
    filename=$(basename "$file")
    filesize=$(ls -lh "$file" | awk '{print $5}')
    filedate=$(stat -f "%Sm" -t "%Y-%m-%d %H:%M" "$file" 2>/dev/null || stat -c "%y" "$file" 2>/dev/null | cut -d'.' -f1)
    echo "  $filename ($filesize) - $filedate"
done

if [ $(ls -1 $BACKUP_DIR/database_*.sqlite 2>/dev/null | wc -l) -eq 0 ]; then
    echo "  No backups found!"
    exit 1
fi

echo ""
echo "-------------------------------------------"
echo ""
read -p "Enter the backup filename to restore (e.g., database_20260111_120000.sqlite): " BACKUP_FILE

# Validate input
if [ -z "$BACKUP_FILE" ]; then
    echo "Error: No filename provided"
    exit 1
fi

# Check if file exists
if [ ! -f "$BACKUP_DIR/$BACKUP_FILE" ]; then
    echo "Error: Backup file not found: $BACKUP_DIR/$BACKUP_FILE"
    exit 1
fi

# Confirm restoration
echo ""
echo "WARNING: This will replace the current database!"
read -p "Are you sure you want to restore from $BACKUP_FILE? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Rollback cancelled."
    exit 0
fi

# Create a backup of current database before rollback
CURRENT_DATE=$(date +%Y%m%d_%H%M%S)
if [ -f "$APP_DIR/database/database.sqlite" ]; then
    cp "$APP_DIR/database/database.sqlite" "$BACKUP_DIR/database_${CURRENT_DATE}_pre_rollback.sqlite"
    echo "Current database backed up as: database_${CURRENT_DATE}_pre_rollback.sqlite"
fi

# Perform rollback
cp "$BACKUP_DIR/$BACKUP_FILE" "$APP_DIR/database/database.sqlite"
chown www-data:www-data "$APP_DIR/database/database.sqlite"
chmod 644 "$APP_DIR/database/database.sqlite"

echo ""
echo "==========================================="
echo "   ROLLBACK COMPLETE"
echo "==========================================="
echo "Database restored from: $BACKUP_FILE"
echo "Pre-rollback backup: database_${CURRENT_DATE}_pre_rollback.sqlite"
echo ""
