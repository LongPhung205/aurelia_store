@extends(request()->ajax() ? 'admin.layouts.empty' : 'admin.layouts.admin')

@section('title', 'Chi tiết phiếu nhập kho')

@section('content')
<div class="w-full flex flex-col {{ request()->ajax() ? 'p-6' : 'p-6 h-[calc(100vh-64px)]' }}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 shrink-0 bg-white dark:bg-[#111] p-4 rounded-2xl ring-1 ring-black/5 dark:ring-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
        <div>
            <h4 class="text-xl font-medium text-gray-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-upc-scan text-gray-400"></i>
                <span class="font-mono text-blue-600 dark:text-blue-400">{{ $import->code }}</span>
            </h4>
        </div>
        
        <div class="flex items-center gap-3">
            @if($import->status === 'pending')
                <form action="{{ route('admin.imports.update', $import->id) }}" method="POST" class="inline-block" id="cancelForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="cancel">
                    <button type="button" class="px-4 py-2 text-sm font-medium text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500 dark:hover:text-white" onclick="confirmCancel()">
                        Hủy Phiếu
                    </button>
                </form>
                
                <form action="{{ route('admin.imports.update', $import->id) }}" method="POST" class="inline-block" id="completeForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="complete">
                    <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-sm shadow-emerald-500/20" onclick="confirmComplete()">
                        Chốt Nhập Kho
                    </button>
                </form>
            @elseif($import->status === 'completed')
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 font-medium">{{ $import->completed_at->format('d/m/Y H:i') }}</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 uppercase tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Đã hoàn thành
                    </span>
                </div>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 uppercase tracking-wide">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Đã hủy
                </span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1 min-h-0">
        <!-- Cột trái: Chi tiết hàng -->
        <div class="lg:col-span-2 flex flex-col min-h-0 bg-white dark:bg-[#111] rounded-2xl ring-1 ring-black/5 dark:ring-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-white/5 shrink-0 bg-gray-50/50 dark:bg-white/[0.02]">
                <h5 class="mb-0 font-medium text-gray-900 dark:text-white text-sm uppercase tracking-wider">Sản phẩm nhập</h5>
            </div>
            
            <div class="overflow-auto relative bg-gray-50/30 dark:bg-transparent p-4 custom-scrollbar flex-1">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400" style="border-collapse: separate; border-spacing: 0 8px;">
                    <thead class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky top-0 z-10 bg-gray-50/90 dark:bg-[#111]/90 backdrop-blur-md">
                        <tr>
                            <th scope="col" class="px-5 py-3 rounded-l-2xl">Sản phẩm</th>
                            <th scope="col" class="px-5 py-3 text-center">SL</th>
                            <th scope="col" class="px-5 py-3 text-right">Thành tiền</th>
                            <th scope="col" class="px-5 py-3 text-center rounded-r-2xl">Chi tiết</th>
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
                            <tr class="bg-white dark:bg-[#111] shadow-sm hover:shadow-md dark:hover:bg-white/[0.02] ring-1 ring-black/5 dark:ring-white/5 transition-all group">
                                <td class="px-5 py-3 rounded-l-2xl align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden ring-1 ring-black/5 dark:ring-white/10 shrink-0 bg-gray-100 dark:bg-gray-800">
                                            <img src="{{ filter_var($productThumb, FILTER_VALIDATE_URL) ? $productThumb : asset('storage/'.$productThumb) }}" alt="{{ $product->name ?? 'N/A' }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white max-w-[200px] truncate" title="{{ $product->name ?? 'Sản phẩm đã xóa' }}">{{ $product->name ?? 'Sản phẩm đã xóa' }}</div>
                                            <div class="text-[11px] text-gray-500 font-medium mt-0.5"><i class="bi bi-box-seam"></i> {{ $details->count() }} phân loại</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center align-middle">
                                    <span class="font-medium text-lg text-gray-900 dark:text-white">{{ $productQty }}</span>
                                </td>
                                <td class="px-5 py-3 text-right align-middle font-mono font-medium text-blue-600 dark:text-blue-400">
                                    {{ number_format($productSubtotal) }} đ
                                </td>
                                <td class="px-5 py-3 text-center rounded-r-2xl align-middle">
                                    <button type="button" onclick="toggleSubRows('{{ $productId }}')" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-blue-600 dark:bg-white/5 dark:hover:bg-white/10 dark:text-gray-400 transition-colors inline-flex justify-center items-center">
                                        <i class="bi bi-chevron-down text-sm transition-transform duration-200" id="icon-{{ $productId }}"></i>
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
                                <tr class="hidden sub-row-{{ $productId }}">
                                    <td colspan="4" class="p-0">
                                        <div class="mx-4 my-1 bg-gray-50/80 dark:bg-white/[0.02] rounded-xl p-3 ring-1 ring-black/5 dark:ring-white/5 flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $imageUrl }}" alt="{{ $detail->variant->sku }}" class="w-8 h-8 object-cover rounded-lg ring-1 ring-black/5 dark:ring-white/10">
                                                <div>
                                                    <div class="font-mono text-xs font-medium text-gray-700 dark:text-gray-300">{{ $detail->variant->sku ?? 'N/A' }}</div>
                                                    <div class="text-[11px] text-gray-500 font-medium">
                                                        @if(optional($detail->variant)->color) {{ $detail->variant->color->name }} @endif
                                                        @if(optional($detail->variant)->color && optional($detail->variant)->size) - @endif
                                                        @if(optional($detail->variant)->size) {{ $detail->variant->size->name }} @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-6 text-sm">
                                                <div class="text-center">
                                                    <span class="block text-[10px] text-gray-400 uppercase font-medium">SL</span>
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ $detail->quantity }}</span>
                                                </div>
                                                <div class="text-right font-mono">
                                                    <span class="block text-[10px] text-gray-400 uppercase font-medium">Đơn giá</span>
                                                    {{ number_format($detail->unit_price) }} đ
                                                </div>
                                                <div class="text-right font-mono font-medium text-blue-600 dark:text-blue-400 min-w-[80px]">
                                                    <span class="block text-[10px] text-blue-300 dark:text-blue-500 uppercase">Thành tiền</span>
                                                    {{ number_format($detail->subtotal) }} đ
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Sticky Footer for totals -->
            <div class="p-4 border-t border-gray-100 dark:border-white/5 shrink-0 bg-white dark:bg-[#111] flex justify-between items-center rounded-b-2xl">
                <div class="font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider text-sm">Tổng cộng</div>
                <div class="flex items-center gap-8">
                    <div class="text-center">
                        <span class="text-xs text-gray-400 block font-medium">Tổng số lượng</span>
                        <span class="font-medium text-xl text-gray-900 dark:text-white">{{ $totalQty }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-gray-400 block font-medium">Tổng giá trị</span>
                        <span class="font-medium text-2xl font-mono text-blue-600 dark:text-blue-400">{{ number_format($import->total_amount) }} <span class="text-sm">đ</span></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cột phải: Thông tin phiếu -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-[#111] rounded-2xl ring-1 ring-black/5 dark:ring-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.02)] overflow-hidden sticky top-0">
                <div class="p-4 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02]">
                    <h5 class="font-medium text-gray-900 dark:text-white text-sm uppercase tracking-wider">Thông tin chứng từ</h5>
                </div>
                
                <div class="p-5 space-y-5">
                    <!-- Provider Info -->
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <i class="bi bi-buildings"></i>
                        </div>
                        <div>
                            <span class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider">Nhà cung cấp</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $import->supplier->name ?? 'N/A' }}</span>
                            @if(optional($import->supplier)->phone)
                                <span class="block text-xs font-mono text-gray-500 mt-0.5"><i class="bi bi-telephone-fill opacity-50 mr-1"></i> {{ $import->supplier->phone }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <hr class="border-gray-100 dark:border-white/5">
                    
                    <!-- Creator Info -->
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div>
                            <span class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider">Người lập phiếu</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $import->user->name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-white/5">

                    <!-- Time Info -->
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <span class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider">Thời gian tạo</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $import->created_at->format('d/m/Y') }}</span>
                            <span class="block text-xs font-mono text-gray-500 mt-0.5">{{ $import->created_at->format('H:i:s') }}</span>
                        </div>
                    </div>

                    @if($import->note)
                    <hr class="border-gray-100 dark:border-white/5">
                    
                    <div>
                        <span class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-2">Ghi chú</span>
                        <div class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50/80 dark:bg-white/[0.02] p-3 rounded-xl ring-1 ring-black/5 dark:ring-white/5 relative">
                            <i class="bi bi-quote text-2xl text-gray-200 dark:text-white/10 absolute top-1 right-2"></i>
                            <p class="relative z-10 leading-relaxed">{{ $import->note }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    if (typeof window.toggleSubRows === 'undefined') {
        window.toggleSubRows = function(productId) {
            document.querySelectorAll('.sub-row-' + productId).forEach(el => {
                el.classList.toggle('hidden');
            });
            
            const icon = document.getElementById('icon-' + productId);
            if (icon) {
                if (icon.classList.contains('rotate-180')) {
                    icon.classList.remove('rotate-180');
                } else {
                    icon.classList.add('rotate-180');
                }
            }
        };
    }
</script>
@endpush
