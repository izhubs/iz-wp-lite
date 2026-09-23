<?php
/**
 * Template Name: Trang Đặt Tour & Thanh Toán
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

// Extract input parameters from GET query if forwarded from single-tour
$tour_id        = isset($_GET['tour_id']) ? absint($_GET['tour_id']) : 0;
$departure_date = isset($_GET['departure_date']) ? sanitize_text_field(wp_unslash($_GET['departure_date'])) : '';
$adults_count   = isset($_GET['adults']) ? max(1, absint($_GET['adults'])) : 1;
$child_count    = isset($_GET['children']) ? max(0, absint($_GET['children'])) : 0;
$infant_count   = isset($_GET['infants']) ? max(0, absint($_GET['infants'])) : 0;

// Fetch tour details if tour_id is provided
$tour_title   = '';
$tour_code    = '';
$price_adult  = 0;
$price_child  = 0;
$price_infant = 0;
$thumb_url    = '';

if ($tour_id > 0) {
    $tour_post = get_post($tour_id);
    if ($tour_post instanceof \WP_Post && $tour_post->post_type === 'tour') {
        $tour_title   = $tour_post->post_title;
        $tour_code    = (string) get_post_meta($tour_id, '_tour_code', true);
        $price_adult  = (int) get_post_meta($tour_id, '_tour_price_adult', true);
        $price_child  = (int) get_post_meta($tour_id, '_tour_price_child', true);
        $price_infant = (int) get_post_meta($tour_id, '_tour_price_infant', true);
        $thumb_id     = get_post_thumbnail_id($tour_id);
        $thumb_url    = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : '';

        if ($price_child === 0 && $price_adult > 0) {
            $price_child = (int) round($price_adult * 0.75);
        }
        if ($price_infant === 0 && $price_adult > 0) {
            $price_infant = (int) round($price_adult * 0.1);
        }
    }
}

// Calculate initial total
$total_estimated = ($adults_count * $price_adult) + ($child_count * $price_child) + ($infant_count * $price_infant);
$total_guests    = $adults_count + $child_count + $infant_count;
?>

<?php iz_breadcrumbs(); ?>

<main id="primary" class="site-main iz-booking-page">
    <div class="iz-container">
        <header class="iz-booking-page-header">
            <h1 class="iz-booking-title">Xác Nhận Đặt Tour & Thanh Toán</h1>
            <p class="iz-booking-subtitle">Hoàn tất biểu mẫu thông tin khách hàng bên dưới để giữ chỗ nhanh chóng và nhận mã thanh toán VietQR tức thì.</p>
        </header>

        <!-- Success Notification Box (Populated dynamically via JavaScript upon REST API 201 response) -->
        <div id="iz-booking-success-screen" class="iz-booking-success-box" style="display: none;">
            <div class="iz-success-badge">
                <svg viewBox="0 0 24 24" width="36" height="36" fill="currentColor"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/></svg>
            </div>
            <h2 class="iz-success-title">ĐẶT TOUR THÀNH CÔNG!</h2>
            <p class="iz-success-msg">Mã đơn đặt tour của bạn đã được ghi nhận trên hệ thống Gonatour. Chuyên viên tư vấn sẽ liên hệ xác nhận trong vòng 15 phút.</p>

            <div class="iz-booking-summary-receipt">
                <div class="iz-receipt-line">
                    <span>Mã Đơn Đặt:</span>
                    <strong id="receipt-booking-code" class="iz-receipt-highlight">-</strong>
                </div>
                <div class="iz-receipt-line">
                    <span>Tour Đã Đặt:</span>
                    <strong id="receipt-tour-title">-</strong>
                </div>
                <div class="iz-receipt-line">
                    <span>Ngày Khởi Hành:</span>
                    <span id="receipt-departure-date">-</span>
                </div>
                <div class="iz-receipt-line">
                    <span>Số Khách:</span>
                    <span id="receipt-guests">-</span>
                </div>
                <div class="iz-receipt-line iz-receipt-total">
                    <span>Tổng Tiền Thanh Toán:</span>
                    <strong id="receipt-total-amount" class="iz-price-red">-</strong>
                </div>
            </div>

            <!-- VietQR Container -->
            <div id="iz-receipt-vietqr-wrap" class="iz-vietqr-pay-card" style="display: none;">
                <h3 class="iz-vietqr-title">Quét Mã VietQR Chuyển Khoản Tự Động</h3>
                <p class="iz-vietqr-desc">Mở ứng dụng ngân hàng bất kỳ (Vietcombank, MBBank, Techcombank, ACB, VPBank...) và quét mã bên dưới để thanh toán đúng số tiền và nội dung chuyển khoản:</p>
                <div class="iz-qr-image-box">
                    <img id="receipt-vietqr-img" src="" alt="Mã VietQR" />
                </div>
                <div class="iz-qr-transfer-info">
                    <p><strong>Ngân hàng:</strong> MBBank (Ngân hàng Quân Đội)</p>
                    <p><strong>Số tài khoản:</strong> <code>0784849849</code></p>
                    <p><strong>Chủ tài khoản:</strong> CONG TY CP DU LICH GONATOUR</p>
                    <p><strong>Nội dung chuyển:</strong> <code id="receipt-memo-code">-</code></p>
                </div>
            </div>

            <div class="iz-success-actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="iz-btn-primary">Quay Về Trang Chủ</a>
                <a href="tel:0784849849" class="iz-btn-outline">Gọi Tổng Đài Hỗ Trợ 0784 849 849</a>
            </div>
        </div>

        <!-- Booking Main Layout (Form + Summary Sidebar) -->
        <div id="iz-booking-main-layout" class="iz-booking-layout-grid">
            <!-- Left: Client Info Form -->
            <div class="iz-booking-form-wrap">
                <form id="iz-checkout-form" class="iz-checkout-form" novalidate>
                    <input type="hidden" name="tour_id" value="<?php echo esc_attr((string) $tour_id); ?>" />
                    <input type="hidden" name="departure_date" value="<?php echo esc_attr($departure_date); ?>" />
                    <input type="hidden" name="adult_count" id="form-adult-count" value="<?php echo esc_attr((string) $adults_count); ?>" />
                    <input type="hidden" name="child_count" id="form-child-count" value="<?php echo esc_attr((string) $child_count); ?>" />
                    <input type="hidden" name="infant_count" id="form-infant-count" value="<?php echo esc_attr((string) $infant_count); ?>" />

                    <!-- Section: Contact Information -->
                    <div class="iz-form-card">
                        <h2 class="iz-form-card-title">
                            <span class="iz-step-num">1</span>
                            Thông Tin Người Liên Hệ (Trưởng Đoàn)
                        </h2>
                        <div class="iz-form-grid-2">
                            <div class="iz-input-group">
                                <label for="customer_name">Họ và Tên <span class="iz-req">*</span></label>
                                <input type="text" id="customer_name" name="customer_name" required placeholder="VD: Nguyễn Văn An" />
                            </div>
                            <div class="iz-input-group">
                                <label for="customer_phone">Số Điện Thoại Di Động <span class="iz-req">*</span></label>
                                <input type="tel" id="customer_phone" name="customer_phone" required placeholder="VD: 0987654321" />
                            </div>
                        </div>

                        <div class="iz-form-grid-2">
                            <div class="iz-input-group">
                                <label for="customer_email">Địa Chỉ Email <span class="iz-req">*</span></label>
                                <input type="email" id="customer_email" name="customer_email" required placeholder="VD: nguyenvanan@gmail.com" />
                            </div>
                            <div class="iz-input-group">
                                <label for="customer_address">Địa Chỉ / Tỉnh Thành</label>
                                <input type="text" id="customer_address" name="customer_address" placeholder="VD: Quận 1, TP. Hồ Chí Minh" />
                            </div>
                        </div>

                        <div class="iz-input-group">
                            <label for="customer_note">Ghi Chú Đơn Tour (Nếu có yêu cầu đặc biệt)</label>
                            <textarea id="customer_note" name="customer_note" rows="3" placeholder="Yêu cầu ăn chay, có trẻ nhỏ, phòng giường đôi, xe đưa đón..."></textarea>
                        </div>
                    </div>

                    <!-- Section: Passenger List -->
                    <div class="iz-form-card">
                        <h2 class="iz-form-card-title">
                            <span class="iz-step-num">2</span>
                            Danh Sách Hành Khách Đi Tour
                        </h2>
                        <p class="iz-form-card-desc">Cung cấp họ tên và năm sinh chính xác theo CCCD/Hộ chiếu để làm thủ tục mua bảo hiểm và xuất vé máy bay.</p>

                        <div id="iz-passenger-list" class="iz-passengers-container">
                            <?php for ($i = 1; $i <= $total_guests; $i++) : ?>
                                <div class="iz-passenger-row">
                                    <div class="iz-passenger-num">Khách #<?php echo esc_html((string) $i); ?></div>
                                    <div class="iz-passenger-fields">
                                        <input type="text" name="passenger_name[]" placeholder="Họ và tên hành khách" required />
                                        <select name="passenger_gender[]">
                                            <option value="male">Nam</option>
                                            <option value="female">Nữ</option>
                                        </select>
                                        <input type="text" name="passenger_dob[]" placeholder="Năm sinh (VD: 1990)" />
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Section: Payment Methods -->
                    <div class="iz-form-card">
                        <h2 class="iz-form-card-title">
                            <span class="iz-step-num">3</span>
                            Phương Thức Thanh Toán
                        </h2>

                        <div class="iz-payment-options">
                            <label class="iz-payment-radio is-selected">
                                <input type="radio" name="payment_method" value="vietqr" checked />
                                <div class="iz-payment-info">
                                    <strong>Chuyển khoản VietQR tự động (Khuyên dùng)</strong>
                                    <p>Tạo mã QR tức thì có sẵn số tiền và mã đơn. Quét mã bằng app ngân hàng xác nhận ngay lập tức, không lo nhầm lẫn STK.</p>
                                </div>
                                <span class="iz-pay-badge-rec">Nhanh & Tiện</span>
                            </label>

                            <label class="iz-payment-radio">
                                <input type="radio" name="payment_method" value="bank_transfer" />
                                <div class="iz-payment-info">
                                    <strong>Chuyển khoản ngân hàng thủ công</strong>
                                    <p>Chuyển khoản qua số tài khoản công ty Gonatour tại MBBank hoặc Vietcombank và gửi ủy nhiệm chi.</p>
                                </div>
                            </label>

                            <label class="iz-payment-radio">
                                <input type="radio" name="payment_method" value="cash" />
                                <div class="iz-payment-info">
                                    <strong>Thanh toán tại văn phòng Gonatour</strong>
                                    <p>Đến trực tiếp văn phòng tại TP.HCM (63A Nam Kỳ Khởi Nghĩa, Q1) hoặc Hà Nội (18 Chùa Hà, Cầu Giấy) thanh toán bằng tiền mặt hoặc quẹt thẻ.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button & Feedback Alert -->
                    <div class="iz-checkout-submit-wrap">
                        <div id="iz-checkout-alert" class="iz-alert iz-alert-error" style="display: none;"></div>
                        <button type="submit" id="iz-submit-booking-btn" class="iz-submit-booking-btn">
                            <span class="iz-btn-text">HOÀN TẤT ĐẶT TOUR & NHẬN MÃ VIETQR</span>
                            <span class="iz-btn-loading" style="display: none;">Đang xử lý đơn hàng...</span>
                        </button>
                        <p class="iz-submit-disclaimer">Bằng cách nhấn nút Hoàn tất đặt tour, bạn đồng ý với Điều khoản sử dụng và Chính sách bảo mật của Gonatour.</p>
                    </div>
                </form>
            </div>

            <!-- Right: Booking Summary Receipt -->
            <aside class="iz-booking-summary-sidebar">
                <div class="iz-summary-card">
                    <h3 class="iz-summary-title">Tóm Tắt Chuyến Đi</h3>

                    <?php if (!empty($tour_title)) : ?>
                        <div class="iz-summary-tour-info">
                            <?php if (!empty($thumb_url)) : ?>
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($tour_title); ?>" class="iz-summary-thumb" />
                            <?php endif; ?>
                            <div>
                                <h4 class="iz-summary-tour-name"><?php echo esc_html($tour_title); ?></h4>
                                <?php if (!empty($tour_code)) : ?>
                                    <span class="iz-summary-code">Mã tour: <strong><?php echo esc_html($tour_code); ?></strong></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="iz-summary-tour-info">
                            <p><strong>Tour du lịch theo yêu cầu khách hàng</strong></p>
                        </div>
                    <?php endif; ?>

                    <div class="iz-summary-lines">
                        <div class="iz-summary-line">
                            <span>Ngày khởi hành:</span>
                            <strong><?php echo esc_html($departure_date ?: 'Liên hệ xác nhận'); ?></strong>
                        </div>
                        <div class="iz-summary-line">
                            <span>Người lớn (&ge; 12t):</span>
                            <span><?php echo esc_html((string) $adults_count); ?> &times; <?php echo esc_html(iz_format_price($price_adult)); ?></span>
                        </div>
                        <?php if ($child_count > 0) : ?>
                            <div class="iz-summary-line">
                                <span>Trẻ em (5 - 11t):</span>
                                <span><?php echo esc_html((string) $child_count); ?> &times; <?php echo esc_html(iz_format_price($price_child)); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($infant_count > 0) : ?>
                            <div class="iz-summary-line">
                                <span>Em bé (&lt; 5t):</span>
                                <span><?php echo esc_html((string) $infant_count); ?> &times; <?php echo esc_html(iz_format_price($price_infant)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="iz-summary-total-line">
                        <span>Tổng Thanh Toán:</span>
                        <strong class="iz-sum-total-price"><?php echo esc_html(iz_format_price($total_estimated)); ?></strong>
                    </div>

                    <div class="iz-summary-trust-badge">
                        <div class="iz-trust-check">✓ Đảm bảo giá minh bạch 100%</div>
                        <div class="iz-trust-check">✓ Giữ chỗ nhanh trong 24 giờ</div>
                        <div class="iz-trust-check">✓ Hỗ trợ xuất hóa đơn VAT điện tử</div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php
get_footer();
