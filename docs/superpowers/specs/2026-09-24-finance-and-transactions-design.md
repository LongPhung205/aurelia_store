# Thiết Kế Chi Tiết: Module Tài Chính & Đối Soát Giao Dịch (Finance & Transaction Management)

- **Ngày ban hành:** 24/09/2026
- **Dự án:** Aurelia Store (Laravel 11, TailwindCSS, Alpine.js, Chart.js)
- **Tác giả:** Antigravity Engineering & Product Design

---

## 1. Mục tiêu & Bối cảnh

### 1.1. Vấn đề hiện tại
- Hệ thống cửa hàng Aurelia Store có 3 phương thức thanh toán chính: **COD (Tiền mặt khi giao)**, **MoMo (Ví điện tử)**, và **PayOS (Chuyển khoản QR ngân hàng)**.
- Hiện tại bảng `payment_transactions` chỉ ghi nhận giao dịch của PayOS và MoMo khi thanh toán online; các đơn hàng COD chỉ lưu trạng thái trong bảng `orders` mà chưa có bản ghi giao dịch riêng.
- Quản trị viên chưa có công cụ tập trung để theo dõi và đối soát tiền mặt bưu tá nộp về cho các đơn hàng COD, chưa thể xem lịch sử dòng tiền tập trung của cả 3 cổng thanh toán.
- Chưa có trang báo cáo phân tích tài chính chuyên sâu để chủ cửa hàng theo dõi Doanh thu thực thu, Doanh thu chờ thu (hàng đang đi đường), Chi phí vốn nhập hàng và Lợi nhuận gộp ước tính.

### 1.2. Mục tiêu giải pháp
1. Xây dựng phân hệ **Tài chính & Dòng tiền** trên Admin Sidebar với 2 trang chuyên trách:
   - **Thống kê tài chính (`/admin/finance`)**: Phân tích KPI doanh thu, chi phí, lợi nhuận gộp, biểu đồ xu hướng theo ngày/tháng, cơ cấu cổng thanh toán.
   - **Danh sách giao dịch (`/admin/transactions`)**: Bảng quản lý toàn bộ giao dịch (COD, MoMo, PayOS), hỗ trợ tìm kiếm, lọc đa tiêu chí, phân trang và Modal đối soát thanh toán an toàn.
2. Thiết lập cơ chế đồng bộ tập trung vào bảng `payment_transactions`: Tự động sinh giao dịch cho đơn COD mới và cung cấp command backfill cho đơn cũ.
3. Quy trình đối soát thanh toán chuẩn mực: MoMo/PayOS tự động `success` qua Webhook; đơn COD bắt đầu bằng `pending` và được Admin/Kế toán phê duyệt `success` khi nhận tiền từ bưu tá, đồng bộ 2 chiều với trạng thái thanh toán của `Order`.

---

## 2. Kiến trúc Dữ liệu & Database Schema

### 2.1. Cập nhật bảng `payment_transactions`
Tạo migration bổ sung các trường phục vụ kiểm toán và đối soát:
- `admin_id` (`bigint unsigned, nullable`): Khóa ngoại liên kết tới bảng `users(id)`, ghi nhận nhân viên/Admin đã duyệt đối soát giao dịch.
- `note` (`text, nullable`): Ghi chú đối soát (ví dụ: *Đã nhận tiền nộp từ shipper GHN*, *Mã sao kê VCB...*).
- `reconciled_at` (`timestamp, nullable`): Thời điểm đối soát thành công.
- Đảm bảo trường `payment_method` hỗ trợ đầy đủ các giá trị: `'cod'`, `'momo'`, `'payos'`.
- Trường `status` gồm: `'pending'` (chờ thanh toán/chưa đối soát), `'success'` (đã thanh toán/đã đối soát), `'failed'` (thất bại/hủy).

### 2.2. Quan hệ Eloquent Model
- `PaymentTransaction` belongsTo `Order`.
- `PaymentTransaction` belongsTo `User` (chủ đơn hàng thông qua `order.user_id`, và admin duyệt đối soát qua quan hệ `reconciledBy()` liên kết tới `admin_id`).
- `Order` hasMany `PaymentTransaction`.

### 2.3. Tự động sinh bản ghi Giao dịch cho Đơn hàng COD
- Tại [`CheckoutController::store()`](file:///d:/XAMPP/htdocs/aurelia_store/app/Http/Controllers/Client/CheckoutController.php):
  Khi đơn hàng được đặt với `payment_method == 'cod'`, tạo bản ghi `PaymentTransaction`:
  ```php
  PaymentTransaction::create([
      'order_id'       => $order->id,
      'transaction_id' => 'COD-ORD-' . $order->id,
      'amount'         => $order->total_amount,
      'payment_method' => 'cod',
      'status'         => 'pending',
  ]);
  ```
- **Seeder / Backfill Command (`SyncCodTransactionsCommand`):** Quét tất cả `orders` có `payment_method = 'cod'` mà chưa có `payment_transactions` liên kết, tự động sinh bản ghi tương ứng để chuẩn hóa 100% dữ liệu lịch sử.

---

## 3. Đặc tả Trang Danh sách Giao dịch & Đối soát (`/admin/transactions`)

### 3.1. Bố cục Giao diện (Fixed Table Layout)
- Bố cục tuân thủ nguyên tắc **Fixed Table Layout**: Chiều cao vừa vặn khung hình trình duyệt (`h-[calc(100vh-4rem)]`), chỉ cuộn dữ liệu bên trong bảng; thanh lọc và thanh phân trang luôn cố định.
- **Thanh công cụ & Bộ lọc (Filters):**
  - **Ô tìm kiếm:** Hỗ trợ tìm kiếm theo Mã giao dịch (`transaction_id`), Mã đơn hàng (`ORD-xx`), Tên khách hàng hoặc Số điện thoại.
  - **Lọc theo Cổng thanh toán:** Tất cả / `COD (Tiền mặt)` / `PayOS (Chuyển khoản QR)` / `MoMo (Ví điện tử)`.
  - **Lọc theo Trạng thái:** Tất cả / `Chờ thanh toán (pending)` / `Thành công (success)` / `Thất bại (failed)`.
  - **Lọc theo Khoảng ngày:** `Từ ngày` -> `Đến ngày`.
  - **Nút "Lọc"** & **Nút "Đặt lại"** (Reset).

### 3.2. Cấu trúc Cột Dữ liệu
1. **Mã giao dịch / Mã đơn:** 
   - Mã giao dịch in đậm font monospace.
   - Link clickable chuyển sang trang chi tiết đơn hàng: `#ORD-33`.
2. **Khách hàng:** Tên khách hàng, Số điện thoại người nhận.
3. **Phương thức thanh toán:** Badge nhận diện màu sắc:
   - PayOS: Badge xanh dương kèm icon chuyển khoản/ngân hàng.
   - MoMo: Badge tím/hồng đặc trưng.
   - COD: Badge cam/xám (Tiền mặt).
4. **Số tiền:** Định dạng VNĐ in đậm rõ ràng (ví dụ: `920.000đ`).
5. **Trạng thái:**
   - 🟢 `Thành công (success)`: Badge xanh lá.
   - 🟡 `Chờ thanh toán (pending)`: Badge vàng cam.
   - 🔴 `Thất bại (failed)`: Badge đỏ.
6. **Thời gian & Đối soát:**
   - Ngày giờ tạo giao dịch.
   - Nếu đã đối soát: Hiển thị tên Admin đối soát và ngày đối soát.
7. **Thao tác:**
   - Nút **"Đối soát / Cập nhật"** (Mở Modal).
   - Nút **"Xem đơn"** (Icon mắt chuyển đến chi tiết đơn hàng).

### 3.3. Modal Đối soát thanh toán (Reconciliation Modal)
- Hiển thị thông tin tóm tắt: Mã GD, Mã đơn, Số tiền, Phương thức hiện tại.
- Dropdown chọn trạng thái mới: `Thành công (success)`, `Chờ thanh toán (pending)`, `Thất bại (failed)`.
- Ô nhập Ghi chú đối soát / Mã tham chiếu: Textarea nhập lý do / thông tin phiếu thu bưu tá.
- Nút bấm Lưu cập nhật: Kích hoạt SweetAlert2 xác nhận hành động, gửi `PATCH /admin/transactions/{transaction}`.
- **Xử lý đồng bộ 2 chiều:**
  - Nếu đổi sang `success`: Cập nhật `orders.payment_status = 'paid'`, lưu `admin_id = auth()->id()`, `reconciled_at = now()`.
  - Nếu đổi sang `failed`: Cập nhật `orders.payment_status = 'failed'`.
  - Nếu đổi sang `pending`: Cập nhật `orders.payment_status = 'pending'`.

---

## 4. Đặc tả Trang Thống kê Tài chính (`/admin/finance`)

### 4.1. Bộ lọc Thời gian
- Thanh chọn khoảng thời gian trên đầu trang:
  - Nút chọn nhanh: `Hôm nay` · `7 ngày qua` · `Tháng này` *(mặc định)* · `Quý này` · `Năm nay`.
  - Input chọn khoảng ngày tùy chỉnh: `from_date` và `to_date`.

### 4.2. Hệ thống Thẻ chỉ số (KPI Summary Cards)
1. **Doanh thu thực thu:** Tổng tiền các giao dịch có `status = 'success'` trong kỳ lọc (tiền PayOS + MoMo + COD đã đối soát). Kèm % tăng trưởng so với kỳ trước.
2. **Doanh thu chờ thu (Dòng tiền đang đi đường):** Tổng tiền các giao dịch `status = 'pending'` (đơn COD đang giao chưa đối soát hoặc đơn online chờ chuyển tiền).
3. **Chi phí vốn nhập hàng:** Tổng tiền từ các phiếu nhập kho (`imports`) có trạng thái hoàn tất trong kỳ lọc.
4. **Lợi nhuận gộp ước tính:** `Doanh thu thực thu - Chi phí vốn nhập hàng`. Hiển thị cùng tỷ suất lợi nhuận gộp (`Margin %`).

### 4.3. Biểu đồ Trực quan (Chart.js)
1. **Biểu đồ Miền / Cột (Revenue Over Time):** Biểu đồ doanh thu biến động theo từng ngày trong tháng (hoặc từng tháng trong năm).
2. **Biểu đồ Tròn (Payment Methods Breakdown):** Tỷ trọng doanh thu theo từng cổng thanh toán (% COD vs % PayOS vs % MoMo).
3. **Bảng tóm tắt dòng tiền theo Cổng thanh toán:**
   - Cột: Cổng thanh toán | Số giao dịch thành công | Doanh thu thực thu | Doanh thu chờ thu | Tỷ lệ thành công (%).

---

## 5. Tích hợp Điều hướng & Quyền hạn (Navigation & Security)

### 5.1. Sidebar Navigation
Thêm nhóm menu mới **Tài chính & Dòng tiền** trong [`admin/layouts/admin.blade.php`](file:///d:/XAMPP/htdocs/aurelia_store/resources/views/admin/layouts/admin.blade.php):
- **Thống kê tài chính:** route `admin.finance.index` (`/admin/finance`).
- **Danh sách giao dịch:** route `admin.transactions.index` (`/admin/transactions`).

### 5.2. Phân quyền & Bảo mật
- Tất cả route nằm trong nhóm middleware `auth` và `role:admin`.
- CSRF Token được kiểm tra bắt buộc cho mọi thao tác cập nhật đối soát.
- Mọi thao tác cập nhật đối soát đều ghi nhận ID của Admin thực hiện vào cột `admin_id`.

---

## 6. Kế hoạch Kiểm thử & Xác nhận (Testing & Verification)
1. **Kiểm thử Database & Backfill:** Chạy migration và seeder/command backfill, kiểm tra 100% đơn hàng cũ đều có bản ghi giao dịch chính xác.
2. **Kiểm thử Bộ lọc & Tìm kiếm:** Thử nghiệm tìm kiếm theo mã đơn, mã giao dịch, lọc theo cổng thanh toán, lọc theo trạng thái, đảm bảo phân trang giữ nguyên query params.
3. **Kiểm thử Đối soát 2 chiều:** Đổi trạng thái giao dịch COD từ `pending` sang `success`, xác nhận trạng thái thanh toán của Đơn hàng tự động nhảy sang `paid` và lưu thông tin `admin_id`.
4. **Kiểm thử Thống kê & Biểu đồ:** Kiểm tra công thức tính Doanh thu thực thu, Doanh thu chờ thu, Chi phí nhập kho và tính toán chính xác trên biểu đồ Chart.js.
