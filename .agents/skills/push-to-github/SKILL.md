---
name: push-to-github
description: Use when you need to stage, commit, and push code changes to a GitHub repository following a standard Git workflow.
---

# Quy Trình Đẩy Code Lên GitHub (Push to GitHub)

## Overview
Kỹ năng này cung cấp quy trình chuẩn để kiểm tra, đóng gói (stage), ghi lại (commit) và đẩy (push) mã nguồn lên GitHub. Việc tuân thủ quy trình này giúp tránh xung đột mã nguồn (conflict), đảm bảo lịch sử thay đổi rõ ràng và dễ dàng phối hợp làm việc nhóm.

## When to Use
- Khi hoàn thành một tính năng, sửa một lỗi (bug fix) hoặc cập nhật tài liệu.
- Khi cần lưu lại tiến độ công việc lên remote repository (GitHub) để tránh mất mát dữ liệu.
- Khi người dùng yêu cầu "đẩy code", "push code", "commit code", hoặc "lưu code lên github".

## Core Workflow

### Bước 1: Kiểm tra trạng thái mã nguồn (Check Status)
Luôn kiểm tra xem những file nào đã bị thay đổi, file nào mới thêm vào hoặc bị xóa để tránh commit nhầm các file không cần thiết.
```bash
git status
```

### Bước 2: Thêm thay đổi vào Staging Area (Stage Changes)
Chỉ thêm những file liên quan đến tính năng hoặc lỗi đang xử lý.
```bash
# Thêm tất cả thay đổi (cần cẩn thận kiểm tra bằng git status trước)
git add .

# Thêm từng file hoặc thư mục cụ thể (Khuyên dùng)
git add <đường_dẫn_file_hoặc_thư_mục>
```

### Bước 3: Ghi lại thay đổi (Commit)
Sử dụng thông điệp commit (commit message) rõ ràng, mang tính mô tả. Nên theo chuẩn Conventional Commits (`loại: thông điệp`).
Các loại phổ biến: `feat` (tính năng mới), `fix` (sửa lỗi), `docs` (tài liệu), `refactor` (cải thiện code), `chore` (cấu hình/linh tinh).

```bash
git commit -m "feat: thêm chức năng đăng nhập qua Google"
```

### Bước 4: Cập nhật code mới nhất từ Remote (Pull)
Nếu làm việc chung trên một nhánh, hãy luôn pull code mới nhất từ GitHub về máy trước khi push để tránh lỗi "non-fast-forward" (rejected push).
```bash
git pull origin <tên_nhánh>
```
*Lưu ý: Nếu có conflict (xung đột) xảy ra ở bước này, cần mở file lên để xử lý conflict, sau đó chạy lại `git add .` và `git commit -m "Merge conflict resolved"`.*

### Bước 5: Đẩy code lên GitHub (Push)
Đẩy các commit đã lưu ở máy (local) lên máy chủ GitHub (remote).
```bash
git push origin <tên_nhánh>
```
*Lưu ý: Nếu đây là lần push đầu tiên của một nhánh mới tạo trên máy, cần thiết lập upstream bằng lệnh:*
```bash
git push -u origin <tên_nhánh>
```

## Common Mistakes

| Sai lầm thường gặp | Hậu quả / Cách khắc phục |
|---|---|
| **Quên `git pull` trước khi `push`** | Bị lỗi `rejected` do mã trên GitHub mới hơn mã trên máy. **Cách sửa:** Chạy `git pull origin <tên_nhánh>`, giải quyết xung đột (nếu có), commit rồi push lại. |
| **Ghi commit message chung chung** | Ví dụ: "update", "fix bug". Gây khó khăn lớn khi cần tra cứu lịch sử thay đổi sau này. **Cách sửa:** Ghi cụ thể nội dung thay đổi (VD: "fix: lỗi không tính phí ship khi đặt hàng"). |
| **Dùng `git add .` bừa bãi** | Dễ commit nhầm các file log, file cấu hình cá nhân hoặc các file rác (do `.gitignore` thiết lập thiếu). **Cách sửa:** Dùng `git status` kiểm tra kỹ trước khi add. |
| **Push nhầm nhánh** | Có thể làm hỏng nhánh `main` hoặc ghi đè nhánh của người khác. **Cách sửa:** Luôn nhìn tên nhánh hiện tại trong terminal hoặc chạy `git branch` trước khi thực hiện push. |
