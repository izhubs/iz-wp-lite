# iz-wp-lite

> **The Ultra-Lightweight, AI-Native 12-Factor WordPress Starter.**  
> Chạy WordPress hoàn chỉnh trên VPS 512MB RAM (~45MB Idle RAM), tích hợp Caddy tự động hóa SSL, cơ chế cơ sở dữ liệu kép (SQLite / MariaDB) và sẵn sàng cho kỷ nguyên Vibe Coding / AI Agents.

---

## 1. Tại Sao Lại Là iz-wp-lite? (The Problem & The Why)

WordPress truyền thống rất mạnh mẽ nhưng đi kèm gánh nặng kỹ thuật hơn 20 năm:
- **Tốn kém tài nguyên:** Ngăn xếp LAMP (Linux, Apache, MySQL, PHP) tiêu tốn 400MB–650MB RAM tĩnh, buộc người dùng phải thuê VPS từ 1GB–2GB RAM ($6–$15/tháng) dù lượng truy cập chỉ vài trăm lượt/ngày.
- **Rủi ro bảo mật tệp cấu hình:** Tệp `wp-config.php` nằm chung thư mục với mã nguồn công khai, dễ bị khai thác khi cấu hình sai web server.
- **Bất tiện khi quản lý Git:** Dữ liệu hình ảnh tải lên (`uploads/`) và mã nguồn Core bị trộn lẫn vào nhau, khiến việc deploy tự động (CI/CD) thường xuyên gặp lỗi xung đột.

**iz-wp-lite giải quyết triệt để các vấn đề này:**
- **Mức RAM tĩnh ~45MB:** Nhờ cơ chế SQLite nhúng trực tiếp và Caddy Server viết bằng Go, hệ thống chạy mượt mà trên **VPS 512MB RAM ($3.5/tháng)**.
- **Bảo mật 12-Factor App:** Web Root cô lập tại thư mục `web/`. File `.env`, cấu hình hệ thống và thư viện `vendor/` nằm hoàn toàn ngoài tầm quét của web server.
- **Không tốn dung lượng ổ cứng:** Tích hợp sẵn `s3-uploads` đẩy ảnh trực tiếp sang Cloudflare R2 (10GB miễn phí, băng thông tải ra $0).

---

## 2. Ngăn Xếp Công Nghệ (Technical Stack)

| Tầng kiến trúc | Công nghệ sử dụng | Giá trị cốt lõi |
|---|---|---|
| **Cấu trúc dự án** | [Roots Bedrock](https://roots.io/bedrock/) | Cấu trúc 12-Factor chuẩn mực, quản lý package qua Composer và `wpackagist.org`. |
| **Cơ sở dữ liệu kép** | SQLite 3 (WAL Mode) / MariaDB 10.11 | Tự động chuyển đổi qua 1 dòng cấu hình `.env` (`DB_ENGINE=sqlite` hoặc `mysql`). |
| **Web Server & SSL** | Caddy v2 (Official Alpine Binary) | Nhẹ (~25MB RAM), tự động cấp và gia hạn SSL Let's Encrypt / ZeroSSL 100% không cần Certbot. |
| **Runtime PHP** | PHP 8.3-FPM (Alpine Linux) | Bộ điều phối `ondemand`, giới hạn 3 worker, bộ nhớ đệm OPcache 64MB. |
| **Lưu trữ Media** | Cloudflare R2 + `humanmade/s3-uploads` | Phân tán hình ảnh qua CDN biên mạng, VPS không tốn dung lượng đĩa cho media. |
| **Hạ tầng triển khai** | `izDeploy` / Docker Compose | Tích hợp sẵn hợp đồng PaaS `.agent/izdeploy.json`, deploy trong 2–5 giây qua kỹ thuật Sub-second swap. |

---

## 3. Sẵn Sàng Cho Kỷ Nguyên AI Agents & Vibe Coding

`iz-wp-lite` được thiết kế từ gốc để làm việc liền mạch với các AI Coding IDE hiện đại (**Cursor, Claude Code, Antigravity, Windsurf**):

- **Không cần nhớ lệnh phức tạp:** Cấu trúc dự án minh bạch với các tệp hướng dẫn rõ ràng giúp AI Agent hiểu toàn bộ ngữ cảnh hệ thống.
- **Thêm tính năng bằng ngôn ngữ tự nhiên:** Bạn chỉ cần ra lệnh cho AI Agent: *"Thêm plugin Rank Math SEO"* hoặc *"Cập nhật giao diện trang chủ"*, AI sẽ tự động cập nhật `composer.json`, kiểm tra syntax và commit sạch sẽ.
- **Hợp đồng triển khai tự động (`.agent/izdeploy.json`):** AI Agent có thể trực tiếp thực thi lệnh deploy lên máy chủ từ xa mà không sợ làm sập hệ thống hay cấu hình sai cổng mạng.

---

## 4. Dành Cho Ai? (Who It's For)

- **Doanh nghiệp B2B & Dịch vụ địa phương:** Xây dựng Landing Page, Website giới thiệu công ty đạt điểm Google PageSpeed 98–100 với chi phí vận hành máy chủ gần như bằng 0.
- **Content Creators, Bloggers, SEO Specialists:** Các trang tin tức, blog chuyên môn cần thời gian tải trang nhanh, tự động có Sitemap XML, Table of Contents và tối ưu AEO/GEO.
- **Solo Developers & Agencies (như `iz-web`):** Cần chuẩn hóa quy trình xuất bản website hàng loạt bằng Git/Docker, quản lý tập trung và loại bỏ hoàn toàn các sự cố "lỗi trắng trang khi bấm update plugin".

> **Lưu ý:** Nếu bạn cần làm website thương mại điện tử phức tạp (WooCommerce) với hàng nghìn đơn hàng mỗi ngày, hãy chuyển sang chế độ `DB_ENGINE=mysql` và sử dụng máy chủ có tối thiểu 2GB RAM.

---

## 5. Khởi Chạy Nhanh Trong 3 Bước (Quickstart)

### Bước 1: Sao chép dự án và thiết lập môi trường
```bash
git clone https://github.com/izhubs/iz-wp-lite.git my-site && cd my-site
copy .env.example .env
```

### Bước 2: Khởi chạy container
```bash
docker compose -f docker/docker-compose.yml up -d --build
```

### Bước 3: Hoàn tất cài đặt
Mở trình duyệt tại `http://localhost:8080` để hoàn tất cấu hình WordPress ban đầu trong 1 phút.

---

## 6. Cơ Chế Chuyển Đổi Dual-Engine (SQLite / MySQL)

Thay đổi giá trị trong tệp `.env`:

* **Chế độ SQLite (Mặc định - Siêu nhẹ, RAM ~45MB):**
  ```dotenv
  DB_ENGINE=sqlite
  ```
  Hệ thống tự động kích hoạt chế độ Write-Ahead Logging (`PRAGMA journal_mode = WAL`) và cơ chế chống khóa tệp `busy_timeout = 5000ms`.

* **Chế độ MariaDB / MySQL (Khi cần mở rộng giao dịch):**
  ```dotenv
  DB_ENGINE=mysql
  DB_NAME=wp_database
  DB_USER=wp_user
  DB_PASSWORD=secret_password
  DB_HOST=127.0.0.1
  ```
  Tệp drop-in `web/app/db.php` sẽ tự động chuyển quyền xử lý về driver MySQL gốc của WordPress Core.

---

## 7. Bản Quyền & Tôn Trọng Tác Giả Gốc (Credits & License)

- **Mã nguồn khung & cấu hình:** Phát hành theo [Giấy phép MIT](LICENSE).
- **Mã nguồn WordPress Core & Plugins:** Tuân thủ giấy phép GNU General Public License v2 (GPLv2).
- **Trân trọng ghi nhận (Acknowledgements):** Kiến trúc khung của dự án được kế thừa và phát triển dựa trên [Roots Bedrock](https://roots.io/bedrock/) của nhóm phát triển **Roots.io**. Trân trọng đóng góp của cộng đồng Roots cho hệ sinh thái WordPress hiện đại.
- **Tuyên bố miễn trừ nhãn hiệu (Trademark Disclaimer):** WordPress là nhãn hiệu đã đăng ký của WordPress Foundation. Dự án `iz-wp-lite` là một sáng kiến mã nguồn mở độc lập, không liên kết, không được tài trợ và không đại diện cho WordPress Foundation.
