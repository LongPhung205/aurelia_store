<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportCatalogProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:import 
                            {--json= : Đường dẫn file JSON sản phẩm} 
                            {--images= : Thư mục chứa ảnh sản phẩm} 
                            {--category= : Tên, Slug hoặc ID danh mục đích} 
                            {--parent= : Tên, Slug hoặc ID danh mục cha (tuỳ chọn)} 
                            {--prepare : Tự động chạy script Python nhận diện màu và làm sạch tên}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nhập catalog sản phẩm thời trang tự động vào Aurelia Store (đồng bộ ảnh, 4 size S/M/L/XL, màu sắc, giá chuẩn, SKU kho)';

    /**
     * Bảng màu chuẩn shop
     */
    protected array $colorsMap = [
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

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $jsonPath = $this->option('json');
        $imagesDir = $this->option('images');
        $catInput = $this->option('category');

        if (!$jsonPath || !$catInput) {
            $this->error('Thiếu tham số bắt buộc. Cú pháp:');
            $this->line('php artisan catalog:import --json="duong_dan.json" --images="thu_muc_anh" --category="Tên danh mục"');
            return 1;
        }

        $isAbsolute = fn($p) => str_starts_with($p, '/') || str_starts_with($p, '\\') || preg_match('/^[A-Za-z]:[\\\\\/]/', $p);
        $jsonFullPath = $isAbsolute($jsonPath) ? $jsonPath : base_path($jsonPath);
        if (!File::exists($jsonFullPath)) {
            $this->error("Không tìm thấy file JSON: {$jsonFullPath}");
            return 1;
        }

        $imagesFullPath = $imagesDir 
            ? ($isAbsolute($imagesDir) ? $imagesDir : base_path($imagesDir)) 
            : base_path('images');

        // 1. Xác định danh mục đích
        $targetCategory = is_numeric($catInput)
            ? Category::find($catInput)
            : Category::where('slug', Str::slug($catInput))
                ->orWhere('name', 'like', "%{$catInput}%")
                ->first();

        if (!$targetCategory) {
            $this->warn("Chưa có danh mục '{$catInput}'. Đang tự động khởi tạo...");
            $targetCategory = Category::create([
                'name' => $catInput,
                'slug' => Str::slug($catInput),
                'parent_id' => $this->option('parent') ? (int)$this->option('parent') : null,
                'is_active' => 1,
            ]);
            $this->info("✓ Đã tạo mới danh mục: {$targetCategory->name} (ID: {$targetCategory->id})");
        } else {
            $this->info("✓ Sử dụng danh mục: {$targetCategory->name} (ID: {$targetCategory->id})");
        }

        // Tự động tìm danh mục cha và Hàng Mới Về
        $parentCategory = $targetCategory->parent_id ? Category::find($targetCategory->parent_id) : null;
        if (!$parentCategory && $this->option('parent')) {
            $parentInput = $this->option('parent');
            $parentCategory = is_numeric($parentInput) ? Category::find($parentInput) : Category::where('slug', $parentInput)->first();
            if ($parentCategory && $targetCategory->parent_id !== $parentCategory->id) {
                $targetCategory->update(['parent_id' => $parentCategory->id]);
            }
        }

        $newArrivalCategory = Category::where('slug', 'hang-moi-ve')->orWhere('name', 'like', '%Hàng Mới Về%')->first();

        $categoryIds = array_filter([
            $targetCategory->id,
            $parentCategory?->id,
            $newArrivalCategory?->id,
        ]);

        // 2. Chạy prepare_catalog.py nếu có flag --prepare hoặc file JSON chưa có màu sắc
        $rawItems = json_decode(File::get($jsonFullPath), true);
        if (!$rawItems || !is_array($rawItems)) {
            $this->error("Nội dung file JSON không hợp lệ hoặc rỗng.");
            return 1;
        }

        $needsPrepare = $this->option('prepare') || empty($rawItems[0]['color']);
        $preparedJsonPath = $jsonFullPath;

        if ($needsPrepare) {
            $this->info("Đang tự động chạy script chuẩn hóa tên và nhận diện màu sắc bằng Computer Vision...");
            $scriptPath = base_path('.agents/skills/seed-fashion-products/scripts/prepare_catalog.py');
            $tempOutput = base_path('scratch/' . pathinfo($jsonFullPath, PATHINFO_FILENAME) . '_prepared.json');

            if (File::exists($scriptPath)) {
                $cmd = sprintf('python -X utf8 "%s" --json="%s" --images="%s" --output="%s"', $scriptPath, $jsonFullPath, $imagesFullPath, $tempOutput);
                exec($cmd, $output, $retCode);
                if ($retCode === 0 && File::exists($tempOutput)) {
                    $preparedJsonPath = $tempOutput;
                    $rawItems = json_decode(File::get($preparedJsonPath), true);
                    $this->info("✓ Đã chuẩn hóa dữ liệu thành công qua Python!");
                } else {
                    $this->warn("Không thể chạy script Python, tiếp tục với dữ liệu gốc.");
                }
            }
        }

        // 3. Chuẩn bị thư mục storage
        $productDir = storage_path('app/public/products');
        $variantDir = storage_path('app/public/variants');
        if (!File::isDirectory($productDir)) File::makeDirectory($productDir, 0755, true);
        if (!File::isDirectory($variantDir)) File::makeDirectory($variantDir, 0755, true);

        // 4. Bảng Colors & Sizes
        $colorModels = [];
        foreach ($this->colorsMap as $cName => $hex) {
            $colorModels[$cName] = Color::firstOrCreate(['name' => $cName], ['hex_code' => $hex]);
        }

        $sizeModels = [];
        foreach (['S', 'M', 'L', 'XL'] as $sName) {
            $sizeModels[$sName] = Size::firstOrCreate(['name' => $sName]);
        }

        // 5. Nạp dữ liệu trong DB Transaction
        $this->info("Bắt đầu nạp " . count($rawItems) . " sản phẩm vào danh mục '{$targetCategory->name}'...");

        $createdCount = 0;
        $updatedCount = 0;
        $usedSlugs = [];
        $usedSkus = [];
        $codeCounts = [];

        DB::transaction(function () use (
            $rawItems, $imagesFullPath, $productDir, $variantDir,
            $colorModels, $sizeModels, $categoryIds, $targetCategory,
            &$createdCount, &$updatedCount, &$usedSlugs, &$usedSkus, &$codeCounts
        ) {
            foreach ($rawItems as $item) {
                // Giá chuẩn
                $cleanPrice = (float) preg_replace('/[^0-9]/', '', (string)($item['price'] ?? '0'));
                if ($cleanPrice <= 0) $cleanPrice = 399000;

                // Tên sạch
                $cleanName = trim($item['name'] ?? '');
                $cleanCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $item['code'] ?? ''));
                if (!empty($cleanCode)) {
                    $cleanName = preg_replace('/\s+' . preg_quote($cleanCode, '/') . '$/i', '', $cleanName);
                }
                $cleanName = preg_replace('/\s+[A-Za-z]+\d+[A-Za-z0-9]*$/', '', $cleanName);
                $cleanName = preg_replace('/\s+[A-Z0-9]{5,}$/', '', $cleanName);
                $cleanName = str_replace(';', ' ', $cleanName);
                $cleanName = preg_replace('/\bzen\b/ui', 'ren', $cleanName);
                $cleanName = str_replace(['xoè', 'Xoè'], ['xòe', 'Xòe'], $cleanName);
                $cleanName = preg_replace('/\s+/', ' ', $cleanName);
                $cleanName = trim($cleanName);

                // Ảnh
                $localImg = $item['local_image'] ?? '';
                $imageBasename = basename($localImg);
                if (empty($imageBasename) && !empty($cleanCode)) {
                    $imageBasename = "{$cleanCode}.jpg";
                }
                $srcImage = $imagesFullPath . '/' . $imageBasename;
                $destProductImage = $productDir . '/' . $imageBasename;
                $destVariantImage = $variantDir . '/' . $imageBasename;

                if (File::exists($srcImage)) {
                    if (!File::exists($destProductImage)) File::copy($srcImage, $destProductImage);
                    if (!File::exists($destVariantImage)) File::copy($srcImage, $destVariantImage);
                }

                // Slug duy nhất
                $baseSlug = Str::slug($cleanName);
                $slug = $baseSlug;
                $counter = 1;
                while (isset($usedSlugs[$slug]) || Product::where('slug', $slug)->exists()) {
                    $counter++;
                    $slug = "{$baseSlug}-{$counter}";
                }
                $usedSlugs[$slug] = true;

                // Chất liệu
                $material = $item['material'] ?? 'Tuyết mưa cao cấp chống nhăn';

                // Tìm hoặc tạo sản phẩm
                $product = null;
                if (!empty($imageBasename)) {
                    $existingImg = ProductImage::where('image_url', 'products/' . $imageBasename)->first();
                    if ($existingImg) $product = $existingImg->product;
                }

                if (!$product) {
                    $product = Product::create([
                        'name' => $cleanName,
                        'slug' => $slug,
                        'short_description' => "{$cleanName} thiết kế cao cấp, tôn dáng thanh lịch và nữ tính cho phái đẹp.",
                        'description' => "{$cleanName} là sản phẩm thời trang thiết kế công sở cao cấp thuộc bộ sưu tập {$targetCategory->name} Aurelia Store. Được may từ chất liệu {$material}, đường may tỉ mỉ, phom dáng chuẩn mang lại sự thoải mái và tự tin suốt cả ngày dài.",
                        'material' => $material,
                        'status' => 'active',
                        'view_count' => rand(20, 150),
                    ]);
                    $createdCount++;
                } else {
                    $product->update([
                        'name' => $cleanName,
                        'material' => $material,
                    ]);
                    $updatedCount++;
                }

                // Gán danh mục
                $product->categories()->sync($categoryIds);

                // Gán ảnh chính
                if (!empty($imageBasename)) {
                    ProductImage::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'image_url' => 'products/' . $imageBasename,
                        ],
                        [
                            'display_order' => 0,
                        ]
                    );
                }

                // Màu sắc
                $colorName = $item['color'] ?? 'Đen';
                $color = $colorModels[$colorName] ?? $colorModels['Đen'];

                // Tạo 4 biến thể với 4 size (S, M, L, XL) và SKU an toàn
                $safeCode = !empty($cleanCode) ? $cleanCode : 'PROD';
                $codeCounts[$safeCode] = ($codeCounts[$safeCode] ?? 0) + 1;
                $codeSuffix = $codeCounts[$safeCode] > 1 ? "-{$codeCounts[$safeCode]}" : "";
                $skuPrefix = "AUR-{$safeCode}{$codeSuffix}";

                foreach ($sizeModels as $sizeName => $sizeModel) {
                    $sku = "{$skuPrefix}-" . strtoupper($sizeName);
                    $dupCounter = 1;
                    while (isset($usedSkus[$sku]) || ProductVariant::where('sku', $sku)->where('product_id', '!=', $product->id)->exists()) {
                        $dupCounter++;
                        $sku = "{$skuPrefix}-{$dupCounter}-" . strtoupper($sizeName);
                    }
                    $usedSkus[$sku] = true;

                    ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'size_id' => $sizeModel->id,
                        ],
                        [
                            'color_id' => $color->id,
                            'sku' => $sku,
                            'barcode' => null,
                            'price' => $cleanPrice,
                            'sale_price' => null, // Không khuyến mại
                            'cost_price' => 0.00,  // Giá vốn = 0
                            'stock_quantity' => 0, // Tồn kho = 0
                            'low_stock_threshold' => 5,
                            'weight_grams' => 250,
                            'thumbnail_url' => !empty($imageBasename) ? 'variants/' . $imageBasename : null,
                            'is_active' => 1,
                        ]
                    );
                }
            }
        });

        $this->newLine();
        $this->info("=================================================");
        $this->info("🎉 HOÀN THÀNH IMPORT CATALOG SẢN PHẨM");
        $this->info("   Danh mục đích: {$targetCategory->name} (ID: {$targetCategory->id})");
        $this->info("   Sản phẩm tạo mới: {$createdCount}");
        $this->info("   Sản phẩm cập nhật: {$updatedCount}");
        $this->info("   Tổng biến thể (x4 size): " . (($createdCount + $updatedCount) * 4));
        $this->info("=================================================");
        $this->newLine();

        // Tự động kiểm tra chất lượng
        $verifyScript = base_path('.agents/skills/seed-fashion-products/scripts/verify_catalog.php');
        if (File::exists($verifyScript)) {
            $this->info("Đang tự động chạy script xác minh chất lượng...");
            passthru(sprintf('php "%s" --category="%s"', $verifyScript, $targetCategory->id));
        }

        return 0;
    }
}
