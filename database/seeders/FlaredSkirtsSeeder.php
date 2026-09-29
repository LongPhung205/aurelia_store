<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FlaredSkirtsSeeder extends Seeder
{
    /**
     * Seed 125 flared skirts (Chân váy dáng xòe) from lamer_chan_vay_with_colors.json
     * into 'Chân váy dáng xòe' (ID: 10), 'Chân Váy' (ID: 6), and 'Hàng Mới Về' (ID: 5).
     */
    public function run(): void
    {
        $jsonPath = base_path('scratch/lamer_chan_vay_with_colors.json');
        if (!File::exists($jsonPath)) {
            $jsonPath = base_path('lamer_chan_vay.json');
        }
        if (!File::exists($jsonPath)) {
            $this->command->error("Không tìm thấy file dữ liệu JSON: {$jsonPath}");
            return;
        }

        $items = json_decode(File::get($jsonPath), true);
        if (!$items) {
            $this->command->error("Dữ liệu JSON rỗng hoặc sai định dạng.");
            return;
        }

        // 1. Thư mục đích trong public storage
        $productDir = storage_path('app/public/products');
        $variantDir = storage_path('app/public/variants');
        if (!File::isDirectory($productDir)) {
            File::makeDirectory($productDir, 0755, true);
        }
        if (!File::isDirectory($variantDir)) {
            File::makeDirectory($variantDir, 0755, true);
        }

        // 2. Bảng Colors
        $colorsMap = [
            'Đen'           => '#000000',
            'Trắng'         => '#ffffff',
            'Trắng Kem'     => '#f5f2eb',
            'Đỏ'            => '#ff0000',
            'Vàng'          => '#eeff00',
            'Xanh Da Trời'  => '#006eff',
            'Xanh Denim'    => '#3b5998',
            'Xanh Navy'     => '#1a237e',
            'Xanh Baby'     => '#90caf9',
            'Xanh Bơ'       => '#c5e1a5',
            'Xanh Lục Bảo'  => '#00695c',
            'Hồng Pastel'   => '#f8bbd0',
            'Tím Pastel'    => '#d8b4e2',
            'Cam Đào'       => '#ffab91',
            'Nâu Cà Phê'    => '#5d4037',
            'Nâu Tây'       => '#6d4c41',
            'Nâu Ghi'       => '#8d6e63',
            'Xám Ghi'       => '#9e9e9e',
        ];

        $colorModels = [];
        foreach ($colorsMap as $cName => $hex) {
            $colorModels[$cName] = Color::firstOrCreate(
                ['name' => $cName],
                ['hex_code' => $hex]
            );
        }

        // 3. Kích cỡ S, M, L, XL
        $sizeModels = [];
        foreach (['S', 'M', 'L', 'XL'] as $sName) {
            $sizeModels[$sName] = Size::firstOrCreate(['name' => $sName]);
        }

        // 4. Danh mục: Chân váy dáng xòe (ID 10), Chân Váy (ID 6), Hàng Mới Về (ID 5)
        $flaredCategory = Category::find(10);
        if (!$flaredCategory) {
            $flaredCategory = Category::create([
                'id' => 10,
                'name' => 'Chân váy dáng xòe',
                'slug' => 'chan-vay-dang-xoe',
                'parent_id' => 6,
                'is_active' => 1,
            ]);
        }
        $skirtCategory = Category::find(6);
        $newArrivalCategory = Category::find(5);

        $categoryIds = array_filter([
            $flaredCategory?->id ?? 10,
            $skirtCategory?->id ?? 6,
            $newArrivalCategory?->id ?? 5,
        ]);

        DB::transaction(function () use ($items, $productDir, $variantDir, $colorModels, $sizeModels, $categoryIds) {
            $createdCount = 0;
            $usedSlugs = [];
            $usedSkus = [];
            $codeCounts = [];

            foreach ($items as $idx => $item) {
                $cleanPrice = (float) preg_replace('/[^0-9]/', '', $item['price'] ?? '0');
                if ($cleanPrice <= 0) {
                    $cleanPrice = 399000;
                }

                $imageBasename = basename($item['local_image']);
                $srcImage = base_path('images/' . $imageBasename);
                $destProductImage = $productDir . '/' . $imageBasename;
                $destVariantImage = $variantDir . '/' . $imageBasename;

                if (File::exists($srcImage)) {
                    if (!File::exists($destProductImage)) {
                        File::copy($srcImage, $destProductImage);
                    }
                    if (!File::exists($destVariantImage)) {
                        File::copy($srcImage, $destVariantImage);
                    }
                }

                // Tên sản phẩm sạch 100%, loại bỏ triệt để mã code và ký tự thừa ở đuôi
                $cleanName = trim($item['name'] ?? '');
                if (!empty($item['code'])) {
                    $cleanName = preg_replace('/\s+' . preg_quote($item['code'], '/') . '$/i', '', $cleanName);
                }
                $cleanName = preg_replace('/\s+[A-Za-z]+\d+[A-Za-z0-9]*$/', '', $cleanName);
                $cleanName = preg_replace('/\s+[A-Z0-9]{6,}$/', '', $cleanName);
                $cleanName = str_replace(';', ' ', $cleanName);
                $cleanName = preg_replace('/\bzen\b/ui', 'ren', $cleanName);
                $cleanName = str_replace(['xoè', 'Xoè'], ['xòe', 'Xòe'], $cleanName);
                $cleanName = preg_replace('/\s+/', ' ', $cleanName);
                $cleanName = trim($cleanName);

                // Tạo slug duy nhất
                $baseSlug = Str::slug($cleanName);
                $slug = $baseSlug;
                $counter = 1;
                while (isset($usedSlugs[$slug]) || Product::where('slug', $slug)->exists()) {
                    $counter++;
                    $slug = "{$baseSlug}-{$counter}";
                }
                $usedSlugs[$slug] = true;

                // Đoán chất liệu theo tên
                $material = 'Tuyết mưa cao cấp chống nhăn';
                $nameLower = mb_strtolower($cleanName, 'UTF-8');
                if (str_contains($nameLower, 'tơ')) {
                    $material = 'Voan tơ tằm mềm rủ';
                } elseif (str_contains($nameLower, 'lụa')) {
                    $material = 'Lụa Satin ngọc trai cao cấp';
                } elseif (str_contains($nameLower, 'dạ') || str_contains($nameLower, 'tweed')) {
                    $material = 'Dạ tweed dệt sợi ánh kim';
                } elseif (str_contains($nameLower, 'jean') || str_contains($nameLower, 'denim')) {
                    $material = 'Denim Cotton co giãn nhẹ';
                } elseif (str_contains($nameLower, 'ren')) {
                    $material = 'Ren thêu hoa cao cấp 2 lớp';
                } elseif (str_contains($nameLower, 'nhung')) {
                    $material = 'Nhung tăm cao cấp mềm mịn';
                } elseif (str_contains($nameLower, 'kaki') || str_contains($nameLower, 'khaki')) {
                    $material = 'Kaki Cotton đứng phom';
                }

                $product = Product::create([
                    'name' => $cleanName,
                    'slug' => $slug,
                    'short_description' => "{$cleanName} thiết kế xòe bồng bềnh duyên dáng, che khuyết điểm tối ưu và tôn vẻ đẹp nữ tính thanh lịch.",
                    'description' => "{$cleanName} là sản phẩm thời trang thiết kế công sở cao cấp thuộc bộ sưu tập Chân Váy Dáng Xòe Aurelia Store. Được may từ chất liệu {$material}, đường may tỉ mỉ, phom dáng xòe tự nhiên rủ nhẹ mang lại sự thoải mái và tự tin suốt cả ngày dài. Dễ dàng phối cùng áo sơ mi, áo kiểu hoặc áo len mỏng.",
                    'material' => $material,
                    'status' => 'active',
                    'view_count' => rand(15, 180),
                ]);
                $createdCount++;

                // Gán danh mục: Chân váy dáng xòe (ID 10), Chân Váy (ID 6), Hàng Mới Về (ID 5)
                $product->categories()->sync($categoryIds);

                // Gán ảnh chính cho sản phẩm
                ProductImage::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'image_url' => 'products/' . $imageBasename,
                    ],
                    [
                        'display_order' => 0,
                    ]
                );

                // Màu sắc nhận diện
                $colorName = $item['color'] ?? 'Đen';
                $color = $colorModels[$colorName] ?? $colorModels['Đen'];

                // Tạo 4 biến thể với 4 size (S, M, L, XL) và đảm bảo SKU tuyệt đối không trùng
                $cleanCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $item['code'] ?? 'SKIRT'));
                $codeCounts[$cleanCode] = ($codeCounts[$cleanCode] ?? 0) + 1;
                $codeSuffix = $codeCounts[$cleanCode] > 1 ? "-{$codeCounts[$cleanCode]}" : "";
                $skuPrefix = "AUR-{$cleanCode}{$codeSuffix}";

                foreach ($sizeModels as $sizeName => $sizeModel) {
                    $sku = "{$skuPrefix}-" . strtoupper($sizeName);
                    $dupCounter = 1;
                    while (isset($usedSkus[$sku]) || ProductVariant::where('sku', $sku)->exists()) {
                        $dupCounter++;
                        $sku = "{$skuPrefix}-{$dupCounter}-" . strtoupper($sizeName);
                    }
                    $usedSkus[$sku] = true;

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'color_id' => $color->id,
                        'size_id' => $sizeModel->id,
                        'sku' => $sku,
                        'barcode' => null,
                        'price' => $cleanPrice,
                        'sale_price' => null, // Không thiết lập khuyến mãi theo yêu cầu
                        'cost_price' => 0.00,  // Giá vốn để 0, cập nhật khi nhập kho
                        'stock_quantity' => 0, // Tồn kho để 0, cập nhật khi nhập kho
                        'low_stock_threshold' => 5,
                        'weight_grams' => 250,
                        'thumbnail_url' => 'variants/' . $imageBasename,
                        'is_active' => 1,
                    ]);
                }
            }

            $this->command->info("Hoàn tất nạp Chân Váy Dáng Xòe: Tạo mới {$createdCount} sản phẩm, " . ($createdCount * 4) . " biến thể.");
        });
    }
}
