<?php
/**
 * Template Name: Trang Dịch Vụ Du Lịch
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

<?php iz_breadcrumbs(); ?>

<main id="primary" class="site-main iz-service-page">
    <div class="iz-container">
        <header class="iz-service-header">
            <h1 class="iz-service-title"><?php the_title(); ?></h1>
            <p class="iz-service-lead">Dịch vụ lữ hành toàn diện - Cam kết chất lượng, giải pháp tối ưu và chi phí hợp lý nhất cho mọi nhu cầu di chuyển & nghỉ dưỡng.</p>
        </header>

        <!-- Service Detail Cards Grid -->
        <div class="iz-service-details-grid">
            <!-- 1. Dịch Vụ Visa -->
            <section class="iz-service-card" id="visa">
                <div class="iz-service-card-icon">✈️</div>
                <h2 class="iz-service-card-title">Dịch Vụ Visa Chuyên Nghiệp</h2>
                <p class="iz-service-card-desc">Gonatour chuyên tư vấn và thụ lý hồ sơ xin visa các thị trường khó: Mỹ, Canada, Châu Âu (Schengen), Úc, Nhật Bản, Hàn Quốc, Đài Loan.</p>
                <ul class="iz-service-features">
                    <li>✓ Thẩm định hồ sơ miễn phí, đưa ra giải pháp tăng tỷ lệ đậu trên 99%.</li>
                    <li>✓ Hỗ trợ dịch thuật công chứng tư pháp chuẩn xác, bảo mật thông tin.</li>
                    <li>✓ Hướng dẫn luyện phỏng vấn 1-1 tự tin, đúng trọng tâm câu hỏi của Lãnh sự quán.</li>
                    <li>✓ Giao nhận kết quả visa tận nơi nhanh chóng, an toàn.</li>
                </ul>
                <div class="iz-service-card-action">
                    <a href="tel:0784849849" class="iz-btn-primary">Tư Vấn Visa Ngay: 0784 849 849</a>
                </div>
            </section>

            <!-- 2. Khách Sạn & Combo -->
            <section class="iz-service-card" id="combo">
                <div class="iz-service-card-icon">🏨</div>
                <h2 class="iz-service-card-title">Combo Khách Sạn & Vé Máy Bay</h2>
                <p class="iz-service-card-desc">Trải nghiệm du lịch tự do, linh hoạt lịch trình với các gói combo vé máy bay khứ hồi và phòng nghỉ resort 4-5 sao tại Phú Quốc, Đà Nẵng, Nha Trang, Quy Nhơn.</p>
                <ul class="iz-service-features">
                    <li>✓ Hợp tác trực tiếp chuỗi resort danh tiếng: Vinpearl, FLC, Sun World...</li>
                    <li>✓ Giá tốt hơn đặt phòng riêng lẻ từ 20% đến 35%.</li>
                    <li>✓ Tặng kèm đưa đón sân bay và buffet sáng tiêu chuẩn quốc tế.</li>
                    <li>✓ Hỗ trợ nâng hạng phòng hoặc hoàn đổi vé máy bay linh hoạt.</li>
                </ul>
                <div class="iz-service-card-action">
                    <a href="tel:0784849849" class="iz-btn-primary">Đặt Combo Nghỉ Dưỡng: 0784 849 849</a>
                </div>
            </section>

            <!-- 3. Thuê Xe Du Lịch -->
            <section class="iz-service-card" id="car-rental">
                <div class="iz-service-card-icon">🚐</div>
                <h2 class="iz-service-card-title">Thuê Xe Du Lịch 4 - 45 Chỗ</h2>
                <p class="iz-service-card-desc">Hệ thống xe đời mới (Sedan 4 chỗ, SUV 7 chỗ, Ford Transit 16 chỗ, Thaco Universe 29 - 45 chỗ) sang trọng, tiện nghi, bảo dưỡng định kỳ.</p>
                <ul class="iz-service-features">
                    <li>✓ Đội ngũ tài xế lịch thiệp, thông thạo địa hình, tuân thủ tốc độ.</li>
                    <li>✓ Báo giá trọn gói xăng xe, cầu đường, bến bãi, không phát sinh chi phí.</li>
                    <li>✓ Phục vụ đưa đón sân bay, cưới hỏi, công tác và tour đường dài.</li>
                    <li>✓ Hợp đồng rõ ràng, hóa đơn VAT đầy đủ theo quy định pháp luật.</li>
                </ul>
                <div class="iz-service-card-action">
                    <a href="tel:0784849849" class="iz-btn-primary">Báo Giá Thuê Xe: 0784 849 849</a>
                </div>
            </section>

            <!-- 4. Teambuilding & Sự Kiện -->
            <section class="iz-service-card" id="teambuilding">
                <div class="iz-service-card-icon">🎯</div>
                <h2 class="iz-service-card-title">Tổ Chức Teambuilding & Company Trip</h2>
                <p class="iz-service-card-desc">Giải pháp gắn kết văn hóa doanh nghiệp trọn gói: Lên ý tưởng concept độc quyền, kịch bản trò chơi bùng nổ, âm thanh sân khấu và đêm Gala Dinner ấn tượng.</p>
                <ul class="iz-service-features">
                    <li>✓ Đội ngũ MC, đạo diễn trò chơi năng lượng và chuyên nghiệp.</li>
                    <li>✓ Đạo cụ game đồ sộ, an toàn, mang tính tương tác đồng đội cao.</li>
                    <li>✓ Dịch vụ quay phim flycam, chụp ảnh sự kiện truyền thông nội bộ.</li>
                    <li>✓ Tối ưu ngân sách cho đoàn từ 30 đến 1.000 khách.</li>
                </ul>
                <div class="iz-service-card-action">
                    <a href="tel:0784849849" class="iz-btn-primary">Nhận Kịch Bản Teambuilding: 0784 849 849</a>
                </div>
            </section>
        </div>

        <!-- Consultation Form -->
        <section class="iz-service-consult-form-box">
            <h2 class="iz-consult-title">Đăng Ký Tư Vấn Dịch Vụ Miễn Phí</h2>
            <p class="iz-consult-desc">Để lại thông tin nhu cầu của bạn, chuyên viên Gonatour sẽ liên hệ tư vấn và gửi báo giá chi tiết trong vòng 10 phút.</p>

            <form class="iz-consult-form" onsubmit="event.preventDefault(); alert('Cảm ơn bạn! Chuyên viên Gonatour sẽ liên hệ lại ngay.');">
                <div class="iz-form-grid-3">
                    <input type="text" placeholder="Họ và tên của bạn *" required />
                    <input type="tel" placeholder="Số điện thoại di động *" required />
                    <select required>
                        <option value="">-- Dịch vụ quan tâm --</option>
                        <option value="visa">Dịch vụ Visa</option>
                        <option value="combo">Combo Vé & Khách sạn</option>
                        <option value="car">Thuê xe du lịch</option>
                        <option value="teambuilding">Teambuilding & Gala</option>
                    </select>
                </div>
                <textarea rows="3" placeholder="Nội dung chi tiết nhu cầu: Số lượng khách, thời gian dự kiến, điểm đến..."></textarea>
                <button type="submit" class="iz-btn-primary" style="margin-top: 12px; width: 100%;">GỬI YÊU CẦU TƯ VẤN</button>
            </form>
        </section>
    </div>
</main>

<?php
get_footer();
