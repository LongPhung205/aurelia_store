# Original User Request

## 2026-09-15T05:23:04Z

**Aurelia Store Development & Evaluation:** Đánh giá luồng xử lý Webhook PayOS, sửa lỗi "treo đơn hàng" và viết Unit/Feature Tests cho phần thanh toán trên nền tảng E-commerce (Laravel 11).

Working directory: d:\XAMPP\htdocs\aurelia_store
Integrity mode: development

## Requirements

### R1. Tối ưu luồng Webhook PayOS
Kiểm tra logic xử lý Webhook (IPN) hiện tại. Phát hiện và khắc phục các nguyên nhân gây ra tình trạng đơn hàng không được cập nhật trạng thái (treo pending). Đảm bảo xử lý đúng các trường hợp sai chữ ký và trùng lặp webhook (idempotency). Không được phá vỡ các luồng thanh toán khác.

### R2. Viết Unit/Feature Tests
Phát triển bộ test tự động bằng PHPUnit để mô phỏng và kiểm thử quá trình nhận Webhook từ PayOS. Phải bao phủ các case: thanh toán thành công, sai chữ ký (checksum), webhook gọi nhiều lần.

## Acceptance Criteria

### Programmatic Verification
- [ ] Chạy lệnh `php artisan test` thành công 100%, tất cả test liên quan đến luồng PayOS phải PASS (đánh giá tự động, không dựa trên cảm quan của agent).
- [ ] Logic bảo mật: Test case giả mạo request webhook (sai chữ ký hoặc không có chữ ký) phải bị từ chối thành công.
- [ ] Logic Idempotency: Test case gửi 2 webhook giống nhau liên tiếp không gây lỗi dữ liệu hay lặp lại việc xử lý trạng thái.
