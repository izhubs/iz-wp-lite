# Local Development Setup

**Prerequisites:** Git, Docker Desktop (Mac/Windows) hoặc Docker Engine (Linux)

---

## Quick start (SQLite mode — fastest, zero config)

```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site
cd my-site
cp .env.example .env
docker compose up -d --build
```

Open: http://localhost:8080

SQLite mode không cần database configuration. Phù hợp để test nhanh, xem giao diện, dev theme.

---

## Full stack local (MariaDB — giống production)

```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site
cd my-site
cp .env.example .env
```

Mở `.env`, đảm bảo:
```ini
DB_ENGINE=mysql
WP_ENV=development
WP_HOME=http://localhost:8080
WP_SITEURL=http://localhost:8080/wp
```

```bash
docker compose up -d --build
```

MariaDB khởi động mất ~10–15 giây lần đầu. Kiểm tra:
```bash
docker compose logs mariadb --follow
# Chờ dòng: "ready for connections"
```

Open: http://localhost:8080

---

## Chuyển đổi giữa SQLite và MariaDB

Sửa `.env`:
```ini
# MariaDB (default, giống production):
DB_ENGINE=mysql

# SQLite (instant, không cần DB service):
DB_ENGINE=sqlite
```

Restart app container (không cần rebuild):
```bash
docker compose restart app
```

**Lưu ý:** Chuyển engine sẽ mất data nếu đã cài WordPress. Mỗi engine có database riêng biệt — không convert tự động.

---

## WP-CLI

Chạy WP-CLI bên trong container:

```bash
# Alias tiện dụng (thêm vào ~/.bashrc hoặc ~/.zshrc)
alias wp='docker compose exec app wp --allow-root'

# Sử dụng
wp core version
wp plugin list
wp user list
wp cache flush
wp search-replace 'http://old.domain' 'http://new.domain' --all-tables
```

---

## Cài plugin mới

```bash
# Tìm plugin trên https://wpackagist.org
composer require wpackagist-plugin/contact-form-7

# Plugin được auto-activated qua 01-default-plugins-activator.php mu-plugin
# Hoặc activate thủ công:
wp plugin activate contact-form-7
```

---

## Xem logs

```bash
# Tất cả containers
docker compose logs --follow

# Riêng app (Caddy + PHP-FPM)
docker compose logs app --follow

# Riêng MariaDB
docker compose logs mariadb --follow

# WordPress debug log (bật WP_DEBUG=true trong .env trước)
docker compose exec app tail -f /var/www/html/web/app/debug.log
```

---

## Debug PHP

Bật debug mode trong `.env`:
```ini
WP_ENV=development
WP_DEBUG=true
WP_DEBUG_LOG=true
WP_DEBUG_DISPLAY=false
```

Restart:
```bash
docker compose restart app
```

Log lỗi PHP:
```bash
docker compose exec app tail -f /var/www/html/web/app/debug.log
```

---

## Bind-mount code để live edit (không cần rebuild)

Bỏ comment trong `docker/docker-compose.yml`:
```yaml
volumes:
  - wp_database:/var/www/html/web/app/database
  - wp_uploads:/var/www/html/web/app/uploads
  - wp_sessions:/var/lib/php/sessions
  # Uncomment lines below for live editing:
  - ../web/app:/var/www/html/web/app   # ← bỏ dấu #
  - ../config:/var/www/html/config     # ← bỏ dấu #
```

```bash
docker compose up -d  # không cần --build
```

Thay đổi file local → phản ánh ngay trong container.

---

## HTTPS local (tuỳ chọn)

Caddy hỗ trợ `localhost` HTTPS qua mkcert. Sửa `docker/Caddyfile`:

```caddyfile
{
  admin off
  # Xóa dòng auto_https off để bật local HTTPS
}

localhost:8443 {
  # ... phần còn lại giữ nguyên
}
```

Và update `docker/docker-compose.yml` để expose port 8443:
```yaml
ports:
  - "8080:8080"
  - "8443:8443"
```

---

## Các lệnh thường dùng

```bash
# Start
docker compose up -d

# Stop (giữ data)
docker compose down

# Xóa tất cả kể cả data (cẩn thận)
docker compose down -v

# Rebuild image sau khi sửa Dockerfile
docker compose up -d --build

# Shell vào container
docker compose exec app sh

# MariaDB shell
docker compose exec mariadb mariadb -u"${DB_USER:-wp_user}" -p"${DB_PASSWORD:-wp_secure_password}" "${DB_NAME:-wp_lite}"

# Xem RAM usage thực tế
docker stats --no-stream

# Composer install (trong container, không cần PHP local)
docker compose exec app composer install
```

---

## Reset về trạng thái ban đầu

```bash
docker compose down -v          # Xóa containers + volumes (mất data WP)
docker volume prune             # Dọn volumes không dùng
docker image rm iz-wp-lite-app  # Xóa image để rebuild từ đầu
docker compose up -d --build    # Build lại từ đầu
```
