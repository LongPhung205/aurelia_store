@extends('client.pages.layout')

@section('title', 'Hệ thống cửa hàng - Aurelia Store')
@section('page_title', 'Hệ thống cửa hàng')
@section('page_subtitle', 'Trải nghiệm không gian mua sắm sang trọng tại các cửa hàng của Aurelia.')

@section('page_content')
<p class="mb-10 text-center text-lg text-gray-600">Với mong muốn mang lại trải nghiệm mua sắm hoàn hảo và chân thực nhất, Aurelia Store hiện đã có mặt tại các trung tâm thương mại và những con phố thời trang sầm uất nhất.</p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Store 1 -->
    <div class="border border-gray-100 rounded-2xl overflow-hidden hover:shadow-md transition-shadow">
        <div class="bg-gray-100 h-48 w-full flex items-center justify-center text-gray-400">
            <i class="bi bi-shop text-5xl"></i>
        </div>
        <div class="p-6">
            <h3 class="!mt-0 text-xl font-bold text-gray-900 mb-2">Aurelia Flagship Store - Quận 1</h3>
            <p class="text-sm text-brand font-medium mb-4"><i class="bi bi-star-fill text-yellow-400 mr-1"></i> Cửa hàng tiêu chuẩn Flagship</p>
            <div class="space-y-2 text-gray-600 text-sm">
                <p><i class="bi bi-geo-alt w-5 inline-block text-brand"></i> 72 Lê Thánh Tôn, P. Bến Nghé, Quận 1, TP. HCM</p>
                <p><i class="bi bi-telephone w-5 inline-block text-brand"></i> 028 3822 6868</p>
                <p><i class="bi bi-clock w-5 inline-block text-brand"></i> Mở cửa: 9:00 - 22:00 hàng ngày</p>
            </div>
            <button class="mt-6 w-full py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium rounded-lg transition-colors border border-gray-200">Xem bản đồ chỉ đường</button>
        </div>
    </div>

    <!-- Store 2 -->
    <div class="border border-gray-100 rounded-2xl overflow-hidden hover:shadow-md transition-shadow">
        <div class="bg-gray-100 h-48 w-full flex items-center justify-center text-gray-400">
            <i class="bi bi-shop text-5xl"></i>
        </div>
        <div class="p-6">
            <h3 class="!mt-0 text-xl font-bold text-gray-900 mb-2">Aurelia Boutique - Hai Bà Trưng</h3>
            <p class="text-sm text-gray-500 font-medium mb-4">Cửa hàng cao cấp</p>
            <div class="space-y-2 text-gray-600 text-sm">
                <p><i class="bi bi-geo-alt w-5 inline-block text-brand"></i> 135 Hai Bà Trưng, P. Bến Nghé, Quận 1, TP. HCM</p>
                <p><i class="bi bi-telephone w-5 inline-block text-brand"></i> 028 3822 7979</p>
                <p><i class="bi bi-clock w-5 inline-block text-brand"></i> Mở cửa: 9:30 - 22:00 hàng ngày</p>
            </div>
            <button class="mt-6 w-full py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium rounded-lg transition-colors border border-gray-200">Xem bản đồ chỉ đường</button>
        </div>
    </div>
    
    <!-- Store 3 -->
    <div class="border border-gray-100 rounded-2xl overflow-hidden hover:shadow-md transition-shadow">
        <div class="bg-gray-100 h-48 w-full flex items-center justify-center text-gray-400">
            <i class="bi bi-shop text-5xl"></i>
        </div>
        <div class="p-6">
            <h3 class="!mt-0 text-xl font-bold text-gray-900 mb-2">Aurelia Vincom Bà Triệu - Hà Nội</h3>
            <p class="text-sm text-gray-500 font-medium mb-4">Gian hàng TTTM</p>
            <div class="space-y-2 text-gray-600 text-sm">
                <p><i class="bi bi-geo-alt w-5 inline-block text-brand"></i> Tầng L2, Vincom Center, 191 Bà Triệu, Hà Nội</p>
                <p><i class="bi bi-telephone w-5 inline-block text-brand"></i> 024 3974 6868</p>
                <p><i class="bi bi-clock w-5 inline-block text-brand"></i> Mở cửa: 9:30 - 22:00 hàng ngày</p>
            </div>
            <button class="mt-6 w-full py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium rounded-lg transition-colors border border-gray-200">Xem bản đồ chỉ đường</button>
        </div>
    </div>
</div>
@endsection
