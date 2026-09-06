---
name: integrate-payment-gateway
description: Tiêu chuẩn và nguyên tắc tích hợp cổng thanh toán PayOS vào dự án e-Commerce.
---

# Quy trình Tích hợp Cổng thanh toán PayOS

Tích hợp thanh toán là tính năng nhạy cảm nhất. Sai sót có thể dẫn đến việc khách không trả tiền nhưng đơn hàng vẫn thành công hoặc sai lệch tồn kho.

## 1. Quản lý Cấu hình an toàn
- Tuyệt đối không hard-code các Secret Key, API Key vào thẳng code.
- Khai báo biến môi trường trong `.env`:
  ```
  PAYOS_CLIENT_ID=...
  PAYOS_API_KEY=...
  PAYOS_CHECKSUM_KEY=...
  ```
- Lấy thông tin qua `env()` hoặc thông qua file config (VD: `config('payos.client_id')`).

## 2. Tạo URL Thanh toán (Redirect)
1. Trong Controller (VD: `CheckoutController@store`), tiếp nhận request tạo đơn hàng.
2. Lưu đơn hàng vào database với trạng thái `payment_method = 'payos'` và `payment_status = 'pending'`.
3. Khởi tạo đối tượng `PayOS` từ SDK `payos/payos` (nếu có) hoặc gọi API trực tiếp.
4. Tạo dữ liệu thanh toán (Payment Data) bao gồm: `orderCode` (thường là ID đơn hàng dạng số hoặc kết hợp Unix timestamp), `amount` (số tiền), `description`, `returnUrl`, `cancelUrl`.
5. Gọi API tạo Link thanh toán của PayOS.
6. Redirect người dùng sang `checkoutUrl` do PayOS trả về.

## 3. Xử lý IPN (Webhook) - QUAN TRỌNG NHẤT
IPN là route mà PayOS gọi ngầm về Server (thông qua public URL, ví dụ dùng Ngrok khi test local) để báo kết quả.
1. **Route Webhook phải bỏ qua CSRF**: Cấu hình trong `bootstrap/app.php` (hoặc `VerifyCsrfToken` middleware) để bỏ qua CSRF cho route này.
2. **Xác thực chữ ký (Verify Signature)**: Bắt buộc dùng thư viện PayOS hoặc hàm xác thực để kiểm tra Checksum của dữ liệu webhook gửi về. Nếu sai -> Dừng ngay lập tức (Báo lỗi - Ngăn chặn giả mạo).
3. **Database Transaction**: Nếu chữ ký đúng và mã trạng thái là thành công (`code == '00'` hoặc `success`), bắt đầu cập nhật:
   ```php
   DB::beginTransaction();
   try {
       // Cập nhật payment_status = paid
       // Trừ số lượng tồn kho (Inventory)
       // Xóa giỏ hàng (nếu chưa xóa ở bước 2)
       DB::commit();
   } catch (\Exception $e) {
       DB::rollBack();
       // Ghi log lỗi
   }
   ```
4. Trả về đúng format JSON theo yêu cầu của PayOS (thường là `{"error": 0, "message": "Ok", "data": null}`) để họ ghi nhận Webhook thành công.

## 4. Trang Return / Cancel URL
- Đây chỉ là trang hiển thị kết quả cho khách hàng xem sau khi thanh toán xong.
- Tuyệt đối **KHÔNG ĐƯỢC CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG Ở ĐÂY**. Mọi cập nhật dữ liệu phải diễn ra ở Webhook.
