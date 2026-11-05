#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
if [[ ! -f .env ]]; then
  cp .env.example .env
  task_uid=${SUDO_UID:-$(id -u)}; task_gid=${SUDO_GID:-$(id -g)}
  [[ "$task_uid" -gt 0 ]] || task_uid=1000
  [[ "$task_gid" -gt 0 ]] || task_gid=1000
  sed -i "s/^HOST_UID=.*/HOST_UID=$task_uid/;s/^HOST_GID=.*/HOST_GID=$task_gid/" .env
fi
[[ -f ../.env ]] || cp ../.env.example ../.env
# The active routes serve JSON; the unused welcome scaffold needs no Vite artifact.
docker compose build "$@"
image=$(docker compose config --images | head -n1)
if ! python3 - <<'CHECK'
import re, sys
s = open('../.env').read()
m = re.search(r'^APP_KEY=(.*)$', s, re.M)
sys.exit(0 if m and m.group(1).strip().strip('"\'') else 1)
CHECK
then
  docker run --rm --network none --user "$(id -u):$(id -g)" \
    -v "$(cd .. && pwd)/.env:/app/.env" --entrypoint php "$image" artisan key:generate --force --no-interaction >/dev/null
fi
./prepare-storage.sh
echo 'Build dan permission siap. Jalankan docker compose up -d --force-recreate.'
