<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
{
    /**
     * Hiển thị trang chat admin với toàn bộ conversations.
     */
    public function index(Request $request)
    {
        // Lấy tất cả conversations, kèm tin nhắn mới nhất và user
        $conversations = Conversation::with(['user', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }])
            ->withCount(['messages as unread_count' => function ($q) {
                // Đếm tin nhắn chưa đọc (từ phía khách hàng - user_id khác admin)
                $q->where('user_id', '!=', Auth::id());
            }])
            ->latest('updated_at')
            ->get();

        // Nếu có conversation_id trên URL, load conversation đó
        $activeConversation = null;
        $messages = collect();

        if ($request->has('conversation_id')) {
            $activeConversation = Conversation::with([
                'user',
                'order.items.productVariant.product.primaryImage'
            ])->findOrFail($request->conversation_id);
            $messages = Message::with('user')
                ->where('conversation_id', $activeConversation->id)
                ->oldest()
                ->get();
        } elseif ($conversations->isNotEmpty()) {
            // Mặc định chọn conversation đầu tiên
            $activeConversation = $conversations->first()->load([
                'user',
                'order.items.productVariant.product.primaryImage'
            ]);
            $messages = Message::with('user')
                ->where('conversation_id', $activeConversation->id)
                ->oldest()
                ->get();
        }

        return view('admin.chat.index', compact('conversations', 'activeConversation', 'messages'));
    }

    /**
     * Đóng hoặc mở lại một conversation.
     */
    public function toggleStatus(Conversation $conversation)
    {
        $conversation->update([
            'status' => $conversation->status === 'open' ? 'closed' : 'open',
        ]);

        return redirect()->back()->with(
            'success',
            $conversation->status === 'open' ? 'Đã mở lại hội thoại.' : 'Đã đóng hội thoại.'
        );
    }

    /**
     * Admin gửi tin nhắn phản hồi khách hàng.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content'         => 'required|string|max:2000',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => Auth::id(),
            'content'         => $request->content,
        ]);

        // Cập nhật thời gian conversation để sort lên đầu
        $conversation->touch();

        broadcast(new MessageSent($message->load('user')))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => $message->load('user'),
        ]);
    }
}
