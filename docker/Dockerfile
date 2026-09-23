# ==============================================================================
# Stage 1: Composer Dependency Builder
# ==============================================================================
FROM composer:2 AS builder

WORKDIR /app

# Copy dependency definitions
COPY composer.json composer.lock* ./

# Install dependencies (fallback to update if lockfile is missing)
RUN if [ -f composer.lock ]; then \
        composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs; \
    else \
        composer update --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs; \
    fi

# ==============================================================================
# Stage 2: Ultra-lightweight Runtime (PHP 8.3 FPM + Caddy on Alpine)
# ==============================================================================
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install Caddy binary directly from official multi-arch image
COPY --from=caddy:2-alpine /usr/bin/caddy /usr/bin/caddy

# Install essential runtime tools and PHP extension installer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install required PHP extensions for WordPress + SQLite + MariaDB
RUN apk add --no-cache \
        sqlite-libs \
        ca-certificates \
        tzdata \
    && install-php-extensions \
        pdo_sqlite \
        sqlite3 \
        mysqli \
        pdo_mysql \
        opcache \
        gd \
        zip \
        intl \
        exif

ADD --chmod=0755 https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar /usr/local/bin/wp

# Configure PHP-FPM for VPS 512MB RAM footprint (ondemand, max_children=3)
RUN echo "[www]" > /usr/local/etc/php-fpm.d/zz-docker-lowram.conf \
    && echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.d/zz-docker-lowram.conf \
    && echo "pm = ondemand" >> /usr/local/etc/php-fpm.d/zz-docker-lowram.conf \
    && echo "pm.max_children = 3" >> /usr/local/etc/php-fpm.d/zz-docker-lowram.conf \
    && echo "pm.process_idle_timeout = 10s" >> /usr/local/etc/php-fpm.d/zz-docker-lowram.conf \
    && echo "pm.max_requests = 500" >> /usr/local/etc/php-fpm.d/zz-docker-lowram.conf

# Configure PHP runtime & Opcache settings
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "upload_max_filesize = 32M" >> /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "post_max_size = 32M" >> /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "opcache.enable = 1" >> /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "opcache.enable_cli = 0" >> /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "opcache.memory_consumption = 64" >> /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "opcache.interned_strings_buffer = 8" >> /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "opcache.max_accelerated_files = 10000" >> /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "opcache.revalidate_freq = 60" >> /usr/local/etc/php/conf.d/zz-lowram.ini \
    && echo "opcache.fast_shutdown = 1" >> /usr/local/etc/php/conf.d/zz-lowram.ini

# Copy Caddy configuration
COPY docker/Caddyfile /etc/caddy/Caddyfile

# Copy composer vendor dependencies from builder stage
COPY --from=builder /app/vendor /var/www/html/vendor

# Copy application source files and WordPress core from builder
COPY config /var/www/html/config
COPY web /var/www/html/web
COPY --from=builder /app/web /var/www/html/web
COPY composer.json /var/www/html/composer.json
COPY .env.example /var/www/html/.env.example

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create persistent storage directories and assign permissions to www-data
RUN mkdir -p /var/www/html/web/app/database \
             /var/www/html/web/app/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 750 /var/www/html/web/app/database

# Expose Caddy HTTP port
EXPOSE 8080

# Define entrypoint and default command
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["caddy", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
