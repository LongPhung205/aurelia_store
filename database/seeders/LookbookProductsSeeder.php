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

class LookbookProductsSeeder extends Seeder
{
    /**
     * Seed all 42 unique lookbook products into 'Đầm & Váy Công Sở' (ID: 8) and 'Hàng Mới Về' (ID: 5).
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

        // 2. Khởi tạo/Đảm bảo bảng Colors có đầy đủ mã màu thực tế
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
            'Hồng Pastel'   => '#f8bbd0',
            'Tím Pastel'    => '#d8b4e2',
            'Cam Đào'       => '#ffab91',
            'Nâu Cà Phê'    => '#5d4037',
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

        // 3. Đảm bảo các Size có sẵn
        $sizeModels = [];
        foreach (['S', 'M', 'L', 'XL'] as $sName) {
            $sizeModels[$sName] = Size::firstOrCreate(['name' => $sName]);
        }

        // 4. Lấy danh mục Đầm & Váy Công Sở (ID 8) và Hàng Mới Về (ID 5)
        $officeCategory = Category::find(8);
        if (!$officeCategory) {
            $officeCategory = Category::create([
                'id' => 8,
                'name' => 'Đầm & Váy Công Sở',
                'slug' => 'dam-vay-cong-so',
                'parent_id' => 4,
                'is_active' => 1
            ]);
        }
        $newArrivalCategory = Category::find(5);

        $categoryIds = array_filter([$officeCategory?->id, $newArrivalCategory?->id]);

        // 5. Danh sách chi tiết 42 mẫu đầm công sở thiết kế cao cấp
        $items = [
            [
                'file' => 'item_1788862342891.jpg',
                'name' => 'Đầm Voan Tơ Cổ Sen Thêu Hoa',
                'color' => 'Tím Pastel',
                'material' => 'Voan tơ tằm thêu chỉ nổi',
                'price' => 850000,
                'short' => 'Thiết kế dáng xòe bay bổng với cổ sen thêu hoa tỉ mỉ cùng sắc tím pastel ngọt ngào, trang nhã.',
                'desc' => "Đầm Voan Tơ Cổ Sen Thêu Hoa là lựa chọn hoàn hảo cho quý cô yêu thích vẻ đẹp thanh lịch và nữ tính nơi công sở. Chất liệu voan tơ cao cấp mềm mại, thoáng mát và có độ rủ tự nhiên. Điểm nhấn cổ sen thêu hoa thủ công tinh xảo cùng hàng cúc ngọc trai sang trọng, form váy xòe chữ A nhẹ nhàng che khuyết điểm hoàn hảo.",
            ],
            [
                'file' => 'item_1788862343269.jpg',
                'name' => 'Đầm Đen Ôm Phối Viền Sóng Quý Phái',
                'color' => 'Đen',
                'material' => 'Chéo Ý cao cấp co giãn nhẹ',
                'price' => 920000,
                'short' => 'Đầm đen dáng bút chì chiết eo phối viền sóng lượn thanh thoát, tôn dáng tối đa.',
                'desc' => "Mẫu đầm liền đen công sở kinh điển mang hơi thở hiện đại. Chất vải Chéo Ý dày dặn, đứng form và co giãn nhẹ tạo sự thoải mái suốt ngày dài làm việc. Đường viền uốn lượn trắng tương phản tạo hiệu ứng thị giác thon gọn vòng eo, kết hợp cùng clutch cầm tay rất sang trọng.",
            ],
            [
                'file' => 'item_1788862343587.jpg',
                'name' => 'Đầm Tơ Hoa Nhí Thắt Nơ Eo',
                'color' => 'Vàng',
                'material' => 'Tơ sống in họa tiết hoa nhí',
                'price' => 790000,
                'short' => 'Tone màu vàng pastel rạng rỡ, điểm xuyết hoa nhí mùa hạ và đai nơ thon gọn vòng 2.',
                'desc' => "Chiếc đầm mang năng lượng tươi mới cho ngày làm việc hứng khởi. Thiết kế cổ V đan chéo thanh thoát, tay áo bồng nhẹ che bắp tay khéo léo. Phần eo có dây thắt nơ tùy chỉnh độ ôm, tùng váy xòe 2 lớp bồng bềnh kín đáo.",
            ],
            [
                'file' => 'item_1788862343916.jpg',
                'name' => 'Đầm Dạ Tweed Cổ Vuông Dáng A',
                'color' => 'Nâu Ghi',
                'material' => 'Dạ Tweed cao cấp dệt kim tuyến',
                'price' => 1150000,
                'short' => 'Dạ tweed phong cách tiểu thư cổ điển với cổ vuông kiêu kỳ và hàng cúc đồng quý tộc.',
                'desc' => "Chất liệu dạ tweed Hàn Quốc được dệt tỉ mỉ với độ bền vượt trội, tone nâu ghi sang trọng phù hợp môi trường văn phòng chuyên nghiệp và các cuộc họp quan trọng. Cổ vuông khoe xương quai xanh tinh tế kết hợp form chữ A trẻ trung.",
            ],
            [
                'file' => 'item_1788862344271.jpg',
                'name' => 'Đầm Lụa Trắng Phối Ren Tiểu Thư',
                'color' => 'Trắng',
                'material' => 'Lụa ngọc trai phối ren thêu',
                'price' => 980000,
                'short' => 'Sắc trắng thuần khiết phối ren tinh xảo, nét đẹp thuần khiết cho quý cô công sở hiện đại.',
                'desc' => "Được may từ lụa ngọc trai có độ bóng nhẹ và bề mặt mướt mịn. Chi tiết viền ren ngực và tay áo được may tỉ mỉ, giúp tổng thể bộ trang phục toát lên khí chất thanh tao, sang trọng.",
            ],
            [
                'file' => 'item_1788862344597.jpg',
                'name' => 'Đầm Sơ Mi Denim Khóa Kéo Năng Động',
                'color' => 'Xanh Denim',
                'material' => 'Denim Cotton mềm mại thấm hút mồ hôi',
                'price' => 850000,
                'short' => 'Phom sơ mi denim thời thượng với khóa kéo kim loại hiện đại và túi ốp cá tính.',
                'desc' => "Sự kết hợp hoàn hảo giữa tính lịch sự của sơ mi công sở và sự trẻ trung của chất liệu denim. Vải denim được wash mềm, không thô ráp, đường may trần chỉ nổi tỉ mỉ tôn trọn vóc dáng thanh mảnh.",
            ],
            [
                'file' => 'item_1788862344905.jpg',
                'name' => 'Đầm Chiffon Họa Tiết Hoa Nhiệt Đới',
                'color' => 'Xanh Da Trời',
                'material' => 'Chiffon lụa in chuyển nhiệt sắc nét',
                'price' => 780000,
                'short' => 'Sắc xanh dịu mát cùng họa tiết hoa nhiệt đới mùa hè mang đến sự tươi mới, trang nhã.',
                'desc' => "Chất voan chiffon mềm rủ, chống nhăn tốt giúp bạn luôn chỉn chu từ sáng đến tối. Họa tiết hoa màu nước độc quyền trên nền xanh nhạt mang đến cảm giác mát lành, thanh lịch tuyệt đối.",
            ],
            [
                'file' => 'item_1788862345245.jpg',
                'name' => 'Đầm Dạ Tweed Dáng Suông Tay Lỡ',
                'color' => 'Xám Ghi',
                'material' => 'Dạ Tweed dệt sợi nổi cao cấp',
                'price' => 1250000,
                'short' => 'Thiết kế dáng suông tay lỡ thanh tao, biểu tượng của sự sang trọng và quyền lực nơi công sở.',
                'desc' => "Dành cho những ngày thời tiết se lạnh hoặc phòng làm việc điều hòa. Phom váy suông che khuyết điểm tối đa, kết hợp cúc kim loại bọc viền tinh tế và lớp lót lụa mềm êm ái.",
            ],
            [
                'file' => 'item_1788862345621.jpg',
                'name' => 'Đầm Chấm Bi Cổ V Dáng Xòe',
                'color' => 'Đỏ',
                'material' => 'Lụa Mango mềm rủ',
                'price' => 790000,
                'short' => 'Họa tiết chấm bi retro cổ điển trên nền đỏ đô quý phái, tạo điểm nhấn nổi bật.',
                'desc' => "Phong cách vintage kiểu Pháp với cổ chữ V tôn vòng 1 vừa phải, tay phồng nhẹ và tùng váy xếp ly mềm mại. Thích hợp đi làm hàng ngày hoặc hẹn hò sau giờ tan sở.",
            ],
            [
                'file' => 'item_1788862345923.jpg',
                'name' => 'Đầm Sơ Mi Kẻ Caro Thắt Đai',
                'color' => 'Vàng',
                'material' => 'Cotton đũi dệt caro thoáng mát',
                'price' => 820000,
                'short' => 'Kiểu dáng đầm sơ mi công sở kẻ caro trẻ trung kèm thắt lưng vải đồng bộ tôn dáng.',
                'desc' => "Đầm sơ mi cổ đức thanh lịch không bao giờ lỗi mốt. Đường kẻ caro nhã nhặn phối cúc ngực tinh tế, có đai thắt eo tạo eo thon gọn và chiều cao lý tưởng cho người mặc.",
            ],
            [
                'file' => 'item_1788862346236.jpg',
                'name' => 'Đầm Lụa Cúp Ngực Phối Lưới Đi Tiệc',
                'color' => 'Đen',
                'material' => 'Lụa Satin phối lưới vi tính cao cấp',
                'price' => 1050000,
                'short' => 'Đầm dạ tiệc công sở tone đen huyền bí phối lưới tơ phần cổ vừa kín đáo vừa gợi cảm.',
                'desc' => "Phù hợp cho các buổi dạ tiệc công ty, gala dinner hoặc gặp gỡ đối tác trang trọng. Chất satin bóng nhẹ cao cấp kết hợp phần cúp ngực may khéo léo giúp tôn lên khí chất đài các.",
            ],
            [
                'file' => 'item_1788862346542.jpg',
                'name' => 'Đầm Voan Hoa Nhí Chân Bèo Nhún',
                'color' => 'Hồng Pastel',
                'material' => 'Voan Chiffon dập nhăn tự nhiên',
                'price' => 760000,
                'short' => 'Sắc hồng pastel ngọt ngào với chi tiết bèo nhún bồng bềnh, duyên dáng và trẻ trung.',
                'desc' => "Từng lớp bèo nhún nhẹ nhàng ở chân váy tạo hiệu ứng chuyển động thướt tha khi sải bước. Chất vải voan chiffon 2 lớp dày dặn không lộ nội y, màu sắc nền nã phù hợp môi trường văn phòng trẻ.",
            ],
            [
                'file' => 'item_1788862346899.jpg',
                'name' => 'Đầm Công Sở Cổ Sen Viền Ren',
                'color' => 'Trắng Kem',
                'material' => 'Tuyết Mưa Hàn Quốc đứng form',
                'price' => 890000,
                'short' => 'Thiết kế cổ sen viền ren chỉ nổi thanh tao trên chất vải Tuyết Mưa cao cấp chống nhăn.',
                'desc' => "Váy chữ A đứng dáng chuẩn form công sở. Cổ áo sen viền ren thủ công kết hợp hàng cúc bọc vải cùng màu tạo nên sự hài hòa, kín đáo và vô cùng lịch sự.",
            ],
            [
                'file' => 'item_1788862347210.jpg',
                'name' => 'Đầm Xếp Ly Dáng Dài Thanh Lịch',
                'color' => 'Xanh Da Trời',
                'material' => 'Voan tơ dập ly nhiệt định hình',
                'price' => 860000,
                'short' => 'Tùng váy dập ly nhỏ tinh tế, tạo độ bay bổng duyên dáng cho từng bước chân.',
                'desc' => "Kỹ thuật dập ly nhiệt công nghệ cao giữ nếp ly vĩnh viễn sau nhiều lần giặt. Tone xanh da trời tươi sáng tôn da, phần eo có chun co giãn nhẹ thoải mái khi ngồi làm việc lâu.",
            ],
            [
                'file' => 'item_1788862347521.jpg',
                'name' => 'Đầm Lụa Trắng Cổ Ren Cúc Ngọc',
                'color' => 'Trắng Kem',
                'material' => 'Lụa Habutai cao cấp phối ren nổi',
                'price' => 990000,
                'short' => 'Đầm lụa trắng ngà cổ bẻ phối ren thêu đính ngọc trai, khí chất quý phái tiểu thư.',
                'desc' => "Sử dụng lụa Habutai mềm như mây, nhẹ tênh trên da. Hàng cúc ngọc trai ánh xà cừ điểm xuyết trước ngực làm bừng sáng khuôn mặt người mặc, mang lại diện mạo chỉn chu hoàn hảo.",
            ],
            [
                'file' => 'item_1788862347855.jpg',
                'name' => 'Đầm Ren Hoa Nổi Dáng Xòe Tiểu Thư',
                'color' => 'Trắng',
                'material' => 'Ren thêu nổi cao cấp có lớp lót lụa',
                'price' => 950000,
                'short' => 'Chất ren thêu hoa văn 3D độc quyền, dáng xòe tiểu thư sang trọng và trang nhã.',
                'desc' => "Từng cánh hoa ren được thêu nổi tạo chiều sâu tinh xảo cho trang phục. Bên trong lót lụa mềm mượt thấm hút tốt, không gây ngứa hay khó chịu khi vận động cả ngày.",
            ],
            [
                'file' => 'item_1788862348179.jpg',
                'name' => 'Đầm Voan Tơ Cúp Ngực Thắt Nơ Lưng',
                'color' => 'Hồng Pastel',
                'material' => 'Voan tơ tằm óng ánh nhẹ',
                'price' => 880000,
                'short' => 'Thiết kế thắt nơ lưng điệu đà, sắc hồng phấn nhẹ nhàng thanh lịch cho nàng công sở.',
                'desc' => "Phần lưng phối nơ tinh nghịch nhưng vẫn đảm bảo độ kín đáo khi diện đi làm. Phom váy ôm vừa phải phần thân trên và xòe nhẹ phía dưới giúp tôn dáng tối đa.",
            ],
            [
                'file' => 'item_1788862348514.jpg',
                'name' => 'Đầm Pastel Viền Đen Cổ Bẻ Thanh Lịch',
                'color' => 'Xanh Baby',
                'material' => 'Umi cao cấp co giãn 4 chiều',
                'price' => 890000,
                'short' => 'Đường viền đen tương phản trên nền xanh pastel tạo phong cách Parisian Chic thanh lịch.',
                'desc' => "Lấy cảm hứng từ phong cách thời trang nước Pháp, chiếc đầm sở hữu phom dáng chữ A tôn dáng với đường viền đen nổi bật ở cổ và túi áo, mang đến vẻ đẹp hiện đại và tri thức.",
            ],
            [
                'file' => 'item_1788862348822.jpg',
                'name' => 'Đầm Chiffon Hồng Pastel Tay Phồng',
                'color' => 'Hồng Pastel',
                'material' => 'Chiffon dệt cát cao cấp',
                'price' => 790000,
                'short' => 'Tay áo phồng công chúa nhẹ nhàng, chất vải chiffon dệt cát bay bổng che khuyết điểm.',
                'desc' => "Tay phồng lỡ bo chun nhẹ tạo điểm nhấn nữ tính, cổ tròn kín đáo phù hợp quy chuẩn công sở. Váy có lót trong cùng màu dày dặn, không sợ mỏng manh.",
            ],
            [
                'file' => 'item_1788862349149.jpg',
                'name' => 'Đầm Dạ Tweed Đính Cúc Ngọc Trai',
                'color' => 'Đen',
                'material' => 'Dạ Tweed dệt kim sa mịn',
                'price' => 1200000,
                'short' => 'Sắc đen dạ tweed quý tộc điểm xuyết cúc ngọc trai sang trọng, đẳng cấp hàng hiệu.',
                'desc' => "Thiết kế cao cấp thuộc bộ sưu tập dạ tiệc & công sở mùa đông. Vải dạ tweed dệt sợi kim sa lấp lánh nhẹ dưới ánh đèn, cúc ngọc trai viền kim loại mạ vàng sang chảnh.",
            ],
            [
                'file' => 'item_1788862349500.jpg',
                'name' => 'Đầm Hoa Nhí Vintage Cổ Vuông',
                'color' => 'Đỏ',
                'material' => 'Thô lụa mềm mát chống nhăn',
                'price' => 750000,
                'short' => 'Cổ vuông khoe xương quai xanh quyến rũ, họa tiết hoa nhí cổ điển ngọt ngào.',
                'desc' => "Kiểu dáng cổ vuông đang là xu hướng được yêu thích nhất. Chất thô lụa mềm mại, thấm mồ hôi cực tốt, phom váy xòe tự nhiên mang lại cảm giác thoải mái khi di chuyển.",
            ],
            [
                'file' => 'item_1788862349826.jpg',
                'name' => 'Đầm Suông Phối Khóa Kéo Trước',
                'color' => 'Nâu Cà Phê',
                'material' => 'Tuyết Mưa cao cấp đứng form',
                'price' => 860000,
                'short' => 'Dáng suông thanh lịch giấu bụng hoàn hảo, điểm nhấn khóa kéo vàng trước ngực hiện đại.',
                'desc' => "Giải pháp hoàn hảo cho nàng tự ti về vòng eo. Dáng suông chữ A nhẹ nhàng, tone nâu cà phê trầm ấm sang trọng, khóa kim loại mạ vàng tạo điểm nhấn đắt giá.",
            ],
            [
                'file' => 'item_1788862350159.jpg',
                'name' => 'Đầm Chiffon Vàng Dáng Xòe Mùa Hè',
                'color' => 'Vàng',
                'material' => 'Chiffon cát mỏng mát 2 lớp',
                'price' => 780000,
                'short' => 'Sắc vàng pastel dịu mắt, tùng váy xòe rộng bay bổng cho ngày làm việc rạng ngời.',
                'desc' => "Màu vàng bơ pastel ngọt ngào, không kén da. Thiết kế cổ V đan chéo và tay áo cánh tiên mềm mại, thích hợp diện trong cả thời tiết hè oi ả.",
            ],
            [
                'file' => 'item_1788862350478.jpg',
                'name' => 'Đầm Voan Tơ Hoa Nhí Cổ Phối Ren',
                'color' => 'Xanh Bơ',
                'material' => 'Tơ sống thêu hoa phối ren chỉ',
                'price' => 890000,
                'short' => 'Gam màu xanh bơ dịu mát hot trend phối ren ngực tỉ mỉ, đai nơ thắt eo xinh xắn.',
                'desc' => "Xanh bơ pastel là màu sắc được các tín đồ thời trang săn đón nhiều nhất mùa này. Thiết kế phối ren chỉ chạy dọc thân áo và chân tay, tạo nên vẻ đẹp thuần khiết chuẩn nàng thơ công sở.",
            ],
            [
                'file' => 'item_1788862350814.jpg',
                'name' => 'Đầm Hoa Sứ Nền Navy Cổ Điển',
                'color' => 'Xanh Navy',
                'material' => 'Lụa Satin in hoa chìm',
                'price' => 880000,
                'short' => 'Họa tiết hoa sứ trắng thanh tao trên nền xanh navy quyền lực, tôn dáng và làm sáng da.',
                'desc' => "Tone xanh navy tôn da tuyệt đối kết hợp cùng những cánh hoa sứ trắng muốt. Form váy may theo tỉ lệ vàng ôm nhẹ phần eo và buông lơi tự nhiên, toát lên phong thái chuyên nghiệp.",
            ],
            [
                'file' => 'item_1788862351147.jpg',
                'name' => 'Đầm Xanh Hoa Nhí Nhún Chun Eo',
                'color' => 'Xanh Da Trời',
                'material' => 'Voan tơ in hoa cao cấp',
                'price' => 760000,
                'short' => 'Thiết kế nhún chun eo bản to co giãn thoải mái, hoa nhí xanh mát mắt và nữ tính.',
                'desc' => "Bản chun eo mềm mại giúp siết eo tự nhiên mà không gây hằn ngấn khó chịu. Tùng váy xếp tầng bồng bềnh mang lại nét trẻ trung, tươi tắn cho quý cô văn phòng.",
            ],
            [
                'file' => 'item_1788862351504.jpg',
                'name' => 'Đầm Sơ Mi Kẻ Sọc Hồng Trẻ Trung',
                'color' => 'Hồng Pastel',
                'material' => 'Kate Hàn dệt sọc chìm thoáng khí',
                'price' => 820000,
                'short' => 'Sọc dọc hồng pastel tạo hiệu ứng kéo dài đôi chân, cổ bẻ sơ mi lịch sự kín đáo.',
                'desc' => "Họa tiết sọc dọc luôn là bí quyết hack dáng đỉnh cao. Vải Kate Hàn đanh mịn, không xù lông, thấm hút mồ hôi tốt cùng đai thắt lưng may liền tiện dụng.",
            ],
            [
                'file' => 'item_1788862351815.jpg',
                'name' => 'Đầm Kẻ Caro Xanh Cổ Bèo Nhún',
                'color' => 'Xanh Da Trời',
                'material' => 'Cotton dệt caro mềm mại',
                'price' => 790000,
                'short' => 'Kẻ caro xanh ngọc thanh mát, cổ sen bèo nhún điệu đà cùng hàng cúc gỗ mộc mạc.',
                'desc' => "Thiết kế mang hơi hướng Mori Girl vintage ngọt ngào. Đường bèo nhún viền cổ áo kết hợp dây rút eo bằng vải mộc tạo nên tổng thể nhẹ nhàng, thanh tao và gần gũi.",
            ],
            [
                'file' => 'item_1788862352155.jpg',
                'name' => 'Đầm Sơ Mi Nâu Tây Thắt Đai Kim Loại',
                'color' => 'Nâu Cà Phê',
                'material' => 'Chéo Ý dệt sọc xương cá',
                'price' => 920000,
                'short' => 'Tone nâu tây sang chảnh thời thượng, thắt lưng da mặt kim loại vàng tôn trọn vòng eo.',
                'desc' => "Sắc nâu tây thời thượng cực kỳ tôn da và dễ phối phụ kiện. Cổ sơ mi đứng lịch sự, đường may chỉ trần sắc sảo và thắt lưng kim loại tạo điểm nhấn đẳng cấp cho set đồ công sở.",
            ],
            [
                'file' => 'item_1788862352468.jpg',
                'name' => 'Đầm Tơ Hoa Cam Đào Cổ V Phối Ren',
                'color' => 'Cam Đào',
                'material' => 'Tơ óng in hoa phối ren hoa văn',
                'price' => 890000,
                'short' => 'Tone cam đào rạng rỡ, cổ V viền ren hoa tinh tế mang lại nét đẹp ngọt ngào quý phái.',
                'desc' => "Sắc cam đào ấm áp làm bừng sáng khuôn mặt. Chất liệu tơ óng có độ bóng mờ sang trọng, chân váy xòe chữ A che gọn phần bắp chân, giúp nàng tự tin suốt ngày dài.",
            ],
            [
                'file' => 'item_1788862352791.jpg',
                'name' => 'Đầm Dáng A Phối Ren Chân Hàng Cúc Đen',
                'color' => 'Trắng Kem',
                'material' => 'Gấm vân hoa chìm phối ren mi',
                'price' => 950000,
                'short' => 'Đầm trắng kem dáng A phối chân ren mi đen tương phản, hàng cúc đen điểm xuyết nổi bật.',
                'desc' => "Thiết kế tối giản mà đầy ấn tượng với hai gam màu đen - trắng kinh điển. Vải gấm dệt hoa chìm sang trọng, chân váy phối ren mi cao cấp, cổ trụ khoét giọt nước độc đáo.",
            ],
            [
                'file' => 'item_1788862353101.jpg',
                'name' => 'Đầm Jean Xòe Nhẹ May Chỉ Nổi',
                'color' => 'Xanh Denim',
                'material' => 'Denim Cotton co giãn nhẹ',
                'price' => 870000,
                'short' => 'Chất jean mềm co giãn màu xanh chàm, các đường may chỉ nổi tạo phom dáng thắt eo.',
                'desc' => "Chất denim cao cấp đã qua xử lý giặt mềm, không phai màu. Thiết kế các đường rã may dọc thân giúp thắt eo tự nhiên và tạo cảm giác cơ thể thon thả hơn.",
            ],
            [
                'file' => 'item_1788862353435.jpg',
                'name' => 'Đầm Sát Nách Thắt Nơ Vai Quý Phái',
                'color' => 'Đen',
                'material' => 'Chifon cao cấp phối lụa',
                'price' => 890000,
                'short' => 'Đầm đen dáng midi sát nách thắt nơ vai quý tộc, lựa chọn hàng đầu cho tiệc tối và công sở.',
                'desc' => "Thiết kế nơ buộc vai linh hoạt, cổ V khoét sâu vừa phải tạo nét quyến rũ kín đáo. Chất vải rủ mềm mại tạo độ bay cho từng bước đi, có thể kết hợp cùng áo blazer khoác ngoài khi đi làm.",
            ],
            [
                'file' => 'item_1788862353789.jpg',
                'name' => 'Đầm Hoa Nhiệt Đới Rực Rỡ Cổ Bẻ',
                'color' => 'Xanh Da Trời',
                'material' => 'Lụa cát in họa tiết hoa 3D',
                'price' => 820000,
                'short' => 'Họa tiết hoa nhiệt đới mùa hè phối màu rực rỡ trên nền xanh da trời tươi mát.',
                'desc' => "Năng lượng mùa hè tràn ngập trong từng họa tiết hoa mẫu đơn nở rộ. Cổ áo sơ mi bẻ thanh lịch cân bằng lại nét rực rỡ, giúp trang phục vừa nổi bật vừa chuẩn mực công sở.",
            ],
            [
                'file' => 'item_1788862354147.jpg',
                'name' => 'Đầm Hoa Nhí Nâu Vintage Cổ V',
                'color' => 'Nâu Cà Phê',
                'material' => 'Voan tơ in hoa cổ điển',
                'price' => 790000,
                'short' => 'Họa tiết hoa nhí phong cách hoài cổ trên nền be nâu, viền ren ren hoa quanh cổ.',
                'desc' => "Gam màu be nâu nhã nhặn tôn lên nét đẹp đằm thắm của phụ nữ Á Đông. Thiết kế cổ V viền đăng ten tỉ mỉ, tay cánh dơi nhẹ nhàng thoải mái cho các hoạt động văn phòng.",
            ],
            [
                'file' => 'item_1788862354486.jpg',
                'name' => 'Đầm Linen Sát Nách Thắt Dây Thừng',
                'color' => 'Xanh Baby',
                'material' => 'Linen bột tự nhiên dệt gân nổi',
                'price' => 850000,
                'short' => 'Chất vải Linen tự nhiên màu xanh baby thoáng mát, điểm nhấn dây thắt eo phong cách resort.',
                'desc' => "Chất liệu Linen tự nhiên thân thiện với môi trường, có độ thở và thấm hút mồ hôi tuyệt đối. Phom dáng suông nhẹ xếp nếp dọc thân tạo vẻ thanh thoát và thoải mái tối đa.",
            ],
            [
                'file' => 'item_1788862354811.jpg',
                'name' => 'Đầm Chấm Bi Cổ V Viền Ren Nâu Be',
                'color' => 'Nâu Cà Phê',
                'material' => 'Voan Chiffon hạt cát',
                'price' => 830000,
                'short' => 'Họa tiết chấm bi đen trên nền nâu be trang nhã, cổ áo đắp ren trắng tinh tế.',
                'desc' => "Sự pha trộn tinh tế giữa vẻ đẹp cổ điển và nét trẻ trung hiện đại. Chấm bi nhỏ nhã nhặn, chi tiết ren viền cổ trắng làm điểm nhấn sáng bừng cho khuôn mặt người mặc.",
            ],
            [
                'file' => 'item_1788862355138.jpg',
                'name' => 'Đầm Ren Hoa Nổi Thắt Dây Eo',
                'color' => 'Trắng Kem',
                'material' => 'Ren dệt hoa chìm có lót lụa',
                'price' => 960000,
                'short' => 'Tone trắng kem tinh khôi dệt hoa nổi toàn thân, đai dây thắt eo mảnh mai duyên dáng.',
                'desc' => "Váy ren trắng là must-have item trong tủ đồ của mọi quý cô. Hoa văn dệt chìm sang trọng không phô trương, dây thắt eo mảnh nhẹ tạo điểm nhấn thon gọn.",
            ],
            [
                'file' => 'item_1788862355414.jpg',
                'name' => 'Đầm Cotton Thêu Đục Lỗ Nâu Socola',
                'color' => 'Nâu Cà Phê',
                'material' => 'Cotton thêu đục lỗ cao cấp',
                'price' => 920000,
                'short' => 'Họa tiết thêu đục lỗ ren độc đáo trên nền nâu socola trầm ấm, sang trọng và thoáng mát.',
                'desc' => "Kỹ thuật thêu đục lỗ laser tinh xảo tạo nên bề mặt vải độc đáo và thoáng khí tuyệt đối. Tone nâu socola sang trọng phối đai eo đồng điệu tôn dáng chuẩn.",
            ],
            [
                'file' => 'item_1788862355765.jpg',
                'name' => 'Đầm Tơ Hoa Phấn Cổ V Dáng Xòe',
                'color' => 'Trắng Kem',
                'material' => 'Tơ nhung in hoa sắc nét',
                'price' => 880000,
                'short' => 'Họa tiết hoa đào phấn rơi nhẹ trên nền trắng kem, cổ V viền ren trang nhã.',
                'desc' => "Họa tiết hoa phấn mộng mơ mang lại vẻ đẹp dịu dàng như nàng thơ. Phom váy chữ A xòe nhẹ, chiều dài qua gối kín đáo, chuẩn mực cho mọi môi trường công sở.",
            ],
            [
                'file' => 'item_1788862356088.jpg',
                'name' => 'Đầm Chấm Bi Xanh Viền Ren Chân',
                'color' => 'Xanh Baby',
                'material' => 'Chiffon dập gân chìm',
                'price' => 840000,
                'short' => 'Tone xanh baby chấm bi trắng tinh nghịch phối chân ren thêu trắng sang trọng.',
                'desc' => "Sắc xanh baby ngọt ngào giúp trẻ hóa phong cách công sở. Chân váy phối ren mi trắng cao cấp tạo điểm nhấn kiêu sa, tùng váy xòe xếp ly nhẹ nhàng thướt tha.",
            ],
            [
                'file' => 'item_1788862356392.jpg',
                'name' => 'Đầm Ombre Hoa Đỏ Thắt Đai Dáng Xòe',
                'color' => 'Cam Đào',
                'material' => 'Lụa Satin in chuyển màu Ombre',
                'price' => 910000,
                'short' => 'Hiệu ứng chuyển màu Ombre từ cam đào sang hoa đỏ rực rỡ, kèm đai thắt eo sang chảnh.',
                'desc' => "Kỹ thuật in chuyển màu Ombre đỉnh cao tạo hiệu ứng chân váy nở rộ như một vườn hoa. Thân trên màu cam đào nhẹ nhàng, cổ bẻ sơ mi thanh lịch và thắt lưng da mảnh tạo điểm nhấn ấn tượng.",
            ],
        ];

        DB::transaction(function () use ($items, $productDir, $variantDir, $colorModels, $sizeModels, $categoryIds) {
            $createdCount = 0;
            $updatedCount = 0;

            foreach ($items as $idx => $item) {
                $srcImage = base_path('images/' . $item['file']);
                $destProductImage = $productDir . '/' . $item['file'];
                $destVariantImage = $variantDir . '/' . $item['file'];

                // Sao chép ảnh vào public storage nếu file nguồn tồn tại
                if (File::exists($srcImage)) {
                    if (!File::exists($destProductImage)) {
                        File::copy($srcImage, $destProductImage);
                    }
                    if (!File::exists($destVariantImage)) {
                        File::copy($srcImage, $destVariantImage);
                    }
                }

                $baseSlug = Str::slug($item['name']);
                $slug = $baseSlug;
                
                // Đảm bảo slug duy nhất
                $existingProduct = Product::where('slug', $slug)->first();
                if (!$existingProduct) {
                    $product = Product::create([
                        'name' => $item['name'],
                        'slug' => $slug,
                        'short_description' => $item['short'],
                        'description' => $item['desc'],
                        'material' => $item['material'],
                        'status' => 'active',
                        'view_count' => rand(15, 250),
                    ]);
                    $createdCount++;
                } else {
                    $product = $existingProduct;
                    $product->update([
                        'name' => $item['name'],
                        'short_description' => $item['short'],
                        'description' => $item['desc'],
                        'material' => $item['material'],
                        'status' => 'active',
                    ]);
                    $updatedCount++;
                }

                // Gán danh mục Đầm & Váy Công Sở và Hàng Mới Về
                $product->categories()->sync($categoryIds);

                // Tạo ảnh chính cho sản phẩm nếu chưa có
                ProductImage::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'image_url' => 'products/' . $item['file'],
                    ],
                    [
                        'display_order' => 0,
                    ]
                );

                // Màu sắc tương ứng của sản phẩm
                $color = $colorModels[$item['color']] ?? $colorModels['Đen'];

                // Tạo 4 biến thể với 4 size (S, M, L, XL)
                foreach ($sizeModels as $sizeName => $sizeModel) {
                    $sku = 'AUR-' . strtoupper(Str::slug(Str::limit($item['name'], 20, ''))) . '-' . strtoupper($sizeName) . '-' . $product->id;
                    
                    ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'color_id' => $color->id,
                            'size_id' => $sizeModel->id,
                        ],
                        [
                            'sku' => $sku,
                            'barcode' => null,
                            'price' => $item['price'],
                            'sale_price' => null, // Không có khuyến mãi
                            'cost_price' => 0.00,  // Giá vốn để 0, cập nhật khi nhập kho
                            'stock_quantity' => 0, // Tồn kho để 0, cập nhật khi nhập kho
                            'low_stock_threshold' => 5,
                            'weight_grams' => 350,
                            'thumbnail_url' => 'variants/' . $item['file'],
                            'is_active' => 1,
                        ]
                    );
                }
            }

            $this->command->info("Đã xử lý xong: Tạo mới {$createdCount}, Cập nhật {$updatedCount} sản phẩm.");
        });
    }
}
