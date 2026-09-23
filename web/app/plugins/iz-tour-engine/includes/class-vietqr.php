<?php
/**
 * VietQR Integration Helper and Settings.
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
 * Class IZ_Tour_VietQR
 *
 * Implements the VietQR QuickLink specification for automated bank transfer reconciliation.
 * Generates dynamic QR image URLs based on VietQR NAPAS 247 gateway standards.
 *
 * DECISION: Client-side dynamic image generation via img.vietqr.io vs local QR SVG generation.
 * WHY: img.vietqr.io provides official banking logos, certified NAPAS EMVCo format,
 * and handles background templating with zero PHP GD/Imagick dependencies.
 * REF: https://vietqr.io/
 */
final class IZ_Tour_VietQR
{
    public const OPTION_BANK_BIN     = 'iz_tour_bank_bin';
    public const OPTION_ACCOUNT_NO   = 'iz_tour_account_no';
    public const OPTION_ACCOUNT_NAME = 'iz_tour_account_name';
    public const OPTION_BANK_NAME    = 'iz_tour_bank_name';

    private const DEFAULT_TEMPLATE = 'compact2';

    /**
     * Common Vietnamese Banking Institutions and their NAPAS BIN codes.
     */
    public const SUPPORTED_BANKS = [
        '970422' => 'MBBank (Ngân hàng Quân Đội)',
        '970436' => 'Vietcombank (Ngoại thương Việt Nam)',
        '970415' => 'VietinBank (Công thương Việt Nam)',
        '970418' => 'BIDV (Đầu tư và Phát triển Việt Nam)',
        '970407' => 'Techcombank (Kỹ thương Việt Nam)',
        '970416' => 'ACB (Á Châu)',
        '970432' => 'VPBank (Việt Nam Thịnh Vượng)',
        '970423' => 'TPBank (Tiên Phong)',
        '970403' => 'Sacombank (Sài Gòn Thương Tín)',
        '970437' => 'HDBank (Phát triển TP.HCM)',
        '970441' => 'VIB (Quốc tế)',
        '970426' => 'MSB (Hàng Hải)',
    ];

    /**
     * Hook settings registration into WordPress lifecycle.
     *
     * WHY: Integrates VietQR payment parameters into wp-admin Settings API.
     */
    public function register(): void
    {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'register_settings_page']);
    }

    /**
     * Register settings fields in WordPress database.
     *
     * WHY: Ensures configuration fields are whitelisted and sanitized
     * through the WordPress Settings API.
     */
    public function register_settings(): void
    {
        register_setting('iz_tour_settings_group', self::OPTION_BANK_BIN, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '970422',
        ]);

        register_setting('iz_tour_settings_group', self::OPTION_ACCOUNT_NO, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);

        register_setting('iz_tour_settings_group', self::OPTION_ACCOUNT_NAME, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);

        register_setting('iz_tour_settings_group', self::OPTION_BANK_NAME, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);
    }

    /**
     * Register the Tour Settings submenu item.
     *
     * WHY: Gives administrators an intuitive UI location under Quản Lý Tour to configure payments.
     */
    public function register_settings_page(): void
    {
        add_submenu_page(
            'edit.php?post_type=tour',
            __('Cấu Hình VietQR & Tour', 'iz-tour-engine'),
            __('Cài Đặt VietQR', 'iz-tour-engine'),
            'manage_options',
            'iz-tour-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Render the admin configuration dashboard for VietQR.
     *
     * WHY: Provides a form for merchant banking details with live validation and visual QR test.
     */
    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bạn không có quyền truy cập trang này.', 'iz-tour-engine'));
        }

        $current_bin          = (string) get_option(self::OPTION_BANK_BIN, '970422');
        $current_account_no   = (string) get_option(self::OPTION_ACCOUNT_NO, '');
        $current_account_name = (string) get_option(self::OPTION_ACCOUNT_NAME, '');
        $test_amount          = 100000;
        $test_code            = 'TESTQR';

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php esc_html_e('Cấu hình tài khoản ngân hàng để tự động tạo mã VietQR thanh toán cho các đơn đặt tour.', 'iz-tour-engine'); ?></p>

            <?php settings_errors(); ?>

            <div style="display: flex; gap: 32px; align-items: flex-start; max-width: 1000px; margin-top: 20px;">
                <div style="flex: 1; background: #fff; padding: 24px; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('iz_tour_settings_group');
                        ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row">
                                    <label for="<?php echo esc_attr(self::OPTION_BANK_BIN); ?>"><?php esc_html_e('Ngân Hàng Tiếp Nhận:', 'iz-tour-engine'); ?></label>
                                </th>
                                <td>
                                    <select name="<?php echo esc_attr(self::OPTION_BANK_BIN); ?>" id="<?php echo esc_attr(self::OPTION_BANK_BIN); ?>" class="regular-text">
                                        <?php foreach (self::SUPPORTED_BANKS as $bin => $name) : ?>
                                            <option value="<?php echo esc_attr($bin); ?>" <?php selected($current_bin, $bin); ?>>
                                                <?php echo esc_html($name . ' - ' . $bin); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php esc_html_e('Chọn ngân hàng theo mã định danh NAPAS (BIN).', 'iz-tour-engine'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="<?php echo esc_attr(self::OPTION_ACCOUNT_NO); ?>"><?php esc_html_e('Số Tài Khoản:', 'iz-tour-engine'); ?> <span style="color:#d63638">*</span></label>
                                </th>
                                <td>
                                    <input type="text" name="<?php echo esc_attr(self::OPTION_ACCOUNT_NO); ?>" id="<?php echo esc_attr(self::OPTION_ACCOUNT_NO); ?>" value="<?php echo esc_attr($current_account_no); ?>" class="regular-text" required placeholder="VD: 0123456789" />
                                    <p class="description"><?php esc_html_e('Số tài khoản ngân hàng thụ hưởng thanh toán.', 'iz-tour-engine'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="<?php echo esc_attr(self::OPTION_ACCOUNT_NAME); ?>"><?php esc_html_e('Tên Chủ Tài Khoản:', 'iz-tour-engine'); ?> <span style="color:#d63638">*</span></label>
                                </th>
                                <td>
                                    <input type="text" name="<?php echo esc_attr(self::OPTION_ACCOUNT_NAME); ?>" id="<?php echo esc_attr(self::OPTION_ACCOUNT_NAME); ?>" value="<?php echo esc_attr($current_account_name); ?>" class="regular-text" required placeholder="VD: NGUYEN VAN A hoặc CONG TY DU LICH" />
                                    <p class="description"><?php esc_html_e('Viết hoa, không dấu (Khuyến nghị chuẩn ngân hàng NAPAS).', 'iz-tour-engine'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <?php submit_button(__('Lưu Cấu Hình VietQR', 'iz-tour-engine')); ?>
                    </form>
                </div>

                <div style="width: 320px; background: #f6f7f7; padding: 20px; border: 1px solid #dcdcde; border-radius: 4px; text-align: center;">
                    <h3 style="margin-top: 0;"><?php esc_html_e('Xem Trước Mã VietQR Test', 'iz-tour-engine'); ?></h3>
                    <?php if (!empty($current_account_no)) : ?>
                        <?php $test_qr = self::build_qr_url($test_amount, $test_code, $current_bin, $current_account_no, $current_account_name); ?>
                        <img src="<?php echo esc_url($test_qr); ?>" alt="Preview VietQR" style="max-width: 100%; height: auto; border: 1px solid #ccc; border-radius: 4px;" />
                        <p style="font-size: 12px; color: #50575e; margin-top: 8px;">
                            <?php printf(esc_html__('Tài khoản: %s | Tiền test: 100.000 VND', 'iz-tour-engine'), esc_html($current_account_no)); ?>
                        </p>
                    <?php else : ?>
                        <p style="color: #d63638;"><?php esc_html_e('Vui lòng nhập và lưu Số Tài Khoản để hiển thị mã VietQR.', 'iz-tour-engine'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Builds a certified VietQR QuickLink image URL.
     *
     * WHY: NAPAS 247 specification requires clean URL query parameters without
     * special characters to prevent bank mobile app scanning parsing errors.
     *
     * @param int    $amount       Payment amount in VND.
     * @param string $order_code   Booking identifier / memo.
     * @param string $bank_bin     Optional override for bank BIN.
     * @param string $account_no   Optional override for account number.
     * @param string $account_name Optional override for account name.
     * @return string Fully qualified image URL.
     */
    public static function build_qr_url(
        int $amount,
        string $order_code,
        string $bank_bin = '',
        string $account_no = '',
        string $account_name = ''
    ): string {
        $bin  = !empty($bank_bin) ? $bank_bin : (string) get_option(self::OPTION_BANK_BIN, '970422');
        $acc  = !empty($account_no) ? $account_no : (string) get_option(self::OPTION_ACCOUNT_NO, '');
        $name = !empty($account_name) ? $account_name : (string) get_option(self::OPTION_ACCOUNT_NAME, '');

        if (empty($acc)) {
            return '';
        }

        // Clean and normalize transfer description: max 25 chars alphanumeric
        $clean_code = self::clean_order_info($order_code);

        $params = [
            'amount'      => max(0, $amount),
            'addInfo'     => $clean_code,
            'accountName' => trim($name),
        ];

        return sprintf(
            'https://img.vietqr.io/image/%s-%s-%s.png?%s',
            rawurlencode($bin),
            rawurlencode($acc),
            self::DEFAULT_TEMPLATE,
            http_build_query($params)
        );
    }

    /**
     * Normalizes order code into NAPAS bank reference format.
     *
     * WHY: Vietnamese banking gateways reject special characters, accents,
     * and descriptions exceeding standard length limits.
     *
     * @param string $raw_code Raw reference text.
     * @return string Sanitized alphanumeric string.
     */
    public static function clean_order_info(string $raw_code): string
    {
        // Remove accents
        $transliterated = remove_accents($raw_code);
        // Replace non-alphanumeric with nothing
        $alphanumeric = preg_replace('/[^A-Za-z0-9]/', '', $transliterated) ?? '';
        // Limit length to 25 characters
        return strtoupper(substr($alphanumeric, 0, 25));
    }
}
