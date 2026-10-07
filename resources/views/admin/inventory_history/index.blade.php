@extends('admin.layouts.admin')

@section('title', 'Lịch sử Kho (Thẻ Kho)')



@section('content')
<div class="px-4 md:px-8 py-4 w-full h-[calc(100vh-64px)] flex flex-col bg-gray-50 dark:bg-[#050505]">
    <!-- The Main App Container -->
    <div class="flex-1 flex flex-col min-h-0 bg-white dark:bg-[#0a0a0a] rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-none ring-1 ring-black/5 dark:ring-white/10 overflow-hidden relative">

        
        <!-- Advanced Filters Toolbar -->
        <div class="p-3 md:p-4 border-b border-gray-100 dark:border-white/5 shrink-0 bg-white/60 dark:bg-[#0a0a0a]/60 backdrop-blur-2xl z-30">
            
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5">
                
                <!-- Type Pills -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.inventory_history.index', array_merge(request()->except('type', 'page'))) }}" 
                       class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 {{ !request()->filled('type') ? 'bg-gray-900 text-white dark:bg-white dark:text-black shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white' }}">
                       Tất cả
                       @if(isset($counts['all']) && $counts['all'] > 0)
                           <span class="ml-1.5 px-1.5 py-0.5 text-[10px] rounded-full {{ !request()->filled('type') ? 'bg-white/20 dark:bg-black/20' : 'bg-gray-200 dark:bg-white/10' }}">{{ $counts['all'] }}</span>
                       @endif
                    </a>

                    <a href="{{ route('admin.inventory_history.index', array_merge(request()->except('type', 'page'), ['type' => 'import'])) }}" 
                       class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 {{ request('type') === 'import' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20' }}">
                       Nhập kho
                       @if(isset($counts['import']) && $counts['import'] > 0)
                           <span class="ml-1.5 px-1.5 py-0.5 text-[10px] rounded-full {{ request('type') === 'import' ? 'bg-white/20' : 'bg-emerald-200/50 dark:bg-emerald-400/20' }}">{{ $counts['import'] }}</span>
                       @endif
                    </a>

                    <a href="{{ route('admin.inventory_history.index', array_merge(request()->except('type', 'page'), ['type' => 'export'])) }}" 
                       class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 {{ request('type') === 'export' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/20' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20' }}">
                       Xuất kho
                       @if(isset($counts['export']) && $counts['export'] > 0)
                           <span class="ml-1.5 px-1.5 py-0.5 text-[10px] rounded-full {{ request('type') === 'export' ? 'bg-white/20' : 'bg-rose-200/50 dark:bg-rose-400/20' }}">{{ $counts['export'] }}</span>
                       @endif
                    </a>
                </div>

                <!-- Query Filters Form -->
                <form action="{{ route('admin.inventory_history.index') }}" method="GET" class="flex flex-wrap w-full xl:w-auto items-center gap-3">
                    @if(request()->filled('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif

                    <!-- Date Range: From -->
                    <div class="relative w-full md:w-36">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="bi bi-calendar-event text-gray-400"></i>
                        </div>
                        <input type="text" name="date_from" value="{{ request('date_from') }}" id="date-from-picker"
                               class="bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-2 focus:ring-gray-900 block w-full pl-10 p-2.5 dark:bg-white/5 dark:ring-white/10 dark:text-white dark:focus:ring-white transition-all" 
                               placeholder="Từ ngày">
                    </div>

                    <!-- Date Range: To -->
                    <div class="relative w-full md:w-36">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="bi bi-calendar-check text-gray-400"></i>
                        </div>
                        <input type="text" name="date_to" value="{{ request('date_to') }}" id="date-to-picker"
                               class="bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-2 focus:ring-gray-900 block w-full pl-10 p-2.5 dark:bg-white/5 dark:ring-white/10 dark:text-white dark:focus:ring-white transition-all" 
                               placeholder="Đến ngày">
                    </div>

                    <!-- User Select -->
                    <div class="relative w-full md:w-48">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="bi bi-person-circle text-gray-400"></i>
                        </div>
                        <select name="user_id" class="bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-2 focus:ring-gray-900 block w-full pl-10 p-2.5 dark:bg-white/5 dark:ring-white/10 dark:text-white dark:focus:ring-white transition-all appearance-none cursor-pointer">
                            <option value="">Tất cả người tạo</option>
                            @foreach($users ?? [] as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="px-5 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl font-medium text-sm hover:opacity-90 active:scale-95 transition-all shrink-0">
                        Tìm kiếm
                    </button>
                    
                    @if(request()->filled('date_from') || request()->filled('date_to') || request()->filled('user_id'))
                        <a href="{{ route('admin.inventory_history.index', request()->only('type')) }}" class="p-2.5 text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 rounded-xl transition-all dark:bg-white/5 dark:hover:bg-rose-500/10 shrink-0 group">
                            <i class="bi bi-x-lg block group-hover:rotate-90 transition-transform"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
        
        <!-- Table Wrapper: Floating Rows Design -->
        <div class="overflow-auto flex-1 relative bg-gray-50/50 dark:bg-[#050505]/50 px-4 pb-2">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400" style="border-collapse: separate; border-spacing: 0 8px;">
                <thead class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky top-0 z-20 backdrop-blur-md bg-gray-50/90 dark:bg-[#050505]/90">
                    <tr>
                        <th scope="col" class="px-5 py-3 rounded-l-2xl">Thời gian</th>
                        <th scope="col" class="px-5 py-3">Mã chứng từ / Nội dung</th>
                        <th scope="col" class="px-5 py-3 text-center">Trạng thái</th>
                        <th scope="col" class="px-5 py-3 text-center">Biến động</th>
                        <th scope="col" class="px-5 py-3">Nhân sự</th>
                        <th scope="col" class="px-5 py-3 text-right rounded-r-2xl">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr class="bg-white dark:bg-[#111] shadow-[0_2px_10px_rgba(0,0,0,0.02)] dark:shadow-none hover:shadow-lg dark:hover:bg-white/[0.05] ring-1 ring-black/5 dark:ring-white/5 transition-all duration-300 group">
                            <td class="px-5 py-3 whitespace-nowrap rounded-l-2xl align-middle">
                                <div class="font-medium text-gray-900 dark:text-white text-base font-mono">
                                    {{ \Carbon\Carbon::parse($history->created_at)->format('H:i:s') }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                                    {{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y') }}
                                </div>
                            </td>
                            
                            <td class="px-5 py-3 align-middle">
                                <div class="font-medium text-gray-900 dark:text-white flex items-center gap-3">
                                    @if($history->reference && get_class($history->reference) == 'App\Models\Import')
                                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 flex items-center justify-center shrink-0 ring-1 ring-emerald-500/20">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                        </div>
                                        <a href="{{ route('admin.imports.show', $history->reference_id) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 underline decoration-gray-300 dark:decoration-gray-700 underline-offset-4 transition-colors font-mono">{{ $history->reference->code }}</a>
                                    @elseif($history->reference && get_class($history->reference) == 'App\Models\Order')
                                        @if($history->type == 'import' || $history->total_quantity > 0)
                                            <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 flex items-center justify-center shrink-0 ring-1 ring-amber-500/20">
                                                <i class="bi bi-arrow-return-left"></i>
                                            </div>
                                            <span>Khách hoàn trả (ĐH): <a href="{{ route('admin.orders.show', $history->reference_id) }}" class="hover:text-amber-600 dark:hover:text-amber-400 underline decoration-gray-300 dark:decoration-gray-700 underline-offset-4 transition-colors font-mono">ORD-{{ $history->reference_id }}</a></span>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 flex items-center justify-center shrink-0 ring-1 ring-indigo-500/20">
                                                <i class="bi bi-cart3"></i>
                                            </div>
                                            <span>Đơn hàng: <a href="{{ route('admin.orders.show', $history->reference_id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 underline decoration-gray-300 dark:decoration-gray-700 underline-offset-4 transition-colors font-mono">ORD-{{ $history->reference_id }}</a></span>
                                        @endif
                                    @elseif($history->reference)
                                        Mã: <span class="font-mono">{{ $history->reference->code ?? $history->reference_id }}</span>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400 flex items-center justify-center shrink-0 ring-1 ring-gray-900/10 dark:ring-white/20">
                                            <i class="bi bi-sliders"></i>
                                        </div>
                                        <span>Điều chỉnh thủ công</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium ml-10">
                                    {{ $history->note ?? 'Cập nhật số lượng kho' }} 
                                </div>
                                
                                @if(isset($history->preview_variants) && $history->preview_variants->count() > 0)
                                <div class="flex items-center -space-x-3 mt-2 ml-10 hover:space-x-1 transition-all duration-500 ease-out">
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
                                        <img class="w-8 h-8 rounded-full ring-2 ring-white dark:ring-[#111] object-cover relative z-10 shadow-sm" src="{{ $imageUrl }}" alt="{{ $variant->sku }}" title="{{ optional($variant->product)->name ?? $variant->sku }}">
                                    @endforeach
                                    @if($history->product_count > 3)
                                        <div class="flex items-center justify-center w-8 h-8 text-[10px] font-medium text-gray-700 bg-gray-100 ring-2 ring-white rounded-full dark:ring-[#111] dark:bg-white/10 dark:text-white relative z-20 shadow-sm">
                                            +{{ $history->product_count - 3 }}
                                        </div>
                                    @endif
                                    <span class="opacity-0 group-hover:opacity-100 pl-2 text-[10px] font-medium text-gray-400 transition-opacity delay-100">Click chi tiết để xem thêm</span>
                                </div>
                                @endif
                            </td>
                            
                            <td class="px-5 py-3 text-center align-middle">
                                @if($history->type == 'import' && $history->reference && get_class($history->reference) == 'App\Models\Order')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Hoàn trả
                                    </span>
                                @elseif($history->type == 'import')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Nhập kho
                                    </span>
                                @elseif($history->type == 'export')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Xuất kho
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Điều chỉnh
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-5 py-3 text-center align-middle">
                                <div class="inline-flex items-baseline justify-center font-mono">
                                    @if($history->type == 'import' || $history->total_quantity > 0)
                                        <span class="text-emerald-500 dark:text-emerald-400 font-medium text-xl mr-1">+</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium text-2xl tracking-tighter">{{ abs($history->total_quantity) }}</span>
                                    @elseif($history->type == 'export' || $history->total_quantity < 0)
                                        <span class="text-rose-500 dark:text-rose-400 font-medium text-xl mr-1">-</span>
                                        <span class="text-rose-600 dark:text-rose-400 font-medium text-2xl tracking-tighter">{{ abs($history->total_quantity) }}</span>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400 font-medium text-2xl tracking-tighter">{{ $history->total_quantity }}</span>
                                    @endif
                                </div>
                            </td>
                            
                            <td class="px-5 py-3 align-middle">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-700 dark:text-gray-300 font-medium tracking-wider ring-1 ring-black/5 dark:ring-white/10 text-xs">
                                        {{ substr($history->user->name ?? 'N', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $history->user->name ?? 'System' }}</div>
                                        <div class="text-[9px] uppercase tracking-widest text-gray-400">Admin</div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-5 py-3 text-right rounded-r-2xl align-middle">
                                <button type="button" class="view-details-btn group/btn relative inline-flex items-center justify-center p-3 text-gray-500 bg-gray-50 hover:bg-gray-900 hover:text-white rounded-xl dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white dark:hover:text-black transition-all duration-300" 
                                    data-ref-type="{{ $history->reference_type }}" 
                                    data-ref-id="{{ $history->reference_id }}"
                                    data-id="{{ $history->group_id }}">
                                    <i class="bi bi-arrow-up-right text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-16 text-center">
                                <div class="inline-flex flex-col items-center justify-center text-gray-400 dark:text-gray-600">
                                    <div class="w-24 h-24 mb-6 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center ring-4 ring-gray-50 dark:ring-[#0a0a0a]">
                                        <i class="bi bi-inbox text-4xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Không tìm thấy dữ liệu</h3>
                                    <p class="text-sm max-w-sm">Chưa có giao dịch kho nào được ghi nhận hoặc không có kết quả phù hợp với bộ lọc hiện tại.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($histories->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5 bg-white dark:bg-[#0a0a0a] z-30">
                <!-- Customized Pagination -->
                <div class="flex items-center justify-between">
                    <div class="hidden md:block">
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                            Hiển thị <span class="font-medium text-gray-900 dark:text-white">{{ $histories->firstItem() }}</span> đến <span class="font-medium text-gray-900 dark:text-white">{{ $histories->lastItem() }}</span> trong <span class="font-medium text-gray-900 dark:text-white">{{ $histories->total() }}</span> kết quả
                        </p>
                    </div>
                    <div>
                        {{ $histories->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Premium Modal for details -->
<div id="detailsModal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
    
    <!-- Backdrop with heavy blur -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    
    <div class="relative w-full max-w-4xl mx-auto my-6 z-50 p-4">
        <!-- Modal content -->
        <div class="relative bg-white dark:bg-[#0a0a0a] rounded-[2rem] shadow-2xl flex flex-col max-h-[85vh] ring-1 ring-black/5 dark:ring-white/10 overflow-hidden scale-95 opacity-0 transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] transform" id="modalContent">
            
            <!-- Modal header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02]">
                <h3 class="text-xl font-medium tracking-tight text-gray-900 dark:text-white flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <i class="bi bi-file-earmark-text text-sm"></i>
                    </div>
                    Chi tiết biến động
                </h3>
                <button type="button" class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-900 dark:bg-white/5 dark:hover:bg-white/10 dark:hover:text-white flex justify-center items-center transition-colors" onclick="closeModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <!-- Modal body -->
            <div class="p-0 overflow-y-auto custom-scrollbar" id="detailsModalBody">
                <div class="flex flex-col items-center justify-center py-24">
                    <div class="w-10 h-10 border-4 border-gray-200 border-t-gray-900 dark:border-white/10 dark:border-t-white rounded-full animate-spin"></div>
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide">Đang tải dữ liệu...</p>
                </div>
            </div>
            
            <!-- Modal footer -->
            <div class="p-6 border-t border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02] flex justify-end">
                <button type="button" class="px-6 py-2.5 rounded-full text-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:ring-4 focus:ring-gray-100 dark:bg-white/5 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white dark:focus:ring-white/10 transition-all duration-300" onclick="closeModal()">
                    Đóng cửa sổ
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar {
        z-index: 99999 !important;
        font-family: inherit;
    }
    /* Custom Scrollbar for Modal Body to keep it clean */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.3);
        border-radius: 20px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>
@endpush

@push('scripts')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
<script>
    function initFlatpickr() {
        if(typeof flatpickr !== 'undefined') {
            flatpickr("#date-from-picker", {
                dateFormat: "Y-m-d",
                locale: "vn",
                altInput: true,
                altFormat: "d/m/Y",
                placeholder: "Từ ngày",
                disableMobile: true
            });
            flatpickr("#date-to-picker", {
                dateFormat: "Y-m-d",
                locale: "vn",
                altInput: true,
                altFormat: "d/m/Y",
                placeholder: "Đến ngày",
                disableMobile: true
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initFlatpickr);
    // Support for Turbolinks/Livewire if present
    document.addEventListener('turbolinks:load', initFlatpickr);
    document.addEventListener('livewire:load', initFlatpickr);
    // Also run immediately just in case
    initFlatpickr();

    const modal = document.getElementById('detailsModal');
    const modalContent = document.getElementById('modalContent');
    const modalBody = document.getElementById('detailsModalBody');

    function openModal() {
        modal.classList.remove('hidden');
        // Trigger animation after removing hidden class
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal() {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); // Wait for transition to finish
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const refType = this.getAttribute('data-ref-type');
                const refId = this.getAttribute('data-ref-id');
                const id = this.getAttribute('data-id');

                openModal();
                
                modalBody.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-24">
                        <div class="w-10 h-10 border-4 border-gray-200 border-t-gray-900 dark:border-white/10 dark:border-t-white rounded-full animate-spin"></div>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide">Đang tải dữ liệu...</p>
                    </div>`;

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
                        modalBody.innerHTML = '<div class="text-rose-500 text-center py-10 font-medium flex flex-col items-center gap-2"><i class="bi bi-exclamation-circle text-2xl"></i>Có lỗi xảy ra khi tải dữ liệu.</div>';
                    });
            });
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    });
</script>
@endpush
