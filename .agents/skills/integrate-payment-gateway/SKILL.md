---
name: integrate-payment-gateway
description: Tiêu chuẩn và các nguyên tắc bảo mật khắt khe khi tích hợp cổng thanh toán online (VNPay, MoMo, ZaloPay, Stripe) vào dự án e-Commerce.
---

# Quy trình Tích hợp Cổng thanh toán (Payment Gateway)

Tích hợp thanh toán là tính năng nhạy cảm nhất. Sai sót có thể dẫn đến việc khách không trả tiền nhưng đơn hàng vẫn thành công.

## 1. Quản lý Cấu hình an toàn
- Tuyệt đối không hard-code các Secret Key, Partner Code vào thẳng code.
- Phải đặt chúng trong `.env` và gọi qua `config('payment.xxx')`.

## 2. Tạo URL Thanh toán (Redirect)
1. Tiếp nhận request tạo đơn hàng, lưu đơn hàng vào database với trạng thái `pending` (chưa thanh toán).
2. Tạo chuỗi ký tự kiểm tra (Checksum/Signature) theo đúng tài liệu của Cổng thanh toán. Thuật toán thường là HMAC-SHA512 hoặc RSA.
3. Redirect người dùng sang trang thanh toán của đối tác.

## 3. Xử lý IPN (Webhook) - QUAN TRỌNG NHẤT
IPN (Instant Payment Notification) là route mà Cổng thanh toán gọi ngầm về Server của bạn để báo kết quả.
1. **Route IPN phải bỏ qua CSRF**: Mở cấu hình web middleware để thêm route IPN vào danh sách ngoại lệ CSRF.
2. **Xác thực chữ ký (Verify Signature)**: Bước đầu tiên của hàm IPN là lấy tất cả tham số trả về, hash lại theo công thức và so sánh với mã Hash/Signature mà cổng thanh toán gửi sang. Nếu sai -> Dừng ngay lập tức (Báo lỗi chữ ký không hợp lệ - Ngăn chặn giả mạo).
3. **Database Transaction**: Nếu chữ ký đúng và trạng thái là thành công, bắt đầu cập nhật đơn hàng.
   ```php
   DB::beginTransaction();
   try {
       // Cập nhật trạng thái đơn hàng = paid
       // Trừ số lượng tồn kho (Inventory)
       DB::commit();
   } catch (\Exception $e) {
       DB::rollBack();
       // Ghi log lỗi
   }
   ```
4. Trả về đúng format (thường là mảng JSON có mã lỗi) theo yêu cầu của Cổng thanh toán để họ biết IPN đã được nhận thành công.

## 4. Trang Return URL
Đây chỉ là trang hiển thị cho khách hàng xem sau khi thanh toán xong, tuyệt đối **KHÔNG ĐƯỢC CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG Ở ĐÂY**. Mọi cập nhật dữ liệu phải diễn ra ở IPN.
