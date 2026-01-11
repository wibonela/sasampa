#!/bin/bash

# ===========================================
# SASAMPA POS - DAILY BACKUP SCRIPT
# ===========================================
# Run this via cron for automatic daily backups
# Keeps last 30 days of backups
# ===========================================

APP_DIR="/var/www/sasampa"
BACKUP_DIR="/var/backups/sasampa"
DATE=$(date +%Y%m%d_%H%M%S)
LOG_FILE="$BACKUP_DIR/backup.log"

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Backup database
if [ -f "$APP_DIR/database/database.sqlite" ]; then
    cp "$APP_DIR/database/database.sqlite" "$BACKUP_DIR/database_$DATE.sqlite"
    echo "$(date '+%Y-%m-%d %H:%M:%S') - SUCCESS: Backup created - database_$DATE.sqlite" >> $LOG_FILE
else
    echo "$(date '+%Y-%m-%d %H:%M:%S') - WARNING: No database found at $APP_DIR/database/database.sqlite" >> $LOG_FILE
fi

# Backup .env file (weekly - only on Sundays)
if [ $(date +%u) -eq 7 ]; then
    if [ -f "$APP_DIR/.env" ]; then
        cp "$APP_DIR/.env" "$BACKUP_DIR/env_$DATE.backup"
        echo "$(date '+%Y-%m-%d %H:%M:%S') - SUCCESS: Weekly .env backup created" >> $LOG_FILE
    fi
fi

# Cleanup: Keep only last 30 days of database backups
find $BACKUP_DIR -name "database_*.sqlite" -mtime +30 -delete 2>/dev/null
find $BACKUP_DIR -name "env_*.backup" -mtime +90 -delete 2>/dev/null

# Cleanup: Keep log file under 1MB
if [ -f "$LOG_FILE" ] && [ $(stat -f%z "$LOG_FILE" 2>/dev/null || stat -c%s "$LOG_FILE" 2>/dev/null) -gt 1048576 ]; then
    tail -1000 "$LOG_FILE" > "$LOG_FILE.tmp"
    mv "$LOG_FILE.tmp" "$LOG_FILE"
fi
