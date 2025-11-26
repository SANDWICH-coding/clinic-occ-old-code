<?php

use App\Models\ChatConversation;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('chat.user.{userId}', function ($user, $userId) {
    $allowed = (int) $user->id === (int) $userId;
    Log::debug("Channel auth - chat.user.{$userId}", [
        'user_id' => $user->id,
        'allowed' => $allowed
    ]);
    return $allowed;
});

Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = ChatConversation::find($conversationId);
    $allowed = $conversation && $conversation->isParticipant($user->id);
    
    Log::debug("Channel auth - chat.conversation.{$conversationId}", [
        'user_id' => $user->id,
        'allowed' => $allowed
    ]);
    
    return $allowed;
});

Broadcast::channel('chat.presence.{conversationId}', function ($user, $conversationId) {
    $conversation = ChatConversation::find($conversationId);
    
    if ($conversation && $conversation->isParticipant($user->id)) {
        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'role' => $user->role,
        ];
    }
});