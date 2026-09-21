# Local Development

**Prerequisites:** Git, Docker Desktop (macOS/Windows) or Docker Engine (Linux)

---

## Quick start — SQLite mode (fastest, zero config)

```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site
cd my-site
cp .env.example .env
docker compose up -d --build
```

Open: http://localhost:8080

SQLite requires no database configuration. Use for rapid UI testing, theme development, and plugin evaluation.

---

## Full stack — MariaDB mode (matches production)

```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site
cd my-site
cp .env.example .env
```

Edit `.env` and confirm:
```ini
DB_ENGINE=mysql
WP_ENV=development
WP_HOME=http://localhost:8080
WP_SITEURL=http://localhost:8080/wp
```

```bash
docker compose up -d --build
```

MariaDB takes ~10–15 seconds to initialize on first run:
```bash
docker compose logs mariadb --follow
# Wait for: "ready for connections"
```

Open: http://localhost:8080

---

## Switching between SQLite and MariaDB

Edit `.env`:
```ini
# MariaDB (default, 100% plugin compatible):
DB_ENGINE=mysql

# SQLite (instant boot, no database service):
DB_ENGINE=sqlite
```

Restart the app container without rebuilding:
```bash
docker compose restart app
```

**Note:** Switching engines does not migrate existing data. Each engine maintains a separate database. WordPress setup must be re-completed after switching.

---

## WP-CLI

Run WP-CLI inside the container:

```bash
# Convenient alias — add to ~/.bashrc or ~/.zshrc
alias wp='docker compose exec app wp --allow-root'

# Usage
wp core version
wp plugin list
wp user list
wp cache flush
wp search-replace 'http://old.domain' 'http://new.domain' --all-tables
```

---

## Installing plugins

```bash
# Search for any plugin at https://wpackagist.org
composer require wpackagist-plugin/contact-form-7

# Plugins are auto-activated via 01-default-plugins-activator.php mu-plugin
# Or activate manually:
wp plugin activate contact-form-7
```

---

## Viewing logs

```bash
# All containers
docker compose logs --follow

# App only (Caddy + PHP-FPM)
docker compose logs app --follow

# MariaDB only
docker compose logs mariadb --follow

# WordPress debug log (requires WP_DEBUG=true in .env)
docker compose exec app tail -f /var/www/html/web/app/debug.log
```

---

## PHP debug mode

Enable in `.env`:
```ini
WP_ENV=development
WP_DEBUG=true
WP_DEBUG_LOG=true
WP_DEBUG_DISPLAY=false
```

Apply:
```bash
docker compose restart app
docker compose exec app tail -f /var/www/html/web/app/debug.log
```

---

## Live editing — bind-mount source (no rebuild needed)

Uncomment in `docker/docker-compose.yml`:
```yaml
volumes:
  - wp_database:/var/www/html/web/app/database
  - wp_uploads:/var/www/html/web/app/uploads
  - wp_sessions:/var/lib/php/sessions
  - ../web/app:/var/www/html/web/app   # ← uncomment
  - ../config:/var/www/html/config     # ← uncomment
```

```bash
docker compose up -d   # no --build required
```

Local file changes reflect immediately inside the container.

---

## Local HTTPS (optional)

Caddy supports `localhost` HTTPS via mkcert. Edit `docker/Caddyfile`:

```caddyfile
{
  admin off
  # Remove auto_https off to enable local HTTPS
}

localhost:8443 {
  # ... rest of config unchanged
}
```

Update port mapping in `docker/docker-compose.yml`:
```yaml
ports:
  - "8080:8080"
  - "8443:8443"
```

---

## Common commands

```bash
# Start
docker compose up -d

# Stop (preserves data)
docker compose down

# Wipe everything including volumes (destructive)
docker compose down -v

# Rebuild image after Dockerfile changes
docker compose up -d --build

# Shell into app container
docker compose exec app sh

# MariaDB shell
docker compose exec mariadb mariadb \
  -u"${DB_USER:-wp_user}" \
  -p"${DB_PASSWORD:-wp_secure_password}" \
  "${DB_NAME:-wp_lite}"

# Check actual RAM usage
docker stats --no-stream

# Composer install inside container (no local PHP required)
docker compose exec app composer install
```

---

## Full reset

```bash
docker compose down -v          # Remove containers + volumes (data lost)
docker volume prune             # Clean orphaned volumes
docker image rm iz-wp-lite-app  # Remove image to force full rebuild
docker compose up -d --build
```
