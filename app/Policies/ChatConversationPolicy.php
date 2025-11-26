<?php
// app/Policies/ChatConversationPolicy.php

namespace App\Policies;

use App\Models\ChatConversation;
use App\Models\User;

class ChatConversationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isNurse() || $user->isStudent();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChatConversation $chatConversation): bool
    {
        // Dean can view all conversations for moderation
        if ($user->isDean()) {
            return true;
        }

        // Participants can view their conversations
        return $chatConversation->nurse_id === $user->id 
            || $chatConversation->student_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only nurses can initiate new conversations
        return $user->isNurse();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChatConversation $chatConversation): bool
    {
        // Participants can send messages (which updates last_message_at)
        return $chatConversation->nurse_id === $user->id 
            || $chatConversation->student_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChatConversation $chatConversation): bool
    {
        // Only nurses can delete their conversations, or dean for moderation
        return $user->isDean() 
            || ($user->isNurse() && $chatConversation->nurse_id === $user->id);
    }

    /**
     * Determine whether the user can send messages in the conversation.
     */
    public function sendMessage(User $user, ChatConversation $chatConversation): bool
    {
        return $chatConversation->nurse_id === $user->id 
            || $chatConversation->student_id === $user->id;
    }

    /**
     * Determine whether the user can mark messages as read.
     */
    public function markAsRead(User $user, ChatConversation $chatConversation): bool
    {
        return $chatConversation->nurse_id === $user->id 
            || $chatConversation->student_id === $user->id;
    }

    /**
     * Determine whether the user can search for students.
     */
    public function searchStudents(User $user): bool
    {
        return $user->isNurse();
    }
}