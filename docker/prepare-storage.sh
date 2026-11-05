#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
image=$(docker compose config --images | head -n1)
runtime_uid=$(docker run --rm --network none --entrypoint id "$image" -u app)
runtime_gid=$(docker run --rm --network none --entrypoint id "$image" -g app)
[[ "$runtime_uid" -gt 0 && "$runtime_gid" -gt 0 ]]
target=${STORAGE_TARGET:-"$(pwd)/../volume/data"}
# Reject symlinks in every component, including ancestors.
python3 - "$target" <<'PY'
import os, sys
p = os.path.abspath(sys.argv[1])
while p != '/':
    if os.path.islink(p):
        sys.exit('Storage target contains symlink: ' + p)
    p = os.path.dirname(p)
PY
docker run --rm --network none --user 0:0 --security-opt no-new-privileges:true \
  --cap-drop ALL --cap-add CHOWN --cap-add FOWNER --cap-add DAC_OVERRIDE \
  -v "$target:/data" --entrypoint sh "$image" -ec '
    find /data -xdev -type d -exec chown "$1:$2" {} + -exec chmod u+rwx {} +
    find /data -xdev -type f -exec chown "$1:$2" {} + -exec chmod u+rw {} +
  ' sh "$runtime_uid" "$runtime_gid"
docker run --rm --network none --security-opt no-new-privileges:true --cap-drop ALL \
  -v "$target:/data" --entrypoint sh "$image" -ec 'f=$(mktemp /data/.write-check.XXXXXX); rm "$f"'
