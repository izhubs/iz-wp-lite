<?php
/**
 * 404 Not Found Template.
 *
 * PHP version 8.1+
 *
 * @package IZTourTheme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main iz-404-page">
    <div class="iz-container">
        <div class="iz-404-content">
            <span class="iz-404-code">404</span>
            <h1 class="iz-404-title"><?php esc_html_e('Rất tiếc! Trang bạn tìm kiếm không tồn tại', 'iz-tour-theme'); ?></h1>
            <p class="iz-404-desc"><?php esc_html_e('Đường dẫn có thể đã bị thay đổi hoặc không còn khả dụng trên hệ thống Gonatour.', 'iz-tour-theme'); ?></p>

            <div class="iz-404-search">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/tours/')); ?>">
                    <input type="search" name="search" placeholder="Nhập điểm đến bạn muốn tìm (VD: Phú Quốc, Đà Nẵng...)" required />
                    <button type="submit">Tìm Tour</button>
                </form>
            </div>

            <div class="iz-404-actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="iz-btn-primary"><?php esc_html_e('Về Trang Chủ', 'iz-tour-theme'); ?></a>
                <a href="<?php echo esc_url(home_url('/tours/')); ?>" class="iz-btn-outline"><?php esc_html_e('Xem Danh Sách Tour', 'iz-tour-theme'); ?></a>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
