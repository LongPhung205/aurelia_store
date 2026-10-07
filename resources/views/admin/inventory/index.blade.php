@extends('admin.layouts.admin')

@section('title', 'Tồn kho tổng quan')



@section('content')
<div class="px-4 md:px-8 py-4 w-full h-[calc(100vh-64px)] flex flex-col bg-gray-50 dark:bg-[#050505]">
    
    <!-- The Main App Container -->
    <div class="flex-1 flex flex-col min-h-0 bg-white dark:bg-[#0a0a0a] rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-none ring-1 ring-black/5 dark:ring-white/10 overflow-hidden relative">
        
        <!-- Advanced Filters Toolbar -->
        <div class="p-3 md:p-4 border-b border-gray-100 dark:border-white/5 shrink-0 bg-white/60 dark:bg-[#0a0a0a]/60 backdrop-blur-2xl z-30">
            
            <form action="{{ route('admin.inventory.index') }}" method="GET" class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5">
                
                <div class="flex flex-wrap items-center gap-4 w-full xl:w-auto">
                    <!-- Category Select -->
                    <div class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="bi bi-diagram-3 text-gray-400"></i>
                        </div>
                        <select name="category_id" class="bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-2 focus:ring-gray-900 block w-full pl-10 p-2.5 dark:bg-white/5 dark:ring-white/10 dark:text-white dark:focus:ring-white transition-all appearance-none cursor-pointer">
                            <option value="">Tất cả danh mục</option>
                            @php
                                $printCategory = function($category, $prefix = '') use (&$printCategory) {
                                    $selected = request('category_id') == $category->id ? 'selected' : '';
                                    echo "<option value='{$category->id}' {$selected}>{$prefix} {$category->name}</option>";
                                    if ($category->children) {
                                        foreach ($category->children as $child) {
                                            $printCategory($child, $prefix . '— ');
                                        }
                                    }
                                };
                            @endphp
                            @foreach($categories as $category)
                                @php $printCategory($category); @endphp
                            @endforeach
                        </select>
                    </div>

                    <!-- Low Stock Toggle -->
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="sr-only peer" onchange="this.form.submit()">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-500"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-red-500 transition-colors">Sắp hết hàng</span>
                    </label>
                </div>
                
                <div class="flex items-center gap-2 w-full xl:w-auto">
                    <button type="submit" class="px-5 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl font-medium text-sm hover:opacity-90 active:scale-95 transition-all shrink-0">
                        Tìm kiếm
                    </button>
                    
                    @if(request()->anyFilled(['category_id', 'low_stock']))
                        <a href="{{ route('admin.inventory.index') }}" class="p-2.5 text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 rounded-xl transition-all dark:bg-white/5 dark:hover:bg-rose-500/10 shrink-0 group">
                            <i class="bi bi-x-lg block group-hover:rotate-90 transition-transform"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
        
        <!-- Table Wrapper: Floating Rows Design -->
        <div class="overflow-auto flex-1 relative bg-gray-50/50 dark:bg-[#050505]/50 px-4 pb-2">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400" style="border-collapse: separate; border-spacing: 0 8px;">
                <thead class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky top-0 z-20 backdrop-blur-md bg-gray-50/90 dark:bg-[#050505]/90">
                    <tr>
                        <th scope="col" class="px-5 py-3 rounded-l-2xl">Sản phẩm</th>
                        <th scope="col" class="px-5 py-3">Danh mục</th>
                        <th scope="col" class="px-5 py-3 text-center">Tổng tồn kho</th>
                        <th scope="col" class="px-5 py-3 text-center">Trạng thái</th>
                        <th scope="col" class="px-5 py-3 text-right rounded-r-2xl">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $isLowStock = $product->low_stock_count > 0;
                        @endphp
                        <tr class="bg-white dark:bg-[#111] shadow-[0_2px_10px_rgba(0,0,0,0.02)] dark:shadow-none hover:shadow-lg dark:hover:bg-white/[0.05] ring-1 {{ $isLowStock ? 'ring-red-500/30 dark:ring-red-500/20 bg-red-50/10 dark:bg-red-900/10' : 'ring-black/5 dark:ring-white/5' }} transition-all duration-300 group">
                            <td class="px-5 py-3 rounded-l-2xl align-middle">
                                <div class="flex items-center gap-4">
                                    @php
                                        $thumbnail = null;
                                        if ($product->images && $product->images->count() > 0) {
                                            $thumbnail = $product->images->first()->image_url;
                                        } else {
                                            $variantWithThumb = $product->variants->whereNotNull('thumbnail_url')->first();
                                            if ($variantWithThumb) {
                                                $thumbnail = $variantWithThumb->thumbnail_url;
                                            }
                                        }
                                        if (!$thumbnail) {
                                            $thumbnail = 'https://ui-avatars.com/api/?name=' . urlencode($product->name ?? 'NA') . '&background=F3F4F6&color=6B7280';
                                        }
                                    @endphp
                                    <div class="relative w-14 h-14 shrink-0 rounded-xl overflow-hidden ring-1 ring-black/5 dark:ring-white/10 group-hover:shadow-md transition-shadow">
                                        @if($thumbnail)
                                            <img class="w-full h-full object-cover" src="{{ filter_var($thumbnail, FILTER_VALIDATE_URL) ? $thumbnail : asset('storage/'.$thumbnail) }}" alt="{{ $product->name }}">
                                        @else
                                            <div class="w-full h-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                                <i class="bi bi-image text-xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white text-base truncate max-w-[300px] hover:text-clip hover:whitespace-normal transition-all" title="{{ $product->name }}">
                                            {{ $product->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium flex items-center gap-1.5">
                                            <i class="bi bi-box-seam"></i> {{ $product->variants_count }} phân loại (biến thể)
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-5 py-3 align-middle">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($product->categories as $category)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300 ring-1 ring-inset ring-gray-200 dark:ring-white/10">
                                            {{ $category->name }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 dark:text-gray-600 text-xs italic font-medium">Chưa phân loại</span>
                                    @endforelse
                                </div>
                            </td>
                            
                            <td class="px-5 py-3 text-center align-middle">
                                <div class="inline-flex items-baseline justify-center font-mono">
                                    <span class="font-medium text-2xl tracking-tighter {{ $isLowStock ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                        {{ $product->variants_sum_stock_quantity ?? 0 }}
                                    </span>
                                </div>
                            </td>
                            
                            <td class="px-5 py-3 text-center align-middle">
                                @if(($product->variants_sum_stock_quantity ?? 0) <= 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Hết hàng
                                    </span>
                                @elseif($isLowStock)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> Sắp hết
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Đủ hàng
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-5 py-3 text-right rounded-r-2xl align-middle">
                                <button type="button" class="view-details-btn group/btn relative inline-flex items-center justify-center p-3 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500 dark:hover:text-white transition-all duration-300" 
                                    data-id="{{ $product->id }}">
                                    <span class="hidden xl:inline-block mr-2 text-sm font-medium">Xem chi tiết</span>
                                    <i class="bi bi-arrow-right text-lg xl:text-base group-hover/btn:translate-x-0.5 transition-transform"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-16 text-center">
                                <div class="inline-flex flex-col items-center justify-center text-gray-400 dark:text-gray-600">
                                    <div class="w-24 h-24 mb-6 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center ring-4 ring-gray-50 dark:ring-[#0a0a0a]">
                                        <i class="bi bi-box-seam text-4xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Chưa có sản phẩm</h3>
                                    <p class="text-sm max-w-sm">Không tìm thấy sản phẩm nào phù hợp với bộ lọc hiện tại.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5 bg-white dark:bg-[#0a0a0a] z-30">
                <!-- Customized Pagination -->
                <div class="flex items-center justify-between">
                    <div class="hidden md:block">
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                            Hiển thị <span class="font-medium text-gray-900 dark:text-white">{{ $products->firstItem() }}</span> đến <span class="font-medium text-gray-900 dark:text-white">{{ $products->lastItem() }}</span> trong <span class="font-medium text-gray-900 dark:text-white">{{ $products->total() }}</span> sản phẩm
                        </p>
                    </div>
                    <div>
                        {{ $products->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Premium Glass Modal for details -->
<div id="detailsModal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
    
    <!-- Backdrop with heavy blur -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('detailsModal').classList.add('hidden'); document.getElementById('modalContent').classList.remove('scale-100', 'opacity-100');"></div>
    
    <div class="relative w-full max-w-4xl mx-auto my-6 z-50 p-4">
        <!-- Modal content -->
        <div class="relative bg-white dark:bg-[#0a0a0a] rounded-[2rem] shadow-2xl flex flex-col max-h-[85vh] ring-1 ring-black/5 dark:ring-white/10 overflow-hidden scale-95 opacity-0 transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] transform" id="modalContent">
            
            <!-- Modal header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02]">
                <h3 class="text-xl font-medium tracking-tight text-gray-900 dark:text-white flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <i class="bi bi-box-seam text-sm"></i>
                    </div>
                    Chi tiết tồn kho phân loại
                </h3>
                <button type="button" class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-900 dark:bg-white/5 dark:hover:bg-white/10 dark:hover:text-white flex justify-center items-center transition-colors" onclick="document.getElementById('detailsModal').classList.add('hidden'); document.getElementById('modalContent').classList.remove('scale-100', 'opacity-100');">
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
                <button type="button" class="px-6 py-2.5 rounded-full text-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:ring-4 focus:ring-gray-100 dark:bg-white/5 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white dark:focus:ring-white/10 transition-all duration-300" onclick="document.getElementById('detailsModal').classList.add('hidden'); document.getElementById('modalContent').classList.remove('scale-100', 'opacity-100');">
                    Đóng cửa sổ
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom Scrollbar for Modal Body */
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('detailsModal');
        const modalContent = document.getElementById('modalContent');
        const modalBody = document.getElementById('detailsModalBody');

        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');

                openModal();
                modalBody.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-24">
                        <div class="w-10 h-10 border-4 border-gray-200 border-t-gray-900 dark:border-white/10 dark:border-t-white rounded-full animate-spin"></div>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide">Đang tải dữ liệu...</p>
                    </div>`;

                let url = '{{ route("admin.inventory.details", ":id") }}'.replace(':id', id);

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
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        });
    });
</script>
@endpush
