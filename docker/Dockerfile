FROM dunglas/frankenphp:1.3.6-php8.4.2

# Set timezone agar semua layanan sinkron (opsional tapi bagus untuk log)
ENV TZ=Asia/Makassar

# Install supervisor, unzip, dan php extensions (1 layer supaya cache optimal)
RUN apt-get update && \
    apt-get install -y --no-install-recommends unzip supervisor ffmpeg && \
    install-php-extensions \
        pdo_mysql \
        gd \
        intl \
        zip \
        opcache \
        redis \
        exif && \
    rm -rf /var/lib/apt/lists/*

# Salin konfigurasi PHP (di-cache selama tidak diubah)
COPY ./php.ini "$PHP_INI_DIR/php.ini"

# Install Composer (gunakan checksum biar lebih aman dan cache-aware)
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get install -y --no-install-recommends default-mysql-client
RUN apt-get update && apt-get install -y --no-install-recommends bash \
    curl \
    tzdata \
    cron

COPY ./scheduler/cron /etc/cron.d/artisan-schedule
RUN chmod 0644 /etc/cron.d/artisan-schedule && crontab /etc/cron.d/artisan-schedule

# Buat log folder
RUN mkdir -p /var/log && touch /var/log/cron.log
RUN apt-get install -y nano

# Jalankan cron sebagai proses utama container
# CMD ["cron", "-f"]

