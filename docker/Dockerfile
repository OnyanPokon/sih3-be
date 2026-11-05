FROM dunglas/frankenphp:php8.2.35-bookworm
ENV TZ=Asia/Makassar XDG_CONFIG_HOME=/tmp/caddy/config XDG_DATA_HOME=/tmp/caddy/data
WORKDIR /app
RUN apt-get update && apt-get install -y --no-install-recommends curl ffmpeg default-mysql-client unzip \
    && install-php-extensions pdo_mysql gd intl zip opcache redis exif bcmath \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ARG HOST_UID=1000
ARG HOST_GID=1000
RUN test "$HOST_UID" -gt 0 && test "$HOST_GID" -gt 0 \
    && groupadd -g "$HOST_GID" app && useradd -u "$HOST_UID" -g app -m app \
    && chown app:app /app
COPY --chown=app:app composer.json composer.lock ./
USER app
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts
COPY --chown=app:app app ./app
COPY --chown=app:app bootstrap/app.php bootstrap/providers.php ./bootstrap/
COPY --chown=app:app config ./config
COPY --chown=app:app database/migrations ./database/migrations
COPY --chown=app:app database/factories ./database/factories
COPY --chown=app:app database/seeders ./database/seeders
COPY --chown=app:app public ./public
COPY --chown=app:app resources/views ./resources/views
COPY --chown=app:app routes ./routes
COPY --chown=app:app stubs ./stubs
COPY --chown=app:app artisan ./artisan
USER root
RUN chown -R app:app /app
USER app
RUN mkdir -p bootstrap/cache storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && ln -s /app/storage/app/public public/storage \
    && composer dump-autoload --no-dev --no-interaction --optimize --no-scripts
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/app-entrypoint
ENTRYPOINT ["/usr/local/bin/app-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
