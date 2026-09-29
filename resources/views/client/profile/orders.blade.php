@extends('client.profile.layout')

@section('profile_content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Quản lý Đơn hàng</h2>
            <p class="text-sm text-gray-500 mt-1">Theo dõi và quản lý các đơn hàng bạn đã đặt</p>
        </div>
    </div>

    <!-- Tabs Trạng thái -->
    <div class="flex overflow-x-auto gap-2 mb-6 pb-2 custom-scrollbar">
        @php
            $statuses = [
                'all' => 'Tất cả',
                'pending' => 'Chờ xác nhận',
                'processing' => 'Đã xác nhận',
                'ready_to_pick' => 'Chờ lấy hàng',
                'shipping' => 'Đang giao',
                'completed' => 'Đã giao',
                'cancelled' => 'Đã hủy'
            ];
            $currentStatus = request('status', 'all');
        @endphp

        @foreach($statuses as $key => $label)
            <a href="{{ route('profile.orders', ['status' => $key]) }}" 
               class="px-4 py-2 text-sm font-medium rounded-full whitespace-nowrap transition-colors {{ $currentStatus === $key ? 'bg-brand text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Danh sách đơn hàng -->
    <div class="space-y-6">
        @forelse($orders as $order)
            <div class="border border-gray-200 rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                <!-- Header Đơn hàng -->
                <div class="bg-gray-50 px-5 py-3 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-gray-900">Mã đơn: #ORD-{{ $order->id }}</span>
                        <span class="text-gray-400 text-sm">|</span>
                        <span class="text-gray-500 text-sm"><i class="bi bi-clock mr-1"></i> {{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'ready_to_pick' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                'shipping' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'completed' => 'bg-green-100 text-green-800 border-green-200',
                                'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                            ];
                            $statusLabels = [
                                'pending' => 'Chờ xác nhận',
                                'processing' => 'Đã xác nhận',
                                'ready_to_pick' => 'Chờ lấy hàng',
                                'shipping' => 'Đang giao hàng',
                                'completed' => 'Đã giao thành công',
                                'cancelled' => 'Đã hủy',
                            ];
                            $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            $label = $statusLabels[$order->status] ?? ucfirst($order->status);
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $colorClass }}">
                            {{ $label }}
                        </span>
                    </div>
                </div>

                <!-- Danh sách sản phẩm trong đơn -->
                <div class="p-5">
                    @foreach($order->items->take(2) as $item)
                        @php
                            $variant = $item->productVariant;
                            $product = $variant->product ?? null;
                            $imgUrl = $variant->thumbnail_url ?? ($product->primary_image_url ?? 'https://via.placeholder.com/150');
                            if ($imgUrl && !Str::startsWith($imgUrl, ['http://', 'https://'])) {
                                $imgUrl = Storage::url($imgUrl);
                            }
                        @endphp
                        <div class="flex gap-4 mb-4 last:mb-0">
                            <div class="w-20 h-24 bg-gray-100 rounded-lg overflow-hidden border border-gray-200 shrink-0">
                                <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-grow flex flex-col justify-center">
                                <h4 class="font-medium text-gray-900 line-clamp-2 leading-snug">{{ $item->product_name }}</h4>
                                <div class="text-sm text-gray-500 mt-1">Phân loại: {{ $item->variant_attributes }}</div>
                                <div class="text-sm text-gray-500">x{{ $item->quantity }}</div>
                            </div>
                            <div class="shrink-0 flex items-center justify-end">
                                <span class="font-bold text-gray-900">{{ number_format($item->price, 0, ',', '.') }}đ</span>
                            </div>
                        </div>
                    @endforeach

                    @if($order->items->count() > 2)
                        <div class="text-center mt-2 pt-2 border-t border-gray-100">
                            <span class="text-sm text-gray-500">Và {{ $order->items->count() - 2 }} sản phẩm khác...</span>
                        </div>
                    @endif
                </div>

                <!-- Footer Đơn hàng -->
                <div class="bg-gray-50 px-5 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-gray-600 text-sm">
                        Tổng tiền: <span class="text-xl font-bold text-brand ml-1">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="flex gap-3 w-full sm:w-auto items-center">
                        <a href="{{ route('profile.orders.show', $order->id) }}" class="flex-1 sm:flex-none text-center bg-white border border-gray-300 text-gray-700 px-5 py-2 rounded-lg font-medium hover:bg-gray-50 hover:text-brand hover:border-brand transition-colors text-sm">
                            Xem chi tiết
                        </a>
                        @if($order->status === 'pending')
                            <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="form-cancel-order inline-block flex-1 sm:flex-none" data-order-id="{{ $order->id }}">
                                @csrf
                                <button type="button" class="btn-trigger-cancel w-full text-center bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 hover:text-red-700 px-5 py-2 rounded-lg font-medium transition-colors text-sm">
                                    <i class="bi bi-x-circle mr-1"></i> Hủy đơn
                                </button>
                            </form>
                        @endif
                        @if($order->status === 'completed')
                            <a href="#" class="flex-1 sm:flex-none text-center bg-brand text-white px-5 py-2 rounded-lg font-medium hover:bg-[#C2185B] transition-colors text-sm">
                                Mua lại
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <div class="w-20 h-20 bg-white text-gray-300 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl shadow-sm">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Chưa có đơn hàng nào</h3>
                <p class="text-gray-500 mb-6">Bạn chưa có đơn hàng nào trong trạng thái này.</p>
                <a href="{{ route('home') }}" class="inline-block bg-brand text-white px-6 py-2.5 rounded-lg font-medium hover:bg-[#C2185B] transition-colors shadow-sm">
                    Tiếp tục mua sắm
                </a>
            </div>
        @endforelse
    </div>

    <!-- Phân trang -->
    <div class="mt-8">
        {{ $orders->appends(request()->query())->links() }}
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endsection
