#!/usr/bin/env bash
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

print_help(){
  echo "Usage: $0 [--deps]"
  echo "  --deps   Install PHP and JS dependencies and run basic setup"
}

if [ "$#" -gt 0 ]; then
  case "$1" in
    --deps)
      echo "Installing PHP dependencies..."
      if command -v composer >/dev/null 2>&1; then
        composer install --no-interaction --prefer-dist || composer install --no-interaction
      else
        echo "composer not found in PATH"
      fi

      if [ -f package.json ]; then
        echo "Installing JS dependencies..."
        if command -v npm >/dev/null 2>&1; then
          npm ci || npm install
          echo "Running npm build (if defined)..."
          npm run build || true
        else
          echo "npm not found in PATH"
        fi
      fi

      if [ -f artisan ]; then
        echo "Running artisan basic setup..."
        php artisan key:generate --force || true
      fi

      echo "Dependencies installed."
      exit 0
      ;;
    -h|--help)
      print_help
      exit 0
      ;;
    *)
      print_help
      exit 1
      ;;
  esac
fi

# Default: start dev server
if [ -f artisan ]; then
  echo "Starting artisan serve on 0.0.0.0:8000"
  php artisan serve --host=0.0.0.0 --port=8000
else
  if [ -d public ]; then
    echo "Starting PHP built-in server on 0.0.0.0:8000 (public/)"
    php -S 0.0.0.0:8000 -t public
  else
    echo "No 'artisan' or 'public' directory found — nothing to serve."
    exit 1
  fi
fi
