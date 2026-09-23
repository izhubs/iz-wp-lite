<?php
/**
 * Header template for iZ Tour Theme.
 *
 * PHP version 8.1+
 *
 * @package IZTourTheme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="iz-site-header" id="site-header">
    <!-- Top Bar -->
    <div class="iz-topbar">
        <div class="iz-container iz-topbar-inner">
            <div class="iz-topbar-left">
                <span class="iz-topbar-item">
                    <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 4V3z"/></svg>
                    Hotline: <a href="tel:0784849849"><strong>0784 849 849</strong></a>
                </span>
                <span class="iz-topbar-item iz-hide-mobile">
                    <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    Email: <a href="mailto:info@gonatour.vn">info@gonatour.vn</a>
                </span>
                <span class="iz-topbar-item iz-hide-tablet">
                    <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    08:00 - 18:00 (Thứ 2 - Thứ 7)
                </span>
            </div>

            <div class="iz-topbar-right">
                <a href="<?php echo esc_url(home_url('/tra-cuu-don/')); ?>" class="iz-topbar-link">
                    <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                    Tra Cứu Đơn Đặt
                </a>
                <span class="iz-topbar-sep">|</span>
                <a href="<?php echo esc_url(home_url('/khuyen-mai/')); ?>" class="iz-topbar-link iz-highlight-sale">
                    ⚡ Khuyến Mãi Mới
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <div class="iz-main-header">
        <div class="iz-container iz-header-inner">
            <!-- Mobile Menu Trigger -->
            <button type="button" class="iz-mobile-toggle" id="iz-mobile-menu-btn" aria-label="<?php esc_attr_e('Mở menu', 'iz-tour-theme'); ?>">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- Brand Logo -->
            <div class="iz-brand">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="iz-logo-link" rel="home">
                    <span class="iz-logo-symbol">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
                            <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                        </svg>
                    </span>
                    <span class="iz-logo-text-group">
                        <strong class="iz-logo-main">GONATOUR</strong>
                        <small class="iz-logo-sub">Du Lịch & Nghỉ Dưỡng Trọn Gói</small>
                    </span>
                </a>
            </div>

            <!-- Quick Search Bar -->
            <div class="iz-header-search">
                <form role="search" method="get" class="iz-search-form" action="<?php echo esc_url(home_url('/tours/')); ?>">
                    <div class="iz-search-wrapper">
                        <input type="search" class="iz-search-input" placeholder="Tìm tour: Đà Nẵng, Phú Quốc, Thái Lan..." value="<?php echo esc_attr(get_search_query()); ?>" name="search" autocomplete="off" />
                        <button type="submit" class="iz-search-submit" aria-label="Tìm kiếm">
                            <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                            </svg>
                            <span>Tìm Tour</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Header Hotline Callout -->
            <div class="iz-header-hotline">
                <div class="iz-hotline-icon">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                        <path d="M20 15.5c-1.25 0-2.45-.2-3.57-.57a1.02 1.02 0 00-1.02.24l-2.2 2.2a15.045 15.045 0 01-6.59-6.59l2.2-2.21a.96.96 0 00.25-1A11.36 11.36 0 018.5 4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1 9.39 9.39 17 17 21 21 .55 0 1-.45 1-1v-3.5c0-.55-.45-1-1-1zM19 12h2a9 9 0 00-9-9v2a7 7 0 017 7zm-4 0h2a5 5 0 00-5-5v2a3 3 0 013 3z"/>
                    </svg>
                </div>
                <div class="iz-hotline-text">
                    <span class="iz-hotline-label">TƯ VẤN ĐẶT TOUR</span>
                    <a href="tel:0784849849" class="iz-hotline-number">0784 849 849</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Mega Navigation Menu -->
    <nav class="iz-primary-nav" aria-label="<?php esc_attr_e('Menu điều hướng chính', 'iz-tour-theme'); ?>">
        <div class="iz-container">
            <ul class="iz-menu-list">
                <li class="iz-menu-item <?php echo is_front_page() ? 'current-menu-item' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <svg viewBox="0 0 20 20" width="15" height="15" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                        Trang Chủ
                    </a>
                </li>

                <!-- Mega Menu: Tour Trong Nước -->
                <li class="iz-menu-item has-mega-menu">
                    <a href="<?php echo esc_url(home_url('/tours/?tour_type=trong-nuoc')); ?>">
                        Tour Trong Nước
                        <svg class="iz-chevron" viewBox="0 0 20 20" width="12" height="12" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </a>
                    <div class="iz-mega-dropdown">
                        <div class="iz-mega-grid">
                            <div class="iz-mega-col">
                                <h4 class="iz-mega-col-title">Miền Bắc</h4>
                                <ul class="iz-mega-sublist">
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/ha-noi/')); ?>">Tour Hà Nội</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/ha-long/')); ?>">Tour Hạ Long - Bái Tử Long</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/sa-pa/')); ?>">Tour Sa Pa - Fansipan</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/ninh-binh/')); ?>">Tour Ninh Bình - Tràng An</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/ha-giang/')); ?>">Tour Hà Giang Mùa Hoa</a></li>
                                </ul>
                            </div>
                            <div class="iz-mega-col">
                                <h4 class="iz-mega-col-title">Miền Trung</h4>
                                <ul class="iz-mega-sublist">
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/da-nang/')); ?>">Tour Đà Nẵng - Bà Nà Hills</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/hoi-an/')); ?>">Tour Phố Cổ Hội An</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/hue/')); ?>">Tour Cố Đô Huế</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/quy-nhon/')); ?>">Tour Quy Nhơn - Kỳ Co Eo Gió</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/nha-trang/')); ?>">Tour Biển Đảo Nha Trang</a></li>
                                </ul>
                            </div>
                            <div class="iz-mega-col">
                                <h4 class="iz-mega-col-title">Tây Nguyên & Miền Nam</h4>
                                <ul class="iz-mega-sublist">
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/da-lat/')); ?>">Tour Đà Lạt Thành Phố Ngàn Hoa</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/buon-ma-thuot/')); ?>">Tour Buôn Ma Thuột - Hồ Lắk</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/mang-den/')); ?>">Tour Măng Đen Kon Tum</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/mien-tay/')); ?>">Tour Miền Tây Sông Nước 4T</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/can-tho/')); ?>">Tour Cần Thơ Chợ Nổi Cái Răng</a></li>
                                </ul>
                            </div>
                            <div class="iz-mega-col">
                                <h4 class="iz-mega-col-title">Biển Đảo Thiên Đường</h4>
                                <ul class="iz-mega-sublist">
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/phu-quoc/')); ?>">Tour Phú Quốc 3N2Đ Siêu Rẻ</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/phu-quoc-grand-world/')); ?>">Tour Grand World - Cáp Treo</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/con-dao/')); ?>">Tour Côn Đảo Tâm Linh Viếng Mộ Cô Sáu</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/phu-quy/')); ?>">Tour Đảo Phú Quý Hoang Sơ</a></li>
                                </ul>
                                <div class="iz-mega-banner-box">
                                    <span class="iz-badge-hot">HOT</span>
                                    <p>Ưu đãi đặt sớm hè 2026 giảm ngay 500k/khách</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Mega Menu: Tour Nước Ngoài -->
                <li class="iz-menu-item has-mega-menu">
                    <a href="<?php echo esc_url(home_url('/tours/?tour_type=quoc-te')); ?>">
                        Tour Nước Ngoài
                        <svg class="iz-chevron" viewBox="0 0 20 20" width="12" height="12" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </a>
                    <div class="iz-mega-dropdown">
                        <div class="iz-mega-grid">
                            <div class="iz-mega-col">
                                <h4 class="iz-mega-col-title">Đông Nam Á</h4>
                                <ul class="iz-mega-sublist">
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/thai-lan/')); ?>">Tour Thái Lan (Bangkok - Pattaya)</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/phuket/')); ?>">Tour Phuket Thiên Đường Biển</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/singapore/')); ?>">Tour Singapore - Sentosa</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/singapore-malaysia/')); ?>">Tour Singapore - Malaysia 5N4Đ</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/bali/')); ?>">Tour Bali Indonesia Sống Ảo</a></li>
                                </ul>
                            </div>
                            <div class="iz-mega-col">
                                <h4 class="iz-mega-col-title">Đông Bắc Á</h4>
                                <ul class="iz-mega-sublist">
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/han-quoc/')); ?>">Tour Hàn Quốc (Seoul - Nami - Everland)</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/jeju/')); ?>">Tour Đảo Jeju Miễn Visa</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/nhat-ban/')); ?>">Tour Nhật Bản (Tokyo - Phú Sĩ - Kyoto)</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/dai-loan/')); ?>">Tour Đài Loan Trọn Gói</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/trung-quoc/')); ?>">Tour Trương Gia Giới - Phượng Hoàng Cổ Trấn</a></li>
                                </ul>
                            </div>
                            <div class="iz-mega-col">
                                <h4 class="iz-mega-col-title">Châu Âu & Úc</h4>
                                <ul class="iz-mega-sublist">
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/chau-au-tay-au/')); ?>">Tour Pháp - Thụy Sĩ - Ý 10N9Đ</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/chau-au-dong-au/')); ?>">Tour Đông Âu Cổ Kính</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/uc/')); ?>">Tour Úc (Sydney - Melbourne)</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/anh/')); ?>">Tour Vương Quốc Anh</a></li>
                                </ul>
                            </div>
                            <div class="iz-mega-col">
                                <h4 class="iz-mega-col-title">Châu Mỹ & Châu Phi</h4>
                                <ul class="iz-mega-sublist">
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/my-bo-tay/')); ?>">Tour Bờ Tây Nước Mỹ (Los Angeles - Vegas)</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/my-bo-dong/')); ?>">Tour Bờ Đông Nước Mỹ (New York - DC)</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/dubai/')); ?>">Tour Dubai - Abu Dhabi Xa Hoa</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/diem-den/ai-cap/')); ?>">Tour Ai Cập Huyền Bí Kim Tự Tháp</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Dropdown: Dịch Vụ Du Lịch -->
                <li class="iz-menu-item has-dropdown">
                    <a href="<?php echo esc_url(home_url('/dich-vu-du-lich/')); ?>">
                        Dịch Vụ Du Lịch
                        <svg class="iz-chevron" viewBox="0 0 20 20" width="12" height="12" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </a>
                    <ul class="iz-dropdown-sublist">
                        <li><a href="<?php echo esc_url(home_url('/dich-vu/dich-vu-visa/')); ?>">Dịch Vụ Visa Chuyên Nghiệp</a></li>
                        <li><a href="<?php echo esc_url(home_url('/dich-vu/khach-san-combo/')); ?>">Combo Vé Máy Bay & Khách Sạn</a></li>
                        <li><a href="<?php echo esc_url(home_url('/dich-vu/thue-xe-du-lich/')); ?>">Thuê Xe Du Lịch 4 - 45 Chỗ</a></li>
                        <li><a href="<?php echo esc_url(home_url('/dich-vu/teambuilding/')); ?>">Tổ Chức Teambuilding & Sự Kiện</a></li>
                    </ul>
                </li>

                <li class="iz-menu-item">
                    <a href="<?php echo esc_url(home_url('/cam-nang-du-lich/')); ?>">Cẩm Nang Du Lịch</a>
                </li>
                <li class="iz-menu-item">
                    <a href="<?php echo esc_url(home_url('/khuyen-mai/')); ?>">Khuyến Mãi</a>
                </li>
                <li class="iz-menu-item">
                    <a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên Hệ</a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<!-- Mobile Navigation Drawer -->
<div class="iz-drawer-backdrop" id="iz-drawer-overlay"></div>
<aside class="iz-mobile-drawer" id="iz-mobile-drawer" aria-label="<?php esc_attr_e('Menu trên thiết bị di động', 'iz-tour-theme'); ?>">
    <div class="iz-drawer-header">
        <div class="iz-drawer-brand">
            <strong>GONATOUR</strong>
        </div>
        <button type="button" class="iz-drawer-close" id="iz-drawer-close-btn" aria-label="Đóng menu">
            &times;
        </button>
    </div>

    <div class="iz-drawer-body">
        <form role="search" method="get" class="iz-drawer-search" action="<?php echo esc_url(home_url('/tours/')); ?>">
            <input type="search" placeholder="Tìm điểm đến hoặc tên tour..." name="search" />
            <button type="submit" aria-label="Tìm kiếm">
                <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
            </button>
        </form>

        <ul class="iz-drawer-nav">
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Trang Chủ</a></li>
            <li class="iz-drawer-accordion">
                <button type="button" class="iz-drawer-acc-btn">
                    <span>Tour Trong Nước</span>
                    <span class="iz-acc-icon">+</span>
                </button>
                <ul class="iz-drawer-acc-panel">
                    <li><a href="<?php echo esc_url(home_url('/diem-den/mien-bac/')); ?>">Tour Miền Bắc (Hà Nội, Hạ Long, Sa Pa)</a></li>
                    <li><a href="<?php echo esc_url(home_url('/diem-den/mien-trung/')); ?>">Tour Miền Trung (Đà Nẵng, Huế, Hội An)</a></li>
                    <li><a href="<?php echo esc_url(home_url('/diem-den/phu-quoc/')); ?>">Tour Phú Quốc 3N2Đ</a></li>
                    <li><a href="<?php echo esc_url(home_url('/diem-den/tay-nguyen/')); ?>">Tour Tây Nguyên - Đà Lạt</a></li>
                    <li><a href="<?php echo esc_url(home_url('/diem-den/con-dao/')); ?>">Tour Côn Đảo Tâm Linh</a></li>
                </ul>
            </li>
            <li class="iz-drawer-accordion">
                <button type="button" class="iz-drawer-acc-btn">
                    <span>Tour Nước Ngoài</span>
                    <span class="iz-acc-icon">+</span>
                </button>
                <ul class="iz-drawer-acc-panel">
                    <li><a href="<?php echo esc_url(home_url('/diem-den/thai-lan/')); ?>">Tour Thái Lan</a></li>
                    <li><a href="<?php echo esc_url(home_url('/diem-den/singapore/')); ?>">Tour Singapore - Malaysia</a></li>
                    <li><a href="<?php echo esc_url(home_url('/diem-den/han-quoc/')); ?>">Tour Hàn Quốc</a></li>
                    <li><a href="<?php echo esc_url(home_url('/diem-den/nhat-ban/')); ?>">Tour Nhật Bản</a></li>
                    <li><a href="<?php echo esc_url(home_url('/diem-den/chau-au/')); ?>">Tour Châu Âu Trọn Gói</a></li>
                </ul>
            </li>
            <li class="iz-drawer-accordion">
                <button type="button" class="iz-drawer-acc-btn">
                    <span>Dịch Vụ Du Lịch</span>
                    <span class="iz-acc-icon">+</span>
                </button>
                <ul class="iz-drawer-acc-panel">
                    <li><a href="<?php echo esc_url(home_url('/dich-vu/dich-vu-visa/')); ?>">Dịch Vụ Visa</a></li>
                    <li><a href="<?php echo esc_url(home_url('/dich-vu/khach-san-combo/')); ?>">Combo Vé & Khách Sạn</a></li>
                    <li><a href="<?php echo esc_url(home_url('/dich-vu/thue-xe-du-lich/')); ?>">Thuê Xe Du Lịch</a></li>
                    <li><a href="<?php echo esc_url(home_url('/dich-vu/teambuilding/')); ?>">Teambuilding Doanh Nghiệp</a></li>
                </ul>
            </li>
            <li><a href="<?php echo esc_url(home_url('/cam-nang-du-lich/')); ?>">Cẩm Nang Du Lịch</a></li>
            <li><a href="<?php echo esc_url(home_url('/khuyen-mai/')); ?>">Khuyến Mãi</a></li>
            <li><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên Hệ</a></li>
        </ul>
    </div>

    <div class="iz-drawer-footer">
        <div class="iz-drawer-phone">
            <span>Hotline 24/7:</span>
            <a href="tel:0784849849">0784 849 849</a>
        </div>
        <div class="iz-drawer-sub">
            Trụ sở: Tòa nhà Bitexco, Q.1, TP. Hồ Chí Minh
        </div>
    </div>
</aside>
