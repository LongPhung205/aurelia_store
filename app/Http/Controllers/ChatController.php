<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\Client\SendChatMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function sendMessage(SendChatMessageRequest $request)
    {
        $validated = $request->validated();
        $conversation = Conversation::findOrFail($validated['conversation_id']);

        // Security check: only allow sender if they are the owner of the conversation or an admin
        if ($conversation->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this conversation.');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => $message->load('user')
        ]);
    }
}
