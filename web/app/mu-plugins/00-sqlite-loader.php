<?php
/**
 * Plugin Name: 00-sqlite-loader
 * Plugin URI: https://github.com/izhubs/iz-wp-lite
 * Description: SQLite runtime guardian and drop-in verification engine for iz-wp-lite.
 * Version: 1.0.0
 * Author: iZdigi Systems Team
 *
 * DECISION: Runtime WAL & Pragmas Enforcement / WHY: Protect SQLite from write contention locks on VPS 512MB RAM / TRADE-OFF: WAL file persistence / REF: https://sqlite.org/wal.html
 *
 * @package iz-wp-lite
 * @ai-context Must-Use plugin verifying SQLite drop-in presence, directory security, and SQLite WAL pragmas.
 * @ai-constraint Must gracefully no-op if DB_ENGINE is not sqlite.
 */

// Block direct script execution
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verify drop-in status and enforce SQLite performance pragmas.
 *
 * Checks if SQLite is active, validates storage directory permissions,
 * and sets PRAGMA journal_mode = WAL for maximum concurrency.
 *
 * @return void
 * @ai-constraint Runs on muplugins_loaded to ensure early database optimization.
 */
function iz_wp_lite_bootstrap_sqlite(): void {
    if (!defined('DB_ENGINE') || DB_ENGINE !== 'sqlite') {
        return;
    }

    $webroot_dir = dirname(__DIR__, 2);
    $app_dir = $webroot_dir . '/app';
    $dropin_file = $app_dir . '/db.php';

    // Verify that web/app/db.php exists. If missing, attempt automatic recovery from plugin source.
    if (!file_exists($dropin_file)) {
        $source_candidates = [
            $app_dir . '/plugins/sqlite-database-integration/db.copy',
            $app_dir . '/mu-plugins/sqlite-database-integration/db.copy',
        ];

        foreach ($source_candidates as $source) {
            if (file_exists($source)) {
                @copy($source, $dropin_file);
                break;
            }
        }
    }

    // Ensure database directory exists with restrictive permissions
    $db_dir = defined('DB_DIR') ? DB_DIR : $app_dir . '/database';
    if (!is_dir($db_dir)) {
        @mkdir($db_dir, 0750, true);
    }

    // Protect against direct web access
    $htaccess_file = $db_dir . '/.htaccess';
    if (is_dir($db_dir) && !file_exists($htaccess_file)) {
        @file_put_contents($htaccess_file, "Require all denied\nDeny from all\n");
    }

    // Enforce SQLite WAL (Write-Ahead Logging) and busy timeout
    global $wpdb;
    if (isset($wpdb) && method_exists($wpdb, 'query')) {
        // Enforce WAL mode for non-blocking concurrent reads during writes
        $wpdb->query("PRAGMA journal_mode = WAL;");
        // Wait up to 5000ms if database is temporarily locked by another process
        $wpdb->query("PRAGMA busy_timeout = 5000;");
        // Safe synchronous mode for WAL
        $wpdb->query("PRAGMA synchronous = NORMAL;");
    }
}
add_action('muplugins_loaded', 'iz_wp_lite_bootstrap_sqlite', 1);

/**
 * Register admin bar node displaying current database engine status.
 *
 * Informs administrators whether the site is running on SQLite or MariaDB.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar object.
 * @return void
 * @ai-constraint Strictly visible only to administrators.
 */
function iz_wp_lite_admin_bar_badge($wp_admin_bar): void {
    if (!current_user_can('manage_options')) {
        return;
    }

    $is_sqlite = defined('DB_ENGINE') && DB_ENGINE === 'sqlite';
    $engine_title = $is_sqlite ? 'DB: SQLite (WAL)' : 'DB: MariaDB/MySQL';

    $wp_admin_bar->add_node([
        'id'    => 'iz-db-engine',
        'title' => '<span class="ab-icon dashicons dashicons-database"></span> ' . esc_html($engine_title),
        'href'  => admin_url('site-health.php'),
        'meta'  => ['title' => 'iz-wp-lite Database Engine Status'],
    ]);
}
add_action('admin_bar_menu', 'iz_wp_lite_admin_bar_badge', 100);
