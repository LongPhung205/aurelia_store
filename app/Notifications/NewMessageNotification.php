<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Message;

class NewMessageNotification extends Notification
{
    use Queueable;

    public $messageObj;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $messageObj)
    {
        $this->messageObj = $messageObj;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_message',
            'conversation_id' => $this->messageObj->conversation_id,
            'sender_name' => $this->messageObj->sender_type == 'user' ? ($this->messageObj->user->name ?? 'Khách hàng') : 'System',
            'message' => \Illuminate\Support\Str::limit($this->messageObj->content, 50),
            'url' => route('admin.chat.index', ['conversation' => $this->messageObj->conversation_id])
        ];
    }
}
