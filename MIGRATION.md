# Migrating from Standard WordPress to iz-wp-lite

This guide covers moving an existing WordPress site (shared hosting / cPanel / vanilla LAMP) to iz-wp-lite.

**Time estimate:** 1–3 hours depending on site complexity and number of plugins.

---

## Before you start

### Compatibility check

Run this checklist before migrating:

- [ ] All active plugins available on [wpackagist.org](https://wpackagist.org) (search by slug)
- [ ] No plugins purchased from CodeCanyon / Envato that lack Composer endpoints
- [ ] PHP version on existing site is 8.1+ (`php -v` or check phpinfo())
- [ ] Site does not use WordPress Multisite
- [ ] Client understands they will no longer install plugins via Dashboard

Plugins not on wpackagist require a private Satis repository. This is outside iz-wp-lite scope.

---

## Step 1: Export current site data

On your existing server:

```bash
# Export database
wp db export backup-$(date +%Y%m%d).sql

# Or via WP-CLI docker exec if already containerized
docker exec -it <container> wp db export /var/www/html/backup.sql --allow-root
```

If WP-CLI is not available, use phpMyAdmin or your hosting control panel's database export.

**Do NOT export `wp-content/plugins/` or `wp-content/themes/` — these will be reinstalled via Composer.**

Export only:
- Database dump (`.sql`)
- `wp-content/uploads/` (media files)
- Custom theme files (if not on wpackagist)

---

## Step 2: Install iz-wp-lite

```bash
curl -fsSL https://raw.githubusercontent.com/izhubs/iz-wp-lite/main/install.sh | bash -s my-migrated-site
cd my-migrated-site
```

---

## Step 3: Configure .env

Edit `.env` to match your existing database credentials (or create a new DB):

```ini
WP_ENV=production
WP_HOME=https://yourdomain.com
WP_SITEURL=https://yourdomain.com/wp

DB_ENGINE=mysql
DB_NAME=your_existing_db_name
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_HOST=mariadb:3306
DB_PREFIX=wp_
```

Copy your WP salts from the old `wp-config.php` into `.env` under the AUTH_KEY / NONCE_KEY blocks.

> [!IMPORTANT]
> **Check Table Prefix:** If your existing site uses a custom prefix (e.g. `$table_prefix = 'wp_custom_';` in your old `wp-config.php`), ensure you set `DB_PREFIX=wp_custom_` in `.env`. If this does not match, WordPress will display the fresh installation screen instead of loading your migrated data.

---

## Step 4: Reinstall plugins (Composer or Direct Upload)

### Method A: Public plugins via Composer (Recommended)
Map each old plugin slug to its wpackagist package:

```bash
# Example: old site had these plugins active
composer require wpackagist-plugin/contact-form-7
composer require wpackagist-plugin/seo-by-rank-math
composer require wpackagist-plugin/akismet
composer require wpackagist-plugin/wordfence
```

Search for any plugin at: `https://wpackagist.org/?s=<plugin-slug>`

### Method B: Premium or private plugins (Direct Upload)
For paid plugins (ACF Pro, Elementor Pro, CodeCanyon ZIPs) not available on wpackagist, simply copy or upload them directly into `web/app/plugins/` (before or after migration):

```bash
# Copy unpacked plugin into the container
docker cp /path/to/my-premium-plugin iz-wp-lite-app:/var/www/html/web/app/plugins/

# Fix permissions
docker exec iz-wp-lite-app chown -R www-data:www-data /var/www/html/web/app/plugins/
```

*(Note: `web/app/plugins/*` is already ignored by Git in `.gitignore`, so uploading plugins directly will not dirty your repository).*

---

## Step 5: Migrate custom theme

If your theme is not on wpackagist, copy it manually:

```bash
cp -r /old-site/wp-content/themes/my-custom-theme web/app/themes/
```

Add it to `.gitignore` exclusion or track it in your own private Composer package.

---

## Step 6: Import database

```bash
# Start containers first
docker compose -f docker/docker-compose.yml up -d

# Import dump into MariaDB container
docker exec -i iz-wp-lite-db mariadb \
  -u"${DB_USER:-wp_user}" \
  -p"${DB_PASSWORD:-wp_secure_password}" \
  "${DB_NAME:-wp_lite}" < backup-20240101.sql
```

---

## Step 7: Search-replace URLs

WordPress stores the full domain URL in the database (serialized PHP). After import, update all references:

```bash
# Run WP-CLI inside the app container
docker exec -it iz-wp-lite-app wp --allow-root search-replace \
  'https://old-domain.com' \
  'https://new-domain.com' \
  --all-tables
```

This handles serialized strings correctly. Do NOT run manual SQL `REPLACE()` — it breaks serialized data.

---

## Step 8: Migrate uploads

```bash
# Copy media files into Docker volume
docker cp /old-site/wp-content/uploads/. iz-wp-lite-app:/var/www/html/web/app/uploads/

# Fix permissions
docker exec iz-wp-lite-app chown -R www-data:www-data /var/www/html/web/app/uploads/
```

---

## Step 9: Verify

```bash
# Check site is reachable
curl -I http://localhost:8080

# Check WordPress status
docker exec -it iz-wp-lite-app wp --allow-root core version
docker exec -it iz-wp-lite-app wp --allow-root plugin list
docker exec -it iz-wp-lite-app wp --allow-root theme list
```

Navigate to `http://localhost:8080/wp/wp-admin` to confirm the admin panel loads.

---

## Step 10: Go live

1. Update DNS to point domain to the new VPS IP
2. Change `WP_HOME` and `WP_SITEURL` in `.env` to the production domain
3. Restart containers: `docker compose -f docker/docker-compose.yml restart`
4. Run search-replace again for the final domain

```bash
docker exec -it iz-wp-lite-app wp --allow-root search-replace \
  'http://localhost:8080' \
  'https://yourdomain.com' \
  --all-tables
```

---

## Common migration issues

| Issue | Cause | Fix |
|---|---|---|
| White screen after import | Old plugin still referenced in DB, plugin not reinstalled | `docker exec ... wp --allow-root plugin deactivate <slug>` |
| WordPress shows install screen | `DB_PREFIX` in `.env` does not match tables in database | Check table prefix in DB dump and update `DB_PREFIX` in `.env` |
| Media images 404 | Uploads not copied or wrong permissions | Re-run Step 8 |
| Admin redirect loop | `WP_HOME` / `WP_SITEURL` mismatch in DB vs `.env` | Run search-replace (Step 7) |
| "Error establishing database connection" | DB credentials wrong or MariaDB not ready | `docker compose logs mariadb` |
| Serialized data corrupted | Manual SQL search-replace was used | Restore from dump, use WP-CLI search-replace |
| Plugin not found on wpackagist | Premium / CodeCanyon plugin | Copy ZIP / folder directly to `web/app/plugins/` (Method B) |

---

## Rollback

iz-wp-lite never modifies your original server. To roll back, simply point DNS back to the old server.

Your old site remains unchanged throughout this migration.

---

## Migrating out — iz-wp-lite → standard WordPress

If you need to move back to a standard WordPress installation (shared hosting, cPanel, managed WordPress), the process is straightforward. iz-wp-lite uses standard WordPress data formats — nothing is proprietary.

### What you are moving

| Data | Location in iz-wp-lite | Destination |
|---|---|---|
| Database | MariaDB container (`iz_wp_mariadb_data` volume) | New host's MySQL/MariaDB |
| Media files | `web/app/uploads/` | `wp-content/uploads/` |
| Plugins | Listed in `composer.json` | Install via WordPress dashboard |
| Themes | `web/app/themes/` | `wp-content/themes/` |
| Configuration | `.env` | `wp-config.php` |

### Step 1: Export the database

```bash
docker compose -f docker/docker-compose.yml exec -T mariadb \
  mariadb-dump \
  -u"${DB_USER:-wp_user}" \
  -p"${DB_PASSWORD:-wp_secure_password}" \
  "${DB_NAME:-wp_lite}" \
  > export.sql
```

### Step 2: Export media uploads

```bash
# Copy uploads out of the Docker volume to your local machine
docker cp iz-wp-lite-app:/var/www/html/web/app/uploads ./uploads-export
```

Or from the VPS directly:
```bash
rsync -avz deploy@<vps-ip>:/home/deploy/my-site/web/app/uploads/ ./uploads-export/
```

### Step 3: Export themes (if custom)

```bash
docker cp iz-wp-lite-app:/var/www/html/web/app/themes ./themes-export
```

### Step 4: Install standard WordPress on destination

Use your hosting provider's WordPress installer (cPanel Softaculous, WP Engine, etc.) or download from [wordpress.org](https://wordpress.org/download/).

### Step 5: Import the database

In cPanel → phpMyAdmin, or via WP-CLI on the destination:

```bash
# If the destination has WP-CLI
wp db import export.sql
```

Or import through phpMyAdmin: drop all tables in the new database, then import `export.sql`.

### Step 6: Search-replace the domain

Bedrock stores the site URL in the database using the same format as standard WordPress. After import, update the domain:

```bash
wp search-replace 'https://old-domain.com' 'https://new-domain.com' --all-tables
```

### Step 7: Copy uploads

Copy the contents of `uploads-export/` into `wp-content/uploads/` on the destination server.

### Step 8: Reconstruct wp-config.php

The destination `wp-config.php` needs the same database credentials and salts that were in your iz-wp-lite `.env`. Copy the values from `.env`:

```php
define( 'DB_NAME',     'your_db_name' );
define( 'DB_USER',     'your_db_user' );
define( 'DB_PASSWORD', 'your_db_password' );
define( 'DB_HOST',     'localhost' );

define( 'AUTH_KEY',         'value from .env AUTH_KEY' );
define( 'SECURE_AUTH_KEY',  'value from .env SECURE_AUTH_KEY' );
// ... (all 8 salts)
```

### Step 9: Reinstall plugins

iz-wp-lite manages plugins via Composer. Check `composer.json` under `require` and `suggest` to find the plugin list. Install each plugin on the destination via the WordPress dashboard or WP-CLI:

```bash
wp plugin install contact-form-7 seo-by-rank-math akismet --activate
```

### Step 10: Re-enable dashboard plugin management

On iz-wp-lite, `DISALLOW_FILE_MODS=true` prevents dashboard installs. Standard WordPress does not set this. No action required — the destination site allows dashboard management by default.

---

### What does NOT transfer

| Feature | iz-wp-lite | Standard WordPress |
|---|---|---|
| Automatic SSL | Caddy auto-cert | Needs manual Certbot or hosting SSL |
| Git-based deploys | Native | Needs plugin or CI/CD setup |
| Security hardening mu-plugins | Auto-loaded | Must replicate manually or use Wordfence |
| `.env` secret isolation | Built-in | `wp-config.php` in web root by default |
| MariaDB micro-config | `mariadb-lowram.cnf` | Not applicable on shared hosting |

These are architectural differences, not data. Your WordPress content (posts, pages, media, users) migrates 100% intact.

