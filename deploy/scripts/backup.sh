#!/usr/bin/env bash
set -Eeuo pipefail

umask 077
BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="$BASE_DIR/backups"
LABEL="${1:-scheduled}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
COMPOSE=(docker compose --project-directory "$BASE_DIR" --env-file "$BASE_DIR/.env" -f "$BASE_DIR/compose.production.yml")

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

DB_TMP="$BACKUP_DIR/database-${LABEL}-${TIMESTAMP}.dump.tmp"
DB_FINAL="${DB_TMP%.tmp}"
STORAGE_TMP="$BACKUP_DIR/storage-${LABEL}-${TIMESTAMP}.tar.gz.tmp"
STORAGE_FINAL="${STORAGE_TMP%.tmp}"

cleanup() {
    rm -f "$DB_TMP" "$STORAGE_TMP"
}
trap cleanup EXIT

"${COMPOSE[@]}" exec -T db sh -c 'PGPASSWORD="$POSTGRES_PASSWORD" pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" -Fc' > "$DB_TMP"
test -s "$DB_TMP"
mv "$DB_TMP" "$DB_FINAL"

"${COMPOSE[@]}" exec -T app tar -C /var/www/html/storage/app -czf - . > "$STORAGE_TMP"
test -s "$STORAGE_TMP"
mv "$STORAGE_TMP" "$STORAGE_FINAL"

find "$BACKUP_DIR" -maxdepth 1 -type f \( -name 'database-*.dump' -o -name 'storage-*.tar.gz' \) -mtime +14 -delete

sha256sum "$DB_FINAL" "$STORAGE_FINAL" > "$BACKUP_DIR/checksums-${LABEL}-${TIMESTAMP}.sha256"
find "$BACKUP_DIR" -maxdepth 1 -type f -name 'checksums-*.sha256' -mtime +14 -delete

trap - EXIT
echo "Backup complete: $DB_FINAL and $STORAGE_FINAL"
