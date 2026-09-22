<?php
/**
 * Configuration overrides for WP_ENV === 'production'
 *
 * @package iz-wp-lite
 * @ai-context Hardened production environment configuration with disabled error reporting.
 * @ai-constraint Strictly disable debug output and error display.
 */

use function Env\env;

// =============================================================================
// Reverse proxy SSL detection (Cloudflare Tunnel, Nginx upstream, etc.)
// WHY: When a reverse proxy terminates HTTPS and forwards HTTP to this
// container, WordPress sees HTTP and issues a 301 redirect to HTTPS,
// creating an infinite redirect loop (ERR_TOO_MANY_REDIRECTS).
// This block tells WordPress to trust the X-Forwarded-Proto header.
// DECISION: Trust X-Forwarded-Proto unconditionally in production /
// WHY: Container network is isolated — only trusted upstream proxies reach
// this PHP process. TRADE-OFF: If somehow exposed directly without proxy,
// a malicious client could spoof the header. Acceptable in container env.
// REF: u/devops_medic r/selfhosted 2024 — confirmed fix for WP redirect loop.
// =============================================================================
if (
    isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
    && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
) {
    $_SERVER['HTTPS'] = 'on';
}

define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', false);
define('SCRIPT_DEBUG', false);
define('DISALLOW_INDEXING', false);
define('DISALLOW_FILE_MODS', true);
define('DISALLOW_FILE_EDIT', true);

ini_set('display_errors', '0');

