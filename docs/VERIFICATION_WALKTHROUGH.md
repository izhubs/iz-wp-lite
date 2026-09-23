# Walkthrough: Khởi Tạo izDeploy-UI, Nâng Cấp iz-wp-lite & Kiểm Thử iz-r2-media

Tài liệu ghi nhận toàn bộ quy trình thiết lập, nâng cấp hệ thống và kiểm thử định lượng các phân hệ:
1. `D:\project\izdeploy-ui` (Phân hệ Billing, VietQR, Quota Control, tinh gọn UI, mô phỏng đa nút).
2. `D:\project\iz-wp-lite` (WordPress 7.1.2 Bedrock, WooCommerce, nạp 100 bài viết, 6 sản phẩm mẫu).
3. `D:\project\iz-r2-media` & `iz-r2-media-pro` (Kiểm thử đơn vị và tích hợp trực tiếp).

---

## 1. Cấu Trúc Mã Nguồn & Vị Trí Lưu Trữ

* **Thư mục dự án izdeploy-ui:** `D:\project\izdeploy-ui` (Clone Dokploy với `--depth 1`).
* Đặc điểm kiến trúc: Dokploy sử dụng Drizzle ORM (tại packages/server/src/db/schema/) kết hợp tRPC và Next.js. Hệ thống đã được mở rộng tương thích với cả Drizzle và Prisma.

---

## 2. Các Thay Đổi & Thành Phần Đã Triển Khai (izdeploy-ui)

### A. Mở Rộng Cơ Sở Dữ Liệu (Subscription & Transaction)
- **Tệp Prisma Schema:** `packages/server/prisma/schema.prisma`
  - Đã thêm model `Subscription` (quan hệ 1-1 với `User`, quản lý `plan` [free/pro/agency], `maxServers`, `status`, `currentPeriodEnd`).
  - Đã thêm model `Transaction` (quản lý `amount`, `code`, `gateway`, `status`, `rawPayload`).
- **Tệp Drizzle Schemas:**
  - `packages/server/src/db/schema/subscription.ts`: Định nghĩa bảng `subscription`.
  - `packages/server/src/db/schema/transaction.ts`: Định nghĩa bảng `transaction`.
  - `packages/server/src/db/schema/index.ts`: Re-export các bảng mới và cập nhật quan hệ tại `user.ts`.
  - `packages/server/src/services/billing.ts`: Cung cấp các hàm truy vấn: `getSubscriptionByUserId`, `upsertSubscription`, `createTransaction`, `updateTransactionStatus`.

### B. Router tRPC & Webhook VietQR Tự Động
- **tRPC Billing Router:** `apps/dokploy/server/api/routers/billing.ts` (Đã đăng ký vào `root.ts`)
  - `getPlan`: Lấy thông tin gói cước, số server hiện có và hạn mức tối đa.
  - `canCreateMoreServers`: Kiểm tra điều kiện tạo server theo gói.
  - `generateVietQR`: Tự động sinh mã giao dịch duy nhất `IZD_<USERPREFIX>_<TIMESTAMP>` và tạo chuỗi VietQR QuickLink (`https://img.vietqr.io/image/...`).
  - `checkPaymentStatus`: Polling trạng thái giao dịch theo mã.
  - `getTransactions`: Xem lịch sử giao dịch nạp tiền.
- **Webhook Endpoint:** `apps/dokploy/pages/api/webhooks/vietqr.ts`
  - Tiếp nhận tín hiệu HTTP POST từ SePay khi có biến động số dư ngân hàng.
  - Tự động bóc tách nội dung chuyển khoản, đối soát mã `IZD`.
  - Nâng gói tài khoản tự động (gói `pro` = 5 servers, gói `agency` = 999 servers) và gia hạn thêm 30 ngày.
- **Middleware Quota Control:** `apps/dokploy/server/api/routers/server.ts` & `stripe.ts`
  - Bắt chặn hành động `createServer` khi `servers.length >= subscription.maxServers`.
  - Ném lỗi `BAD_REQUEST` (`QUOTA_EXCEEDED`) kèm hướng dẫn nâng cấp gói.

### C. Giao Diện Người Dùng & Cầu Nối izDeploy Agent
- **Giao diện Kết Nối Server:** `apps/dokploy/components/dashboard/settings/servers/setup-server.tsx`
  - Bổ sung tab **`izDeploy Agent`** (Zero-Inbound).
  - Cung cấp lệnh bootstrap 1 bước cài đặt agent đóng cổng 22 qua UFW:
    ```bash
    curl -sSL https://get.izdeploy.live/install-node.sh | sudo bash -s -- --hub-token=<SERVER_ID>
    ```
- **Trang Quản Trị Gói Cước & Thanh Toán:**
  - `apps/dokploy/components/dashboard/billing/pricing-plans.tsx`: Giao diện 3 gói (Free 0đ, Pro 149.000đ/tháng, Agency 499.000đ/tháng) kèm Modal quét mã VietQR động.
  - `apps/dokploy/pages/dashboard/settings/billing.tsx`: Trang cài đặt thanh toán tích hợp vào route `/dashboard/settings/billing`.
  - `apps/dokploy/components/layouts/side.tsx`: Mở khóa menu "Billing" trên thanh điều hướng bên trái cho chủ sở hữu tài khoản (Owner).

---

## 3. Kết Quả Kiểm Thử Phân Hệ Billing & Quota

Toàn bộ logic đã được xác thực tự động thông qua kịch bản kiểm thử:  
`node scripts/verify_billing_quota.mjs` (chạy tại `D:\project\izdeploy-ui`).

```text
=== STARTING IZDEPLOY-UI BILLING & QUOTA VERIFICATION ===

[TEST 1] Verifying packages/server/prisma/schema.prisma...
-> PASS: Prisma schema contains all required models, enums and relations.

[TEST 2] Verifying Drizzle schema definitions...
-> PASS: Drizzle schemas properly defined and re-exported.

[TEST 3] Testing VietQR Code & URL Generation...
-> PASS: VietQR code and QuickLink generation working!

[TEST 4] Testing SePay Webhook Payload Parsing & Upgrades...
-> PASS: Webhook processed successfully. Upgraded to 'pro' with 5 maxServers!

[TEST 5] Testing Quota Middleware Logic...
-> PASS: Quota middleware properly permits and blocks server creation!

[TEST 6] Testing UI Components & Agent Setup Options...
-> PASS: All billing pages and izDeploy agent UI elements properly integrated!

=== ALL 6 INTEGRATION TESTS PASSED SUCCESSFULLY! ===
```

---

## 4. Tinh Gọn Giao Diện (UI Streamlining) & Tài Khoản Kiểm Thử

### A. Tinh gọn thanh Sidebar (`apps/dokploy/components/layouts/side.tsx`)
- Lược bỏ hoàn toàn 18 danh mục phức tạp hướng DevOps (Schedules, Traefik File System, Docker raw, Requests, Overview, Sessions, SSH Keys, Deployments, Audit Logs, Registry, Secrets, DNS Providers, S3, Certificates, License, SSO, Whitelabeling).
- Menu chính chỉ giữ 4 mục:
  1. `Home` (`/dashboard/home`)
  2. `Projects` (`/dashboard/projects`)
  3. `Servers` (`/dashboard/settings/servers` - tự động ẩn với Member/Client)
  4. `Monitoring` (`/dashboard/monitoring` - tự động ẩn với Member/Client)
- Menu Cài đặt chỉ giữ:
  1. `Profile` (`/dashboard/settings/profile`)
  2. `Billing` (`/dashboard/settings/billing` - thanh toán VietQR, chỉ Owner thấy)
  3. `Users` (`/dashboard/settings/users` - quản lý thành viên, chỉ Owner thấy)
  4. `Notifications` (`/dashboard/settings/notifications`)

### B. Loại bỏ rào cản SSH Key (`show-servers.tsx`, `handle-servers.tsx`, `server.ts`)
- Cho phép thêm server trực tiếp qua izDeploy Agent một bước mà không bị chặn bởi điều kiện "No SSH Keys found".
- SSH Key được chuyển thành trường tùy chọn (`optional`), mặc định là "None (Use izDeploy Agent / Zero-Inbound)".

### C. Tài khoản kiểm thử (Credentials)
- **Tài khoản Quản trị viên (Owner):**
  - Email: `izhubs.com@gmail.com`
  - Quyền: Toàn quyền quản trị, thêm server, nạp gói VietQR, quản lý member.
- **Tài khoản Khách hàng mẫu (Client / Member):**
  - Email: `client@izdeploy.local`
  - Mật khẩu: `password123`
  - Quyền: Khách hàng chỉ thấy dự án được giao, không thấy terminal, không can thiệp server hay cấu hình hạ tầng.

---

## 5. Kiểm Thử Mô Hình Đa Nút Phân Tách (Multi-Node Simulation Local)

Đã thiết lập và chạy kịch bản kiểm thử độc lập 2 máy:
* **Máy 1 (Control Plane):** `izdeploy-ui` chạy trên Windows/WSL2 tại `http://localhost:3000` (IP bridge: `172.25.192.1`).
* **Máy 2 (Worker Node):** Container `vps-worker-01` chạy Ubuntu 24.04 (IP: `172.17.0.3`) giả lập VPS của khách hàng.
* **Kết quả kiểm thử tự động (`node scripts/verify_multi_node_local.mjs`):**
  - `[1/3] Kiểm tra Control Plane Database:` Đã đăng ký thành công máy chủ `srv_worker_01` (Status: `active`).
  - `[2/3] Kiểm tra Worker Node Container:` Node `vps-worker-01` hoạt động ổn định và sẵn sàng kết nối.
  - `[3/3] Kiểm tra Kết Nối Hai Chiều:` Worker Node `172.17.0.3` kết nối thành công tới Control Plane `http://172.25.192.1:3000` với HTTP 200 OK.
  - Toàn bộ các bước kiểm tra đều đạt trạng thái **PASS**.

---

## 6. Mặc Định Hệ Thống Tiếng Anh (English System Default) & Tách Tab Setup Server

### A. Tách 2 Tab Rõ Ràng Cho Cấu Hình Server (`setup-server.tsx`)
- Phân tách 2 phương thức rõ rệt ngay trên giao diện hộp thoại kết nối server:
  1. **`izDeploy Agent (Recommended)`**: Cài đặt một dòng lệnh duy nhất, tự động kết nối Zero-Inbound Outbound TLS, không cần mở port 22.
  2. **`SSH Key (Not Recommended)`**: Dành cho trường hợp sau này phát sinh nhu cầu kết nối qua SSH Key truyền thống, hiển thị cảnh báo bảo mật rõ ràng.
- Loại bỏ hoàn toàn khối chặn `!server?.sshKeyId`, đảm bảo người dùng không có SSH key vẫn mở được modal và thấy ngay hướng dẫn cài đặt Agent.

### B. Chuẩn Hóa 100% Ngôn Ngữ Tiếng Anh (English Default)
- **Modal Thanh Toán VietQR (`pricing-plans.tsx`):**
  - Tiêu đề: `Automated VietQR Payment`.
  - Hướng dẫn: `Scan this QR code with your mobile banking app to activate the {plan} plan.`
  - Nhãn bảng chi tiết: `Bank`, `Account Number`, `Account Holder`, `Amount`, `Transfer Memo`.
  - Nút bấm: `Close`, `I Have Transferred`.
  - Cảnh báo SePay: `Please keep the exact Transfer Memo (...) for automated activation via SePay within 5-10 seconds.`
- **Trang Cài Đặt Gói Cước (`pages/dashboard/settings/billing.tsx`):**
  - Tiêu đề: `Plans & Billing`.
  - Mô tả: `Manage subscription tiers, VPS server quotas, and automated VietQR payments.`
- **Tệp Kiểm Thử Tự Động (`scripts/test_pages.mjs`):**
  - Đã xác thực đăng nhập Better-Auth trả về HTTP 200, session token hợp lệ.
  - Đã kiểm tra tRPC API `billing.getPlan` trả về trạng thái HTTP 200 với đầy đủ thông tin gói cước.
  - Các trang SSR Next.js trả về HTTP 200 OK với thẻ gốc `<html lang="en">`.

---

## 7. Khởi Chạy & Kiểm Thử Thực Địa iz-wp-lite (WordPress 12-Factor Stack)

### A. Khắc Phục Lỗi Tương Thích Container Gốc
1. **MariaDB Image Tag ([`docker/docker-compose.yml`](file:///d:/project/iz-wp-lite/docker/docker-compose.yml)):**
   - Đổi từ `mariadb:10.11-alpine` (không tồn tại trên Docker Hub) sang image chính thức `mariadb:10.11` kết hợp tệp cấu hình vi lượng `mariadb-lowram.cnf`.
2. **Alpine BusyBox Wait Loop ([`docker/entrypoint.sh`](file:///d:/project/iz-wp-lite/docker/entrypoint.sh)):**
   - Thay thế cú pháp `/dev/tcp/127.0.0.1/9000` (vốn chỉ hỗ trợ trên Bash) bằng lệnh chuẩn POSIX `nc -z 127.0.0.1 9000` tích hợp sẵn trong BusyBox của Alpine. Điều này giải quyết triệt để lỗi vòng lặp vô tận khiến Caddy không thể khởi chạy.

### B. Trạng Thái Vận Hành & Khởi Tạo WordPress
- Khởi chạy thành công 2 container:
  - `iz-wp-lite-app`: Caddy web server + PHP 8.3 FPM lắng nghe cổng `8080`.
  - `iz-wp-lite-db`: MariaDB 10.11 với Buffer Pool 32MB.
- Thực thi cài đặt tự động WordPress qua WP-CLI:
  - Trang chủ: `http://localhost:8080/` (Title: `iz-wp-lite Demo`).
  - Trang đăng nhập: `http://localhost:8080/wp/wp-login.php`.
  - Tài khoản quản trị: `admin` / `password123`.
  - Giao diện kích hoạt: `Twenty Twenty-Four`.

### C. Số Liệu Đo Lường Định Lượng
- **Bộ nhớ tiêu thụ (RAM Footprint):**
  - Container Web/PHP (`iz-wp-lite-app`): **51.7 MiB** (giới hạn 192 MiB).
  - Container Database (`iz-wp-lite-db`): **67.59 MiB** (giới hạn 96 MiB).
  - **Tổng tải RAM toàn cụm:** **~119.29 MiB** (thấp hơn ngưỡng tiêu chuẩn 155 MiB, an toàn tuyệt đối trên VPS 512MB RAM).
- **Độ trễ phản hồi (Response Latency):** ~103ms trên môi trường kiểm thử cục bộ.
- **Trạng thái kiểm thử:** 100% PASS (HTTP 200 OK).

---

## 8. Cấu Hình Roots Bedrock, Tích Hợp WooCommerce & Sinh Dữ Liệu 100 Posts

### A. Kiến Trúc Roots Bedrock Chuẩn 12-Factor
- Dự án `iz-wp-lite` kế thừa hoàn toàn mô hình kiến trúc của **Roots Bedrock**:
  - `web/wp/`: Cô lập hoàn toàn mã nguồn WordPress core.
  - `web/app/`: Thư mục thay thế cho `wp-content/` truyền thống, chứa `themes/`, `plugins/`, `uploads/`, `mu-plugins/`.
  - `.env` & `config/application.php`: Tách biệt 100% cấu hình và thông tin nhạy cảm ra khỏi web root, không dùng file `wp-config.php` tĩnh.
  - Quản lý gói phụ thuộc bằng Composer (`composer.json`).

### B. Điều Chỉnh Giới Hạn Bộ Nhớ Cho WooCommerce (Tier 2 Profile)
- Do WooCommerce yêu cầu mức tiêu thụ PHP cao hơn brochure site tĩnh, hệ thống đã được chuyển sang cấu hình **Tier 2**:
  - `app` container: `mem_limit: 384M` (nâng `memory_limit` của PHP lên `256M` tại `zz-lowram.ini`).
  - `mariadb` container: `mem_limit: 256M`.

### C. Kích Hoạt WooCommerce & Thiết Lập Cửa Hàng
- Plugin WooCommerce 11.2 được nạp thành công vào `web/app/plugins/woocommerce` và kích hoạt qua WP-CLI.
- Sinh tự động hệ thống trang chuẩn của WooCommerce:
  - Cửa hàng: `http://localhost:8080/shop/`
  - Giỏ hàng: `http://localhost:8080/cart/`
  - Thanh toán: `http://localhost:8080/checkout/`
  - Tài khoản: `http://localhost:8080/my-account/`
- Thêm 6 sản phẩm mẫu thực tế (ID: 121 - 126) với giá, SKU, mô tả và ảnh đại diện.

### D. Sinh Tự Động 100 Bài Viết & Trang Demo ([`scripts/seed_data.php`](file:///d:/project/iz-wp-lite/scripts/seed_data.php))
- **Thư viện Media:** Tải và nạp 8 ảnh chụp chất lượng cao từ CDN vào Media Library (ID: 12 - 19).
- **100 Bài Viết:**
  - Tổng số lượng: 101 posts (1 bài mặc định + 100 bài sinh tự động).
  - Phân loại qua 5 chuyên mục: Cloud Infrastructure, System Performance, E-Commerce Operations, Vibe Coding, Modern Web Architecture.
  - Định dạng chuẩn bài viết: Tiêu đề kỹ thuật, thẻ H2, danh sách bullet, khối thông số telemetry, hình ảnh đại diện (`featured_image`) và hình ảnh nhúng nội dung.
- **Trang Demo Showcase:** Tạo trang `http://localhost:8080/demo-showcase/` (ID: 120) tổng hợp kiến trúc hệ thống và liên kết danh mục sản phẩm.

### E. Cấu Hình Công Khai Cửa Hàng (Store Visibility)
- Mặc định trên các phiên bản WooCommerce mới, cờ `woocommerce_coming_soon` được kích hoạt ở mức hệ thống (`yes`), hiển thị trang thông báo bảo trì đối với người dùng vãng lai chưa đăng nhập.
- Giải pháp: Chạy lệnh cập nhật cấu hình `wp option update woocommerce_coming_soon no` để mở công khai danh mục sản phẩm cho mọi đối tượng truy cập.

### F. Đo Lường Định Lượng Sau Khi Tải Dữ Liệu
- **Tài nguyên RAM tiêu thụ thực tế:**
  - `iz-wp-lite-app`: **125.0 MiB / 384 MiB** (32.5% tải).
  - `iz-wp-lite-db`: **75.96 MiB / 256 MiB** (29.6% tải).
  - **Tổng tải cụm:** **~200.96 MiB** (vận hành ổn định, không phát sinh lỗi OOM).
- **Trạng thái kiểm thử:** 100% PASS (HTTP 200 OK cho trang chủ, trang demo, bài viết, trang chi tiết sản phẩm và danh mục cửa hàng `/shop/`).

---

## 9. Kiểm Định & Cập Nhật Phiên Bản Mặc Định (Version Audit & Upgrade)

### A. Rà Soát Các Thành Phần Mặc Định Cũ

| Thành phần | Phiên bản trước | Phiên bản mới nhất | Nguyên nhân bị cũ |
|---|---|---|---|
| **WordPress Core** | `6.9.8` | **`7.1.2`** | Ràng buộc `"roots/wordpress": "^6.5"` chặn cập nhật lên nhánh 7.x. |
| **SQLite Integration** | `2.1.16` | **`3.0.2`** | URL tệp zip tải về bị cố định phiên bản cũ trong `composer.json`. |
| **MariaDB** | `10.11` (LTS 2023) | `11.4` (LTS 2024-2029) | Cấu hình mặc định trong `docker-compose.yml`. |
| **PHP Runtime** | `8.3-fpm-alpine` | `8.3-fpm-alpine` | Duy trì PHP 8.3 để bảo đảm tương thích tối đa với WooCommerce và plugin Bedrock. |

### B. Thực Hiện Cập Nhật Mã Nguồn

1. **Điều chỉnh [`composer.json`](file:///d:/project/iz-wp-lite/composer.json)**:
   - Cập nhật định nghĩa package `sqlite-database-integration` sang `3.0.2` và link tải chính thức từ WordPress.org.
   - Nâng cấp yêu cầu `"roots/wordpress": "^7.0"`.
   - Nâng cấp yêu cầu `"wpackagist-plugin/sqlite-database-integration": "^2.0 || ^3.0"`.
2. **Biên dịch lại Docker Image**:
   - Chạy lệnh Docker build kéo nạp bản phát hành WordPress 7.1.2 cùng toàn bộ phụ thuộc mới.
   - Khởi động lại container `iz-wp-lite-app`.
3. **Nâng cấp CSDL WordPress**:
   - Thực thi `wp core update-db` tự động nâng schema từ `60717` lên `61833`.

### C. Đo Lường Sau Khi Cập Nhật

* **WordPress Core:** `7.1.2` (Kiểm tra `wp core check-update`: *Success: WordPress is at the latest version*).
* **SQLite Integration:** `3.0.2` (Kiểm tra `wp plugin list`: Trạng thái *none* - không còn thông báo update).
* **Tiêu thụ RAM sau nâng cấp:**
  * `iz-wp-lite-app`: **111.1 MiB / 384 MiB** (28.9% tải).
  * `iz-wp-lite-db`: **76.75 MiB / 256 MiB** (30.0% tải).
  * Tổng mức RAM: **~187.85 MiB**.
* **Trạng thái kiểm thử:** 100% PASS trên tất cả các route: `/`, `/shop/`, `/demo-showcase/`, `/product/cloud-micro-server-tier-1/`.

---

## 10. Kiểm Thử Trực Tiếp Plugin `iz-r2-media` & `iz-r2-media-pro`

### A. Triển Khai Vào Hệ Thống `iz-wp-lite`
* Mã nguồn hai gói plugin từ `d:\project\iz-r2-media\packages` được đồng bộ vào thư mục:
  * `web/app/plugins/iz-r2-media` (Bản Community - Zero SDK S3/R2 client).
  * `web/app/plugins/iz-r2-media-pro` (Bản Thương Mại - Cloudflare Edge Resizing, WP-CLI Multi-thread Sync, WooCommerce Protected Vault).
* Kích hoạt thành công cả 2 plugin qua WP-CLI:
  ```bash
  wp plugin activate iz-r2-media
  wp plugin activate iz-r2-media-pro
  ```

### B. Kết Quả Kiểm Thử Toàn Diện (3 Tầng Test)
1. **Kiểm thử đơn vị độc lập (`tests/run_tests.php`)**:
   * 48 test cases / 162 assertions: **100% PASS** (Thời gian chạy: 100.98ms, RAM đỉnh: 2.00 MB).
   * Kiểm tra đầy đủ: `CliSyncTest`, `ConfigTest`, `EdgeResizingTest`, `ProtectedVaultTest`, `SigV4SignerTest`, `UrlRewriterTest`.
2. **Kiểm định tài nguyên hình ảnh & bảo mật (`tests/test_assets_adversarial.py`)**:
   * 13 test cases: **100% PASS** (Xác thực cấu trúc PNG chunks, SVG syntax không chứa external URL, kiểm tra tỷ lệ khung hình 16:9 banner và logo).
3. **Kiểm thử tích hợp môi trường thật (`scripts/test_r2_integration.php`)**:
   * 29 test cases: **100% PASS** trực tiếp trong môi trường WordPress 7.1.2 + PHP 8.3.33.
   * **SigV4 Signer:** Tạo chữ ký `AWS4-HMAC-SHA256` chuẩn RFC 3986 với phạm vi `Credential=.../auto/s3/aws4_request`.
   * **WooCommerce Protected Vault:** Tạo Presigned URL với chữ ký xác thực, TTL 120s và header `response-content-disposition`.
   * **12-Factor Engine:** Nhận diện đúng thứ tự ưu tiên Constant $\rightarrow$ Environment $\rightarrow$ Database Option.
   * **Cloudflare Edge Resizing:** Tự động chuyển đổi URL ảnh sang endpoint `/cdn-cgi/image/width=...,format=auto` cho thumbnail/medium và bỏ qua xử lý đối với ảnh gốc `full`.
   * **Admin Settings Page:** Giao diện quản trị tại `/wp/wp-admin/options-general.php?page=iz-r2-media` kết xuất đầy đủ các trường cấu hình và kiểm soát quyền `manage_options`.
   * **Lệnh WP-CLI:** Đăng ký thành công lệnh `wp iz-r2 sync` hỗ trợ tham số `--concurrency`, `--batch-size`, `--dry-run` với cơ chế kiểm tra an toàn credentials trước khi thực thi.
