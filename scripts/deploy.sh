#!/usr/bin/env bash
# Deploy theme + plugin to staging or prod over the `nixihost` SSH alias.
# Usage: scripts/deploy.sh staging|prod [--dry-run]
set -euo pipefail

TARGET="${1:-}"
DRY="${2:-}"
case "$TARGET" in
  staging) DOCROOT="/home/baireyco/staging.jeffclarkart.com" ;;
  prod)    DOCROOT="/home/baireyco/jeffclarkart.com" ;;
  *) echo "usage: $0 staging|prod [--dry-run]" >&2; exit 1 ;;
esac

HOST="nixihost"
REPO="$(cd "$(dirname "$0")/.." && pwd)"
RSYNC=(rsync -az --delete --exclude '.DS_Store' --exclude '*.map')
[[ "$DRY" == "--dry-run" ]] && RSYNC+=(--dry-run -v)

echo "→ $TARGET ($DOCROOT)"
"${RSYNC[@]}" "$REPO/wp-content/themes/jca/"          "$HOST:$DOCROOT/wp-content/themes/jca/"
"${RSYNC[@]}" "$REPO/wp-content/plugins/jca-catalog/" "$HOST:$DOCROOT/wp-content/plugins/jca-catalog/"

if [[ "$DRY" != "--dry-run" ]]; then
  ssh "$HOST" "cd '$DOCROOT' && ~/bin/wp cache flush --quiet && (~/bin/wp litespeed-purge all --quiet 2>/dev/null || true) && ~/bin/wp rewrite flush --quiet"
  echo "✓ deployed and caches flushed"
fi
