<?php
/**
 * Integration Test for iz-r2-media and iz-r2-media-pro inside iz-wp-lite.
 * Run via: wp eval-file /var/www/html/scripts/test_r2_integration.php --allow-root --path=/var/www/html/web/wp
 */

echo "========================================================================\n";
echo "  iz-r2-media & iz-r2-media-pro Integration Test Suite\n";
echo "  WordPress: " . get_bloginfo('version') . " | PHP: " . PHP_VERSION . "\n";
echo "========================================================================\n\n";

$passed = 0;
$failed = 0;

function assert_test(bool $condition, string $title, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$title}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$title}" . ($details ? " - {$details}" : '') . "\n";
    }
}

// 1. Verify Core Classes & Hooks
echo "▶ 1. Class & Hook Registration Verification\n";
assert_test(class_exists('\IzHubs\R2Media\Core\Plugin'), 'Core Plugin class exists');
assert_test(class_exists('\IzHubs\R2Media\Core\Config'), 'Core Config class exists');
assert_test(class_exists('\IzHubs\R2Media\Core\SigV4Signer'), 'Core SigV4Signer class exists');
assert_test(class_exists('\IzHubs\R2Media\Core\R2Client'), 'Core R2Client class exists');
assert_test(class_exists('\IzHubs\R2MediaPro\Plugin'), 'Pro Plugin class exists');
assert_test(class_exists('\IzHubs\R2MediaPro\EdgeResizing'), 'Pro EdgeResizing class exists');
assert_test(class_exists('\IzHubs\R2MediaPro\WooCommerce\ProtectedVault'), 'Pro WooCommerce ProtectedVault class exists');
assert_test(class_exists('\IzHubs\R2MediaPro\Cli\SyncCommand'), 'Pro SyncCommand class exists');

// 2. Test SigV4 Signer
echo "\n▶ 2. SigV4 Signer Cryptographic Vector Verification\n";
$access_key = 'AKIAIOSFODNN7EXAMPLE';
$secret_key = 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY';

$headers = \IzHubs\R2Media\Core\SigV4Signer::signRequest(
    'GET',
    'examplebucket.r2.cloudflarestorage.com',
    '/test.txt',
    [],
    '',
    $access_key,
    $secret_key,
    'auto',
    1369353600 // Fixed timestamp: 2013-05-24T00:00:00Z
);

assert_test(isset($headers['Authorization']), 'Authorization header generated');
assert_test(str_contains($headers['Authorization'], 'AWS4-HMAC-SHA256'), 'Uses AWS4-HMAC-SHA256 algorithm');
assert_test(str_contains($headers['Authorization'], 'Credential=AKIAIOSFODNN7EXAMPLE/20130524/auto/s3/aws4_request'), 'Credential scope matches RFC specification');

// 3. Test Presigned URL Generation
echo "\n▶ 3. Presigned URL Generation (WooCommerce Protected Vault)\n";
$presigned = \IzHubs\R2Media\Core\SigV4Signer::createPresignedUrl(
    'GET',
    'examplebucket.r2.cloudflarestorage.com',
    '/downloads/ebook.pdf',
    $access_key,
    $secret_key,
    'auto',
    120, // 120s TTL
    ['response-content-disposition' => 'attachment; filename="ebook.pdf"'],
    1369353600
);
assert_test(str_contains($presigned, 'X-Amz-Signature='), 'Presigned URL contains signature');
assert_test(str_contains($presigned, 'X-Amz-Expires=120'), 'Presigned URL contains TTL');
assert_test(str_contains($presigned, 'response-content-disposition='), 'Presigned URL preserves Content-Disposition');

// 4. Test Configuration Resolution (12-Factor)
echo "\n▶ 4. 12-Factor Configuration Engine\n";
assert_test(defined('\IzHubs\R2Media\Core\Config::OPTION_NAME'), 'Option name constant is defined');
$defaults = \IzHubs\R2Media\Core\Config::get_all();
assert_test(is_array($defaults), 'Config returns array of settings');
assert_test(array_key_exists('keep_local', $defaults), 'Default keep_local exists');

// 5. Test Edge Resizing URL Generation
echo "\n▶ 5. Pro Cloudflare Edge Resizing URL Engine\n";
$edge_resizing = new \IzHubs\R2MediaPro\EdgeResizing();
$sample_r2_url = 'https://cdn.izdigi.com/2026/09/sample-photo.jpg';

// Test thumbnail transform (e.g. 150x150 crop)
$transformed_thumb = $edge_resizing->transform_url($sample_r2_url, 'thumbnail');
assert_test(str_contains($transformed_thumb, '/cdn-cgi/image/'), 'Edge resizing injected /cdn-cgi/image/ path');
assert_test(str_contains($transformed_thumb, 'width=150'), 'Thumbnail width 150 is specified');
assert_test(str_contains($transformed_thumb, 'format=auto'), 'WebP/AVIF format=auto is specified');

// Test medium transform (e.g. 300x300)
$transformed_medium = $edge_resizing->transform_url($sample_r2_url, 'medium');
assert_test(str_contains($transformed_medium, 'width=300'), 'Medium width 300 is specified');

// Test full size bypass (master image should not be resized)
$transformed_full = $edge_resizing->transform_url($sample_r2_url, 'full');
assert_test($transformed_full === $sample_r2_url, 'Full master image bypasses edge resizing (zero transformation)');

// 6. Test Admin Settings Page Output
echo "\n▶ 6. Admin Settings Page Rendering\n";
ob_start();
$settings_page = new \IzHubs\R2Media\Admin\SettingsPage();
$settings_page->render();
$admin_html = ob_get_clean();

assert_test(!empty($admin_html), 'Settings page HTML rendered successfully');
assert_test(str_contains($admin_html, 'Cloudflare R2'), 'Settings page contains Cloudflare R2 title');
assert_test(str_contains($admin_html, 'Account ID'), 'Settings page contains Account ID input field');
assert_test(str_contains($admin_html, 'Bucket Name'), 'Settings page contains Bucket Name input field');

// 7. Test Media Library Attachments Integration
echo "\n▶ 7. Media Library Integration with Existing Media\n";
$sample_attachment = get_posts([
    'post_type'      => 'attachment',
    'posts_per_page' => 1,
    'post_status'    => 'inherit',
]);
if (!empty($sample_attachment)) {
    $att = $sample_attachment[0];
    $url = wp_get_attachment_url($att->ID);
    assert_test(!empty($url), "Attachment ID {$att->ID} resolved URL: " . substr($url, 0, 50) . "...");
    
    // Check metadata
    $meta = wp_get_attachment_metadata($att->ID);
    assert_test(is_array($meta) && isset($meta['width']), "Attachment ID {$att->ID} has valid metadata dimensions");
} else {
    echo "  [SKIP] No attachments found to test url rewrite\n";
}

echo "\n========================================================================\n";
echo "  TEST SUMMARY: {$passed} PASSED | {$failed} FAILED\n";
echo "========================================================================\n";

if ($failed > 0) {
    exit(1);
}
