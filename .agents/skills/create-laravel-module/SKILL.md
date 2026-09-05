---
name: create-laravel-module
description: Hướng dẫn chi tiết quy trình từng bước để xây dựng một Module CRUD mới (đặc biệt là cho phần Admin) trong dự án Laravel 11. Dùng skill này khi user yêu cầu "tạo module", "thêm chức năng quản lý", hoặc "tạo trang CRUD".
---

# Quy trình xây dựng một Module mới trong Laravel

Khi nhận được yêu cầu tạo một module mới (ví dụ: Quản lý Bài viết, Quản lý Thương hiệu,...), hãy thực hiện tuần tự theo các bước dưới đây để đảm bảo tính nhất quán và chuẩn nghiệp vụ của dự án.

## Bước 1: Tạo Model và Migration
Luôn bắt đầu từ cơ sở dữ liệu.
1. Chạy lệnh tạo Model kèm Migration:
   ```bash
   php artisan make:model ModelName -m
   ```
2. Mở file Migration vừa được tạo trong `database/migrations/`, thêm các cột dữ liệu theo yêu cầu.
   - Nhớ thêm các ràng buộc như `nullable`, `unique`, `constrained()` nếu cần.
   - **Cải tiến:** Thêm `$table->softDeletes();` nếu module cần tính năng Xóa mềm (Soft Deletes) để chống xóa nhầm.
   - **Cải tiến:** Đánh index (ví dụ: `$table->index('status')`) cho các cột hay dùng để tìm kiếm, lọc (email, slug, status).
3. Chạy `php artisan migrate` (hỏi ý kiến user trước khi chạy).

## Bước 2: Thiết lập Model
Mở file Model vừa tạo trong `app/Models/ModelName.php`:
1. Khai báo thuộc tính `$fillable` để cho phép Mass Assignment cho các cột.
2. (Tùy chọn) Định nghĩa các quan hệ (Relationships) như `hasMany`, `belongsTo`.
3. Nếu ở Bước 1 có dùng Soft Deletes, hãy khai báo trait `use SoftDeletes;` ở đầu class Model.

## Bước 3: Tạo Form Request Validation (Tùy chọn nhưng Khuyến khích)
Nếu form có trên 5 trường hoặc logic kiểm tra phức tạp (như check upload file, check trùng lặp):
1. Chạy lệnh: `php artisan make:request StoreModelNameRequest` và `UpdateModelNameRequest`.
2. Di chuyển các logic validate vào hàm `rules()` của các file này, hàm `authorize()` trả về `true` (hoặc check phân quyền tại đây).

## Bước 4: Tạo Controller
Tạo Controller để xử lý logic, đặc biệt là Admin Controller.
1. Chạy lệnh:
   ```bash
   php artisan make:controller Admin/ModelNameController -r
   ```
2. **Hàm `index`:** Dùng phân trang thay vì lấy tất cả dữ liệu.
   - Dùng `ModelName::latest()->paginate(10);` thay vì `ModelName::all();` để tránh tràn bộ nhớ.
3. **Hàm `store` và `update`:**
   - Sử dụng **Form Request** (từ Bước 3) hoặc **Inline Validation** (chỉ dùng nếu form rất đơn giản).
   - **Xử lý Upload File:** Nếu có avatar/image, dùng `Storage::disk('public')->put()` để lưu vào `storage/app/public`, lưu đường dẫn vào DB, và nhớ xóa ảnh cũ khi upload ảnh mới trong hàm update.
   - **Database Transactions:** Với các tác vụ phức tạp (nhập kho, thêm nhiều bảng cùng lúc), luôn bọc trong block `DB::beginTransaction()`, `try-catch` và `DB::rollBack()` để đảm bảo toàn vẹn dữ liệu.
4. **Hàm `destroy`:**
   - Xóa các file vật lý (nếu có) trước khi xóa bản ghi trong DB.
   - Nếu đã cấu hình Soft Deletes, hàm `delete()` sẽ tự động xử lý.
5. Cuối hàm nhớ return: `return redirect()->route('admin.module_name.index')->with('success', 'Thông báo thành công');` (Các thư viện như Toastr/SweetAlert2 sẽ bắt flash message này để hiển thị).

## Bước 5: Khai báo Routes
Mở file `routes/web.php`.
1. Tìm đến khối dành cho Admin (thường có prefix `admin`, name `admin.` và middleware `['auth', 'role:admin']`).
2. Thêm Route Resource cho module mới.
   ```php
   Route::resource('module_names', Admin\ModelNameController::class);
   ```

## Bước 6: Xây dựng Giao diện (Views)
1. Tạo thư mục mới tại `resources/views/admin/module_names/`.
2. Tạo các file cần thiết:
   - `index.blade.php`: Chứa bảng danh sách dữ liệu. Phải có giao diện hiển thị phân trang (ví dụ: `{{ $items->links() }}`).
   - `create.blade.php`: Chứa form thêm mới. Nếu có upload file, bắt buộc phải có thuộc tính `enctype="multipart/form-data"`.
   - `edit.blade.php`: Chứa form cập nhật (nhớ dùng `@method('PUT')`).
3. Các file view này phải kế thừa layout chính của admin bằng `@extends('admin.layouts.admin')`.

## Bước 7: Cập nhật Sidebar/Menu (Nếu cần)
- Nhắc hoặc chủ động tìm file sidebar (ví dụ `resources/views/admin/layouts/sidebar.blade.php`) để thêm menu link dẫn tới `route('admin.module_names.index')`.

---
**Lưu ý cho AI:**
- Luôn kiểm tra xem thư mục hoặc file đã tồn tại chưa trước khi tạo/ghi đè.
- Khuyến khích kiểm tra các file view mẫu có sẵn (như views của categories hay materials) để tái sử dụng component giao diện.
