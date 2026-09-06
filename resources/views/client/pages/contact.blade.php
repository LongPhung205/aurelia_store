@extends('client.pages.layout')

@section('title', 'Liên hệ - Aurelia Store')
@section('page_title', 'Liên hệ hỗ trợ')
@section('page_subtitle', 'Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn mọi lúc.')

@section('page_content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-12 mt-8">
    <div>
        <h2 class="!mt-0">Thông tin liên hệ</h2>
        <p>Nếu bạn có bất kỳ câu hỏi nào về sản phẩm, đơn hàng hoặc cần tư vấn phong cách, đừng ngần ngại liên hệ với Aurelia Store qua các kênh dưới đây. Đội ngũ CSKH sẽ phản hồi bạn sớm nhất có thể.</p>
        
        <div class="mt-8 space-y-6">
            <div class="flex items-start">
                <i class="bi bi-geo-alt text-2xl text-brand mr-4 mt-1"></i>
                <div>
                    <h3 class="!mt-0 mb-1 text-lg font-bold">Trụ sở chính</h3>
                    <p class="text-gray-600">Tầng 12, Tòa nhà Vincom Center, 72 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh.</p>
                </div>
            </div>
            
            <div class="flex items-start">
                <i class="bi bi-telephone text-2xl text-brand mr-4 mt-1"></i>
                <div>
                    <h3 class="!mt-0 mb-1 text-lg font-bold">Hotline (Miễn phí)</h3>
                    <p class="text-gray-600 font-medium">1800.6868</p>
                    <p class="text-sm text-gray-500">Hoạt động từ 8:00 - 22:00 hàng ngày</p>
                </div>
            </div>
            
            <div class="flex items-start">
                <i class="bi bi-envelope text-2xl text-brand mr-4 mt-1"></i>
                <div>
                    <h3 class="!mt-0 mb-1 text-lg font-bold">Email hỗ trợ</h3>
                    <p class="text-gray-600">care@aureliastore.com</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-gray-50 p-8 rounded-2xl border border-gray-100">
        <h3 class="!mt-0 mb-6 text-xl font-bold">Gửi lời nhắn cho chúng tôi</h3>
        <form action="#" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                <input type="text" class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                <input type="text" class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung tin nhắn</label>
                <textarea rows="4" class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50"></textarea>
            </div>
            <button type="button" class="w-full bg-brand text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-colors">Gửi lời nhắn</button>
        </form>
    </div>
</div>
@endsection
