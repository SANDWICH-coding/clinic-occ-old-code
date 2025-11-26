{{-- resources/views/chat/student/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Chat with Campus Nurses')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Chat with Campus Nurses</h1>
            <p class="text-lg text-gray-600 mb-6">Get health advice and support from our nursing staff</p>
        </div>

        <!-- Health Notice -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <div class="flex items-start gap-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Important Notice</h3>
                    <p class="text-blue-700 mb-2">For medical emergencies, call campus security or visit the clinic immediately. Do not use chat for emergency situations.</p>
                    <p class="text-sm text-blue-600">Chat is for non-urgent health questions, symptom discussions, and appointment scheduling.</p>
                </div>
            </div>
        </div>

        <!-- Conversations List -->
        @if(isset($conversations) && $conversations->count() > 0)
        <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Your Conversations</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($conversations as $conversation)
                <a href="{{ route('chat.conversation', $conversation->id) }}" 
                   class="block p-6 hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($conversation->nurse->first_name ?? 'N', 0, 1)) }}{{ strtoupper(substr($conversation->nurse->last_name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $conversation->nurse->full_name ?? 'Nurse' }}</h3>
                                <p class="text-sm text-gray-600">
                                    {{ $conversation->nurse->department ?? 'Campus Nurse' }}
                                </p>
                                @if($conversation->lastMessage)
                                <p class="text-sm text-gray-500 mt-1 truncate max-w-md">
                                    {{ Str::limit($conversation->lastMessage->message, 50) }}
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            @if($conversation->last_message_at)
                            <p class="text-sm text-gray-500">
                                {{ $conversation->last_message_at->diffForHumans() }}
                            </p>
                            @endif
                            @if($conversation->unread_count > 0)
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full mt-1">
                                {{ $conversation->unread_count }}
                            </span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Start New Chat Card -->
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8 text-center">
            <div class="mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-blue-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    @if(isset($conversations) && $conversations->count() > 0)
                    Start New Conversation
                    @else
                    Start a Conversation
                    @endif
                </h2>
                <p class="text-gray-600 mb-6">Begin chatting with a campus nurse for health advice and support.</p>
            </div>

            <button onclick="startChat()" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Start New Chat
            </button>

            <div class="mt-6 text-sm text-gray-500">
                <p>You'll be connected with an available campus nurse. All conversations are confidential.</p>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <a href="{{ route('student.appointments.create') }}" 
               class="p-6 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-200 text-center group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-blue-600 mb-3 group-hover:text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="font-semibold text-gray-900 group-hover:text-blue-600">Schedule Appointment</h3>
                <p class="text-sm text-gray-600 mt-2">Book a face-to-face consultation</p>
            </a>

            <a href="{{ route('student.symptom-checker.index') }}" 
               class="p-6 bg-white rounded-lg border border-gray-200 hover:border-green-300 hover:shadow-md transition-all duration-200 text-center group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-green-600 mb-3 group-hover:text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="font-semibold text-gray-900 group-hover:text-green-600">Symptom Checker</h3>
                <p class="text-sm text-gray-600 mt-2">Check your symptoms online</p>
            </a>

            <a href="{{ route('student.medical-records.index') }}" 
               class="p-6 bg-white rounded-lg border border-gray-200 hover:border-purple-300 hover:shadow-md transition-all duration-200 text-center group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-purple-600 mb-3 group-hover:text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="font-semibold text-gray-900 group-hover:text-purple-600">Medical Records</h3>
                <p class="text-sm text-gray-600 mt-2">View your health history</p>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let refreshInterval;

function startChat() {
    const button = event.target.closest('button') || event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Connecting...';
    button.disabled = true;

    console.log('Starting chat...');

    fetch('{{ route("chat.get-or-create-conversation") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            return response.json().then(errorData => {
                console.error('Error data:', errorData);
                throw new Error(errorData.error || `HTTP error! Status: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success data:', data);
        
        if (data.success && data.redirect_url) {
            console.log('Redirecting to:', data.redirect_url);
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 300);
        } else {
            throw new Error(data.error || 'Failed to start conversation');
        }
    })
    .catch(error => {
        console.error('Catch error:', error);
        alert('Failed to start conversation: ' + error.message + '\n\nPlease make sure:\n1. You are logged in\nn2. There are active nurses available\n3. Try refreshing the page');
        
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// AUTO-REFRESH CONVERSATIONS EVERY 5 SECONDS
async function refreshConversations() {
    const conversationsContainer = document.querySelector('.divide-y.divide-gray-100');
    if (!conversationsContainer) return;

    try {
        const response = await fetch('{{ route('chat.index') }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to fetch conversations');
        
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newConversations = doc.querySelector('.divide-y.divide-gray-100');
        
        if (newConversations) {
            conversationsContainer.innerHTML = newConversations.innerHTML;
            console.log('Conversations refreshed at:', new Date().toLocaleTimeString());
        }
    } catch (error) {
        console.error('Error refreshing conversations:', error);
    }
}

// Initialize auto-refresh when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Start auto-refresh every 5 seconds
    refreshInterval = setInterval(refreshConversations, 5000);

    // Stop auto-refresh when user leaves the page
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });

    // Pause auto-refresh when tab is not visible to save resources
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        } else {
            if (!refreshInterval) {
                refreshInterval = setInterval(refreshConversations, 5000);
                refreshConversations(); // Refresh immediately when tab becomes visible
            }
        }
    });
});

// Check if there are any error messages from redirect
@if(session('error'))
    alert('{{ session('error') }}');
@endif

@if(session('success'))
    console.log('Success: {{ session('success') }}');
@endif
</script>
@endpush