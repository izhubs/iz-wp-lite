<?php
/**
 * Seed Script: 100 Posts with Images, Demo Page, and WooCommerce Products
 * Executed via WP-CLI: wp eval-file /var/www/html/scripts/seed_data.php
 */

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

echo "=== Seeding iz-wp-lite: Demo Page, 100 Posts & WooCommerce Products ===\n\n";

// 1. Download & Import Sample Media Attachments
echo "[1/4] Importing high-resolution media attachments...\n";
$attachment_ids = [];
$sample_images = [
    'https://picsum.photos/id/1/1200/800.jpg'   => 'Laptop on workspace desk',
    'https://picsum.photos/id/20/1200/800.jpg'  => 'Minimalist creative workspace',
    'https://picsum.photos/id/60/1200/800.jpg'  => 'Modern electronic computing station',
    'https://picsum.photos/id/119/1200/800.jpg' => 'Compact mechanical hardware',
    'https://picsum.photos/id/180/1200/800.jpg' => 'Notebook and technical sketch',
    'https://picsum.photos/id/201/1200/800.jpg' => 'Engineering blueprint and tools',
    'https://picsum.photos/id/366/1200/800.jpg' => 'Architecture and modern geometric facade',
    'https://picsum.photos/id/445/1200/800.jpg' => 'Studio lighting and workstation setup',
];

foreach ($sample_images as $url => $desc) {
    echo "  - Fetching: {$desc}...\n";
    $tmp_file = download_url($url);
    if (!is_wp_error($tmp_file)) {
        $file_array = [
            'name'     => sanitize_title($desc) . '.jpg',
            'tmp_name' => $tmp_file,
        ];
        $id = media_handle_sideload($file_array, 0, $desc);
        if (!is_wp_error($id)) {
            $attachment_ids[] = $id;
            echo "    -> Imported attachment ID: {$id}\n";
        } else {
            echo "    -> Error: " . $id->get_error_message() . "\n";
            @unlink($tmp_file);
        }
    } else {
        echo "    -> Download failed: " . $tmp_file->get_error_message() . "\n";
    }
}

if (empty($attachment_ids)) {
    echo "Warning: No remote images imported, using placeholder IDs.\n";
}

// 2. Setup Categories
echo "\n[2/4] Setting up categories...\n";
$categories = [
    'Cloud Infrastructure'     => 'Articles regarding VPS optimization, Docker containers, and runtime memory limits.',
    'System Performance'       => 'Benchmarking TTFB, Caddy caching, and PHP-FPM worker tuning.',
    'E-Commerce Operations'    => 'WooCommerce performance scaling, order processing, and payment webhooks.',
    'Vibe Coding & Automation' => 'AI agent coding workflows, deterministic test suites, and rapid prototyping.',
    'Modern Web Architecture'  => '12-Factor principles, Bedrock structure, and micro-footprint design.',
];

$cat_ids = [];
foreach ($categories as $name => $desc) {
    $term = get_term_by('name', $name, 'category');
    if (!$term) {
        $created = wp_insert_term($name, 'category', ['description' => $desc]);
        if (!is_wp_error($created)) {
            $cat_ids[] = $created['term_id'];
        }
    } else {
        $cat_ids[] = $term->term_id;
    }
}

// 3. Generate 100 Posts
echo "\n[3/4] Generating 100 rich posts with featured images...\n";
$titles_matrix = [
    'Optimizing PHP 8.3 FPM Process Management for Low-RAM VPS Environments',
    'How Caddy Inverted Static Caching Cuts TTFB to Under 80ms',
    'Dual-Engine Architecture: Switching Between MariaDB and SQLite in Seconds',
    'Hardening WordPress Security by Eliminating XML-RPC and Direct File Modifications',
    'Scaling WooCommerce Without Expanding Server Resources on Budget Hardware',
    'Zero-Inbound Reverse Tunnels: Securing Remote VPS Deployments without Open Ports',
    'Managing Persistent Docker Named Volumes for WordPress Uploads and Sessions',
    'Deterministic CI/CD Pipelines for Bedrock-Based WordPress Deployments',
    'Benchmarking Database Query Latency: MariaDB 10.11 Micro-Footprint Configurations',
    'Automating Payment Verification with VietQR and Webhook Callbacks in PHP',
    'Why Bedrock 12-Factor Structure Prevents Production Web Shell Vulnerabilities',
    'Building Micro-SaaS Applications with Headless WordPress and Next.js',
    'Tuning Linux Kernel OOM Killer Limits to Guard MariaDB in Constrained Environments',
    'The 155MB Idle RAM WordPress Stack: Real-World Memory Profile Breakdown',
    'Configuring Opcache Interned Strings and Accelerated Files for Rapid Execution',
];

$post_count = 100;
for ($i = 1; $i <= $post_count; $i++) {
    $base_title = $titles_matrix[($i - 1) % count($titles_matrix)];
    $suffix_num = str_pad($i, 3, '0', STR_PAD_LEFT);
    $title = "{$base_title} (Vol. {$suffix_num})";

    $assigned_cat = !empty($cat_ids) ? [$cat_ids[$i % count($cat_ids)]] : [1];
    $thumb_id = !empty($attachment_ids) ? $attachment_ids[$i % count($attachment_ids)] : null;
    $img_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';

    $days_ago = rand(1, 60);
    $post_date = date('Y-m-d H:i:s', time() - ($days_ago * 86400) + ($i * 120));

    $content = <<<HTML
<p>In modern web infrastructure, running production applications with predictable memory limits is an essential engineering discipline. This entry analyzes execution latency, process lifecycle, and caching efficiency under simulated traffic conditions.</p>

<!-- wp:heading -->
<h2>Core Performance Architecture</h2>
<!-- /wp:heading -->

<p>By enforcing an on-demand worker model and fine-tuning MariaDB buffer pool sizes, system stability remains uncompromised even under spike scenarios. The container architecture guarantees deterministic startup times and clean isolation between compute and storage layers.</p>

HTML;

    if ($img_url) {
        $content .= <<<HTML
<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large">
    <img src="{$img_url}" alt="{$title}" />
    <figcaption>Technical benchmark visualization and deployment metrics for iteration #{$i}.</figcaption>
</figure>
<!-- /wp:image -->

HTML;
    }

    $content .= <<<HTML
<!-- wp:heading -->
<h2>Measured Telemetry Data</h2>
<!-- /wp:heading -->

<ul>
    <li><strong>Runtime Environment:</strong> PHP 8.3.33 FPM / Alpine Linux</li>
    <li><strong>Web Engine:</strong> Caddy 2.x Reverse Proxy</li>
    <li><strong>Average TTFB:</strong> ~98ms - 105ms</li>
    <li><strong>Active Worker Memory:</strong> 48.9 MiB</li>
    <li><strong>Buffer Pool Footprint:</strong> 32 MiB strict allocation</li>
</ul>

<p>Maintaining strict limits avoids memory fragmentation and ensures the host operating system retains ample cache buffer for disk I/O operations.</p>
HTML;

    $post_id = wp_insert_post([
        'post_title'    => $title,
        'post_content'  => $content,
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_category' => $assigned_cat,
        'post_date'     => $post_date,
        'post_date_gmt' => $post_date,
        'post_type'     => 'post',
    ]);

    if ($post_id && !is_wp_error($post_id)) {
        if ($thumb_id) {
            set_post_thumbnail($post_id, $thumb_id);
        }
        if ($i % 20 === 0 || $i === $post_count) {
            echo "  - Generated {$i}/{$post_count} posts (latest ID: {$post_id})\n";
        }
    }
}

// 4. Create Demo Showcase Page & WooCommerce Products
echo "\n[4/4] Creating Demo Showcase Page & WooCommerce Products...\n";

// Demo Page
$demo_thumb = !empty($attachment_ids) ? $attachment_ids[0] : null;
$demo_img = $demo_thumb ? wp_get_attachment_url($demo_thumb) : '';

$demo_page_content = <<<HTML
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size"><strong>Welcome to iz-wp-lite: The 155MB Micro-Footprint WordPress Stack</strong></p>
<!-- /wp:paragraph -->

<p>This demo environment demonstrates a production-hardened WordPress deployment built upon Roots Bedrock architecture, Caddy 2.x reverse proxy, and MariaDB 10.11 with low-RAM telemetry tuning.</p>

HTML;

if ($demo_img) {
    $demo_page_content .= <<<HTML
<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large">
    <img src="{$demo_img}" alt="iz-wp-lite Infrastructure Overview" />
    <figcaption>Ultra-lightweight WordPress architecture running seamlessly on a 512MB VPS.</figcaption>
</figure>
<!-- /wp:image -->

HTML;
}

$demo_page_content .= <<<HTML
<!-- wp:heading -->
<h2>Key System Highlights</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
    <li><strong>Bedrock 12-Factor Isolation:</strong> WordPress core is segregated in <code>web/wp</code>, and web secrets are isolated in <code>.env</code> outside the public web root.</li>
    <li><strong>Dual-Engine Database Switch:</strong> Seamless transition between MariaDB (production compatibility) and SQLite (instant zero-service testing) via a single environment flag.</li>
    <li><strong>Automated WooCommerce Ready:</strong> Configured with Tier 2 resource boundaries supporting online stores without system crashes.</li>
    <li><strong>Caddy Web Server:</strong> Native gzip/zstd compression, static asset headers, and built-in network blocking of <code>xmlrpc.php</code>.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Explore Our Demo Catalog</h2>
<!-- /wp:heading -->

<p>Visit the <a href="/shop/">WooCommerce Store Catalog</a> to test product listing, cart additions, and checkout flows.</p>
HTML;

$demo_page_id = wp_insert_post([
    'post_title'   => 'Demo Showcase: High Performance WordPress',
    'post_name'    => 'demo-showcase',
    'post_content' => $demo_page_content,
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_author'  => 1,
]);

if ($demo_thumb && $demo_page_id) {
    set_post_thumbnail($demo_page_id, $demo_thumb);
}
echo "  - Created Demo Showcase Page (ID: {$demo_page_id})\n";

// WooCommerce Sample Products
if (class_exists('WC_Product_Simple')) {
    echo "  - Seeding 6 WooCommerce sample products...\n";
    $products = [
        ['name' => 'Cloud Micro Server (Tier 1)', 'price' => 149000, 'sku' => 'SRV-TIER1', 'desc' => '512MB RAM dedicated micro VPS profile for personal portfolios.'],
        ['name' => 'High-Speed NVMe Storage Pack (20GB)', 'price' => 79000, 'sku' => 'STR-20GB', 'desc' => 'Ultra-fast NVMe storage expansion for media and persistent volumes.'],
        ['name' => 'AI Agent Automation Node', 'price' => 299000, 'sku' => 'AGT-NODE1', 'desc' => 'Zero-inbound autonomous agent runner with deterministic execution.'],
        ['name' => 'Pro SSL Gateway & Edge Cache', 'price' => 99000, 'sku' => 'NET-CADDY', 'desc' => 'Global reverse proxy configuration with micro-caching.'],
        ['name' => 'Developer Diagnostic Toolkit', 'price' => 120000, 'sku' => 'DEV-TOOL', 'desc' => 'Comprehensive CLI and profiling extensions for high-traffic environments.'],
        ['name' => 'Managed MariaDB Cluster Access', 'price' => 399000, 'sku' => 'DB-CLUSTER', 'desc' => 'Managed database instance with automated daily backup snapshotting.'],
    ];

    foreach ($products as $idx => $p) {
        $product = new WC_Product_Simple();
        $product->set_name($p['name']);
        $product->set_regular_price($p['price']);
        $product->set_sku($p['sku']);
        $product->set_description($p['desc']);
        $product->set_short_description($p['desc']);
        $product->set_status('publish');
        if (!empty($attachment_ids)) {
            $product->set_image_id($attachment_ids[$idx % count($attachment_ids)]);
        }
        $p_id = $product->save();
        echo "    -> Created Product: {$p['name']} (ID: {$p_id})\n";
    }

    // Ensure store is publicly visible (disable Coming Soon mode)
    update_option('woocommerce_coming_soon', 'no');
    echo "  - Disabled WooCommerce coming_soon mode (store is public).\n";
}

echo "\n=== SEEDING COMPLETED SUCCESSFULLY ===\n";
