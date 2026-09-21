<?php
/**
 * Plugin Name: iz-wp-lite Dual-Engine Database Drop-in
 * Description: Intercepts WordPress database connection and routes to SQLite or native MySQL based on DB_ENGINE environment variable.
 * Version: 1.0.0
 *
 * DECISION: Dynamic Drop-in Switching / WHY: Avoid file renaming or deletion when switching DB_ENGINE between sqlite and mysql in .env / TRADE-OFF: Early check overhead (~0.05ms) / REF: https://github.com/WordPress/sqlite-database-integration
 *
 * @package iz-wp-lite
 * @ai-context WordPress early drop-in interceptor replacing default wpdb.
 * @ai-constraint Must not throw fatal error if SQLite plugin is missing or if DB_ENGINE=mysql.
 */

// If MySQL/MariaDB is chosen, exit drop-in and let WordPress instantiate standard wpdb
if (defined('DB_ENGINE') && DB_ENGINE === 'mysql') {
    return;
}

// Locate SQLite driver implementation folder
$sqlite_search_paths = [
    WP_CONTENT_DIR . '/plugins/sqlite-database-integration',
    WP_CONTENT_DIR . '/mu-plugins/sqlite-database-integration',
    dirname(__DIR__, 2) . '/vendor/wpackagist-plugin/sqlite-database-integration',
];

$sqlite_driver_dir = null;
foreach ($sqlite_search_paths as $path) {
    if (file_exists($path . '/wp-includes/sqlite/db.php')) {
        $sqlite_driver_dir = $path;
        break;
    }
}

if (!$sqlite_driver_dir) {
    // If SQLite driver files have not yet been downloaded via composer,
    // bail out to prevent a fatal error.
    return;
}

if (!defined('DATABASE_TYPE')) {
    define('DATABASE_TYPE', 'sqlite');
}
if (!defined('DB_ENGINE')) {
    define('DB_ENGINE', 'sqlite');
}

// Define SQLite database directory and file if not defined
if (!defined('DB_DIR')) {
    define('DB_DIR', WP_CONTENT_DIR . '/database');
}
if (!defined('DB_FILE')) {
    define('DB_FILE', '.ht.sqlite');
}

// Require the driver implementation
require_once $sqlite_driver_dir . '/wp-includes/sqlite/db.php';
