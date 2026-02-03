#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -lt 3 ]; then
  echo "Usage: $0 <user> <host> <path>"
  echo "Example: $0 deploy@server.com /var/www/sor4"
  exit 1
fi

USER="$1"
HOST="$2"
DEST="$3"

RSYNC_EXCLUDES=(--exclude='.git' --exclude='vendor' --exclude='node_modules' --exclude='.env' --exclude='storage' --exclude='public/storage')

echo "Syncing files to ${USER}@${HOST}:${DEST}"
rsync -az --delete "${RSYNC_EXCLUDES[@]}" ./ ${USER}@${HOST}:${DEST}

echo "Running remote deployment commands"
ssh ${USER}@${HOST} <<'SSH'
set -e
cd "${DEST}"
if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader --no-interaction
fi
if [ -f artisan ]; then
  php artisan migrate --force || true
  php artisan config:cache || true
  php artisan route:cache || true
fi
SSH

echo "Deployment finished."
