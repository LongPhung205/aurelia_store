@extends('admin.layouts.admin')

@section('title', 'Phiếu Nhập Kho')

@section('content')
<div class="px-0 w-full h-[calc(100vh-90px)] flex flex-col">
    <div class="flex justify-between items-center mb-6 shrink-0">
        <div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Quản Lý Nhập Kho</h4>
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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Phiếu nhập kho</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 shrink-0">
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center">
                <div class="inline-flex flex-shrink-0 justify-center items-center w-12 h-12 text-blue-600 bg-blue-100 rounded-lg dark:bg-blue-900 dark:text-blue-300">
                    <i class="bi bi-file-earmark-text text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng Phiếu Nhập</p>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_imports']) }}</h3>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center">
                <div class="inline-flex flex-shrink-0 justify-center items-center w-12 h-12 text-emerald-600 bg-emerald-100 rounded-lg dark:bg-emerald-900 dark:text-emerald-300">
                    <i class="bi bi-cash-stack text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Chi Phí Nhập Kho (Tháng này)</p>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['monthly_cost']) }} đ</h3>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center">
                <div class="inline-flex flex-shrink-0 justify-center items-center w-12 h-12 text-purple-600 bg-purple-100 rounded-lg dark:bg-purple-900 dark:text-purple-300">
                    <i class="bi bi-buildings text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nhà Cung Cấp Đang GD</p>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['active_suppliers']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <x-admin.card class="flex-1 flex flex-col min-h-0 border-0 shadow-sm" bodyClass="flex-1 flex flex-col min-h-0" noPadding="true">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0 flex justify-between items-center bg-white dark:bg-gray-800 rounded-t-lg">
            <div class="flex items-center gap-3">
                <h5 class="mb-0 font-semibold text-blue-600 dark:text-blue-400"><i class="bi bi-box-arrow-in-down mr-2"></i>Tất cả phiếu nhập</h5>
                <button type="button" onclick="document.getElementById('filterForm').classList.toggle('hidden')" class="text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-md font-medium border border-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600 dark:text-gray-300 transition-colors">
                    <i class="bi bi-funnel"></i> Bộ lọc nâng cao
                </button>
            </div>
            <x-admin.button tag="a" href="{{ route('admin.imports.create') }}" variant="primary" size="sm" icon="bi bi-plus-circle">
                Tạo Phiếu Nhập
            </x-admin.button>
        </div>
        
        <!-- Filter Form -->
        <div id="filterForm" class="{{ request()->anyFilled(['search', 'supplier_id', 'status', 'from_date', 'to_date']) ? '' : 'hidden' }} p-5 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 shrink-0">
            <form action="{{ route('admin.imports.index') }}" method="GET" class="flex flex-col gap-4">
                <!-- Hàng 1: Tìm kiếm & NCC -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Search Box -->
                    <div>
                        <label for="search" class="block mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Tìm kiếm</label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <i class="bi bi-search text-gray-400"></i>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors" placeholder="Mã phiếu, tên nhà cung cấp, SĐT, người tạo...">
                        </div>
                    </div>
                    <!-- Supplier Dropdown -->
                    <div>
                        <label for="supplier_id" class="block mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Nhà cung cấp</label>
                        <select name="supplier_id" id="supplier_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors">
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5 items-end">
                    <!-- Status Dropdown -->
                    <div>
                        <label for="status" class="block mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Trạng thái</label>
                        <select name="status" id="status" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Nháp</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                    </div>
                    <!-- From Date -->
                    <div>
                        <label for="from_date" class="block mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Từ ngày</label>
                        <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors">
                    </div>
                    <!-- To Date -->
                    <div>
                        <label for="to_date" class="block mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Đến ngày</label>
                        <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors">
                    </div>
                    <!-- Buttons -->
                    <div class="flex gap-2 h-[42px]">
                        <button type="submit" class="flex-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-md text-sm px-4 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-colors flex items-center justify-center">
                            <i class="bi bi-funnel mr-1"></i> Lọc
                        </button>
                        <a href="{{ route('admin.imports.index') }}" class="flex-1 text-center text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:text-blue-700 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-md text-sm px-4 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700 transition-colors flex items-center justify-center">
                            Xóa
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="overflow-auto flex-1 relative">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-800 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.1)]">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-bold tracking-wide">Mã Phiếu</th>
                        <th scope="col" class="px-6 py-3 font-bold tracking-wide">Nhà cung cấp</th>
                        <th scope="col" class="px-6 py-3 font-bold tracking-wide">Nhân viên tạo</th>
                        <th scope="col" class="px-6 py-3 font-bold tracking-wide text-right">Tổng tiền</th>
                        <th scope="col" class="px-6 py-3 font-bold tracking-wide text-center">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 font-bold tracking-wide text-right">Ngày nhập</th>
                        <th scope="col" class="px-6 py-3 font-bold tracking-wide text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-600 transition-colors duration-150">
                            <td class="px-6 py-4 font-mono font-bold text-blue-600 dark:text-blue-400">
                                {{ $import->code }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $import->supplier->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $import->user->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono">
                                {{ number_format($import->total_amount) }} đ
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($import->status === 'completed')
                                    <span class="bg-green-100 text-green-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800">Đã nhập kho</span>
                                @elseif($import->status === 'cancelled')
                                    <span class="bg-red-100 text-red-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800">Đã hủy</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800">Nháp</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                {{ $import->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" onclick="openDetailsModal('{{ route('admin.imports.show', $import->id) }}')" class="inline-flex items-center justify-center w-8 h-8 text-blue-600 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 hover:text-blue-700 focus:ring-4 focus:ring-blue-50 dark:bg-gray-700 dark:text-blue-400 dark:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:ring-gray-700 transition-colors" title="Chi tiết">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Không có phiếu nhập nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($imports->hasPages())
            <x-slot name="footer">
                <div class="mt-2 flex justify-end w-full">
                    {{ $imports->links('pagination::tailwind') }}
                </div>
            </x-slot>
        @endif
    </x-admin.card>
</div>

<!-- Details Modal -->
<div id="detailsModal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden flex items-center justify-center w-full h-full bg-black/50 backdrop-blur-sm overflow-x-hidden overflow-y-auto p-4">
    <div class="relative w-full max-w-5xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 flex flex-col max-h-[90vh]">
            <!-- Modal body -->
            <div class="p-6 overflow-y-auto bg-gray-50 dark:bg-gray-900 rounded-lg" id="detailsModalBody">
                <div class="flex justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openDetailsModal(url) {
        const modal = document.getElementById('detailsModal');
        const modalBody = document.getElementById('detailsModalBody');
        
        modal.classList.remove('hidden');
        modalBody.innerHTML = `
            <div class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div>
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
            modalBody.innerHTML = '<div class="text-center text-red-500 py-4">Có lỗi xảy ra khi tải dữ liệu.</div>';
            console.error('Error fetching details:', error);
        });
    }

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
