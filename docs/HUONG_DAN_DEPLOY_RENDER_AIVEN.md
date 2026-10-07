# HƯỚNG DẪN TRIỂN KHAI AURELIA STORE LÊN RENDER & AIVEN MYSQL

Tài liệu này cung cấp hướng dẫn chi tiết từng bước để đưa dự án **Aurelia Store** (Laravel 12 + Vite + Tailwind CSS) lên **Render.com** (dưới dạng Docker Web Service) và kết nối với cơ sở dữ liệu cloud **Aiven MySQL** sử dụng SSL CA Certificate an toàn.

---

## MỤC LỤC
1. [Khái quát kiến trúc triển khai](#1-khái-quát-kiến-trúc-triển-khai)
2. [Bước 1: Thiết lập Cơ sở dữ liệu Aiven MySQL](#bước-1-thiết-lập-cơ-sở-dữ-liệu-aiven-mysql)
3. [Bước 2: Chuẩn bị Repository GitHub](#bước-2-chuẩn-bị-repository-github)
4. [Bước 3: Tạo và Cấu hình Web Service trên Render](#bước-3-tạo-và-cấu-hình-web-service-trên-render)
5. [Bước 4: Cấu hình Secret Files & Biến Môi Trường (Environment Variables)](#bước-4-cấu-hình-secret-files--biến-môi-trường-environment-variables)
6. [Bước 5: Kích hoạt Migration & Khởi tạo Tài khoản Admin](#bước-5-kích-hoạt-migration--khởi-tạo-tài-khoản-admin)
7. [Checklist Xử Lý Sự Cố Thường Gặp (Troubleshooting)](#checklist-xử-lý-sự-cố-thường-gặp-troubleshooting)

---

## 1. Khái quát kiến trúc triển khai

```text
[ Người dùng / Trình duyệt ]
             │ (HTTPS:443)
             ▼
[ Render Cloud Edge Reverse Proxy ]
             │ (HTTP:${PORT})
             ▼
[ Docker Container ]
  ├── Nginx (Reverse Proxy & Static Files: public/build)
  ├── PHP-FPM 8.2 (Xử lý ứng dụng Laravel 12)
  └── entrypoint.sh (Cấp quyền, xác thực CA, cache, quản lý tiến trình)
             │
             │ (SSL / TLS Port 25xxx - Xác thực bởi ca.pem)
             ▼
[ Aiven Cloud Managed MySQL ]
```

---

## Bước 1: Thiết lập Cơ sở dữ liệu Aiven MySQL

1. Truy cập [Aiven Console](https://console.aiven.io/) và đăng nhập/đăng ký tài khoản.
2. Nhấn **Create service**:
   - **Service type:** Chọn `MySQL`.
   - **Cloud provider & Region:** Chọn gần người dùng (ví dụ: `Singapore` hoặc `Frankfurt`).
   - **Plan:** Chọn `Free tier` hoặc plan phù hợp với nhu cầu.
   - **Service name:** Đặt tên dịch vụ, ví dụ: `aurelia-mysql`.
3. Nhấn **Create free service** (hoặc Create service). Chờ 2-3 phút đến khi trạng thái chuyển sang **Running**.
4. Lấy thông tin kết nối tại tab **Overview**:
   - **Host:** (ví dụ: `mysql-xxxx-aurelia-store.a.aivencloud.com`)
   - **Port:** (ví dụ: `25060`)
   - **User:** `avnadmin`
   - **Password:** Nhấn nút copy mật khẩu.
   - **Database:** `defaultdb`
5. **Tải chứng chỉ SSL CA:**
   - Tại mục **Connection information** > bấm nút **Download CA Certificate** (file thường tên là `ca.pem`).
   - Lưu file `ca.pem` này vào máy để chuẩn bị tải lên Render ở Bước 4.

---

## Bước 2: Chuẩn bị Repository GitHub

Đảm bảo các file cấu hình Docker đã được commit và push lên nhánh triển khai (ví dụ nhánh `main` hoặc `feat/render-aiven-deployment`):

```bash
git push origin feat/render-aiven-deployment
```

Các thành phần cốt lõi cần có trong repo:
- `Dockerfile` (Multi-stage build Node 20 + PHP 8.2 FPM + Nginx)
- `.dockerignore` (Đã loại trừ file rác, vendor và node_modules)
- `docker/nginx.conf`
- `docker/php.ini`
- `docker/php-fpm.conf`
- `docker/entrypoint.sh`
- `docker/check-ca.php`
- `config/seeding.php`
- `database/seeders/AdminUserSeeder.php`

---

## Bước 3: Tạo và Cấu hình Web Service trên Render

1. Đăng nhập vào [Render Dashboard](https://dashboard.render.com/).
2. Nhấn nút **New +** ở góc trên bên phải > chọn **Web Service**.
3. Chọn **Build and deploy from a Git repository**.
4. Tìm và kết nối với repository **aurelia_store**.
5. Điền thông tin cơ bản:
   - **Name:** `aurelia-store`
   - **Region:** Chọn cùng khu vực hoặc gần khu vực của Aiven (ví dụ: `Singapore`).
   - **Branch:** Chọn nhánh chứa code triển khai (ví dụ: `main` hoặc `feat/render-aiven-deployment`).
   - **Language:** Chọn **Docker** (Render sẽ tự động tìm và build từ `Dockerfile`).
   - **Instance Type:** Chọn `Free` (hoặc `Starter`).

---

## Bước 4: Cấu hình Secret Files & Biến Môi Trường (Environment Variables)

### 4.1. Thêm Secret File (Chứng chỉ SSL CA)
1. Tại trang cấu hình dịch vụ Render, cuộn xuống mục **Secret Files** (hoặc tab **Environment** > **Secret Files**).
2. Nhấn **Add Secret File**:
   - **Filename:** `ca.pem`
   - **File Contents:** Mở file `ca.pem` đã tải từ Aiven ở Bước 1 bằng Notepad/VS Code, copy toàn bộ nội dung (bắt đầu bằng `-----BEGIN CERTIFICATE-----` và kết thúc bằng `-----END CERTIFICATE-----`) dán vào ô này.
3. Render sẽ lưu file này tại đường dẫn: `/etc/secrets/ca.pem`. Khi container khởi động, file `docker/entrypoint.sh` sẽ tự động phát hiện, cài đặt và phân quyền file chứng chỉ này.

### 4.2. Thêm Biến Môi Trường (Environment Variables)
Chuyển sang mục **Environment Variables**, thêm các biến sau:

| Tên biến (Key) | Giá trị mẫu / Hướng dẫn (Value) | Ghi chú |
| :--- | :--- | :--- |
| `APP_NAME` | `Aurelia Store` | Tên website |
| `APP_ENV` | `production` | Môi trường chạy |
| `APP_DEBUG` | `false` | Bắt buộc tắt debug ở production |
| `APP_URL` | `https://aurelia-store.onrender.com` | URL Render cấp cho Web Service |
| `APP_KEY` | `base64:AbCdEf...` | Sinh bằng lệnh: `php artisan key:generate --show` |
| `DB_CONNECTION` | `mysql` | Driver database |
| `DB_HOST` | `mysql-xxxx-aurelia-store.a.aivencloud.com` | Lấy từ Aiven Overview |
| `DB_PORT` | `25060` | Cổng từ Aiven |
| `DB_DATABASE` | `defaultdb` | Tên database Aiven |
| `DB_USERNAME` | `avnadmin` | User database Aiven |
| `DB_PASSWORD` | `Mật khẩu Aiven` | Mật khẩu DB Aiven |
| `MYSQL_ATTR_SSL_CA` | `/run/app-certificates/mysql-ca.pem` | Nơi entrypoint cài đặt chứng chỉ |
| `SESSION_DRIVER` | `database` | Lưu session trong DB (hoặc `file`) |
| `CACHE_STORE` | `file` | Cache file |
| `QUEUE_CONNECTION` | `database` | Lưu queue jobs trong database |
| `RUN_MIGRATIONS` | `true` | Tự động chạy `migrate --force` khi start |
| `RUN_SEEDERS` | `true` | Chạy seeder admin trong lần đầu deploy |
| `SEED_ADMIN_NAME` | `Quản Trị Viên Aurelia` | Họ tên admin khởi tạo |
| `SEED_ADMIN_EMAIL` | `admin@aureliastore.com` | Email đăng nhập của Admin |
| `SEED_ADMIN_PASSWORD` | `MatKhauBaoMatTren12KyTu!` | Mật khẩu tối thiểu 12 ký tự |
| `PAYOS_CLIENT_ID` | `...` | Mã Client PayOS |
| `PAYOS_API_KEY` | `...` | API Key PayOS |
| `PAYOS_CHECKSUM_KEY` | `...` | Checksum Key PayOS |
| `GHN_TOKEN` | `...` | Token Giao Hàng Nhanh |
| `GHN_SHOP_ID` | `...` | Shop ID Giao Hàng Nhanh |
| `BROADCAST_CONNECTION` | `reverb` | Driver real-time chat Reverb |
| `REVERB_APP_ID` | `895058` | App ID Reverb |
| `REVERB_APP_KEY` | `t6g92t9i9z0tnh33ryb2` | App Key Reverb |
| `REVERB_APP_SECRET` | `aobg30a4r7o55p2q2c9h` | App Secret Reverb |
| `REVERB_HOST` | `127.0.0.1` | Host Reverb trong container |
| `REVERB_PORT` | `8080` | Port Reverb trong container |
| `REVERB_SCHEME` | `http` | Giao thức nội bộ Reverb |
| `VITE_REVERB_APP_KEY` | `${REVERB_APP_KEY}` | Key gửi cho Client Vite |
| `VITE_REVERB_HOST` | `<ten-service>.onrender.com` | Domain public Render cấp |
| `VITE_REVERB_PORT` | `443` | Cổng HTTPS/WSS public |
| `VITE_REVERB_SCHEME` | `https` | Giao thức WSS public |

*(Tham khảo thêm mẫu đầy đủ tại file [docker/render.env.example](file:///d:/XAMPP/htdocs/aurelia_store/docker/render.env.example))*

---

## Bước 5: Kích hoạt Migration & Khởi tạo Tài khoản Admin

1. Nhấn nút **Create Web Service** (hoặc **Deploy latest commit**).
2. Render sẽ tiến hành build Docker Image:
   - Biên dịch frontend Vite & Tailwind CSS.
   - Cài đặt extensions PHP & dependencies Composer production.
   - Đóng gói container và khởi chạy `app-entrypoint`.
3. Theo dõi tab **Logs**:
   - Khi thấy dòng:
     ```text
     [ENTRYPOINT] Detected Secret File at /etc/secrets/ca.pem. Installing...
     [CA-CHECK] Success: Valid CA certificate detected and verified.
     [ENTRYPOINT] RUN_MIGRATIONS is true. Executing migrations...
     [ENTRYPOINT] RUN_SEEDERS is true. Executing seeders...
     Admin user [admin@aureliastore.com] successfully created with role admin.
     [ENTRYPOINT] Aurelia Store is ready and listening on port 10000.
     ```
   - Quá trình deploy đã thành công!
4. **Bảo mật sau lần triển khai đầu tiên:**
   - Quay lại tab **Environment Variables** trên Render.
   - Đổi `RUN_SEEDERS` từ `true` thành `false` để tránh chạy lại seeder ở các lần deploy kế tiếp.

---

## Checklist Xử Lý Sự Cố Thường Gặp (Troubleshooting)

### 1. Lỗi kết nối Cơ sở dữ liệu: `SQLSTATE[HY000] [2002] Connection refused` hoặc lỗi SSL Certificate
- **Nguyên nhân:** Chưa cấu hình Secret File `ca.pem` hoặc đường dẫn `MYSQL_ATTR_SSL_CA` bị sai.
- **Khắc phục:** 
  1. Kiểm tra xem file `ca.pem` đã được thêm vào mục **Secret Files** với đúng tên `ca.pem` chưa.
  2. Đảm bảo biến `MYSQL_ATTR_SSL_CA=/run/app-certificates/mysql-ca.pem`.
  3. Kiểm tra xem host, port và mật khẩu Aiven có chính xác không.

### 2. Giao diện bị vỡ (CSS/JS không tải được hoặc báo lỗi Vite manifest)
- **Nguyên nhân:** Build Vite thiếu file hoặc sai đường dẫn `APP_URL`.
- **Khắc phục:**
  1. Kiểm tra biến `APP_URL` trên Render: phải đúng giao thức `https://<ten-service>.onrender.com`.
  2. Trong `bootstrap/app.php` đã có `$middleware->trustProxies(at: '*');` để nhận dạng đúng HTTPS từ proxy Render.
  3. Quá trình multi-stage Docker build đã tự động biên dịch `public/build`.

### 3. Lỗi 502 Bad Gateway hoặc Container thoát đột ngột (Exit Code 137 / Out of memory)
- **Nguyên nhân:** Render Free Tier có 512MB RAM, nếu chạy cùng lúc quá nhiều worker PHP-FPM sẽ bị OOM (Out Of Memory).
- **Khắc phục:**
  - File `docker/php-fpm.conf` đã được thiết lập `pm = ondemand` và `pm.max_children = 5` để tự động thu hồi RAM khi nhàn rỗi.
  - File `docker/php.ini` đã giới hạn `memory_limit = 256M`.

### 4. Lỗi Webhook PayOS hoặc MoMo báo lỗi CSRF (419 Page Expired)
- **Nguyên nhân:** Request POST từ cổng thanh toán bị bộ lọc CSRF chặn.
- **Khắc phục:** 
  - `bootstrap/app.php` đã được cấu hình ngoại lệ CSRF:
    ```php
    $middleware->validateCsrfTokens(except: [
        '/payos/webhook',
        '/payment/momo/notify',
    ]);
    ```

---

## 8. Hướng dẫn Kích hoạt Webhook PayOS sau khi Deploy

Sau khi ứng dụng hoạt động trên Render (`https://aurelia-store.onrender.com`), thực hiện các bước sau để PayOS tự động bắn webhook cập nhật đơn hàng:

### Cách 1: Đăng ký trên Dashboard PayOS (Khuyên dùng)
1. Đăng nhập [my.payos.vn](https://my.payos.vn/).
2. Chọn **Kênh thanh toán** của bạn.
3. Tìm đến mục **Webhook URL** và dán đường link:
   ```text
   https://aurelia-store.onrender.com/payos/webhook
   ```
4. Bấm **Xác nhận / Lưu Webhook**. Hệ thống PayOS sẽ gửi gói tin test và website sẽ trả về `200 OK` (đã hỗ trợ sẵn trong `PayOSController`).

### Cách 2: Kích hoạt qua Artisan Command trên Render Shell
Mở tab **Shell** trong dịch vụ Render của bạn và chạy lệnh:
```bash
php artisan payos:confirm-webhook
```
Hoặc chỉ định URL cụ thể:
```bash
php artisan payos:confirm-webhook https://aurelia-store.onrender.com/payos/webhook
```

---
*Tài liệu được khởi tạo và kiểm chuẩn tự động cho dự án Aurelia Store.*

