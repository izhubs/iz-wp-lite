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

# Wait loop: check PHP-FPM socket/port ready
until [ -S /var/run/php-fpm.sock ] || nc -z 127.0.0.1 9000 2>/dev/null; do
  sleep 0.1
done

# Signal trap chuyển tiếp tín hiệu tới php-fpm
trap 'kill -TERM $(pidof php-fpm) 2>/dev/null; kill -TERM $caddy_pid 2>/dev/null; wait $caddy_pid' TERM INT

# Execute main process (Caddy)
"$@" &
caddy_pid=$!
wait $caddy_pid
