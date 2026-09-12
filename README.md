# Aurelia Store

Chào mừng bạn đến với dự án **Aurelia Store**. Đây là một dự án E-commerce được xây dựng trên nền tảng **Laravel 11** và **Vite** (cùng với các công nghệ frontend như TailwindCSS, AlpineJS).

## 🚀 Hướng dẫn cài đặt và khởi chạy dự án

### 1. Yêu cầu hệ thống
- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL / MariaDB

### 2. Cài đặt các package (Backend & Frontend)
Mở terminal tại thư mục gốc của dự án và chạy:
```bash
composer install
npm install
```

### 3. Cấu hình môi trường (.env)
Tạo file `.env` từ file `.env.example`:
```bash
cp .env.example .env
```
Tạo App Key:
```bash
php artisan key:generate
```
Mở file `.env` và cấu hình thông tin Database (MySQL):
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tên_database_của_bạn
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Chạy Migration (Tạo bảng database)
```bash
php artisan migrate
```
*(Nếu có dữ liệu mẫu, bạn có thể chạy thêm `php artisan db:seed`)*

### 5. Khởi chạy Server Local
Do dự án sử dụng **Vite**, bạn cần mở **2 cửa sổ terminal song song**:

**Terminal 1 (Backend - Laravel):**
```bash
php artisan serve
```

**Terminal 2 (Frontend - Vite):**
```bash
npm run dev
```
Bây giờ, bạn có thể truy cập dự án tại: `http://localhost:8000`

---

## 💳 Hướng dẫn theo dõi và xử lý khi "Treo PayOS" (Lỗi Webhook)

Tính năng thanh toán qua PayOS phụ thuộc rất nhiều vào **Webhook (IPN)** để tự động cập nhật trạng thái đơn hàng. Nếu đơn hàng đã thanh toán nhưng trạng thái trên hệ thống vẫn là `pending` (treo PayOS), hãy làm theo các bước kiểm tra sau:

### 1. Hiểu nguyên lý hoạt động
- Sau khi khách hàng thanh toán thành công, PayOS sẽ tự động gọi ngầm (POST) về một URL (Webhook) trên server của chúng ta.
- Trang "Return URL" (trang thông báo thành công cho khách hàng xem) **KHÔNG** làm nhiệm vụ cập nhật trạng thái đơn hàng. Mọi cập nhật (trừ tồn kho, đổi status thành `paid`) đều phải nằm ở Webhook.

### 2. Cách kiểm tra khi chạy ở môi trường Local (Máy tính cá nhân)
Để PayOS có thể gọi Webhook về máy local của bạn, bạn **BẮT BUỘC** phải dùng một công cụ Expose Localhost như **Ngrok**:

1. Chạy lệnh ngrok để expose port 8000:
   ```bash
   ngrok http 8000
   ```
2. Copy đường dẫn public ngrok (ví dụ: `https://abcd.ngrok-free.app`).
3. Truy cập vào Dashboard của PayOS, vào phần cấu hình Webhook và điền đường dẫn của ngrok cộng với route xử lý IPN của bạn (ví dụ: `https://abcd.ngrok-free.app/api/payment/payos/ipn`).

### 3. Các bước khắc phục lỗi "Treo PayOS"
Nếu đã cấu hình đúng nhưng đơn hàng vẫn treo, hãy kiểm tra theo thứ tự:

- **Kiểm tra Route Webhook có bị block bởi CSRF không?**
  Route xử lý IPN của PayOS phải được loại trừ khỏi Middleware xác thực CSRF (có thể cấu hình trong `bootstrap/app.php` hoặc `VerifyCsrfToken`).
- **Kiểm tra Checksum (Chữ ký dữ liệu):**
  Lỗi rất hay gặp là sai `PAYOS_CHECKSUM_KEY`. Hãy kiểm tra trong file `.env` xem key này có khớp với Dashboard của PayOS không. Nếu sai checksum, logic code thường sẽ `return` hoặc văng lỗi để chặn giả mạo, dẫn đến lệnh cập nhật database không được chạy.
- **Theo dõi Log Lỗi:**
  Mở file `storage/logs/laravel.log`. Tất cả các thao tác `try/catch` lỗi cập nhật database hoặc lỗi sai chữ ký trong IPN cần được ghi log. Hãy tìm xem có Exception nào văng ra vào thời điểm giao dịch không.
- **Kiểm tra Response trả về cho PayOS:**
  Đảm bảo hàm xử lý Webhook của bạn luôn trả về định dạng JSON đúng theo chuẩn PayOS yêu cầu khi xử lý xong (VD: `{"error": 0, "message": "Ok", "data": null}`). Nếu bạn trả về lỗi HTTP 500, PayOS có thể sẽ gọi lại webhook nhiều lần.

### 4. Xử lý thủ công
Trong trường hợp Webhook thực sự bị lỗi (do server downtime lúc đó), bạn có thể kiểm tra trực tiếp trạng thái trên [Dashboard PayOS](https://my.payos.vn/). Nếu giao dịch bên đó báo "Thành công", bạn có thể vào Admin Panel của dự án để update thủ công trạng thái đơn hàng từ `pending` sang `paid`.


php artisan serve
php artisan reverb:start
npm run dev
