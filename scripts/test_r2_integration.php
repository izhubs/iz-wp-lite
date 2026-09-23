<?php
/**
 * Integration Test for iz-r2-media and iz-r2-media-pro inside iz-wp-lite.
 * Run via: wp eval-file /var/www/html/scripts/test_r2_integration.php --allow-root --path=/var/www/html/web/wp
 */

echo "========================================================================\n";
echo "  iz-r2-media & iz-r2-media-pro Integration Test Suite\n";
echo "  WordPress: " . get_bloginfo('version') . " | PHP: " . PHP_VERSION . "\n";
echo "========================================================================\n\n";

$GLOBALS['passed'] = 0;
$GLOBALS['failed'] = 0;

function assert_test(bool $condition, string $title, string $details = ''): void {
    if ($condition) {
        $GLOBALS['passed']++;
        echo "  [PASS] {$title}\n";
    } else {
        $GLOBALS['failed']++;
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
$defaults = \IzHubs\R2Media\Core\Config::getAll();
assert_test(is_array($defaults), 'Config returns array of settings');
assert_test(array_key_exists('keep_local', $defaults), 'Default keep_local exists');
assert_test(array_key_exists('account_id', $defaults), 'Default account_id exists');
assert_test(array_key_exists('bucket', $defaults), 'Default bucket exists');

// 5. Test Media Library Attachments Integration & Edge Resizing
echo "\n▶ 5. Media Library & Pro Edge Resizing Engine\n";
$sample_attachment = get_posts([
    'post_type'      => 'attachment',
    'posts_per_page' => 1,
    'post_status'    => 'inherit',
]);

if (!empty($sample_attachment)) {
    $att = $sample_attachment[0];
    $att_id = $att->ID;
    $url = wp_get_attachment_url($att_id);
    assert_test(!empty($url), "Attachment ID {$att_id} resolved URL");
    
    // Test Edge Resizing on actual media attachment
    $edge = \IzHubs\R2MediaPro\EdgeResizing::init();
    
    // Simulate R2 metadata for this attachment
    update_post_meta($att_id, '_iz_r2_key', '2026/09/sample.jpg');
    
    $cdn_base = 'https://cdn.example.com/2026/09/sample.jpg';
    $thumb_url = $edge->transform_url($cdn_base, $att_id, 'thumbnail', '2026/09/sample.jpg');
    assert_test(str_contains($thumb_url, '/cdn-cgi/image/'), 'Edge resizing produces /cdn-cgi/image/ endpoint');
    assert_test(str_contains($thumb_url, 'width='), 'Edge resizing specifies width attribute');
    assert_test(str_contains($thumb_url, 'format=auto'), 'Edge resizing specifies format=auto attribute');

    // Test Full size bypass (should not resize master)
    $full_url = $edge->transform_url($cdn_base, $att_id, 'full', '2026/09/sample.jpg');
    assert_test(!str_contains($full_url, '/cdn-cgi/image/'), 'Full master image bypasses /cdn-cgi/image/ endpoint');

    // Clean up simulated meta
    delete_post_meta($att_id, '_iz_r2_key');
} else {
    echo "  [SKIP] No attachments found\n";
}

// 6. Test Admin Settings Page Output
echo "\n▶ 6. Admin Settings Page Rendering\n";
wp_set_current_user(1);
ob_start();
$settings_page = new \IzHubs\R2Media\Admin\SettingsPage();
$settings_page->render();
$admin_html = ob_get_clean();

assert_test(!empty($admin_html), 'Settings page HTML rendered successfully');
assert_test(str_contains($admin_html, 'Cloudflare R2'), 'Settings page contains Cloudflare R2 title');
assert_test(str_contains($admin_html, 'Account ID'), 'Settings page contains Account ID input field');
assert_test(str_contains($admin_html, 'Bucket Name'), 'Settings page contains Bucket Name input field');
assert_test(str_contains($admin_html, 'Access Key ID'), 'Settings page contains Access Key ID field');

echo "\n========================================================================\n";
echo "  TEST SUMMARY: {$GLOBALS['passed']} PASSED | {$GLOBALS['failed']} FAILED\n";
echo "========================================================================\n";

if ($GLOBALS['failed'] > 0) {
    exit(1);
}
