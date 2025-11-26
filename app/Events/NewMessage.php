<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        if (!$this->message->conversation) {
            $this->message->load('conversation');
        }

        return [
            new PrivateChannel('chat.user.' . $this->message->conversation->student_id),
            new PrivateChannel('chat.user.' . $this->message->conversation->nurse_id),
        ];
    }

    public function broadcastAs()
    {
        return 'new.chat.message';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message->toApiResponse(),
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
        ];
    }

    public function broadcastWhen(): bool
    {
        return $this->message !== null && 
               $this->message->conversation !== null &&
               $this->message->sender !== null;
    }
}