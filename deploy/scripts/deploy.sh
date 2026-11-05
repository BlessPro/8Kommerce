#!/usr/bin/env bash
set -Eeuo pipefail

umask 077
BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COMPOSE=(docker compose --project-directory "$BASE_DIR" --env-file "$BASE_DIR/.env" -f "$BASE_DIR/compose.production.yml")

if [[ ! -f "$BASE_DIR/.env" ]]; then
    echo "Missing $BASE_DIR/.env. Copy .env.production.example and configure it first." >&2
    exit 1
fi

cd "$BASE_DIR"

"${COMPOSE[@]}" config --quiet

if "${COMPOSE[@]}" ps --status running --services 2>/dev/null | grep -qx app; then
    "$BASE_DIR/scripts/backup.sh" predeploy
fi

"${COMPOSE[@]}" pull
"${COMPOSE[@]}" up -d --remove-orphans --wait --wait-timeout 240
"${COMPOSE[@]}" exec -T app php artisan optimize
"${COMPOSE[@]}" exec -T app php artisan queue:restart

APP_DOMAIN="$(sed -n 's/^APP_DOMAIN=//p' "$BASE_DIR/.env" | tail -n 1)"
STATUS_DOMAIN="$(sed -n 's/^STATUS_DOMAIN=//p' "$BASE_DIR/.env" | tail -n 1)"

curl --fail --silent --show-error --retry 12 --retry-delay 5 --retry-all-errors "https://${APP_DOMAIN}/live" >/dev/null
curl --fail --silent --show-error --retry 12 --retry-delay 5 --retry-all-errors "https://${STATUS_DOMAIN}/up?format=json" >/dev/null

"${COMPOSE[@]}" ps
echo "Deployment healthy: https://${APP_DOMAIN} and https://${STATUS_DOMAIN}"
