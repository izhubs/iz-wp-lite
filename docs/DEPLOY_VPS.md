# Deploying to a VPS

This guide uses **Hetzner CX11** (Ubuntu 22.04 LTS, 512MB RAM, ~\$4.50/month) as the reference target. The same steps apply to BuyVM, Vultr, DigitalOcean, Contabo, and any VPS running Ubuntu 22.04.

---

## 1. Provision the server

### Create a Hetzner server

1. Sign up at [hetzner.com/cloud](https://hetzner.com/cloud)
2. New Server → Location: Nuremberg or Singapore → Image: **Ubuntu 22.04** → Type: **CX11 (2 vCPU, 512MB RAM, 20GB SSD)**
3. Add your SSH public key
4. Create

### SSH into the server

```bash
ssh root@<your-vps-ip>
```

### Install Docker

```bash
curl -fsSL https://get.docker.com | sh
systemctl enable docker
systemctl start docker
docker --version
```

### Create a non-root deploy user (recommended)

```bash
useradd -m -s /bin/bash deploy
usermod -aG docker deploy
mkdir -p /home/deploy/.ssh
cp ~/.ssh/authorized_keys /home/deploy/.ssh/
chown -R deploy:deploy /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
chmod 600 /home/deploy/.ssh/authorized_keys
```

---

## 2. Add swap (required for 512MB VPS)

MariaDB spikes RAM on startup. Swap prevents OOM kills during initialization:

```bash
fallocate -l 1G /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab

# Use swap only when necessary (reduces SSD wear)
echo 'vm.swappiness=10' >> /etc/sysctl.conf
sysctl -p
```

Verify:
```bash
free -h
# Swap: 1.0Gi total
```

---

## 3. Install iz-wp-lite

SSH as the `deploy` user:
```bash
ssh deploy@<your-vps-ip>
```

One-command installer (Tier 1 default, fits CX11):
```bash
curl -fsSL https://raw.githubusercontent.com/izhubs/iz-wp-lite/main/install.sh | bash -s my-site
cd my-site
```

Or manually:
```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site
cd my-site
cp .env.example .env
```

---

## 4. Configure .env for production

```bash
nano .env
```

Update the following:

```ini
WP_ENV=production
WP_HOME=https://yourdomain.com
WP_SITEURL=https://yourdomain.com/wp

DB_ENGINE=mysql
DB_NAME=wp_lite
DB_USER=wp_user
DB_PASSWORD=<strong-random-password>
DB_HOST=mariadb:3306

# Generate at: https://roots.io/salts.html
AUTH_KEY='<64-char-random-string>'
SECURE_AUTH_KEY='<64-char-random-string>'
LOGGED_IN_KEY='<64-char-random-string>'
NONCE_KEY='<64-char-random-string>'
AUTH_SALT='<64-char-random-string>'
SECURE_AUTH_SALT='<64-char-random-string>'
LOGGED_IN_SALT='<64-char-random-string>'
NONCE_SALT='<64-char-random-string>'
```

---

## 5. Configure Caddyfile for production (real domain + auto HTTPS)

`docker/Caddyfile` ships with `auto_https off` and port 8080 for local development. For production with a real domain, replace the entire file:

```bash
nano docker/Caddyfile
```

```caddyfile
{
    admin off
    email admin@yourdomain.com    # Let's Encrypt notification address
}

yourdomain.com {
    root * /var/www/html/web
    encode gzip zstd

    @blocked {
        path */.*
        path *.env*
        path /config/*
        path /vendor/*
        path *.sqlite*
        path *.db*
        path /app/database/*
    }
    respond @blocked 403

    @xmlrpc { path /xmlrpc.php }
    respond @xmlrpc 403

    @wp_content { path_regexp wpcontent ^/wp-content/(.*)$ }
    rewrite @wp_content /app/{re.wpcontent.1}

    file_server

    @cached_page {
        not method POST
        not path_regexp ^/(wp-admin|wp-login\.php)
        not header Cookie "*wordpress_logged_in*"
        file {
            try_files /app/cache/page_cache/{uri}/index.html /app/cache/cache-enabler/{host}{uri}/index.html
        }
    }
    rewrite @cached_page {http.matchers.file.relative}

    php_fastcgi 127.0.0.1:9000 {
        index index.php
    }

    @static { path *.css *.js *.ico *.gif *.jpg *.jpeg *.png *.svg *.woff *.woff2 *.webp *.avif }
    header @static Cache-Control "public, max-age=31536000, immutable"

    header {
        X-Content-Type-Options "nosniff"
        X-Frame-Options "SAMEORIGIN"
        Referrer-Policy "strict-origin-when-cross-origin"
        Strict-Transport-Security "max-age=31536000; includeSubDomains"
    }

    log {
        output stdout
        format console
        level INFO
    }
}
```

Update `docker/docker-compose.yml` to expose ports 80 and 443:
```yaml
ports:
  - "80:80"
  - "443:443"
  - "443:443/udp"   # HTTP/3 QUIC
```

---

## 6. Point DNS to the VPS

DNS must resolve before starting Caddy (Caddy validates domain ownership for SSL):

```
A     yourdomain.com        <your-vps-ip>
A     www.yourdomain.com    <your-vps-ip>   # optional
```

Set TTL to 300 (5 minutes) for fast propagation. Verify:
```bash
dig yourdomain.com +short
# Must return <your-vps-ip>
```

---

## 7. Start

```bash
docker compose -f docker/docker-compose.yml up -d --build
```

Caddy automatically requests a Let's Encrypt SSL certificate. First issuance takes ~30 seconds.

Verify:
```bash
curl -I https://yourdomain.com

docker compose -f docker/docker-compose.yml ps

docker compose -f docker/docker-compose.yml logs app --follow
```

---

## 8. Auto-start on reboot (systemd)

```bash
sudo nano /etc/systemd/system/iz-wp-lite.service
```

```ini
[Unit]
Description=iz-wp-lite WordPress stack
After=docker.service
Requires=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/home/deploy/my-site
ExecStart=/usr/bin/docker compose -f docker/docker-compose.yml up -d
ExecStop=/usr/bin/docker compose -f docker/docker-compose.yml down
User=deploy
Group=deploy

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable iz-wp-lite
sudo systemctl start iz-wp-lite
```

Test after reboot:
```bash
sudo reboot
# SSH back in after ~60 seconds
docker ps   # Containers must be running automatically
```

---

## 9. Backup

### Database

```bash
cat > /home/deploy/backup-db.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/home/deploy/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"

docker compose -f /home/deploy/my-site/docker/docker-compose.yml exec -T mariadb \
  mariadb-dump \
  -u"${DB_USER:-wp_user}" \
  -p"${DB_PASSWORD:-wp_secure_password}" \
  "${DB_NAME:-wp_lite}" \
  | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Retain last 7 days
find "$BACKUP_DIR" -name "db_*.sql.gz" -mtime +7 -delete
echo "Backup saved: $BACKUP_DIR/db_$DATE.sql.gz"
EOF

chmod +x /home/deploy/backup-db.sh
```

Schedule daily at 03:00:
```bash
crontab -e
# Add:
0 3 * * * /home/deploy/backup-db.sh >> /home/deploy/backup.log 2>&1
```

### Uploads

```bash
# Pull uploads to local machine
rsync -avz deploy@<your-vps-ip>:/home/deploy/my-site/web/app/uploads/ ./backup-uploads/
```

---

## 10. Updating WordPress and plugins

```bash
cd /home/deploy/my-site
git pull
docker compose -f docker/docker-compose.yml exec app composer update --no-interaction

# Rebuild only if Dockerfile changed
docker compose -f docker/docker-compose.yml up -d --build

docker compose -f docker/docker-compose.yml exec app wp --allow-root cache flush
```

---

## 11. Monitoring RAM

```bash
# Live container resource usage
docker stats --no-stream

# Verify memory limits are applied
docker inspect iz-wp-lite-app | grep -i memory

# MariaDB InnoDB buffer pool status
docker compose -f docker/docker-compose.yml exec mariadb \
  mariadb -u root -e "SHOW ENGINE INNODB STATUS\G" 2>/dev/null | grep -A5 "BUFFER POOL"
```

---

## Firewall (UFW)

```bash
ufw allow ssh
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 443/udp    # HTTP/3
ufw enable
ufw status
```

---

## Troubleshooting

| Symptom | Diagnostic command | Fix |
|---|---|---|
| Site not loading | `docker compose logs app` | Check PHP or Caddy errors |
| SSL not issued | `docker compose logs app \| grep acme` | DNS not propagated yet — wait |
| MariaDB crash | `docker compose logs mariadb` | OOM — check `free -h`, increase swap |
| Site slow | `docker stats` | RAM pressure — add swap or upgrade VPS tier |
| "Error establishing DB connection" | `docker compose ps mariadb` | MariaDB still initializing — wait 15–30s |
| Uploads not saving | `docker compose exec app ls -la web/app/uploads` | Permission issue — run `chown -R www-data` |
