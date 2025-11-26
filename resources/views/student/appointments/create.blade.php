@extends('layouts.app')

@section('title', 'Request New Appointment')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
    .hidden {
        display: none;
    }
    .error-border {
        border-color: #ef4444 !important;
    }
    
    /* Time slot selection styling */
    #appointment_time option:disabled {
        color: #9ca3af;
        background-color: #f3f4f6;
    }
    
    /* Date input styling */
    input[type="date"]:invalid {
        border-color: #ef4444;
    }

    /* Symptom checker integration styles */
    .symptom-badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-200 text-blue-800;
    }
    
    .symptom-list {
        @apply flex flex-wrap gap-2 mt-1;
    }

    .emergency-alert {
        @apply mt-3 p-2 bg-red-100 border border-red-300 rounded;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Request New Appointment</h1>
            <p class="text-gray-600 mt-1">Fill out the form below to request an appointment with clinic staff.</p>
        </div>
        
        {{-- Server-side notifications --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <div class="flex items-center">
                    <span class="mr-2">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <div class="flex items-center">
                    <span class="mr-2">⚠</span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <div class="flex items-center mb-2">
                    <span class="mr-2">⚠</span>
                    <span class="font-semibold">Please correct the following errors:</span>
                </div>
                <ul class="list-disc list-inside ml-6">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Dynamic notifications container --}}
        <div id="dynamic-notifications"></div>
        
        {{-- Symptom Checker Results (if available) --}}
        @php
            $symptomData = session('current_symptom_check') ?? session('symptom_data') ?? null;
            $prefilledSymptoms = $symptomData['symptoms'] ?? [];
            $isEmergency = $symptomData['is_emergency'] ?? false;
            $suggestedAppointmentType = $isEmergency ? 'emergency' : 'scheduled';
        @endphp

        @if($symptomData && !empty($prefilledSymptoms))
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 7a1 1 0 000 2h6a1 1 0 100-2H8zm0 4a1 1 0 000 2h3a1 1 0 100-2H8z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-blue-800">Symptom Check Results</h3>
                        
                        <div class="mt-2">
                            <p class="text-sm text-blue-700 font-medium">Your reported symptoms:</p>
                            <div class="symptom-list">
                                @foreach($prefilledSymptoms as $symptom)
                                    <span class="symptom-badge">{{ $symptom }}</span>
                                @endforeach
                            </div>
                        </div>
                        
                        <p class="text-xs text-blue-600 mt-2">
                            Checked: {{ $symptomData['created_at'] ?? now()->format('M j, Y g:i A') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        <form action="{{ route('student.appointments.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6" id="appointmentForm">
            @csrf
            
            {{-- Symptoms Hidden Input (if from checker) --}}
            @if(!empty($prefilledSymptoms))
                @foreach($prefilledSymptoms as $symptom)
                    <input type="hidden" name="symptoms[]" value="{{ $symptom }}">
                @endforeach
                <input type="hidden" name="symptom_check_id" value="{{ $symptomData['id'] ?? '' }}">
            @endif
            
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
                       max="{{ date('Y-m-d', strtotime('+30 days')) }}"
                       value="{{ old('appointment_date') }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('appointment_date') border-red-500 @enderror">
                @error('appointment_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Select a weekday within the next 30 days</p>
            </div>

            {{-- Appointment Time Slot --}}
            <div class="mb-4 relative">
                <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">
                    Preferred Time Slot <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select id="appointment_time" 
                            name="appointment_time" 
                            required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('appointment_time') border-red-500 @enderror">
                        <option value="">-- Select a date first --</option>
                    </select>
                    <div id="timeSlotLoader" class="hidden absolute right-3 top-3">
                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                @error('appointment_time')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Available time slots will appear after selecting a date</p>
            </div>

            {{-- Hidden Appointment Type Field --}}
            <input type="hidden" name="appointment_type" value="scheduled">

            {{-- Reason --}}
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Visit <span class="text-red-500">*</span>
                </label>
                <textarea id="reason" 
                          name="reason" 
                          rows="4" 
                          required
                          maxlength="500"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reason') border-red-500 @enderror"
                          placeholder="Please describe the reason for your visit (minimum 10 characters)...">@if(!empty($prefilledSymptoms)){{ implode(', ', $prefilledSymptoms) }}. @endif{{ old('reason') }}</textarea>
                <div class="flex justify-between items-center mt-1">
                    <span id="reason-counter" class="text-xs text-gray-500">0/500</span>
                    <span id="reason-minimum" class="text-xs text-gray-500">Minimum 10 characters</span>
                </div>
                @error('reason')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Additional Notes --}}
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Additional Notes (Optional)
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3"
                          maxlength="300"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Any additional information you'd like to share...">{{ old('notes') }}</textarea>
                <span id="notes-counter" class="text-xs text-gray-500 block mt-1">0/300</span>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end space-x-3 border-t pt-4">
                <a href="{{ route('student.appointments.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-150">
                    Cancel
                </a>
                <button type="submit" 
                        id="submitBtn"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                    <span id="submitText">Request Appointment</span>
                    <span id="loadingText" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const reasonTextarea = document.getElementById('reason');
    const notesTextarea = document.getElementById('notes');
    const reasonCounter = document.getElementById('reason-counter');
    const notesCounter = document.getElementById('notes-counter');
    const form = document.getElementById('appointmentForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingText = document.getElementById('loadingText');
    const timeLoader = document.getElementById('timeSlotLoader');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Character counters
    function updateCharacterCounter(textarea, counter) {
        const current = textarea.value.length;
        const max = textarea.getAttribute('maxlength');
        counter.textContent = `${current}/${max}`;
        
        // Update color based on length for reason field
        if (textarea.id === 'reason') {
            if (current < 10) {
                counter.classList.add('text-red-500');
                counter.classList.remove('text-gray-500');
            } else {
                counter.classList.remove('text-red-500');
                counter.classList.add('text-gray-500');
            }
        }
    }

    reasonTextarea.addEventListener('input', () => updateCharacterCounter(reasonTextarea, reasonCounter));
    notesTextarea.addEventListener('input', () => updateCharacterCounter(notesTextarea, notesCounter));
    updateCharacterCounter(reasonTextarea, reasonCounter);
    updateCharacterCounter(notesTextarea, notesCounter);

    // Load time slots
    async function loadTimeSlots() {
        const date = dateInput.value;
        if (!date) {
            resetTimeSelect('-- Select a date first --');
            return;
        }

        console.log('📅 Loading time slots for:', date);
        showLoadingState();

        try {
            const response = await fetch(`/student/appointments/available-slots?date=${encodeURIComponent(date)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            });

            console.log('📡 Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Server response error:', errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const data = await response.json();
            console.log('✅ Received API response:', data);
            
            if (data && data.success && data.slots && data.slots.length > 0) {
                console.log('🕒 Available slots:', data.slots.filter(s => s.is_available));
                populateTimeSlots(data.slots);
                
                const availCount = data.slots.filter(s => s.is_available).length;
                if (availCount > 0) {
                    showNotification(`Found ${availCount} available time slot(s)!`, 'success', 3000);
                } else {
                    showNotification('All time slots are booked for this date.', 'warning', 4000);
                }
            } else {
                console.warn('⚠ No slots data received');
                resetTimeSelect('No available slots for this date');
                showNotification('No available time slots found for this date.', 'warning', 4000);
            }
        } catch (error) {
            console.error('❌ Error loading time slots:', error);
            handleTimeSlotError(error);
        }
    }

    function showLoadingState() {
        timeLoader.classList.remove('hidden');
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Loading available time slots...</option>';
        submitBtn.disabled = true;
    }

    function populateTimeSlots(slots) {
        timeLoader.classList.add('hidden');
        timeSelect.disabled = false;
        
        let options = '<option value="">-- Select a time slot --</option>';
        const availableSlots = slots.filter(slot => slot.is_available);
        
        if (availableSlots.length === 0) {
            options = '<option value="">No available slots for this date</option>';
            submitBtn.disabled = true;
        } else {
            availableSlots.forEach(slot => {
                options += `<option value="${slot.value}">${slot.label}</option>`;
            });
            submitBtn.disabled = false;
        }
        
        timeSelect.innerHTML = options;
        timeSelect.classList.remove('error-border');
    }

    function handleTimeSlotError(error) {
        console.error('Time slot error:', error);
        timeLoader.classList.add('hidden');
        timeSelect.disabled = false;
        
        const fallbackSlots = [
            { value: '09:00:00', label: '9:00 AM - Morning', is_available: true },
            { value: '10:00:00', label: '10:00 AM - Morning', is_available: true },
            { value: '13:30:00', label: '1:30 PM - Afternoon', is_available: true },
            { value: '14:30:00', label: '2:30 PM - Afternoon', is_available: true },
            { value: '15:30:00', label: '3:30 PM - Afternoon', is_available: true },
            { value: '16:30:00', label: '4:30 PM - Afternoon', is_available: true }
        ];
        
        populateTimeSlots(fallbackSlots);
        showNotification('Using default time slots. Availability may vary.', 'info', 4000);
    }

    function resetTimeSelect(message) {
        timeSelect.innerHTML = `<option value="">${message}</option>`;
        timeSelect.disabled = false;
        submitBtn.disabled = true;
        timeLoader.classList.add('hidden');
    }

    // Date validation
    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const dayOfWeek = selectedDate.getDay();
        
        if (selectedDate < today) {
            showNotification('Cannot select a date in the past.', 'error', 4000);
            this.value = '';
            resetTimeSelect('-- Select a date first --');
            return;
        }
        
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            showNotification('Clinic is closed on weekends. Please select a weekday.', 'error', 4000);
            this.value = '';
            resetTimeSelect('-- Select a date first --');
            return;
        }
        
        // Check if date is within 30 days
        const maxDate = new Date();
        maxDate.setDate(today.getDate() + 30);
        if (selectedDate > maxDate) {
            showNotification('Please select a date within the next 30 days.', 'error', 4000);
            this.value = '';
            resetTimeSelect('-- Select a date first --');
            return;
        }
        
        loadTimeSlots();
    });

    // Time select change
    timeSelect.addEventListener('change', function() {
        submitBtn.disabled = this.value === '';
        if (this.value) {
            this.classList.remove('error-border');
            console.log('✅ Selected time:', this.value);
        }
    });

    // Ensure proper time format (H:i:s)
    function ensureProperTimeFormat() {
        const timeValue = timeSelect.value;
        if (timeValue && timeValue.length === 5) {
            timeSelect.value = timeValue + ':00';
            console.log('🕒 Converted time format:', timeSelect.value);
        }
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        let hasErrors = false;

        // Ensure proper time format before validation
        ensureProperTimeFormat();

        // Validate date
        if (!dateInput.value) {
            showNotification('Please select a date.', 'error', 4000);
            dateInput.focus();
            hasErrors = true;
        }

        // Validate time slot
        if (!timeSelect.value || timeSelect.value === '') {
            showNotification('Please select a time slot.', 'error', 4000);
            timeSelect.classList.add('error-border');
            timeSelect.focus();
            hasErrors = true;
        }

        // Validate reason
        if (reasonTextarea.value.trim().length < 10) {
            showNotification('Please provide a detailed reason (at least 10 characters).', 'error', 4000);
            reasonTextarea.focus();
            hasErrors = true;
        }

        if (hasErrors) {
            e.preventDefault();
            return;
        }

        // DEBUG: Log all form data
        const formData = new FormData(form);
        console.log('📤 Form data being submitted:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}:`, value);
        }

        console.log('Submitting appointment:', {
            date: dateInput.value,
            time: timeSelect.value,
            reason: reasonTextarea.value.substring(0, 50) + '...'
        });

        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        loadingText.classList.remove('hidden');
    });

    // Notification function
    function showNotification(message, type = 'info', duration = 4000) {
        const container = document.getElementById('dynamic-notifications');
        const notification = document.createElement('div');
        
        const colors = {
            success: 'bg-green-100 border-green-400 text-green-700',
            error: 'bg-red-100 border-red-400 text-red-700',
            warning: 'bg-yellow-100 border-yellow-400 text-yellow-700',
            info: 'bg-blue-100 border-blue-400 text-blue-700'
        };
        
        const icons = {
            success: '✓',
            error: '⚠',
            warning: '⚠',
            info: 'ℹ'
        };
        
        notification.className = `${colors[type]} border px-4 py-3 rounded mb-4 transition-all duration-300 transform translate-y-2 opacity-0`;
        notification.innerHTML = `
            <div class="flex items-center">
                <span class="mr-2">${icons[type]}</span>
                <span class="flex-1">${message}</span>
                <button class="ml-2 text-gray-500 hover:text-gray-700 font-bold text-lg" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        `;
        
        container.appendChild(notification);
        setTimeout(() => notification.classList.remove('translate-y-2', 'opacity-0'), 10);
        
        if (duration > 0) {
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }
    }

    // Initialize form state
    function initializeForm() {
        submitBtn.disabled = true;
        
        // If there's a date value from old input, load time slots
        if (dateInput.value) {
            console.log('🔄 Initializing with existing date:', dateInput.value);
            setTimeout(() => loadTimeSlots(), 500);
        }
        
        // Update character counters for old input
        updateCharacterCounter(reasonTextarea, reasonCounter);
        updateCharacterCounter(notesTextarea, notesCounter);
    }

    // Initialize the form
    initializeForm();
});
</script>
@endsection