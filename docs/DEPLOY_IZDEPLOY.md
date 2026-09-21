# Deploying to izDeploy PaaS

izDeploy reads the `.agent/izdeploy.json` contract file from your repository and automatically provisions, builds, and deploys your container to managed infrastructure.

---

## Requirements

- An izDeploy account (contact: hello@izdigi.com)
- A domain pointing to izDeploy nameservers
- Repository pushed to GitHub or GitLab

---

## Contract file

`.agent/izdeploy.json` (schema v2.0) is already configured in the repository:

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
    "tier1": "512MB VPS, ~$4.50/mo",
    "tier2": "1GB VPS, ~$8/mo",
    "tier3": "2GB VPS + Redis, ~$15/mo"
  }
}
```

AI coding agents (Cursor, Claude Code, Antigravity) read this contract and execute deployments without requiring human intervention.

---

## Deploy via izDeploy CLI

```bash
# Install the CLI
npm install -g @izdigi/deploy-cli

# Authenticate
izdeploy login

# Deploy from repository root
izdeploy deploy --contract .agent/izdeploy.json --domain yourdomain.com

# Select a VPS tier
izdeploy deploy --contract .agent/izdeploy.json --domain yourdomain.com --tier tier2

# Check deployment status
izdeploy status my-site

# Stream logs
izdeploy logs my-site --follow

# Roll back to previous version
izdeploy rollback my-site
```

---

## Secrets management

Secrets (database password, WordPress salts) are never stored in the repository. Register them via the CLI:

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

# Or auto-generate salts
izdeploy secrets generate-salts my-site
```

---

## Git webhook auto-deploy

```bash
# Connect GitHub repository
izdeploy connect github --repo izhubs/iz-wp-lite --project my-site

# Every git push to main branch triggers an automatic deployment
```

---

## VPS tier selection

| Tier | Flag | RAM | Price |
|---|---|---|---|
| Tier 1 | `--tier tier1` | 512MB | ~\$4.50/mo |
| Tier 2 | `--tier tier2` | 1GB | ~\$8/mo |
| Tier 3 | `--tier tier3` | 2GB + Redis | ~\$15/mo |

---

## AI agent deployment (Vibe Coding workflow)

In your IDE (Cursor / Antigravity / Claude Code):

```
"Deploy this WordPress site to production on a 512MB VPS using izDeploy"
```

The agent reads `.agent/izdeploy.json` → generates the deploy command → executes → reports the live URL back.

Contract-driven deployment means the AI agent does not need to know infrastructure details — everything is defined in the contract file.

---

## Pre/post deploy hooks

Defined in `izdeploy.json`:

```json
"deployment": {
  "strategy": "rolling",
  "zero_downtime": true,
  "pre_deploy": [
    "docker compose exec -T mariadb mariadb-admin ping --silent"
  ],
  "post_deploy": [
    "wp --allow-root cache flush"
  ]
}
```

`pre_deploy` verifies MariaDB is healthy before the new container goes live. `post_deploy` flushes the WordPress object cache after the container swap.

---

## Volumes and backups

izDeploy preserves named volumes across all deployments. Backup commands:

```bash
# Create a manual backup
izdeploy backup create my-site

# List available backups
izdeploy backup list my-site

# Restore from a specific backup
izdeploy backup restore my-site --backup-id <id>
```

---

## Monitoring

```bash
# Resource usage (RAM, CPU, request count)
izdeploy stats my-site

# Container logs
izdeploy logs my-site

# Set alerts (fires when thresholds are exceeded)
izdeploy alerts set my-site \
  --memory-threshold 80 \
  --cpu-threshold 80 \
  --email admin@yourdomain.com
```
