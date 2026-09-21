# Deploy to izDeploy PaaS

izDeploy đọc file `.agent/izdeploy.json` trong repo và tự động provision, build, và deploy container lên infrastructure.

---

## Yêu cầu

- Tài khoản izDeploy (liên hệ: hello@izdigi.com)
- Domain đã trỏ về izDeploy nameservers
- Repository đã push lên GitHub / GitLab

---

## Cấu trúc contract

File `.agent/izdeploy.json` (schema v2.0) trong repo đã cấu hình sẵn:

```json
{
  "$schema": "https://izdeploy.izhubs.com/schemas/v2.0/izdeploy.json",
  "version": "2.0",
  "project": {
    "name": "iz-wp-lite",
    "type": "wordpress",
    "php_version": "8.3"
  },
  "services": ["web", "mariadb"],
  "vps_profiles": {
    "tier1": { "512MB, $4.50/mo" },
    "tier2": { "1GB, $8/mo" },
    "tier3": { "2GB + Redis, $15/mo" }
  }
}
```

AI Agents (Cursor, Claude Code, Antigravity) đọc contract này và tự thực hiện deploy mà không cần human input.

---

## Deploy qua izDeploy CLI

```bash
# Cài izDeploy CLI
npm install -g @izdigi/deploy-cli

# Login
izdeploy login

# Deploy từ repo root
izdeploy deploy --contract .agent/izdeploy.json --domain yourdomain.com

# Chọn VPS tier
izdeploy deploy --contract .agent/izdeploy.json --domain yourdomain.com --tier tier2

# Xem status
izdeploy status my-site

# Xem logs
izdeploy logs my-site --follow

# Rollback về version trước
izdeploy rollback my-site
```

---

## Secrets management

Secrets (DB password, WP salts) không được lưu trong repo. Đăng ký qua izDeploy CLI:

```bash
izdeploy secrets set my-site \
  DB_PASSWORD="<strong-password>" \
  AUTH_KEY="<salt>" \
  SECURE_AUTH_KEY="<salt>" \
  LOGGED_IN_KEY="<salt>" \
  NONCE_KEY="<salt>" \
  AUTH_SALT="<salt>" \
  SECURE_AUTH_SALT="<salt>" \
  LOGGED_IN_SALT="<salt>" \
  NONCE_SALT="<salt>"

# Generate salts tự động
izdeploy secrets generate-salts my-site
```

---

## Auto-deploy qua Git webhook

```bash
# Kết nối GitHub repo
izdeploy connect github --repo izhubs/iz-wp-lite --project my-site

# Mọi git push vào main branch → trigger deploy tự động
```

---

## VPS Tier selection

| Tier | Command | RAM | Price |
|---|---|---|---|
| Tier 1 | `--tier tier1` | 512MB | ~\$4.50/mo |
| Tier 2 | `--tier tier2` | 1GB | ~\$8/mo |
| Tier 3 | `--tier tier3` | 2GB + Redis | ~\$15/mo |

---

## AI Agent deploy (Vibe Coding workflow)

Trong IDE (Cursor / Antigravity / Claude Code):

```
"Deploy this WordPress site to production on a 512MB VPS using izDeploy"
```

Agent đọc `.agent/izdeploy.json` → tự generate deploy command → execute → báo cáo URL.

Contract-driven deployment: AI không cần biết infrastructure details — tất cả được define trong `izdeploy.json`.

---

## Pre/Post deploy hooks

Trong `izdeploy.json`:

```json
"deployment": {
  "pre_deploy": [
    "docker compose exec -T mariadb mariadb-admin ping --silent"
  ],
  "post_deploy": [
    "wp --allow-root cache flush"
  ]
}
```

Hook `pre_deploy` kiểm tra MariaDB healthy trước khi swap container. `post_deploy` flush cache sau khi container mới live.

---

## Volumes & Backups

izDeploy preserve named volumes across deploys. Backup tự động:

```bash
# Manual backup
izdeploy backup create my-site

# List backups
izdeploy backup list my-site

# Restore
izdeploy backup restore my-site --backup-id <id>
```

---

## Monitoring

```bash
izdeploy stats my-site          # RAM, CPU, request count
izdeploy logs my-site           # Container logs
izdeploy alerts set my-site \   # Alert khi RAM > 80%
  --cpu-threshold 80 \
  --memory-threshold 80 \
  --email admin@yourdomain.com
```
