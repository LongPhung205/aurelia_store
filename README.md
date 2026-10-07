<div align="center">

# 👗 AURELIA STORE
### High-End Fashion E-Commerce Platform
**Nền tảng thương mại điện tử thời trang thiết kế cao cấp với kiến trúc Real-time, Data Mining & Cloud Deployment**

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev)
[![Docker](https://img.shields.io/badge/Docker-Multi--Stage-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com)
[![MySQL SSL](https://img.shields.io/badge/Aiven-MySQL_Cloud-FF1300?style=for-the-badge&logo=mysql&logoColor=white)](https://aiven.io)
[![Render Cloud](https://img.shields.io/badge/Render-Cloud_Service-46E3B7?style=for-the-badge&logo=render&logoColor=white)](https://render.com)

[🌐 Trải Nghiệm Live Demo](https://aurelia-store.onrender.com) • [📖 Tài Liệu Triển Khai](docs/HUONG_DAN_DEPLOY_RENDER_AIVEN.md) • [⚡ Báo Cáo Lỗi](https://github.com/LongPhung205/aurelia_store/issues)

</div>

---

## 📌 1. Giới thiệu tổng quan (Overview)

**Aurelia Store** là hệ thống E-Commerce chuyên ngành thời trang thiết kế được xây dựng với tư duy kỹ thuật chuyên sâu (Enterprise-ready), không đơn thuần là một ứng dụng CRUD cơ bản. Dự án kết hợp giữa **trải nghiệm người dùng mượt mà (UX/UI cao cấp)** với **kiến trúc xử lý bất đồng bộ (Asynchronous Queues & WebSockets)**, áp dụng các thuật toán **Khai phá dữ liệu (Data Mining)** và **Phân khúc khách hàng (RFM)** nhằm tối ưu hóa doanh số bán lẻ trực tuyến.

### 🌐 Live Demo & Tài khoản thử nghiệm
* **Website:** [https://aurelia-store.onrender.com](https://aurelia-store.onrender.com)
* **Tài khoản Khách hàng (Customer Demo):**
  - Email: `customer@aureliastore.com` (hoặc đăng ký mới kèm OTP)
  - Mật khẩu: `password123`
* **Tài khoản Quản trị viên (Admin Demo):**
  - Email: `admin@example.com`
  - Mật khẩu: `AureliaAdmin2026@!`

---

## 🏗️ 2. Sơ đồ kiến trúc hệ thống (System Architecture)

```text
[ Trình duyệt / Khách hàng & Admin ]
                 │
                 │ (HTTPS : 443 / WSS : 443)
                 ▼
[ Render Cloud Edge Reverse Proxy ] (SSL Termination)
                 │
                 │ (HTTP / WS Proxy qua port 10000)
                 ▼
┌────────────────── Docker Container (Alpine Runtime) ──────────────────┐
│                                                                        │
│   ┌───────────────────────────────────────────────────────────────┐    │
│   │ Nginx (Reverse Proxy & Static Cache: public/build)            │    │
│   │ ├── /app -> WebSocket Proxy (127.0.0.1:8080)                  │    │
│   │ └── /    -> FastCGI Unix Socket / 9000                        │    │
│   └───────────────────────────────┬───────────────────────────────┘    │
│                                   │                                    │
│   ┌───────────────────────────────▼───────────────────────────────┐    │
│   │ PHP-FPM 8.2 (Laravel 12 Engine - On-demand Memory Control)    │    │
│   └───────────────────────────────────────────────────────────────┘    │
│                                   │                                    │
│   ┌────────────────── Tiến trình chạy ngầm (Daemons) ─────────────┐    │
│   │ ├── Queue Worker: Xử lý email OTP, thông báo đơn, cảnh báo kho│    │
│   │ ├── Laravel Reverb: Server WebSocket Chat CSKH Real-time      │    │
│   │ └── Scheduler Loop: Tự động hủy đơn quá hạn & hoàn tồn kho    │    │
│   └───────────────────────────────────────────────────────────────┘    │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    │ (TLS / SSL Connection với ca.pem)
                                    ▼
                     [ Aiven Cloud Managed MySQL ]
                                    │
                ┌───────────────────┴───────────────────┐
                ▼                                       ▼
  [ Dịch vụ Thanh toán (3rd APIs) ]      [ Dịch vụ Vận chuyển & Mail ]
   • PayOS (VietQR Webhook)               • Giao Hàng Nhanh (GHN Open API)
   • MoMo (IPN Security HMAC)             • Gmail SMTP / Queue Mails
```

---

## 💎 3. Các điểm sáng kỹ thuật (Technical Highlights)

### 🤖 A. Khai phá dữ liệu & Trí tuệ kinh doanh (Data Mining & BI)
* **Khai phá giỏ hàng bằng Thuật toán Apriori (`MarketBasketMiningService`):**
  - Phân tích các giao dịch thực tế trong quá khứ để tìm tập phổ biến (Frequent Itemsets).
  - Tính toán chỉ số **Support**, **Confidence** và **Lift** để gợi ý tính năng *"Thường được mua cùng nhau"* (Frequently Bought Together Combo) ngay tại trang chi tiết sản phẩm.
* **Phân tích phân khúc khách hàng RFM (`RfmAnalyticsService`):**
  - Chấm điểm khách hàng theo 3 chiều: **Recency** (Lần mua gần nhất), **Frequency** (Tần suất mua), **Monetary** (Tổng giá trị chi tiêu).
  - Tự động chia khách hàng thành các nhóm: *VIP (Champions), Trung thành (Loyal), Nguy cơ rời bỏ (At Risk), Cần chăm sóc (Needs Attention)* giúp xây dựng chiến dịch Marketing cá nhân hóa.

### ⚡ B. Xử lý bất đồng bộ & Real-time (Async & WebSocket)
* **Laravel Reverb WebSocket (Real-time Live Chat):**
  - Kênh chat CSKH trực tiếp giữa Khách hàng và Admin theo kiến trúc `PrivateChannel('chat.{conversation_id}')`.
  - Frontend sử dụng `Laravel Echo` tự động kết nối qua Secure WebSocket (`wss://`) không cần bên thứ 3 (Pusher).
* **Hệ thống Hàng đợi nền (Queue Workers):**
  - Toàn bộ email OTP xác thực đăng nhập (`RegistrationOtpMail`), thông báo đơn hàng mới (`NewOrderNotification`) và cảnh báo tồn kho thấp (`LowStockNotification`) đều thực thi ngầm qua cơ chế `ShouldQueue`.
* **Bộ lập lịch tự động (Automated Scheduler):**
  - Tự động quét và giải phóng tồn kho (`orders:release-unpaid`) mỗi phút cho các đơn hàng thanh toán online quá hạn, ngăn chặn tình trạng "giữ hàng ảo".

### 💳 C. Tích hợp Cổng thanh toán & Vận chuyển thực tế
* **PayOS (VietQR Tự động):** Thanh toán quét mã QR chuẩn ngân hàng Việt Nam, xác thực chữ ký dữ liệu (Checksum HMAC-SHA256) và xác nhận Webhook tự động qua lệnh Artisan.
* **MoMo Payment Gateway:** Tích hợp thanh toán QR và thẻ quốc tế, xử lý callback an toàn qua IPN và đối soát giao dịch.
* **Giao Hàng Nhanh (GHN API):** Tự động đồng bộ danh mục Tỉnh/Thành, Quận/Huyện, Phường/Xã và tính toán chính xác cước phí vận chuyển thực tế ngay tại trang Checkout.

### 🛡️ D. DevOps & Bảo mật chuyên sâu (Security & Cloud DevOps)
* **Docker Multi-Stage Build:**
  - Stage 1 (Node 20): Biên dịch tài nguyên Vite & tối ưu CSS.
  - Stage 2 (Composer 2): Cài đặt thư viện production không chứa dev-dependencies.
  - Stage 3 (Alpine PHP 8.2 + Nginx): Thu nhỏ kích thước image, quản lý tiến trình bằng `tini`.
* **Zero Secret Leak:** Toàn bộ thông tin nhạy cảm, API keys, chứng chỉ SSL được quản lý qua biến môi trường Cloud và Secret Files, chặn truy cập file ẩn (`.env`, `.git`) từ cấp độ Nginx.

---

## 🛠️ 4. Công nghệ sử dụng (Tech Stack)

| Thành phần | Công nghệ / Thư viện |
| :--- | :--- |
| **Backend Core** | PHP 8.2+, Laravel 12.x (MVC Architecture, Service Layer) |
| **Database** | MySQL 8.0+ trên Aiven Cloud (Kết nối mã hóa SSL CA) |
| **Real-time / WebSocket** | Laravel Reverb, Laravel Echo, Pusher JS Client |
| **Background / Queue** | Database Queue Driver, Cron Scheduler |
| **Frontend & UI** | Blade Template, Tailwind CSS, Alpine.js, Flowbite, Bootstrap Icons |
| **Build Tools** | Vite 7.x, PostCSS, Autoprefixer |
| **DevOps / Infra** | Docker, Nginx Alpine, PHP-FPM, Render Web Service |
| **Payment & Logistics** | PayOS SDK, MoMo API, GHN Open API, Google SMTP |

---

## 📂 5. Cấu trúc thư mục dự án (Directory Structure)

```bash
aurelia_store/
├── app/
│   ├── Console/Commands/        # Custom Artisan Commands (Hủy đơn, Sync kho, Webhook)
│   ├── Events/                  # WebSocket Events (MessageSent)
│   ├── Http/
│   │   ├── Controllers/         # Client & Admin Controllers
│   │   ├── Middleware/          # Role Checking, HTTPS Enforcement, Proxies
│   │   └── Requests/            # Form Request Validation Rules
│   ├── Mail/                    # Mailable Classes (Registration OTP Mail)
│   ├── Models/                  # Eloquent Models & Business Relationships
│   ├── Notifications/           # Quản lý Thông báo đa kênh (Database & Mail)
│   └── Services/                # Service Layer (Apriori, RFM, GHN, MoMo, Cart)
├── config/                      # Toàn bộ cấu hình hệ thống
├── database/
│   ├── migrations/              # Database Schema (40+ migrations)
│   └── seeders/                 # Catalog & Lookbook Seeders
├── docker/                      # Cấu hình Docker Production (Nginx, FPM, Entrypoint)
├── docs/                        # Tài liệu hướng dẫn & sơ đồ kiến trúc
├── resources/
│   ├── css/ & js/               # Frontend Assets (Tailwind & Alpine)
│   └── views/                   # Blade Views (Admin Dashboard & Client Storefront)
├── routes/                      # Web, API, Console & WebSocket Channels
├── Dockerfile                   # Multi-stage Docker Build
└── README.md                    # Tài liệu chính của dự án
```

---

## 🚀 6. Hướng dẫn cài đặt & Khởi chạy Local (Quick Start)

### Yêu cầu hệ thống:
- PHP >= 8.2 & Composer
- Node.js >= 18 & NPM
- MySQL >= 8.0

### Các bước cài đặt:
```bash
# 1. Clone mã nguồn
git clone https://github.com/LongPhung205/aurelia_store.git
cd aurelia_store

# 2. Cài đặt các gói phụ thuộc
composer install
npm install

# 3. Thiết lập môi trường
cp .env.example .env
php artisan key:generate

# 4. Cấu hình Database trong file .env và chạy Migration
php artisan migrate --seed

# 5. Tạo Symbolic Link cho thư mục lưu trữ ảnh
php artisan storage:link

# 6. Khởi chạy toàn bộ hệ thống (Server + Queue + Logs + Vite)
composer run dev
```
Truy cập website tại: `http://localhost:8000`

---

## 👨‍💻 Tác giả (Author)

* **Họ và tên:** Phùng Văn Long
* **Email:** [phungvanlong10925@gmail.com](mailto:phungvanlong10925@gmail.com)
* **GitHub:** [@LongPhung205](https://github.com/LongPhung205)
* **Vai trò:** Fullstack Developer (Backend Heavy / Architecture / DevOps)

---
<div align="center">
<i>Dự án được xây dựng với mục tiêu thể hiện kỹ năng giải quyết bài toán thực tế, tối ưu hóa hiệu năng và tuân thủ các chuẩn mực phát triển phần mềm hiện đại.</i>
</div>
