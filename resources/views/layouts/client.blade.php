<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-[#FDFDFC] text-[#1b1b18] flex flex-col min-h-screen">
        <x-header />

        @yield('full_width_top')

        <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </main>
        <x-footer />
        
        {{-- ============================================================
             FLOATING CHAT WIDGET – hiển thị trên mọi trang client
             ============================================================ --}}
        <div id="chat-widget-root">

            {{-- Nút bong bóng --}}
            <button id="chat-toggle-btn" aria-label="Mở chat hỗ trợ"
                class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full flex items-center justify-center
                       bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-xl
                       hover:shadow-2xl hover:scale-110 active:scale-95 transition-all duration-200 group">
                <i id="chat-icon-open"  class="bi bi-chat-dots-fill text-2xl"></i>
                {{-- Badge tin chưa đọc --}}
                <span id="chat-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-rose-500 text-white text-[10px] font-bold rounded-full hidden items-center justify-center">0</span>
            </button>

            {{-- Popup Widget --}}
            <div id="chat-popup"
                 class="fixed bottom-6 right-6 z-50 w-80 sm:w-96 rounded-2xl shadow-2xl overflow-hidden
                        flex flex-col border border-slate-200
                        transform scale-95 opacity-0 pointer-events-none transition-all duration-200 origin-bottom-right"
                 style="height: 520px;">

                {{-- Header --}}
                <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
                            <i class="bi bi-headset text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white text-sm font-bold leading-tight">Hỗ trợ Aurelia</p>
                            <p class="text-indigo-200 text-[11px] flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full inline-block"></span>
                                Chúng tôi luôn sẵn sàng
                            </p>
                        </div>
                    </div>
                    <button id="chat-close-btn" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Thông tin đơn hàng (hiện khi mở từ order) --}}
                <div id="chat-order-banner" class="hidden items-center gap-2 px-3 py-2 bg-indigo-50 border-b border-indigo-100 text-sm">
                    <i class="bi bi-bag-check-fill text-indigo-500 shrink-0"></i>
                    <span class="text-indigo-700 font-medium text-xs" id="chat-order-label"></span>
                </div>

                {{-- Messages area --}}
                <div id="chat-messages" class="flex-1 overflow-y-auto p-3 space-y-3 bg-slate-50"
                     style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">

                    {{-- Loading state --}}
                    <div id="chat-loading" class="flex flex-col items-center justify-center h-full text-slate-400 py-8">
                        <div class="w-10 h-10 border-3 border-indigo-300 border-t-indigo-600 rounded-full animate-spin mb-3"></div>
                        <p class="text-xs">Đang tải...</p>
                    </div>

                    {{-- Tin nhắn sẽ được render bằng JS --}}
                </div>

                {{-- Input area --}}
                <div class="px-3 py-3 bg-white border-t border-slate-200 shrink-0">
                    <div class="flex items-end gap-2">
                        <textarea id="chat-widget-input" rows="1" placeholder="Nhập tin nhắn..."
                            class="flex-1 resize-none text-sm px-3 py-2 rounded-xl border border-slate-200 bg-slate-50
                                   focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all text-slate-800 placeholder-slate-400"
                            style="min-height: 38px; max-height: 96px; scrollbar-width: thin;"></textarea>
                        <button id="chat-widget-send"
                            class="w-9 h-9 flex items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600
                                   text-white rounded-xl hover:opacity-90 active:scale-95 transition-all shrink-0 disabled:opacity-50">
                            <i class="bi bi-send-fill text-xs"></i>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1.5 ml-0.5">
                        <kbd class="px-1 py-0.5 bg-slate-100 border border-slate-200 rounded text-[9px] font-mono">Enter</kbd> gửi &nbsp;·&nbsp;
                        <kbd class="px-1 py-0.5 bg-slate-100 border border-slate-200 rounded text-[9px] font-mono">Shift+Enter</kbd> xuống dòng
                    </p>
                </div>
            </div>
        </div>

        {{-- ============================================================
             Script chat widget – PHẢI đặt sau @stack('scripts')
             ============================================================ --}}

        @stack('scripts')

        <script>
        (function () {
            const IS_AUTH    = {{ auth()->check() ? 'true' : 'false' }};
            const LOGIN_URL  = '{{ route("login") }}';
            const OPEN_URL   = '{{ route("client.chat.open") }}';
            const SEND_URL   = '{{ route("client.chat.send") }}';
            const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const CURRENT_USER_ID = {{ auth()->check() ? auth()->id() : 'null' }};

            let conversationId = null;
            let echoChannel    = null;
            let unreadCount    = 0;

            // ── DOM refs ──
            const toggleBtn  = document.getElementById('chat-toggle-btn');
            const popup      = document.getElementById('chat-popup');
            const badge      = document.getElementById('chat-badge');
            const messages   = document.getElementById('chat-messages');
            const loading    = document.getElementById('chat-loading');
            const input      = document.getElementById('chat-widget-input');
            const sendBtn    = document.getElementById('chat-widget-send');
            const closeBtn   = document.getElementById('chat-close-btn');
            const orderBanner = document.getElementById('chat-order-banner');
            const orderLabel  = document.getElementById('chat-order-label');

            let isOpen = false;

            // ── Toggle popup ──
            function openChat(orderId) {
                if (!IS_AUTH) { window.location.href = LOGIN_URL; return; }

                isOpen = true;
                popup.classList.remove('scale-95','opacity-0','pointer-events-none');
                popup.classList.add('scale-100','opacity-100');
                
                // Hide bubble button
                toggleBtn.classList.add('scale-0', 'opacity-0', 'pointer-events-none');

                clearBadge();

                if (conversationId === null) {
                    initConversation(orderId);
                } else if (orderId && conversationId) {
                    // Nếu mở từ đơn hàng khác → reinit
                    initConversation(orderId);
                } else {
                    scrollToBottom();
                }
            }

            function closeChat() {
                isOpen = false;
                popup.classList.add('scale-95','opacity-0','pointer-events-none');
                popup.classList.remove('scale-100','opacity-100');

                // Show bubble button
                toggleBtn.classList.remove('scale-0', 'opacity-0', 'pointer-events-none');
            }

            toggleBtn.addEventListener('click', () => isOpen ? closeChat() : openChat(null));
            closeBtn.addEventListener('click', closeChat);

            // ── Lắng nghe sự kiện từ trang order_show ──
            document.addEventListener('openChatForOrder', function (e) {
                openChat(e.detail.orderId);
            });

            // ── Khởi tạo conversation ──
            async function initConversation(orderId) {
                loading.classList.remove('hidden');
                // Xóa tin nhắn cũ
                Array.from(messages.children).forEach(el => {
                    if (el.id !== 'chat-loading') el.remove();
                });
                loading.classList.remove('hidden');

                try {
                    const res  = await fetch(OPEN_URL, {
                        method: 'POST',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'X-Requested-With':'XMLHttpRequest' },
                        body: JSON.stringify({ order_id: orderId || null }),
                    });
                    const data = await res.json();

                    loading.classList.add('hidden');
                    conversationId = data.conversation_id;

                    // Hiện banner đơn hàng
                    if (data.order) {
                        orderLabel.textContent = `Hỗ trợ đơn hàng #ORD-${data.order.id}`;
                        orderBanner.classList.remove('hidden');
                        orderBanner.classList.add('flex');
                    } else {
                        orderBanner.classList.add('hidden');
                        orderBanner.classList.remove('flex');
                    }

                    // Render tin nhắn cũ
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => appendBubble(msg, msg.user_id === CURRENT_USER_ID));
                    } else {
                        appendWelcome();
                    }

                    scrollToBottom();
                    subscribeEcho();
                } catch (e) {
                    loading.classList.add('hidden');
                    console.error('Chat init error:', e);
                }
            }

            // ── Tin nhắn chào mừng ──
            function appendWelcome() {
                const el = document.createElement('div');
                el.className = 'flex items-start gap-2';
                el.innerHTML = `
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shrink-0">
                        <i class="bi bi-headset text-white text-xs"></i>
                    </div>
                    <div class="bg-white text-slate-700 text-sm px-3 py-2 rounded-2xl rounded-tl-sm shadow-sm max-w-[80%]">
                        Xin chào! Chúng tôi có thể giúp gì cho bạn? 😊
                    </div>`;
                messages.appendChild(el);
            }

            // ── Build bubble ──
            function appendBubble(msg, isMe) {
                const el  = document.createElement('div');
                el.dataset.msgId = msg.id;
                el.className = `flex items-end gap-2 ${isMe ? 'flex-row-reverse' : ''}`;

                const avatar = msg.user && msg.user.avatar
                    ? `/storage/${msg.user.avatar}`
                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(msg.user?.name||'?')}&background=${isMe?'6366f1':'e2e8f0'}&color=${isMe?'fff':'475569'}&size=60`;

                const time = new Date(msg.created_at).toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'});
                const safe = String(msg.content).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');

                el.innerHTML = `
                    <img src="${avatar}" class="w-6 h-6 rounded-full object-cover shrink-0 mb-0.5" alt="">
                    <div class="max-w-[75%]">
                        <div class="text-sm px-3 py-2 rounded-2xl shadow-sm leading-relaxed
                            ${isMe ? 'bg-gradient-to-br from-indigo-500 to-violet-600 text-white rounded-br-sm' : 'bg-white text-slate-700 rounded-bl-sm'}">
                            ${safe}
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5 ${isMe?'text-right':'text-left'}">${time}</p>
                    </div>`;

                messages.appendChild(el);
            }

            function scrollToBottom() {
                messages.scrollTop = messages.scrollHeight;
            }

            // ── Gửi tin nhắn ──
            async function sendMessage() {
                if (!conversationId) return;
                const content = input.value.trim();
                if (!content) return;

                sendBtn.disabled = input.disabled = true;

                try {
                    const res  = await fetch(SEND_URL, {
                        method: 'POST',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'X-Requested-With':'XMLHttpRequest' },
                        body: JSON.stringify({ conversation_id: conversationId, content }),
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        appendBubble(data.message, true);
                        input.value = '';
                        input.style.height = 'auto';
                        scrollToBottom();
                    }
                } catch (e) {
                    console.error('Send error:', e);
                } finally {
                    sendBtn.disabled = input.disabled = false;
                    input.focus();
                }
            }

            input?.addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
            });
            input?.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 96) + 'px';
            });
            sendBtn?.addEventListener('click', sendMessage);

            // ── Laravel Echo realtime ──
            function subscribeEcho() {
                if (!conversationId || typeof window.Echo === 'undefined') return;
                if (echoChannel) echoChannel.stopListening('MessageSent');

                echoChannel = window.Echo.private(`chat.${conversationId}`)
                    .listen('MessageSent', (e) => {
                        const msg = e.message;
                        if (msg.user_id === CURRENT_USER_ID) return;

                        appendBubble(msg, false);
                        scrollToBottom();

                        if (!isOpen) {
                            unreadCount++;
                            badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                            badge.classList.remove('hidden');
                            badge.classList.add('flex');
                        }
                    });
            }

            function clearBadge() {
                unreadCount = 0;
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        })();
        </script>

        {{-- Global Toast Notification (Alpine.js) --}}
        <div x-data="{ show: false, message: '', type: 'success' }"
             @notify.window="message = $event.detail.message; type = $event.detail.type || 'success'; show = true; setTimeout(() => show = false, 4000)"
             class="fixed top-24 right-4 z-[9999] transition-all duration-300 transform"
             :class="show ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'"
             style="display: none;" x-show="show">
             <div class="px-6 py-4 rounded-xl shadow-2xl text-white font-medium flex items-center gap-3"
                  :class="type === 'success' ? 'bg-emerald-600' : (type === 'error' ? 'bg-rose-600' : 'bg-gray-800')">
                  <i class="bi text-xl" :class="type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'"></i>
                  <span x-text="message"></span>
             </div>
        </div>

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Flash messages to Toast -->
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: @json(session('success')), type: 'success' }
                    }));
                });
            </script>
        @endif
        @if(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: @json(session('error')), type: 'error' }
                    }));
                });
            </script>
        @endif
        @if(session('warning'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: @json(session('warning')), type: 'error' }
                    }));
                });
            </script>
        @endif
        @if($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: @json($errors->first()), type: 'error' }
                    }));
                });
            </script>
        @endif

        <!-- Global SweetAlert2 & Confirmation Handlers -->
        <script>
            // Override native window.alert with modern SweetAlert2
            window.alert = function(message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Thông báo',
                        text: String(message),
                        icon: 'info',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Đóng'
                    });
                } else {
                    console.log('Alert:', message);
                }
            };

            // Delegated submit handler for forms with .form-delete or .form-confirm
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!form || !form.classList) return;

                const isDelete = form.classList.contains('form-delete');
                const isConfirm = form.classList.contains('form-confirm');

                if (isDelete || isConfirm) {
                    e.preventDefault();
                    const title = form.dataset.confirmTitle || (isDelete ? 'Xóa dữ liệu?' : 'Xác nhận thao tác?');
                    const text = form.dataset.confirmText || (isDelete ? 'Hành động này không thể hoàn tác!' : 'Bạn có chắc chắn muốn tiếp tục?');
                    const icon = form.dataset.confirmIcon || (isDelete ? 'warning' : 'question');
                    const confirmBtn = form.dataset.confirmBtn || (isDelete ? '<i class="bi bi-trash mr-1"></i> Đồng ý xóa' : '<i class="bi bi-check-lg mr-1"></i> Xác nhận');
                    const confirmColor = form.dataset.confirmColor || (isDelete ? '#e11d48' : '#2563eb');

                    Swal.fire({
                        title: title,
                        text: text,
                        icon: icon,
                        showCancelButton: true,
                        confirmButtonColor: confirmColor,
                        cancelButtonColor: '#64748b',
                        confirmButtonText: confirmBtn,
                        cancelButtonText: 'Hủy',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });

            // Global Order Cancellation Handler
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.form-cancel-order').forEach(form => {
                    const btn = form.querySelector('.btn-trigger-cancel');
                    if (!btn) return;

                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        const orderId = form.dataset.orderId || '';

                        Swal.fire({
                            title: 'Hủy đơn hàng #' + (orderId ? 'ORD-' + orderId : ''),
                            text: 'Bạn có chắc chắn muốn hủy đơn hàng này không? Số lượng sản phẩm sẽ được hoàn trả lại kho.',
                            icon: 'warning',
                            input: 'select',
                            inputOptions: {
                                'Đổi ý không muốn mua nữa': 'Đổi ý không muốn mua nữa',
                                'Muốn thay đổi địa chỉ nhận hàng': 'Muốn thay đổi địa chỉ nhận hàng',
                                'Muốn đổi phân loại/kích cỡ': 'Muốn đổi phân loại/kích cỡ',
                                'Đặt nhầm đơn hàng': 'Đặt nhầm đơn hàng',
                                'Khác': 'Lý do khác'
                            },
                            inputPlaceholder: 'Chọn lý do hủy...',
                            showCancelButton: true,
                            confirmButtonColor: '#e11d48',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: '<i class="bi bi-x-circle mr-1"></i> Đồng ý hủy',
                            cancelButtonText: 'Giữ lại đơn hàng',
                            inputValidator: (value) => {
                                if (!value) {
                                    return 'Vui lòng chọn lý do hủy đơn!';
                                }
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                let reasonInput = form.querySelector('input[name="cancel_reason"]');
                                if (!reasonInput) {
                                    reasonInput = document.createElement('input');
                                    reasonInput.type = 'hidden';
                                    reasonInput.name = 'cancel_reason';
                                    form.appendChild(reasonInput);
                                }
                                reasonInput.value = result.value || 'Khách hàng yêu cầu hủy đơn';
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>

    </body>
</html>
