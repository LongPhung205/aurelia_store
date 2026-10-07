@extends('admin.layouts.admin')

@section('title', 'Phiếu Nhập Kho')



@section('content')
<div class="px-4 md:px-8 py-4 w-full h-[calc(100vh-64px)] flex flex-col bg-gray-50 dark:bg-[#050505]">
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4 shrink-0">
        <div class="bg-white rounded-2xl p-4 shadow-[0_2px_10px_rgba(0,0,0,0.02)] ring-1 ring-black/5 dark:bg-[#111] dark:ring-white/5 dark:shadow-none flex items-center">
            <div class="inline-flex flex-shrink-0 justify-center items-center w-12 h-12 text-blue-600 bg-blue-50 rounded-xl dark:bg-blue-500/10 dark:text-blue-400">
                <i class="bi bi-file-earmark-text text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tổng Phiếu Nhập</p>
                <h3 class="text-xl font-medium text-gray-900 dark:text-white tracking-tight">{{ number_format($stats['total_imports']) }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-[0_2px_10px_rgba(0,0,0,0.02)] ring-1 ring-black/5 dark:bg-[#111] dark:ring-white/5 dark:shadow-none flex items-center">
            <div class="inline-flex flex-shrink-0 justify-center items-center w-12 h-12 text-emerald-600 bg-emerald-50 rounded-xl dark:bg-emerald-500/10 dark:text-emerald-400">
                <i class="bi bi-cash-stack text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Chi Phí Nhập Kho (Tháng này)</p>
                <h3 class="text-xl font-medium text-gray-900 dark:text-white tracking-tight">{{ number_format($stats['monthly_cost']) }} đ</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-[0_2px_10px_rgba(0,0,0,0.02)] ring-1 ring-black/5 dark:bg-[#111] dark:ring-white/5 dark:shadow-none flex items-center">
            <div class="inline-flex flex-shrink-0 justify-center items-center w-12 h-12 text-purple-600 bg-purple-50 rounded-xl dark:bg-purple-500/10 dark:text-purple-400">
                <i class="bi bi-buildings text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Nhà Cung Cấp Đang GD</p>
                <h3 class="text-xl font-medium text-gray-900 dark:text-white tracking-tight">{{ number_format($stats['active_suppliers']) }}</h3>
            </div>
        </div>
    </div>

    <!-- The Main App Container -->
    <div class="flex-1 flex flex-col min-h-0 bg-white dark:bg-[#0a0a0a] rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-none ring-1 ring-black/5 dark:ring-white/10 overflow-hidden relative">
        <div class="p-3 md:p-4 border-b border-gray-100 dark:border-white/5 shrink-0 flex flex-wrap justify-between items-center bg-white/60 dark:bg-[#0a0a0a]/60 backdrop-blur-2xl z-30">
            <div class="flex items-center gap-3">
                <button type="button" onclick="document.getElementById('filterForm').classList.toggle('hidden')" class="text-xs font-medium px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-xl dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 transition-colors flex items-center gap-2 ring-1 ring-black/5 dark:ring-white/10">
                    <i class="bi bi-funnel"></i> Lọc nâng cao
                </button>
            </div>
            <a href="{{ route('admin.imports.create') }}" class="px-5 py-2.5 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 rounded-xl font-medium text-sm hover:opacity-90 active:scale-95 transition-all shrink-0 flex items-center gap-2">
                <i class="bi bi-plus-lg"></i> Tạo Phiếu Nhập
            </a>
        </div>
        
        <!-- Filter Form -->
        <div id="filterForm" class="{{ request()->anyFilled(['search', 'supplier_id', 'status', 'from_date', 'to_date']) ? '' : 'hidden' }} p-4 bg-gray-50/50 dark:bg-white/[0.02] border-b border-gray-100 dark:border-white/5 shrink-0 z-20 relative">
            <form action="{{ route('admin.imports.index') }}" method="GET" class="flex flex-col gap-3">
                <!-- Hàng 1: Tìm kiếm & NCC -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <i class="bi bi-search text-gray-400"></i>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" class="bg-white border-0 ring-1 ring-inset ring-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-2 focus:ring-gray-900 block w-full ps-10 p-2.5 dark:bg-[#111] dark:ring-white/10 dark:text-white dark:focus:ring-white transition-all placeholder-gray-400" placeholder="Mã phiếu, tên NCC, SĐT...">
                        </div>
                    </div>
                    <div>
                        <select name="supplier_id" id="supplier_id" class="bg-white border-0 ring-1 ring-inset ring-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-2 focus:ring-gray-900 block w-full p-2.5 dark:bg-[#111] dark:ring-white/10 dark:text-white dark:focus:ring-white transition-all cursor-pointer">
                            <option value="">-- Tất cả Nhà cung cấp --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Hàng 2: Trạng thái & Khoảng thời gian & Nút -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <select name="status" id="status" class="bg-white border-0 ring-1 ring-inset ring-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-2 focus:ring-gray-900 block w-full p-2.5 dark:bg-[#111] dark:ring-white/10 dark:text-white dark:focus:ring-white transition-all cursor-pointer">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Nháp</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-[10px] font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Từ ngày</label>
                        <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}" class="bg-white border-0 ring-1 ring-inset ring-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-2 focus:ring-gray-900 block w-full p-2.5 dark:bg-[#111] dark:ring-white/10 dark:text-white dark:focus:ring-white transition-all">
                    </div>
                    <div>
                        <label class="block mb-1 text-[10px] font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Đến ngày</label>
                        <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}" class="bg-white border-0 ring-1 ring-inset ring-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-2 focus:ring-gray-900 block w-full p-2.5 dark:bg-[#111] dark:ring-white/10 dark:text-white dark:focus:ring-white transition-all">
                    </div>
                    <div class="flex gap-2 h-[42px]">
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 rounded-xl font-medium text-sm hover:opacity-90 active:scale-95 transition-all">
                            Lọc
                        </button>
                        <a href="{{ route('admin.imports.index') }}" class="flex-1 px-4 py-2.5 bg-white text-gray-700 hover:bg-gray-50 hover:text-rose-600 ring-1 ring-inset ring-gray-200 rounded-xl font-medium text-sm text-center dark:bg-transparent dark:ring-white/10 dark:text-gray-300 dark:hover:bg-rose-500/10 dark:hover:text-rose-400 dark:hover:ring-rose-500/20 transition-all">
                            Xóa
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="overflow-auto flex-1 relative bg-gray-50/50 dark:bg-[#050505]/50 px-4 pb-2">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400" style="border-collapse: separate; border-spacing: 0 8px;">
                <thead class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky top-0 z-10 backdrop-blur-md bg-gray-50/90 dark:bg-[#050505]/90">
                    <tr>
                        <th scope="col" class="px-5 py-3 rounded-l-2xl">Mã Phiếu</th>
                        <th scope="col" class="px-5 py-3">Nhà cung cấp</th>
                        <th scope="col" class="px-5 py-3">Nhân sự</th>
                        <th scope="col" class="px-5 py-3 text-right">Tổng tiền</th>
                        <th scope="col" class="px-5 py-3 text-center">Trạng thái</th>
                        <th scope="col" class="px-5 py-3 text-right">Ngày nhập</th>
                        <th scope="col" class="px-5 py-3 text-right rounded-r-2xl">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                        <tr class="bg-white dark:bg-[#111] shadow-[0_2px_10px_rgba(0,0,0,0.02)] dark:shadow-none hover:shadow-lg dark:hover:bg-white/[0.05] ring-1 ring-black/5 dark:ring-white/5 transition-all duration-300 group">
                            <td class="px-5 py-3 font-mono font-medium text-blue-600 dark:text-blue-400 rounded-l-2xl align-middle">
                                {{ $import->code }}
                            </td>
                            <td class="px-5 py-3 align-middle">
                                <div class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $import->supplier->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-3 align-middle">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-700 dark:text-gray-300 font-medium tracking-wider ring-1 ring-black/5 dark:ring-white/10 text-xs">
                                        {{ substr($import->user->name ?? 'N', 0, 1) }}
                                    </div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100 text-xs">{{ $import->user->name ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right align-middle">
                                <div class="font-medium text-gray-900 dark:text-white font-mono tracking-tight">{{ number_format($import->total_amount) }} <span class="text-gray-400">đ</span></div>
                            </td>
                            <td class="px-5 py-3 text-center align-middle">
                                @if($import->status === 'completed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-medium bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 uppercase tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Đã nhập kho
                                    </span>
                                @elseif($import->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-medium bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 uppercase tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Đã hủy
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-medium bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 uppercase tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Nháp
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right align-middle">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $import->created_at->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-gray-400">{{ $import->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-5 py-3 text-right rounded-r-2xl align-middle">
                                <button type="button" onclick="openDetailsModal('{{ route('admin.imports.show', $import->id) }}')" class="group/btn relative inline-flex items-center justify-center p-2.5 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500 dark:hover:text-white transition-all duration-300" title="Chi tiết">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="inline-flex flex-col items-center justify-center text-gray-400 dark:text-gray-600">
                                    <div class="w-20 h-20 mb-4 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center ring-4 ring-gray-50 dark:ring-[#0a0a0a]">
                                        <i class="bi bi-file-earmark-x text-3xl"></i>
                                    </div>
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Chưa có phiếu nhập</h3>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($imports->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5 bg-white dark:bg-[#0a0a0a] z-30">
                <div class="flex items-center justify-between">
                    <div class="hidden md:block">
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                            Hiển thị <span class="font-medium text-gray-900 dark:text-white">{{ $imports->firstItem() }}</span> đến <span class="font-medium text-gray-900 dark:text-white">{{ $imports->lastItem() }}</span> trong <span class="font-medium text-gray-900 dark:text-white">{{ $imports->total() }}</span> phiếu
                        </p>
                    </div>
                    <div>
                        {{ $imports->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Premium Details Modal -->
<div id="detailsModal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="relative w-full max-w-5xl mx-auto my-6 z-50 p-4">
        <!-- Modal content -->
        <div class="relative bg-white dark:bg-[#0a0a0a] rounded-[2rem] shadow-2xl flex flex-col max-h-[85vh] ring-1 ring-black/5 dark:ring-white/10 overflow-hidden scale-95 opacity-0 transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] transform" id="modalContent">
            
            <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02]">
                <h3 class="text-xl font-medium tracking-tight text-gray-900 dark:text-white flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <i class="bi bi-file-earmark-text text-sm"></i>
                    </div>
                    Chi tiết phiếu nhập kho
                </h3>
                <button type="button" class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-900 dark:bg-white/5 dark:hover:bg-white/10 dark:hover:text-white flex justify-center items-center transition-colors" onclick="closeModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="p-0 overflow-y-auto custom-scrollbar bg-gray-50 dark:bg-[#050505]" id="detailsModalBody">
                <div class="flex flex-col items-center justify-center py-24">
                    <div class="w-10 h-10 border-4 border-gray-200 border-t-gray-900 dark:border-white/10 dark:border-t-white rounded-full animate-spin"></div>
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide">Đang tải dữ liệu...</p>
                </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openDetailsModal(url) {
        const modal = document.getElementById('detailsModal');
        const modalContent = document.getElementById('modalContent');
        const modalBody = document.getElementById('detailsModalBody');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);

        modalBody.innerHTML = `
            <div class="flex flex-col items-center justify-center py-24">
                <div class="w-10 h-10 border-4 border-gray-200 border-t-gray-900 dark:border-white/10 dark:border-t-white rounded-full animate-spin"></div>
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide">Đang tải dữ liệu...</p>
            </div>
        `;
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;
        })
        .catch(error => {
            modalBody.innerHTML = '<div class="text-rose-500 text-center py-10 font-medium flex flex-col items-center gap-2"><i class="bi bi-exclamation-circle text-2xl"></i>Có lỗi xảy ra khi tải dữ liệu.</div>';
            console.error('Error fetching details:', error);
        });
    }

    function closeModal() {
        const modal = document.getElementById('detailsModal');
        const modalContent = document.getElementById('modalContent');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !document.getElementById('detailsModal').classList.contains('hidden')) {
            closeModal();
        }
    });

    // These functions need to be global since they are called from the injected HTML
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

    window.confirmCancel = function() {
        Swal.fire({
            title: 'Hủy phiếu nhập?',
            text: "Bạn có chắc muốn hủy phiếu nhập này không? Thao tác này không thể hoàn tác.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Đồng ý, Hủy',
            cancelButtonText: 'Không'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancelForm').submit();
            }
        });
    };

    window.confirmComplete = function() {
        Swal.fire({
            title: 'Chốt nhập kho?',
            text: "Khi chốt phiếu, số lượng sẽ được cộng vào kho. Bạn có chắc chắn không?",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Chắc chắn',
            cancelButtonText: 'Xem lại'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('completeForm').submit();
            }
        });
    };
</script>
@endpush
