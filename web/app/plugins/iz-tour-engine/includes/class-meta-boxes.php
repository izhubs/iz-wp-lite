<?php
/**
 * Meta Boxes Management for Tours and Tour Bookings.
 *
 * PHP version 8.1+
 *
 * @package IZTourEngine
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class IZ_Tour_Meta_Boxes
 *
 * Handles creation, rendering, and secure persistence of custom metadata
 * for 'tour' and 'tour_booking' post types.
 *
 * DECISION: Native WordPress meta boxes with pure vanilla JavaScript repeater vs React/Gutenberg blocks.
 * WHY: Maximizes stability across diverse WordPress environments, requires zero build step (webpack/npm),
 * and prevents Gutenberg block validation breakage during core updates.
 * REF: https://developer.wordpress.org/plugins/metadata/custom-meta-boxes/
 */
final class IZ_Tour_Meta_Boxes
{
    private const TOUR_NONCE_ACTION = 'iz_tour_meta_save_action';
    private const TOUR_NONCE_FIELD  = 'iz_tour_meta_nonce';

    private const BOOKING_NONCE_ACTION = 'iz_tour_booking_meta_save_action';
    private const BOOKING_NONCE_FIELD  = 'iz_tour_booking_meta_nonce';

    /**
     * Register meta box hooks with WordPress core.
     *
     * WHY: Connects UI creation to 'add_meta_boxes' and persistence to 'save_post'.
     */
    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_tour', [$this, 'save_tour_meta'], 10, 2);
        add_action('save_post_tour_booking', [$this, 'save_booking_meta'], 10, 2);
    }

    /**
     * Define meta box containers for post types.
     *
     * WHY: Injects distinct configuration dashboards into the post editor screens.
     */
    public function add_meta_boxes(): void
    {
        add_meta_box(
            'iz_tour_specs_meta',
            __('Thông Tin & Bảng Giá Tour', 'iz-tour-engine'),
            [$this, 'render_tour_meta_box'],
            'tour',
            'normal',
            'high'
        );

        add_meta_box(
            'iz_tour_itinerary_meta',
            __('Lịch Trình Chi Tiết (Itinerary)', 'iz-tour-engine'),
            [$this, 'render_itinerary_meta_box'],
            'tour',
            'normal',
            'high'
        );

        add_meta_box(
            'iz_tour_booking_details_meta',
            __('Thông Tin Khách Hàng & Đơn Đặt', 'iz-tour-engine'),
            [$this, 'render_booking_meta_box'],
            'tour_booking',
            'normal',
            'high'
        );
    }

    /**
     * Render the Tour specifications and pricing meta box.
     *
     * WHY: Centralizes commercial attributes (pricing, transport, dates) needed
     * for booking calculations and frontend filtering.
     *
     * @param \WP_Post $post Current post object.
     */
    public function render_tour_meta_box(\WP_Post $post): void
    {
        wp_nonce_field(self::TOUR_NONCE_ACTION, self::TOUR_NONCE_FIELD);

        $tour_code        = get_post_meta($post->ID, '_tour_code', true);
        $duration         = get_post_meta($post->ID, '_tour_duration', true);
        $departure_city   = get_post_meta($post->ID, '_tour_departure_city', true);
        $transportation   = get_post_meta($post->ID, '_tour_transportation', true);
        $price_adult      = (int) get_post_meta($post->ID, '_tour_price_adult', true);
        $price_child      = (int) get_post_meta($post->ID, '_tour_price_child', true);
        $price_infant     = (int) get_post_meta($post->ID, '_tour_price_infant', true);
        $price_single_sup = (int) get_post_meta($post->ID, '_tour_price_single_sup', true);
        $departure_dates  = get_post_meta($post->ID, '_tour_departure_dates', true);

        ?>
        <style>
            .iz-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px; }
            .iz-meta-field { display: flex; flex-direction: column; }
            .iz-meta-field label { font-weight: 600; margin-bottom: 4px; }
            .iz-meta-field input, .iz-meta-field textarea, .iz-meta-field select { width: 100%; }
            .iz-meta-hint { font-size: 12px; color: #646970; margin-top: 4px; }
            .iz-section-title { font-weight: 700; border-bottom: 1px solid #c3c4c7; padding-bottom: 6px; margin: 16px 0 12px; }
        </style>

        <div class="iz-meta-container">
            <div class="iz-meta-grid">
                <div class="iz-meta-field">
                    <label for="_tour_code"><?php esc_html_e('Mã Tour (Tour Code)', 'iz-tour-engine'); ?></label>
                    <input type="text" id="_tour_code" name="_tour_code" value="<?php echo esc_attr($tour_code); ?>" placeholder="VD: DN-3N2D-01" />
                    <span class="iz-meta-hint"><?php esc_html_e('Mã định danh duy nhất của tour.', 'iz-tour-engine'); ?></span>
                </div>
                <div class="iz-meta-field">
                    <label for="_tour_duration"><?php esc_html_e('Thời Lượng (Duration)', 'iz-tour-engine'); ?></label>
                    <input type="text" id="_tour_duration" name="_tour_duration" value="<?php echo esc_attr($duration); ?>" placeholder="VD: 3 Ngày 2 Đêm" />
                    <span class="iz-meta-hint"><?php esc_html_e('Hiển thị trên thẻ tour và bộ lọc.', 'iz-tour-engine'); ?></span>
                </div>
            </div>

            <div class="iz-meta-grid">
                <div class="iz-meta-field">
                    <label for="_tour_departure_city"><?php esc_html_e('Nơi Khởi Hành', 'iz-tour-engine'); ?></label>
                    <input type="text" id="_tour_departure_city" name="_tour_departure_city" value="<?php echo esc_attr($departure_city); ?>" placeholder="VD: Hà Nội, TP.HCM" />
                </div>
                <div class="iz-meta-field">
                    <label for="_tour_transportation"><?php esc_html_e('Phương Tiện Di Chuyển', 'iz-tour-engine'); ?></label>
                    <input type="text" id="_tour_transportation" name="_tour_transportation" value="<?php echo esc_attr($transportation); ?>" placeholder="VD: Máy bay Vietnam Airlines, Ô tô đời mới" />
                </div>
            </div>

            <div class="iz-section-title"><?php esc_html_e('Bảng Giá Áp Dụng (VND)', 'iz-tour-engine'); ?></div>

            <div class="iz-meta-grid">
                <div class="iz-meta-field">
                    <label for="_tour_price_adult"><?php esc_html_e('Giá Người Lớn (Adult Price)', 'iz-tour-engine'); ?> <span style="color:#d63638">*</span></label>
                    <input type="number" id="_tour_price_adult" name="_tour_price_adult" value="<?php echo esc_attr((string) $price_adult); ?>" min="0" step="1000" />
                    <span class="iz-meta-hint"><?php esc_html_e('Giá cơ sở cho mỗi khách từ 12 tuổi trở lên.', 'iz-tour-engine'); ?></span>
                </div>
                <div class="iz-meta-field">
                    <label for="_tour_price_child"><?php esc_html_e('Giá Trẻ Em (Child Price)', 'iz-tour-engine'); ?></label>
                    <input type="number" id="_tour_price_child" name="_tour_price_child" value="<?php echo esc_attr((string) $price_child); ?>" min="0" step="1000" />
                    <span class="iz-meta-hint"><?php esc_html_e('Áp dụng trẻ em từ 5 đến 11 tuổi.', 'iz-tour-engine'); ?></span>
                </div>
            </div>

            <div class="iz-meta-grid">
                <div class="iz-meta-field">
                    <label for="_tour_price_infant"><?php esc_html_e('Giá Em Bé (Infant Price)', 'iz-tour-engine'); ?></label>
                    <input type="number" id="_tour_price_infant" name="_tour_price_infant" value="<?php echo esc_attr((string) $price_infant); ?>" min="0" step="1000" />
                    <span class="iz-meta-hint"><?php esc_html_e('Áp dụng trẻ nhỏ dưới 5 tuổi.', 'iz-tour-engine'); ?></span>
                </div>
                <div class="iz-meta-field">
                    <label for="_tour_price_single_sup"><?php esc_html_e('Phụ Thu Phòng Đơn (Single Supplement)', 'iz-tour-engine'); ?></label>
                    <input type="number" id="_tour_price_single_sup" name="_tour_price_single_sup" value="<?php echo esc_attr((string) $price_single_sup); ?>" min="0" step="1000" />
                </div>
            </div>

            <div class="iz-section-title"><?php esc_html_e('Lịch Khởi Hành (Departure Dates)', 'iz-tour-engine'); ?></div>

            <div class="iz-meta-field">
                <label for="_tour_departure_dates"><?php esc_html_e('Danh Sách Ngày Khởi Hành (Mỗi ngày một dòng)', 'iz-tour-engine'); ?></label>
                <textarea id="_tour_departure_dates" name="_tour_departure_dates" rows="4" placeholder="2026-10-15&#10;2026-10-25&#10;2026-11-05"><?php echo esc_textarea($departure_dates); ?></textarea>
                <span class="iz-meta-hint"><?php esc_html_e('Định dạng chuẩn YYYY-MM-DD hoặc ngày cụ thể. Mỗi dòng tương ứng một đợt khởi hành.', 'iz-tour-engine'); ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Render the structured Itinerary meta box with dynamic repeater.
     *
     * WHY: Enables structured multi-day itinerary input without external plugins,
     * serializing as a normalized JSON array for flexible frontend templating.
     *
     * @param \WP_Post $post Current post object.
     */
    public function render_itinerary_meta_box(\WP_Post $post): void
    {
        $raw_itinerary = get_post_meta($post->ID, '_tour_itinerary', true);
        $days = is_string($raw_itinerary) && !empty($raw_itinerary) ? json_decode($raw_itinerary, true) : [];
        if (!is_array($days)) {
            $days = [];
        }
        ?>
        <style>
            .iz-itinerary-item { background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 4px; padding: 12px; margin-bottom: 12px; }
            .iz-itinerary-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-weight: 600; }
            .iz-itinerary-item input, .iz-itinerary-item textarea { width: 100%; margin-top: 4px; }
            .iz-btn-remove { color: #b32d2e; cursor: pointer; text-decoration: underline; background: none; border: none; font-size: 13px; }
            .iz-btn-remove:hover { color: #d63638; }
        </style>

        <div id="iz-itinerary-container">
            <?php if (!empty($days)) : ?>
                <?php foreach ($days as $idx => $day) : ?>
                    <div class="iz-itinerary-item" data-index="<?php echo esc_attr((string) $idx); ?>">
                        <div class="iz-itinerary-header">
                            <span><?php esc_html_e('Ngày', 'iz-tour-engine'); ?> <span class="day-num"><?php echo esc_html((string) ($idx + 1)); ?></span></span>
                            <button type="button" class="iz-btn-remove" onclick="izRemoveDay(this)"><?php esc_html_e('Xóa Ngày Này', 'iz-tour-engine'); ?></button>
                        </div>
                        <p>
                            <label><strong><?php esc_html_e('Tiêu Đề Lộ Trình:', 'iz-tour-engine'); ?></strong></label>
                            <input type="text" name="_itinerary_title[]" value="<?php echo esc_attr($day['title'] ?? ''); ?>" placeholder="VD: Khởi hành Hà Nội - Hạ Long - Vịnh Bái Tử Long" />
                        </p>
                        <p>
                            <label><strong><?php esc_html_e('Chi Tiết Hoạt Động:', 'iz-tour-engine'); ?></strong></label>
                            <textarea name="_itinerary_desc[]" rows="3" placeholder="Mô tả chi tiết sáng/chiều/tối, bữa ăn, khách sạn..."><?php echo esc_textarea($day['desc'] ?? ''); ?></textarea>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" class="button button-secondary" id="iz-add-day-btn" onclick="izAddDay()"><?php esc_html_e('+ Thêm Ngày Mới Vào Lịch Trình', 'iz-tour-engine'); ?></button>

        <script>
            function izAddDay() {
                const container = document.getElementById('iz-itinerary-container');
                const items = container.querySelectorAll('.iz-itinerary-item');
                const nextDay = items.length + 1;

                const dayDiv = document.createElement('div');
                dayDiv.className = 'iz-itinerary-item';
                dayDiv.innerHTML = `
                    <div class="iz-itinerary-header">
                        <span>Ngày <span class="day-num">${nextDay}</span></span>
                        <button type="button" class="iz-btn-remove" onclick="izRemoveDay(this)">Xóa Ngày Này</button>
                    </div>
                    <p>
                        <label><strong>Tiêu Đề Lộ Trình:</strong></label>
                        <input type="text" name="_itinerary_title[]" value="" placeholder="VD: Khởi hành Hà Nội - Hạ Long" />
                    </p>
                    <p>
                        <label><strong>Chi Tiết Hoạt Động:</strong></label>
                        <textarea name="_itinerary_desc[]" rows="3" placeholder="Mô tả chi tiết hoạt động..."></textarea>
                    </p>
                `;
                container.appendChild(dayDiv);
            }

            function izRemoveDay(btn) {
                const item = btn.closest('.iz-itinerary-item');
                if (item) {
                    item.remove();
                    izReindexDays();
                }
            }

            function izReindexDays() {
                const container = document.getElementById('iz-itinerary-container');
                const items = container.querySelectorAll('.iz-itinerary-item');
                items.forEach((item, index) => {
                    const numSpan = item.querySelector('.day-num');
                    if (numSpan) {
                        numSpan.textContent = index + 1;
                    }
                });
            }
        </script>
        <?php
    }

    /**
     * Render the Tour Booking meta box.
     *
     * WHY: Displays order data, customer identity, guest counts, and payment state
     * for front-desk or operations staff processing bookings.
     *
     * @param \WP_Post $post Current post object.
     */
    public function render_booking_meta_box(\WP_Post $post): void
    {
        wp_nonce_field(self::BOOKING_NONCE_ACTION, self::BOOKING_NONCE_FIELD);

        $customer_name   = get_post_meta($post->ID, '_booking_customer_name', true);
        $customer_phone  = get_post_meta($post->ID, '_booking_customer_phone', true);
        $customer_email  = get_post_meta($post->ID, '_booking_customer_email', true);
        $customer_note   = get_post_meta($post->ID, '_booking_customer_note', true);
        $tour_id         = (int) get_post_meta($post->ID, '_booking_tour_id', true);
        $departure_date  = get_post_meta($post->ID, '_booking_departure_date', true);
        $adult_count     = max(1, (int) get_post_meta($post->ID, '_booking_adult_count', true));
        $child_count     = (int) get_post_meta($post->ID, '_booking_child_count', true);
        $infant_count    = (int) get_post_meta($post->ID, '_booking_infant_count', true);
        $total_amount    = (int) get_post_meta($post->ID, '_booking_total_amount', true);
        $booking_status  = get_post_meta($post->ID, '_booking_status', true) ?: 'pending';
        $payment_method  = get_post_meta($post->ID, '_booking_payment_method', true) ?: 'vietqr';
        $booking_code    = get_post_meta($post->ID, '_booking_code', true) ?: ('BK-' . $post->ID);

        // Fetch published tours for assignment
        $tours = get_posts([
            'post_type'      => 'tour',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);
        ?>
        <div class="iz-meta-container">
            <div class="iz-meta-grid">
                <div class="iz-meta-field">
                    <label for="_booking_code"><strong><?php esc_html_e('Mã Đơn Đặt:', 'iz-tour-engine'); ?></strong></label>
                    <input type="text" id="_booking_code" name="_booking_code" value="<?php echo esc_attr($booking_code); ?>" readonly style="background:#f0f0f1;" />
                </div>
                <div class="iz-meta-field">
                    <label for="_booking_status"><strong><?php esc_html_e('Trạng Thái Đơn Hàng:', 'iz-tour-engine'); ?></strong></label>
                    <select id="_booking_status" name="_booking_status">
                        <option value="pending" <?php selected($booking_status, 'pending'); ?>><?php esc_html_e('Chờ xử lý (Pending)', 'iz-tour-engine'); ?></option>
                        <option value="confirmed" <?php selected($booking_status, 'confirmed'); ?>><?php esc_html_e('Đã xác nhận (Confirmed)', 'iz-tour-engine'); ?></option>
                        <option value="paid" <?php selected($booking_status, 'paid'); ?>><?php esc_html_e('Đã thanh toán (Paid)', 'iz-tour-engine'); ?></option>
                        <option value="cancelled" <?php selected($booking_status, 'cancelled'); ?>><?php esc_html_e('Đã hủy (Cancelled)', 'iz-tour-engine'); ?></option>
                    </select>
                </div>
            </div>

            <div class="iz-section-title"><?php esc_html_e('Thông Tin Khách Hàng', 'iz-tour-engine'); ?></div>

            <div class="iz-meta-grid">
                <div class="iz-meta-field">
                    <label for="_booking_customer_name"><?php esc_html_e('Họ và Tên Khách:', 'iz-tour-engine'); ?> <span style="color:#d63638">*</span></label>
                    <input type="text" id="_booking_customer_name" name="_booking_customer_name" value="<?php echo esc_attr($customer_name); ?>" required />
                </div>
                <div class="iz-meta-field">
                    <label for="_booking_customer_phone"><?php esc_html_e('Số Điện Thoại:', 'iz-tour-engine'); ?> <span style="color:#d63638">*</span></label>
                    <input type="text" id="_booking_customer_phone" name="_booking_customer_phone" value="<?php echo esc_attr($customer_phone); ?>" required />
                </div>
            </div>

            <div class="iz-meta-grid">
                <div class="iz-meta-field">
                    <label for="_booking_customer_email"><?php esc_html_e('Email:', 'iz-tour-engine'); ?></label>
                    <input type="email" id="_booking_customer_email" name="_booking_customer_email" value="<?php echo esc_attr($customer_email); ?>" />
                </div>
                <div class="iz-meta-field">
                    <label for="_booking_payment_method"><?php esc_html_e('Phương Thức Thanh Toán:', 'iz-tour-engine'); ?></label>
                    <select id="_booking_payment_method" name="_booking_payment_method">
                        <option value="vietqr" <?php selected($payment_method, 'vietqr'); ?>><?php esc_html_e('VietQR (Chuyển khoản QR tự động)', 'iz-tour-engine'); ?></option>
                        <option value="bank_transfer" <?php selected($payment_method, 'bank_transfer'); ?>><?php esc_html_e('Chuyển khoản ngân hàng thủ công', 'iz-tour-engine'); ?></option>
                        <option value="cash" <?php selected($payment_method, 'cash'); ?>><?php esc_html_e('Tiền mặt tại quầy', 'iz-tour-engine'); ?></option>
                    </select>
                </div>
            </div>

            <div class="iz-meta-field">
                <label for="_booking_customer_note"><?php esc_html_e('Ghi Chú Của Khách:', 'iz-tour-engine'); ?></label>
                <textarea id="_booking_customer_note" name="_booking_customer_note" rows="2"><?php echo esc_textarea($customer_note); ?></textarea>
            </div>

            <div class="iz-section-title"><?php esc_html_e('Dịch Vụ & Số Lượng Khách', 'iz-tour-engine'); ?></div>

            <div class="iz-meta-grid">
                <div class="iz-meta-field">
                    <label for="_booking_tour_id"><?php esc_html_e('Tour Đăng Ký:', 'iz-tour-engine'); ?></label>
                    <select id="_booking_tour_id" name="_booking_tour_id">
                        <option value="0"><?php esc_html_e('-- Chọn Tour --', 'iz-tour-engine'); ?></option>
                        <?php foreach ($tours as $t) : ?>
                            <option value="<?php echo esc_attr((string) $t->ID); ?>" <?php selected($tour_id, $t->ID); ?>>
                                <?php echo esc_html($t->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="iz-meta-field">
                    <label for="_booking_departure_date"><?php esc_html_e('Ngày Khởi Hành:', 'iz-tour-engine'); ?></label>
                    <input type="date" id="_booking_departure_date" name="_booking_departure_date" value="<?php echo esc_attr($departure_date); ?>" />
                </div>
            </div>

            <div class="iz-meta-grid" style="grid-template-columns: 1fr 1fr 1fr 1.5fr;">
                <div class="iz-meta-field">
                    <label for="_booking_adult_count"><?php esc_html_e('Người Lớn:', 'iz-tour-engine'); ?></label>
                    <input type="number" id="_booking_adult_count" name="_booking_adult_count" value="<?php echo esc_attr((string) $adult_count); ?>" min="1" />
                </div>
                <div class="iz-meta-field">
                    <label for="_booking_child_count"><?php esc_html_e('Trẻ Em:', 'iz-tour-engine'); ?></label>
                    <input type="number" id="_booking_child_count" name="_booking_child_count" value="<?php echo esc_attr((string) $child_count); ?>" min="0" />
                </div>
                <div class="iz-meta-field">
                    <label for="_booking_infant_count"><?php esc_html_e('Em Bé:', 'iz-tour-engine'); ?></label>
                    <input type="number" id="_booking_infant_count" name="_booking_infant_count" value="<?php echo esc_attr((string) $infant_count); ?>" min="0" />
                </div>
                <div class="iz-meta-field">
                    <label for="_booking_total_amount"><?php esc_html_e('Tổng Tiền (VND):', 'iz-tour-engine'); ?></label>
                    <input type="number" id="_booking_total_amount" name="_booking_total_amount" value="<?php echo esc_attr((string) $total_amount); ?>" min="0" step="1000" style="font-size: 16px; font-weight: bold; color: #0073aa;" />
                </div>
            </div>

            <?php if ($total_amount > 0 && class_exists('IZ_Tour_VietQR')) : ?>
                <?php
                $vietqr_url = IZ_Tour_VietQR::build_qr_url($total_amount, $booking_code);
                ?>
                <div style="margin-top: 16px; padding: 12px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; display: flex; gap: 16px; align-items: center;">
                    <img src="<?php echo esc_url($vietqr_url); ?>" alt="VietQR" style="max-width: 140px; height: auto; border: 1px solid #ddd;" />
                    <div>
                        <strong style="display: block; font-size: 14px; margin-bottom: 4px;"><?php esc_html_e('Mã VietQR Tự Động Cho Đơn Này', 'iz-tour-engine'); ?></strong>
                        <p style="margin: 0 0 6px 0; font-size: 13px;">
                            <?php printf(esc_html__('Khách chuyển khoản đúng số tiền: %s VND', 'iz-tour-engine'), number_format($total_amount, 0, ',', '.')); ?><br />
                            <?php printf(esc_html__('Nội dung chuyển khoản: %s', 'iz-tour-engine'), esc_html($booking_code)); ?>
                        </p>
                        <a href="<?php echo esc_url($vietqr_url); ?>" target="_blank" class="button button-small"><?php esc_html_e('Xem / Tải Mã QR Lớn', 'iz-tour-engine'); ?></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Persist metadata for 'tour' post type.
     *
     * WHY: Guarantees input sanitization, prevents CSRF attacks via nonce checks,
     * and ensures only authorized users modify tour business rules.
     *
     * @param int      $post_id Post ID.
     * @param \WP_Post $post    Post object.
     */
    public function save_tour_meta(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST[self::TOUR_NONCE_FIELD]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::TOUR_NONCE_FIELD])), self::TOUR_NONCE_ACTION)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = [
            '_tour_code'            => 'sanitize_text_field',
            '_tour_duration'        => 'sanitize_text_field',
            '_tour_departure_city'  => 'sanitize_text_field',
            '_tour_transportation'  => 'sanitize_text_field',
            '_tour_price_adult'     => 'absint',
            '_tour_price_child'     => 'absint',
            '_tour_price_infant'    => 'absint',
            '_tour_price_single_sup' => 'absint',
            '_tour_departure_dates' => 'sanitize_textarea_field',
        ];

        foreach ($fields as $field_key => $sanitizer) {
            if (isset($_POST[$field_key])) {
                $raw_val = wp_unslash($_POST[$field_key]);
                $clean_val = match ($sanitizer) {
                    'absint'                   => absint($raw_val),
                    'sanitize_textarea_field'  => sanitize_textarea_field($raw_val),
                    default                    => sanitize_text_field($raw_val),
                };
                update_post_meta($post_id, $field_key, $clean_val);
            }
        }

        // Process Itinerary array
        if (isset($_POST['_itinerary_title']) && is_array($_POST['_itinerary_title'])) {
            $titles = array_map('sanitize_text_field', wp_unslash($_POST['_itinerary_title']));
            $descs  = isset($_POST['_itinerary_desc']) && is_array($_POST['_itinerary_desc'])
                ? array_map('sanitize_textarea_field', wp_unslash($_POST['_itinerary_desc']))
                : [];

            $itinerary = [];
            foreach ($titles as $idx => $title) {
                if (trim($title) !== '' || !empty($descs[$idx])) {
                    $itinerary[] = [
                        'day'   => $idx + 1,
                        'title' => $title,
                        'desc'  => $descs[$idx] ?? '',
                    ];
                }
            }

            update_post_meta($post_id, '_tour_itinerary', wp_json_encode($itinerary, JSON_UNESCAPED_UNICODE));
        } else {
            delete_post_meta($post_id, '_tour_itinerary');
        }
    }

    /**
     * Persist metadata for 'tour_booking' post type.
     *
     * WHY: Secures customer data against manipulation, validates pricing format,
     * and sets booking identifiers.
     *
     * @param int      $post_id Post ID.
     * @param \WP_Post $post    Post object.
     */
    public function save_booking_meta(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST[self::BOOKING_NONCE_FIELD]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::BOOKING_NONCE_FIELD])), self::BOOKING_NONCE_ACTION)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = [
            '_booking_customer_name'   => 'sanitize_text_field',
            '_booking_customer_phone'  => 'sanitize_text_field',
            '_booking_customer_email'  => 'sanitize_email',
            '_booking_customer_note'   => 'sanitize_textarea_field',
            '_booking_tour_id'         => 'absint',
            '_booking_departure_date'  => 'sanitize_text_field',
            '_booking_adult_count'     => 'absint',
            '_booking_child_count'     => 'absint',
            '_booking_infant_count'    => 'absint',
            '_booking_total_amount'    => 'absint',
            '_booking_status'          => 'sanitize_key',
            '_booking_payment_method'  => 'sanitize_key',
            '_booking_code'            => 'sanitize_text_field',
        ];

        foreach ($fields as $field_key => $sanitizer) {
            if (isset($_POST[$field_key])) {
                $raw_val = wp_unslash($_POST[$field_key]);
                $clean_val = match ($sanitizer) {
                    'absint'                  => absint($raw_val),
                    'sanitize_email'          => sanitize_email($raw_val),
                    'sanitize_textarea_field' => sanitize_textarea_field($raw_val),
                    'sanitize_key'            => sanitize_key($raw_val),
                    default                   => sanitize_text_field($raw_val),
                };
                update_post_meta($post_id, $field_key, $clean_val);
            }
        }
    }
}
