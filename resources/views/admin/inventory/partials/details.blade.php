<div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3 font-semibold">Mã SKU</th>
                <th scope="col" class="px-6 py-3 font-semibold">Phân loại</th>
                <th scope="col" class="px-6 py-3 font-semibold text-right">Giá vốn</th>
                <th scope="col" class="px-6 py-3 font-semibold text-center">Tồn kho</th>
                <th scope="col" class="px-6 py-3 font-semibold text-center">Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse($variants as $variant)
                @php
                    $isLowStock = $variant->stock_quantity <= $variant->low_stock_threshold;
                @endphp
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 {{ $isLowStock ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                        <div class="flex items-center space-x-3">
                            @php
                                $imageUrl = $variant->thumbnail_url ?: (optional(optional($variant->product)->images)->first()->image_url ?? null);
                                if ($imageUrl && !Str::startsWith($imageUrl, 'http')) {
                                    $imageUrl = asset('storage/' . $imageUrl);
                                }
                                if (!$imageUrl) {
                                    $imageUrl = 'https://ui-avatars.com/api/?name=' . urlencode($variant->sku ?? 'NA') . '&background=F3F4F6&color=6B7280';
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" alt="{{ $variant->sku }}" class="w-10 h-10 object-cover rounded-md border border-gray-200 dark:border-gray-600">
                            <x-admin.badge variant="dark">{{ $variant->sku }}</x-admin.badge>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($variant->color)
                            <x-admin.badge variant="dark" class="mr-1">
                                <div class="w-3 h-3 rounded-full inline-block mr-1 align-middle border border-gray-300" style="background-color: {{ $variant->color->hex_code }}"></div>
                                {{ $variant->color->name }}
                            </x-admin.badge>
                        @endif
                        @if($variant->size)
                            <x-admin.badge variant="dark">{{ $variant->size->name }}</x-admin.badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right font-mono">
                        {{ number_format($variant->cost_price) }} đ
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="font-bold {{ $isLowStock ? 'text-red-600 dark:text-red-400 text-lg' : 'text-gray-900 dark:text-white' }}">
                            {{ $variant->stock_quantity }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($variant->stock_quantity <= 0)
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Hết hàng</span>
                        @elseif($isLowStock)
                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Sắp hết (<={{ $variant->low_stock_threshold }})</span>
                        @else
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Còn hàng</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Sản phẩm này chưa có biến thể nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
