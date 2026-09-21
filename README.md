# iz-wp-lite

> **The Ultra-Lightweight, AI-Native 12-Factor WordPress Starter.**  
> Complete WordPress runtime on 512MB RAM VPS instances (~45MB Idle RAM), featuring Caddy automated SSL, dual-engine database routing (SQLite / MariaDB), Security-First isolation, and native readiness for AI Coding Agents.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.3-777BB4.svg)](https://www.php.net/)
[![WordPress Version](https://img.shields.io/badge/WordPress-6.5+-21759B.svg)](https://wordpress.org/)
[![Memory Footprint](https://img.shields.io/badge/Idle%20RAM-~45MB-brightgreen.svg)]()

---

## 1. Why iz-wp-lite? (The Problem & The Why)

Traditional WordPress architectures carry over 20 years of legacy overhead:
- **Severe Resource Bloat:** Standard LAMP (Linux, Apache, MySQL, PHP) stacks consume 400MB–650MB of baseline RAM, forcing developers to rent 1GB–2GB VPS instances ($6–$15/month) even for simple corporate websites with low daily traffic.
- **Critical Configuration Vulnerabilities:** `wp-config.php` resides directly inside the public Document Root. A single web server misconfiguration can expose database credentials as plaintext.
- **Git & Deployment Friction:** User media uploads (`wp-content/uploads/`) and WordPress Core files are mixed into the repository tree, causing version drift and merge conflicts during automated deployments.

**iz-wp-lite resolves these structural bottlenecks:**
- **~45MB Baseline Memory:** Embedded SQLite engine combined with Caddy written in Go runs reliably on **512MB RAM VPS instances ($3.50/month)**.
- **Security-First Architecture:** Web root strictly isolated to `web/`. The `.env` secret file, configuration logic, and `vendor/` libraries remain completely inaccessible via HTTP.
- **Stateless Application Layer:** Native S3/R2 object storage integration eliminates local disk growth, delivering 0$ egress bandwidth.

---

## 2. Technical Stack

| Layer | Technology | Engineering Rationale |
|---|---|---|
| **Skeleton & Structure** | [Roots Bedrock](https://roots.io/bedrock/) | 12-Factor web app structure; Composer and `wpackagist.org` dependency lifecycle. |
| **Dual-Engine Database** | SQLite 3 (WAL Mode) / MariaDB 10.11 | Dynamic runtime database routing via `.env` (`DB_ENGINE=sqlite` or `mysql`). |
| **Web Server & SSL** | Caddy v2 (Official Alpine Binary) | ~25MB memory footprint; zero-touch automated Let's Encrypt / ZeroSSL certificate provisioning. |
| **PHP Runtime** | PHP 8.3-FPM (Alpine Linux) | Managed via `ondemand` governor, 3 worker cap, and 64MB OPcache buffer. |
| **Cloud Storage** | Cloudflare R2 / S3 | Distributed Edge CDN assets with 10GB free tier and zero egress bandwidth fees. |
| **Deployment Engine** | `izDeploy` / Docker Compose | Single-contract PaaS (`.agent/izdeploy.json`); sub-second container swaps. |

---

## 3. Security-First & Performance-First Design

### Security-First Architecture
1. **Isolated Document Root:** Web server serves strictly from `/var/www/html/web`. Parent directories containing `.env`, `config/`, and `composer.json` return HTTP 403 Forbidden.
2. **Immutable Production Core (`DISALLOW_FILE_MODS`):** File editing and plugin installations via WP-Admin are locked down on Production (`DISALLOW_FILE_MODS = true`), eliminating webshell attacks from compromised admin accounts.
3. **Hardened Passwords (`roots/wp-password-bcrypt`):** Replaces legacy MD5 hashing with PHP native Bcrypt algorithms.
4. **Automated TLS Protocol Standards:** Caddy enforces TLS 1.2 / TLS 1.3 and HSTS headers out of the box with zero manual configuration.

### Performance-First Benchmarks
- **Cold Boot Time:** Under 15 seconds via Docker Multi-Stage builds.
- **Idle Memory:** ~45MB (SQLite mode) vs ~190MB (MariaDB mode) vs ~550MB (Standard LAMP stack).
- **Time to First Byte (TTFB):** Under 30ms when served through Cloudflare Edge Caching.

---

## 4. Native AI Agent & Vibe Coding Readiness

`iz-wp-lite` is engineered specifically for modern AI Developer Environments (**Cursor, Claude Code, Antigravity, Windsurf**):

- **Zero Command-Line Friction:** Clear, deterministic repository boundaries enable AI agents to read, modify, and lint code with zero hallucination.
- **Natural Language Extension:** Issue directives directly to your AI Agent:
  - *"Enable Rank Math SEO and regenerate lockfile"*
  - *"Deploy this release to my Hetzner VPS using izDeploy"*
- **PaaS Deployment Contract (`.agent/izdeploy.json`):** AI Agents execute remote deployments via single-command contracts, ensuring automated validation before container rollover.

---

## 5. Who It's For

- **B2B Enterprises & Local Service Businesses:** Deliver high-converting corporate sites achieving 98–100 Google PageSpeed scores with near-zero infrastructure overhead.
- **Content Creators, Authors & SEO Specialists:** Maintain lean editorial sites with automated XML sitemaps, structured schema markup, and rapid indexing.
- **Solo Developers & Web Agencies (e.g., `iz-web`):** Standardize reproducible client deployments across Git and Docker, completely preventing white-screen update failures.

> **Constraint:** For high-concurrency transactional e-commerce (WooCommerce > 50 orders/hour), switch to `DB_ENGINE=mysql` with minimum 2GB RAM.

---

## 6. Quickstart (3 Commands)

### Step 1: Clone Repository & Configure Environment
```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site && cd my-site
cp .env.example .env
```

### Step 2: Build and Run Container
```bash
docker compose -f docker/docker-compose.yml up -d --build
```

### Step 3: Complete Setup
Navigate to `http://localhost:8080` to complete the standard WordPress installation.

---

## 7. Pre-Configured Optional Plugins (Comment / Uncomment)

To keep `iz-wp-lite` strictly minimal and bloat-free by default, optional enterprise plugins are defined as one-line activations.

### Enabling Popular Plugins

Run the corresponding command in your project root, or let your AI Agent execute it:

```bash
# 1. Cloudflare R2 / S3 Media Offloading (Zero server disk usage)
composer require humanmade/s3-uploads

# 2. SEO, XML Sitemaps & Schema Markup (Rank Math)
composer require wpackagist-plugin/seo-by-rank-math

# 3. Automatic H2/H3 Table of Contents Navigation
composer require wpackagist-plugin/easy-table-of-contents

# 4. Contact Form Engine
composer require wpackagist-plugin/contact-form-7
```

*Note: Once installed via Composer, `web/app/mu-plugins/01-default-plugins-activator.php` will automatically detect and activate them in WP-Admin with zero manual clicking required.*

---

## 8. Dual-Engine Database Routing

Toggle the database engine at any time in `.env`:

* **SQLite Mode (Default - ~45MB RAM):**
  ```ini
  DB_ENGINE=sqlite
  ```
  Runs with Write-Ahead Logging (`PRAGMA journal_mode = WAL`) and 5000ms busy timeout.

* **MariaDB / MySQL Mode:**
  ```ini
  DB_ENGINE=mysql
  DB_NAME=wp_database
  DB_USER=wp_user
  DB_PASSWORD=secret_password
  DB_HOST=mariadb:3306
  ```
  The drop-in at `web/app/db.php` automatically routes queries back to WordPress core's native MySQL engine.

---

## 9. License & Acknowledgements

- **Framework & Configuration:** [MIT License](LICENSE).
- **WordPress Core & Components:** GNU General Public License v2 (GPLv2).
- **Acknowledgements:** Project skeleton derived from and inspired by [Roots Bedrock](https://roots.io/bedrock/) by **Roots.io**. We acknowledge their leadership in modernizing the WordPress development ecosystem.
- **Trademark Disclaimer:** WordPress is a registered trademark of the WordPress Foundation. `iz-wp-lite` is an independent open-source project and is not affiliated with, endorsed by, or sponsored by the WordPress Foundation.
