#!/bin/bash
# ===========================================
# SASAMPA - shared database helpers
# ===========================================
# Sourced by deploy.sh, backup.sh, rollback.sh and cutover-to-postgres.sh.
# Works with SQLite (today) and PostgreSQL (after cutover); the mode comes from
# DB_CONNECTION in the app's .env, so the same scripts keep working on both.
# Needs APP_DIR to be set by the caller.

env_get() {
    # env_get KEY [default] - read a value from $APP_DIR/.env (strips quotes and CR)
    local val
    val=$(grep -E "^$1=" "$APP_DIR/.env" 2>/dev/null | tail -n1 | cut -d= -f2- | tr -d '\r')
    val="${val%\"}"; val="${val#\"}"; val="${val%\'}"; val="${val#\'}"
    [ -n "$val" ] && echo "$val" || echo "${2:-}"
}

db_driver() { env_get DB_CONNECTION sqlite; }

sqlite_path() {
    local p
    p=$(env_get DB_DATABASE "")
    case "$p" in
        "")  echo "$APP_DIR/database/database.sqlite" ;;
        /*)  echo "$p" ;;
        *)   echo "$APP_DIR/$p" ;;
    esac
}

# File extension of a database backup for the current driver
db_backup_ext() { [ "$(db_driver)" = "pgsql" ] && echo "pgdump" || echo "sqlite"; }

# db_backup <dest-file-without-extension>
# Writes <dest>.sqlite or <dest>.pgdump, prints the path, returns non-zero on failure.
# Backups hold customer data, so they are created readable by the owner only.
db_backup() {
    local base="$1" out
    ( umask 077
      case "$(db_driver)" in
        pgsql)
            out="$base.pgdump"
            PGPASSWORD="$(env_get DB_PASSWORD)" pg_dump -Fc --no-owner \
                -h "$(env_get DB_HOST 127.0.0.1)" -p "$(env_get DB_PORT 5432)" \
                -U "$(env_get DB_USERNAME)" -f "$out" "$(env_get DB_DATABASE)" || exit 1
            ;;
        *)
            local src; src="$(sqlite_path)"
            out="$base.sqlite"
            [ -f "$src" ] || exit 2
            # .backup is consistent even while the app is writing; plain cp is only a fallback
            if command -v sqlite3 >/dev/null 2>&1; then
                sqlite3 "$src" ".backup '$out'" || exit 1
            else
                cp "$src" "$out" || exit 1
            fi
            ;;
      esac
      echo "$out" )
}

# db_restore <backup-file>  - replaces the current database with the backup
db_restore() {
    local file="$1"
    case "$file" in
        *.pgdump)
            # --single-transaction: either everything is restored or nothing changes
            PGPASSWORD="$(env_get DB_PASSWORD)" pg_restore --clean --if-exists --no-owner --no-privileges \
                --single-transaction -h "$(env_get DB_HOST 127.0.0.1)" -p "$(env_get DB_PORT 5432)" \
                -U "$(env_get DB_USERNAME)" -d "$(env_get DB_DATABASE)" "$file"
            ;;
        *.sqlite)
            local dst; dst="$(sqlite_path)"
            cp "$file" "$dst"
            chown www-data:www-data "$dst" 2>/dev/null || true
            chmod 644 "$dst" 2>/dev/null || true
            ;;
        *) echo "Unknown backup type: $file" >&2; return 1 ;;
    esac
}

# Delete old database backups of both kinds, keeping the newest $1
db_prune_backups() {
    local keep="$1"
    ls -1t "$BACKUP_DIR"/database_*.sqlite "$BACKUP_DIR"/database_*.pgdump 2>/dev/null \
        | tail -n +$((keep + 1)) | xargs -r rm -f
}

# Newest-first list of backup IDs (works for both extensions, ignores duplicates)
db_backup_ids() {
    ls -1 "$BACKUP_DIR"/database_*.sqlite "$BACKUP_DIR"/database_*.pgdump 2>/dev/null \
        | sed -E 's/.*database_(.*)\.(sqlite|pgdump)$/\1/' | sort -ru | head -"${1:-20}"
}

# Path of the database backup for an ID (either extension), or empty
db_backup_file() {
    for ext in pgdump sqlite; do
        [ -f "$BACKUP_DIR/database_$1.$ext" ] && { echo "$BACKUP_DIR/database_$1.$ext"; return; }
    done
}
