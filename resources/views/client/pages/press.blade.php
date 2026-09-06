@extends('client.pages.layout')

@section('title', 'Góc báo chí - Aurelia Store')
@section('page_title', 'Báo chí nói gì về Aurelia?')
@section('page_subtitle', 'Sự công nhận từ các tạp chí thời trang uy tín là động lực to lớn của chúng tôi.')

@section('page_content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
    <!-- Press 1 -->
    <div class="flex flex-col border border-gray-100 rounded-xl overflow-hidden hover:shadow-lg transition-all bg-white">
        <div class="h-48 bg-gray-900 flex items-center justify-center p-8">
            <h3 class="text-white text-4xl font-serif tracking-widest uppercase">VOGUE</h3>
        </div>
        <div class="p-6 flex-1 flex flex-col">
            <p class="text-sm text-gray-500 mb-2">Tháng 10, 2025</p>
            <h4 class="font-bold text-lg text-gray-900 mb-3">"Sự trỗi dậy của thời trang bền vững tại Việt Nam"</h4>
            <p class="text-gray-600 text-sm mb-4 line-clamp-3">Aurelia Store mang đến một làn gió mới với tư duy thiết kế tối giản, tập trung vào chất liệu lụa thân thiện với môi trường, đánh dấu bước ngoặt lớn trong ngành thời trang nội địa...</p>
            <a href="#" class="mt-auto text-brand font-semibold text-sm hover:underline">Đọc toàn bộ bài viết &rarr;</a>
        </div>
    </div>
    
    <!-- Press 2 -->
    <div class="flex flex-col border border-gray-100 rounded-xl overflow-hidden hover:shadow-lg transition-all bg-white">
        <div class="h-48 bg-black flex items-center justify-center p-8">
            <h3 class="text-white text-3xl font-serif tracking-widest uppercase">ELLE</h3>
        </div>
        <div class="p-6 flex-1 flex flex-col">
            <p class="text-sm text-gray-500 mb-2">Tháng 03, 2026</p>
            <h4 class="font-bold text-lg text-gray-900 mb-3">"Bộ sưu tập Xuân Hè 2026: Lời thì thầm của Nắng"</h4>
            <p class="text-gray-600 text-sm mb-4 line-clamp-3">Màn ra mắt BST mới của Aurelia đã chinh phục giới mộ điệu bởi những đường cắt may phóng khoáng, biến những quý cô công sở trở nên kiêu kỳ và quyến rũ hơn bao giờ hết...</p>
            <a href="#" class="mt-auto text-brand font-semibold text-sm hover:underline">Đọc toàn bộ bài viết &rarr;</a>
        </div>
    </div>
    
    <!-- Press 3 -->
    <div class="flex flex-col border border-gray-100 rounded-xl overflow-hidden hover:shadow-lg transition-all bg-white">
        <div class="h-48 bg-white border-b border-gray-100 flex items-center justify-center p-8">
            <h3 class="text-gray-900 text-2xl font-serif uppercase tracking-widest font-bold">Harper's BAZAAR</h3>
        </div>
        <div class="p-6 flex-1 flex flex-col">
            <p class="text-sm text-gray-500 mb-2">Tháng 08, 2026</p>
            <h4 class="font-bold text-lg text-gray-900 mb-3">"Thương hiệu local brand vươn tầm quốc tế"</h4>
            <p class="text-gray-600 text-sm mb-4 line-clamp-3">Với chất lượng hoàn thiện không thua kém các nhà mốt danh tiếng, Aurelia đang từng bước khẳng định vị thế và mở rộng thị trường sang các nước lân cận trong khu vực...</p>
            <a href="#" class="mt-auto text-brand font-semibold text-sm hover:underline">Đọc toàn bộ bài viết &rarr;</a>
        </div>
    </div>
</div>

<h2 class="mt-12">Thông cáo báo chí (Press Kit)</h2>
<p>Dành cho các cơ quan thông tấn báo chí, stylist và đối tác truyền thông. Vui lòng tải tài liệu giới thiệu thương hiệu, logo chuẩn và hình ảnh chất lượng cao của bộ sưu tập mới nhất tại liên kết dưới đây.</p>
<button class="mt-4 bg-gray-900 hover:bg-black text-white px-6 py-2 rounded-lg font-medium inline-flex items-center transition-colors">
    <i class="bi bi-download mr-2"></i> Tải xuống Press Kit (PDF, 15MB)
</button>
<p class="mt-4 text-sm text-gray-500">Mọi liên hệ hợp tác truyền thông, vui lòng gửi email về: <strong>pr@aureliastore.com</strong></p>
@endsection
