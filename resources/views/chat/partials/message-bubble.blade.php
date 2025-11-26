{{-- resources/views/chat/partials/message-bubble.blade.php --}}
@php
    $isOwn = $message->sender_id == auth()->id();
@endphp

<div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}">
    <div class="max-w-xs lg:max-w-md xl:max-w-lg">
        @if(!$isOwn)
            <p class="text-xs text-gray-500 mb-1 ml-1">{{ $message->sender->full_name }}</p>
        @endif
        
        <div class="rounded-2xl px-4 py-2 {{ $isOwn ? 'bg-blue-600 text-white rounded-br-sm' : 'bg-white text-gray-900 border border-gray-200 rounded-bl-sm shadow-sm' }}">
            @if($message->image_path)
                <img src="{{ Storage::url($message->image_path) }}" 
                     alt="Image" 
                     class="rounded-lg mb-2 max-w-full cursor-pointer hover:opacity-90 transition-opacity"
                     onclick="openImageModal('{{ Storage::url($message->image_path) }}')"
                     loading="lazy">
            @endif
            
            @if($message->message)
                <p class="text-sm whitespace-pre-wrap break-words leading-relaxed">{{ $message->message }}</p>
            @endif
            
            <div class="flex items-center justify-end mt-1 space-x-1">
                <span class="text-xs {{ $isOwn ? 'text-blue-200' : 'text-gray-500' }}">
                    {{ $message->created_at->format('g:i A') }}
                </span>
                @if($isOwn)
                    <span class="text-xs {{ $message->is_read ? 'text-blue-200' : 'text-blue-300' }}" title="{{ $message->is_read ? 'Read' : 'Sent' }}">
                        {{ $message->is_read ? '✓✓' : '✓' }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>