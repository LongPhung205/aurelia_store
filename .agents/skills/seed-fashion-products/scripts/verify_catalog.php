<?php
/**
 * Script kiểm tra và xác thực dữ liệu danh mục sản phẩm thời trang.
 * Cách chạy:
 *   php .agents/skills/seed-fashion-products/scripts/verify_catalog.php --category="Chân váy dáng xòe"
 *   php .agents/skills/seed-fashion-products/scripts/verify_catalog.php --category=10
 */

require __DIR__ . '/../../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\File;

$options = getopt('', ['category:']);
$catInput = $options['category'] ?? null;

if (!$catInput) {
    echo "Sử dụng: php verify_catalog.php --category=\"Tên hoặc ID danh mục\"\n";
    exit(1);
}

// 1. Tìm danh mục
if (is_numeric($catInput)) {
    $category = Category::find($catInput);
} else {
    $category = Category::where('slug', $catInput)
        ->orWhere('name', 'like', "%{$catInput}%")
        ->first();
}

if (!$category) {
    echo "❌ Lỗi: Không tìm thấy danh mục '{$catInput}' trong hệ thống.\n";
    exit(1);
}

echo "\n======================================================\n";
echo "🔍 KIỂM TRA CHẤT LƯỢNG DỮ LIỆU: {$category->name} (ID: {$category->id})\n";
echo "   Slug: /danh-muc/{$category->slug}\n";
echo "   Parent ID: " . ($category->parent_id ?? 'None') . "\n";
echo "======================================================\n\n";

$products = $category->products()->with(['variants', 'primaryImage'])->get();
$productCount = $products->count();

if ($productCount === 0) {
    echo "⚠️ Cảnh báo: Danh mục hiện chưa có sản phẩm nào.\n";
    exit(0);
}

echo "✓ Tổng số sản phẩm trong danh mục: {$productCount}\n";

$totalVariants = 0;
$invalidVariantsCount = 0;
$salePriceCount = 0;
$costPriceNonZero = 0;
$stockNonZero = 0;
$dirtyNames = [];
$missingImages = 0;

foreach ($products as $p) {
    // Kiểm tra tên
    if (preg_match('/[A-Za-z]+\d+[A-Za-z0-9]*$/', $p->name) || str_contains($p->name, ';')) {
        $dirtyNames[] = $p->name;
    }

    // Kiểm tra ảnh chính
    $imgUrl = $p->primary_image_url;
    if ($imgUrl && !File::exists(storage_path('app/public/' . $imgUrl))) {
        $missingImages++;
    }

    $variants = $p->variants;
    $totalVariants += $variants->count();

    // Mỗi sản phẩm phải có 4 biến thể S, M, L, XL
    if ($variants->count() !== 4) {
        $invalidVariantsCount++;
    }

    foreach ($variants as $v) {
        if ($v->sale_price !== null) $salePriceCount++;
        if ((float)$v->cost_price !== 0.0) $costPriceNonZero++;
        if ((int)$v->stock_quantity !== 0) $stockNonZero++;

        if ($v->thumbnail_url && !File::exists(storage_path('app/public/' . $v->thumbnail_url))) {
            $missingImages++;
        }
    }
}

echo "✓ Tổng số biến thể: {$totalVariants} (Tỉ lệ: " . ($totalVariants / $productCount) . " biến thể/sản phẩm)\n";

// Báo cáo kiểm định
$hasError = false;

if ($invalidVariantsCount > 0) {
    echo "❌ Có {$invalidVariantsCount} sản phẩm không đủ 4 size (S, M, L, XL)!\n";
    $hasError = true;
} else {
    echo "✓ 100% sản phẩm có đủ 4 size chuẩn (S, M, L, XL)\n";
}

if (!empty($dirtyNames)) {
    echo "❌ Phát hiện " . count($dirtyNames) . " tên sản phẩm còn dính mã code / chấm phẩy:\n";
    foreach (array_slice($dirtyNames, 0, 5) as $dn) echo "   - {$dn}\n";
    $hasError = true;
} else {
    echo "✓ 100% tên sản phẩm đã được làm sạch chuẩn thời trang cao cấp\n";
}

if ($salePriceCount > 0) {
    echo "⚠️ Chú ý: Có {$salePriceCount} biến thể đã thiết lập sale_price.\n";
} else {
    echo "✓ 100% biến thể để sale_price = NULL (chưa áp dụng khuyến mãi)\n";
}

if ($costPriceNonZero > 0 || $stockNonZero > 0) {
    echo "⚠️ Chú ý: Tồn kho hoặc giá vốn khác 0 (Cost: {$costPriceNonZero}, Stock: {$stockNonZero})\n";
} else {
    echo "✓ 100% biến thể có cost_price = 0, stock_quantity = 0 (sẵn sàng nhập kho)\n";
}

if ($missingImages > 0) {
    echo "❌ Có {$missingImages} file ảnh bị thiếu trong storage/app/public!\n";
    $hasError = true;
} else {
    echo "✓ 100% ảnh đại diện và ảnh biến thể tồn tại trong storage\n";
}

// Kiểm tra trang web thực tế
$url = "http://127.0.0.1:8000/danh-muc/{$category->slug}";
echo "\nĐang kiểm tra kết nối URL: {$url} ... ";

$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true,
    ]
]);

$response = @file_get_contents($url, false, $context);
if ($response !== false && isset($http_response_header[0]) && str_contains($http_response_header[0], '200')) {
    echo "✓ HTTP 200 OK (Độ dài: " . number_format(strlen($response)) . " bytes)\n";
} else {
    echo "⚠️ Server dev có thể chưa khởi động hoặc trang trả về mã khác 200.\n";
}

echo "\n" . ($hasError ? "❌ KẾT QUẢ: CÓ VẤN ĐỀ CẦN XỬ LÝ" : "🎉 KẾT QUẢ: TẤT CẢ TIÊU CHÍ ĐẠT 100% HOÀN HẢO!") . "\n";
echo "======================================================\n\n";
