<?php
/**
 * Single Tour Template for iZ Tour Theme.
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

$tour_id         = get_the_ID();
$tour_code       = (string) get_post_meta($tour_id, '_tour_code', true) ?: ('TOUR-' . $tour_id);
$duration        = (string) get_post_meta($tour_id, '_tour_duration', true) ?: '3 Ngày 2 Đêm';
$departure_city  = (string) get_post_meta($tour_id, '_tour_departure_city', true) ?: 'TP. Hồ Chí Minh';
$transportation  = (string) get_post_meta($tour_id, '_tour_transportation', true) ?: 'Máy bay / Ô tô du lịch đời mới';
$price_adult     = (int) get_post_meta($tour_id, '_tour_price_adult', true);
$price_child     = (int) get_post_meta($tour_id, '_tour_price_child', true);
$price_infant    = (int) get_post_meta($tour_id, '_tour_price_infant', true);
$price_single    = (int) get_post_meta($tour_id, '_tour_price_single_sup', true);
$departure_dates = iz_get_tour_departures($tour_id);
$itinerary_days  = iz_get_tour_itinerary($tour_id);

// If prices child/infant not explicitly set, calculate business defaults
if ($price_child === 0 && $price_adult > 0) {
    $price_child = (int) round($price_adult * 0.75);
}
if ($price_infant === 0 && $price_adult > 0) {
    $price_infant = (int) round($price_adult * 0.1);
}

// Destination terms
$dest_terms = get_the_terms($tour_id, 'tour_destination');
$dest_name  = !empty($dest_terms) && !is_wp_error($dest_terms) ? reset($dest_terms)->name : 'Du lịch trọn gói';

// Booking page URL
$booking_page_url = home_url('/dat-tour/');
?>

<?php iz_breadcrumbs(); ?>

<main id="primary" class="site-main iz-single-tour">
    <div class="iz-container">
        <!-- Tour Header Summary -->
        <div class="iz-tour-header-block">
            <div class="iz-tour-badges-row">
                <span class="iz-badge-hot">TOUR BÁN CHẠY</span>
                <span class="iz-badge-code">MÃ: <?php echo esc_html($tour_code); ?></span>
            </div>

            <h1 class="iz-tour-h1"><?php the_title(); ?></h1>

            <div class="iz-tour-meta-bar">
                <div class="iz-meta-pill">
                    <?php echo iz_render_stars(5.0); ?>
                    <strong>5.0 / 5.0</strong>
                    <span class="iz-review-count">(100% đánh giá hài lòng)</span>
                </div>
                <div class="iz-meta-pill">
                    <svg viewBox="0 0 20 20" width="15" height="15" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    <span>Thời gian: <strong><?php echo esc_html($duration); ?></strong></span>
                </div>
                <div class="iz-meta-pill">
                    <svg viewBox="0 0 20 20" width="15" height="15" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    <span>Khởi hành: <strong><?php echo esc_html($departure_city); ?></strong></span>
                </div>
                <div class="iz-meta-pill">
                    <svg viewBox="0 0 20 20" width="15" height="15" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                    <span>Phương tiện: <strong><?php echo esc_html($transportation); ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Tour Gallery -->
        <div class="iz-tour-gallery-wrap">
            <div class="iz-gallery-main">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('iz-tour-banner', ['class' => 'iz-gallery-hero-img', 'alt' => get_the_title()]); ?>
                <?php else : ?>
                    <div class="iz-card-placeholder" style="height: 440px;">
                        <span>GONATOUR - TRẢI NGHIỆM ĐỈNH CAO</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2-Column Split: Content Left (70%) vs Sticky Booking Right (30%) -->
        <div class="iz-tour-content-grid">
            <!-- Left Column: Tour Details -->
            <div class="iz-tour-main-content">
                <!-- 1. Highlights Section -->
                <section class="iz-detail-box">
                    <h2 class="iz-box-title">
                        <svg viewBox="0 0 20 20" width="20" height="20" fill="var(--iz-brand-red)"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                        Điểm Nhấn Nổi Bật Của Chuyến Đi
                    </h2>
                    <ul class="iz-highlights-list">
                        <li>Trải nghiệm dịch vụ lữ hành trọn gói chất lượng cao cùng đội ngũ hướng dẫn viên giàu kinh nghiệm.</li>
                        <li>Lưu trú tại hệ thống khách sạn / resort tiêu chuẩn từ 3 - 5 sao tiện nghi, trung tâm.</li>
                        <li>Thực đơn phong phú, thưởng thức đặc sản ẩm thực địa phương trứ danh tại các nhà hàng uy tín.</li>
                        <li>Vé tham quan các danh lam thắng cảnh nổi tiếng nhất theo đúng lịch trình đã cam kết.</li>
                        <li>Bảo hiểm du lịch toàn diện với mức bồi thường tối đa lên đến 100.000.000 đ/vụ.</li>
                    </ul>
                </section>

                <!-- 2. Departure Dates & Pricing Table -->
                <section class="iz-detail-box">
                    <h2 class="iz-box-title">
                        <svg viewBox="0 0 20 20" width="20" height="20" fill="var(--iz-brand-red)"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                        Lịch Khởi Hành Sắp Tới & Tình Trạng Chỗ
                    </h2>
                    <div class="iz-table-responsive">
                        <table class="iz-departures-table">
                            <thead>
                                <tr>
                                    <th>Ngày khởi hành</th>
                                    <th>Mã tour</th>
                                    <th>Giá người lớn</th>
                                    <th>Giá trẻ em</th>
                                    <th>Tình trạng</th>
                                    <th>Chọn ngày</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($departure_dates)) : ?>
                                    <?php foreach ($departure_dates as $idx => $d) : ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($d); ?></strong></td>
                                            <td><code><?php echo esc_html($tour_code); ?></code></td>
                                            <td class="iz-price-col"><?php echo esc_html(iz_format_price($price_adult)); ?></td>
                                            <td><?php echo esc_html(iz_format_price($price_child)); ?></td>
                                            <td><span class="iz-stock-badge in-stock">Còn chỗ</span></td>
                                            <td>
                                                <button type="button" class="iz-select-date-btn" onclick="izPickDepartureDate('<?php echo esc_js($d); ?>')">
                                                    Chọn
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td><strong>Khởi hành Thứ 6 hàng tuần</strong></td>
                                        <td><code><?php echo esc_html($tour_code); ?></code></td>
                                        <td class="iz-price-col"><?php echo esc_html(iz_format_price($price_adult)); ?></td>
                                        <td><?php echo esc_html(iz_format_price($price_child)); ?></td>
                                        <td><span class="iz-stock-badge in-stock">Còn chỗ</span></td>
                                        <td>
                                            <button type="button" class="iz-select-date-btn" onclick="izPickDepartureDate('Thứ 6 hàng tuần')">Chọn</button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- 3. Structured Itinerary Accordion -->
                <section class="iz-detail-box">
                    <h2 class="iz-box-title">
                        <svg viewBox="0 0 20 20" width="20" height="20" fill="var(--iz-brand-red)"><path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.553.894l4 2A1 1 0 007 17V4.414l-3.293-3.293a1 1 0 00-1.414 0zM17.447 3.106A1 1 0 0016 4v12.586l3.293 3.293A1 1 0 0021 19V9a1 1 0 00-.553-.894l-3-2z" clip-rule="evenodd"/></svg>
                        Lịch Trình Chi Tiết
                    </h2>

                    <div class="iz-itinerary-accordion" id="iz-itinerary-accordion">
                        <?php if (!empty($itinerary_days)) : ?>
                            <?php foreach ($itinerary_days as $idx => $day) : ?>
                                <div class="iz-acc-item <?php echo $idx === 0 ? 'is-open' : ''; ?>">
                                    <button type="button" class="iz-acc-trigger">
                                        <div class="iz-acc-day-badge">Ngày <?php echo esc_html((string) ($day['day'] ?? ($idx + 1))); ?></div>
                                        <div class="iz-acc-title"><?php echo esc_html($day['title'] ?? ''); ?></div>
                                        <span class="iz-acc-icon"></span>
                                    </button>
                                    <div class="iz-acc-content">
                                        <div class="iz-acc-inner">
                                            <div class="iz-acc-meals">
                                                <span>🍽️ Bữa ăn: Sáng, Trưa, Tối theo chương trình</span>
                                                <span>🏨 Khách sạn: Tiêu chuẩn 3-4 sao trung tâm</span>
                                            </div>
                                            <div class="iz-acc-desc">
                                                <?php echo nl2br(esc_html($day['desc'] ?? '')); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <!-- Fallback default itinerary -->
                            <div class="iz-acc-item is-open">
                                <button type="button" class="iz-acc-trigger">
                                    <div class="iz-acc-day-badge">Ngày 1</div>
                                    <div class="iz-acc-title">Đón Khách - Di Chuyển Đến Điểm Tham Quan - Nhận Phòng Khách Sạn</div>
                                    <span class="iz-acc-icon"></span>
                                </button>
                                <div class="iz-acc-content">
                                    <div class="iz-acc-inner">
                                        <div class="iz-acc-meals"><span>🍽️ Bữa ăn: Trưa, Tối</span><span>🏨 Khách sạn: 4 sao trung tâm</span></div>
                                        <div class="iz-acc-desc">
                                            Xe và hướng dẫn viên đón đoàn tại điểm hẹn, khởi hành đi tour. Đoàn dùng bữa trưa tại nhà hàng địa phương với các món đặc sản hấp dẫn. Buổi chiều đoàn tham quan danh thắng và check-in khách sạn nghỉ ngơi.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="iz-acc-item">
                                <button type="button" class="iz-acc-trigger">
                                    <div class="iz-acc-day-badge">Ngày 2</div>
                                    <div class="iz-acc-title">Khám Phá Trọn Vẹn Danh Thắng - Trải Nghiệm Văn Hóa Ẩm Thực</div>
                                    <span class="iz-acc-icon"></span>
                                </button>
                                <div class="iz-acc-content">
                                    <div class="iz-acc-inner">
                                        <div class="iz-acc-meals"><span>🍽️ Bữa ăn: Sáng buffet, Trưa, Tối</span><span>🏨 Khách sạn: 4 sao trung tâm</span></div>
                                        <div class="iz-acc-desc">
                                            Quý khách dùng điểm tâm buffet tại khách sạn. Đoàn bắt đầu hành trình khám phá các điểm tham quan biểu tượng, tự do chụp ảnh check-in và mua sắm quà lưu niệm địa phương.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="iz-acc-item">
                                <button type="button" class="iz-acc-trigger">
                                    <div class="iz-acc-day-badge">Ngày 3</div>
                                    <div class="iz-acc-title">Mua Sắm Đặc Sản - Tạm Biệt & Trở Về Điểm Đón Ban Đầu</div>
                                    <span class="iz-acc-icon"></span>
                                </button>
                                <div class="iz-acc-content">
                                    <div class="iz-acc-inner">
                                        <div class="iz-acc-meals"><span>🍽️ Bữa ăn: Sáng buffet, Trưa</span></div>
                                        <div class="iz-acc-desc">
                                            Đoàn làm thủ tục trả phòng khách sạn, dùng bữa trưa. Xe đưa đoàn về lại điểm đón ban đầu. Hướng dẫn viên chia tay và hẹn gặp lại quý khách trong các hành trình tiếp theo.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- 4. Tour Policies (Included vs Excluded) -->
                <section class="iz-detail-box">
                    <h2 class="iz-box-title">
                        <svg viewBox="0 0 20 20" width="20" height="20" fill="var(--iz-brand-red)"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        Chính Sách & Điều Kiện Tour
                    </h2>

                    <div class="iz-policy-comparison">
                        <!-- Included -->
                        <div class="iz-policy-card is-included">
                            <h3 class="iz-policy-header">
                                <span class="iz-policy-icon">✓</span>
                                GIÁ TOUR BAO GỒM
                            </h3>
                            <ul class="iz-policy-list">
                                <li>Phương tiện vận chuyển đời mới, máy lạnh suốt tuyến.</li>
                                <li>Khách sạn tiêu chuẩn theo chương trình (2 khách/phòng, lẻ nam/nữ ngủ phòng 3).</li>
                                <li>Các bữa ăn chất lượng theo thực đơn phong phú.</li>
                                <li>Vé vào cổng các điểm tham quan có trong lịch trình.</li>
                                <li>Hướng dẫn viên nhiệt tình, tận tâm theo suốt hành trình.</li>
                                <li>Bảo hiểm du lịch mức trách nhiệm tối đa 100.000.000 đ.</li>
                                <li>Nước suối 1 chai 500ml/khách/ngày và nón du lịch Gonatour.</li>
                            </ul>
                        </div>

                        <!-- Excluded -->
                        <div class="iz-policy-card is-excluded">
                            <h3 class="iz-policy-header">
                                <span class="iz-policy-icon">✕</span>
                                GIÁ TOUR CHƯA BAO GỒM
                            </h3>
                            <ul class="iz-policy-list">
                                <li>Chi phí cá nhân: giặt ủi, điện thoại, thức uống ngoài chương trình.</li>
                                <li>Vé tham quan ngoài các điểm quy định trong lịch trình.</li>
                                <li>Phụ thu phòng đơn: <?php echo esc_html(iz_format_price($price_single ?: 1200000)); ?> (nếu có yêu cầu ngủ riêng).</li>
                                <li>Thuế GTGT (VAT) 8% - 10% nếu yêu cầu xuất hóa đơn đỏ.</li>
                                <li>Tiền bồi dưỡng (Tip) cho hướng dẫn viên và lái xe.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Child Policy -->
                    <div class="iz-child-policy">
                        <h4>Quy định giá vé trẻ em:</h4>
                        <ul>
                            <li><strong>Dưới 5 tuổi:</strong> Miễn phí giá tour (Gia đình tự túc chi phí phát sinh nếu có). Hai người lớn chỉ kèm 1 trẻ em miễn phí.</li>
                            <li><strong>Từ 5 đến 11 tuổi:</strong> Tính <?php echo esc_html(iz_format_price($price_child)); ?> (Bao gồm suất ăn, vé tham quan, ghế ngồi trên xe, ngủ chung giường cùng bố mẹ).</li>
                            <li><strong>Từ 12 tuổi trở lên:</strong> Tính bằng giá vé người lớn, hưởng đầy đủ tiêu chuẩn dịch vụ.</li>
                        </ul>
                    </div>
                </section>

                <!-- 5. Editorial Content / Additional Notes -->
                <?php if (get_the_content()) : ?>
                    <section class="iz-detail-box">
                        <h2 class="iz-box-title">Thông Tin Bổ Sung</h2>
                        <div class="iz-entry-content">
                            <?php the_content(); ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <!-- Right Column: Sticky Realtime Booking Card -->
            <aside class="iz-tour-sidebar">
                <div class="iz-sticky-booking-card" id="iz-booking-card">
                    <div class="iz-booking-card-head">
                        <span class="iz-booking-label">Giá trọn gói từ:</span>
                        <div class="iz-booking-price-line">
                            <strong class="iz-booking-price-num" id="iz-unit-price" data-adult-price="<?php echo esc_attr((string) $price_adult); ?>" data-child-price="<?php echo esc_attr((string) $price_child); ?>" data-infant-price="<?php echo esc_attr((string) $price_infant); ?>">
                                <?php echo esc_html(iz_format_price($price_adult)); ?>
                            </strong>
                            <span class="iz-booking-per">/ khách</span>
                        </div>
                    </div>

                    <form id="iz-booking-calculator-form" method="get" action="<?php echo esc_url($booking_page_url); ?>">
                        <input type="hidden" name="tour_id" value="<?php echo esc_attr((string) $tour_id); ?>" />

                        <!-- Pick Departure Date -->
                        <div class="iz-booking-field">
                            <label for="booking-departure-date">
                                <strong>1. Chọn Ngày Khởi Hành</strong> <span class="iz-req">*</span>
                            </label>
                            <select id="booking-departure-date" name="departure_date" required>
                                <option value="">-- Vui lòng chọn ngày đi --</option>
                                <?php if (!empty($departure_dates)) : ?>
                                    <?php foreach ($departure_dates as $d) : ?>
                                        <option value="<?php echo esc_attr($d); ?>"><?php echo esc_html($d); ?></option>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <option value="Thứ 6 hàng tuần" selected>Thứ 6 hàng tuần</option>
                                    <option value="Thứ 7 hàng tuần">Thứ 7 hàng tuần</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Realtime Guest Counters -->
                        <div class="iz-booking-field">
                            <label><strong>2. Số Lượng Khách</strong></label>

                            <!-- Adults Counter -->
                            <div class="iz-guest-counter-row">
                                <div class="iz-guest-info">
                                    <strong>Người lớn</strong>
                                    <small>&ge; 12 tuổi (<?php echo esc_html(iz_format_price($price_adult)); ?>)</small>
                                </div>
                                <div class="iz-qty-controls">
                                    <button type="button" class="iz-qty-btn iz-btn-minus" data-target="adult_count" aria-label="Giảm người lớn">-</button>
                                    <input type="number" id="adult_count" name="adults" value="1" min="1" max="50" readonly class="iz-qty-input" />
                                    <button type="button" class="iz-qty-btn iz-btn-plus" data-target="adult_count" aria-label="Tăng người lớn">+</button>
                                </div>
                            </div>

                            <!-- Children Counter -->
                            <div class="iz-guest-counter-row">
                                <div class="iz-guest-info">
                                    <strong>Trẻ em</strong>
                                    <small>5 - 11 tuổi (<?php echo esc_html(iz_format_price($price_child)); ?>)</small>
                                </div>
                                <div class="iz-qty-controls">
                                    <button type="button" class="iz-qty-btn iz-btn-minus" data-target="child_count" aria-label="Giảm trẻ em">-</button>
                                    <input type="number" id="child_count" name="children" value="0" min="0" max="50" readonly class="iz-qty-input" />
                                    <button type="button" class="iz-qty-btn iz-btn-plus" data-target="child_count" aria-label="Tăng trẻ em">+</button>
                                </div>
                            </div>

                            <!-- Infants Counter -->
                            <div class="iz-guest-counter-row">
                                <div class="iz-guest-info">
                                    <strong>Em bé</strong>
                                    <small>&lt; 5 tuổi (<?php echo esc_html(iz_format_price($price_infant)); ?>)</small>
                                </div>
                                <div class="iz-qty-controls">
                                    <button type="button" class="iz-qty-btn iz-btn-minus" data-target="infant_count" aria-label="Giảm em bé">-</button>
                                    <input type="number" id="infant_count" name="infants" value="0" min="0" max="20" readonly class="iz-qty-input" />
                                    <button type="button" class="iz-qty-btn iz-btn-plus" data-target="infant_count" aria-label="Tăng em bé">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Realtime Total Price Box -->
                        <div class="iz-booking-total-box">
                            <span class="iz-total-label">Tổng Chi Phí Dự Tính:</span>
                            <strong class="iz-total-amount" id="iz-calculated-total"><?php echo esc_html(iz_format_price($price_adult)); ?></strong>
                        </div>

                        <!-- CTA Book Now Button -->
                        <button type="submit" class="iz-booking-cta-btn">
                            <svg viewBox="0 0 20 20" width="18" height="18" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                            <span>ĐẶT TOUR NGAY</span>
                        </button>
                    </form>

                    <!-- Hotline Support -->
                    <div class="iz-booking-hotline-box">
                        <span>Hoặc liên hệ tư vấn nhanh qua tổng đài:</span>
                        <a href="tel:0784849849" class="iz-booking-hotline-link">
                            <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 4V3z"/></svg>
                            0784 849 849 (24/7)
                        </a>
                    </div>

                    <!-- Trust Points -->
                    <div class="iz-booking-trust-points">
                        <div class="iz-trust-item">
                            <span class="iz-point-icon">🛡️</span>
                            <span>Bảo hiểm du lịch quốc tế</span>
                        </div>
                        <div class="iz-trust-item">
                            <span class="iz-point-icon">⚡</span>
                            <span>Thanh toán VietQR tự động tức thì</span>
                        </div>
                        <div class="iz-trust-item">
                            <span class="iz-point-icon">⭐</span>
                            <span>Không phụ phí ẩn, giá cam kết</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<script>
function izPickDepartureDate(dateStr) {
    const sel = document.getElementById('booking-departure-date');
    if (sel) {
        let found = false;
        for (let i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value === dateStr) {
                sel.selectedIndex = i;
                found = true;
                break;
            }
        }
        if (!found) {
            const opt = document.createElement('option');
            opt.value = dateStr;
            opt.textContent = dateStr;
            opt.selected = true;
            sel.appendChild(opt);
        }
        sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        sel.focus();
    }
}
</script>

<?php
get_footer();
