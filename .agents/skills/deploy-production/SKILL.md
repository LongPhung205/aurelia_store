---
name: deploy-production
description: Checklist và thứ tự các lệnh cần thiết để Deploy (đưa mã nguồn) dự án Laravel lên máy chủ Production an toàn, tránh downtime.
---

# Quy trình Deploy Code lên Server Thực Tế (Production)

Đây là checklist các bước cần thực hiện qua Terminal (SSH) trên máy chủ VPS/Linux để đảm bảo code mới hoạt động mà không làm sập web.

## 1. Cập nhật mã nguồn
1. Xóa cache cũ để tránh xung đột:
   ```bash
   php artisan optimize:clear
   ```
2. Cập nhật code mới nhất từ Git (hoặc file zip):
   ```bash
   git pull origin main
   ```

## 2. Cài đặt thư viện (Chỉ cập nhật thay đổi)
Cài đặt thư viện composer (LOẠI BỎ CÁC GÓI DEV để nhẹ và bảo mật):
```bash
composer install --optimize-autoloader --no-dev
```

## 3. Cập nhật Cơ sở dữ liệu
Chạy migrate (Luôn thêm tham số `--force` trên production vì Laravel sẽ hỏi chặn lại):
```bash
php artisan migrate --force
```

## 4. Tối ưu hóa (Optimization)
Chạy các lệnh cache để tăng tốc độ load web (RẤT QUAN TRỌNG):
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 5. Khởi động lại các tiến trình ngầm (Workers)
Nếu dự án có dùng Queue hoặc Horizon, bạn phải báo cho chúng biết là code đã đổi để chúng nạp lại code mới:
```bash
php artisan queue:restart
```
(Nếu dùng Supervisor, tiến trình sẽ tự động được khởi động lại).

## 6. Phân quyền (Nếu cần)
Kiểm tra lại quyền truy cập thư mục cache và lưu trữ:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```
*(Thay www-data bằng user chạy web server tương ứng như nginx/apache).*
