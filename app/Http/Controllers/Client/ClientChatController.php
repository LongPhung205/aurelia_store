<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientChatController extends Controller
{
    /**
     * Tìm hoặc tạo conversation cho khách hàng.
     * - Nếu có order_id: tìm conversation open của user gắn order đó
     * - Nếu không: tìm conversation open gần nhất của user (chat chung)
     * Trả về JSON để widget frontend dùng.
     */
    public function openOrCreate(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $user    = Auth::user();
        $orderId = $request->order_id;

        // Tìm conversation phù hợp đang mở
        $query = Conversation::where('user_id', $user->id)
                              ->where('status', 'open');

        if ($orderId) {
            $query->where('order_id', $orderId);
        } else {
            $query->whereNull('order_id');
        }

        $conversation = $query->latest()->first();

        // Tạo mới nếu chưa có
        if (! $conversation) {
            $conversation = Conversation::create([
                'user_id'  => $user->id,
                'order_id' => $orderId,
                'status'   => 'open',
            ]);
        }

        // Nạp tin nhắn cũ kèm thông tin người gửi
        $messages = Message::with('user')
            ->where('conversation_id', $conversation->id)
            ->oldest()
            ->get()
            ->map(function ($msg) {
                return [
                    'id'              => $msg->id,
                    'content'         => $msg->content,
                    'user_id'         => $msg->user_id,
                    'created_at'      => $msg->created_at->toISOString(),
                    'user' => [
                        'id'     => $msg->user->id,
                        'name'   => $msg->user->name,
                        'avatar' => $msg->user->avatar,
                    ],
                ];
            });

        // Thông tin đơn hàng nếu có
        $orderInfo = null;
        if ($orderId) {
            $conversation->loadMissing('order');
            if ($conversation->order) {
                $orderInfo = [
                    'id'     => $conversation->order->id,
                    'status' => $conversation->order->status,
                    'total'  => $conversation->order->total_amount,
                ];
            }
        }

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages'        => $messages,
            'order'           => $orderInfo,
        ]);
    }

    /**
     * Khách hàng gửi tin nhắn.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content'         => 'required|string|max:2000',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        // Chỉ cho phép chủ conversation gửi tin
        if ($conversation->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền gửi tin vào cuộc hội thoại này.');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => Auth::id(),
            'content'         => $request->content,
        ]);

        $conversation->touch();

        broadcast(new MessageSent($message->load('user')))->toOthers();

        // Notify Admins
        try {
            $admins = \App\Models\User::where('role', 'admin')->get();
            if ($admins->count() > 0) {
                // To avoid spam, maybe we should only notify if there is no recent notification for this conversation?
                // But simple implementation is just to send it.
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewMessageNotification($message));
            }
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json([
            'status'  => 'success',
            'message' => $message->load('user'),
        ]);
    }
}
