<?php
/**
 * Application configuration
 *
 * DECISION: Dual-Engine Database Switch / WHY: Enable single-container SQLite on VPS 512MB RAM with seamless fallback to MariaDB / TRADE-OFF: Concurrency limited to WAL mode writes / REF: https://github.com/roots/bedrock
 *
 * @package iz-wp-lite
 * @ai-context System bootstrapper loading environment variables and configuring WordPress core constants.
 * @ai-constraint Strictly maintain 12-Factor App statelessness and never commit secrets.
 */

use function Env\env;
use Dotenv\Dotenv;

$root_dir = dirname(__DIR__);
$webroot_dir = $root_dir . '/web';

/**
 * Expose global env() function from oscarotero/env
 * and initialize Dotenv if .env exists.
 */
if (file_exists($root_dir . '/.env')) {
    $dotenv = Dotenv::createUnsafeImmutable($root_dir);
    $dotenv->load();
    $dotenv->required(['WP_HOME']);

    if (env('DB_ENGINE') === 'mysql') {
        if (!env('DATABASE_URL')) {
            $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD']);
        }
    }
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

/**
 * URLs
 */
define('WP_HOME', env('WP_HOME') ?: 'http://localhost:8080');
define('WP_SITEURL', env('WP_SITEURL') ?: env('WP_HOME') . '/wp');

/**
 * Custom Content Directory
 */
define('CONTENT_DIR', '/app');
define('WP_CONTENT_DIR', $webroot_dir . CONTENT_DIR);
define('WP_CONTENT_URL', env('WP_HOME') . CONTENT_DIR);

/**
 * Dual-Engine Database Configuration
 * Supports 'sqlite' (default) and 'mysql' (MariaDB)
 */
$db_engine = strtolower(env('DB_ENGINE') ?: 'sqlite');
define('DB_ENGINE', $db_engine);

if ($db_engine === 'sqlite') {
    // Determine SQLite storage location
    $db_dir_rel = env('DB_DIR') ?: 'web/app/database';
    // Resolve relative path against root_dir if not absolute
    $db_dir = (strpos($db_dir_rel, '/') === 0 || preg_match('/^[A-Za-z]:[\\\\\/]/', $db_dir_rel))
        ? $db_dir_rel
        : $root_dir . '/' . $db_dir_rel;
    $db_file = env('DB_FILE') ?: '.ht.sqlite';

    define('DB_DIR', $db_dir);
    define('DB_FILE', $db_file);

    // Provide default fallback credentials to satisfy WordPress core checks
    define('DB_NAME', env('DB_NAME') ?: 'sqlite');
    define('DB_USER', env('DB_USER') ?: 'sqlite');
    define('DB_PASSWORD', env('DB_PASSWORD') ?: 'sqlite');
    define('DB_HOST', env('DB_HOST') ?: 'localhost');

    // Ensure SQLite database directory exists with strict permissions
    if (!is_dir($db_dir)) {
        @mkdir($db_dir, 0750, true);
    }
    // Prevent direct web directory browsing
    if (is_dir($db_dir) && !file_exists($db_dir . '/index.php')) {
        @file_put_contents($db_dir . '/index.php', "<?php\n// Silence is golden.\n");
    }
} else {
    // MariaDB / MySQL Configuration
    if (env('DATABASE_URL')) {
        $dsn = (object) parse_url(env('DATABASE_URL'));
        define('DB_NAME', substr($dsn->path, 1));
        define('DB_USER', $dsn->user);
        define('DB_PASSWORD', isset($dsn->pass) ? $dsn->pass : null);
        define('DB_HOST', isset($dsn->port) ? "{$dsn->host}:{$dsn->port}" : $dsn->host);
    } else {
        define('DB_NAME', env('DB_NAME'));
        define('DB_USER', env('DB_USER'));
        define('DB_PASSWORD', env('DB_PASSWORD'));
        define('DB_HOST', env('DB_HOST') ?: '127.0.0.1');
    }
}

define('DB_CHARSET', env('DB_CHARSET') ?: 'utf8mb4');
define('DB_COLLATE', env('DB_COLLATE') ?: '');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

/**
 * Authentication Unique Keys and Salts
 */
define('AUTH_KEY',         env('AUTH_KEY') ?: 'insecure-auth-key-placeholder');
define('SECURE_AUTH_KEY',  env('SECURE_AUTH_KEY') ?: 'insecure-sec-auth-key-placeholder');
define('LOGGED_IN_KEY',    env('LOGGED_IN_KEY') ?: 'insecure-logged-in-key-placeholder');
define('NONCE_KEY',        env('NONCE_KEY') ?: 'insecure-nonce-key-placeholder');
define('AUTH_SALT',        env('AUTH_SALT') ?: 'insecure-auth-salt-placeholder');
define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT') ?: 'insecure-sec-salt-placeholder');
define('LOGGED_IN_SALT',   env('LOGGED_IN_SALT') ?: 'insecure-logged-salt-placeholder');
define('NONCE_SALT',       env('NONCE_SALT') ?: 'insecure-nonce-salt-placeholder');

/**
 * Custom Settings
 */
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', env('DISALLOW_FILE_MODS') ?: (WP_ENV !== 'development'));

/**
 * Cloudflare R2 / S3 Uploads (Stage 2 Ready)
 */
if (env('S3_UPLOADS_BUCKET')) {
    define('S3_UPLOADS_BUCKET', env('S3_UPLOADS_BUCKET'));
    define('S3_UPLOADS_KEY', env('S3_UPLOADS_KEY'));
    define('S3_UPLOADS_SECRET', env('S3_UPLOADS_SECRET'));
    define('S3_UPLOADS_REGION', env('S3_UPLOADS_REGION') ?: 'auto');
    if (env('S3_UPLOADS_ENDPOINT')) {
        define('S3_UPLOADS_ENDPOINT', env('S3_UPLOADS_ENDPOINT'));
    }
    if (env('S3_UPLOADS_BUCKET_URL')) {
        define('S3_UPLOADS_BUCKET_URL', env('S3_UPLOADS_BUCKET_URL'));
    }
}

/**
 * Load environment specific configuration
 */
$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';
if (file_exists($env_config)) {
    require_once $env_config;
}

/**
 * Bootstrap WordPress ABSPATH
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}
