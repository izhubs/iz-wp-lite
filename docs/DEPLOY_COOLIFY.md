# Deploying to Coolify

[Coolify](https://coolify.io) is a self-hosted PaaS that runs on your own VPS. It handles SSL (via Traefik), Git-based auto-deploy, and environment variable management through a web UI — similar to Heroku or Railway but without platform fees.

**Requirement:** A VPS with Coolify installed. See [coolify.io/docs](https://coolify.io/docs/installation) for Coolify setup.

---

## How it works

```
Internet
  → Traefik (Coolify-managed, ports 80/443, SSL termination)
    → iz-wp-lite app container (port 8080, Caddy as internal router)
      → PHP-FPM (port 9000, internal)
    → iz-wp-lite mariadb container (port 3306, internal)
```

Caddy in the container has `auto_https off` — no conflict with Traefik.
`X-Forwarded-Proto` headers from Traefik are handled by `config/environments/production.php`.

---

## Step 1: Create a project in Coolify

1. Open your Coolify dashboard → **Projects** → **New Project**
2. Name it (e.g., `iz-wp-lite` or the client site name)
3. **Add New Resource** → **Docker Compose**

---

## Step 2: Connect the Git repository

- **Source:** GitHub / GitLab / Gitea / Public URL
- **Repository:** `https://github.com/izhubs/iz-wp-lite` (or your fork)
- **Branch:** `main`
- **Docker Compose Location:** `docker-compose.yml` (root-level file)

If Coolify cannot find the compose file, try the alternative path:
- `docker/docker-compose.yml`

---

## Step 3: Set environment variables

In Coolify → **Environment Variables**, add each variable:

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

Mark `DB_PASSWORD` and all salts as **Secret** in the Coolify UI to prevent them from appearing in logs.

Generate salts: https://roots.io/salts.html

---

## Step 4: Domain and SSL

1. Coolify → **Domains** → **Add Domain**: `yourdomain.com`
2. Point DNS to the Coolify VPS IP:
   ```
   A    yourdomain.com    <coolify-vps-ip>
   ```
3. Traefik automatically provisions Let's Encrypt SSL — no further configuration required.

---

## Step 5: Deploy

Coolify → **Deploy** → **Deploy Now**

First build takes 2–4 minutes (uncached). Monitor output under **Deployments** → latest deployment → build log.

---

## Step 6: Verify

Visit `https://yourdomain.com` — you should see the WordPress installation wizard.

If you see a default Caddy page instead of WordPress:
- Check that `WP_HOME` is set to `https://yourdomain.com` (not `http://`)
- Restart containers in Coolify UI

---

## Auto-deploy on push

Coolify → **Source** → enable **Auto Deploy on Push**.

Every `git push` to `main` triggers a rebuild and redeploy automatically.

---

## Persistent volumes

Coolify preserves Docker named volumes across deployments:

| Volume | Mount path | Contents |
|---|---|---|
| `iz_wp_mariadb_data` | `/var/lib/mysql` | MariaDB database files |
| `iz_wp_uploads` | `/var/www/html/web/app/uploads` | WordPress media files |
| `iz_wp_sessions` | `/var/lib/php/sessions` | PHP session files |
| `iz_wp_database` | `/var/www/html/web/app/database` | SQLite file (if DB_ENGINE=sqlite) |

**Note:** `docker compose down` in Coolify does **not** delete volumes. Only deleting the project removes them.

---

## Scaling to a larger VPS

When traffic increases, raise memory limits without changing the codebase:

1. Edit `mem_limit` values in `docker-compose.yml` → commit → Coolify auto-redeploys
2. Or override resources directly in Coolify → **Resources** for each service

Tier reference:

| Tier | `mem_limit` app | `mem_limit` mariadb | VPS RAM |
|---|---|---|---|
| 1 | `192M` | `96M` | 512MB |
| 2 | `384M` | `384M` | 1GB |
| 3 | `768M` | `768M` | 2GB |

---

## WooCommerce profile on Coolify

Use the WooCommerce Docker Compose override:

In Coolify → **Docker Compose** settings → add a second compose file:
```
docker/profiles/woocommerce.yml
```

Or merge the contents of `docker/profiles/woocommerce.yml` into `docker-compose.yml`, commit, and let Coolify redeploy.

Minimum VPS for WooCommerce: **1GB RAM** (Hetzner CX21, ~\$8/month).

---

## WP-CLI via Coolify terminal

Coolify provides terminal access to running containers.

In Coolify UI → **Containers** → `iz-wp-lite-app` → **Terminal**:
```bash
wp --allow-root core version
wp --allow-root plugin list
wp --allow-root cache flush
```

Or SSH into the Coolify VPS directly:
```bash
ssh user@<coolify-vps-ip>
docker exec -it iz-wp-lite-app wp --allow-root core version
```

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| Deploy fails "compose file not found" | Wrong compose file path | Set path to `docker-compose.yml` (root) |
| `ERR_TOO_MANY_REDIRECTS` | `WP_HOME` is `http://` instead of `https://` | Set `WP_HOME=https://yourdomain.com` |
| SSL certificate not issued | DNS not propagated | Run `dig yourdomain.com +short`, wait and retry |
| MariaDB "connection refused" on first deploy | MariaDB container slower than app container | Coolify retries healthcheck automatically — wait 30–60s |
| OOM kill / container restart loop | `mem_limit` too low | Raise `mem_limit` in compose or Coolify Resources |
| "Error establishing a database connection" | Wrong `DB_HOST` | Must be `mariadb:3306` (service name in compose) |
| Uploads missing after redeploy | Volume not mounted correctly | Confirm Coolify preserves named volumes (not bind mounts) |

---

## Coolify vs bare VPS vs izDeploy

| | Bare VPS | Coolify | izDeploy |
|---|---|---|---|
| **SSL** | Caddy auto-cert (direct domain) | Traefik (Coolify-managed) | Caddy contract |
| **Compose file** | `docker/docker-compose.yml` | `docker-compose.yml` (root) | `.agent/izdeploy.json` |
| **Env vars** | `.env` file on server | Coolify UI | `izdeploy secrets set` |
| **Updates** | `git pull` + `docker compose up` | `git push` auto-triggers | `git push` auto-triggers |
| **Multi-site** | Manual per site | Coolify projects | Separate contracts |
| **Best for** | Full control, DevOps teams | Agencies managing multiple clients | AI-native, izDeploy users |
