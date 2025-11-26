<?php
// app/Models/ChatMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'message_type', 
        'formatted_created_at', 
        'short_message',
        'is_own_message',
        'sender_name'
    ];

    // Constants for message types
    const TYPE_TEXT = 'text';
    const TYPE_SYSTEM = 'system';

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id')->withDefault([
            'first_name' => 'Unknown',
            'last_name' => 'User',
            'role' => 'unknown',
            'full_name' => 'Unknown User'
        ]);
    }

    // Accessors
    public function getMessageTypeAttribute(): string
    {
        if ($this->sender_type === 'system') {
            return self::TYPE_SYSTEM;
        }

        return self::TYPE_TEXT;
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('M j, Y g:i A') : 'Unknown time';
    }

    public function getShortMessageAttribute(): string
    {
        if (empty($this->message)) {
            return 'No message content';
        }
        return str($this->message)->limit(50)->toString();
    }

    public function getIsOwnMessageAttribute(): bool
    {
        return $this->sender_id === auth()->id();
    }

    public function getSenderNameAttribute(): string
    {
        if ($this->sender_type === 'system') {
            return 'System';
        }
        
        return $this->sender ? $this->sender->full_name : 'Unknown User';
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at ? $this->created_at->diffForHumans() : 'Unknown';
    }

    // Scopes
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    public function scopeForReceiver(Builder $query, $receiverId): Builder
    {
        return $query->where('sender_id', '!=', $receiverId);
    }

    public function scopeForSender(Builder $query, $senderId): Builder
    {
        return $query->where('sender_id', $senderId);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeConversation(Builder $query, $conversationId): Builder
    {
        return $query->where('conversation_id', $conversationId);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeWithConversation(Builder $query): Builder
    {
        return $query->with(['conversation' => function($query) {
            $query->with(['nurse', 'student']);
        }]);
    }

    public function scopeWithSender(Builder $query): Builder
    {
        return $query->with(['sender' => function($query) {
            $query->select('id', 'first_name', 'last_name', 'role', 'student_id', 'email');
        }]);
    }

    // Helper Methods
    public function markAsRead(): bool
    {
        if (!$this->is_read) {
            return $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return true;
    }

    public function markAsUnread(): bool
    {
        if ($this->is_read) {
            return $this->update([
                'is_read' => false,
                'read_at' => null,
            ]);
        }

        return true;
    }

    public function isFromUser($userId): bool
    {
        return (int) $this->sender_id === (int) $userId;
    }

    public function isUnread(): bool
    {
        return !$this->is_read;
    }

    public function isText(): bool
    {
        return $this->message_type === self::TYPE_TEXT;
    }

    public function isSystem(): bool
    {
        return $this->message_type === self::TYPE_SYSTEM;
    }

    /**
     * Get the receiver ID for this message
     */
    public function getReceiverId(): int
    {
        $conversation = $this->conversation;
        
        if (!$conversation) {
            return 0;
        }

        return (int) $this->sender_id === (int) $conversation->nurse_id 
            ? (int) $conversation->student_id 
            : (int) $conversation->nurse_id;
    }

    /**
     * Check if message can be deleted by user
     */
    public function canBeDeletedBy($userId): bool
    {
        return (int) $this->sender_id === (int) $userId;
    }

    /**
     * Soft delete message (mark as deleted)
     */
    public function softDelete(): bool
    {
        return $this->update([
            'message' => 'Message deleted',
            'deleted_at' => now(),
        ]);
    }

    /**
     * Check if message is owned by user
     */
    public function isOwnedBy($userId): bool
    {
        return (int) $this->sender_id === (int) $userId;
    }

    /**
     * Get the other participant in conversation
     */
    public function getOtherParticipant(): ?User
    {
        $conversation = $this->conversation;
        
        if (!$conversation) {
            return null;
        }

        return (int) $this->sender_id === (int) $conversation->nurse_id 
            ? $conversation->student 
            : $conversation->nurse;
    }

    /**
     * Create a system message
     */
    public static function createSystemMessage($conversationId, $message): self
    {
        return self::create([
            'conversation_id' => $conversationId,
            'sender_id' => 0,
            'sender_type' => 'system',
            'message' => $message,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get message status for display
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_read) {
            return 'read';
        }

        return 'sent';
    }

    /**
     * Check if message should be displayed as system message
     */
    public function shouldDisplayAsSystem(): bool
    {
        return $this->isSystem() || $this->sender_type === 'system';
    }

    /**
     * Check if message is recent (within last 5 minutes)
     */
    public function isRecent(): bool
    {
        return $this->created_at && $this->created_at->diffInMinutes(now()) < 5;
    }

    /**
     * Get formatted message for display
     */
    public function getDisplayMessage(): string
    {
        if ($this->isSystem()) {
            return '<em>' . e($this->message) . '</em>';
        }

        return nl2br(e($this->message));
    }

    /**
     * Get message preview (truncated)
     */
    public function getPreview(int $length = 100): string
    {
        if (empty($this->message)) {
            return 'No message';
        }

        return str($this->message)->limit($length);
    }

    /**
     * Check if message is empty
     */
    public function isEmpty(): bool
    {
        return empty(trim($this->message ?? ''));
    }

    /**
     * Get message length
     */
    public function getLength(): int
    {
        return mb_strlen($this->message ?? '');
    }

    /**
     * Check if message is too long
     */
    public function isTooLong(int $maxLength = 5000): bool
    {
        return $this->getLength() > $maxLength;
    }

    /**
     * Get conversation participant IDs
     */
    public function getParticipantIds(): array
    {
        $conversation = $this->conversation;
        
        if (!$conversation) {
            return [];
        }

        return [
            'nurse_id' => (int) $conversation->nurse_id,
            'student_id' => (int) $conversation->student_id
        ];
    }

    /**
     * Check if user is participant in this message's conversation
     */
    public function isUserParticipant($userId): bool
    {
        $participants = $this->getParticipantIds();
        
        return in_array((int) $userId, $participants);
    }

    /**
     * Get message data for API response
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'sender_type' => $this->sender_type,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'message_type' => $this->message_type,
            'formatted_created_at' => $this->formatted_created_at,
            'short_message' => $this->short_message,
            'is_own_message' => $this->is_own_message,
            'sender_name' => $this->sender_name,
            'time_ago' => $this->time_ago,
            'status' => $this->status,
            'sender' => $this->sender ? [
                'id' => $this->sender->id,
                'first_name' => $this->sender->first_name,
                'last_name' => $this->sender->last_name,
                'full_name' => $this->sender->full_name,
                'role' => $this->sender->role,
                'student_id' => $this->sender->student_id,
                'email' => $this->sender->email,
            ] : null
        ];
    }

    /**
     * Mark multiple messages as read
     */
    public static function markMultipleAsRead(array $messageIds, $userId): int
    {
        return self::whereIn('id', $messageIds)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get unread messages count for user in conversation
     */
    public static function getUnreadCountForUser($conversationId, $userId): int
    {
        return self::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get last message in conversation
     */
    public static function getLastMessage($conversationId): ?self
    {
        return self::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get messages for conversation with pagination
     */
    public static function getConversationMessages($conversationId, $perPage = 50)
    {
        return self::where('conversation_id', $conversationId)
            ->withSender()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set sender_type if not provided
        static::creating(function ($message) {
            if (empty($message->sender_type) && $message->sender_id) {
                $user = User::find($message->sender_id);
                if ($user) {
                    $message->sender_type = $user->role;
                }
            }
        });

        // Update conversation's last_message_at when message is created
        static::created(function ($message) {
            $message->conversation->update([
                'last_message_at' => $message->created_at,
            ]);
        });
    }
}