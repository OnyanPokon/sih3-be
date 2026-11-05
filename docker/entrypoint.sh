#!/bin/sh
set -eu
cd /app
: "${APP_KEY:?APP_KEY kosong; jalankan docker/build.sh}"
# Refuse custom generated-cache locations rather than delete arbitrary paths.
for name in APP_CONFIG_CACHE APP_SERVICES_CACHE APP_PACKAGES_CACHE APP_ROUTES_CACHE APP_EVENTS_CACHE; do
    eval 'value=${'"$name"'-}'
    if [ -n "$value" ]; then
        echo "$name harus memakai lokasi bootstrap/cache standar" >&2
        exit 1
    fi
done
mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
if [ ! -L public/storage ]; then
    [ ! -e public/storage ] || { echo 'public/storage bukan symlink' >&2; exit 1; }
    ln -s /app/storage/app/public public/storage
fi
[ "$(readlink public/storage)" = /app/storage/app/public ] || { echo 'Target public/storage tidak sesuai' >&2; exit 1; }
find bootstrap/cache -maxdepth 1 -type f -name '*.php' -delete
php artisan package:discover --ansi
php artisan optimize:clear
php artisan optimize
exec "$@"
