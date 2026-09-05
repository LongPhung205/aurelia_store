<div class="relative overflow-x-auto">
    @php
        $firstDetail = $details->first();
        $isOrder = $firstDetail && $firstDetail->reference && get_class($firstDetail->reference) == 'App\Models\Order';
    @endphp

    @if($isOrder)
        <div class="flex p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <i class="bi bi-info-circle-fill inline flex-shrink-0 mr-3 text-lg"></i>
            <div>
                <span class="font-medium">Phiếu xuất kho tự động</span> sinh ra từ Đơn hàng <strong>ORD-{{ $firstDetail->reference_id }}</strong>. 
                <a href="{{ route('admin.orders.show', $firstDetail->reference_id) }}" class="font-semibold underline hover:text-blue-900 dark:hover:text-blue-300 ml-1">Xem chi tiết đơn hàng</a>
            </div>
        </div>
    @endif

    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Sản phẩm</th>
                <th scope="col" class="px-6 py-3 text-center">Biến động</th>
                <th scope="col" class="px-6 py-3 text-center">Tồn trước</th>
                <th scope="col" class="px-6 py-3 text-center">Tồn sau</th>
                <th scope="col" class="px-6 py-3 text-center">Thời gian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $detail)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4 align-middle">
                        <div class="flex items-center space-x-3">
                            @php
                                $imageUrl = optional($detail->variant)->thumbnail_url ?: (optional(optional(optional($detail->variant)->product)->images)->first()->image_url ?? null);
                                if ($imageUrl && !Str::startsWith($imageUrl, 'http')) {
                                    $imageUrl = asset('storage/' . $imageUrl);
                                }
                                if (!$imageUrl) {
                                    $imageUrl = 'https://ui-avatars.com/api/?name=' . urlencode(optional($detail->variant)->sku ?? 'NA') . '&background=F3F4F6&color=6B7280';
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" alt="{{ optional($detail->variant)->sku }}" class="w-10 h-10 object-cover rounded-md border border-gray-200 dark:border-gray-600">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white">{{ optional($detail->variant)->sku ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ optional(optional($detail->variant)->product)->name ?? 'N/A' }}
                                    @if(optional($detail->variant)->color)
                                        - {{ $detail->variant->color->name }}
                                    @endif
                                    @if(optional($detail->variant)->size)
                                        - {{ $detail->variant->size->name }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center font-bold text-lg">
                        @if($detail->type == 'import' || $detail->quantity_changed > 0)
                            <span class="text-green-600 dark:text-green-400">+{{ abs($detail->quantity_changed) }}</span>
                        @elseif($detail->type == 'export' || $detail->quantity_changed < 0)
                            <span class="text-red-600 dark:text-red-400">-{{ abs($detail->quantity_changed) }}</span>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $detail->quantity_changed }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        {{ $detail->stock_before }}
                    </td>
                    <td class="px-6 py-4 text-center font-bold text-gray-900 dark:text-white">
                        {{ $detail->stock_after }}
                    </td>
                    <td class="px-6 py-4 text-center text-xs">
                        {{ \Carbon\Carbon::parse($detail->created_at)->format('H:i:s d/m') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu chi tiết.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
