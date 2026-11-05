#!/usr/bin/env bash
set -Eeuo pipefail

umask 077
BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="$BASE_DIR/.env"
IMAGE="${1:-}"

if [[ ! "$IMAGE" =~ ^ghcr\.io/blesspro/8kommerce@sha256:[a-f0-9]{64}$ ]]; then
    echo "Usage: $0 ghcr.io/blesspro/8kommerce@sha256:<64-character-digest>" >&2
    exit 1
fi

cp "$ENV_FILE" "$ENV_FILE.before-rollback"
TMP_FILE="$(mktemp "$BASE_DIR/.env.XXXXXX")"

awk -v image="$IMAGE" '
    BEGIN { replaced = 0 }
    /^APP_IMAGE=/ { print "APP_IMAGE=" image; replaced = 1; next }
    { print }
    END { if (! replaced) print "APP_IMAGE=" image }
' "$ENV_FILE" > "$TMP_FILE"

chmod 600 "$TMP_FILE"
mv "$TMP_FILE" "$ENV_FILE"
exec "$BASE_DIR/scripts/deploy.sh"
