#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Script tự động chuẩn hóa dữ liệu sản phẩm thời trang:
1. Làm sạch tên sản phẩm (loại bỏ hoàn toàn mã code ở đuôi, sửa dấu ';', chuẩn hóa 'xoè' -> 'xòe', 'zen' -> 'ren').
2. Tự động nhận diện màu sắc bằng Computer Vision (Pillow) dựa vào khoảng cách màu RGB đến bảng màu chuẩn Aurelia Store.
3. Tự động suy đoán chất liệu thời trang từ tên sản phẩm.
4. Chuẩn hóa giá bán, kiểm tra file ảnh tồn tại.
"""

import argparse
import json
import os
import re
import sys

try:
    from PIL import Image
except ImportError:
    print("Warning: Thư viện Pillow chưa được cài đặt. Chạy 'pip install pillow' để nhận diện màu sắc.")
    Image = None

# Bảng màu chuẩn thời trang Aurelia Store (Tên màu -> RGB trung tâm)
SHOP_COLORS = {
    'Đen': (25, 25, 25),
    'Trắng': (250, 250, 250),
    'Trắng Kem': (245, 240, 230),
    'Đỏ': (205, 30, 45),
    'Vàng': (245, 215, 60),
    'Xanh Da Trời': (70, 150, 240),
    'Xanh Denim': (60, 90, 150),
    'Xanh Navy': (25, 35, 75),
    'Xanh Baby': (160, 200, 240),
    'Xanh Bơ': (180, 210, 140),
    'Xanh Lục Bảo': (15, 105, 90),
    'Hồng Pastel': (245, 195, 210),
    'Tím Pastel': (210, 180, 225),
    'Cam Đào': (255, 170, 145),
    'Nâu Cà Phê': (80, 50, 40),
    'Nâu Tây': (120, 80, 60),
    'Nâu Ghi': (140, 115, 105),
    'Xám Ghi': (155, 155, 155),
}

def clean_product_name(raw_name: str, code: str = '') -> str:
    """Làm sạch tên sản phẩm thuần túy thời trang cao cấp."""
    name = (raw_name or '').strip()
    
    # 1. Bỏ mã sản phẩm nếu dính ở cuối tên
    if code:
        name = re.sub(rf'\s+{re.escape(code)}$', '', name, flags=re.IGNORECASE)
        # Bỏ cả phần code dạng ' - CODE' hoặc ' (CODE)'
        name = re.sub(rf'\s*[-_/(]\s*{re.escape(code)}\s*[)/]?$', '', name, flags=re.IGNORECASE)
    
    # 2. Bỏ các mã code dạng Tsx1121, V62R23T006, M62L25H001 ở cuối (phải có cả chữ và số hoặc code in hoa dài)
    name = re.sub(r'\s+[A-Za-z]+\d+[A-Za-z0-9]*$', '', name)
    name = re.sub(r'\s+[A-Z0-9]{5,}$', '', name)
    
    # 3. Chuẩn hóa dấu chấm phẩy, từ ngữ chuyên ngành
    name = name.replace(';', ' ')
    name = re.sub(r'\bzen\b', 'ren', name, flags=re.IGNORECASE)
    name = name.replace('xoè', 'xòe').replace('Xoè', 'Xòe')
    
    # 4. Chuẩn hóa khoảng trắng
    name = re.sub(r'\s+', ' ', name).strip()
    return name

def detect_material(name: str) -> str:
    """Suy đoán chất liệu từ từ khóa trong tên sản phẩm."""
    name_lower = name.lower()
    if 'tơ' in name_lower:
        return 'Voan tơ tằm mềm rủ'
    elif 'lụa' in name_lower or 'satin' in name_lower:
        return 'Lụa Satin ngọc trai cao cấp'
    elif 'dạ' in name_lower or 'tweed' in name_lower:
        return 'Dạ tweed dệt sợi ánh kim'
    elif 'jean' in name_lower or 'denim' in name_lower:
        return 'Denim Cotton co giãn nhẹ'
    elif 'ren' in name_lower:
        return 'Ren thêu hoa cao cấp 2 lớp'
    elif 'nhung' in name_lower:
        return 'Nhung tăm cao cấp mềm mịn'
    elif 'kaki' in name_lower or 'khaki' in name_lower:
        return 'Kaki Cotton đứng phom'
    elif 'len' in name_lower:
        return 'Len dệt kim co giãn cao cấp'
    elif 'gấm' in name_lower:
        return 'Gấm dệt hoa văn chìm sang trọng'
    return 'Tuyết mưa cao cấp chống nhăn'

def detect_color_from_image(image_path: str, fallback_name: str = '') -> str:
    """Nhận diện màu sắc của trang phục từ hình ảnh lookbook."""
    # 1. Thử nhận diện từ gợi ý trong tên nếu có từ khóa rõ ràng
    name_lower = fallback_name.lower()
    keyword_map = {
        'đen': 'Đen', 'black': 'Đen',
        'trắng kem': 'Trắng Kem', 'kem': 'Trắng Kem', 'be': 'Trắng Kem',
        'trắng': 'Trắng', 'white': 'Trắng',
        'đỏ': 'Đỏ', 'red': 'Đỏ',
        'vàng': 'Vàng', 'yellow': 'Vàng',
        'xanh denim': 'Xanh Denim', 'jean': 'Xanh Denim',
        'xanh navy': 'Xanh Navy', 'than': 'Xanh Navy',
        'xanh baby': 'Xanh Baby', 'xanh bơ': 'Xanh Bơ',
        'xanh lục': 'Xanh Lục Bảo', 'xanh rêu': 'Xanh Lục Bảo',
        'hồng': 'Hồng Pastel', 'pink': 'Hồng Pastel',
        'tím': 'Tím Pastel',
        'cam': 'Cam Đào',
        'nâu cà phê': 'Nâu Cà Phê', 'nâu': 'Nâu Tây',
        'xám': 'Xám Ghi', 'ghi': 'Xám Ghi',
    }
    
    if not Image or not os.path.exists(image_path):
        for kw, col in keyword_map.items():
            if kw in name_lower:
                return col
        return 'Đen'
    
    try:
        with Image.open(image_path) as img:
            img = img.convert('RGB')
            w, h = img.size
            
            # Cắt vùng trang phục (chân váy thường nằm ở 42% - 75% chiều cao, 25% - 75% chiều rộng)
            crop_box = (int(w * 0.25), int(h * 0.42), int(w * 0.75), int(h * 0.75))
            cropped = img.crop(crop_box)
            cropped = cropped.resize((60, 60), Image.Resampling.BILINEAR)
            
            # Lấy danh sách pixel an toàn
            try:
                pixels = list(cropped.get_flattened_data()) if hasattr(cropped, 'get_flattened_data') else list(cropped.getdata())
            except Exception:
                pixels = [cropped.getpixel((x, y)) for y in range(cropped.height) for x in range(cropped.width)]
            valid_pixels = [
                p for p in pixels
                if not (p[0] > 235 and p[1] > 235 and p[2] > 235)  # Loại bỏ nền trắng
                and not (abs(p[0] - p[1]) < 6 and abs(p[1] - p[2]) < 6 and p[0] > 215) # Nền xám nhạt
            ]
            
            if not valid_pixels:
                # Nếu lọc hết, lấy toàn bộ pixel
                valid_pixels = pixels
                
            # Tính RGB trung bình
            avg_r = sum(p[0] for p in valid_pixels) / len(valid_pixels)
            avg_g = sum(p[1] for p in valid_pixels) / len(valid_pixels)
            avg_b = sum(p[2] for p in valid_pixels) / len(valid_pixels)
            avg_color = (avg_r, avg_g, avg_b)
            
            # Tìm màu gần nhất trong SHOP_COLORS theo khoảng cách Euclidean
            best_color = 'Đen'
            min_dist = float('inf')
            
            for color_name, ref_rgb in SHOP_COLORS.items():
                # Trọng số nhận thức màu sắc (Red * 0.3, Green * 0.59, Blue * 0.11)
                dist = (
                    0.30 * (avg_color[0] - ref_rgb[0]) ** 2 +
                    0.59 * (avg_color[1] - ref_rgb[1]) ** 2 +
                    0.11 * (avg_color[2] - ref_rgb[2]) ** 2
                ) ** 0.5
                if dist < min_dist:
                    min_dist = dist
                    best_color = color_name
                    
            return best_color
    except Exception as e:
        for kw, col in keyword_map.items():
            if kw in name_lower:
                return col
        return 'Đen'

def process_catalog(input_json_path: str, images_dir: str, output_json_path: str):
    if not os.path.exists(input_json_path):
        print(f"Error: Không tìm thấy file JSON nguồn: {input_json_path}")
        sys.exit(1)
        
    with open(input_json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)
        
    if not isinstance(data, list):
        print("Error: JSON phải là mảng danh sách sản phẩm.")
        sys.exit(1)
        
    print(f"Đang xử lý {len(data)} sản phẩm từ {input_json_path}...")
    processed_items = []
    
    for idx, item in enumerate(data):
        raw_name = item.get('name', '')
        code = item.get('code', '').strip()
        price_str = item.get('price', '0')
        
        # 1. Làm sạch tên
        clean_name = clean_product_name(raw_name, code)
        
        # 2. Tìm ảnh cục bộ
        local_img = item.get('local_image', '')
        img_basename = os.path.basename(local_img) if local_img else (f"{code}.jpg" if code else "")
        full_img_path = os.path.join(images_dir, img_basename)
        
        # 3. Nhận diện màu sắc & chất liệu
        color = item.get('color') or detect_color_from_image(full_img_path, clean_name)
        material = item.get('material') or detect_material(clean_name)
        
        # 4. Trích xuất giá chuẩn
        price_num = int(re.sub(r'[^0-9]', '', str(price_str)) or 0)
        if price_num <= 0:
            price_num = 399000
            
        processed_items.append({
            'name': clean_name,
            'code': code,
            'price': price_num,
            'raw_price': price_str,
            'color': color,
            'material': material,
            'local_image': f"images/{img_basename}",
            'image_exists': os.path.exists(full_img_path),
            'url': item.get('url', ''),
            'image_url': item.get('image_url', ''),
        })
        
    os.makedirs(os.path.dirname(os.path.abspath(output_json_path)), exist_ok=True)
    with open(output_json_path, 'w', encoding='utf-8') as f:
        json.dump(processed_items, f, ensure_ascii=False, indent=2)
        
    print(f"✓ Đã hoàn tất xử lý {len(processed_items)} sản phẩm!")
    print(f"✓ Kết quả lưu tại: {output_json_path}")

def main():
    parser = argparse.ArgumentParser(description="Chuẩn hóa dữ liệu catalog thời trang Aurelia Store")
    parser.add_argument('--json', required=True, help="Đường dẫn file JSON nguồn")
    parser.add_argument('--images', required=True, help="Thư mục chứa ảnh sản phẩm")
    parser.add_argument('--output', required=True, help="Đường dẫn file JSON kết quả")
    
    args = parser.parse_args()
    process_catalog(args.json, args.images, args.output)

if __name__ == '__main__':
    main()
