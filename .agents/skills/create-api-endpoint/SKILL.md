---
name: create-api-endpoint
description: Quy trình chuẩn để xây dựng API RESTful cho Mobile App hoặc SPA (React, Vue) trong dự án Laravel 11.
---

# Quy trình Xây dựng API Endpoint chuẩn RESTful

Khi dự án yêu cầu mở rộng API, hãy tuân thủ quy trình sau để đảm bảo API dễ bảo trì và dễ tích hợp cho team Mobile/Frontend.

## 1. Định tuyến (Routes)
- Đặt routes trong `routes/api.php` (Các route này mặc định được Laravel thêm prefix `/api` và không dính tới web session).
- Sử dụng phiên bản cho API: `Route::prefix('v1')->group(...)`.

## 2. Controller & Form Request
- Tạo API Controller (không có các hàm view như create/edit): `php artisan make:controller Api/V1/ModelNameController --api`
- Validate dữ liệu bằng Form Request tương tự như web, nhưng nếu lỗi, Laravel sẽ tự động trả về lỗi JSON chuẩn `422 Unprocessable Entity` thay vì redirect.

## 3. Format Response Bằng API Resources
Tuyệt đối tránh `return $model;` trực tiếp. Hãy dùng API Resources để kiểm soát chính xác cấu trúc JSON trả về, ẩn đi các cột nhạy cảm.
1. Tạo resource: `php artisan make:resource ModelNameResource` (hoặc `ModelNameCollection` cho danh sách).
2. Trong hàm `toArray`, map các cột cần thiết:
   ```php
   return [
       'id' => $this->id,
       'title' => $this->name,
       'created_at' => $this->created_at->format('Y-m-d H:i:s'),
   ];
   ```
3. Controller trả về: `return new ModelNameResource($model);` hoặc `return ModelNameResource::collection($models);`

## 4. Chuẩn hóa format phản hồi (ApiResponse)
Nên tạo một Trait hoặc BaseController để chuẩn hóa dạng JSON trả về cho toàn hệ thống:
```json
{
    "success": true,
    "message": "Lấy danh sách thành công",
    "data": { ... }
}
```

## 5. Xác thực (Authentication)
Dùng `Laravel Sanctum` hoặc `Laravel Passport` để quản lý Token (Bearer Token). Tuyệt đối không dùng Auth Session cho API.
