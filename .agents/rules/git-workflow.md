---
trigger: manual
description: Ensures the AI always commits and pushes code using the push-to-github skill after completing a task.
---

# Git Workflow Enforcement

**Bắt buộc (MANDATORY):**
Mỗi khi bạn (AI) hoàn thành việc triển khai một chức năng mới, sửa một lỗi (bug fix), hoặc hoàn tất bất kỳ một tác vụ (task) nào có làm thay đổi mã nguồn, bạn PHẢI tự động đề xuất việc commit và push code lên GitHub.

**Quy trình:**
1. Khi hoàn thành công việc và code đã được kiểm tra (verify), bạn thông báo cho người dùng rằng công việc đã xong.
2. Ngay lập tức, tự động áp dụng kỹ năng (skill) `push-to-github` để xử lý.
3. Chủ động chạy `git status` để kiểm tra thay đổi.
4. Đề xuất một đoạn commit message hợp lý (theo chuẩn Conventional Commits như `feat: ...`, `fix: ...`) dựa trên những gì bạn vừa làm.
5. Hỏi ý kiến người dùng xem họ có muốn bạn tự động chạy lệnh `git add`, `git commit` và `git push` hay không, HOẶC nếu người dùng đã cấp quyền từ trước, hãy tự động thực thi các lệnh đó.

**Lưu ý:** Không bao giờ chuyển sang tác vụ mới mà không xử lý hoặc nhắc nhở về việc commit code của tác vụ vừa hoàn thành.