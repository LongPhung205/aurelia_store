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

class LamerSkirtsSeeder extends Seeder
{
    /**
     * Seed 26 A-line skirts from scraped Lamer data into 'Chân váy dáng A' (ID: 9), 'Chân Váy' (ID: 6), and 'Hàng Mới Về' (ID: 5).
     */
    public function run(): void
    {
        // 1. Đảm bảo các thư mục đích trong public storage tồn tại
        $productDir = storage_path('app/public/products');
        $variantDir = storage_path('app/public/variants');
        if (!File::isDirectory($productDir)) {
            File::makeDirectory($productDir, 0755, true);
        }
        if (!File::isDirectory($variantDir)) {
            File::makeDirectory($variantDir, 0755, true);
        }

        // 2. Đảm bảo các màu sắc có sẵn
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

        // 3. Đảm bảo các kích cỡ S, M, L, XL
        $sizeModels = [];
        foreach (['S', 'M', 'L', 'XL'] as $sName) {
            $sizeModels[$sName] = Size::firstOrCreate(['name' => $sName]);
        }

        // 4. Lấy các danh mục: Chân váy dáng A (ID: 9), Chân Váy (ID: 6), Hàng Mới Về (ID: 5)
        $aLineCategory = Category::find(9);
        $skirtCategory = Category::find(6);
        $newArrivalCategory = Category::find(5);

        $categoryIds = array_filter([
            $aLineCategory?->id ?? 9,
            $skirtCategory?->id ?? 6,
            $newArrivalCategory?->id ?? 5,
        ]);

        // 5. Dữ liệu chuẩn hóa 26 sản phẩm (tên sạch, không kèm mã code ở đuôi)
        $rawItems = [
            [
                "name" => "Chân váy A cạp liền phối tơ",
                "code" => "V62R26H010",
                "price" => "499.000 ₫",
                "local_image" => "V62R26H010.jpg",
                "color" => "Trắng Kem",
                "material" => "Tuyết nhung phối tơ cao cấp",
                "short" => "Chân váy chữ A cạp liền phối tơ nhẹ nhàng, vạt đắp chéo xếp lớp hiện đại.",
            ],
            [
                "name" => "Chân váy lụa dáng A",
                "code" => "S62R25Q006",
                "price" => "349.300 ₫",
                "local_image" => "S62R25Q006.jpg",
                "color" => "Xanh Navy",
                "material" => "Lụa Satin cao cấp bóng mờ",
                "short" => "Chân váy lụa dáng A mềm rủ nữ tính, tôn dáng thon gọn và phong cách sang trọng.",
            ],
            [
                "name" => "Chân váy dáng A xẻ tà 2 lớp",
                "code" => "L62R26H015",
                "price" => "599.000 ₫",
                "local_image" => "L62R26H015.jpg",
                "color" => "Trắng Kem",
                "material" => "Gấm tơ dập nổi 2 lớp",
                "short" => "Chân váy dáng A xẻ tà 2 lớp quý phái, chất liệu gấm dập nổi cao cấp tiểu thư.",
            ],
            [
                "name" => "Chân váy A túi chéo đóng cúc",
                "code" => "V62R25T007",
                "price" => "399.000 ₫",
                "local_image" => "V62R25T007.jpg",
                "color" => "Nâu Cà Phê",
                "material" => "Nhung tăm gân nổi cao cấp",
                "short" => "Chân váy A chất nhung tăm ấm áp, thiết kế túi chéo đóng cúc trẻ trung năng động.",
            ],
            [
                "name" => "Chân váy lụa khóa sau",
                "code" => "S62R25Q004",
                "price" => "349.300 ₫",
                "local_image" => "S62R25Q004.jpg",
                "color" => "Xanh Lục Bảo",
                "material" => "Lụa ngọc trai cao cấp",
                "short" => "Chân váy lụa dáng A khóa kéo sau tinh tế, bề mặt lụa mướt mịn tôn vẻ quý phái.",
            ],
            [
                "name" => "Chân váy ngắn phối ren",
                "code" => "V62R25Q010",
                "price" => "99.000 ₫",
                "local_image" => "V62R25Q010.jpg",
                "color" => "Trắng Kem",
                "material" => "Dạ ép mỏng phối ren thêu",
                "short" => "Chân váy ngắn phối chân ren mi tinh xảo, nét đẹp nữ tính chuẩn nàng thơ công sở.",
            ],
            [
                "name" => "Chân váy dạ dáng A",
                "code" => "O62R25T001",
                "price" => "199.500 ₫",
                "local_image" => "O62R25T001.jpg",
                "color" => "Trắng Kem",
                "material" => "Dạ dệt mềm mịn đứng phom",
                "short" => "Chân váy dạ dáng A màu be sữa cạp cao, trẻ trung và dễ dàng phối đồ đông xuân.",
            ],
            [
                "name" => "Chân váy A cạp chun 2 bên",
                "code" => "V62R25Q015",
                "price" => "419.300 ₫",
                "local_image" => "V62R25Q015.jpg",
                "color" => "Xanh Navy",
                "material" => "Tuyết mưa cao cấp chống nhăn",
                "short" => "Chân váy midi dáng A cạp chun hai bên sườn co giãn thoải mái suốt ngày dài làm việc.",
            ],
            [
                "name" => "Chân váy A xếp ly bung",
                "code" => "L62R25Q016",
                "price" => "349.300 ₫",
                "local_image" => "L62R25Q016.jpg",
                "color" => "Xanh Denim",
                "material" => "Denim Cotton mềm thoáng mát",
                "short" => "Chân váy chữ A xếp ly bung dáng dài phóng khoáng, chất vải denim mềm rủ thanh lịch.",
            ],
            [
                "name" => "Chân váy A xẻ trước nắp túi giả",
                "code" => "V62R25Q004",
                "price" => "599.000 ₫",
                "local_image" => "V62R25Q004.jpg",
                "color" => "Xanh Denim",
                "material" => "Denim cao cấp chỉ trần trắng",
                "short" => "Thiết kế xẻ trước cá tính cùng nắp túi giả may chỉ nổi thời thượng trên nền denim sang trọng.",
            ],
            [
                "name" => "Chân váy kẻ phối cạp",
                "code" => "V62R25Q007",
                "price" => "99.000 ₫",
                "local_image" => "V62R25Q007.jpg",
                "color" => "Hồng Pastel",
                "material" => "Dạ dệt caro đuôi cá phối cạp trắng",
                "short" => "Chân váy chữ A đuôi cá nhẹ nhàng, họa tiết kẻ caro hồng pastel phối cạp trắng nổi bật.",
            ],
            [
                "name" => "Chân váy jean túi ốp diễu chỉ",
                "code" => "V62R24Q019",
                "price" => "299.000 ₫",
                "local_image" => "V62R24Q019.jpg",
                "color" => "Xanh Denim",
                "material" => "Denim wash chàm cao cấp",
                "short" => "Chân váy jean midi túi ốp diễu chỉ nổi phong cách Hàn Quốc trẻ trung và năng động.",
            ],
            [
                "name" => "Chân váy dạ tweed dáng A túi ốp",
                "code" => "V62R23T002",
                "price" => "199.000 ₫",
                "local_image" => "V62R23T002.jpg",
                "color" => "Hồng Pastel",
                "material" => "Dạ tweed dệt ánh kim",
                "short" => "Chân váy dạ tweed dáng A túi ốp đính cúc quý phái, tone hồng pastel tiểu thư ngọt ngào.",
            ],
            [
                "name" => "Chân váy A túi chéo",
                "code" => "V62R23Q010",
                "price" => "199.000 ₫",
                "local_image" => "V62R23Q010.jpg",
                "color" => "Nâu Ghi",
                "material" => "Dạ len dệt caro nhuyễn",
                "short" => "Chân váy A họa tiết caro nâu ghi vintage, túi chéo tiện lợi cạp cao tôn dáng.",
            ],
            [
                "name" => "Chân váy dáng A gấu lượn",
                "code" => "S62R23Q002",
                "price" => "199.000 ₫",
                "local_image" => "S62R23Q002.jpg",
                "color" => "Đen",
                "material" => "Tuyết mưa đứng form co giãn nhẹ",
                "short" => "Chân váy chữ A cạp cao gấu lượn đính cúc giữa, tôn trọn đôi chân thon dài quyến rũ.",
            ],
            [
                "name" => "Chân váy dáng A xẻ sau",
                "code" => "L62R24Q021",
                "price" => "199.000 ₫",
                "local_image" => "L62R24Q021.jpg",
                "color" => "Nâu Tây",
                "material" => "Kaki tuyết mưa cao cấp",
                "short" => "Chân váy dáng A dài màu nâu tây thời thượng, xẻ sau thanh lịch chuẩn quý cô công sở.",
            ],
            [
                "name" => "Chân váy ngắn nẹp giả diễu chỉ",
                "code" => "V62R24Q014",
                "price" => "399.000 ₫",
                "local_image" => "V62R24Q014.jpg",
                "color" => "Đen",
                "material" => "Umi Hàn Quốc dày dặn co giãn",
                "short" => "Chân váy ngắn nẹp giả diễu chỉ trắng tương phản, túi vát cá tính và hiện đại.",
            ],
            [
                "name" => "Chân váy dáng A cạp liền bổ mảnh",
                "code" => "O62R24Q012",
                "price" => "175.000 ₫",
                "local_image" => "O62R24Q012.jpg",
                "color" => "Xanh Baby",
                "material" => "Tuyết mưa Hàn Quốc",
                "short" => "Chân váy dáng A cạp liền bổ mảnh màu xanh pastel nhã nhặn, tôn đường cong nữ tính.",
            ],
            [
                "name" => "Chân váy dáng A phối gấu diễu chỉ",
                "code" => "V62R24H010",
                "price" => "199.000 ₫",
                "local_image" => "V62R24H010.jpg",
                "color" => "Xanh Denim",
                "material" => "Jean Cotton wash mềm",
                "short" => "Chân váy jean chữ A ngắn phối gấu lật diễu chỉ trắng, trẻ trung và năng động.",
            ],
            [
                "name" => "Chân váy cạp liền ly súp túi cơi",
                "code" => "V62R24H003",
                "price" => "199.000 ₫",
                "local_image" => "V62R24H003.jpg",
                "color" => "Đen",
                "material" => "Tuyết mưa đứng phom công sở",
                "short" => "Chân váy cạp liền ly súp túi cơi viền chỉ nổi tinh tế, phom dáng chuẩn mực cho nàng công sở.",
            ],
            [
                "name" => "Chân váy dạ tweed dáng A viền xích",
                "code" => "V62R23T006",
                "price" => "99.000 ₫",
                "local_image" => "V62R23T006.jpg",
                "color" => "Đen",
                "material" => "Dạ tweed dệt kim sa viền xích",
                "short" => "Chân váy dạ tweed dáng A màu đen dệt sợi ánh kim, viền xích vàng sang trọng đẳng cấp.",
            ],
            [
                "name" => "Chân váy cạp cao túi cơi trang trí",
                "code" => "V62R23Q006",
                "price" => "422.100 ₫",
                "local_image" => "V62R23Q006.jpeg",
                "color" => "Trắng Kem",
                "material" => "Kaki tuyết mưa mướt mịn",
                "short" => "Chân váy chữ A cạp cao túi cơi trang trí màu be sáng, hack chiều cao và tôn dáng tối đa.",
            ],
            [
                "name" => "Chân váy A chiết ly xếp nếp",
                "code" => "V62R23Q004",
                "price" => "99.000 ₫",
                "local_image" => "V62R23Q004.jpg",
                "color" => "Nâu Tây",
                "material" => "Tuyết mưa đanh mịn",
                "short" => "Chân váy A xếp ly lệch tà phối cúc bên màu nâu be thời thượng, duyên dáng và thanh lịch.",
            ],
            [
                "name" => "Chân váy A kẹp lé phối màu",
                "code" => "V62R23Q002",
                "price" => "99.000 ₫",
                "local_image" => "V62R23Q002.jpg",
                "color" => "Nâu Tây",
                "material" => "Kaki cotton dày dặn",
                "short" => "Chân váy chữ A ngắn kẹp lé chỉ trắng phối màu nâu kaki, phom đứng thanh tao.",
            ],
            [
                "name" => "Chân váy dạ tweed kẻ caro ziczac",
                "code" => "V62R22T035",
                "price" => "99.000 ₫",
                "local_image" => "V62R22T035.jpg",
                "color" => "Đen",
                "material" => "Dạ tweed dệt caro ziczac",
                "short" => "Chân váy dạ tweed kẻ caro ziczac đen trắng kinh điển, phong cách tiểu thư đài các.",
            ],
            [
                "name" => "Chân váy dạ tweed chữ A ánh kim",
                "code" => "S62R23T001",
                "price" => "299.000 ₫",
                "local_image" => "S62R23T001.jpg",
                "color" => "Đen",
                "material" => "Dạ tweed kim tuyến cao cấp",
                "short" => "Chân váy dạ tweed chữ A ngắn màu đen lấp lánh ánh kim sa, lựa chọn số một cho tiệc và dạo phố.",
            ],
        ];

        DB::transaction(function () use ($rawItems, $productDir, $variantDir, $colorModels, $sizeModels, $categoryIds) {
            $updatedCount = 0;
            $createdCount = 0;

            foreach ($rawItems as $item) {
                $cleanPrice = (float) preg_replace('/[^0-9]/', '', $item['price']);

                // Copy ảnh
                $srcImage = base_path('images/' . $item['local_image']);
                $destProductImage = $productDir . '/' . $item['local_image'];
                $destVariantImage = $variantDir . '/' . $item['local_image'];

                if (File::exists($srcImage)) {
                    if (!File::exists($destProductImage)) {
                        File::copy($srcImage, $destProductImage);
                    }
                    if (!File::exists($destVariantImage)) {
                        File::copy($srcImage, $destVariantImage);
                    }
                }

                // Tên sản phẩm sạch 100%, không dính mã code
                $cleanName = $item['name'];
                $baseSlug = Str::slug($cleanName);

                // Tìm sản phẩm đã tạo trước đó qua mã SKU của variant hoặc slug cũ
                $existingVariant = ProductVariant::where('sku', 'AUR-' . strtoupper($item['code']) . '-S')->first();
                $product = $existingVariant ? $existingVariant->product : null;

                if (!$product) {
                    $product = Product::where('slug', 'like', '%' . Str::slug($item['code']) . '%')->first();
                }

                // Đảm bảo slug duy nhất
                $slug = $baseSlug;
                $counter = 1;
                while (Product::where('slug', $slug)->where('id', '!=', $product?->id ?? 0)->exists()) {
                    $counter++;
                    $slug = "{$baseSlug}-{$counter}";
                }

                if (!$product) {
                    $product = Product::create([
                        'name' => $cleanName,
                        'slug' => $slug,
                        'short_description' => $item['short'],
                        'description' => "{$cleanName} là sản phẩm thời trang thiết kế công sở cao cấp thuộc bộ sưu tập Chân Váy Dáng A Aurelia Store. Được may từ chất liệu {$item['material']} cao cấp, đường may tỉ mỉ, phom dáng chuẩn chỉ tôn trọn đường cong cơ thể phái đẹp. Thích hợp phối cùng áo sơ mi, áo len mỏng hoặc blazer công sở.",
                        'material' => $item['material'],
                        'status' => 'active',
                        'view_count' => rand(20, 180),
                    ]);
                    $createdCount++;
                } else {
                    $product->update([
                        'name' => $cleanName,
                        'slug' => $slug,
                        'short_description' => $item['short'],
                        'description' => "{$cleanName} là sản phẩm thời trang thiết kế công sở cao cấp thuộc bộ sưu tập Chân Váy Dáng A Aurelia Store. Được may từ chất liệu {$item['material']} cao cấp, đường may tỉ mỉ, phom dáng chuẩn chỉ tôn trọn đường cong cơ thể phái đẹp. Thích hợp phối cùng áo sơ mi, áo len mỏng hoặc blazer công sở.",
                        'material' => $item['material'],
                        'status' => 'active',
                    ]);
                    $updatedCount++;
                }

                // Gán danh mục
                $product->categories()->sync($categoryIds);

                // Gán ảnh chính
                ProductImage::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'image_url' => 'products/' . $item['local_image'],
                    ],
                    [
                        'display_order' => 0,
                    ]
                );

                // Màu sắc
                $color = $colorModels[$item['color']] ?? $colorModels['Đen'];

                // Cập nhật 4 size
                foreach ($sizeModels as $sizeName => $sizeModel) {
                    $sku = 'AUR-' . strtoupper($item['code']) . '-' . strtoupper($sizeName);

                    ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'color_id' => $color->id,
                            'size_id' => $sizeModel->id,
                        ],
                        [
                            'sku' => $sku,
                            'barcode' => null,
                            'price' => $cleanPrice,
                            'sale_price' => null,
                            'cost_price' => 0.00,
                            'stock_quantity' => 0,
                            'low_stock_threshold' => 5,
                            'weight_grams' => 250,
                            'thumbnail_url' => 'variants/' . $item['local_image'],
                            'is_active' => 1,
                        ]
                    );
                }
            }

            $this->command->info("Đã cập nhật xong tên sạch cho 26 Chân Váy: {$updatedCount} sản phẩm cập nhật, {$createdCount} sản phẩm tạo mới.");
        });
    }
}
