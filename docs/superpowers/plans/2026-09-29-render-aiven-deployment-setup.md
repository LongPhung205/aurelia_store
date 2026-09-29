# Render & Aiven Deployment Setup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Xây dựng và hoàn thiện toàn bộ bộ tập tin cấu hình triển khai Docker, Nginx, PHP-FPM, Entrypoint, Seeder Admin bảo mật và biến môi trường để đưa dự án **Aurelia Store** lên Render.com (Web Service) kết nối cơ sở dữ liệu Aiven MySQL theo tài liệu mẫu.

**Architecture:** Sử dụng Docker multi-stage build kết hợp:
1. `node:20-alpine` biên dịch Vite & Tailwind CSS assets.
2. `php:8.2-fpm-alpine` cài đặt PHP extensions (`pdo_mysql`, `gd`, `zip`, `bcmath`, `opcache`, v.v.) và Composer dependencies tối ưu hóa production.
3. Nginx đóng vai trò Web Server tiếp nhận request và chuyển qua PHP-FPM port 9000.
4. Shell script `entrypoint.sh` quản lý chứng chỉ CA Aiven SSL từ Render Secret File, cấp quyền thư mục `storage/`, chạy `migrate --force`, kích hoạt `AdminUserSeeder`, và giám sát song song Nginx + PHP-FPM.

**Tech Stack:** Docker, Nginx, PHP 8.2 Alpine, PHP-FPM, Composer, Node.js 20, Vite, Laravel 12, MySQL (Aiven SSL CA).

## Global Constraints

- Phải đảm bảo file `entrypoint.sh` sử dụng định dạng ngắt dòng Unix (LF), tuyệt đối không để dính CRLF của Windows để tránh lỗi `exec format error` trên Linux container.
- Không được đưa thông tin nhạy cảm (APP_KEY thật, mật khẩu database thật, secret key PayOS) vào mã nguồn hay Dockerfile; tất cả cấu hình qua biến môi trường.
- Hỗ trợ đầy đủ việc build assets của Vite (`npm run build`) để không bị vỡ giao diện trên production.
- Hỗ trợ SSL CA của Aiven MySQL thông qua Render Secret Files (`/etc/secrets/ca.pem`).
- Cho phép Render reverse proxy hoạt động chính xác bằng cách khai báo `trustProxies('*')` trong Laravel.

---

### Task 1: Cấu hình Seeding Admin bảo mật & Reverse Proxy Laravel

**Files:**
- Create: `config/seeding.php`
- Create: `database/seeders/AdminUserSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `bootstrap/app.php`
- Test: `tests/Feature/AdminUserSeederTest.php`

**Interfaces:**
- Consumes: Biến môi trường `SEED_ADMIN_NAME`, `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD`.
- Produces: Tài khoản `User` với role `admin` và `email_verified_at` hợp lệ mà không hardcode credentials trong code.

- [x] **Step 1: Viết test cho AdminUserSeeder**
  Tạo `tests/Feature/AdminUserSeederTest.php` kiểm tra:
  - Khi chưa cấu hình email hoặc email không hợp lệ -> ném ngoại lệ.
  - Khi mật khẩu ngắn hơn 12 ký tự -> ném ngoại lệ.
  - Khi hợp lệ -> tạo tài khoản Admin và `email_verified_at` không rỗng.
  - Khi tài khoản admin đã tồn tại -> giữ nguyên mật khẩu cũ, không tạo trùng.

- [x] **Step 2: Chạy test để xác nhận test thất bại**
  ```powershell
  php artisan test tests/Feature/AdminUserSeederTest.php
  ```

- [x] **Step 3: Tạo `config/seeding.php`**
  Đọc thông tin cấu hình từ `env()`:
  ```php
  <?php
  return [
      'admin' => [
          'name' => env('SEED_ADMIN_NAME', 'Aurelia Admin'),
          'email' => env('SEED_ADMIN_EMAIL'),
          'password' => env('SEED_ADMIN_PASSWORD'),
      ],
  ];
  ```

- [x] **Step 4: Tạo `database/seeders/AdminUserSeeder.php`**
  Thực hiện logic kiểm tra email, validate mật khẩu >= 12 ký tự, dùng `forceFill` tạo admin với `email_verified_at = now()`.

- [x] **Step 5: Cập nhật `database/seeders/DatabaseSeeder.php`**
  Thêm gọi `AdminUserSeeder::class` trong luồng seeder.

- [x] **Step 6: Cấu hình `trustProxies` trong `bootstrap/app.php`**
  Thêm `$middleware->trustProxies(at: '*');` để nhận diện đúng giao thức HTTPS từ Render proxy.

- [x] **Step 7: Chạy lại test xác nhận thành công**
  ```powershell
  php artisan test tests/Feature/AdminUserSeederTest.php
  ```

- [x] **Step 8: Commit Git**
  ```powershell
  git add config/seeding.php database/seeders/AdminUserSeeder.php database/seeders/DatabaseSeeder.php bootstrap/app.php tests/Feature/AdminUserSeederTest.php
  git commit -m "feat(deploy): add AdminUserSeeder and configure trusted proxies for Render"
  ```

---

### Task 2: Xây dựng các tập tin cấu hình Docker Server

**Files:**
- Create: `docker/nginx.conf`
- Create: `docker/php.ini`
- Create: `docker/php-fpm.conf`
- Create: `docker/check-ca.php`
- Create: `docker/entrypoint.sh`
- Create: `docker/render.env.example`

**Interfaces:**
- Consumes: Cổng `${PORT}` từ Render, Secret File CA tại `${MYSQL_ATTR_SSL_CA}`, biến cờ `${RUN_MIGRATIONS}`, `${RUN_SEEDERS}`.
- Produces: Môi trường chạy web Nginx + PHP-FPM được cấu hình tối ưu tài nguyên và tự động điều phối khi khởi động container.

- [x] **Step 1: Tạo `docker/nginx.conf`**
  Cấu hình server block lắng nghe `0.0.0.0:${PORT}`, chuyển hướng FastCGI sang `127.0.0.1:9000`, phục vụ `public/index.php`, chặn các file ẩn và file PHP ngoài luồng.

- [x] **Step 2: Tạo `docker/php.ini`**
  Tắt `expose_php`, tắt `display_errors`, bật `log_errors`, thiết lập `memory_limit=256M`, `upload_max_filesize=15M`, `post_max_size=20M`, bật OPcache cho môi trường production.

- [x] **Step 3: Tạo `docker/php-fpm.conf`**
  Cấu hình pool `[www]` lắng nghe `127.0.0.1:9000`, `clear_env = no` để nhận biến môi trường, thiết lập process manager `ondemand`, `max_children = 5` phù hợp với gói Render Free.

- [x] **Step 4: Tạo `docker/check-ca.php`**
  Script kiểm tra file CA có tồn tại, đọc được và chứa header `-----BEGIN CERTIFICATE-----` hợp lệ.

- [x] **Step 5: Tạo `docker/entrypoint.sh`**
  - Bật kiểm soát lỗi: `set -Eeuo pipefail`.
  - Sao chép và phân quyền file CA sang `/run/app-certificates/mysql-ca.pem` (`chmod 400`, `chown www-data:www-data`).
  - Dùng `envsubst` thay `${PORT}` vào template cấu hình Nginx.
  - Phân quyền thư mục `storage` và `bootstrap/cache`.
  - Chạy `php artisan config:cache`.
  - Kiểm tra và chạy `php artisan migrate --force` nếu `RUN_MIGRATIONS=true`.
  - Kiểm tra và chạy `php artisan db:seed --force` nếu `RUN_SEEDERS=true`.
  - Cache route và view: `route:cache`, `view:cache`.
  - Kiểm tra cú pháp `nginx -t` và `php-fpm -t`.
  - Bẫy tín hiệu (traps) và khởi chạy song song Nginx + PHP-FPM.

- [x] **Step 6: Tạo `docker/render.env.example`**
  Tập hợp danh sách biến mẫu để dán vào Render Environment Dashboard.

- [x] **Step 7: Kiểm tra cú pháp PHP & Shell scripts**
  ```powershell
  php -l docker/check-ca.php
  ```

- [x] **Step 8: Commit Git**
  ```powershell
  git add docker/
  git commit -m "feat(docker): add server configs, check-ca script, and entrypoint handler"
  ```

---

### Task 3: Xây dựng Dockerfile Multi-Stage & .dockerignore

**Files:**
- Create: `.dockerignore`
- Create: `Dockerfile`

**Interfaces:**
- Consumes: Toàn bộ mã nguồn dự án, `package.json`, `composer.json`.
- Produces: Docker Image hoàn chỉnh có sẵn compiled Vite assets (`public/build`), vendor composer, và cấu hình runtime.

- [x] **Step 1: Tạo `.dockerignore`**
  Loại bỏ `vendor`, `node_modules`, `.env`, `.git`, `.agents`, `storage`, `tests`, `scratch`, các file `.sql`, `.pem`, `.key`.

- [x] **Step 2: Tạo `Dockerfile`**
  - **Stage 1 (node-build):** Base `node:20-alpine`, copy `package*.json`, chạy `npm ci`, copy code và chạy `npm run build`.
  - **Stage 2 (php-base):** Base `php:8.2-fpm-alpine`, cài đặt packages hệ thống (nginx, curl, gettext, su-exec, tini, ca-certificates) và extensions (`pdo_mysql`, `mbstring`, `zip`, `gd`, `bcmath`, `opcache`).
  - **Stage 3 (build):** Cài Composer dependencies `--no-dev --optimize-autoloader`, copy `public/build` từ Stage 1.
  - **Stage 4 (production):** Copy ứng dụng, các file config `docker/`, cấp quyền, khai báo `HEALTHCHECK` trên đường dẫn `/up` và đặt `ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/app-entrypoint"]`.

- [x] **Step 3: Commit Git**
  ```powershell
  git add Dockerfile .dockerignore
  git commit -m "feat(docker): add multi-stage Dockerfile supporting Vite and .dockerignore"
  ```

---

### Task 4: Kiểm tra tính tương thích Cache & Line Endings (Dry Run)

**Files:**
- Validate: `docker/entrypoint.sh`
- Validate: Toàn bộ routes, config, view caching

- [x] **Step 1: Kiểm tra cấu hình và route caching**
  Đảm bảo không có closure không thể serialize trong file config/route:
  ```powershell
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```
- [x] **Step 2: Dọn dẹp cache về trạng thái phát triển local**
  ```powershell
  php artisan optimize:clear
  ```

- [x] **Step 3: Đảm bảo định dạng xuống dòng LF cho `docker/entrypoint.sh`**
  Sử dụng script để xác nhận file không dính CRLF của Windows.

---

### Task 5: Tạo tài liệu hướng dẫn và Checklist triển khai thực tế

**Files:**
- Create: `docs/HUONG_DAN_DEPLOY_RENDER_AIVEN.md`

- [x] **Step 1: Viết tài liệu `docs/HUONG_DAN_DEPLOY_RENDER_AIVEN.md`**
  Tổng hợp hướng dẫn với hình minh họa các bước:
  1. Hướng dẫn tạo MySQL trên Aiven & cách tải `ca.pem`.
  2. Hướng dẫn tạo Web Service trên Render & thêm Secret File.
  3. Bảng tra cứu giá trị biến môi trường chuẩn bị sẵn cho `aurelia_store`.
  4. Các bước khởi tạo Admin và kích hoạt Seeding an toàn.
  5. Checklist xử lý lỗi phổ biến (lỗi kết nối SSL, lỗi 502, lỗi CSRF webhook PayOS).

- [x] **Step 2: Commit Git tài liệu**
  ```powershell
  git add docs/HUONG_DAN_DEPLOY_RENDER_AIVEN.md
  git commit -m "docs: add comprehensive deployment guide for Render and Aiven"
  ```
