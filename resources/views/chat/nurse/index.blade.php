{{-- resources/views/chat/nurse/index.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Chat - Messages')

@section('content')
<div class="container-fluid h-screen flex flex-col">
    <div class="bg-white rounded-xl shadow-lg flex flex-1 overflow-hidden">
        <!-- Sidebar - Conversations List -->
        <div class="w-full md:w-80 lg:w-96 border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-indigo-600 flex-shrink-0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-white">Messages</h2>
                    <span id="unread-badge" class="hidden px-2 py-1 bg-red-500 text-white text-xs rounded-full">0</span>
                </div>
                
                <!-- Search Students -->
                <div class="relative">
                    <input 
                        type="text" 
                        id="student-search" 
                        placeholder="Search students by name or ID..." 
                        class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all bg-white/90 backdrop-blur-sm"
                        autocomplete="off"
                        aria-label="Search students"
                    >
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    
                    <!-- Search Results Dropdown -->
                    <div id="search-results" class="absolute w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-xl max-h-80 overflow-y-auto hidden z-20">
                        <div class="p-2 text-center text-gray-500 text-sm">
                            Type to search students...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversations List -->
            <div class="flex-1 overflow-y-auto" id="conversations-list" style="height: calc(100vh - 180px);">
                @forelse($conversations as $conversation)
                    <a href="{{ route('chat.conversation', $conversation->id) }}" 
                       class="flex items-center p-4 hover:bg-gray-100 border-b border-gray-100 transition-colors duration-200 conversation-item {{ request()->route('conversation') == $conversation->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}"
                       data-conversation-id="{{ $conversation->id }}"
                       aria-label="Conversation with {{ $conversation->student->full_name }}"
                    >
                        <!-- Student Avatar -->
                        <div class="relative flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white font-semibold shadow-md">
                                {{ strtoupper(substr($conversation->student->first_name, 0, 1)) }}{{ strtoupper(substr($conversation->student->last_name, 0, 1)) }}
                            </div>
                            <!-- Online Status -->
                            <span class="absolute bottom-0 right-0 h-3 w-3 {{ $conversation->student->is_online ? 'bg-green-400' : 'bg-gray-400' }} border-2 border-white rounded-full user-status" data-user-id="{{ $conversation->student->id }}"></span>
                        </div>

                        <!-- Student Info -->
                        <div class="ml-3 flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $conversation->student->full_name }}
                                </p>
                                @if($conversation->last_message_at)
                                    <span class="text-xs text-gray-500 ml-2 flex-shrink-0">
                                        {{ $conversation->last_message_at->diffForHumans(null, true) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">{{ $conversation->student->student_id }} • {{ $conversation->student->course }}</p>
                            @if($conversation->lastMessage)
                                <p class="text-sm text-gray-600 truncate mt-1">
                                    @if($conversation->lastMessage->sender_id == auth()->id())
                                        <span class="text-blue-500 font-medium">You: </span>
                                    @endif
                                    {{ $conversation->lastMessage->message ? Str::limit($conversation->lastMessage->message, 40) : '📷 Image' }}
                                </p>
                            @else
                                <p class="text-sm text-gray-400 italic">No messages yet</p>
                            @endif
                        </div>

                        <!-- Unread Badge -->
                        @if($conversation->unread_count > 0)
                            <div class="ml-2 flex-shrink-0">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-blue-500 text-white text-xs font-bold shadow-sm unread-count">
                                    {{ $conversation->unread_count > 99 ? '99+' : $conversation->unread_count }}
                                </span>
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="p-8 text-center" id="empty-state">
                        <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No conversations yet</h3>
                        <p class="mt-2 text-sm text-gray-500">Search for students above to start a new conversation</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 hidden md:flex items-center justify-center bg-gray-50">
            <div class="text-center p-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-100 mb-4">
                    <svg class="h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Select a conversation</h3>
                <p class="text-gray-600 max-w-sm">Choose a student from the list or search for a new student to start messaging</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Force full height */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;
}

.container-fluid {
    height: 100vh !important;
    max-height: 100vh !important;
    display: flex;
    flex-direction: column;
}

/* Conversations List - FIXED SCROLLBAR */
#conversations-list {
    overflow-y: auto !important;
    overflow-x: hidden !important;
    height: calc(100vh - 180px) !important;
    max-height: none !important;
    min-height: 200px;
    display: block !important;
    position: relative;
}

/* Make sure the scrollbar is always visible and functional */
#conversations-list {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f8fafc;
}

/* Webkit Scrollbar Styling */
#conversations-list::-webkit-scrollbar {
    width: 8px;
    background: #f8fafc;
}

#conversations-list::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 4px;
}

#conversations-list::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 4px;
    border: 2px solid #f8fafc;
}

#conversations-list::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

#conversations-list::-webkit-scrollbar-thumb:active {
    background: #64748b;
}

/* Firefox Scrollbar */
#conversations-list {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f8fafc;
}

/* Ensure conversation items don't cause overflow */
.conversation-item {
    min-height: 72px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

/* Search Results Scrollbar */
#search-results::-webkit-scrollbar {
    width: 6px;
}

#search-results::-webkit-scrollbar-track {
    background: #f1f5f9;
}

#search-results::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 3px;
}

#search-results::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Remove any max-height restrictions from parent elements */
.bg-white.rounded-xl.shadow-lg {
    height: 100% !important;
    max-height: none !important;
}

.w-full.md\:w-80.lg\:w-96 {
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .container-fluid {
        height: 100vh !important;
    }
    
    #conversations-list {
        height: calc(100vh - 160px) !important;
    }
    
    /* Thinner scrollbar for mobile */
    #conversations-list::-webkit-scrollbar {
        width: 6px;
    }
}

/* Ensure no parent is hiding overflow incorrectly */
.flex-1.overflow-hidden {
    overflow: hidden !important;
}

/* But conversations list should overflow */
#conversations-list {
    overflow-y: auto !important;
}

/* Force the container to allow scrolling */
.flex.flex-col {
    min-height: 0; /* This is important for flex children to scroll */
}

/* Auto-refresh indicator */
.refresh-indicator {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(59, 130, 246, 0.9);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    z-index: 10;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* Loading state for conversations */
.conversations-loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Debug borders - remove if not needed */
/* #conversations-list {
    border: 2px solid red;
} */
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const conversationsList = document.getElementById('conversations-list');
    const searchInput = document.getElementById('student-search');
    const searchResults = document.getElementById('search-results');
    let searchTimeout;
    let refreshInterval;
    let isRefreshing = false;

    // Debug: Check if scrollbar is working
    console.log('Conversations List Scroll Info:', {
        scrollHeight: conversationsList.scrollHeight,
        clientHeight: conversationsList.clientHeight,
        scrollable: conversationsList.scrollHeight > conversationsList.clientHeight,
        overflowStyle: window.getComputedStyle(conversationsList).overflowY
    });

    // Force scrollbar to be functional
    conversationsList.style.overflowY = 'scroll';
    conversationsList.style.height = 'calc(100vh - 180px)';

    // If still not working, try a more aggressive approach
    setTimeout(() => {
        conversationsList.style.display = 'none';
        setTimeout(() => {
            conversationsList.style.display = 'block';
        }, 100);
    }, 500);

    // Search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            searchResults.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Type at least 2 characters...</div>';
            searchResults.classList.add('hidden');
            return;
        }
        
        searchResults.classList.remove('hidden');
        searchResults.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Searching...</div>';
        
        searchTimeout = setTimeout(() => {
            searchStudents(query);
        }, 500);
    });

    async function searchStudents(query) {
        try {
            const response = await fetch(`{{ route('chat.search-students') }}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.success && data.students) {
                displaySearchResults(data.students);
            } else {
                searchResults.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">No students found</div>';
            }
        } catch (error) {
            console.error('Search error:', error);
            searchResults.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Search failed</div>';
        }
    }

    function displaySearchResults(students) {
        if (students.length === 0) {
            searchResults.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">No students found</div>';
            return;
        }
        
        const html = students.map(student => `
            <button 
                onclick="startConversation(${student.id})" 
                class="w-full flex items-center p-3 hover:bg-blue-50 border-b border-gray-100 text-left transition-colors duration-200"
                aria-label="Start conversation with ${student.first_name} ${student.last_name}"
            >
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                    ${student.first_name?.charAt(0) || ''}${student.last_name?.charAt(0) || ''}
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${student.first_name} ${student.last_name}</p>
                    <p class="text-xs text-gray-500 truncate">${student.student_id} • ${student.course || 'N/A'}</p>
                </div>
            </button>
        `).join('');
        
        searchResults.innerHTML = html;
    }

    window.startConversation = async function(studentId) {
        try {
            const response = await fetch('{{ route('chat.get-or-create-conversation') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ student_id: studentId })
            });
            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                alert('Failed to start conversation: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to start conversation');
        }
    };

    // Close search results on outside click
    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // AUTO-REFRESH CONVERSATIONS EVERY 5 SECONDS
    async function refreshConversations() {
        if (isRefreshing) return;
        
        isRefreshing = true;
        
        try {
            // Add loading state
            conversationsList.classList.add('conversations-loading');
            
            const response = await fetch('{{ route('chat.index') }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                cache: 'no-cache'
            });
            
            if (!response.ok) throw new Error('Failed to fetch conversations');
            
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newConversationsList = doc.getElementById('conversations-list');
            
            if (newConversationsList) {
                // Store current scroll position and active conversation
                const scrollPos = conversationsList.scrollTop;
                const activeConversation = document.querySelector('.conversation-item.bg-blue-50');
                const activeConversationId = activeConversation ? activeConversation.dataset.conversationId : null;
                
                // Update conversations list
                conversationsList.innerHTML = newConversationsList.innerHTML;
                
                // Restore active state if still present
                if (activeConversationId) {
                    const newActiveItem = conversationsList.querySelector(`[data-conversation-id="${activeConversationId}"]`);
                    if (newActiveItem) {
                        newActiveItem.classList.add('bg-blue-50', 'border-l-4', 'border-l-blue-500');
                    }
                }
                
                // Restore scroll position
                conversationsList.scrollTop = scrollPos;
                
                console.log('Conversations refreshed at:', new Date().toLocaleTimeString());
            }
        } catch (error) {
            console.error('Error refreshing conversations:', error);
        } finally {
            isRefreshing = false;
            conversationsList.classList.remove('conversations-loading');
        }
    }

    // Start auto-refresh
    function startAutoRefresh() {
        if (!refreshInterval) {
            refreshInterval = setInterval(refreshConversations, 5000);
            console.log('Auto-refresh started');
        }
    }

    // Stop auto-refresh
    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
            console.log('Auto-refresh stopped');
        }
    }

    // Initialize auto-refresh
    startAutoRefresh();

    // Stop auto-refresh when user leaves the page
    window.addEventListener('beforeunload', stopAutoRefresh);

    // Pause auto-refresh when tab is not visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
            refreshConversations(); // Refresh immediately when tab becomes visible
        }
    });

    // Also handle page focus/blur for better performance
    window.addEventListener('focus', () => {
        startAutoRefresh();
        refreshConversations();
    });

    window.addEventListener('blur', stopAutoRefresh);

    // Update unread count
    async function updateUnreadCount() {
        try {
            const response = await fetch('{{ route('chat.unread-count') }}', {
                cache: 'no-cache'
            });
            const data = await response.json();
            const badge = document.getElementById('unread-badge');
            
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        } catch (error) {
            console.error('Unread count error:', error);
        }
    }

    // Update unread count every 30 seconds
    setInterval(updateUnreadCount, 30000);
    updateUnreadCount();

    // Manual refresh trigger (for debugging)
    window.manualRefresh = refreshConversations;
});
</script>
@endpush