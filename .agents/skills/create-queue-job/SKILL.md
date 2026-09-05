---
name: create-queue-job
description: Hướng dẫn tạo và cấu hình Background Jobs (Queue) trong Laravel để xử lý các tác vụ nặng (như gửi email xác nhận, resize ảnh) mà không làm đứng trình duyệt.
---

# Quy trình xử lý ngầm (Jobs & Queues)

## 1. Cấu hình Queue
1. Đảm bảo biến môi trường `QUEUE_CONNECTION` trong file `.env` được set là `database` hoặc `redis` (trên local có thể để `sync` để debug, nhưng production tuyệt đối không dùng `sync`).
2. Nếu dùng `database`, chạy `php artisan queue:table` và `php artisan migrate`.

## 2. Tạo Job Class
1. Chạy lệnh: `php artisan make:job JobName`
2. Mở file job trong `app/Jobs/JobName.php`.
   - **Cấu hình Retry & Timeout (Rất quan trọng):** Khai báo thêm thuộc tính `public $tries = 3;` (số lần thử lại tối đa nếu gặp lỗi) và `public $timeout = 120;` (thời gian chạy tối đa là 120 giây) ở đầu class để tránh việc một job bị treo vô thời hạn làm nghẽn toàn bộ hàng đợi.
   - Truyền các dữ liệu cần thiết (Model, Mảng dữ liệu) vào hàm `__construct`. Lưu ý: Nếu truyền Model, Laravel tự động dùng `SerializesModels` để chỉ lưu ID, giúp giảm kích thước payload.
   - Viết logic thực hiện tác vụ nặng vào hàm `handle()`.

## 3. Thực thi (Dispatch) Job
Tại Controller hoặc Service, gọi Job:
```php
JobName::dispatch($data);
```
Nếu muốn delay: `JobName::dispatch($data)->delay(now()->addMinutes(5));`

## 4. Chạy Worker (Rất quan trọng)
Job sẽ không tự chạy nếu không có Worker.
- Trên môi trường Dev: Mở terminal chạy `php artisan queue:work`.
- Trên Production: Phải cấu hình **Supervisor** trên Linux để đảm bảo tiến trình `queue:work` luôn chạy ngầm và tự khởi động lại nếu bị crash.

## 5. Xử lý lỗi (Failed Jobs)
- Implement phương thức `failed(\Throwable $exception)` bên trong Job class để thực hiện hành động phụ (ví dụ: gửi thông báo qua Slack/Email) nếu Job thất bại sau nhiều lần thử.
