@extends('admin.layouts.admin')

@section('title', 'Quản lý Giao dịch & Đối soát')
@section('page_title', 'Danh sách Giao dịch & Đối soát')

@section('content')
<div class="space-y-4" x-data="{
    showModal: false,
    selectedTx: null,
    formAction: '',
    reconcileStatus: 'success',
    reconcileNote: '',
    openReconcile(tx) {
        this.selectedTx = tx;
        this.reconcileStatus = tx.status || 'success';
        this.reconcileNote = tx.note || '';
        this.formAction = '/admin/transactions/' + tx.id;
        this.showModal = true;
    }
}">

    <!-- Top Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tổng đã thu</p>
                <h4 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($stats['total_amount']) }}đ</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Thành công</p>
                <h4 class="text-xl font-bold text-slate-800 dark:text-white mt-1">{{ number_format($stats['success_count']) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">check_circle</span>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Chờ thu tiền / COD</p>
                <h4 class="text-xl font-bold text-amber-500 mt-1">{{ number_format($stats['pending_count']) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">hourglass_top</span>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Thất bại / Hủy</p>
                <h4 class="text-xl font-bold text-rose-500 mt-1">{{ number_format($stats['failed_count']) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">cancel</span>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Tìm kiếm</label>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã GD, Đơn, Tên, SĐT..." class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Cổng thanh toán</label>
                <select name="payment_method" class="w-full py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-primary-500">
                    <option value="all">Tất cả phương thức</option>
                    <option value="cod" {{ request('payment_method') == 'cod' ? 'selected' : '' }}>COD (Tiền mặt)</option>
                    <option value="payos" {{ request('payment_method') == 'payos' ? 'selected' : '' }}>PayOS (Ngân hàng)</option>
                    <option value="momo" {{ request('payment_method') == 'momo' ? 'selected' : '' }}>MoMo (Ví điện tử)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Trạng thái</label>
                <select name="status" class="w-full py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-primary-500">
                    <option value="all">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Thành công</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Thất bại / Hủy</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Từ ngày - Đến ngày</label>
                <div class="flex items-center gap-1">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-1/2 py-1.5 px-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-1/2 py-1.5 px-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-1.5 px-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-1 shadow-sm cursor-pointer">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                <a href="{{ route('admin.transactions.index') }}" class="py-1.5 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-medium transition-colors" title="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </x-admin.card>

    <!-- Fixed Table Layout Data Card -->
    <x-admin.card noPadding="true" class="overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col h-[calc(100vh-22rem)] min-h-[420px]">
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 sticky top-0 z-10 border-b border-slate-200 dark:border-slate-700 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Mã GD / Đơn hàng</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Khách hàng</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Cổng thanh toán</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Số tiền</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Trạng thái Đơn & Giao dịch</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">Thời gian & Đối soát</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3">
                            <span class="font-mono font-medium text-slate-800 dark:text-slate-200 block truncate max-w-[160px]">{{ $tx->transaction_id ?? '-' }}</span>
                            @if($tx->order)
                            <a href="{{ route('admin.orders.show', $tx->order_id) }}" class="text-primary-600 dark:text-primary-400 hover:underline font-semibold text-[11px] inline-flex items-center gap-1 mt-0.5">
                                <i class="bi bi-receipt"></i> #ORD-{{ $tx->order_id }}
                            </a>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-800 dark:text-white block">{{ $tx->order->customer_name ?? 'Khách lẻ' }}</span>
                            <span class="text-slate-500 text-[11px] block">{{ $tx->order->customer_phone ?? '-' }}</span>
                        </td>

                        <td class="px-4 py-3">
                            @if($tx->payment_method === 'payos')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    <i class="bi bi-bank"></i> PayOS (QR)
                                </span>
                            @elseif($tx->payment_method === 'momo')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                                    <i class="bi bi-wallet2"></i> MoMo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                    <i class="bi bi-cash"></i> Tiền mặt (COD)
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                            {{ number_format($tx->amount) }}đ
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-2 items-start">
                                @if($tx->order)
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                                            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                            'ready_to_pick' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
                                            'shipping' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                                            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Chờ xác nhận',
                                            'processing' => 'Đã xác nhận',
                                            'ready_to_pick' => 'Chờ lấy hàng',
                                            'shipping' => 'Đang giao hàng',
                                            'completed' => 'Đã giao',
                                            'cancelled' => 'Đã hủy',
                                        ];
                                        $badgeClass = $statusColors[$tx->order->status] ?? 'bg-slate-100 text-slate-800';
                                        $label = $statusLabels[$tx->order->status] ?? ucfirst($tx->order->status);
                                    @endphp
                                    <div class="flex items-center gap-1.5" title="Trạng thái đơn hàng">
                                        <i class="bi bi-box-seam text-slate-400"></i>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $badgeClass }}">
                                            {{ $label }}
                                        </span>
                                    </div>
                                @endif
                                
                                <div class="flex items-center gap-1.5" title="Trạng thái đối soát / giao dịch">
                                    <i class="bi bi-cash-coin text-slate-400"></i>
                                    @if($tx->status === 'success')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                            <span class="material-symbols-outlined text-[15px] leading-none text-emerald-600">check_circle</span> Thành công
                                        </span>
                                    @elseif($tx->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                            <span class="material-symbols-outlined text-[15px] leading-none text-amber-500">hourglass_top</span> Chờ thanh toán
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                                            <span class="material-symbols-outlined text-[15px] leading-none text-rose-600">cancel</span> Thất bại / Hủy
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-slate-500 text-[11px]">
                            <div>{{ $tx->created_at->format('d/m/Y H:i') }}</div>
                            @if($tx->reconciled_at)
                                <div class="text-emerald-600 dark:text-emerald-400 mt-0.5 inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">verified_user</span> {{ $tx->reconciledBy->name ?? 'Admin' }} ({{ $tx->reconciled_at->format('d/m H:i') }})
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <button type="button" @click="openReconcile({{ json_encode($tx) }})" class="px-2.5 py-1 text-xs font-medium rounded-lg text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors cursor-pointer" title="Đối soát / Cập nhật">
                                    <i class="bi bi-pencil-square mr-1"></i> Đối soát
                                </button>
                                @if($tx->order_id)
                                <a href="{{ route('admin.orders.show', $tx->order_id) }}" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" title="Xem chi tiết đơn hàng">
                                    <i class="bi bi-eye text-base"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            <i class="bi bi-inbox text-3xl mb-2 text-slate-400 block"></i>
                            Không tìm thấy giao dịch nào phù hợp với điều kiện lọc.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0">
            {{ $transactions->links('pagination::tailwind') }}
        </div>
        @endif
    </x-admin.card>

    <!-- Reconciliation Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 text-left shadow-2xl border border-slate-100 dark:border-slate-700 transform transition-all">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-600 text-xl">fact_check</span> Đối soát thanh toán
                    </h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="form-confirm"
                      data-confirm-title="Xác nhận đối soát?"
                      data-confirm-text="Trạng thái giao dịch và đơn hàng liên kết sẽ được cập nhật đồng bộ. Bạn có chắc chắn không?"
                      data-confirm-icon="question">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4 p-3.5 bg-slate-50 dark:bg-slate-900 rounded-xl space-y-1.5 text-xs border border-slate-100 dark:border-slate-800">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Mã giao dịch:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedTx?.transaction_id"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Đơn hàng:</span>
                            <span class="font-semibold text-primary-600" x-text="'#ORD-' + selectedTx?.order_id"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Số tiền:</span>
                            <span class="font-bold text-emerald-600 text-sm" x-text="new Intl.NumberFormat('vi-VN').format(selectedTx?.amount || 0) + 'đ'"></span>
                        </div>
                    </div>

                    <!-- Interactive Status Selector (Google Material Symbols) -->
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">
                            Trạng thái thanh toán mới <span class="text-rose-500">*</span>
                        </label>
                        <input type="hidden" name="status" :value="reconcileStatus">

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <!-- Option Success -->
                            <button type="button"
                                    @click="reconcileStatus = 'success'"
                                    :class="reconcileStatus === 'success'
                                        ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20 text-emerald-900 dark:text-emerald-200 shadow-sm'
                                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-600'"
                                    class="flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer group">
                                <span class="material-symbols-outlined text-2xl mb-1.5 transition-transform group-hover:scale-110"
                                      :class="reconcileStatus === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'">
                                    check_circle
                                </span>
                                <span class="text-xs font-bold leading-tight">Thành công</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Đã nhận tiền</span>
                            </button>

                            <!-- Option Pending -->
                            <button type="button"
                                    @click="reconcileStatus = 'pending'"
                                    :class="reconcileStatus === 'pending'
                                        ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 ring-2 ring-amber-500/20 text-amber-900 dark:text-amber-200 shadow-sm'
                                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-600'"
                                    class="flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer group">
                                <span class="material-symbols-outlined text-2xl mb-1.5 transition-transform group-hover:scale-110"
                                      :class="reconcileStatus === 'pending' ? 'text-amber-500 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500'">
                                    hourglass_top
                                </span>
                                <span class="text-xs font-bold leading-tight">Chờ thanh toán</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Chờ thu COD</span>
                            </button>

                            <!-- Option Failed -->
                            <button type="button"
                                    @click="reconcileStatus = 'failed'"
                                    :class="reconcileStatus === 'failed'
                                        ? 'border-rose-500 bg-rose-50/70 dark:bg-rose-950/40 ring-2 ring-rose-500/20 text-rose-900 dark:text-rose-200 shadow-sm'
                                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-600'"
                                    class="flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer group">
                                <span class="material-symbols-outlined text-2xl mb-1.5 transition-transform group-hover:scale-110"
                                      :class="reconcileStatus === 'failed' ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500'">
                                    cancel
                                </span>
                                <span class="text-xs font-bold leading-tight">Thất bại / Hủy</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Không nhận tiền</span>
                            </button>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                            Ghi chú / Mã tham chiếu đối soát
                        </label>
                        <textarea name="note" rows="3" x-model="reconcileNote" class="w-full text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white p-2.5 focus:ring-primary-500" placeholder="Ví dụ: Bưu tá Nguyễn Văn A đã nộp tiền COD ngày 24/09..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 rounded-lg transition-colors cursor-pointer">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-sm cursor-pointer inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">check</span> Lưu đối soát
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
