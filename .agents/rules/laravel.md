# 📜 Bộ Tiêu Chuẩn & Quy Tắc Code (Coding Rules) Cho Dự Án Laravel

## 1. Kiến Trúc MVC & Xử Lý Logic (Architecture)

*   **Thin Controller, Fat Model:** Controller phải cực kỳ gọn gàng. Controller chỉ nên nhận Request, gọi Model/Service để xử lý và trả về View/Response. KHÔNG viết các đoạn logic tính toán phức tạp vào Controller.
*   **Phân Tách Module Rõ Ràng (Admin & Client):** 
    *   Tất cả Controller quản trị nằm trong `App\Http\Controllers\Admin`.
    *   Tất cả Controller giao diện người dùng nằm trong `App\Http\Controllers\Client`.
    *   Tất cả View tương ứng nằm trong `resources/views/admin` và `resources/views/client`.
*   **Sử Dụng Form Request:** Bắt buộc sử dụng `Form Request` để xử lý Validation (kiểm tra dữ liệu). Tuyệt đối không dùng `$request->validate()` trực tiếp trong Controller.
*   **Phân Quyền Bằng Middleware:** Việc kiểm tra phân quyền (Authorization) bắt buộc sử dụng **Middleware** (ví dụ: `AdminMiddleware`, `RoleMiddleware`) để chặn các request không hợp lệ ngay từ lúc truy cập route. Không check quyền lẻ tẻ bên trong Controller.
*   **Tránh Truy Vấn DB Trong View:** Không được sử dụng các câu lệnh truy vấn database trực tiếp bên trong file `.blade.php`. Dữ liệu phải được chuẩn bị ở Controller và truyền sang View.

## 2. Quy Tắc Đặt Tên & Tạo File (Naming & Artisan Commands)

*   **Tạo Controller/Request với Namespace:** Khi dùng Artisan, luôn gõ kèm thư mục phân hệ để Laravel tự tạo đúng namespace:
    *   `php artisan make:controller Admin/ProductController --resource`
    *   `php artisan make:request Admin/Product/StoreProductRequest`
*   **Controller:** `PascalCase`, số ít, luôn có hậu tố `Controller`. (VD: `ProductController`).
*   **Model:** `PascalCase`, số ít. (VD: `Product`, `Category`).
*   **Bảng DB (Table):** `snake_case`, số nhiều. (VD: `products`, `categories`).
*   **Biến & Hàm (Variables/Methods):** `camelCase`. (VD: `$productName`, `calculateTotal()`).
*   **Khóa ngoại (Foreign Key):** `tên_model_dạng_số_ít_id`. (VD: `category_id`).

## 3. Database & Cấu Trúc Dữ Liệu

*   **Chỉ Sử Dụng Migration:** Bất kỳ thay đổi nào về cấu trúc bảng đều phải được thực hiện thông qua file `Migration`. Tuyệt đối không sửa trực tiếp bằng phpMyAdmin.
*   **Dữ Liệu Mẫu (Dummy Data):** Sử dụng `Seeder` và `Factory` để tạo dữ liệu test.
*   **Soft Deletes:** Đối với các dữ liệu quan trọng, ưu tiên sử dụng `SoftDeletes`.

## 4. Routing & URLs

*   **Phân Nhóm Route Rõ Ràng:**
    *   Admin routes: prefix `admin`, name prefix `admin.` (VD: `admin.products.index`, `admin.categories.create`).
    *   Client routes: name prefix `client.` (VD: `client.home`, `client.products.detail`).
*   **Resource Routes:** Ưu tiên sử dụng `Route::resource()` cho các chức năng CRUD chuẩn.
*   **Gọi Route:** Trong View/Controller, luôn sử dụng hàm `route('tên_route')` thay vì viết cứng URL.

## 5. View (Blade Template)

*   **Kế Thừa Layout & Nhúng View:**
    *   Admin views luôn kế thừa `@extends('admin.layouts.admin')` (hoặc `@extends('admin.layouts.master')`).
    *   Client views luôn kế thừa `@extends('client.layouts.app')` (hoặc `@extends('client.layouts.master')`).
    *   Nhúng component/subview: `@include('admin.layouts.sidebar')`, `@include('admin.layouts.header')`.
*   **Form Action & Links:** Luôn kiểm tra kỹ tên route đầy đủ trong form action: `<form action="{{ route('admin.categories.store') }}" method="POST">`.

## 6. Bảo Mật & Môi Trường (Security & Environment)

*   **Không Dùng Hàm env():** Trừ trong các file `config/*.php`, tuyệt đối KHÔNG sử dụng hàm `env()` trực tiếp trong code. Hãy sử dụng hàm `config('tên_biến')`.
