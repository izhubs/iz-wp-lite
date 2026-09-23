# iz-wp-lite — Deploy Guide

## Yêu cầu

- VPS đã cài Docker Engine + iz-deploy agent (`izdeploy-agent`)
- Domain trỏ về IP VPS
- MariaDB đã tạo trước (1 lần duy nhất)

---

## Bước 0: Cài iz-deploy trên VPS (1 lần)

```bash
curl -sSL https://raw.githubusercontent.com/izhubs/iz-deploy/main/scripts/install-node.sh | bash
```

---

## Bước 1: Tạo database MariaDB (1 lần duy nhất)

```bash
izdeploy db create mariadb \
  --name wp-db \
  --database YOUR_DB_NAME \
  --user YOUR_DB_USER \
  --password YOUR_DB_PASSWORD
```

MariaDB sẽ chạy trên `127.0.0.1:3306`, container WordPress kết nối qua host network.

---

## Bước 2: Cấu hình manifest

Sửa 4 giá trị trong `.agent/izdeploy.json`:

```json
"WP_HOME":    "https://YOUR_DOMAIN",
"WP_SITEURL": "https://YOUR_DOMAIN/wp",
"DB_NAME":    "YOUR_DB_NAME",
"DB_USER":    "YOUR_DB_USER",
"DB_PASSWORD":"YOUR_DB_PASSWORD",
"routes": ["YOUR_DOMAIN"]
```

Sau đó set secrets (không commit lên Git):

```bash
izdeploy secret set DB_PASSWORD=xxx AUTH_KEY=xxx SECURE_AUTH_KEY=xxx \
  LOGGED_IN_KEY=xxx NONCE_KEY=xxx AUTH_SALT=xxx SECURE_AUTH_SALT=xxx \
  LOGGED_IN_SALT=xxx NONCE_SALT=xxx
```

Sinh WordPress salts nhanh:

```bash
curl -s https://api.wordpress.org/secret-key/1.1/salt/ | grep define | \
  awk -F"'" '{print $2"="$4}' | xargs izdeploy secret set
```

---

## Bước 3: Build image (local hoặc CI)

```bash
# Local build + push lên registry
docker build -f docker/Dockerfile -t ghcr.io/YOUR_ORG/iz-wp-lite:latest .
docker push ghcr.io/YOUR_ORG/iz-wp-lite:latest
```

Cập nhật `"image"` trong `.agent/izdeploy.json` theo tag vừa push.

---

## Bước 4: Deploy (1 lệnh, mãi mãi)

```bash
izdeploy deploy
```

Lần đầu: init lockfile tự động.  
Các lần sau: chạy 1 lệnh trên là xong.

---

## Bước 5: Sau deploy — setup WordPress

```bash
# Chạy WP-CLI trong container
izdeploy exec wp --allow-root core install \
  --url=https://YOUR_DOMAIN \
  --title="Site Title" \
  --admin_user=admin \
  --admin_email=admin@example.com \
  --admin_password=STRONG_PASSWORD
```

---

## Lệnh vận hành thường dùng

```bash
izdeploy logs           # Xem log realtime
izdeploy logs -f        # Follow log
izdeploy exec wp --allow-root cache flush     # Flush cache
izdeploy exec wp --allow-root plugin list     # Liệt kê plugins
izdeploy rollback       # Rollback version trước
izdeploy status         # Xem trạng thái container
```

---

## Lưu ý kiến trúc

- **MariaDB** chạy riêng (không nằm trong manifest), kết nối qua `127.0.0.1:3306`
- **Volumes** lưu tại `/var/lib/izdeploy/iz-wp-lite/` trên VPS host
- **Healthcheck** gọi `/wp/wp-login.php` — WordPress phải load được trước khi traffic chuyển sang
- **Zero-downtime**: container cũ chỉ bị tắt sau khi container mới pass healthcheck
