# Deploy to Coolify

[Coolify](https://coolify.io) là self-hosted PaaS chạy trên VPS của bạn, tương tự Heroku/Railway nhưng không tốn phí platform. Coolify handle SSL (Traefik), Git auto-deploy, và environment variables qua UI.

**Yêu cầu:** VPS 512MB+ với Coolify đã cài sẵn. Xem [coolify.io/docs](https://coolify.io/docs/installation) để cài Coolify.

---

## Kiến trúc khi deploy lên Coolify

```
Internet
  → Traefik (Coolify, port 80/443, handle SSL)
    → iz-wp-lite app container (port 8080, Caddy làm router nội bộ)
      → PHP-FPM (port 9000 nội bộ)
    → iz-wp-lite mariadb container (port 3306 nội bộ)
```

Caddy trong container đã set `auto_https off` — không xung đột với Traefik.
`X-Forwarded-Proto` từ Traefik được `config/environments/production.php` xử lý đúng.

---

## Bước 1: Tạo project trong Coolify

1. Vào Coolify dashboard → **Projects** → **New Project**
2. Đặt tên: `iz-wp-lite` (hoặc tên site cụ thể)
3. **Add New Resource** → **Docker Compose**

---

## Bước 2: Kết nối Git repository

- **Source:** GitHub / GitLab / Gitea / Public URL
- **Repository:** `https://github.com/izhubs/iz-wp-lite` (hoặc fork của bạn)
- **Branch:** `main`
- **Docker Compose Location:** `docker-compose.yml` (file ở root repo)

Nếu Coolify không tìm thấy compose file:
- Thử đường dẫn: `docker/docker-compose.yml`
- Hoặc custom compose path trong Coolify settings

---

## Bước 3: Environment Variables

Vào **Environment Variables** trong Coolify project, thêm từng biến:

```
WP_ENV                production
WP_HOME               https://yourdomain.com
WP_SITEURL            https://yourdomain.com/wp
DB_ENGINE             mysql
DB_NAME               wp_lite
DB_USER               wp_user
DB_PASSWORD           <strong-password>
DB_HOST               mariadb:3306
AUTH_KEY              <generate at roots.io/salts.html>
SECURE_AUTH_KEY       <generate>
LOGGED_IN_KEY         <generate>
NONCE_KEY             <generate>
AUTH_SALT             <generate>
SECURE_AUTH_SALT      <generate>
LOGGED_IN_SALT        <generate>
NONCE_SALT            <generate>
```

**Quan trọng:** Đánh dấu `DB_PASSWORD` và các salts là **Secret** trong Coolify UI để không bị log.

Generate salts tại: https://roots.io/salts.html

---

## Bước 4: Domain & SSL

1. Coolify → **Domains** → **Add Domain**: `yourdomain.com`
2. Trỏ DNS về IP VPS đang chạy Coolify:
   ```
   A    yourdomain.com    <coolify-vps-ip>
   ```
3. Coolify + Traefik tự động cấp Let's Encrypt SSL — không cần cấu hình thêm

---

## Bước 5: Deploy

Coolify → **Deploy** → **Deploy Now**

Lần đầu build mất 2–4 phút (Docker image chưa cache).

Kiểm tra logs trong Coolify UI: **Deployments** → chọn deployment mới nhất → xem output.

---

## Bước 6: Xác nhận

Truy cập `https://yourdomain.com` → WordPress installation wizard.

Nếu thấy trang Caddy default thay vì WordPress:
- Kiểm tra `WP_HOME` và `WP_SITEURL` trong env vars
- Restart containers trong Coolify

---

## Auto-deploy khi push code

Trong Coolify → **Source** → bật **Auto Deploy on Push**.

Mọi `git push` vào branch `main` sẽ trigger rebuild và redeploy tự động.

---

## Persistent Volumes

Coolify tự động giữ Docker named volumes giữa các lần deploy:

| Volume | Mount path | Nội dung |
|---|---|---|
| `iz_wp_mariadb_data` | `/var/lib/mysql` | Database MariaDB |
| `iz_wp_uploads` | `/var/www/html/web/app/uploads` | Media files |
| `iz_wp_sessions` | `/var/lib/php/sessions` | PHP sessions |
| `iz_wp_database` | `/var/www/html/web/app/database` | SQLite file (nếu dùng) |

`docker compose down` trong Coolify **không xóa volumes**. Chỉ "Delete Project" mới xóa.

---

## Scaling lên Tier 2 trên Coolify

Khi traffic tăng, nâng memory limit trong Coolify mà không cần sửa file:

1. Coolify → **Resources** → chỉnh memory limit cho service `app` và `mariadb`
2. Hoặc sửa `docker-compose.yml` trong repo, `mem_limit: 384M` → commit → Coolify auto-redeploy

---

## WooCommerce trên Coolify

Dùng override profile:

```bash
# Trong Coolify, đổi Docker Compose Location thành:
docker/docker-compose.yml

# Thêm Compose override trong Coolify settings:
docker/profiles/woocommerce.yml
```

Hoặc copy nội dung `docker/profiles/woocommerce.yml` merge vào `docker-compose.yml` rồi commit.

---

## WP-CLI qua Coolify Terminal

Coolify cung cấp terminal access vào container. Trong Coolify UI → **Containers** → `iz-wp-lite-app` → **Terminal**:

```bash
wp --allow-root core version
wp --allow-root plugin list
wp --allow-root cache flush
```

Hoặc SSH vào VPS:
```bash
ssh user@coolify-vps-ip
docker exec -it iz-wp-lite-app wp --allow-root core version
```

---

## Troubleshooting Coolify

| Triệu chứng | Nguyên nhân thường gặp | Fix |
|---|---|---|
| Deploy fail "compose file not found" | Coolify tìm sai path | Đổi compose path thành `docker-compose.yml` |
| `ERR_TOO_MANY_REDIRECTS` | `WP_HOME` chưa đặt hoặc `X-Forwarded-Proto` lỗi | Kiểm tra env `WP_HOME=https://...` (phải có https) |
| SSL certificate không cấp | DNS chưa propagate | Chờ DNS propagate, check `dig yourdomain.com +short` |
| MariaDB "connection refused" | Container MariaDB khởi động chậm hơn app | Coolify retry healthcheck — chờ 30–60s, refresh |
| Memory OOM | `mem_limit` trong compose quá thấp cho VPS | Tăng `mem_limit` trong compose file hoặc Coolify Resources |
| "Error establishing a database connection" | `DB_HOST` sai | Phải là `mariadb:3306` (tên service trong compose) |
| Uploads không hiển thị sau redeploy | Volume mount bị thay đổi | Kiểm tra volumes được Coolify preserve, không phải bind mount |

---

## So sánh Coolify vs izDeploy

| | Coolify | izDeploy |
|---|---|---|
| **Cài đặt** | Cài Coolify trên VPS của bạn | Cloud PaaS, không cần quản lý server |
| **SSL** | Traefik + Let's Encrypt | Caddy auto-cert |
| **Deploy source** | Git (GitHub/GitLab/Gitea) | `.agent/izdeploy.json` contract |
| **Memory limit** | UI hoặc `mem_limit` trong compose | `deploy.resources.limits` |
| **Compose file path** | `docker-compose.yml` (root) | `docker/docker-compose.yml` |
| **Cost** | Free (bạn tự trả VPS) | Tùy pricing izDeploy |
| **Multi-site** | Coolify quản lý nhiều projects | Mỗi contract độc lập |
