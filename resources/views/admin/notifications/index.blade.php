@extends('admin.layouts.admin')

@section('title', 'Tất cả thông báo')

@section('content')
<div class="px-4 py-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-slate-900 dark:text-white">Tất cả thông báo</h1>
            <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">Quản lý và xem lại tất cả các thông báo từ hệ thống.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <form action="{{ route('admin.notifications.mark_all_read') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:w-auto transition-colors">
                    <i class="bi bi-check2-all mr-2"></i>Đánh dấu tất cả đã đọc
                </button>
            </form>
        </div>
    </div>

    <div class="mt-8 bg-white dark:bg-slate-800 shadow-sm ring-1 ring-slate-900/5 sm:rounded-lg">
        <div class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $data = $notification->data;
                    $type = $data['type'] ?? 'default';
                    
                    $iconClass = 'bi-bell';
                    $bgClass = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400';
                    
                    if ($type === 'new_order') {
                        $iconClass = 'bi-cart-check';
                        $bgClass = 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400';
                    } elseif ($type === 'new_message') {
                        $iconClass = 'bi-chat-dots';
                        $bgClass = 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400';
                    } elseif ($type === 'low_stock') {
                        $iconClass = 'bi-exclamation-triangle';
                        $bgClass = 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400';
                    }
                @endphp
                <div class="flex items-center px-4 py-4 sm:px-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors {{ $isUnread ? 'bg-primary-50/20 dark:bg-primary-900/10' : '' }}">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full {{ $bgClass }} flex items-center justify-center text-xl shadow-sm">
                            <i class="bi {{ $iconClass }}"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 px-4">
                        <p class="text-base font-medium text-slate-900 dark:text-white {{ $isUnread ? 'font-bold' : '' }}">
                            <a href="{{ $data['url'] ?? '#' }}" onclick="markNotificationAsRead('{{ $notification->id }}', this.parentElement.parentElement.parentElement)" class="hover:underline">
                                {{ $data['message'] ?? 'Thông báo mới' }}
                            </a>
                        </p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 flex items-center gap-4">
                            <span><i class="bi bi-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}</span>
                            <span class="text-xs">{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                        </p>
                    </div>
                    <div class="flex-shrink-0 flex items-center gap-3">
                        @if($isUnread)
                            <span class="inline-flex items-center rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-medium text-primary-800 dark:bg-primary-900/50 dark:text-primary-300">Mới</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-700 dark:text-slate-300">Đã đọc</span>
                        @endif
                        <a href="{{ $data['url'] ?? '#' }}" onclick="markNotificationAsRead('{{ $notification->id }}', this.parentElement.parentElement)" class="text-slate-400 hover:text-primary-600 dark:hover:text-primary-400">
                            <i class="bi bi-chevron-right text-lg"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center text-slate-500 dark:text-slate-400 flex flex-col items-center">
                    <i class="bi bi-bell-slash text-5xl mb-4 text-slate-300 dark:text-slate-600"></i>
                    <p class="text-lg">Không có thông báo nào</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
