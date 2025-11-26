<?php
// app/Models/ChatConversation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // Add this import
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'nurse_id',
        'student_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    protected $appends = ['unread_count', 'other_participant'];

    // Relationships
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function lastMessage(): HasOne // Fixed return type
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    // Scopes
    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('nurse_id', $userId)
                    ->orWhere('student_id', $userId);
    }

    public function scopeWithUnreadCount(Builder $query, $userId): Builder
    {
        return $query->withCount(['messages as unread_count' => function ($q) use ($userId) {
            $q->where('sender_id', '!=', $userId)
              ->where('is_read', false);
        }]);
    }

    public function scopeWithLastMessage(Builder $query): Builder
    {
        return $query->with(['lastMessage' => function ($query) {
            $query->with('sender');
        }]);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('last_message_at', 'desc');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('messages');
    }

    public function scopeBetweenUsers(Builder $query, $nurseId, $studentId): Builder
    {
        return $query->where('nurse_id', $nurseId)
                    ->where('student_id', $studentId);
    }

    // Accessors
    public function getUnreadCountAttribute(): int
    {
        if (auth()->check()) {
            return $this->getUnreadCountForUser(auth()->id());
        }
        
        return 0;
    }

    public function getOtherParticipantAttribute()
    {
        if (auth()->check()) {
            return $this->getOtherParticipant(auth()->id());
        }
        
        return null;
    }

    public function getLastMessageTextAttribute(): ?string
    {
        return $this->lastMessage ? $this->lastMessage->short_message : null;
    }

    public function getLastMessageTimeAttribute(): ?string
    {
        return $this->lastMessage ? $this->lastMessage->formatted_created_at : null;
    }

    // Helper Methods
    public function getUnreadCountForUser($userId): int
    {
        return $this->messages()
                    ->where('sender_id', '!=', $userId)
                    ->where('is_read', false)
                    ->count();
    }

    public function getOtherParticipant($userId)
    {
        if ($this->nurse_id === $userId) {
            return $this->student;
        } elseif ($this->student_id === $userId) {
            return $this->nurse;
        }
        
        return null;
    }

    public function markMessagesAsRead($userId): bool
    {
        $updated = $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return $updated > 0;
    }

    public function addMessage($senderId, $message, $senderType = null): ChatMessage
    {
        $senderType = $senderType ?? User::find($senderId)->role;

        $chatMessage = $this->messages()->create([
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'message' => $message,
            'is_read' => false,
        ]);

        $this->update(['last_message_at' => now()]);

        return $chatMessage;
    }

    public function addSystemMessage($message): ChatMessage
    {
        return ChatMessage::createSystemMessage($this->id, $message);
    }

    public function getParticipantIds(): array
    {
        return [$this->nurse_id, $this->student_id];
    }

    public function isParticipant($userId): bool
    {
        return in_array($userId, $this->getParticipantIds());
    }

    public function canAccess($userId): bool
    {
        return $this->isParticipant($userId);
    }

    public function getLatestMessages($limit = 50): Collection
    {
        return $this->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse();
    }

    public function hasUnreadMessages($userId): bool
    {
        return $this->getUnreadCountForUser($userId) > 0;
    }

    public function getLastActivityAttribute(): ?string
    {
        return $this->last_message_at?->diffForHumans();
    }

    /**
     * Find or create conversation between nurse and student
     */
    public static function findOrCreateBetween($nurseId, $studentId): self
    {
        return self::firstOrCreate(
            [
                'nurse_id' => $nurseId,
                'student_id' => $studentId,
            ],
            [
                'last_message_at' => now(),
            ]
        );
    }

    /**
     * Get all conversations for user with pagination
     */
    public static function getUserConversations($userId, $perPage = 15)
    {
        return self::forUser($userId)
            ->withUnreadCount($userId)
            ->withLastMessage()
            ->with(['nurse', 'student'])
            ->recent()
            ->paginate($perPage);
    }
}