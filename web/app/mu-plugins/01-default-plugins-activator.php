<?php
/**
 * Plugin Name: 01-default-plugins-activator
 * Plugin URI: https://github.com/izhubs/iz-wp-lite
 * Description: Automatically ensures essential plugins (Rank Math, Easy TOC, S3 Uploads) are active upon deployment.
 * Version: 1.0.0
 * Author: iZdigi Systems Team
 *
 * DECISION: Programmatic Auto-Activation / WHY: Eliminate manual plugin activation clicks in WP-Admin for boilerplate deploys / TRADE-OFF: Runs check on admin init / REF: https://developer.wordpress.org/reference/functions/activate_plugin/
 *
 * @package iz-wp-lite
 * @ai-context Must-Use plugin ensuring baseline SEO and navigation plugins are active.
 */

// Block direct script execution
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Automatically activate baseline plugins if they are present in web/app/plugins.
 *
 * @return void
 */
function iz_wp_lite_auto_activate_default_plugins(): void
{
    // Only run in admin or CLI context to save frontend request cycles
    if (!is_admin() && (!defined('WP_CLI') || !WP_CLI)) {
        return;
    }

    if (!function_exists('is_plugin_active') || !function_exists('activate_plugin')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $default_plugins = [
        'seo-by-rank-math/rank-math.php',
        'easy-table-of-contents/easy-table-of-contents.php',
        's3-uploads/s3-uploads.php',
    ];

    foreach ($default_plugins as $plugin_file) {
        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
        if (file_exists($plugin_path) && !is_plugin_active($plugin_file)) {
            activate_plugin($plugin_file);
        }
    }
}
add_action('admin_init', 'iz_wp_lite_auto_activate_default_plugins', 20);
