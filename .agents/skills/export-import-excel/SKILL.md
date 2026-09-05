---
name: export-import-excel
description: Quy trình sử dụng thư viện maatwebsite/excel để xây dựng chức năng Xuất (Export) và Nhập (Import) dữ liệu Excel chuẩn xác, tối ưu hiệu suất.
---

# Quy trình Xuất/Nhập dữ liệu Excel

## 1. Cài đặt thư viện (Nếu chưa có)
Chạy lệnh cài đặt: `composer require maatwebsite/excel`

## 2. Quy trình Xây dựng Export (Xuất Excel)
1. Tạo class Export: `php artisan make:export ModelNameExport --model=ModelName`
2. Implement các interfaces cần thiết:
   - `FromQuery`: Để lấy dữ liệu không bị quá tải RAM (không dùng `FromCollection` cho dữ liệu lớn).
   - `WithHeadings`: Để định nghĩa tên cột ở dòng đầu tiên.
   - `WithMapping`: Để format lại dữ liệu từng dòng trước khi xuất (ví dụ: chuyển ID thành tên, format ngày tháng).
3. Trong Controller: `return Excel::download(new ModelNameExport, 'filename.xlsx');`

## 3. Quy trình Xây dựng Import (Nhập Excel)
1. Tạo class Import: `php artisan make:import ModelNameImport --model=ModelName`
2. Implement các interfaces cốt lõi:
   - `ToModel`: Để map dữ liệu từng dòng vào DB.
   - `WithHeadingRow`: Để lấy tên cột làm key (thay vì dùng index 0, 1, 2).
   - `WithValidation`: Để validate dữ liệu trước khi lưu.
   - `WithBatchInserts` & `WithChunkReading`: (RẤT QUAN TRỌNG) Để giới hạn số lượng record insert mỗi lần (ví dụ: 1000 dòng/lần), giúp tránh chết Server khi file quá lớn.
3. Trong Controller: `Excel::import(new ModelNameImport, $request->file('file'));`

## 4. Lưu ý chung
- Luôn kiểm tra định dạng file (mimes:xlsx,xls,csv) trong Form Request trước khi đẩy vào class Import.
- Bắt `try-catch` khi Import để thông báo cho người dùng dòng nào bị lỗi thay vì show màn hình lỗi 500.
