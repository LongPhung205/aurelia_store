<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat Support') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Chat Box -->
                    <div id="chat-box" class="h-64 overflow-y-scroll border border-gray-300 p-4 mb-4 bg-gray-50 rounded">
                        <!-- Messages will appear here -->
                        @foreach($messages ?? [] as $msg)
                            <div class="mb-2">
                                <strong>{{ $msg->user->name }}:</strong>
                                <span>{{ $msg->content }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Input Form -->
                    <div class="flex">
                        <input type="text" id="chat-input" class="border rounded px-4 py-2 w-full focus:outline-none focus:border-blue-500" placeholder="Nhập tin nhắn..." autocomplete="off">
                        <button id="send-btn" class="ml-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Gửi</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Script to handle WebSocket & AJAX -->
    <script type="module">
        // Hardcode conversation ID for demonstration (In reality, pass this from controller)
        const conversationId = 1;
        const currentUserId = {{ auth()->id() ?? 'null' }};
        const chatBox = document.getElementById('chat-box');
        const chatInput = document.getElementById('chat-input');
        const sendBtn = document.getElementById('send-btn');

        // Listen for new messages
        window.Echo.private('chat.' + conversationId)
            .listen('MessageSent', (e) => {
                const message = e.message;
                const isMe = message.user_id === currentUserId;
                
                // If it's my own message, skip it because we render it eagerly in axios.then()
                if(isMe) return;

                const div = document.createElement('div');
                div.className = "mb-2";
                div.innerHTML = `<strong>${message.user ? message.user.name : 'Unknown'}:</strong> <span>${message.content}</span>`;
                chatBox.appendChild(div);
                
                // Scroll to bottom
                chatBox.scrollTop = chatBox.scrollHeight;
            });

        // Send message function
        const sendMessage = () => {
            const content = chatInput.value.trim();
            if (!content) return;

            chatInput.value = '';

            axios.post('/chat/message', {
                conversation_id: conversationId,
                content: content
            }).then(response => {
                const message = response.data.message;
                // Render our own message immediately
                const div = document.createElement('div');
                div.className = "mb-2 text-blue-600";
                div.innerHTML = `<strong>You:</strong> <span>${message.content}</span>`;
                chatBox.appendChild(div);
                chatBox.scrollTop = chatBox.scrollHeight;
            }).catch(error => {
                console.error("Error sending message", error);
                alert("Failed to send message!");
            });
        };

        sendBtn.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</x-app-layout>
