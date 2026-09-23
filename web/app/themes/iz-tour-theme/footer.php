<?php
/**
 * Footer template for iZ Tour Theme.
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

<footer class="iz-site-footer">
    <!-- Trust Commitments Bar -->
    <section class="iz-trust-bar" aria-label="<?php esc_attr_e('Cam kết thương hiệu', 'iz-tour-theme'); ?>">
        <div class="iz-container">
            <div class="iz-trust-grid">
                <div class="iz-trust-card">
                    <div class="iz-trust-icon">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/>
                        </svg>
                    </div>
                    <div class="iz-trust-content">
                        <strong class="iz-trust-title">Giá Tốt Nhất</strong>
                        <p class="iz-trust-desc">Cam kết giá cạnh tranh và nhiều ưu đãi độc quyền.</p>
                    </div>
                </div>

                <div class="iz-trust-card">
                    <div class="iz-trust-icon">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
                            <path d="M20 15.5c-1.25 0-2.45-.2-3.57-.57a1.02 1.02 0 00-1.02.24l-2.2 2.2a15.045 15.045 0 01-6.59-6.59l2.2-2.21a.96.96 0 00.25-1A11.36 11.36 0 018.5 4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1 9.39 9.39 17 17 21 21 .55 0 1-.45 1-1v-3.5c0-.55-.45-1-1-1z"/>
                        </svg>
                    </div>
                    <div class="iz-trust-content">
                        <strong class="iz-trust-title">Hỗ Trợ 24/7</strong>
                        <p class="iz-trust-desc">Chuyên viên tư vấn nhiệt tình, chu đáo mọi lúc mọi nơi.</p>
                    </div>
                </div>

                <div class="iz-trust-card">
                    <div class="iz-trust-icon">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                        </svg>
                    </div>
                    <div class="iz-trust-content">
                        <strong class="iz-trust-title">Giấy Phép LHQT</strong>
                        <p class="iz-trust-desc">GP số 79-1068/2019/TCDL-GP LHQT an tâm pháp lý tuyệt đối.</p>
                    </div>
                </div>

                <div class="iz-trust-card">
                    <div class="iz-trust-icon">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <div class="iz-trust-content">
                        <strong class="iz-trust-title">100% Uy Tín</strong>
                        <p class="iz-trust-desc">Hơn 10 năm kinh nghiệm đồng hành cùng hàng vạn du khách.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main 4-Column Footer -->
    <div class="iz-footer-main">
        <div class="iz-container">
            <div class="iz-footer-grid">
                <!-- Col 1: Legal & Company -->
                <div class="iz-footer-col">
                    <div class="iz-footer-brand">
                        <strong class="iz-brand-title">CÔNG TY CỔ PHẦN DU LỊCH GONATOUR</strong>
                    </div>
                    <p class="iz-footer-text">
                        Thương hiệu lữ hành uy tín hàng đầu tại Việt Nam, chuyên tổ chức các tour du lịch trong nước, quốc tế và dịch vụ visa - teambuilding chất lượng cao.
                    </p>
                    <ul class="iz-legal-list">
                        <li><strong>Số GPKD:</strong> 0315729381 do Sở KH&ĐT TP.HCM cấp</li>
                        <li><strong>Giấy phép LHQT:</strong> 79-1068/2019/TCDL-GP LHQT</li>
                    </ul>
                    <div class="iz-bocongthuong-badge">
                        <span class="iz-bct-icon">🛡️</span>
                        <span>Đã đăng ký và thông báo Bộ Công Thương</span>
                    </div>
                </div>

                <!-- Col 2: Locations & Contact -->
                <div class="iz-footer-col">
                    <h3 class="iz-footer-heading">Văn Phòng & Liên Hệ</h3>
                    <ul class="iz-contact-list">
                        <li>
                            <strong class="iz-contact-label">Trụ sở TP.HCM:</strong>
                            <span>Tầng 6, 63A Nam Kỳ Khởi Nghĩa, P. Bến Nghé, Quận 1, TP. Hồ Chí Minh</span>
                        </li>
                        <li>
                            <strong class="iz-contact-label">Văn phòng Hà Nội:</strong>
                            <span>Số 18 Phố Chùa Hà, P. Quan Hoa, Cầu Giấy, Hà Nội</span>
                        </li>
                        <li>
                            <strong class="iz-contact-label">Hotline 24/7:</strong>
                            <a href="tel:0784849849" class="iz-hotline-highlight">0784 849 849</a>
                        </li>
                        <li>
                            <strong class="iz-contact-label">Email hỗ trợ:</strong>
                            <a href="mailto:info@gonatour.vn">info@gonatour.vn</a>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Policy & Support -->
                <div class="iz-footer-col">
                    <h3 class="iz-footer-heading">Chính Sách & Hướng Dẫn</h3>
                    <ul class="iz-footer-links">
                        <li><a href="<?php echo esc_url(home_url('/huong-dan-dat-tour/')); ?>">Hướng dẫn đặt tour trực tuyến</a></li>
                        <li><a href="<?php echo esc_url(home_url('/phuong-thuc-thanh-toan/')); ?>">Phương thức thanh toán & VietQR</a></li>
                        <li><a href="<?php echo esc_url(home_url('/chinh-sach-bao-mat/')); ?>">Chính sách bảo mật thông tin</a></li>
                        <li><a href="<?php echo esc_url(home_url('/dieu-khoan-hoan-huy/')); ?>">Điều kiện hoàn hủy & bảo hiểm</a></li>
                        <li><a href="<?php echo esc_url(home_url('/cau-hoi-thuong-gap/')); ?>">Câu hỏi thường gặp (FAQ)</a></li>
                        <li><a href="<?php echo esc_url(home_url('/tuyen-dung/')); ?>">Tuyển dụng nhân sự</a></li>
                    </ul>
                </div>

                <!-- Col 4: Bank Account & Newsletter -->
                <div class="iz-footer-col">
                    <h3 class="iz-footer-heading">Tài Khoản Ngân Hàng</h3>
                    <div class="iz-bank-box">
                        <div class="iz-bank-item">
                            <strong>MBBank (Ngân hàng Quân Đội)</strong>
                            <p>STK: <code>0784849849</code></p>
                            <p>Chủ TK: CONG TY CP DU LICH GONATOUR</p>
                        </div>
                        <div class="iz-bank-item">
                            <strong>Vietcombank (Ngoại thương VN)</strong>
                            <p>STK: <code>1015886888</code> - CN Tân Định</p>
                            <p>Chủ TK: CONG TY CP DU LICH GONATOUR</p>
                        </div>
                    </div>

                    <div class="iz-newsletter-box">
                        <h4 class="iz-newsletter-title">Đăng Ký Nhận Ưu Đãi</h4>
                        <form class="iz-newsletter-form" onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã đăng ký nhận tin khuyến mãi!');">
                            <input type="email" placeholder="Nhập email của bạn..." required aria-label="Email nhận tin" />
                            <button type="submit">Gửi</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="iz-footer-bottom">
        <div class="iz-container iz-bottom-inner">
            <p class="iz-copyright">
                &copy; <?php echo esc_html(gmdate('Y')); ?> GONATOUR. Toàn bộ bản quyền thuộc về Công ty CP Du lịch Gonatour. Thiết kế và tối ưu bởi iZdigi.
            </p>
            <div class="iz-payment-methods">
                <span class="iz-pay-badge">VietQR</span>
                <span class="iz-pay-badge">Napas 247</span>
                <span class="iz-pay-badge">Visa/Mastercard</span>
                <span class="iz-pay-badge">Chuyển Khoản</span>
            </div>
        </div>
    </div>
</footer>

<!-- Mobile Sticky Bottom Call Bar -->
<div class="iz-mobile-sticky-bar" id="iz-mobile-sticky-bar">
    <a href="tel:0784849849" class="iz-sticky-btn iz-sticky-call">
        <svg viewBox="0 0 20 20" width="18" height="18" fill="currentColor">
            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 4V3z"/>
        </svg>
        <span>Gọi 0784 849 849</span>
    </a>
    <a href="<?php echo esc_url(home_url('/tours/')); ?>" class="iz-sticky-btn iz-sticky-book">
        <svg viewBox="0 0 20 20" width="18" height="18" fill="currentColor">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
        </svg>
        <span>Tìm & Đặt Tour</span>
    </a>
</div>

<?php wp_footer(); ?>
</body>
</html>
