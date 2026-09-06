<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Xu Hướng Thu Đông 2026: Sự Trở Lại Của Những Gam Màu Trầm Ấm',
                'excerpt' => 'Mùa Thu Đông 2026 chứng kiến sự thống trị của các tông màu đất ấm áp. Cùng Aurelia khám phá cách phối đồ thanh lịch nhưng vẫn nổi bật trong tiết trời se lạnh.',
                'content' => '
<h2>1. Nguồn cảm hứng từ thiên nhiên</h2>
<p>Khi tiết trời chuyển lạnh, xu hướng thời trang cũng dần nhường chỗ cho những tông màu mang lại cảm giác ấm áp, an tâm. Nâu đất, cam gạch (terracotta), be và xanh rêu đang làm mưa làm gió trên các sàn diễn thời trang quốc tế.</p>
<p>Tại Aurelia, chúng tôi đã đưa những sắc thái này vào bộ sưu tập mới nhất với chất liệu wool, cashmere và dạ tweed cao cấp, giúp bạn không chỉ ấm áp mà còn toát lên vẻ sang trọng, cổ điển.</p>

<h2>2. Layering - Nghệ thuật phối lớp</h2>
<p>Thu đông là thời điểm lý tưởng nhất để trải nghiệm phong cách layering. Một chiếc áo len cổ lọ mỏng mặc trong, kết hợp cùng áo sơ mi lụa và khoác ngoài bằng một chiếc trench coat màu be sẽ tạo ra chiều sâu cho trang phục của bạn.</p>
<p><strong>Bí quyết từ stylist:</strong> Hãy giữ cho các lớp áo có sự tương đồng về sắc độ nhưng khác biệt về chất liệu để tạo điểm nhấn thị giác tinh tế.</p>
                ',
                'image' => 'posts/post_autumn_winter_1788602520437.jpg',
            ],
            [
                'title' => 'Bí Quyết Phối Đồ Với Áo Blazer: Thanh Lịch Nơi Công Sở, Cuốn Hút Chốn Hẹn Hò',
                'excerpt' => 'Blazer oversized không chỉ là đặc quyền của thời trang công sở. Cùng tìm hiểu cách "biến hoá" chiếc áo này để phù hợp với mọi hoàn cảnh.',
                'content' => '
<h2>1. Sức hút của Blazer Oversized</h2>
<p>Đã qua rồi thời của những chiếc blazer ôm sát cứng nhắc. Xu hướng hiện đại tôn vinh những đường cắt may rộng rãi, mang hơi hướng menswear nhưng lại cực kỳ tôn dáng và nữ tính nếu biết cách phối hợp.</p>

<h2>2. Mặc đẹp từ sáng đến tối</h2>
<ul>
    <li><strong>Đi làm:</strong> Phối blazer cùng áo sơ mi lụa, quần âu ống suông và một đôi loafer. Trông bạn sẽ cực kỳ chuyên nghiệp và tự tin.</li>
    <li><strong>Đi chơi/Hẹn hò:</strong> Đổi áo sơ mi thành một chiếc slip dress lụa mỏng manh bên trong. Sự tương phản giữa nét mềm mại của lụa và sự đứng dáng của blazer sẽ tạo nên sức hút khó cưỡng.</li>
</ul>
<p>Đừng quên phụ kiện! Một chiếc thắt lưng to bản nịt ngang eo blazer sẽ giúp tôn lên vòng hai và mang lại diện mạo hoàn toàn mới.</p>
                ',
                'image' => 'posts/post_blazer_style_1788602530734.jpg',
            ],
            [
                'title' => 'Minimalism: Vẻ Đẹp Của Sự Tối Giản Vượt Thời Gian',
                'excerpt' => 'Phong cách Minimalism không phải là sự đơn điệu, mà là nghệ thuật lược bỏ những chi tiết thừa thãi để tôn vinh đường nét cơ thể và chất liệu cao cấp.',
                'content' => '
<h2>1. Tối giản là gì?</h2>
<p>Minimalism trong thời trang tập trung vào các gam màu đơn sắc (thường là trắng, đen, be, xám) và những đường cắt may tinh giản nhất. Sự sang trọng của Minimalism đến từ chất lượng của vải và sự vừa vặn hoàn hảo trên cơ thể người mặc.</p>

<h2>2. Cách áp dụng Minimalism vào tủ đồ</h2>
<p>Để bắt đầu, hãy đầu tư vào những "item" cơ bản (staple pieces) có chất lượng tốt: Một chiếc áo thun trắng cổ tròn hoàn hảo, một chiếc quần tây đen cắt may chuẩn xác, và một chiếc slip dress đen (Little Black Dress).</p>
<p><em>"Less is more"</em> - Hãy tiết chế phụ kiện. Một đôi khuyên tai nụ bằng bạc hoặc dây chuyền mảnh là quá đủ để hoàn thiện vẻ ngoài thanh lịch này.</p>
                ',
                'image' => 'posts/post_minimalism_1788602555964.jpg',
            ],
            [
                'title' => 'Color of the Year 2026: Trở Thành Tâm Điểm Với Tông Màu Nổi Bật',
                'excerpt' => 'Năm 2026 đánh dấu sự lên ngôi của các gam màu rực rỡ mang đầy năng lượng tích cực. Bạn đã biết cách chinh phục những màu sắc khó nhằn này chưa?',
                'content' => '
<h2>1. Năng lượng từ màu sắc</h2>
<p>Sau những năm ưa chuộng màu trung tính, thời trang đang đón nhận sự trở lại mạnh mẽ của các tông màu rực rỡ như Cam cháy, Hồng Fuchsia và Xanh Cobalt. Những màu sắc này không chỉ giúp bạn nổi bật giữa đám đông mà còn có tác dụng kích thích tinh thần, mang lại năng lượng tích cực.</p>

<h2>2. Quy tắc Color-Blocking</h2>
<p>Nếu bạn là người can đảm, hãy thử phong cách color-blocking bằng cách kết hợp hai gam màu tương phản mạnh (như Cam - Xanh biển). Nếu muốn an toàn hơn, hãy sử dụng màu rực rỡ làm điểm nhấn duy nhất trên trang phục nền trung tính (ví dụ: áo khoác cam phối cùng set đồ all-black).</p>
<p>Aurelia Store tự hào mang đến bộ sưu tập mới nhất với các dải màu hot trend, được tinh chỉnh sắc độ để phù hợp nhất với làn da châu Á.</p>
                ',
                'image' => 'posts/post_color_trend_1788602565391.jpg',
            ]
        ];

        foreach ($posts as $postData) {
            $postData['slug'] = Str::slug($postData['title']);
            $postData['published_at'] = now();
            $postData['is_active'] = true;
            
            // Dùng updateOrCreate để có thể chạy nhiều lần mà không bị lỗi trùng slug
            Post::updateOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );
        }
    }
}
