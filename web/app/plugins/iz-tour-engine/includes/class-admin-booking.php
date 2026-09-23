<?php
/**
 * Admin Management, Columns, Status Updating, and CSV Export for Tour Bookings.
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
 * Class IZ_Tour_Admin_Booking
 *
 * Provides operations tooling for tour operators: custom post table columns,
 * inline status transition actions, and UTF-8 BOM CSV export for guest manifests.
 *
 * DECISION: UTF-8 BOM header in CSV export vs third-party PhpSpreadsheet library.
 * WHY: \xEF\xBB\xBF BOM forces Microsoft Excel on Windows to interpret Vietnamese
 * diacritics correctly without requiring heavy 15MB vendor dependencies.
 * REF: https://www.rfc-editor.org/rfc/rfc4180
 */
final class IZ_Tour_Admin_Booking
{
    private const EXPORT_NONCE_ACTION = 'iz_tour_export_bookings_action';
    private const STATUS_NONCE_ACTION = 'iz_tour_change_status_action';

    /**
     * Hook admin actions and filter callbacks.
     *
     * WHY: Attaches columns, query filters, quick actions, and export triggers to admin lifecycle.
     */
    public function register(): void
    {
        // Custom columns in edit.php?post_type=tour_booking
        add_filter('manage_tour_booking_posts_columns', [$this, 'customize_booking_columns']);
        add_action('manage_tour_booking_posts_custom_column', [$this, 'render_booking_columns'], 10, 2);
        add_filter('manage_edit-tour_booking_sortable_columns', [$this, 'sortable_booking_columns']);

        // Filtering controls above table
        add_action('restrict_manage_posts', [$this, 'render_booking_filters']);
        add_filter('parse_query', [$this, 'filter_booking_query']);

        // Quick status updater action
        add_action('admin_action_iz_tour_update_booking_status', [$this, 'handle_status_update']);

        // CSV Manifest export action
        add_action('admin_post_iz_tour_export_csv', [$this, 'handle_csv_export']);

        // Submenu and admin notices
        add_action('admin_menu', [$this, 'register_export_menu']);
        add_action('admin_notices', [$this, 'render_admin_notices']);
    }

    /**
     * Define column layout for booking list view.
     *
     * WHY: Elevates critical guest, tour, and payment information directly into the
     * dashboard overview without requiring individual post clicks.
     *
     * @param array<string, string> $columns Existing columns.
     * @return array<string, string> Modified columns.
     */
    public function customize_booking_columns(array $columns): array
    {
        return [
            'cb'             => $columns['cb'] ?? '<input type="checkbox" />',
            'booking_code'   => __('Mã Đơn', 'iz-tour-engine'),
            'customer_info'  => __('Khách Hàng', 'iz-tour-engine'),
            'tour_info'      => __('Tour & Ngày Đi', 'iz-tour-engine'),
            'guest_counts'   => __('Số Lượng', 'iz-tour-engine'),
            'total_amount'   => __('Tổng Tiền', 'iz-tour-engine'),
            'payment_status' => __('Trạng Thái', 'iz-tour-engine'),
            'payment_method' => __('Phương Thức', 'iz-tour-engine'),
            'date'           => __('Ngày Đặt', 'iz-tour-engine'),
        ];
    }

    /**
     * Render data cells for custom booking columns.
     *
     * WHY: Formats numeric values, contact details, and actionable status dropdowns.
     *
     * @param string $column  Column identifier.
     * @param int    $post_id Current post ID.
     */
    public function render_booking_columns(string $column, int $post_id): void
    {
        switch ($column) {
            case 'booking_code':
                $code = get_post_meta($post_id, '_booking_code', true) ?: ('BK-' . $post_id);
                $edit_url = get_edit_post_link($post_id);
                echo '<strong><a href="' . esc_url($edit_url) . '">' . esc_html($code) . '</a></strong>';
                break;

            case 'customer_info':
                $name  = get_post_meta($post_id, '_booking_customer_name', true);
                $phone = get_post_meta($post_id, '_booking_customer_phone', true);
                $email = get_post_meta($post_id, '_booking_customer_email', true);

                echo '<div><strong>' . esc_html($name) . '</strong></div>';
                if (!empty($phone)) {
                    echo '<div><a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a></div>';
                }
                if (!empty($email)) {
                    echo '<div style="font-size: 12px; color: #50575e;">' . esc_html($email) . '</div>';
                }
                break;

            case 'tour_info':
                $tour_id        = (int) get_post_meta($post_id, '_booking_tour_id', true);
                $departure_date = get_post_meta($post_id, '_booking_departure_date', true);
                $tour_title     = $tour_id > 0 ? get_the_title($tour_id) : __('Chưa chọn tour', 'iz-tour-engine');

                if ($tour_id > 0) {
                    echo '<div><a href="' . esc_url(get_edit_post_link($tour_id)) . '"><strong>' . esc_html($tour_title) . '</strong></a></div>';
                } else {
                    echo '<div>' . esc_html($tour_title) . '</div>';
                }

                if (!empty($departure_date)) {
                    echo '<div style="font-size: 12px; color: #0073aa;">' . sprintf(esc_html__('Khởi hành: %s', 'iz-tour-engine'), esc_html($departure_date)) . '</div>';
                }
                break;

            case 'guest_counts':
                $adult  = (int) get_post_meta($post_id, '_booking_adult_count', true);
                $child  = (int) get_post_meta($post_id, '_booking_child_count', true);
                $infant = (int) get_post_meta($post_id, '_booking_infant_count', true);

                echo esc_html(sprintf('%d Lớn', $adult));
                if ($child > 0) {
                    echo ' | ' . esc_html(sprintf('%d Trẻ em', $child));
                }
                if ($infant > 0) {
                    echo ' | ' . esc_html(sprintf('%d Em bé', $infant));
                }
                break;

            case 'total_amount':
                $total = (int) get_post_meta($post_id, '_booking_total_amount', true);
                echo '<strong>' . esc_html(number_format($total, 0, ',', '.')) . ' VND</strong>';
                break;

            case 'payment_status':
                $status = get_post_meta($post_id, '_booking_status', true) ?: 'pending';
                $badge_colors = [
                    'pending'   => ['bg' => '#fcf0db', 'color' => '#8a5b00', 'label' => 'Chờ xử lý'],
                    'confirmed' => ['bg' => '#e5f5fa', 'color' => '#006799', 'label' => 'Đã xác nhận'],
                    'paid'      => ['bg' => '#edfaef', 'color' => '#1b7a2d', 'label' => 'Đã thanh toán'],
                    'cancelled' => ['bg' => '#fbeaea', 'color' => '#b32d2e', 'label' => 'Đã hủy'],
                ];
                $cfg = $badge_colors[$status] ?? $badge_colors['pending'];

                echo '<span style="display:inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; background:' . esc_attr($cfg['bg']) . '; color:' . esc_attr($cfg['color']) . ';">' . esc_html($cfg['label']) . '</span>';

                // Inline quick update links
                if (current_user_can('manage_options')) {
                    $nonce = wp_create_nonce(self::STATUS_NONCE_ACTION);
                    echo '<div class="row-actions" style="margin-top: 4px;">';
                    if ($status !== 'paid') {
                        $paid_url = admin_url('admin.php?action=iz_tour_update_booking_status&booking_id=' . $post_id . '&status=paid&_wpnonce=' . $nonce);
                        echo '<a href="' . esc_url($paid_url) . '" style="color:#1b7a2d;">' . esc_html__('[Đã TT]', 'iz-tour-engine') . '</a> ';
                    }
                    if ($status !== 'confirmed') {
                        $conf_url = admin_url('admin.php?action=iz_tour_update_booking_status&booking_id=' . $post_id . '&status=confirmed&_wpnonce=' . $nonce);
                        echo '<a href="' . esc_url($conf_url) . '" style="color:#006799;">' . esc_html__('[Xác nhận]', 'iz-tour-engine') . '</a> ';
                    }
                    if ($status !== 'cancelled') {
                        $canc_url = admin_url('admin.php?action=iz_tour_update_booking_status&booking_id=' . $post_id . '&status=cancelled&_wpnonce=' . $nonce);
                        echo '<a href="' . esc_url($canc_url) . '" style="color:#b32d2e;">' . esc_html__('[Hủy]', 'iz-tour-engine') . '</a>';
                    }
                    echo '</div>';
                }
                break;

            case 'payment_method':
                $method = get_post_meta($post_id, '_booking_payment_method', true) ?: 'vietqr';
                $methods = [
                    'vietqr'        => 'VietQR',
                    'bank_transfer' => 'Chuyển khoản',
                    'cash'          => 'Tiền mặt',
                ];
                echo esc_html($methods[$method] ?? ucfirst($method));
                break;
        }
    }

    /**
     * Mark booking columns as sortable.
     *
     * WHY: Allows sorting by booking date and total amount.
     *
     * @param array<string, string> $columns Sortable columns.
     * @return array<string, string>
     */
    public function sortable_booking_columns(array $columns): array
    {
        $columns['total_amount'] = 'total_amount';
        return $columns;
    }

    /**
     * Render filtering dropdowns (Tour & Status) above the booking post table.
     *
     * WHY: Empowers operators to filter reservations for a specific tour departure.
     *
     * @param string $post_type Current post type.
     */
    public function render_booking_filters(string $post_type): void
    {
        if ($post_type !== 'tour_booking') {
            return;
        }

        // Tour filter
        $selected_tour = isset($_GET['filter_tour_id']) ? absint($_GET['filter_tour_id']) : 0;
        $tours = get_posts([
            'post_type'      => 'tour',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        echo '<select name="filter_tour_id">';
        echo '<option value="0">' . esc_html__('— Tất cả Tour —', 'iz-tour-engine') . '</option>';
        foreach ($tours as $t) {
            printf(
                '<option value="%d" %s>%s</option>',
                esc_attr((string) $t->ID),
                selected($selected_tour, $t->ID, false),
                esc_html($t->post_title)
            );
        }
        echo '</select>';

        // Status filter
        $selected_status = isset($_GET['filter_status']) ? sanitize_key($_GET['filter_status']) : '';
        $statuses = [
            'pending'   => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'paid'      => 'Đã thanh toán',
            'cancelled' => 'Đã hủy',
        ];

        echo '<select name="filter_status">';
        echo '<option value="">' . esc_html__('— Tất cả Trạng Thái —', 'iz-tour-engine') . '</option>';
        foreach ($statuses as $k => $label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($k),
                selected($selected_status, $k, false),
                esc_html($label)
            );
        }
        echo '</select>';

        // Export button right in table controls
        $export_nonce = wp_create_nonce(self::EXPORT_NONCE_ACTION);
        $export_url   = admin_url('admin-post.php?action=iz_tour_export_csv&_wpnonce=' . $export_nonce);
        if ($selected_tour > 0) {
            $export_url .= '&tour_id=' . $selected_tour;
        }
        echo '<a href="' . esc_url($export_url) . '" class="button button-secondary" style="margin-left: 6px;">' . esc_html__('Xuất CSV Danh Sách', 'iz-tour-engine') . '</a>';
    }

    /**
     * Apply filter criteria to WP_Query.
     *
     * WHY: Restricts list results based on chosen tour and status filters.
     *
     * @param \WP_Query $query Main query instance.
     */
    public function filter_booking_query(\WP_Query $query): void
    {
        global $pagenow;
        if (!is_admin() || $pagenow !== 'edit.php' || $query->get('post_type') !== 'tour_booking') {
            return;
        }

        $meta_query = [];

        if (!empty($_GET['filter_tour_id'])) {
            $meta_query[] = [
                'key'     => '_booking_tour_id',
                'value'   => absint($_GET['filter_tour_id']),
                'compare' => '=',
            ];
        }

        if (!empty($_GET['filter_status'])) {
            $meta_query[] = [
                'key'     => '_booking_status',
                'value'   => sanitize_key($_GET['filter_status']),
                'compare' => '=',
            ];
        }

        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }
    }

    /**
     * Handle quick status change requests from table row actions.
     *
     * WHY: Prevents full page edit workflows for common confirmation/payment updates,
     * while enforcing nonce and permission barriers.
     */
    public function handle_status_update(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Không đủ quyền hạn.', 'iz-tour-engine'), 403);
        }

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, self::STATUS_NONCE_ACTION)) {
            wp_die(esc_html__('Yêu cầu không hợp lệ hoặc đã hết hạn.', 'iz-tour-engine'), 403);
        }

        $booking_id = isset($_GET['booking_id']) ? absint($_GET['booking_id']) : 0;
        $status     = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';
        $valid_statuses = ['pending', 'confirmed', 'paid', 'cancelled'];

        if ($booking_id > 0 && in_array($status, $valid_statuses, true)) {
            update_post_meta($booking_id, '_booking_status', $status);
        }

        $redirect = wp_get_referer() ?: admin_url('edit.php?post_type=tour_booking');
        $redirect = add_query_arg('iz_status_updated', '1', $redirect);
        wp_safe_redirect($redirect);
        exit;
    }

    /**
     * Register dedicated export submenu item.
     *
     * WHY: Provides a direct link in the admin menu to download passenger manifests.
     */
    public function register_export_menu(): void
    {
        add_submenu_page(
            'edit.php?post_type=tour',
            __('Xuất Danh Sách Khách', 'iz-tour-engine'),
            __('Xuất Danh Sách Khách', 'iz-tour-engine'),
            'manage_options',
            'iz-tour-export',
            [$this, 'render_export_page']
        );
    }

    /**
     * Render the standalone export page.
     *
     * WHY: Allows customized filtering before exporting passenger lists.
     */
    public function render_export_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Không đủ quyền hạn.', 'iz-tour-engine'));
        }

        $tours = get_posts([
            'post_type'      => 'tour',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Xuất Danh Sách Khách Đi Tour (CSV/Excel)', 'iz-tour-engine'); ?></h1>
            <p><?php esc_html_e('Tải xuống tệp CSV chứa danh sách hành khách, số điện thoại, ngày đi và trạng thái thanh toán để nộp hãng hàng không hoặc làm danh sách đoàn.', 'iz-tour-engine'); ?></p>

            <div style="background: #fff; padding: 24px; border: 1px solid #ccd0d4; border-radius: 4px; max-width: 600px; margin-top: 16px;">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="iz_tour_export_csv" />
                    <?php wp_nonce_field(self::EXPORT_NONCE_ACTION, '_wpnonce'); ?>

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="tour_id"><?php esc_html_e('Chọn Tour:', 'iz-tour-engine'); ?></label></th>
                            <td>
                                <select name="tour_id" id="tour_id" class="regular-text">
                                    <option value="0"><?php esc_html_e('— Tất cả Tour —', 'iz-tour-engine'); ?></option>
                                    <?php foreach ($tours as $t) : ?>
                                        <option value="<?php echo esc_attr((string) $t->ID); ?>"><?php echo esc_html($t->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="status"><?php esc_html_e('Trạng Thái Đơn:', 'iz-tour-engine'); ?></label></th>
                            <td>
                                <select name="status" id="status" class="regular-text">
                                    <option value=""><?php esc_html_e('— Tất cả Trạng Thái —', 'iz-tour-engine'); ?></option>
                                    <option value="paid"><?php esc_html_e('Đã thanh toán (Paid)', 'iz-tour-engine'); ?></option>
                                    <option value="confirmed"><?php esc_html_e('Đã xác nhận (Confirmed)', 'iz-tour-engine'); ?></option>
                                    <option value="pending"><?php esc_html_e('Chờ xử lý (Pending)', 'iz-tour-engine'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(__('Tải Tệp CSV Ngay', 'iz-tour-engine')); ?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Generate and stream UTF-8 BOM CSV passenger manifest.
     *
     * WHY: Streams directly to output stream to handle large datasets with minimal RAM consumption.
     */
    public function handle_csv_export(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Không đủ quyền hạn.', 'iz-tour-engine'), 403);
        }

        $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, self::EXPORT_NONCE_ACTION)) {
            wp_die(esc_html__('Yêu cầu không hợp lệ hoặc đã hết hạn.', 'iz-tour-engine'), 403);
        }

        $tour_id = isset($_REQUEST['tour_id']) ? absint($_REQUEST['tour_id']) : 0;
        $status  = isset($_REQUEST['status']) ? sanitize_key($_REQUEST['status']) : '';

        $meta_query = [];
        if ($tour_id > 0) {
            $meta_query[] = [
                'key'     => '_booking_tour_id',
                'value'   => $tour_id,
                'compare' => '=',
            ];
        }
        if (!empty($status)) {
            $meta_query[] = [
                'key'     => '_booking_status',
                'value'   => $status,
                'compare' => '=',
            ];
        }

        $bookings = get_posts([
            'post_type'      => 'tour_booking',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => $meta_query,
            'orderby'        => 'ID',
            'order'          => 'DESC',
        ]);

        $filename = 'danh-sach-khach-tour-' . gmdate('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            exit;
        }

        // Write UTF-8 BOM for Microsoft Excel compatibility
        fwrite($out, "\xEF\xBB\xBF");

        // CSV Header
        fputcsv($out, [
            'Mã Đơn',
            'Họ và Tên',
            'Số Điện Thoại',
            'Email',
            'Tour Đăng Ký',
            'Ngày Khởi Hành',
            'Người Lớn',
            'Trẻ Em',
            'Em Bé',
            'Tổng Tiền (VND)',
            'Phương Thức',
            'Trạng Thái',
            'Ghi Chú',
            'Ngày Tạo',
        ]);

        $status_labels = [
            'pending'   => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'paid'      => 'Đã thanh toán',
            'cancelled' => 'Đã hủy',
        ];

        foreach ($bookings as $b) {
            $b_id       = $b->ID;
            $b_tour_id  = (int) get_post_meta($b_id, '_booking_tour_id', true);
            $tour_title = $b_tour_id > 0 ? get_the_title($b_tour_id) : 'N/A';
            $st_raw     = get_post_meta($b_id, '_booking_status', true) ?: 'pending';

            fputcsv($out, [
                get_post_meta($b_id, '_booking_code', true) ?: ('BK-' . $b_id),
                get_post_meta($b_id, '_booking_customer_name', true),
                get_post_meta($b_id, '_booking_customer_phone', true),
                get_post_meta($b_id, '_booking_customer_email', true),
                $tour_title,
                get_post_meta($b_id, '_booking_departure_date', true),
                (int) get_post_meta($b_id, '_booking_adult_count', true),
                (int) get_post_meta($b_id, '_booking_child_count', true),
                (int) get_post_meta($b_id, '_booking_infant_count', true),
                (int) get_post_meta($b_id, '_booking_total_amount', true),
                get_post_meta($b_id, '_booking_payment_method', true) ?: 'vietqr',
                $status_labels[$st_raw] ?? $st_raw,
                get_post_meta($b_id, '_booking_customer_note', true),
                $b->post_date,
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Render dismissible notice when status is updated.
     *
     * WHY: Gives visual confirmation of successful state changes.
     */
    public function render_admin_notices(): void
    {
        if (isset($_GET['iz_status_updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Trạng thái đơn đặt tour đã được cập nhật thành công.', 'iz-tour-engine') . '</p></div>';
        }
    }
}
