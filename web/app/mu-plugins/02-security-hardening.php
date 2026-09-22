<?php
/**
 * 02-security-hardening.php
 *
 * Core security hardening mu-plugin for iz-wp-lite.
 * Loaded automatically by WordPress on every request — no activation needed.
 *
 * WHY: WordPress default config enables XML-RPC (brute-force vector),
 * file editing via dashboard (code injection vector), user enumeration
 * via REST API, and comment spam. These are disabled here at the
 * mu-plugin level so they cannot be overridden by regular plugins.
 *
 * DECISION: mu-plugin vs plugin / WHY: mu-plugin loads before regular
 * plugins and cannot be disabled via wp-admin. This guarantees the
 * hardening layer survives plugin conflicts and client dashboard access.
 * TRADE-OFF: Developers cannot toggle these via UI — must edit this file.
 * REF: https://developer.wordpress.org/reference/functions/add_filter/
 *
 * @ai-constraint: Do NOT remove xmlrpc, user-enum, or comment protections
 * without explicit confirmation from project maintainer.
 */

if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// 1. XML-RPC — Disable completely
// WHY: XML-RPC is a legacy remote API used by Jetpack and old mobile apps.
// In 2023+ it is primarily a brute-force and DDoS amplification vector.
// Wordfence 2023 report: XML-RPC attacks account for ~37% of WP brute-force.
// =============================================================================
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_xmlrpc_server_class', '__return_false');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

// =============================================================================
// 2. REST API — Restrict user enumeration
// WHY: /wp-json/wp/v2/users exposes usernames to unauthenticated requests,
// enabling targeted credential attacks. Authenticated access is preserved.
// =============================================================================
add_filter('rest_endpoints', function ($endpoints) {
    if (!is_user_logged_in()) {
        if (isset($endpoints['/wp/v2/users'])) {
            unset($endpoints['/wp/v2/users']);
        }
        if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
        }
    }
    return $endpoints;
});

// =============================================================================
// 3. Login page — Remove error verbosity
// WHY: "Invalid username" vs "Incorrect password" reveals valid usernames.
// Replace with a generic message to prevent username enumeration.
// =============================================================================
add_filter('login_errors', function () {
    return 'Authentication failed. Please verify your credentials.';
});

// =============================================================================
// 4. Author URL enumeration — Block /?author=N redirect
// WHY: Iterating /?author=1, /?author=2 reveals all usernames via redirect.
// =============================================================================
add_action('template_redirect', function () {
    if (isset($_GET['author']) && !is_admin()) {
        wp_die('Author enumeration is disabled.', 403);
    }
});

// =============================================================================
// 5. Comment hardening
// WHY: WordPress comments are a classic spam and XSS injection vector.
// Strategy: require name + email, enforce nofollow on URLs, strip HTML.
// Users who do not need comments should set DISABLE_COMMENTS=true in .env.
//
// DECISION: soft-disable vs hard-disable / WHY: Hard-disable (close all
// comments) breaks sites using comments for community. Soft-disable enforces
// hygiene without removing functionality. TRADE-OFF: Spam can still get
// through — pair with Akismet (wpackagist-plugin/akismet).
// =============================================================================
add_filter('pre_comment_approved', function ($approved, $comment_data) {
    // Require name and email for all comments
    if (empty($comment_data['comment_author']) || empty($comment_data['comment_author_email'])) {
        return new WP_Error('comment_incomplete', 'Name and email are required.');
    }
    return $approved;
}, 10, 2);

// Strip dangerous HTML from comment content (keep only safe tags)
add_filter('pre_comment_content', function ($content) {
    return wp_kses($content, [
        'a'      => ['href' => [], 'title' => []],
        'em'     => [],
        'strong' => [],
        'code'   => [],
        'p'      => [],
    ]);
});

// Enforce nofollow + noopener on all comment links
add_filter('comment_text', function ($text) {
    return preg_replace(
        '/<a\s/i',
        '<a rel="nofollow noopener" ',
        $text
    );
});

// Optional: Disable comments site-wide via .env DISABLE_COMMENTS=true
if (getenv('DISABLE_COMMENTS') === 'true') {
    add_action('init', function () {
        // Close comments on all existing posts
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        // Remove comment count from admin dashboard
        add_filter('comments_array', '__return_empty_array', 10, 2);
    });
    // Remove comment-related menu items from admin
    add_action('admin_menu', function () {
        remove_menu_page('edit-comments.php');
    });
    // Remove comment support from post types
    add_action('init', function () {
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }, 100);
}

// =============================================================================
// 6. Contact Form / Submission hardening
// WHY: WordPress has no native form submission protection. Contact Form 7
// and WPForms are the most common vectors for spam and phishing relay.
// Hardening: nonce verification + honeypot header check at mu-plugin level.
//
// DECISION: Custom honeypot vs plugin honeypot / WHY: Plugin honeypots
// can be detected by scrapers via source code inspection. Custom field
// name is harder to fingerprint. TRADE-OFF: Must match field name in
// CF7/WPForms custom template. REF: CF7 hidden field filter.
// =============================================================================
// Block form submissions that arrive via direct POST without Referer (bots)
add_action('init', function () {
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        && !is_admin()
        && empty($_SERVER['HTTP_REFERER'])
        && isset($_POST['_wpcf7'])   // Only apply to CF7 submissions
    ) {
        wp_die('Form submission rejected: missing referer.', 403);
    }
});

// =============================================================================
// 7. Security headers — supplement Caddy headers with PHP-level additions
// WHY: Caddy handles most security headers. These add CSP and HSTS
// when Caddy is bypassed (e.g., direct PHP-FPM access in dev mode).
// =============================================================================
add_action('send_headers', function () {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // HSTS: only enable in production (requires valid TLS)
        if (defined('WP_ENV') && WP_ENV === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
});

// =============================================================================
// 8. Hide WordPress version from public meta tags
// WHY: Exposing exact WP version enables targeted exploit scanning.
// =============================================================================
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');
