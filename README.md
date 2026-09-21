# iz-wp-lite

Ultra-lightweight 12-Factor WordPress starter based on Roots Bedrock, featuring dual-engine database routing (SQLite / MariaDB), Caddy reverse proxy, and multi-stage Docker builds optimized for 512MB RAM VPS instances.

---

## Technical Specifications

| Parameter | SQLite Mode | MariaDB Mode |
|---|---|---|
| **Container Count** | 1 (Caddy + PHP-FPM 8.3) | 2 (App + MariaDB 10.11) |
| **Idle Memory (Baseline)** | ~45MB RAM | ~190MB RAM |
| **PHP Process Manager** | `ondemand` (`max_children = 3`) | `ondemand` (`max_children = 3`) |
| **Opcache Buffer** | 64MB | 64MB |
| **Storage Engine** | SQLite 3 (WAL mode) | InnoDB |
| **Web Server** | Caddy 2 (HTTP/2, zstd, fastcgi) | Caddy 2 (HTTP/2, zstd, fastcgi) |
| **Recommended Hardware** | 1 vCPU, 512MB RAM | 1 vCPU, 1GB RAM |

---

## Quickstart (3 Commands)

### 1. Clone and Configure Environment

```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site && cd my-site
cp .env.example .env
```

### 2. Start Container

```bash
docker compose -f docker/docker-compose.yml up -d --build
```

### 3. Access Site

Open your browser at `http://localhost:8080` to complete the standard 5-minute WordPress installation.

---

## Architecture Layout

```text
iz-wp-lite/
├── .agent/
│   └── izdeploy.json               # izDeploy PaaS deployment contract
├── config/
│   ├── application.php             # Core environment bootstrapper
│   └── environments/
│       ├── development.php
│       ├── staging.php
│       └── production.php
├── docker/
│   ├── Caddyfile                   # Caddy reverse proxy rules & security filters
│   ├── Dockerfile                  # Multi-stage build (Composer builder + Alpine runtime)
│   ├── docker-compose.yml          # Local and single-node production deployment
│   └── entrypoint.sh               # Volume permissions & daemon coordinator
├── web/
│   ├── app/
│   │   ├── database/               # SQLite storage directory (.ht.sqlite)
│   │   ├── mu-plugins/
│   │   │   └── 00-sqlite-loader.php # Drop-in validator & SQLite WAL pragma enforcer
│   │   ├── plugins/                # Composer-managed plugins
│   │   ├── themes/                 # WordPress themes
│   │   ├── uploads/                # Media uploads (or Cloudflare R2 proxy)
│   │   └── db.php                  # Dual-engine database drop-in router
│   ├── index.php                   # Web entrypoint
│   └── wp-config.php               # Bedrock loader stub
├── .env.example                    # Environment variable schema
├── .gitignore                      # Git exclusion rules
├── composer.json                   # Dependency definitions & installer paths
└── README.md
```

---

## Dual-Engine Database Switch

`iz-wp-lite` provides runtime database routing without modifying code or reinstalling core files.

### Mode A: SQLite (Default)

Configured in `.env`:

```ini
DB_ENGINE=sqlite
DB_DIR=web/app/database
DB_FILE=.ht.sqlite
```

Characteristics:
- Runs in a single container with zero TCP overhead.
- Operates under Write-Ahead Logging (`PRAGMA journal_mode = WAL`) and 5000ms busy timeout to prevent write locks during concurrent requests.
- Suited for corporate profile websites, blogs, documentation hubs, and portfolios.

### Mode B: MariaDB / MySQL

1. Update `.env`:

```ini
DB_ENGINE=mysql
DB_NAME=wp_lite
DB_USER=wp_user
DB_PASSWORD=wp_secure_password
DB_HOST=mariadb:3306
```

2. Uncomment the `mariadb` service block in `docker/docker-compose.yml`.

3. Re-run deployment:

```bash
docker compose -f docker/docker-compose.yml up -d
```

The drop-in at `web/app/db.php` detects `DB_ENGINE=mysql` and automatically passes control back to WordPress core's native MySQL driver.

---

## VPS 512MB Optimization Details

1. **PHP-FPM `ondemand` Governor:**
   - Workers spawn only upon incoming HTTP requests and terminate after 10 seconds of inactivity (`pm.process_idle_timeout = 10s`).
   - Maximum 3 workers (`pm.max_children = 3`) limits PHP memory consumption to under 120MB under concurrent load.

2. **Caddy Reverse Proxy:**
   - Compiled Go binary replacing Nginx and PHP-FPM separate stacks.
   - Built-in gzip/zstd compression.
   - Rejection of hidden files, `.env`, `composer.json`, and `.sqlite` paths directly at L7.

3. **Multi-Stage Build:**
   - Build tools, Git, and Composer cache remain isolated in Stage 1 (`builder`).
   - Stage 2 (`runtime`) contains only Alpine Linux, compiled PHP extensions, and static web assets, keeping final image footprint minimal.

---

## License & Trademark Disclaimer

- Framework code and configuration: [MIT License](LICENSE).
- WordPress components: GNU General Public License v2 (or later).
- **Trademark Disclaimer:** WordPress is a registered trademark of the WordPress Foundation. `iz-wp-lite` is an independent open-source project and is not affiliated with, endorsed by, or sponsored by the WordPress Foundation.
