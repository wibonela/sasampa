#!/bin/bash
# ===========================================
# SASAMPA - SWITCH THE LIVE APP FROM SQLITE TO POSTGRESQL
# ===========================================
# Run on the server:  sudo bash cutover-to-postgres.sh
#
# What it does (the site shows the maintenance page for a few minutes):
#   1. checks PostgreSQL is reachable with the credentials in .env.pgsql
#   2. backs up the SQLite database and .env
#   3. puts the app in maintenance mode and snapshots SQLite
#   4. rebuilds the PostgreSQL schema, copies every row and verifies the counts + revenue
#   5. switches .env to PostgreSQL, rebuilds caches, health-checks the app
#   6. any failure before step 5 leaves the live app on SQLite, untouched;
#      a failed health check in step 5 puts the old .env back automatically
# The SQLite file is never modified or deleted, so you can always go back with rollback.sh.
#
# Needs $APP_DIR/.env.pgsql with the new connection, for example:
#   DB_CONNECTION=pgsql
#   DB_HOST=127.0.0.1
#   DB_PORT=5432
#   DB_DATABASE=sasampa
#   DB_USERNAME=sasampa
#   DB_PASSWORD=...
#   CACHE_STORE=file
# ===========================================
set -uo pipefail

APP_DIR="${APP_DIR:-/var/www/sasampa}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/sasampa}"
PG_ENV_FILE="${PG_ENV_FILE:-$APP_DIR/.env.pgsql}"
HEALTH_URL="${HEALTH_URL:-http://localhost}"
DATE=$(date +%Y%m%d_%H%M%S)

source "$APP_DIR/db-lib.sh"
cd "$APP_DIR" || exit 1

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
step() { echo -e "${BLUE}[$1]${NC} $2"; }
ok()   { echo -e "${GREEN}✓${NC} $1"; }
warn() { echo -e "${YELLOW}⚠${NC} $1"; }
fail() { echo -e "${RED}✗${NC} $1"; }

MAINTENANCE=0
abort() {
    fail "$1"
    if [ "$MAINTENANCE" = 1 ]; then
        php artisan up >/dev/null 2>&1 && ok "App is live again on its previous database (nothing was changed)"
    fi
    exit 1
}

pg_value() { grep -E "^$1=" "$PG_ENV_FILE" | tail -n1 | cut -d= -f2- | tr -d '\r'; }

# ---------- 0. checks ----------
step 0 "Checking prerequisites"
[ "$(db_driver)" = "sqlite" ] || abort "The app is already on '$(db_driver)'. Nothing to do."
[ -f "$PG_ENV_FILE" ] || abort "Missing $PG_ENV_FILE (see the header of this script)."
for k in DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
    [ -n "$(pg_value $k)" ] || abort "$k is missing in $PG_ENV_FILE"
done
[ "$(pg_value DB_CONNECTION)" = "pgsql" ] || abort "DB_CONNECTION in $PG_ENV_FILE must be pgsql"
command -v sqlite3 >/dev/null && command -v pg_dump >/dev/null || abort "sqlite3 and pg_dump must be installed"

export PGPASSWORD="$(pg_value DB_PASSWORD)"
psql -h "$(pg_value DB_HOST)" -p "$(pg_value DB_PORT)" -U "$(pg_value DB_USERNAME)" -d "$(pg_value DB_DATABASE)" \
    -Atc "select 1" >/dev/null 2>&1 || abort "Cannot connect to PostgreSQL with the credentials in $PG_ENV_FILE"
ok "PostgreSQL reachable"
SQLITE_FILE="$(sqlite_path)"
[ -f "$SQLITE_FILE" ] || abort "SQLite database not found at $SQLITE_FILE"
ok "SQLite database found ($(du -h "$SQLITE_FILE" | cut -f1))"

echo ""
echo "This will put the site in maintenance mode, copy ALL data from SQLite into the"
echo "PostgreSQL database '$(pg_value DB_DATABASE)' (anything already in it is erased) and switch the app to it."
read -p "Type CUTOVER to continue: " CONFIRM
[ "$CONFIRM" = "CUTOVER" ] || { echo "Cancelled."; exit 0; }

# ---------- 1. backups ----------
step 1 "Backing up SQLite and .env"
mkdir -p "$BACKUP_DIR"
DB_BACKUP_FILE=$(db_backup "$BACKUP_DIR/database_$DATE") || abort "SQLite backup failed"
( umask 077; cp "$APP_DIR/.env" "$BACKUP_DIR/env_$DATE.backup" ) || abort ".env backup failed"
ok "Backup ID $DATE ($(basename "$DB_BACKUP_FILE"), env_$DATE.backup)"

# ---------- 2. maintenance + snapshot ----------
step 2 "Maintenance mode and snapshot"
php artisan down --retry=60 --refresh=15 >/dev/null 2>&1 || true
MAINTENANCE=1
SNAP="$BACKUP_DIR/cutover_snapshot_$DATE.sqlite"
( umask 077; sqlite3 "$SQLITE_FILE" ".backup '$SNAP'" ) || abort "Snapshot failed"
ok "Snapshot taken"

# ---------- 3. build and fill PostgreSQL ----------
step 3 "Rebuilding the PostgreSQL schema and copying data"
php artisan config:clear >/dev/null 2>&1 || true
PGENV=(DB_CONNECTION=pgsql DB_HOST="$(pg_value DB_HOST)" DB_PORT="$(pg_value DB_PORT)"
       DB_DATABASE="$(pg_value DB_DATABASE)" DB_USERNAME="$(pg_value DB_USERNAME)" DB_PASSWORD="$(pg_value DB_PASSWORD)"
       DB_URL= CACHE_STORE=file SESSION_DRIVER=file QUEUE_CONNECTION=sync MAIL_MAILER=log)
env "${PGENV[@]}" php artisan migrate:fresh --force >/dev/null 2>&1 || abort "Creating the PostgreSQL schema failed"
ok "Schema created"
COPY_LOG=$(mktemp)
if ! env "${PGENV[@]}" php artisan db:copy-from-sqlite "$SNAP" >"$COPY_LOG" 2>&1; then
    tail -n 12 "$COPY_LOG" | cut -c1-200
    rm -f "$COPY_LOG"
    abort "Copying data failed"
fi
grep -E "transactions total|Verified" "$COPY_LOG"
rm -f "$COPY_LOG"
ok "Data copied and verified"

# ---------- 4. switch .env ----------
step 4 "Switching the app to PostgreSQL"
python3 - "$APP_DIR/.env" "$PG_ENV_FILE" <<'PY' || abort "Could not update .env"
import re, sys
env_path, new_path = sys.argv[1], sys.argv[2]
s = open(env_path).read()
for line in open(new_path):
    line = line.strip()
    if not line or line.startswith('#') or '=' not in line:
        continue
    k, v = line.split('=', 1)
    if re.search(r'(?m)^%s=' % re.escape(k), s):
        s = re.sub(r'(?m)^%s=.*$' % re.escape(k), lambda m: '%s=%s' % (k, v), s)
    else:
        s = s.rstrip('\n') + '\n%s=%s\n' % (k, v)
s = re.sub(r'(?m)^DB_URL=.*\n', '', s)
open(env_path, 'w').write(s)
PY

revert_env() {
    cp "$BACKUP_DIR/env_$DATE.backup" "$APP_DIR/.env"
    php artisan config:clear >/dev/null 2>&1 || true
    php artisan config:cache >/dev/null 2>&1 || true
}

php artisan config:clear >/dev/null 2>&1
php artisan config:cache >/dev/null 2>&1 || { revert_env; abort "config:cache failed - old .env restored"; }
php artisan route:cache >/dev/null 2>&1 || true
php artisan view:cache >/dev/null 2>&1 || true
php artisan cache:clear >/dev/null 2>&1 || true

# ---------- 5. health check on the new database ----------
step 5 "Health check"
DRIVER=$(php artisan tinker --execute="echo DB::connection()->getDriverName();" 2>/dev/null | tail -n1)
PG_COMPANIES=$(php artisan tinker --execute="echo DB::table('companies')->count();" 2>/dev/null | tail -n1)
SQ_COMPANIES=$(sqlite3 "$SNAP" "select count(*) from companies")
if [ "$DRIVER" != "pgsql" ] || [ "$PG_COMPANIES" != "$SQ_COMPANIES" ]; then
    revert_env
    abort "Health check failed (driver=$DRIVER, companies pg=$PG_COMPANIES sqlite=$SQ_COMPANIES) - old .env restored"
fi
ok "App is on PostgreSQL ($PG_COMPANIES companies)"

chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
php artisan up >/dev/null 2>&1
MAINTENANCE=0
ok "Maintenance mode off"

HTTP=$(curl -s -o /dev/null -w "%{http_code}" "$HEALTH_URL" --max-time 10 2>/dev/null) || true
HTTP=${HTTP:-000}
if [ "$HTTP" = "200" ] || [ "$HTTP" = "301" ] || [ "$HTTP" = "302" ]; then
    ok "Site responding (HTTP $HTTP)"
else
    warn "Site returned HTTP $HTTP - check it now. To go back: sudo bash rollback.sh and pick backup $DATE (option 2)."
fi

echo ""
echo "==========================================="
echo -e "   ${GREEN}NOW RUNNING ON POSTGRESQL${NC}"
echo "==========================================="
echo "Backup ID for going back: $DATE  (rollback.sh, option 2 restores the SQLite .env)"
echo "The SQLite file is untouched at: $SQLITE_FILE"
echo "Take a first PostgreSQL backup now:  sudo bash $APP_DIR/backup.sh"
echo ""
