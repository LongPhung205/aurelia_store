<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversation_id}', function ($user, $conversation_id) {
    $conversation = \App\Models\Conversation::find($conversation_id);
    if (!$conversation) {
        return false;
    }
    
    // Only the customer who owns the conversation, or an admin can listen to this channel
    return (int) $user->id === (int) $conversation->user_id || $user->isAdmin();
});
