<div class="relative w-full">
    @php
        $firstDetail = $details->first();
        $isOrder = $firstDetail && $firstDetail->reference && get_class($firstDetail->reference) == 'App\Models\Order';
        $isRefund = $isOrder && ($firstDetail->type == 'import' || $firstDetail->quantity_changed > 0);
    @endphp

    @if($isOrder)
        <div class="p-4 mb-6 rounded-2xl flex items-start gap-4 {{ $isRefund ? 'bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-500/20' : 'bg-blue-50 dark:bg-blue-500/10 ring-1 ring-blue-500/20' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $isRefund ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400' : 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400' }}">
                <i class="bi {{ $isRefund ? 'bi-arrow-return-left' : 'bi-cart-check' }} text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 dark:text-white text-base">
                    {{ $isRefund ? 'Đơn hàng hoàn trả' : 'Phiếu xuất kho tự động' }}
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Hệ thống tự động ghi nhận biến động từ đơn hàng <strong class="font-mono text-gray-900 dark:text-gray-200">ORD-{{ $firstDetail->reference_id }}</strong>.
                </p>
                <a href="{{ route('admin.orders.show', $firstDetail->reference_id) }}" class="inline-flex items-center gap-1.5 mt-3 text-sm font-semibold {{ $isRefund ? 'text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300' : 'text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300' }} transition-colors">
                    Xem chi tiết đơn hàng <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    @endif

    <div class="bg-gray-50/50 dark:bg-white/[0.02] rounded-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden">
        <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
            <thead class="text-[10px] font-semibold tracking-[0.1em] text-gray-400 uppercase bg-gray-100/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5">
                <tr>
                    <th scope="col" class="px-5 py-3.5">Sản phẩm</th>
                    <th scope="col" class="px-5 py-3.5 text-center">Biến động</th>
                    <th scope="col" class="px-5 py-3.5 text-center">Tồn trước</th>
                    <th scope="col" class="px-5 py-3.5 text-center">Tồn sau</th>
                    <th scope="col" class="px-5 py-3.5 text-right">Thời gian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @forelse($details as $detail)
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors duration-300">
                        <td class="px-5 py-4 align-middle">
                            <div class="flex items-center space-x-4">
                                @php
                                    $imageUrl = optional($detail->variant)->thumbnail_url ?: (optional(optional(optional($detail->variant)->product)->images)->first()->image_url ?? null);
                                    if ($imageUrl && !Str::startsWith($imageUrl, 'http')) {
                                        $imageUrl = asset('storage/' . $imageUrl);
                                    }
                                    if (!$imageUrl) {
                                        $imageUrl = 'https://ui-avatars.com/api/?name=' . urlencode(optional($detail->variant)->sku ?? 'NA') . '&background=F3F4F6&color=6B7280';
                                    }
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ optional($detail->variant)->sku }}" class="w-12 h-12 object-cover rounded-xl ring-1 ring-black/10 dark:ring-white/10 shadow-sm shrink-0">
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-white font-mono text-xs">{{ optional($detail->variant)->sku ?? 'N/A' }}</div>
                                    <div class="font-medium text-gray-700 dark:text-gray-300 mt-1 leading-tight line-clamp-1">
                                        {{ optional(optional($detail->variant)->product)->name ?? 'Sản phẩm không xác định' }}
                                    </div>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        @if(optional($detail->variant)->color)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                                {{ $detail->variant->color->name }}
                                            </span>
                                        @endif
                                        @if(optional($detail->variant)->size)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                                Size {{ $detail->variant->size->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center align-middle">
                            <div class="inline-flex items-baseline justify-center">
                                @if($detail->type == 'import' || $detail->quantity_changed > 0)
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold text-lg mr-0.5">+</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold text-lg">{{ abs($detail->quantity_changed) }}</span>
                                @elseif($detail->type == 'export' || $detail->quantity_changed < 0)
                                    <span class="text-rose-600 dark:text-rose-400 font-bold text-lg mr-0.5">-</span>
                                    <span class="text-rose-600 dark:text-rose-400 font-bold text-lg">{{ abs($detail->quantity_changed) }}</span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400 font-bold text-lg">{{ $detail->quantity_changed }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center align-middle">
                            <span class="text-gray-500 dark:text-gray-400 font-medium">{{ $detail->stock_before }}</span>
                        </td>
                        <td class="px-5 py-4 text-center align-middle">
                            <span class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 rounded-lg bg-gray-100 dark:bg-white/10 font-bold text-gray-900 dark:text-white">
                                {{ $detail->stock_after }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right align-middle text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($detail->created_at)->format('H:i:s') }}
                            <div class="text-[10px] mt-0.5 opacity-70">{{ \Carbon\Carbon::parse($detail->created_at)->format('d/m/Y') }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="inline-flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                <i class="bi bi-inboxes text-3xl mb-3 opacity-50"></i>
                                <p class="text-sm font-medium">Không có dữ liệu chi tiết.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
