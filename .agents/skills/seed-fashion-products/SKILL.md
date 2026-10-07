---
name: seed-fashion-products
description: Use when importing and seeding fashion product catalogs (JSON + images folder) into Aurelia Store categories, ensuring clean titles, computer vision color detection, 4 sizes (S/M/L/XL), and zero-defect database integrity.
---

# Fashion Product Seeding Skill (Aurelia Store)

## Tổng quan (Overview)

Bộ quy trình và công cụ tự động hóa toàn diện từ A-Z để xử lý dữ liệu cào (raw scraped JSON) và thư mục ảnh lookbook, biến đổi thành danh mục sản phẩm thời trang cao cấp hoàn thiện trên hệ thống Aurelia Store.

Skill này giải quyết triệt để các vấn đề thường gặp:
1. **Làm sạch tên 100%**: Tự động loại bỏ hoàn toàn các mã code kỹ thuật ở đuôi (ví dụ `Tsx1121`, `V62R23T006`), bỏ dấu chấm phẩy `;`, chuẩn hóa chính tả tiếng Việt (`xoè` -> `xòe`, `zen` -> `ren`).
2. **Nhận diện màu sắc tự động (Computer Vision)**: Cắt vùng trang phục và tính khoảng cách Euclidean so khớp vào bảng 18 màu chuẩn của Aurelia Store.
3. **Nhận diện chất liệu thông minh**: Tự động suy đoán chất liệu cao cấp (Lụa Satin, Voan tơ tằm, Dạ tweed, Denim Cotton, Nhung tăm, Tuyết mưa...) dựa trên từ khóa.
4. **Hệ thống 4 Size chuẩn & SKU không bao giờ trùng**: Tự động sinh 4 biến thể (S, M, L, XL) cho mỗi sản phẩm, mã SKU định dạng `AUR-{CODE}-{SIZE}` (tự động cộng hậu tố `-2`, `-3` nếu trùng code ảnh).
5. **Đồng bộ ảnh & Kho hàng**: Tự động copy ảnh vào `storage/app/public/products` và `storage/app/public/variants`, gán `price`, đặt `sale_price = null`, `cost_price = 0`, `stock_quantity = 0`.
6. **Xác thực chất lượng tự động**: Tự động kiểm tra database, biến thể, ảnh và kiểm tra HTTP 200 trang danh mục ngay sau khi nạp.

---

## Khi nào sử dụng (When to Use)

- Người dùng cung cấp file JSON và thư mục ảnh (hoặc URL cào dữ liệu) và yêu cầu nạp vào một danh mục (ví dụ: *Chân váy dáng ôm*, *Chân váy maxi*, *Chân váy dáng suông*, *Đầm dạ hội*...).
- Cần chuẩn hóa dữ liệu catalog thô thành dữ liệu thương mại điện tử chuẩn chỉ, sẵn sàng cho khách hàng mua sắm và thủ kho nhập hàng.

---

## Cấu trúc Bộ công cụ (Tooling Architecture)

```
.agents/skills/seed-fashion-products/
├── SKILL.md                          # Tài liệu hướng dẫn này
└── scripts/
    ├── prepare_catalog.py            # Python: Làm sạch tên, CV nhận diện màu, đoán chất liệu
    └── verify_catalog.php            # PHP: Script kiểm định chất lượng dữ liệu & web HTTP 200

app/Console/Commands/
└── ImportCatalogProductsCommand.php  # Lệnh Artisan 1-Click: php artisan catalog:import
```

---

## Hướng dẫn Thực hiện 1-Click (Quick Workflow)

Khi người dùng yêu cầu:
> *"@[duong_dan.json] @[thu_muc_anh] tiếp tục thêm vào Chân váy dáng ôm"*

Thực hiện ngay lệnh duy nhất sau tại thư mục gốc dự án:

```bash
php artisan catalog:import \
  --json="path/to/products.json" \
  --images="path/to/images" \
  --category="Chân váy dáng ôm" \
  --prepare
```

### Các tham số tùy chọn:
- `--json`: Đường dẫn file JSON nguồn (bắt buộc).
- `--images`: Thư mục chứa ảnh lookbook (bắt buộc, mặc định `images`).
- `--category`: Tên, slug hoặc ID của danh mục đích (ví dụ: `"Chân váy dáng ôm"` hoặc `11` hoặc `"chan-vay-dang-om"`).
- `--parent`: ID hoặc slug danh mục cha (nếu danh mục mới tạo cần gán cha, ví dụ: `6` cho Chân Váy hoặc `4` cho Sản Phẩm).
- `--prepare`: Tự động gọi script Python Computer Vision để nhận diện màu sắc và làm sạch tên sản phẩm trước khi nạp vào DB.

---

## Chi tiết Quy trình Chuẩn hóa Dữ liệu

### 1. Chuẩn hóa Tên Sản Phẩm (Title Sanitization)
Áp dụng các quy tắc nghiêm ngặt:
- Xóa mã code ở đuôi: Regex `\s+[A-Za-z]+\d+[A-Za-z0-9]*$` và `\s+[A-Z0-9]{5,}$`.
- Xóa mã code khớp với trường `code` (ví dụ: `V62R26H010`).
- Thay thế dấu `;` bằng khoảng trắng: `str_replace(';', ' ', $name)`.
- Chuẩn hóa chính tả: `xoè` -> `xòe`, `zen` -> `ren`.
- Chuẩn hóa khoảng trắng thừa: `preg_replace('/\s+/', ' ', $name)`.

### 2. Nhận diện Màu sắc (Color Matching)
Bảng 18 màu sắc chuẩn của Aurelia Store:
`Đen`, `Trắng`, `Trắng Kem`, `Đỏ`, `Vàng`, `Xanh Da Trời`, `Xanh Denim`, `Xanh Navy`, `Xanh Baby`, `Xanh Bơ`, `Xanh Lục Bảo`, `Hồng Pastel`, `Tím Pastel`, `Cam Đào`, `Nâu Cà Phê`, `Nâu Tây`, `Nâu Ghi`, `Xám Ghi`.

Thuật toán CV trong `prepare_catalog.py`:
- Cắt vùng trang phục: `box = (0.25 * w, 0.42 * h, 0.75 * w, 0.75 * h)`.
- Loại bỏ nền studio: lọc bỏ các pixel có RGB > 235 hoặc xám nhạt đồng nhất.
- Tính khoảng cách màu Euclidean có trọng số nhận thức (`R: 0.3, G: 0.59, B: 0.11`) đến bảng 18 màu chuẩn để chọn ra màu phù hợp nhất.

### 3. Quy tắc Quản lý Biến thể & Kho (Variants & Inventory)
- **Kích cỡ**: 100% sản phẩm có đủ 4 size: **S, M, L, XL**.
- **Mã SKU**:
  - Cơ bản: `AUR-{MÃ_CODE}-{SIZE}` (ví dụ: `AUR-V62L26Q001-S`).
  - Trùng code (nhiều ảnh): `AUR-{MÃ_CODE}-{COUNTER}-{SIZE}` (ví dụ: `AUR-M62L25H001-2-S`).
- **Giá bán & Khuyến mãi**:
  - `price`: Giá gốc niêm yết (số nguyên VNĐ).
  - `sale_price`: `NULL` (không tự ý đặt khuyến mại khi chưa có chiến dịch).
  - `cost_price`: `0.00` (sẽ được cập nhật khi tạo phiếu nhập kho).
  - `stock_quantity`: `0` (sẽ được cập nhật qua phiếu nhập kho).
- **Slug sản phẩm**: Đảm bảo duy nhất, nếu trùng tên tự động sinh `slug-2`, `slug-3`.

---

## Quy trình Kiểm tra & Xác minh (Verification Checklist)

Lệnh `catalog:import` đã tích hợp tự động bước kiểm định này. Tuy nhiên, bất cứ lúc nào bạn cũng có thể chạy kiểm tra độc lập:

```bash
php .agents/skills/seed-fashion-products/scripts/verify_catalog.php --category="Tên danh mục"
```

### Tiêu chí kiểm định đạt chuẩn (Success Criteria):
1. **Sản phẩm**: 100% sản phẩm có tên sạch, không dính mã code, mô tả đầy đủ.
2. **Biến thể**: Tỷ lệ biến thể/sản phẩm đúng bằng 4.0 (mỗi sản phẩm có đủ S, M, L, XL).
3. **Giá bán & Kho**: `sale_price = NULL`, `cost_price = 0`, `stock_quantity = 0`.
4. **Hình ảnh**: 100% ảnh đại diện và ảnh biến thể tồn tại trong `storage/app/public/`.
5. **Giao diện Client**: Truy cập URL `http://127.0.0.1:8000/danh-muc/{slug}` phản hồi HTTP **200 OK**.
