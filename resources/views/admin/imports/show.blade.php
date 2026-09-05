@extends(request()->ajax() ? 'admin.layouts.empty' : 'admin.layouts.admin')

@section('title', 'Chi tiết phiếu nhập kho')

@section('content')
<div class="px-0 w-full {{ request()->ajax() ? '' : 'h-[calc(100vh-90px)]' }} flex flex-col">
    <div class="flex justify-between items-center mb-6 shrink-0">
        <div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Phiếu Nhập: {{ $import->code }}</h4>
        </div>
        
        <div>
            @if($import->status === 'pending')
                <form action="{{ route('admin.imports.update', $import->id) }}" method="POST" class="inline-block mr-2" id="cancelForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="cancel">
                    <button type="button" class="text-red-600 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 hover:bg-gray-100 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700" onclick="confirmCancel()">
                        <i class="bi bi-x-circle mr-1"></i> Hủy Phiếu
                    </button>
                </form>
                
                <form action="{{ route('admin.imports.update', $import->id) }}" method="POST" class="inline-block" id="completeForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="complete">
                    <button type="button" class="text-white bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-800" onclick="confirmComplete()">
                        <i class="bi bi-check-circle mr-1"></i> Chốt Nhập Kho
                    </button>
                </form>
            @elseif($import->status === 'completed')
                <span class="bg-green-100 text-green-800 text-sm font-medium px-4 py-2 rounded-lg dark:bg-green-900 dark:text-green-300">
                    <i class="bi bi-check-circle mr-1"></i> Đã hoàn thành ({{ $import->completed_at->format('d/m/Y H:i') }})
                </span>
            @else
                <span class="bg-red-100 text-red-800 text-sm font-medium px-4 py-2 rounded-lg dark:bg-red-900 dark:text-red-300">
                    <i class="bi bi-x-circle mr-1"></i> Đã hủy
                </span>
            @endif
            @if(request()->ajax())
                <button type="button" class="ml-2 text-gray-400 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm w-9 h-9 inline-flex justify-center items-center border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 dark:hover:text-white" onclick="document.getElementById('detailsModal').classList.add('hidden')">
                    <i class="bi bi-x-lg text-lg"></i>
                    <span class="sr-only">Đóng</span>
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1 min-h-0">
        <!-- Cột trái: Chi tiết hàng -->
        <div class="lg:col-span-2 flex flex-col min-h-0">
            <x-admin.card class="flex-1 flex flex-col min-h-0 border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 bg-gray-50 dark:bg-gray-800 rounded-t-lg">
                    <h5 class="mb-0 font-semibold text-gray-900 dark:text-white">Danh sách sản phẩm nhập</h5>
                </div>
                
                <div class="overflow-auto relative max-h-[500px]">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-semibold">Sản phẩm</th>
                                <th scope="col" class="px-6 py-3 font-semibold text-center">Tổng SL</th>
                                <th scope="col" class="px-6 py-3 font-semibold text-right">Tổng thành tiền</th>
                                <th scope="col" class="px-6 py-3 font-semibold text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalQty = 0; @endphp
                            @foreach($groupedDetails as $productId => $details)
                                @php 
                                    $firstDetail = $details->first();
                                    $product = $firstDetail->variant->product ?? null;
                                    $productQty = $details->sum('quantity');
                                    $productSubtotal = $details->sum('subtotal');
                                    $totalQty += $productQty;
                                    
                                    $productThumb = null;
                                    if ($product && $product->images && $product->images->count() > 0) {
                                        $productThumb = $product->images->first()->image_url;
                                    } elseif ($firstDetail && optional($firstDetail->variant)->thumbnail_url) {
                                        $productThumb = $firstDetail->variant->thumbnail_url;
                                    }
                                    
                                    if (!$productThumb) {
                                        $productThumb = 'https://ui-avatars.com/api/?name=' . urlencode($product->name ?? 'NA') . '&background=F3F4F6&color=6B7280';
                                    }
                                @endphp
                                <!-- Dòng đại diện sản phẩm -->
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 align-middle font-medium text-gray-900 dark:text-white">
                                        <div class="flex items-center space-x-3">
                                            <img src="{{ filter_var($productThumb, FILTER_VALIDATE_URL) ? $productThumb : asset('storage/'.$productThumb) }}" alt="{{ $product->name ?? 'N/A' }}" class="w-10 h-10 object-cover rounded-md border border-gray-200 dark:border-gray-600">
                                            <div>
                                                <div class="font-bold block">{{ $product->name ?? 'Sản phẩm đã xóa' }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $details->count() }} biến thể (phân loại)</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-900 dark:text-white text-lg">
                                        {{ $productQty }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono font-bold text-blue-600 dark:text-blue-400">
                                        {{ number_format($productSubtotal) }} đ
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button type="button" onclick="toggleSubRows('{{ $productId }}')" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 focus:outline-none">
                                            <i class="bi bi-chevron-down text-lg transition-transform duration-200" id="icon-{{ $productId }}"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Sub-rows chi tiết từng variant (ẩn mặc định) -->
                                @foreach($details as $detail)
                                    @php
                                        $imageUrl = $detail->variant->thumbnail_url;
                                        if ($imageUrl && !Str::startsWith($imageUrl, 'http')) {
                                            $imageUrl = asset('storage/' . $imageUrl);
                                        }
                                        if (!$imageUrl) {
                                            $imageUrl = 'https://ui-avatars.com/api/?name=' . urlencode($detail->variant->sku) . '&background=F3F4F6&color=6B7280';
                                        }
                                    @endphp
                                    <tr class="hidden sub-row-{{ $productId }} bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <td colspan="4" class="px-6 py-3">
                                            <div class="flex items-center justify-between w-full pl-10">
                                                <!-- Thông tin variant -->
                                                <div class="flex items-center space-x-3 w-1/2">
                                                    <img src="{{ $imageUrl }}" alt="{{ $detail->variant->sku }}" class="w-8 h-8 object-cover rounded-md border border-gray-200 dark:border-gray-600">
                                                    <div>
                                                        <x-admin.badge variant="dark" class="mb-1">{{ $detail->variant->sku ?? 'N/A' }}</x-admin.badge>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            @if(optional($detail->variant)->color)
                                                                {{ $detail->variant->color->name }}
                                                            @endif
                                                            @if(optional($detail->variant)->color && optional($detail->variant)->size) - @endif
                                                            @if(optional($detail->variant)->size)
                                                                {{ $detail->variant->size->name }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Giá và Số lượng -->
                                                <div class="flex items-center space-x-8 text-sm">
                                                    <div class="text-center w-20">
                                                        <span class="block text-xs text-gray-400">SL Nhập</span>
                                                        <span class="font-bold text-gray-900 dark:text-white">{{ $detail->quantity }}</span>
                                                    </div>
                                                    <div class="text-right w-24 font-mono">
                                                        <span class="block text-xs text-gray-400">Đơn giá</span>
                                                        {{ number_format($detail->unit_price) }} đ
                                                    </div>
                                                    <div class="text-right w-28 font-mono font-semibold text-blue-600 dark:text-blue-400">
                                                        <span class="block text-xs text-gray-400 text-blue-300 dark:text-blue-500">Thành tiền</span>
                                                        {{ number_format($detail->subtotal) }} đ
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot class="sticky bottom-0 bg-gray-50 dark:bg-gray-700 shadow-[0_-1px_2px_rgba(0,0,0,0.1)] text-gray-900 dark:text-white font-bold">
                            <tr>
                                <td class="px-6 py-4 text-right">Tổng cộng:</td>
                                <td class="px-6 py-4 text-center">{{ $totalQty }}</td>
                                <td class="px-6 py-4 text-right text-lg text-blue-600 dark:text-blue-400">{{ number_format($import->total_amount) }} đ</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-admin.card>
        </div>
        
        <!-- Cột phải: Thông tin phiếu -->
        <div class="lg:col-span-1">
            <x-admin.card class="border-0 shadow-sm sticky top-4">
                <h5 class="mb-4 font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">Thông tin chứng từ</h5>
                
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Nhà cung cấp</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $import->supplier->name ?? 'N/A' }}</span>
                        @if(optional($import->supplier)->phone)
                            <span class="block text-xs text-gray-500 mt-1"><i class="bi bi-telephone"></i> {{ $import->supplier->phone }}</span>
                        @endif
                    </div>
                    
                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Người tạo phiếu</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ $import->user->name ?? 'N/A' }}</span>
                    </div>

                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Thời gian tạo</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ $import->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>

                    @if($import->note)
                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Ghi chú</span>
                        <p class="text-sm text-gray-900 dark:text-white italic mt-1 bg-gray-50 dark:bg-gray-700 p-2 rounded-lg border border-gray-200 dark:border-gray-600">{{ $import->note }}</p>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    function toggleSubRows(productId) {
        // Toggle the sub-rows visibility
        document.querySelectorAll('.sub-row-' + productId).forEach(el => {
            el.classList.toggle('hidden');
        });
        
        // Rotate the chevron icon
        const icon = document.getElementById('icon-' + productId);
        if (icon) {
            if (icon.classList.contains('rotate-180')) {
                icon.classList.remove('rotate-180');
            } else {
                icon.classList.add('rotate-180');
            }
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmCancel() {
        Swal.fire({
            title: 'Hủy phiếu nhập?',
            text: "Bạn có chắc muốn hủy phiếu nhập này không? Thao tác này không thể hoàn tác.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Đồng ý hủy',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancelForm').submit();
            }
        })
    }

    function confirmComplete() {
        Swal.fire({
            title: 'Chốt nhập kho?',
            text: "Khi chốt phiếu, số lượng sẽ được tự động cộng vào tồn kho và hệ thống sẽ cập nhật lại giá vốn bình quân (nếu có thay đổi). Bạn có chắc chắn không?",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Chắc chắn, chốt phiếu',
            cancelButtonText: 'Xem lại'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('completeForm').submit();
            }
        })
    }
</script>
@endpush
