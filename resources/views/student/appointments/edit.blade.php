{{-- resources/views/student/appointments/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Appointment')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Appointment</h1>
        
        <form action="{{ route('student.appointments.update', $appointment) }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
            @csrf
            @method('PUT')
            
            {{-- Appointment Date --}}
            <div class="mb-4">
                <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Preferred Date <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       id="appointment_date" 
                       name="appointment_date" 
                       required
                       min="{{ date('Y-m-d') }}"
                       value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('appointment_date') border-red-500 @enderror">
                @error('appointment_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nurse Selection --}}
            <div class="mb-4">
                <label for="nurse_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Preferred Nurse (Optional)
                </label>
                <select id="nurse_id" 
                        name="nurse_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nurse_id') border-red-500 @enderror">
                    <option value="">Any available nurse</option>
                    @foreach($nurses as $nurse)
                        <option value="{{ $nurse->id }}" {{ old('nurse_id', $appointment->nurse_id) == $nurse->id ? 'selected' : '' }}>
                            {{ $nurse->full_name }}
                        </option>
                    @endforeach
                </select>
                @error('nurse_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Time Selection --}}
            <div class="mb-4">
                <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">
                    Preferred Time <span class="text-red-500">*</span>
                </label>
                <select id="appointment_time" 
                        name="appointment_time" 
                        required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('appointment_time') border-red-500 @enderror">
                    <option value="">Select a time</option>
                </select>
                @error('appointment_time')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <div id="timeSlotLoader" class="hidden mt-2">
                    <div class="flex items-center text-gray-600">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading available time slots...
                    </div>
                </div>
            </div>

            {{-- Reason --}}
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Visit <span class="text-red-500">*</span>
                </label>
                <textarea id="reason" 
                          name="reason" 
                          rows="3" 
                          required
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('reason') border-red-500 @enderror"
                          placeholder="Please describe the reason for your visit...">{{ old('reason', $appointment->reason) }}</textarea>
                @error('reason')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Priority --}}
            <div class="mb-4">
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                    Priority Level
                </label>
                <select id="priority" 
                        name="priority"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="1" {{ old('priority', $appointment->priority) == '1' ? 'selected' : '' }}>Low</option>
                    <option value="2" {{ old('priority', $appointment->priority) == '2' ? 'selected' : '' }}>Normal</option>
                    <option value="3" {{ old('priority', $appointment->priority) == '3' ? 'selected' : '' }}>High</option>
                    <option value="4" {{ old('priority', $appointment->priority) == '4' ? 'selected' : '' }}>Urgent</option>
                    <option value="5" {{ old('priority', $appointment->priority) == '5' ? 'selected' : '' }}>Emergency</option>
                </select>
            </div>

            {{-- Additional Notes --}}
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Additional Notes (Optional)
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Any additional information you'd like to share...">{{ old('notes', $appointment->notes) }}</textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('student.appointments.show', $appointment) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                    Update Appointment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('appointment_date');
    const nurseSelect = document.getElementById('nurse_id');
    const timeSelect = document.getElementById('appointment_time');
    const timeLoader = document.getElementById('timeSlotLoader');
    const appointmentId = {{ $appointment->id }};
    const currentTime = '{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}';

    function loadTimeSlots() {
        const date = dateInput.value;
        const nurseId = nurseSelect.value;

        if (!date) return;

        // Show loader
        timeLoader.classList.remove('hidden');
        timeSelect.disabled = true;

        fetch(`{{ route('api.appointments.available-slots') }}?date=${date}&nurse_id=${nurseId}&appointment_id=${appointmentId}`)
            .then(response => response.json())
            .then(data => {
                timeLoader.classList.add('hidden');
                if (data.success && data.slots.length > 0) {
                    let hasCurrentTime = false;
                    let options = '<option value="">Select a time</option>';
                    data.slots.forEach(slot => {
                        const selected = slot.value === currentTime ? 'selected' : '';
                        if (slot.value === currentTime) hasCurrentTime = true;
                        options += `<option value="${slot.value}" ${selected}>${slot.label}</option>`;
                    });
                    if (!hasCurrentTime && currentTime) {
                        const currentLabel = formatTime(currentTime);
                        options = `<option value="${currentTime}" selected>${currentLabel} (Current)</option>` + options;
                    }
                    timeSelect.innerHTML = options;
                    timeSelect.disabled = false;
                } else {
                    timeSelect.innerHTML = '<option value="">No available slots</option>';
                    timeSelect.disabled = true;
                }
            })
            .catch(error => {
                timeLoader.classList.add('hidden');
                timeSelect.innerHTML = '<option value="">Error loading slots</option>';
                timeSelect.disabled = true;
                console.error('Error:', error);
            });
    }

    function formatTime(time) {
        const [hours, minutes] = time.split(':');
        const hour = parseInt(hours, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    }

    // Disable weekends
    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const dayOfWeek = selectedDate.getDay();
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            alert('Appointments are not available on weekends.');
            this.value = '';
            return;
        }
        loadTimeSlots();
    });

    nurseSelect.addEventListener('change', loadTimeSlots);

    // Load slots on page load
    loadTimeSlots();
});
</script>
@endpush
@endsection
