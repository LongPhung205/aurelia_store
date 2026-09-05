@extends('admin.layouts.admin')

@section('title', 'Lịch sử Kho (Thẻ Kho)')

@section('content')
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col">
    <div class="flex justify-between items-center mb-6 shrink-0">
        <div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Thẻ Kho / Lịch sử xuất nhập</h4>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            Bảng điều khiển
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Thẻ kho</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <x-admin.card class="flex-1 flex flex-col min-h-0 border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 flex justify-between items-center bg-white dark:bg-gray-800 rounded-t-lg">
            <form action="{{ route('admin.inventory_history.index') }}" method="GET" class="flex w-full md:w-1/2">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="bi bi-search text-gray-500 dark:text-gray-400"></i>
                    </div>
                    <input type="text" name="sku" value="{{ request('sku') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Tra cứu theo mã SKU sản phẩm...">
                </div>
                <button type="submit" class="p-2.5 ml-2 text-sm font-medium text-white bg-blue-700 rounded-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <i class="bi bi-search"></i> Lọc
                </button>
                @if(request()->filled('sku'))
                    <a href="{{ route('admin.inventory_history.index') }}" class="p-2.5 ml-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Xóa lọc</a>
                @endif
            </form>
        </div>
        
        <div class="overflow-auto flex-1 relative bg-white dark:bg-gray-800">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Thời gian</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Mã chứng từ / Nội dung</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Loại</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Tổng số lượng</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Người thao tác</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">
                                {{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    @if($history->reference && get_class($history->reference) == 'App\Models\Import')
                                        Mã: <a href="{{ route('admin.imports.show', $history->reference_id) }}" class="text-blue-600 hover:underline font-mono" title="Xem phiếu nhập">{{ $history->reference->code }}</a>
                                    @elseif($history->reference && get_class($history->reference) == 'App\Models\Order')
                                        Mã đơn: <a href="{{ route('admin.orders.show', $history->reference_id) }}" class="text-blue-600 hover:underline font-mono" title="Xem đơn hàng">ORD-{{ $history->reference_id }}</a>
                                    @elseif($history->reference)
                                        Mã: <span class="font-mono">{{ $history->reference->code ?? $history->reference_id }}</span>
                                    @else
                                        Mã: <span class="font-mono text-gray-500">Điều chỉnh tay</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $history->note ?? 'Cập nhật kho' }} 
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">(Tổng: {{ abs($history->total_quantity) }} sp - {{ $history->variant_count }} biến thể)</span>
                                </div>
                                @if(isset($history->preview_variants) && $history->preview_variants->count() > 0)
                                <div class="flex -space-x-2 mt-2 overflow-hidden">
                                    @foreach($history->preview_variants as $variant)
                                        @php
                                            $imageUrl = $variant->thumbnail_url ?: (optional(optional($variant->product)->images)->first()->image_url ?? null);
                                            if ($imageUrl && !Str::startsWith($imageUrl, 'http')) {
                                                $imageUrl = asset('storage/' . $imageUrl);
                                            }
                                            if (!$imageUrl) {
                                                $imageUrl = 'https://ui-avatars.com/api/?name=' . urlencode($variant->sku ?? 'NA') . '&background=F3F4F6&color=6B7280';
                                            }
                                        @endphp
                                        <img class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-800 object-cover z-10 hover:z-20 relative" src="{{ $imageUrl }}" alt="{{ $variant->sku }}" title="{{ optional($variant->product)->name ?? $variant->sku }}">
                                    @endforeach
                                    @if($history->product_count > 3)
                                        <div class="flex items-center justify-center w-8 h-8 text-xs font-medium text-white bg-gray-500 border-2 border-white rounded-full dark:border-gray-800 z-10 cursor-help" title="Và {{ $history->product_count - 3 }} sản phẩm khác">
                                            +{{ $history->product_count - 3 }}
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($history->type == 'import')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Nhập kho</span>
                                @elseif($history->type == 'export')
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Xuất kho</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">Điều chỉnh</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-lg">
                                @if($history->type == 'import' || $history->total_quantity > 0)
                                    <span class="text-green-600 dark:text-green-400">+{{ abs($history->total_quantity) }}</span>
                                @elseif($history->type == 'export' || $history->total_quantity < 0)
                                    <span class="text-red-600 dark:text-red-400">-{{ abs($history->total_quantity) }}</span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-400">{{ $history->total_quantity }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $history->user->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" class="view-details-btn text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:bg-blue-500 dark:hover:bg-blue-600 focus:outline-none dark:focus:ring-blue-800" 
                                    data-ref-type="{{ $history->reference_type }}" 
                                    data-ref-id="{{ $history->reference_id }}"
                                    data-id="{{ $history->group_id }}">
                                    Xem chi tiết
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Không có lịch sử biến động nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($histories->hasPages())
            <x-slot name="footer">
                <div class="mt-2 flex justify-end w-full">
                    {{ $histories->links('pagination::tailwind') }}
                </div>
            </x-slot>
        @endif
    </x-admin.card>
</div>

<!-- Modal for details -->
<div id="detailsModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full flex items-center justify-center bg-gray-900/50 dark:bg-gray-900/80">
    <div class="relative w-full max-w-4xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 flex flex-col max-h-[90vh]">
            <!-- Modal header -->
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600 shrink-0">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Chi tiết biến động
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" onclick="document.getElementById('detailsModal').classList.add('hidden')">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Đóng</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-6 space-y-6 overflow-y-auto" id="detailsModalBody">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b dark:border-gray-600 shrink-0">
                <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600" onclick="document.getElementById('detailsModal').classList.add('hidden')">Đóng</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('detailsModal');
        const modalBody = document.getElementById('detailsModalBody');

        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const refType = this.getAttribute('data-ref-type');
                const refId = this.getAttribute('data-ref-id');
                const id = this.getAttribute('data-id');

                modal.classList.remove('hidden');
                modalBody.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div></div>';

                let url = '{{ route("admin.inventory_history.details") }}?';
                if (refType && refId) {
                    url += `reference_type=${encodeURIComponent(refType)}&reference_id=${encodeURIComponent(refId)}`;
                } else {
                    url += `id=${encodeURIComponent(id)}`;
                }

                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        modalBody.innerHTML = html;
                    })
                    .catch(error => {
                        modalBody.innerHTML = '<div class="text-red-500 text-center py-4">Có lỗi xảy ra khi tải dữ liệu.</div>';
                    });
            });
        });
    });
</script>
@endpush
