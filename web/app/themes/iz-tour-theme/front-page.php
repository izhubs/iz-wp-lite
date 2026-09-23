<?php
/**
 * Front page template for iZ Tour Theme.
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

// Query Flash Sale tours
$flash_query = new \WP_Query([
    'post_type'      => 'tour',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

// Query Domestic tours
$domestic_query = new \WP_Query([
    'post_type'      => 'tour',
    'post_status'    => 'publish',
    'posts_per_page' => 8,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

// Query International tours
$international_query = new \WP_Query([
    'post_type'      => 'tour',
    'post_status'    => 'publish',
    'posts_per_page' => 8,
    'orderby'        => 'rand',
]);

// Query Blog / Travel Guide posts
$blog_query = new \WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<main id="primary" class="site-main iz-front-page">
    <!-- SECTION 1: HERO BANNER & 4-FIELD SEARCH BOX -->
    <section class="iz-hero-section">
        <div class="iz-hero-slider">
            <div class="iz-hero-slide is-active">
                <div class="iz-hero-overlay"></div>
                <div class="iz-container iz-hero-content">
                    <span class="iz-hero-badge">KHÁM PHÁ MÙA HÈ 2026 CÙNG GONATOUR</span>
                    <h1 class="iz-hero-title">Trải Nghiệm Kỳ Nghỉ Đẳng Cấp & Đáng Nhớ</h1>
                    <p class="iz-hero-subtitle">Hơn 500+ tour du lịch trọn gói trong và ngoài nước với dịch vụ chuẩn quốc tế, giá cam kết tốt nhất thị trường.</p>
                </div>
            </div>
        </div>

        <!-- 4-Field Search Box -->
        <div class="iz-container iz-search-box-container">
            <div class="iz-search-card">
                <div class="iz-search-tabs">
                    <button type="button" class="iz-search-tab is-active" data-search-type="all">
                        <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                        Tìm Tour Trọn Gói
                    </button>
                    <button type="button" class="iz-search-tab" data-search-type="combo">
                        <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                        Combo Vé & Khách Sạn
                    </button>
                    <button type="button" class="iz-search-tab" data-search-type="teambuilding">
                        <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/></svg>
                        Tour Doanh Nghiệp
                    </button>
                </div>

                <form method="get" action="<?php echo esc_url(home_url('/tours/')); ?>" class="iz-quick-search-form">
                    <div class="iz-form-grid">
                        <!-- Field 1: Departure -->
                        <div class="iz-form-field">
                            <label for="search-departure">
                                <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                Nơi Khởi Hành
                            </label>
                            <select id="search-departure" name="departure">
                                <option value="">Tất cả điểm đi</option>
                                <option value="TP.HCM" selected>TP. Hồ Chí Minh</option>
                                <option value="Hà Nội">Hà Nội</option>
                                <option value="Đà Nẵng">Đà Nẵng</option>
                                <option value="Cần Thơ">Cần Thơ</option>
                                <option value="Hải Phòng">Hải Phòng</option>
                            </select>
                        </div>

                        <!-- Field 2: Destination -->
                        <div class="iz-form-field">
                            <label for="search-destination">
                                <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor"><path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.553.894l4 2A1 1 0 007 17V4.414l-3.293-3.293a1 1 0 00-1.414 0zM17.447 3.106A1 1 0 0016 4v12.586l3.293 3.293A1 1 0 0021 19V9a1 1 0 00-.553-.894l-3-2z" clip-rule="evenodd"/></svg>
                                Điểm Đến Mong Muốn
                            </label>
                            <input type="text" id="search-destination" name="search" placeholder="Nhập điểm đến: Phú Quốc, Đà Nẵng, Hàn Quốc..." />
                        </div>

                        <!-- Field 3: Month -->
                        <div class="iz-form-field">
                            <label for="search-month">
                                <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                Thời Gian Khởi Hành
                            </label>
                            <select id="search-month" name="departure_month">
                                <option value="">Tất cả các tháng</option>
                                <option value="2026-10">Tháng 10 / 2026</option>
                                <option value="2026-11">Tháng 11 / 2026</option>
                                <option value="2026-12">Tháng 12 / 2026</option>
                                <option value="2027-01">Tết Dương Lịch 2027</option>
                                <option value="2027-02">Tết Nguyên Đán 2027</option>
                            </select>
                        </div>

                        <!-- Field 4: Tour Type -->
                        <div class="iz-form-field">
                            <label for="search-type">
                                <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/></svg>
                                Dòng Tour Lựa Chọn
                            </label>
                            <select id="search-type" name="tour_type">
                                <option value="">Tất cả loại tour</option>
                                <option value="trong-nuoc">Tour Trong Nước</option>
                                <option value="quoc-te">Tour Nước Ngoài</option>
                                <option value="combo">Combo Tiết Kiệm</option>
                                <option value="teambuilding">Teambuilding Doanh Nghiệp</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="iz-form-action">
                            <button type="submit" class="iz-search-btn">
                                <svg viewBox="0 0 20 20" width="18" height="18" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
                                <span>TÌM TOUR NGAY</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- SECTION 2: FLASH SALE / TOUR GIỜ CHÓT -->
    <section class="iz-section iz-flash-section">
        <div class="iz-container">
            <div class="iz-flash-header">
                <div class="iz-flash-title-wrap">
                    <span class="iz-flash-fire">🔥</span>
                    <h2 class="iz-flash-title">TOUR GIỜ CHÓT - GIÁ SỐC</h2>
                </div>
                <div class="iz-countdown-box" id="iz-flash-countdown" data-end-hours="12">
                    <span class="iz-cd-label">Kết thúc sau:</span>
                    <div class="iz-cd-digits">
                        <span class="iz-cd-num" id="cd-hours">08</span><span class="iz-cd-colon">:</span>
                        <span class="iz-cd-num" id="cd-minutes">45</span><span class="iz-cd-colon">:</span>
                        <span class="iz-cd-num" id="cd-seconds">20</span>
                    </div>
                </div>
            </div>

            <div class="iz-tour-grid iz-grid-4">
                <?php
                if ($flash_query->have_posts()) {
                    while ($flash_query->have_posts()) {
                        $flash_query->the_post();
                        iz_render_tour_card(get_the_ID(), ['flash_sale' => true, 'seats_left' => wp_rand(1, 4)]);
                    }
                    wp_reset_postdata();
                } else {
                    // Fallback visual cards when database has no seed posts yet
                    for ($i = 1; $i <= 4; $i++) {
                        $mock_titles = [
                            1 => 'Tour Đà Nẵng - Bà Nà Hills - Cầu Vàng - Hội An 3N2Đ',
                            2 => 'Tour Thái Lan: Bangkok - Pattaya - Đảo Coral 5N4Đ Bay Vietnam Airlines',
                            3 => 'Tour Phú Quốc: Khám Phá Grand World - Tặng Vé Cáp Treo Hòn Thơm 3N2Đ',
                            4 => 'Tour Singapore - Malaysia Trọn Gói 5N4Đ Khách Sạn 4 Sao Trung Tâm',
                        ];
                        $mock_prices = [1 => 3890000, 2 => 5990000, 3 => 3490000, 4 => 8990000];
                        ?>
                        <article class="iz-tour-card is-flash-sale">
                            <div class="iz-card-media">
                                <a href="<?php echo esc_url(home_url('/tours/')); ?>">
                                    <div class="iz-card-placeholder"><span>Gonatour Flash Sale</span></div>
                                </a>
                                <span class="iz-card-badge iz-badge-sale">-25%</span>
                                <div class="iz-card-flash-ribbon"><span>⚡ GIỜ CHÓT</span></div>
                                <div class="iz-card-meta-overlay">
                                    <span class="iz-meta-item">3N2Đ</span>
                                    <span class="iz-meta-item">TP.HCM</span>
                                </div>
                            </div>
                            <div class="iz-card-body">
                                <div class="iz-card-code">Mã tour: <strong>FS-0<?php echo esc_html((string) $i); ?></strong></div>
                                <h3 class="iz-card-title"><a href="<?php echo esc_url(home_url('/tours/')); ?>"><?php echo esc_html($mock_titles[$i]); ?></a></h3>
                                <div class="iz-card-rating-row"><?php echo iz_render_stars(5.0); ?><span class="iz-card-reviews">(100% hài lòng)</span></div>
                                <div class="iz-card-specs">
                                    <div class="iz-spec-line"><span class="iz-spec-label">Khởi hành:</span><span class="iz-spec-value">Thứ 6 hàng tuần</span></div>
                                    <div class="iz-spec-line"><span class="iz-spec-label">Phương tiện:</span><span class="iz-spec-value">Máy bay khứ hồi</span></div>
                                </div>
                                <div class="iz-flash-seats">
                                    <div class="iz-seats-bar"><div class="iz-seats-fill" style="width: 80%;"></div></div>
                                    <span class="iz-seats-text">Chỉ còn <strong>2</strong> chỗ cuối</span>
                                </div>
                                <div class="iz-card-footer">
                                    <div class="iz-price-block">
                                        <div class="iz-old-price"><?php echo esc_html(iz_format_price($mock_prices[$i] * 1.3)); ?></div>
                                        <div class="iz-current-price"><?php echo esc_html(iz_format_price($mock_prices[$i])); ?></div>
                                    </div>
                                    <a href="<?php echo esc_url(home_url('/tours/')); ?>" class="iz-card-btn"><span>Đặt Ngay</span></a>
                                </div>
                            </div>
                        </article>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- SECTION 3: KHỐI TOUR TRONG NƯỚC NỔI BẬT VỚI TABS -->
    <section class="iz-section iz-domestic-section">
        <div class="iz-container">
            <div class="iz-section-head">
                <div>
                    <h2 class="iz-section-title">TOUR TRONG NƯỚC NỔI BẬT</h2>
                    <p class="iz-section-subtitle">Khám phá dải đất hình chữ S với các bãi biển thơ mộng và di sản văn hóa hào hùng.</p>
                </div>
                <a href="<?php echo esc_url(home_url('/tours/?tour_type=trong-nuoc')); ?>" class="iz-view-more-link">
                    Xem tất cả tour trong nước
                    <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </a>
            </div>

            <!-- Fast Switch Tabs -->
            <div class="iz-tabs-nav" data-tab-group="domestic">
                <button type="button" class="iz-tab-btn is-active" data-filter="all">Tất Cả</button>
                <button type="button" class="iz-tab-btn" data-filter="mien-bac">Miền Bắc</button>
                <button type="button" class="iz-tab-btn" data-filter="mien-trung">Miền Trung</button>
                <button type="button" class="iz-tab-btn" data-filter="mien-nam">Miền Nam & Sông Nước</button>
                <button type="button" class="iz-tab-btn" data-filter="phu-quoc">Phú Quốc</button>
                <button type="button" class="iz-tab-btn" data-filter="tay-nguyen">Tây Nguyên</button>
            </div>

            <!-- Tours Grid -->
            <div class="iz-tour-grid iz-grid-4">
                <?php
                if ($domestic_query->have_posts()) {
                    while ($domestic_query->have_posts()) {
                        $domestic_query->the_post();
                        iz_render_tour_card(get_the_ID());
                    }
                    wp_reset_postdata();
                } else {
                    $mock_domestic = [
                        ['title' => 'Tour Đà Nẵng - Hội An - Bà Nà 3N2Đ', 'price' => 3990000, 'duration' => '3N2Đ', 'dept' => 'TP.HCM'],
                        ['title' => 'Tour Hà Nội - Hạ Long - Ninh Bình 4N3Đ', 'price' => 5490000, 'duration' => '4N3Đ', 'dept' => 'Hà Nội'],
                        ['title' => 'Tour Phú Quốc Thiên Đường Biển Đảo 3N2Đ', 'price' => 3290000, 'duration' => '3N2Đ', 'dept' => 'TP.HCM'],
                        ['title' => 'Tour Sa Pa - Cát Cát - Fansipan Huyền Thoại 3N2Đ', 'price' => 3190000, 'duration' => '3N2Đ', 'dept' => 'Hà Nội'],
                        ['title' => 'Tour Quy Nhơn - Phú Yên - Kỳ Co Eo Gió 4N3Đ', 'price' => 4590000, 'duration' => '4N3Đ', 'dept' => 'TP.HCM'],
                        ['title' => 'Tour Nha Trang - VinWonders - Du Thuyền Ngắm Hoàng Hôn 3N2Đ', 'price' => 3690000, 'duration' => '3N2Đ', 'dept' => 'TP.HCM'],
                        ['title' => 'Tour Đà Lạt Thành Phố Sương Mù 3N3Đ Xe Giường Nằm', 'price' => 2490000, 'duration' => '3N3Đ', 'dept' => 'TP.HCM'],
                        ['title' => 'Tour Miền Tây 4 Tỉnh: Mỹ Tho - Cần Thơ - Sóc Trăng - Bạc Liêu 2N1Đ', 'price' => 1890000, 'duration' => '2N1Đ', 'dept' => 'TP.HCM'],
                    ];

                    foreach ($mock_domestic as $idx => $m) {
                        ?>
                        <article class="iz-tour-card">
                            <div class="iz-card-media">
                                <a href="<?php echo esc_url(home_url('/tours/')); ?>">
                                    <div class="iz-card-placeholder"><span>Du Lịch Việt Nam</span></div>
                                </a>
                                <span class="iz-card-badge iz-badge-hot">Bán Chạy</span>
                                <div class="iz-card-meta-overlay">
                                    <span class="iz-meta-item"><?php echo esc_html($m['duration']); ?></span>
                                    <span class="iz-meta-item"><?php echo esc_html($m['dept']); ?></span>
                                </div>
                            </div>
                            <div class="iz-card-body">
                                <div class="iz-card-code">Mã tour: <strong>VN-0<?php echo esc_html((string) ($idx + 1)); ?></strong></div>
                                <h3 class="iz-card-title"><a href="<?php echo esc_url(home_url('/tours/')); ?>"><?php echo esc_html($m['title']); ?></a></h3>
                                <div class="iz-card-rating-row"><?php echo iz_render_stars(5.0); ?><span class="iz-card-reviews">(100% hài lòng)</span></div>
                                <div class="iz-card-specs">
                                    <div class="iz-spec-line"><span class="iz-spec-label">Khởi hành:</span><span class="iz-spec-value">Hàng tuần</span></div>
                                    <div class="iz-spec-line"><span class="iz-spec-label">Phương tiện:</span><span class="iz-spec-value">Máy bay / Ô tô du lịch</span></div>
                                </div>
                                <div class="iz-card-footer">
                                    <div class="iz-price-block">
                                        <div class="iz-price-label">Giá từ:</div>
                                        <div class="iz-current-price"><?php echo esc_html(iz_format_price($m['price'])); ?></div>
                                    </div>
                                    <a href="<?php echo esc_url(home_url('/tours/')); ?>" class="iz-card-btn"><span>Xem Tour</span></a>
                                </div>
                            </div>
                        </article>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- SECTION 4: KHỐI TOUR NƯỚC NGOÀI NỔI BẬT VỚI TABS -->
    <section class="iz-section iz-international-section">
        <div class="iz-container">
            <div class="iz-section-head">
                <div>
                    <h2 class="iz-section-title">TOUR NƯỚC NGOÀI - CHẠM VÀO THẾ GIỚI</h2>
                    <p class="iz-section-subtitle">Chương trình du lịch quốc tế trọn gói bảo hiểm, hỗ trợ visa 99% đỗ cao.</p>
                </div>
                <a href="<?php echo esc_url(home_url('/tours/?tour_type=quoc-te')); ?>" class="iz-view-more-link">
                    Xem tất cả tour quốc tế
                    <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </a>
            </div>

            <!-- Fast Switch Tabs -->
            <div class="iz-tabs-nav" data-tab-group="international">
                <button type="button" class="iz-tab-btn is-active" data-filter="all">Tất Cả</button>
                <button type="button" class="iz-tab-btn" data-filter="dna">Đông Nam Á</button>
                <button type="button" class="iz-tab-btn" data-filter="thai-lan">Thái Lan</button>
                <button type="button" class="iz-tab-btn" data-filter="singapore">Singapore</button>
                <button type="button" class="iz-tab-btn" data-filter="han-quoc">Hàn Quốc</button>
                <button type="button" class="iz-tab-btn" data-filter="nhat-ban">Nhật Bản</button>
                <button type="button" class="iz-tab-btn" data-filter="chau-au">Châu Âu</button>
            </div>

            <!-- Tours Grid -->
            <div class="iz-tour-grid iz-grid-4">
                <?php
                if ($international_query->have_posts()) {
                    while ($international_query->have_posts()) {
                        $international_query->the_post();
                        iz_render_tour_card(get_the_ID());
                    }
                    wp_reset_postdata();
                } else {
                    $mock_intl = [
                        ['title' => 'Tour Thái Lan: Bangkok - Pattaya 5N4Đ Khách Sạn 4 Sao', 'price' => 5990000, 'duration' => '5N4Đ'],
                        ['title' => 'Tour Singapore - Malaysia 5N4Đ Trọn Gói Bay Vietnam Airlines', 'price' => 8990000, 'duration' => '5N4Đ'],
                        ['title' => 'Tour Hàn Quốc: Seoul - Nami - Everland - Lotte World 5N4Đ', 'price' => 14990000, 'duration' => '5N4Đ'],
                        ['title' => 'Tour Nhật Bản Cung Đường Vàng: Tokyo - Phú Sĩ - Kyoto - Osaka 6N5Đ', 'price' => 28990000, 'duration' => '6N5Đ'],
                        ['title' => 'Tour Châu Âu 3 Nước: Pháp - Thụy Sĩ - Ý 10N9Đ Trọn Gói Visa', 'price' => 65900000, 'duration' => '10N9Đ'],
                        ['title' => 'Tour Đài Loan: Đài Bắc - Đài Trung - Cao Hùng 5N4Đ', 'price' => 11990000, 'duration' => '5N4Đ'],
                        ['title' => 'Tour Dubai - Abu Dhabi Xa Hoa Sa Mạc Safari 5N4Đ', 'price' => 26900000, 'duration' => '5N4Đ'],
                        ['title' => 'Tour Úc: Sydney - Melbourne Thưởng Ngoạn Mùa Thu Vàng 7N6Đ', 'price' => 45900000, 'duration' => '7N6Đ'],
                    ];

                    foreach ($mock_intl as $idx => $m) {
                        ?>
                        <article class="iz-tour-card">
                            <div class="iz-card-media">
                                <a href="<?php echo esc_url(home_url('/tours/')); ?>">
                                    <div class="iz-card-placeholder"><span>Du Lịch Quốc Tế</span></div>
                                </a>
                                <span class="iz-card-badge iz-badge-code">MÃ: QT-0<?php echo esc_html((string) ($idx + 1)); ?></span>
                                <div class="iz-card-meta-overlay">
                                    <span class="iz-meta-item"><?php echo esc_html($m['duration']); ?></span>
                                    <span class="iz-meta-item">TP.HCM</span>
                                </div>
                            </div>
                            <div class="iz-card-body">
                                <div class="iz-card-code">Khởi hành: <strong>Hàng tháng</strong></div>
                                <h3 class="iz-card-title"><a href="<?php echo esc_url(home_url('/tours/')); ?>"><?php echo esc_html($m['title']); ?></a></h3>
                                <div class="iz-card-rating-row"><?php echo iz_render_stars(5.0); ?><span class="iz-card-reviews">(100% hài lòng)</span></div>
                                <div class="iz-card-specs">
                                    <div class="iz-spec-line"><span class="iz-spec-label">Hàng không:</span><span class="iz-spec-value">Hàng không quốc gia 4-5 sao</span></div>
                                    <div class="iz-spec-line"><span class="iz-spec-label">Visa:</span><span class="iz-spec-value">Hỗ trợ trọn gói A-Z</span></div>
                                </div>
                                <div class="iz-card-footer">
                                    <div class="iz-price-block">
                                        <div class="iz-price-label">Giá từ:</div>
                                        <div class="iz-current-price"><?php echo esc_html(iz_format_price($m['price'])); ?></div>
                                    </div>
                                    <a href="<?php echo esc_url(home_url('/tours/')); ?>" class="iz-card-btn"><span>Xem Tour</span></a>
                                </div>
                            </div>
                        </article>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- SECTION 5: DỊCH VỤ DU LỊCH 4 BOX -->
    <section class="iz-section iz-services-section">
        <div class="iz-container">
            <div class="iz-section-head iz-text-center">
                <h2 class="iz-section-title">DỊCH VỤ DU LỊCH CHUYÊN NGHIỆP</h2>
                <p class="iz-section-subtitle">Giải pháp toàn diện đáp ứng mọi nhu cầu di chuyển, nghỉ dưỡng và sự kiện của bạn.</p>
            </div>

            <div class="iz-services-grid">
                <!-- Service 1 -->
                <div class="iz-service-box">
                    <div class="iz-service-icon">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
                            <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                        </svg>
                    </div>
                    <h3 class="iz-service-title">Dịch Vụ Visa Chuyên Nghiệp</h3>
                    <p class="iz-service-desc">Tư vấn visa Mỹ, Schengen Châu Âu, Nhật Bản, Hàn Quốc, Úc với tỷ lệ đạt trên 99%. Xử lý hồ sơ khó và khẩn cấp.</p>
                    <a href="<?php echo esc_url(home_url('/dich-vu/dich-vu-visa/')); ?>" class="iz-service-link">
                        Tìm hiểu thêm
                        <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </a>
                </div>

                <!-- Service 2 -->
                <div class="iz-service-box">
                    <div class="iz-service-icon">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
                            <path d="M7 13c1.66 0 3-1.34 3-3S8.66 7 7 7s-3 1.34-3 3 1.34 3 3 3zm12-6h-8v7H3V5H1v15h2v-3h18v3h2v-9c0-2.21-1.79-4-4-4z"/>
                        </svg>
                    </div>
                    <h3 class="iz-service-title">Combo Vé Máy Bay & Khách Sạn</h3>
                    <p class="iz-service-desc">Tự do trải nghiệm theo lịch trình cá nhân với các combo vé máy bay khứ hồi và resort 4-5 sao đẳng cấp, tiết kiệm đến 35%.</p>
                    <a href="<?php echo esc_url(home_url('/dich-vu/khach-san-combo/')); ?>" class="iz-service-link">
                        Tìm hiểu thêm
                        <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </a>
                </div>

                <!-- Service 3 -->
                <div class="iz-service-box">
                    <div class="iz-service-icon">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
                            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.04 3H5.81l1.04-3zM19 17H5v-4.66l.12-.34h13.77l.11.34V17z"/>
                        </svg>
                    </div>
                    <h3 class="iz-service-title">Thuê Xe Du Lịch 4 - 45 Chỗ</h3>
                    <p class="iz-service-desc">Đội ngũ xe đời mới, sạch sẽ, lái xe am hiểu cung đường, an toàn và đúng hẹn. Phục vụ công tác, đưa đón sân bay và hội nghị.</p>
                    <a href="<?php echo esc_url(home_url('/dich-vu/thue-xe-du-lich/')); ?>" class="iz-service-link">
                        Tìm hiểu thêm
                        <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </a>
                </div>

                <!-- Service 4 -->
                <div class="iz-service-box">
                    <div class="iz-service-icon">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="currentColor">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                    </div>
                    <h3 class="iz-service-title">Tổ Chức Teambuilding & Gala</h3>
                    <p class="iz-service-desc">Kịch bản sáng tạo mang đậm bản sắc doanh nghiệp, hệ thống âm thanh ánh sáng hiện đại và MC chuyên nghiệp gắn kết đội ngũ.</p>
                    <a href="<?php echo esc_url(home_url('/dich-vu/teambuilding/')); ?>" class="iz-service-link">
                        Tìm hiểu thêm
                        <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 6: CẨM NANG DU LỊCH & TIN TỨC (GRID 4 BÀI) -->
    <section class="iz-section iz-news-section">
        <div class="iz-container">
            <div class="iz-section-head">
                <div>
                    <h2 class="iz-section-title">CẨM NANG DU LỊCH & TIN TỨC</h2>
                    <p class="iz-section-subtitle">Kinh nghiệm bỏ túi, review ẩm thực và cẩm nang hữu ích từ các chuyên gia lữ hành.</p>
                </div>
                <a href="<?php echo esc_url(home_url('/cam-nang-du-lich/')); ?>" class="iz-view-more-link">
                    Xem tất cả bài viết
                    <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </a>
            </div>

            <div class="iz-news-grid">
                <?php
                if ($blog_query->have_posts()) {
                    while ($blog_query->have_posts()) {
                        $blog_query->the_post();
                        ?>
                        <article class="iz-news-card">
                            <div class="iz-news-thumb">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium_large', ['class' => 'iz-news-img']); ?>
                                    <?php else : ?>
                                        <div class="iz-card-placeholder"><span>Cẩm Nang Du Lịch</span></div>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="iz-news-body">
                                <div class="iz-news-date">
                                    <svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                    <?php echo esc_html(get_the_date('d/m/Y')); ?>
                                </div>
                                <h3 class="iz-news-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <p class="iz-news-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '...')); ?></p>
                            </div>
                        </article>
                        <?php
                    }
                    wp_reset_postdata();
                } else {
                    $mock_news = [
                        ['title' => 'Kinh nghiệm du lịch Phú Quốc tự túc 3 ngày 2 đêm từ A đến Z chi tiết nhất', 'date' => '15/09/2026'],
                        ['title' => 'Top 10 địa điểm check-in không thể bỏ lỡ khi ghé thăm Đà Nẵng mùa lễ hội', 'date' => '12/09/2026'],
                        ['title' => 'Thủ tục xin visa du lịch Nhật Bản mới nhất: Hồ sơ, chi phí và lưu ý phỏng vấn', 'date' => '08/09/2026'],
                        ['title' => 'Cẩm nang đi tour Thái Lan trọn gói cho người mới đi lần đầu cần chuẩn bị gì?', 'date' => '02/09/2026'],
                    ];

                    foreach ($mock_news as $m) {
                        ?>
                        <article class="iz-news-card">
                            <div class="iz-news-thumb">
                                <a href="<?php echo esc_url(home_url('/cam-nang-du-lich/')); ?>">
                                    <div class="iz-card-placeholder"><span>Cẩm Nang Gonatour</span></div>
                                </a>
                            </div>
                            <div class="iz-news-body">
                                <div class="iz-news-date">
                                    <svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                    <?php echo esc_html($m['date']); ?>
                                </div>
                                <h3 class="iz-news-title">
                                    <a href="<?php echo esc_url(home_url('/cam-nang-du-lich/')); ?>"><?php echo esc_html($m['title']); ?></a>
                                </h3>
                                <p class="iz-news-excerpt">Tổng hợp các kinh nghiệm thực tế về lựa chọn điểm đến, lưu trú khách sạn và mẹo tiết kiệm chi phí cho chuyến du lịch hoàn hảo.</p>
                            </div>
                        </article>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- SECTION 7: BÁO CHÍ & KHÁCH HÀNG TIN TƯỞNG -->
    <section class="iz-section iz-press-section">
        <div class="iz-container">
            <div class="iz-section-head iz-text-center">
                <h2 class="iz-section-title">BÁO CHÍ & ĐỐI TÁC TIN CẬY</h2>
                <p class="iz-section-subtitle">Được đồng hành và ghi nhận bởi các cơ quan truyền thông và thương hiệu vận chuyển hàng đầu.</p>
            </div>

            <div class="iz-press-logos">
                <div class="iz-press-item"><span>Đài Truyền Hình HTV1</span></div>
                <div class="iz-press-item"><span>Báo Doanh Nghiệp & Hội Nhập</span></div>
                <div class="iz-press-item"><span>Báo Đất Việt</span></div>
                <div class="iz-press-item"><span>Vietnam Airlines</span></div>
                <div class="iz-press-item"><span>Vietjet Air</span></div>
                <div class="iz-press-item"><span>Bamboo Airways</span></div>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
