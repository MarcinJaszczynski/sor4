#!/usr/bin/env bash
set -euo pipefail

if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
fi

DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}
DB_NAME=${DB_DATABASE:-database}
DB_USER=${DB_USERNAME:-root}
DB_PASS=${DB_PASSWORD:-}

OUTDIR=${1:-./backups}
mkdir -p "$OUTDIR"
TIMESTAMP=$(date +%F-%H%M)
FILE="$OUTDIR/${DB_NAME}_$TIMESTAMP.sql.gz"

echo "Backing up database $DB_NAME to $FILE"
mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$FILE"

echo "Backup completed: $FILE"
