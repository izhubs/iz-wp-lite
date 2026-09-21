# Deploy to VPS (Bare Metal)

Hướng dẫn này dùng **Hetzner CX11** (Ubuntu 22.04 LTS, 512MB RAM, ~\$4.50/tháng) làm ví dụ. Các bước tương tự áp dụng cho BuyVM, Vultr, DigitalOcean, Contabo.

---

## 1. Chuẩn bị VPS

### Tạo server trên Hetzner

1. Đăng ký tại [hetzner.com/cloud](https://hetzner.com/cloud)
2. New Server → Location: Nuremberg hoặc Singapore → Image: **Ubuntu 22.04** → Type: **CX11 (2 vCPU, 512MB, 20GB SSD)**
3. SSH Key: thêm public key của bạn
4. Create

### SSH vào server

```bash
ssh root@<your-vps-ip>
```

### Cài đặt Docker

```bash
curl -fsSL https://get.docker.com | sh
systemctl enable docker
systemctl start docker

# Test
docker --version
```

### Tạo user không phải root (khuyến nghị)

```bash
useradd -m -s /bin/bash deploy
usermod -aG docker deploy
# Copy SSH key cho user deploy
mkdir -p /home/deploy/.ssh
cp ~/.ssh/authorized_keys /home/deploy/.ssh/
chown -R deploy:deploy /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
chmod 600 /home/deploy/.ssh/authorized_keys
```

---

## 2. Thêm swap (bắt buộc cho 512MB VPS)

MariaDB spike RAM khi khởi động. Swap ngăn OOM kill:

```bash
fallocate -l 1G /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile

# Persist sau reboot
echo '/swapfile none swap sw 0 0' >> /etc/fstab

# Giảm swap aggressiveness (chỉ dùng khi thực sự cần)
echo 'vm.swappiness=10' >> /etc/sysctl.conf
sysctl -p
```

Kiểm tra:
```bash
free -h
# Swap: 1.0Gi total
```

---

## 3. Cài iz-wp-lite

SSH với user `deploy`:
```bash
ssh deploy@<your-vps-ip>
```

Dùng one-liner installer (Tier 1 mặc định, phù hợp CX11):
```bash
curl -fsSL https://raw.githubusercontent.com/izhubs/iz-wp-lite/main/install.sh | bash -s my-site
cd my-site
```

Hoặc manual:
```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site
cd my-site
cp .env.example .env
```

---

## 4. Cấu hình .env

```bash
nano .env
```

Thay đổi các giá trị sau:

```ini
WP_ENV=production
WP_HOME=https://yourdomain.com
WP_SITEURL=https://yourdomain.com/wp

DB_ENGINE=mysql
DB_NAME=wp_lite
DB_USER=wp_user
DB_PASSWORD=<strong-password-here>    # Thay bằng password mạnh
DB_HOST=mariadb:3306

# Salts — generate tại: https://roots.io/salts.html
AUTH_KEY='<random-64-char-string>'
SECURE_AUTH_KEY='<random-64-char-string>'
LOGGED_IN_KEY='<random-64-char-string>'
NONCE_KEY='<random-64-char-string>'
AUTH_SALT='<random-64-char-string>'
SECURE_AUTH_SALT='<random-64-char-string>'
LOGGED_IN_SALT='<random-64-char-string>'
NONCE_SALT='<random-64-char-string>'
```

---

## 5. Cấu hình Caddyfile cho production (HTTPS thực)

`docker/Caddyfile` hiện tại dùng `auto_https off` và port 8080 (dev mode). Cho production với domain thật:

```bash
nano docker/Caddyfile
```

Thay toàn bộ nội dung:

```caddyfile
{
    admin off
    email admin@yourdomain.com    # Email nhận thông báo SSL từ Let's Encrypt
}

yourdomain.com {
    root * /var/www/html/web
    encode gzip zstd

    # Security: block dotfiles, config, database
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

    # Block XML-RPC
    @xmlrpc { path /xmlrpc.php }
    respond @xmlrpc 403

    # Rewrite /wp-content/* → /app/*
    @wp_content { path_regexp wpcontent ^/wp-content/(.*)$ }
    rewrite @wp_content /app/{re.wpcontent.1}

    file_server

    # Tier 2: Static page cache
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

Update `docker/docker-compose.yml` để expose port 80 và 443:
```bash
nano docker/docker-compose.yml
```

Thay phần `ports` của service `app`:
```yaml
ports:
  - "80:80"
  - "443:443"
  - "443:443/udp"   # HTTP/3 QUIC
```

---

## 6. DNS

Trỏ domain về VPS IP trước khi start container (Caddy cần verify domain để cấp SSL):

```
A     yourdomain.com        <your-vps-ip>
A     www.yourdomain.com    <your-vps-ip>   # Optional
```

TTL 300 (5 phút) để propagate nhanh. Kiểm tra:
```bash
dig yourdomain.com +short
# Phải trả về <your-vps-ip>
```

---

## 7. Start production

```bash
docker compose -f docker/docker-compose.yml up -d --build
```

Caddy tự động request SSL certificate từ Let's Encrypt. Lần đầu mất ~30 giây.

Kiểm tra:
```bash
# SSL certificate
curl -I https://yourdomain.com

# Container status
docker compose -f docker/docker-compose.yml ps

# Logs
docker compose -f docker/docker-compose.yml logs app --follow
```

---

## 8. Systemd service (auto-start sau reboot)

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

Test:
```bash
sudo reboot
# Sau khi reboot SSH lại
docker ps  # Containers phải tự chạy lại
```

---

## 9. Backup

### Database backup

```bash
# Tạo script backup
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

# Giữ 7 ngày gần nhất
find "$BACKUP_DIR" -name "db_*.sql.gz" -mtime +7 -delete
echo "Backup: $BACKUP_DIR/db_$DATE.sql.gz"
EOF

chmod +x /home/deploy/backup-db.sh
```

Thêm vào crontab (backup mỗi ngày 03:00):
```bash
crontab -e
# Thêm dòng:
0 3 * * * /home/deploy/backup-db.sh >> /home/deploy/backup.log 2>&1
```

### Uploads backup

```bash
# Rsync uploads về máy local
rsync -avz deploy@<your-vps-ip>:/home/deploy/my-site/web/app/uploads/ ./backup-uploads/

# Hoặc dùng rclone sync lên Cloudflare R2 (xem thêm: docs/DEPLOY_R2.md)
```

---

## 10. Update WordPress / plugins

```bash
cd /home/deploy/my-site

# Pull latest code
git pull

# Update dependencies
docker compose -f docker/docker-compose.yml exec app composer update --no-interaction

# Rebuild nếu Dockerfile thay đổi
docker compose -f docker/docker-compose.yml up -d --build

# Flush cache
docker compose -f docker/docker-compose.yml exec app wp --allow-root cache flush
```

---

## 11. Monitor RAM thực tế

```bash
# RAM usage realtime
docker stats --no-stream

# Xem memory limit có hiệu lực không
docker inspect iz-wp-lite-app | grep -i memory

# MariaDB buffer pool status
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

## Troubleshooting VPS

| Triệu chứng | Lệnh kiểm tra | Cách fix |
|---|---|---|
| Site không load | `docker compose logs app` | Xem lỗi PHP hoặc Caddy |
| SSL không cấp | `docker compose logs app \| grep acme` | DNS chưa propagate, chờ thêm |
| MariaDB crash | `docker compose logs mariadb` | OOM — kiểm tra `free -h`, tăng swap |
| Site chậm | `docker stats` | RAM đầy, tăng swap hoặc nâng VPS tier |
| "Error establishing DB connection" | `docker compose ps mariadb` | MariaDB chưa ready, chờ 15–30s |
| Uploads không lưu | `docker compose exec app ls -la web/app/uploads` | Permission issue, chạy `chown -R www-data` |
