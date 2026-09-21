# iz-wp-lite

> Production-ready WordPress on a 512MB VPS. Git deploy, auto-HTTPS, MariaDB micro-footprint, AI-native contracts.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-6.4%2B-21759B.svg)](https://wordpress.org/)
[![RAM](https://img.shields.io/badge/Idle%20RAM-~155MB-brightgreen.svg)](.agent/izdeploy.json)
[![CI](https://github.com/izhubs/iz-wp-lite/actions/workflows/ci.yml/badge.svg)](https://github.com/izhubs/iz-wp-lite/actions/workflows/ci.yml)

---

## Install (one command)

```bash
curl -fsSL https://raw.githubusercontent.com/izhubs/iz-wp-lite/main/install.sh | bash
```

With a project name:
```bash
curl -fsSL https://raw.githubusercontent.com/izhubs/iz-wp-lite/main/install.sh | bash -s my-site
```

With VPS tier (1=512MB / 2=1GB / 3=2GB):
```bash
curl -fsSL https://raw.githubusercontent.com/izhubs/iz-wp-lite/main/install.sh | bash -s my-site --tier 2
```

Or via Composer:
```bash
composer create-project izhubs/iz-wp-lite my-site
```

Visit `http://localhost:8080` to complete WordPress setup.

---

## Documentation

| Guide | Description |
|---|---|
| [Local Development](docs/LOCAL_DEV.md) | SQLite quick mode, MariaDB full stack, WP-CLI, debug, live edit |
| [Deploy to VPS](docs/DEPLOY_VPS.md) | Bare metal setup on Hetzner/Ubuntu — DNS, SSL, systemd, backup |
| [Deploy to Coolify](docs/DEPLOY_COOLIFY.md) | Self-hosted PaaS — Git auto-deploy, Traefik SSL, environment vars |
| [Deploy to izDeploy](docs/DEPLOY_IZDEPLOY.md) | AI-native PaaS — contract-driven deployment, secrets, auto-scale |
| [Migration Guide](MIGRATION.md) | Move existing WordPress site to iz-wp-lite (10 steps + rollback) |

---

## Why?

Traditional WordPress on a \$4–5/month VPS crashes because default MySQL alone consumes 300–450MB RAM. Managed hosts (WP Engine, Kinsta) solve this but cost \$25–\$290/month per site.

iz-wp-lite fits a full WordPress stack in ~155MB by combining:
- `mariadb-lowram.cnf` — caps MariaDB at 60MB (vs 300MB+ default)
- PHP-FPM `ondemand` — zero workers when idle
- Caddy — 25MB, handles TLS automatically
- Bedrock — keeps `.env` and config outside the web root

---

## How it compares

| | WordPress vanilla | iz-wp-lite | Astro | Ghost | Payload CMS |
|---|---|---|---|---|---|
| **Stack** | PHP + MySQL (LAMP) | PHP + MariaDB micro + Caddy + Docker | Node.js, static HTML | Node.js + MySQL | Node.js + Postgres |
| **Idle RAM** | 300–600MB | ~155MB | ~50MB (build only) | ~200MB | ~250MB |
| **VPS cost** | \$8–15/mo (1GB needed) | \$4.50/mo (512MB fits) | \$0 (Netlify free) or \$4 | \$9/mo managed or \$5 self-host | \$6–10/mo |
| **Git deploy** | No (needs plugin) | Yes (native) | Yes (CI/CD) | Yes (Ghost CLI) | Yes |
| **Auto HTTPS** | No (manual Certbot) | Yes (Caddy zero-config) | Yes (CDN) | Yes (managed) | No (needs proxy) |
| **Plugin ecosystem** | 60,000+ (all) | 60,000+ (wpackagist only) | npm packages | ~100 integrations | Custom code |
| **Client edits content** | Yes (dashboard) | Limited (file mods locked) | No (needs headless CMS) | Yes (clean editor) | Yes (custom admin) |
| **WooCommerce** | Yes | Limited (≤50 orders/day default) | No | No | Custom only |
| **AI agent contract** | No | Yes (`.agent/izdeploy.json`) | No | No | No |
| **Setup time** | 10–30 min | 15 min (one command) | 5 min | 20 min | 60–90 min |
| **Best for** | General purpose, client hand-off | Agency sites, izDeploy PaaS, AI Vibe Coders | Marketing sites, portfolios | Newsletters, blogs | SaaS, custom apps |

### Use iz-wp-lite when

- You manage WordPress deployments and want `git push` → live, not FTP
- You need auto-HTTPS without configuring Certbot
- Your hosting budget is \$4–10/month per site
- You use Cursor, Claude Code, or Gemini Code Assist to scaffold sites
- You are building izDeploy PaaS templates

### Do not use iz-wp-lite when

- The client installs their own plugins via Dashboard (`DISALLOW_FILE_MODS=true` prevents this)
- WooCommerce site exceeds ~50 orders/day (use WooCommerce profile on 1GB+ VPS instead)
- You need WordPress Multisite (not tested, not on roadmap)
- Plugins are from CodeCanyon/Envato (no Composer endpoint, need private Satis repo)

---

## Architecture

```
iz-wp-lite/
├── web/                  # Web root (only this directory is served)
│   ├── app/
│   │   ├── mu-plugins/   # Auto-loaded: DB router, SQLite loader, security hardening
│   │   ├── plugins/      # Composer-managed plugins (gitignored)
│   │   ├── themes/       # Themes (gitignored, managed via Composer)
│   │   └── uploads/      # Media (Docker volume, gitignored)
│   └── wp/               # WordPress core (gitignored, installed by Composer)
├── config/
│   ├── application.php   # Main config (reads .env, sets constants)
│   └── environments/     # Per-environment overrides (dev/staging/production)
├── docker/
│   ├── Dockerfile        # Multi-stage: composer build → php:8.3-fpm-alpine + Caddy
│   ├── Caddyfile         # Auto-HTTPS, static cache routing, security blocks
│   ├── docker-compose.yml
│   ├── mariadb-lowram.cnf
│   └── profiles/
│       └── woocommerce.yml  # Override for WooCommerce (1GB+ VPS)
├── .agent/
│   └── izdeploy.json     # izDeploy PaaS machine-readable contract v2.0
├── install.sh            # One-command installer
└── .env.example          # Environment template
```

---

## VPS Tiers

Three memory profiles. Switch by editing `docker/docker-compose.yml` or using `--tier` flag with `install.sh`:

| Tier | VPS | Price | App RAM | MariaDB RAM | Redis | WooCommerce |
|---|---|---|---|---|---|---|
| **1 (default)** | 512MB (Hetzner CX11) | ~\$4.50/mo | 192MB limit | 96MB limit + micro-config | No | ≤50 orders/day |
| **2** | 1GB (Hetzner CX21) | ~\$8/mo | 384MB limit | 384MB (default config) | No | ≤200 orders/day |
| **3** | 2GB (Hetzner CX31) | ~\$15/mo | 768MB limit | 768MB (default config) | Yes (128MB) | ≤1000 orders/day |

WooCommerce profile (Tier 2+):
```bash
docker compose -f docker/docker-compose.yml -f docker/profiles/woocommerce.yml up -d
```

---

## Security

Built-in hardening (no plugin required):

| Vector | Protection |
|---|---|
| `wp-config.php` exposure | Config outside web root (Bedrock pattern) |
| Dashboard plugin installs | `DISALLOW_FILE_MODS=true` in production |
| XML-RPC brute force | Blocked at Caddy network layer + PHP mu-plugin |
| User enumeration | REST API `/wp/v2/users` restricted for unauthenticated requests |
| Login error verbosity | Generic error message (no username confirmation) |
| Comment spam | HTML stripped, nofollow enforced, name+email required |
| Malware via shared hosting | Container isolation (no shared filesystem) |
| Redirect loop (Cloudflare Tunnel) | `X-Forwarded-Proto` detection in `production.php` |
| WordPress version disclosure | Removed from `<meta>` tags |

Recommended additional plugins (one command each):
```bash
composer require wpackagist-plugin/akismet              # Comment spam
composer require wpackagist-plugin/two-factor           # 2FA for admin
composer require wpackagist-plugin/limit-login-attempts-reloaded  # Brute force
```

Disable comments site-wide (add to `.env`):
```ini
DISABLE_COMMENTS=true
```

---

## Optional plugins

All optional. Install with one command, activated automatically:

```bash
# Media offloading to Cloudflare R2 / S3
composer require humanmade/s3-uploads

# SEO: sitemaps, JSON-LD schemas
composer require wpackagist-plugin/seo-by-rank-math

# Contact forms (pair with CF7 Honeypot for spam)
composer require wpackagist-plugin/contact-form-7
composer require wpackagist-plugin/cf7-honeypot

# Redis object cache (Tier 3 only)
composer require wpackagist-plugin/redis-cache

# Table of contents
composer require wpackagist-plugin/easy-table-of-contents
```

---

## Database engines

| Mode | Set in `.env` | RAM | Use case |
|---|---|---|---|
| **MariaDB** (default) | `DB_ENGINE=mysql` | +60MB | Production, all plugins |
| **SQLite** | `DB_ENGINE=sqlite` | +0MB | Local dev, CI, instant preview |

SQLite write-locks the entire database per request. Do not use SQLite with WooCommerce or any concurrent-write workload.

---

## License & credits

- **License:** [MIT](LICENSE)
- **WordPress Core:** GPLv2 (WordPress Foundation)
- **Project skeleton:** Derived from [Roots Bedrock](https://roots.io/bedrock/) by Roots.io (MIT). Not affiliated with or endorsed by Roots.io.
- **Trademark:** WordPress® is a registered trademark of the WordPress Foundation. iz-wp-lite is an independent project.
