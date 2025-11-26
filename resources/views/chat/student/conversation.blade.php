{{-- resources/views/chat/student/conversation.blade.php --}}
@extends('layouts.app')

@section('title', 'Chat with ' . ($otherParticipant->full_name ?? 'Nurse'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-t-lg shadow-md p-4 sm:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 space-y-2 sm:space-y-0">
                <a href="{{ route('student.dashboard') }}"
                   class="flex items-center gap-2 text-blue-600 hover:text-blue-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="font-semibold text-sm sm:text-base">Back to Dashboard</span>
                </a>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-green-100 text-green-800 px-3 py-1 rounded-full font-medium">
                        <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                        Active Chat
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold shadow-lg text-base sm:text-lg">
                    {{ strtoupper(substr($otherParticipant->first_name ?? 'N', 0, 1)) }}{{ strtoupper(substr($otherParticipant->last_name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <h2 class="font-bold text-lg sm:text-xl text-gray-800">{{ $otherParticipant->full_name ?? 'Campus Nurse' }}</h2>
                    <p class="text-xs sm:text-sm text-gray-600 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ $otherParticipant->department ?? 'Campus Nurse' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-b-lg shadow-md flex flex-col"
             style="height: calc(100vh - 200px); min-height: 400px; max-height: 600px;">
            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-3 sm:p-6 space-y-4 bg-gray-50"
                 id="messages-container"
                 style="max-height: calc(100vh - 260px);">
                @forelse($messages ?? [] as $message)
                    <div class="flex {{ ($message->sender_id ?? 0) == auth()->id() ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id ?? '' }}">
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg rounded-lg px-4 py-3 {{ ($message->sender_id ?? 0) == auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }} shadow-sm">
                            @if($message->message ?? false)
                                <p class="text-sm whitespace-pre-wrap break-words">{!! nl2br(e($message->message)) !!}</p>
                            @endif
                            <p class="text-xs mt-1 opacity-70 {{ ($message->sender_id ?? 0) == auth()->id() ? 'text-blue-100' : 'text-gray-600' }}">
                                {{ ($message->created_at ?? now())->format('M d, g:i A') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-8 sm:py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 sm:h-16 sm:w-16 mx-auto mb-4 text-gray-300"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-base sm:text-lg font-medium">No messages yet</p>
                        <p class="text-xs sm:text-sm">Send a message to start the conversation</p>
                    </div>
                @endforelse
            </div>

            <!-- Message Input -->
            <div class="p-3 sm:p-4 border-t border-gray-200 bg-white">
                <form id="message-form" class="flex gap-2">
                    @csrf
                    <input type="hidden" name="conversation_id" value="{{ $conversation->id ?? '' }}">
                    <div class="flex-1">
                        <input
                            type="text"
                            name="message"
                            id="message-input"
                            placeholder="Type your message..."
                            class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base"
                            autocomplete="off"
                            maxlength="1000"
                            aria-label="Type your message"
                        >
                    </div>
                    <button
                        type="submit"
                        class="px-4 py-2 sm:px-6 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-semibold text-sm sm:text-base"
                        id="send-button"
                        aria-label="Send message"
                    >
                        Send
                    </button>
                </form>
                <p class="text-xs text-gray-500 mt-2">For emergencies, call campus security or visit the clinic immediately.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const messagesContainer = document.getElementById('messages-container');
    const sendButton = document.getElementById('send-button');
    const conversationId = '{{ $conversation->id ?? "" }}';
    let refreshInterval;
    let isUserScrolling = false;
    let scrollTimeout;
    const REFRESH_INTERVAL = 3000; // 3 seconds

    // Enhanced scroll to bottom function
    const scrollToBottom = (force = false) => {
        if (messagesContainer && (!isUserScrolling || force)) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    };
    
    // Initial scroll with delay to ensure DOM is fully rendered
    setTimeout(() => scrollToBottom(true), 300);

    // Detect if user is manually scrolling
    if (messagesContainer) {
        messagesContainer.addEventListener('scroll', () => {
            const isAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop <= messagesContainer.clientHeight + 100;
            isUserScrolling = !isAtBottom;
            
            // Reset scrolling flag after 3 seconds of no scrolling
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                isUserScrolling = false;
            }, 3000);
        });
    }

    // AUTO-REFRESH MESSAGES EVERY 3 SECONDS
    async function refreshMessages() {
        if (!conversationId) return;

        try {
            const response = await fetch(`/api/chat/${conversationId}/messages`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                // Add cache-busting parameter to prevent caching
                cache: 'no-cache'
            });

            if (!response.ok) throw new Error('Failed to fetch messages');

            const data = await response.json();

            if (data.success && data.messages) {
                updateMessagesUI(data.messages);
            }
        } catch (error) {
            console.error('Error refreshing messages:', error);
        }
    }

    // Update messages in the UI
    function updateMessagesUI(messages) {
        if (!messagesContainer) return;

        // Store current message IDs
        const currentMessageIds = Array.from(messagesContainer.querySelectorAll('[data-message-id]'))
            .map(el => el.getAttribute('data-message-id'));

        // Check if there are new messages
        const newMessages = messages.filter(msg => !currentMessageIds.includes(msg.id.toString()));

        if (newMessages.length > 0) {
            // Add new messages
            newMessages.forEach(message => {
                addMessageToUI(message, false);
            });

            // Scroll to bottom if user is not scrolling
            scrollToBottom();
            
            console.log(`${newMessages.length} new message(s) loaded at:`, new Date().toLocaleTimeString());
        }
    }

    // Handle form submission
    if (messageForm) {
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput?.value.trim();

            if (!message) {
                alert('Please enter a message');
                return;
            }

            if (!conversationId) {
                alert('Error: Conversation not found');
                return;
            }

            if (sendButton) {
                sendButton.disabled = true;
                sendButton.textContent = 'Sending...';
            }

            try {
                const response = await fetch(`/api/chat/${conversationId}/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.error || `HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    if (messageInput) messageInput.value = '';
                    if (data.message) {
                        addMessageToUI(data.message, true);
                        scrollToBottom(true);
                    }
                } else {
                    throw new Error(data.error || 'Failed to send message');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(`Failed to send message: ${error.message}`);
            } finally {
                if (sendButton) {
                    sendButton.disabled = false;
                    sendButton.textContent = 'Send';
                }
                if (messageInput) messageInput.focus();
            }
        });
    }

    // Add message to UI
    const addMessageToUI = (message, isNewlySent = false) => {
        if (!messagesContainer || !message) return;

        // Check if message already exists
        const existingMessage = messagesContainer.querySelector(`[data-message-id="${message.id}"]`);
        if (existingMessage) return;

        // Remove empty state if it exists
        const emptyState = messagesContainer.querySelector('.text-center.text-gray-500');
        if (emptyState) {
            emptyState.remove();
        }

        const messageDiv = document.createElement('div');
        const isOwnMessage = (message.sender_id || 0) === {{ auth()->id() }};
        messageDiv.className = `flex ${isOwnMessage ? 'justify-end' : 'justify-start'}`;
        messageDiv.setAttribute('data-message-id', message.id);

        let messageContent = '';
        if (message.message) {
            messageContent += `<p class="text-sm whitespace-pre-wrap break-words">${sanitizeMessage(message.message)}</p>`;
        }
        
        const timeText = isNewlySent ? 'Just now' : formatMessageTime(message.created_at);
        
        messageContent += `
            <p class="text-xs mt-1 opacity-70 ${isOwnMessage ? 'text-blue-100' : 'text-gray-600'}">
                ${timeText}
            </p>
        `;

        messageDiv.innerHTML = `
            <div class="max-w-xs sm:max-w-md lg:max-w-lg rounded-lg px-4 py-3 ${isOwnMessage ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800'} shadow-sm">
                ${messageContent}
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
    };

    // Format message time
    const formatMessageTime = (timestamp) => {
        try {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;

            // Format as "Nov 24, 8:45 PM"
            const options = { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };
            return date.toLocaleString('en-US', options);
        } catch (e) {
            return timestamp;
        }
    };

    // Sanitize message to prevent XSS
    const sanitizeMessage = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    };

    // Handle Enter key for sending message
    if (messageInput) {
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (messageForm) {
                    messageForm.dispatchEvent(new Event('submit'));
                }
            }
        });
    }

    // Focus input
    if (messageInput) {
        messageInput.focus();
    }

    // Start auto-refresh every 3 seconds
    if (conversationId) {
        refreshInterval = setInterval(refreshMessages, REFRESH_INTERVAL);
        console.log('✅ Auto-refresh started for conversation:', conversationId, 'Interval:', REFRESH_INTERVAL + 'ms');
        
        // Initial refresh
        refreshMessages();
    }

    // Stop auto-refresh when user leaves the page
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            console.log('🛑 Auto-refresh stopped');
        }
    });

    // Pause auto-refresh when tab is not visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
                console.log('⏸️ Auto-refresh paused (tab hidden)');
            }
        } else {
            if (!refreshInterval && conversationId) {
                refreshInterval = setInterval(refreshMessages, REFRESH_INTERVAL);
                refreshMessages(); // Refresh immediately when tab becomes visible
                console.log('▶️ Auto-refresh resumed (tab visible)');
            }
        }
    });

    // WebSocket for real-time updates (only if Echo is available)
    if (typeof Echo !== 'undefined' && conversationId) {
        try {
            Echo.private(`chat.${conversationId}`)
                .listen('MessageSent', (e) => {
                    if (e.message && (e.message.sender_id || 0) !== {{ auth()->id() }}) {
                        addMessageToUI(e.message, false);
                        scrollToBottom();
                    }
                });
        } catch (error) {
            console.error('WebSocket error:', error);
        }
    }

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        clearTimeout(scrollTimeout);
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
});
</script>
@endpush