# iz-wp-lite

> **The Modern, AI-Native 12-Factor WordPress Starter.**  
> Production-ready WordPress architecture running on low-resource VPS instances (512MB–1GB RAM), featuring Caddy automated SSL, MariaDB micro-footprint (~60MB RAM), optional SQLite runtime for instant preview, and native readiness for AI Coding Agents.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.3-777BB4.svg)](https://www.php.net/)
[![WordPress Version](https://img.shields.io/badge/WordPress-6.5+-21759B.svg)](https://wordpress.org/)
[![Total Stack RAM](https://img.shields.io/badge/Production%20RAM-~160MB-brightgreen.svg)]()

---

## 1. Why iz-wp-lite? (The Problem & The Why)

Traditional WordPress hosting carries structural legacy debt:
- **Severe Resource Overhead:** Standard LAMP (Apache + MySQL 8.0) setups easily consume 450MB–650MB of idle RAM, triggering Out-Of-Memory (OOM) crashes on entry-level cloud servers ($3.50–$6/month).
- **Configuration & Secret Exposure:** Storing `wp-config.php` inside the public web root creates severe vulnerability surfaces during web server misconfigurations.
- **Git State Pollution:** Mixing uploaded media files (`wp-content/uploads/`) and WordPress Core into Git prevents clean, reproducible CI/CD pipelines.

**iz-wp-lite provides an opinionated 12-Factor engineering solution:**
- **Lean Production Stack (~160MB Total RAM):** Single-process Caddy 2, PHP 8.3-FPM ondemand, and hardened MariaDB 10.11 configured with a 32MB buffer pool (`mariadb-lowram.cnf`), fitting comfortably within a 512MB RAM VPS.
- **Security-First Isolation:** Web root strictly locked to `web/`. Secrets (`.env`), system configuration (`config/`), and Composer libraries (`vendor/`) remain completely unreachable via HTTP.
- **Optional Cloud Media Offloading:** Stateless application design allowing direct asset offloading to Cloudflare R2 / S3 via `humanmade/s3-uploads` ($0 egress bandwidth).
- **Dual-Engine Flexibility:** Production runs on 100% compatible MariaDB by default, while developers and CI/CD pipelines can toggle `DB_ENGINE=sqlite` for 2-second ephemeral preview environments.

---

## 2. Technical Stack

| Layer | Technology | Engineering Rationale |
|---|---|---|
| **Project Structure** | [Roots Bedrock](https://roots.io/bedrock/) | 12-Factor web app architecture; Composer and `wpackagist.org` dependency management. |
| **Production Database** | MariaDB 10.11 (Micro-Footprint) | 100% WordPress ecosystem compatibility; capped at ~60MB RAM via `mariadb-lowram.cnf`. |
| **Instant Preview DB** | SQLite 3 (WAL Mode) | Optional single-file database for local testing and CI ephemeral preview containers. |
| **Web Server & SSL** | Caddy v2 (Alpine Binary) | ~25MB memory footprint; zero-touch automated Let's Encrypt / ZeroSSL certificate lifecycle. |
| **PHP Runtime** | PHP 8.3-FPM (Alpine Linux) | Managed via `ondemand` governor, 3 worker cap, and 64MB OPcache buffer. |
| **Media Offloading (Optional)** | Cloudflare R2 / S3 | Zero-disk growth on VPS; global Edge CDN distribution. |
| **PaaS Deployment** | `izDeploy` / Docker Compose | Single-contract deployment (`.agent/izdeploy.json`); sub-second container swapping. |

---

## 3. Caching Strategy: Architectural Options

To maximize throughput and protect VPS resources, choose between 3 caching tiers:

| Tier | Mechanism | Implementation | Pros & Trade-offs |
|---|---|---|---|
| **Tier 1: Edge Caching (Recommended)** | Cloudflare Cache Everything | Configure Cloudflare Cache Rule: bypass `/wp-admin/*`, cache public HTML. | **Best:** 0MB server RAM, 0% CPU load, sub-30ms global TTFB. |
| **Tier 2: Static Page Caching** | File-based HTML Cache (e.g., Cache Enabler) | Saves pre-rendered `.html` files; Caddy serves static files directly without invoking PHP. | **Good:** Bypasses PHP-FPM for guests, minimal CPU impact. |
| **Tier 3: In-Memory Object Cache** | Redis Container | `pecl/redis` + Redis drop-in (`object-cache.php`). | **Requires 1GB+ RAM:** Consumes ~40MB RAM. Overkill for low-traffic sites (<1000 visits/day). |

---

## 4. Security-First & Performance-First Architecture

### Security-First Hardening
1. **Isolated Document Root:** Web server serves strictly from `/var/www/html/web`. Parent directories containing `.env`, `config/`, and `.git` return HTTP 403 Forbidden.
2. **Immutable Production Core (`DISALLOW_FILE_MODS`):** File modification and web-based plugin installations are locked down on Production, eliminating webshell injection from compromised admin accounts.
3. **Hardened Password Hashing (`roots/wp-password-bcrypt`):** Replaces legacy MD5 password hashes with native PHP Bcrypt.
4. **Automated TLS & Security Headers:** Enforces modern TLS 1.3, HSTS, `X-Content-Type-Options: nosniff`, and `X-Frame-Options: SAMEORIGIN`.

### Performance-First Benchmarks
- **Production Stack RAM (Caddy + PHP-FPM + MariaDB):** ~160MB idle memory.
- **SQLite Preview Stack RAM (Caddy + PHP-FPM):** ~50MB idle memory.
- **Cold Boot Time:** Under 15 seconds via Docker Multi-Stage builds.

---

## 5. Native AI Agent & Vibe Coding Readiness

`iz-wp-lite` is engineered specifically for modern AI Developer Environments (**Cursor, Claude Code, Antigravity, Windsurf**):

- **Deterministic Code Boundaries:** Well-defined repository boundaries enable AI agents to read, modify, and lint code with zero hallucination.
- **Natural Language Workflows:** Issue directives directly in your AI IDE:
  - *"Add Contact Form 7 to composer.json and rebuild"*
  - *"Switch to sqlite engine for local preview"*
  - *"Deploy this release to my Hetzner VPS using izDeploy"*
- **PaaS Deployment Contract (`.agent/izdeploy.json`):** AI Agents execute remote deployments via single-command contracts, ensuring automated validation before container rollover.

---

## 6. Who It's For

- **Solo Developers & Web Agencies (e.g., `iz-web`):** Standardize reproducible client deployments across Git and Docker, completely preventing white-screen update failures.
- **B2B Enterprises & Local Services:** Deliver corporate sites achieving 98–100 Google PageSpeed scores on inexpensive $3.50/month VPS infrastructure.
- **Content Creators, Authors & SEO Specialists:** Maintain lean editorial sites with automated XML sitemaps, structured schema markup, and rapid indexing.
- **PaaS Platforms (e.g., `izDeploy`):** Use SQLite mode as a lightweight "Instant Demo / Ephemeral Preview" template for prospective users.

---

## 7. Quickstart (3 Commands)

### Step 1: Clone Repository & Configure Environment
```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site && cd my-site
cp .env.example .env
```

### Step 2: Build and Run Container (Production MariaDB Default)
```bash
docker compose -f docker/docker-compose.yml up -d --build
```

### Step 3: Complete Setup
Navigate to `http://localhost:8080` to complete the standard WordPress installation.

---

## 8. Database Engine Selection

Toggle the database engine in `.env`:

* **MariaDB Mode (Default - 100% Plugin Compatible, ~160MB Total Stack RAM):**
  ```ini
  DB_ENGINE=mysql
  DB_NAME=wp_lite
  DB_USER=wp_user
  DB_PASSWORD=wp_secure_password
  DB_HOST=mariadb:3306
  ```

* **SQLite Mode (Ephemeral Preview / Instant Demo, ~50MB Total Stack RAM):**
  ```ini
  DB_ENGINE=sqlite
  ```
  *Constraint:* SQLite operates under database-level write locking. Suitable for read-heavy sites, local development, and instant preview demos.

---

## 9. Optional Pre-Configured Plugins

`iz-wp-lite` keeps the core dependency footprint minimal. Activate optional enterprise plugins with single commands:

```bash
# 1. Optional Cloudflare R2 / S3 Media Offloading (Zero VPS disk growth)
composer require humanmade/s3-uploads

# 2. SEO, XML Sitemaps & Schema Markup (Rank Math)
composer require wpackagist-plugin/seo-by-rank-math

# 3. Automatic H2/H3 Table of Contents Navigation
composer require wpackagist-plugin/easy-table-of-contents

# 4. Form Submissions
composer require wpackagist-plugin/contact-form-7
```

*Installed plugins are automatically detected and activated in WP-Admin by `web/app/mu-plugins/01-default-plugins-activator.php`.*

---

## 10. License & Acknowledgements

- **Framework & Configuration:** [MIT License](LICENSE).
- **WordPress Core & Components:** GNU General Public License v2 (GPLv2).
- **Acknowledgements:** Project skeleton derived from and inspired by [Roots Bedrock](https://roots.io/bedrock/) by **Roots.io**. We acknowledge their leadership in modernizing the WordPress development ecosystem.
- **Trademark Disclaimer:** WordPress is a registered trademark of the WordPress Foundation. `iz-wp-lite` is an independent open-source project and is not affiliated with, endorsed by, or sponsored by the WordPress Foundation.
