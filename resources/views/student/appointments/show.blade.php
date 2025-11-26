@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Appointment Details</h1>
            <a href="{{ route('student.appointments.index') }}" 
               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-150">
                Back to Appointments
            </a>
        </div>

        {{-- Status Alert --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        {{-- Appointment Card --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            {{-- Status Header --}}
            <div class="px-6 py-4 border-b {{ $appointment->status_badge_class }}">
                <div class="flex justify-between items-center">
                    <span class="font-semibold">{{ $appointment->status_display }}</span>
                    <span class="text-sm">{{ $appointment->formatted_date }}</span>
                </div>
            </div>

            {{-- Appointment Details --}}
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Left Column --}}
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Appointment Information</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Date:</span>
                                <p class="text-gray-800">{{ $appointment->formatted_date }}</p>
                            </div>
                            
                            @if($appointment->appointment_time)
                            <div>
                                <span class="text-sm font-medium text-gray-600">Time:</span>
                                <p class="text-gray-800">{{ $appointment->formatted_time }}</p>
                            </div>
                            @endif
                            
                            <div>
                                <span class="text-sm font-medium text-gray-600">Reason:</span>
                                <p class="text-gray-800">{{ $appointment->reason }}</p>
                            </div>
                            
                            @php
                                // Filter out unwanted lines from notes
                                $notesDisplay = $appointment->notes;
                                if ($notesDisplay) {
                                    $lines = explode("\n", $notesDisplay);
                                    $filteredLines = array_filter($lines, function($line) {
                                        return !str_contains($line, 'Possible Conditions:') && 
                                               !str_contains($line, 'EMERGENCY FLAGGED:') &&
                                               !str_contains($line, 'Checked:');
                                    });
                                    $notesDisplay = implode("\n", $filteredLines);
                                }
                            @endphp
                            
                            @if($notesDisplay)
                            <div>
                                <span class="text-sm font-medium text-gray-600">Notes:</span>
                                <p class="text-gray-800 whitespace-pre-wrap">{{ trim($notesDisplay) }}</p>
                            </div>
                            @endif
                            
                            @if($appointment->preferred_time)
                            <div>
                                <span class="text-sm font-medium text-gray-600">Preferred Time:</span>
                                <p class="text-gray-800 capitalize">{{ $appointment->preferred_time }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Information</h3>
                        
                        <div class="space-y-3">
                            @if($appointment->accepted_by && $appointment->accepted_at)
                            <div>
                                <span class="text-sm font-medium text-gray-600">Accepted by:</span>
                                <p class="text-gray-800">{{ $appointment->acceptedBy->name ?? 'Nurse' }}</p>
                                <p class="text-sm text-gray-500">{{ $appointment->accepted_at->format('M d, Y g:i A') }}</p>
                            </div>
                            @endif

                            @if($appointment->rescheduled_by && $appointment->rescheduled_at)
                            <div>
                                <span class="text-sm font-medium text-gray-600">Rescheduled by:</span>
                                <p class="text-gray-800">{{ $appointment->rescheduledBy->name ?? 'Nurse' }}</p>
                                <p class="text-sm text-gray-500">{{ $appointment->rescheduled_at->format('M d, Y g:i A') }}</p>
                                @if($appointment->reschedule_reason)
                                <p class="text-sm text-gray-600 mt-1">
                                    <span class="font-medium">Reason:</span> {{ $appointment->reschedule_reason }}
                                </p>
                                @endif
                            </div>
                            @endif

                            @if($appointment->completed_by && $appointment->completed_at)
                            <div>
                                <span class="text-sm font-medium text-gray-600">Completed by:</span>
                                <p class="text-gray-800">{{ $appointment->completedBy->name ?? 'Nurse' }}</p>
                                <p class="text-sm text-gray-500">{{ $appointment->completed_at->format('M d, Y g:i A') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-8 pt-6 border-t border-gray-200">
                    @if($appointment->isRescheduled() && $appointment->requires_student_confirmation)
                        {{-- Confirm Reschedule --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                            <p class="text-blue-800 mb-3">The nurse has rescheduled your appointment. Please confirm or request a different time.</p>
                            <div class="flex space-x-3">
                                <form action="{{ route('student.appointments.confirm-reschedule', $appointment) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
                                        Confirm Reschedule
                                    </button>
                                </form>
                                <button onclick="openRescheduleModal()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-md transition duration-150">
                                    Request Different Time
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($appointment->isFollowUpPending())
                        {{-- Accept Follow-up --}}
                        <div class="bg-purple-50 border border-purple-200 rounded-md p-4 mb-4">
                            <p class="text-purple-800 mb-3">A follow-up appointment has been scheduled. Please confirm your availability.</p>
                            <div class="flex space-x-3">
                                <form action="{{ route('student.appointments.accept-followup', $appointment) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition duration-150">
                                        Accept Follow-up
                                    </button>
                                </form>
                                <button onclick="openFollowUpRescheduleModal()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-md transition duration-150">
                                    Request Reschedule
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($appointment->canBeCancelled())
                        <div class="flex justify-end">
                            <button onclick="openCancelModal()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition duration-150">
                                Cancel Appointment
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Consultation Details (if completed) --}}
        @if($appointment->isCompleted())
        <div class="bg-white rounded-lg shadow-md mt-6 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Consultation Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($appointment->diagnosis)
                <div>
                    <span class="text-sm font-medium text-gray-600">Diagnosis:</span>
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $appointment->diagnosis }}</p>
                </div>
                @endif
                
                @if($appointment->treatment_given)
                <div>
                    <span class="text-sm font-medium text-gray-600">Treatment Given:</span>
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $appointment->treatment_given }}</p>
                </div>
                @endif
                
                @if($appointment->advice_given)
                <div class="md:col-span-2">
                    <span class="text-sm font-medium text-gray-600">Medical Advice:</span>
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $appointment->advice_given }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Reschedule Request Modal --}}
<div id="rescheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Request Reschedule</h3>
        <form action="{{ route('student.appointments.request-reschedule', $appointment) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="reschedule_request_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for reschedule request <span class="text-red-500">*</span>
                </label>
                <textarea id="reschedule_request_reason" name="reschedule_request_reason" rows="3" required minlength="10"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Please explain why you need to reschedule..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRescheduleModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Follow-up Reschedule Modal --}}
<div id="followUpRescheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Request Follow-up Reschedule</h3>
        <form action="{{ route('student.appointments.request-followup-reschedule', $appointment) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="followup_reschedule_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for reschedule request <span class="text-red-500">*</span>
                </label>
                <textarea id="followup_reschedule_reason" name="reschedule_reason" rows="3" required minlength="10"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Please explain why you need to reschedule..."></textarea>
            </div>
            <div class="mb-4">
                <label for="student_preferred_new_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Preferred Date (Optional)
                </label>
                <input type="date" id="student_preferred_new_date" name="preferred_new_date" 
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeFollowUpRescheduleModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Cancel Appointment Modal --}}
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Cancel Appointment</h3>
        <form action="{{ route('student.appointments.destroy', $appointment) }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="mb-4">
                <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for cancellation <span class="text-red-500">*</span>
                </label>
                <textarea id="cancellation_reason" name="cancellation_reason" rows="4" required minlength="10"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Please explain why you need to cancel this appointment..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 10 characters required</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCancelModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                    Keep Appointment
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                    Cancel Appointment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRescheduleModal() {
    document.getElementById('rescheduleModal').classList.remove('hidden');
}

function closeRescheduleModal() {
    document.getElementById('rescheduleModal').classList.add('hidden');
}

function openFollowUpRescheduleModal() {
    document.getElementById('followUpRescheduleModal').classList.remove('hidden');
}

function closeFollowUpRescheduleModal() {
    document.getElementById('followUpRescheduleModal').classList.add('hidden');
}

function openCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('cancellation_reason').value = '';
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const rescheduleModal = document.getElementById('rescheduleModal');
    const followUpModal = document.getElementById('followUpRescheduleModal');
    const cancelModal = document.getElementById('cancelModal');
    
    if (event.target === rescheduleModal) {
        closeRescheduleModal();
    }
    if (event.target === followUpModal) {
        closeFollowUpRescheduleModal();
    }
    if (event.target === cancelModal) {
        closeCancelModal();
    }
});
</script>
@endsection