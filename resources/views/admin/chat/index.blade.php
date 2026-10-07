@extends('admin.layouts.admin')

@section('title', 'Hỗ trợ khách hàng')
@section('page_title', 'Hỗ trợ khách hàng')

{{-- Xóa padding mặc định, trang chat tự quản lý layout --}}
@section('content_class', 'sm:ml-[272px] mt-14 overflow-hidden')

@push('styles')
<style>
    /* Scrollbar mịn */
    .chat-scroll::-webkit-scrollbar { width: 5px; }
    .chat-scroll::-webkit-scrollbar-track { background: transparent; }
    .chat-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .chat-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Bubble tin nhắn admin (bên phải – màu tím gradient) */
    .bubble-admin {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #fff;
        border-radius: 18px 18px 4px 18px;
    }
    /* Bubble tin nhắn khách (bên trái – xám nhạt) */
    .bubble-customer {
        background: #f1f5f9;
        color: #1e293b;
        border-radius: 18px 18px 18px 4px;
    }
    .dark .bubble-customer {
        background: #1e293b;
        color: #e2e8f0;
    }

    /* Conversation item active */
    .conv-item.active {
        background: #ede9fe;
    }
    .dark .conv-item.active {
        background: #1e1b4b;
    }
    .conv-item.active .font-semibold {
        color: #4f46e5;
    }

    /* Typing indicator */
    @keyframes blink {
        0%, 80%, 100% { opacity: 0; transform: scale(0.8); }
        40% { opacity: 1; transform: scale(1); }
    }
    .typing-dot { animation: blink 1.4s infinite ease-in-out; }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    /* Online status dot */
    .dot-online { background: #22c55e; }
    .dot-offline { background: #94a3b8; }
</style>
@endpush

@section('content')
{{-- Wrapper: chiếm toàn bộ chiều cao còn lại sau navbar --}}
<div class="flex h-[calc(100vh-3.5rem)] overflow-hidden bg-white dark:bg-slate-900">

    {{-- ===== CỘT 1: DANH SÁCH CUỘC HỘI THOẠI ===== --}}
    <div class="w-72 xl:w-80 flex-shrink-0 border-r border-slate-200 dark:border-slate-700 flex flex-col bg-white dark:bg-slate-800">

        {{-- Header --}}
        <div class="px-4 pt-4 pb-3 border-b border-slate-200 dark:border-slate-700 shrink-0">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="bi bi-chat-dots-fill text-indigo-500"></i> Tin nhắn
                </h2>
                @php $openCount = $conversations->where('status','open')->count(); @endphp
                @if($openCount > 0)
                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-xs font-bold text-white bg-indigo-500 rounded-full">
                        {{ $openCount > 99 ? '99+' : $openCount }}
                    </span>
                @endif
            </div>

            {{-- Tab filter --}}
            <div class="flex gap-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-1 text-center" id="conv-tabs">
                <button type="button" data-tab="open" class="flex-1 text-xs py-1.5 px-2 rounded-md font-semibold bg-white dark:bg-slate-600 text-indigo-600 dark:text-indigo-300 shadow-sm transition-all">
                    Hộp thư ({{ $conversations->where('status', 'open')->count() }})
                </button>
                <button type="button" data-tab="closed" class="flex-1 text-xs py-1.5 px-2 rounded-md font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white transition-all">
                    Đã đóng ({{ $conversations->where('status', 'closed')->count() }})
                </button>
            </div>

            {{-- Tìm kiếm --}}
            <div class="relative mt-3">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
                <input type="text" id="search-conversations"
                    class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 dark:focus:ring-indigo-600 transition-colors"
                    placeholder="Tìm cuộc trò chuyện...">
            </div>
        </div>

        {{-- Danh sách conversations --}}
        <div class="flex-1 overflow-y-auto chat-scroll" id="conversation-list">
            @forelse($conversations as $conv)
                @php
                    $lastMsg = $conv->messages->first();
                    $isActive = $activeConversation && $activeConversation->id === $conv->id;
                    $cust = $conv->user;
                    $avatarUrl = ($cust && $cust->avatar)
                        ? asset('storage/'.$cust->avatar)
                        : 'https://ui-avatars.com/api/?name='.urlencode($cust->name ?? 'Guest').'&background=6366f1&color=fff&size=80';
                @endphp
                <a href="{{ route('admin.chat.index', ['conversation_id' => $conv->id]) }}"
                   class="conv-item flex items-center gap-3 px-4 py-3.5 border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors {{ $isActive ? 'active' : '' }}"
                   data-conv-id="{{ $conv->id }}" data-status="{{ $conv->status }}">

                    {{-- Avatar --}}
                    <div class="relative shrink-0">
                        <img src="{{ $avatarUrl }}"
                             alt="{{ $cust->name ?? 'Guest' }}"
                             class="w-11 h-11 rounded-full object-cover border-2 {{ $isActive ? 'border-indigo-400' : 'border-slate-200 dark:border-slate-600' }}">
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white dark:border-slate-800 {{ $conv->status === 'open' ? 'dot-online' : 'dot-offline' }}"></span>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                                {{ $cust->name ?? 'Khách #'.$conv->id }}
                            </span>
                            <span class="text-[11px] text-slate-400 shrink-0 ml-1">
                                {{ $lastMsg ? $lastMsg->created_at->diffForHumans(null, true) : '' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                {{ $lastMsg ? Str::limit($lastMsg->content, 36) : 'Chưa có tin nhắn' }}
                            </p>
                            @if($conv->unread_count > 0)
                                <span class="ml-1 shrink-0 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-indigo-500 rounded-full">
                                    {{ $conv->unread_count }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <i class="bi bi-chat-square text-5xl mb-3 opacity-25"></i>
                    <p class="text-sm font-medium">Chưa có cuộc trò chuyện</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ===== CỘT 2: CỬA SỔ CHAT CHÍNH ===== --}}
    <div class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900">

        @if($activeConversation)
            @php $cust = $activeConversation->user; @endphp
            @php $custAvatar = ($cust && $cust->avatar) ? asset('storage/'.$cust->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($cust->name ?? 'Guest').'&background=6366f1&color=fff&size=80'; @endphp

            {{-- Chat header --}}
            <div class="flex items-center justify-between px-5 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 shrink-0 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <img src="{{ $custAvatar }}" alt="{{ $cust->name ?? 'Guest' }}"
                             class="w-10 h-10 rounded-full object-cover">
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-800 {{ $activeConversation->status === 'open' ? 'dot-online' : 'dot-offline' }}"></span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">
                            {{ $cust->name ?? 'Khách vãng lai #'.$activeConversation->id }}
                        </p>
                        <p class="text-xs {{ $activeConversation->status === 'open' ? 'text-emerald-500' : 'text-slate-400' }} font-medium">
                            {{ $activeConversation->status === 'open' ? '● Đang hoạt động' : '○ Ngoại tuyến' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Toggle status --}}
                    <form action="{{ route('admin.chat.close', $activeConversation->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="text-xs px-3 py-1.5 rounded-lg font-medium transition-colors
                            {{ $activeConversation->status === 'open'
                                ? 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400'
                                : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                            <i class="bi {{ $activeConversation->status === 'open' ? 'bi-x-circle' : 'bi-check-circle' }} mr-1"></i>
                            {{ $activeConversation->status === 'open' ? 'Đóng hội thoại' : 'Mở lại' }}
                        </button>
                    </form>
                    <button class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                </div>
            </div>

            {{-- Messages area --}}
            <div class="flex-1 overflow-y-auto chat-scroll p-5 space-y-4" id="messages-container">

                @php $currentDate = null; @endphp
                @foreach($messages as $msg)
                    @php $msgDate = $msg->created_at->format('Y-m-d'); @endphp

                    {{-- Date separator --}}
                    @if($msgDate !== $currentDate)
                        @php $currentDate = $msgDate; @endphp
                        <div class="flex items-center gap-3 my-2">
                            <div class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></div>
                            <span class="text-[11px] text-slate-400 font-medium px-2 shrink-0">
                                {{ $msg->created_at->isToday() ? 'Hôm nay' : ($msg->created_at->isYesterday() ? 'Hôm qua' : $msg->created_at->format('d/m/Y')) }}
                            </span>
                            <div class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></div>
                        </div>
                    @endif

                    @php $isAdmin = $msg->user_id === auth()->id(); @endphp
                    @php $senderAvatar = ($msg->user && $msg->user->avatar) ? asset('storage/'.$msg->user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($msg->user->name ?? '?').'&background='.($isAdmin ? '6366f1' : 'e2e8f0').'&color='.($isAdmin ? 'fff' : '475569').'&size=60'; @endphp

                    <div class="flex items-end gap-2.5 {{ $isAdmin ? 'flex-row-reverse' : '' }}" data-message-id="{{ $msg->id }}">
                        <img src="{{ $senderAvatar }}" alt="{{ $msg->user->name ?? '?' }}"
                             class="w-7 h-7 rounded-full object-cover shrink-0 mb-1">
                        <div class="max-w-[65%]">
                            <div class="px-4 py-2.5 text-sm leading-relaxed shadow-sm {{ $isAdmin ? 'bubble-admin' : 'bubble-customer' }}">
                                {!! nl2br(e($msg->content)) !!}
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1 {{ $isAdmin ? 'text-right' : 'text-left' }}">
                                {{ $msg->created_at->format('H:i') }}
                                @if($isAdmin)
                                    <i class="bi bi-check2-all ml-0.5 text-indigo-400"></i>
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach

                {{-- Typing indicator (hidden) --}}
                <div id="typing-indicator" class="flex items-end gap-2.5 hidden">
                    <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-600 shrink-0"></div>
                    <div class="bubble-customer px-4 py-3 shadow-sm">
                        <div class="flex items-center gap-1">
                            <span class="typing-dot w-2 h-2 bg-slate-400 rounded-full"></span>
                            <span class="typing-dot w-2 h-2 bg-slate-400 rounded-full"></span>
                            <span class="typing-dot w-2 h-2 bg-slate-400 rounded-full"></span>
                        </div>
                    </div>
                </div>

                <div id="messages-end"></div>
            </div>

            {{-- Input area --}}
            <div class="px-5 py-4 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 shrink-0">
                <div class="flex items-end gap-3">
                    <div class="flex-1 relative">
                        <textarea id="message-input" rows="1"
                            placeholder="Nhập tin nhắn phản hồi..."
                            class="w-full resize-none px-4 py-3 pr-10 text-sm bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-2xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 dark:focus:ring-indigo-600 transition-all chat-scroll"
                            style="min-height:44px; max-height:128px;"></textarea>
                        <button type="button" class="absolute right-3 bottom-2.5 text-slate-400 hover:text-slate-600 transition-colors text-base p-1" title="Emoji">
                            😊
                        </button>
                    </div>
                    <button id="send-btn" type="button"
                        class="flex-shrink-0 w-11 h-11 flex items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white rounded-2xl shadow-md hover:shadow-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="bi bi-send-fill text-sm"></i>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 ml-1">
                    <kbd class="px-1.5 py-0.5 text-[10px] bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded font-mono">Enter</kbd>
                    gửi &nbsp;·&nbsp;
                    <kbd class="px-1.5 py-0.5 text-[10px] bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded font-mono">Shift+Enter</kbd>
                    xuống dòng
                </p>
            </div>

        @else
            {{-- No conversation selected --}}
            <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
                <div class="w-20 h-20 rounded-full bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center mb-4">
                    <i class="bi bi-chat-heart-fill text-4xl text-indigo-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Chọn một cuộc trò chuyện</h3>
                <p class="text-sm text-slate-400 max-w-xs">
                    Chọn khách hàng từ danh sách bên trái để bắt đầu hỗ trợ.
                </p>
            </div>
        @endif
    </div>

    {{-- ===== CỘT 3: THÔNG TIN KHÁCH HÀNG (chỉ xl+) ===== --}}
    @if($activeConversation)
    @php $cust = $activeConversation->user; @endphp
    <div class="w-72 xl:w-80 flex-shrink-0 border-l border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hidden xl:flex flex-col overflow-y-auto chat-scroll">

        {{-- Customer profile --}}
        <div class="px-5 pt-6 pb-5 border-b border-slate-200 dark:border-slate-700 text-center">
            <img src="{{ ($cust && $cust->avatar) ? asset('storage/'.$cust->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($cust->name ?? 'Guest').'&background=6366f1&color=fff&size=120' }}"
                 alt="{{ $cust->name ?? 'Guest' }}"
                 class="w-16 h-16 rounded-full object-cover mx-auto mb-3 border-4 border-indigo-100 dark:border-indigo-900/30">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">
                {{ $cust->name ?? 'Khách vãng lai #'.$activeConversation->id }}
            </h3>
            @if($cust && $cust->email)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate px-2">{{ $cust->email }}</p>
            @endif

            <div class="flex items-center justify-center gap-2 mt-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full
                    {{ $activeConversation->status === 'open' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $activeConversation->status === 'open' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    {{ $activeConversation->status === 'open' ? 'Đang mở' : 'Đã đóng' }}
                </span>
                @if($cust)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                    <i class="bi bi-person-fill text-[10px]"></i>
                    {{ ucfirst($cust->role ?? 'customer') }}
                </span>
                @endif
            </div>
        </div>

        {{-- Info rows --}}
        <div class="px-5 py-4 space-y-3 border-b border-slate-200 dark:border-slate-700">
            <h4 class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Thông tin</h4>

            @if($cust)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                    <i class="bi bi-envelope text-slate-500 text-xs"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] text-slate-400 uppercase">Email</p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200 truncate">{{ $cust->email }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                    <i class="bi bi-calendar3 text-slate-500 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase">Tham gia</p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $cust->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
            @endif

            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                    <i class="bi bi-chat-square-dots text-slate-500 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase">Phiên chat</p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">#{{ $activeConversation->id }} &middot; {{ $messages->count() }} tin nhắn</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                    <i class="bi bi-clock-history text-slate-500 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase">Bắt đầu</p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $activeConversation->created_at->format('H:i · d/m/Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Đơn hàng liên kết + Đơn hàng gần đây --}}
        @if($cust)
        <div class="px-5 py-4">

            {{-- Đơn hàng liên kết với cuộc hội thoại này (nổi bật) --}}
            @if($activeConversation->order_id && $activeConversation->order)
            @php 
                $linkedOrder = $activeConversation->order; 
                $linkedOrderImage = null;
                if ($linkedOrder->items->isNotEmpty()) {
                    $firstItem = $linkedOrder->items->first();
                    if ($firstItem->productVariant) {
                        $linkedOrderImage = $firstItem->productVariant->thumbnail_url ?? optional($firstItem->productVariant->product)->primary_image_url;
                    }
                }
            @endphp
            <h4 class="text-[11px] font-bold text-indigo-400 uppercase tracking-widest mb-2">Đơn hàng được hỏi</h4>
            <a href="{{ route('admin.orders.show', $linkedOrder->id) }}"
               class="flex items-center justify-between py-3 px-3 mb-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20
                      border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition-colors group">
                <div class="flex items-center gap-3">
                    @if($linkedOrderImage)
                        <img src="{{ Storage::url($linkedOrderImage) }}" alt="Order Image" class="w-10 h-10 object-cover rounded-md border border-indigo-200">
                    @else
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-md flex items-center justify-center border border-indigo-200 dark:border-indigo-800">
                            <i class="bi bi-bag-fill text-indigo-500 text-lg"></i>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-bold text-indigo-700 dark:text-indigo-300">#ORD-{{ $linkedOrder->id }}</p>
                        <p class="text-xs text-indigo-400">{{ $linkedOrder->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-indigo-800 dark:text-indigo-200">{{ number_format($linkedOrder->total_amount) }}₫</p>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                        @switch($linkedOrder->status)
                            @case('paid') Đã TT @break
                            @case('pending') Chờ xử lý @break
                            @case('cancelled') Đã huỷ @break
                            @case('completed') Hoàn thành @break
                            @default {{ ucfirst($linkedOrder->status) }}
                        @endswitch
                    </span>
                </div>
            </a>
            @endif

            <h4 class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-3">Đơn hàng gần đây</h4>
            @php
                $recentOrders = \App\Models\Order::where('user_id', $cust->id)
                    ->when($activeConversation->order_id, fn($q) => $q->where('id', '!=', $activeConversation->order_id))
                    ->latest()->take(3)->get();
            @endphp
            @forelse($recentOrders as $order)
                <a href="{{ route('admin.orders.show', $order->id) }}"
                   class="flex items-center justify-between py-3 px-3 mb-2 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors group">
                    <div>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            #{{ $order->id }}
                        </p>
                        <p class="text-xs text-slate-400">{{ $order->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ number_format($order->total_amount) }}₫</p>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold
                            @switch($order->status)
                                @case('paid') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 @break
                                @case('pending') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 @break
                                @case('cancelled') bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 @break
                                @default bg-slate-100 text-slate-600
                            @endswitch">
                            @switch($order->status)
                                @case('paid') Đã thanh toán @break
                                @case('pending') Chờ xử lý @break
                                @case('cancelled') Đã huỷ @break
                                @default {{ ucfirst($order->status) }}
                            @endswitch
                        </span>
                    </div>
                </a>
            @empty
                @if(!$activeConversation->order_id)
                <div class="text-center py-6">
                    <i class="bi bi-bag-x text-2xl text-slate-300 dark:text-slate-600"></i>
                    <p class="text-xs text-slate-400 mt-1">Chưa có đơn hàng</p>
                </div>
                @endif
            @endforelse
        </div>
        @endif


    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const conversationId    = {{ $activeConversation ? $activeConversation->id : 'null' }};
    const currentUserId     = {{ auth()->id() }};
    const messageInput      = document.getElementById('message-input');
    const sendBtn           = document.getElementById('send-btn');
    const typingIndicator   = document.getElementById('typing-indicator');

    // ── Scroll to bottom ──
    function scrollToBottom(smooth = false) {
        const el = document.getElementById('messages-end');
        if (el) el.scrollIntoView({ behavior: smooth ? 'smooth' : 'instant' });
    }
    scrollToBottom(false);

    // ── Auto-resize textarea ──
    if (messageInput) {
        messageInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 128) + 'px';
        });

        // Enter = send | Shift+Enter = newline
        messageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    if (sendBtn) sendBtn.addEventListener('click', sendMessage);

    // ── Build bubble HTML ──
    function buildBubble(msg, isAdmin) {
        const name   = msg.user ? msg.user.name : '?';
        const avatar = msg.user && msg.user.avatar
            ? `/storage/${msg.user.avatar}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${isAdmin ? '6366f1' : 'e2e8f0'}&color=${isAdmin ? 'fff' : '475569'}&size=60`;
        const time   = new Date(msg.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        const check  = isAdmin ? '<i class="bi bi-check2-all ml-0.5 text-indigo-400"></i>' : '';
        const safe   = msg.content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');

        return `
        <div class="flex items-end gap-2.5 ${isAdmin ? 'flex-row-reverse' : ''}" data-message-id="${msg.id}">
            <img src="${avatar}" alt="${name}" class="w-7 h-7 rounded-full object-cover shrink-0 mb-1">
            <div class="max-w-[65%]">
                <div class="px-4 py-2.5 text-sm leading-relaxed shadow-sm ${isAdmin ? 'bubble-admin' : 'bubble-customer'}">
                    ${safe}
                </div>
                <p class="text-[11px] text-slate-400 mt-1 ${isAdmin ? 'text-right' : 'text-left'}">
                    ${time} ${check}
                </p>
            </div>
        </div>`;
    }

    // ── Send message via AJAX ──
    async function sendMessage() {
        if (!conversationId || !messageInput) return;
        const content = messageInput.value.trim();
        if (!content) return;

        sendBtn.disabled = messageInput.disabled = true;

        try {
            const res  = await fetch('{{ route("admin.chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ conversation_id: conversationId, content }),
            });
            const data = await res.json();

            if (data.status === 'success') {
                document.getElementById('messages-end')
                    .insertAdjacentHTML('beforebegin', buildBubble(data.message, true));
                messageInput.value = '';
                messageInput.style.height = 'auto';
                scrollToBottom(true);
            }
        } catch (err) {
            console.error('Lỗi gửi tin nhắn:', err);
        } finally {
            sendBtn.disabled = messageInput.disabled = false;
            messageInput.focus();
        }
    }

    // ── Real-time: nhận tin nhắn từ khách qua Reverb ──
    if (conversationId && typeof window.Echo !== 'undefined') {
        window.Echo.private(`chat.${conversationId}`)
            .listen('MessageSent', (e) => {
                const msg = e.message;
                if (msg.user_id === currentUserId) return; // Không vẽ lại tin mình vừa gửi

                if (typingIndicator) typingIndicator.classList.add('hidden');

                document.getElementById('messages-end')
                    .insertAdjacentHTML('beforebegin', buildBubble(msg, false));
                scrollToBottom(true);
            });
    }

    // ── Tìm kiếm và Lọc (Tab) conversation ──
    const searchInput = document.getElementById('search-conversations');
    const tabButtons = document.querySelectorAll('#conv-tabs button');
    let currentTab = 'open';

    function filterConversations() {
        const q = searchInput ? searchInput.value.toLowerCase() : '';
        document.querySelectorAll('.conv-item').forEach(item => {
            const status = item.dataset.status;
            const name = item.querySelector('span.font-semibold')?.textContent.toLowerCase() ?? '';
            const preview = item.querySelector('p.text-xs')?.textContent.toLowerCase() ?? '';
            
            const matchStatus = (status === currentTab);
            const matchSearch = name.includes(q) || preview.includes(q);
            
            item.style.display = (matchStatus && matchSearch) ? '' : 'none';
        });
    }

    if (tabButtons) {
        tabButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                tabButtons.forEach(b => {
                    b.className = 'flex-1 text-xs py-1.5 px-2 rounded-md font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white transition-all';
                });
                this.className = 'flex-1 text-xs py-1.5 px-2 rounded-md font-semibold bg-white dark:bg-slate-600 text-indigo-600 dark:text-indigo-300 shadow-sm transition-all';
                currentTab = this.dataset.tab;
                filterConversations();
            });
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterConversations);
    }
    
    // Khởi chạy lọc lần đầu
    filterConversations();
});
</script>
@endpush
