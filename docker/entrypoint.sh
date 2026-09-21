#!/bin/sh
set -e

# DECISION: Lightweight Process Coordination / WHY: Avoid supervisord Python/C memory footprint on VPS 512MB / TRADE-OFF: Basic signal forwarding / REF: POSIX sh
# @ai-context Starts PHP-FPM daemon before passing execution to Caddy.

# Ensure correct permissions on writable volumes
mkdir -p /var/www/html/web/app/database /var/www/html/web/app/uploads
chown -R www-data:www-data /var/www/html/web/app/database /var/www/html/web/app/uploads
chmod 750 /var/www/html/web/app/database

# Start PHP-FPM as daemon on port 9000
php-fpm -D

# Execute main process (Caddy)
exec "$@"
