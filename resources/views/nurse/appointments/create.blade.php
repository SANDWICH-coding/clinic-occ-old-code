
{{-- resources/views/student/appointments/create.blade.php --}}
@extends('layouts.student-app')

@section('title', 'Request New Appointment')

@push('styles')
<style>
    .form-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .time-preference-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .time-preference-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .time-preference-card.selected {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
    .symptom-tag {
        transition: all 0.2s ease;
    }
    .symptom-tag.selected {
        background-color: #3b82f6;
        color: white;
    }
    .floating-label {
        transform: translateY(-1.5rem) scale(0.875);
        color: #6b7280;
    }
    .calendar-day {
        transition: all 0.2s ease;
    }
    .calendar-day:hover {
        background-color: #f3f4f6;
        transform: scale(1.02);
    }
    .calendar-day.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .calendar-day.selected {
        background-color: #3b82f6;
        color: white;
    }
    .form-field { transition: all 0.2s ease; }
    .form-field:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .error-message { color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; }
    .loading-spinner {
        display: inline-block;
        width: 1.5rem;
        height: 1.5rem;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        {{-- Header Section --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Request New Appointment</h1>
                    <p class="text-gray-600">Schedule your visit to the clinic</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('student.appointments.index') }}" 
                       class="flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to My Appointments
                    </a>
                </div>
            </div>
        </div>

        {{-- Pending Appointments Alert --}}
        @if($hasPendingAppointments)
        <div class="max-w-4xl mx-auto mb-8">
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Notice:</strong> You have pending appointment requests. Please wait for them to be processed before requesting a new appointment.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="max-w-4xl mx-auto">
            <form action="{{ route('student.appointments.store') }}" method="POST" class="space-y-8" id="appointmentForm">
                @csrf
                
                {{-- Basic Information Card --}}
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Your Information</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <p class="text-gray-900 font-semibold">{{ auth()->user()->full_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Student ID</label>
                            <p class="text-gray-900 font-semibold">{{ auth()->user()->student_id }}</p>
                        </div>
                    </div>
                </div>

                {{-- Date Selection Card --}}
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Select Date</h2>
                    </div>

                    <div class="grid grid-cols-7 gap-2 text-center mb-2">
                        <div class="text-sm font-medium text-gray-500">Sun</div>
                        <div class="text-sm font-medium text-gray-500">Mon</div>
                        <div class="text-sm font-medium text-gray-500">Tue</div>
                        <div class="text-sm font-medium text-gray-500">Wed</div>
                        <div class="text-sm font-medium text-gray-500">Thu</div>
                        <div class="text-sm font-medium text-gray-500">Fri</div>
                        <div class="text-sm font-medium text-gray-500">Sat</div>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        @for($i = 0; $i < $startOfCalendar->dayOfWeek; $i++)
                            <div class="calendar-day invisible"></div>
                        @endfor
                        
                        @php
                            $currentDay = $startOfCalendar->copy();
                        @endphp
                        
                        @while($currentDay <= $endOfCalendar)
                            <button type="button"
                                    data-date="{{ $currentDay->toDateString() }}"
                                    class="calendar-day border rounded-lg p-2 text-sm 
                                           {{ $currentDay->month !== $currentMonth->month ? 'text-gray-400' : 'text-gray-900' }} 
                                           {{ $currentDay->isWeekend() || $currentDay->isBefore(today()) ? 'disabled' : '' }} 
                                           {{ $currentDay->toDateString() == old('appointment_date') ? 'selected' : '' }}"
                                    {{ $currentDay->isWeekend() || $currentDay->isBefore(today()) ? 'disabled' : '' }}>
                                {{ $currentDay->day }}
                            </button>
                            @php
                                $currentDay->addDay();
                            @endphp
                        @endwhile
                    </div>
                    <input type="hidden" id="appointment_date" name="appointment_date" value="{{ old('appointment_date') }}">
                    @error('appointment_date')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Time Preference Card --}}
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Preferred Time</h2>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="time-preference-card border rounded-lg p-4 text-center cursor-pointer {{ old('preferred_time', 'morning') == 'morning' ? 'selected' : '' }}"
                             data-preference="morning">
                            <p class="font-medium">Morning</p>
                            <p class="text-sm text-gray-600">9:00 AM - 12:00 PM</p>
                        </div>
                        <div class="time-preference-card border rounded-lg p-4 text-center cursor-pointer {{ old('preferred_time') == 'afternoon' ? 'selected' : '' }}"
                             data-preference="afternoon">
                            <p class="font-medium">Afternoon</p>
                            <p class="text-sm text-gray-600">1:00 PM - 5:00 PM</p>
                        </div>
                    </div>
                    <input type="hidden" id="preferred_time" name="preferred_time" value="{{ old('preferred_time', 'morning') }}">
                    @error('preferred_time')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Available Time Slots --}}
                <div class="bg-white rounded-xl shadow-lg p-8" id="timeSlotsSection" style="display: none;">
                    <div class="flex items-center mb-6">
                        <div class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Select Time Slot</h2>
                    </div>
                    <div id="timeSlotsList" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <!-- Time slots will be loaded here dynamically -->
                    </div>
                    <input type="hidden" id="appointment_time" name="appointment_time" value="{{ old('appointment_time') }}">
                    @error('appointment_time')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Reason for Visit --}}
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Reason for Visit</h2>
                    </div>
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Describe your reason for the visit <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reason" name="reason" rows="4" required
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 form-field focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('reason') border-red-500 @enderror"
                                  placeholder="Please describe the reason for your visit...">{{ old('reason') }}</textarea>
                        <p id="reason_counter" class="text-sm text-gray-500 mt-1">500 characters remaining</p>
                        @error('reason')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Symptoms Selection --}}
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-8 h-8 bg-yellow-600 text-white rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Symptoms</h2>
                    </div>
                    <div id="selected_symptoms" class="flex flex-wrap gap-2 mb-4"></div>
                    <input type="hidden" id="symptoms_input" name="symptoms" value="{{ old('symptoms') }}">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach($commonSymptoms as $symptom)
                            <button type="button" class="symptom-tag border rounded-full px-3 py-1 text-sm hover:bg-blue-100">
                                {{ $symptom }}
                            </button>
                        @endforeach
                    </div>
                    @error('symptoms')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="flex justify-end">
                    <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">
                        Submit Appointment Request
                    </button>
                </div>
            </form>
        </div>

        {{-- Loading Modal --}}
        <div id="loading_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl">
                <div class="flex flex-col items-center">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-700 font-medium">Submitting your request...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('appointmentForm');
    const dateInput = document.getElementById('appointment_date');
    const reasonTextarea = document.getElementById('reason');
    const selectedSymptomsDiv = document.getElementById('selected_symptoms');
    const symptomsInput = document.getElementById('symptoms_input');
    const timePreferenceCards = document.querySelectorAll('[data-preference]');
    const preferredTimeInput = document.getElementById('preferred_time');
    const appointmentTimeInput = document.getElementById('appointment_time');
    const dateButtons = document.querySelectorAll('.calendar-day:not(.disabled)');
    const submitBtn = document.getElementById('submitBtn');
    let selectedSymptoms = [];

    function showError(input, message) {
        const existingError = input.parentNode.querySelector('.error-message');
        if (existingError) existingError.remove();
        const errorDiv = document.createElement('p');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${message}`;
        input.parentNode.appendChild(errorDiv);
        input.classList.add('border-red-500');
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        [dateInput, reasonTextarea, appointmentTimeInput].forEach(input => input.classList.remove('border-red-500'));
    }

    function selectDate(date) {
        dateButtons.forEach(btn => btn.classList.remove('selected'));
        const button = document.querySelector(`[data-date="${date}"]`);
        if (button) {
            button.classList.add('selected');
            dateInput.value = date;
            loadTimeSlots(date);
            validateForm();
        }
    }

    function selectTimePreference(preference) {
        timePreferenceCards.forEach(card => card.classList.remove('selected'));
        const card = document.querySelector(`[data-preference="${preference}"]`);
        if (card) {
            card.classList.add('selected');
            preferredTimeInput.value = preference;
            validateForm();
        }
    }

    function toggleSymptom(symptom) {
        const index = selectedSymptoms.indexOf(symptom);
        if (index === -1) {
            selectedSymptoms.push(symptom);
        } else {
            selectedSymptoms.splice(index, 1);
        }
        updateSymptomsDisplay();
    }

    function updateSymptomsDisplay() {
        selectedSymptomsDiv.innerHTML = '';
        selectedSymptoms.forEach(symptom => {
            const tag = document.createElement('span');
            tag.className = 'px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm flex items-center';
            tag.innerHTML = `
                ${symptom}
                <button type="button" class="ml-2 text-blue-600 hover:text-blue-800">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            tag.querySelector('button').onclick = () => toggleSymptom(symptom);
            selectedSymptomsDiv.appendChild(tag);
        });
        symptomsInput.value = JSON.stringify(selectedSymptoms);
        validateForm();
    }

    function loadTimeSlots(date) {
        const timeSlotsSection = document.getElementById('timeSlotsSection');
        const timeSlotsList = document.getElementById('timeSlotsList');
        
        if (!date) {
            timeSlotsSection.style.display = 'none';
            return;
        }
        
        timeSlotsSection.style.display = 'block';
        timeSlotsList.innerHTML = '<div class="col-span-full text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i><p class="text-gray-600 mt-2">Loading available time slots...</p></div>';
        
        fetch(`/student/appointments/available-slots?date=${encodeURIComponent(date)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            timeSlotsList.innerHTML = '';
            if (data.success && data.slots && data.slots.length > 0) {
                data.slots.forEach(slot => {
                    if (slot.is_available) {
                        const slotButton = document.createElement('button');
                        slotButton.type = 'button';
                        slotButton.className = 'border-2 border-gray-300 rounded-lg p-3 hover:border-blue-500 hover:bg-blue-50 transition-all text-center';
                        slotButton.innerHTML = `
                            <p class="font-semibold text-gray-800">${slot.label}</p>
                            <p class="text-xs text-gray-600">${slot.period.charAt(0).toUpperCase() + slot.period.slice(1)}</p>
                        `;
                        slotButton.onclick = function() {
                            document.querySelectorAll('#timeSlotsList button').forEach(btn => {
                                btn.classList.remove('border-blue-500', 'bg-blue-50', 'border-2');
                                btn.classList.add('border-gray-300');
                            });
                            this.classList.remove('border-gray-300');
                            this.classList.add('border-blue-500', 'bg-blue-50', 'border-2');
                            appointmentTimeInput.value = slot.value;
                            validateForm();
                        };
                        timeSlotsList.appendChild(slotButton);
                    }
                });
            } else {
                timeSlotsList.innerHTML = '<div class="col-span-full text-center py-8"><i class="fas fa-calendar-times text-4xl text-gray-400 mb-3"></i><p class="text-gray-600">No available time slots for this date.</p><p class="text-sm text-gray-500 mt-1">Please select another date.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading time slots:', error);
            timeSlotsList.innerHTML = '<div class="col-span-full text-center py-4 text-red-600"><i class="fas fa-exclamation-circle mr-2"></i>Error loading time slots. Please try again.</div>';
            timeSlotsSection.style.display = 'none';
        });
    }

    function validateForm() {
        clearErrors();
        let isValid = true;

        if (!dateInput.value) {
            showError(dateInput.parentNode, 'Please select an appointment date');
            isValid = false;
        } else {
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today || selectedDate.getDay() === 0 || selectedDate.getDay() === 6) {
                showError(dateInput.parentNode, 'Please select a valid date (no weekends or past dates)');
                isValid = false;
            }
        }

        if (!appointmentTimeInput.value) {
            showError(appointmentTimeInput.parentNode, 'Please select an appointment time');
            isValid = false;
        }

        if (!reasonTextarea.value.trim()) {
            showError(reasonTextarea, 'Please provide a reason for your visit');
            isValid = false;
        } else if (reasonTextarea.value.trim().length < 10) {
            showError(reasonTextarea, 'Reason must be at least 10 characters long');
            isValid = false;
        } else if (reasonTextarea.value.length > 500) {
            showError(reasonTextarea, 'Reason must be 500 characters or less');
            isValid = false;
        }

        submitBtn.disabled = !isValid;
        return isValid;
    }

    // Event listeners for date buttons
    dateButtons.forEach(button => {
        button.addEventListener('click', function() {
            selectDate(this.dataset.date);
        });
    });

    // Event listeners for symptom tags
    document.querySelectorAll('.symptom-tag').forEach(button => {
        button.addEventListener('click', function() {
            toggleSymptom(this.textContent.trim());
            this.classList.toggle('selected');
        });
    });

    // Event listeners for time preference cards
    timePreferenceCards.forEach(card => {
        card.addEventListener('click', function() {
            selectTimePreference(this.dataset.preference);
        });
    });

    // Character counter
    const maxReasonLength = 500;
    reasonTextarea.addEventListener('input', function() {
        const remaining = maxReasonLength - this.value.length;
        const counterDiv = document.getElementById('reason_counter');
        counterDiv.textContent = `${remaining} characters remaining`;
        counterDiv.classList.toggle('text-red-500', remaining < 0);
        counterDiv.classList.toggle('text-gray-500', remaining >= 0);
        validateForm();
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!validateForm()) return;

        if ({{ $hasPendingAppointments ? 'true' : 'false' }}) {
            alert('You have pending appointment requests. Please wait for them to be processed.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Submitting...
        `;
        document.getElementById('loading_modal').classList.remove('hidden');
        this.submit();
    });

    // Initialize
    selectTimePreference('morning');
    const oldSymptoms = symptomsInput.value;
    if (oldSymptoms) {
        try {
            selectedSymptoms = JSON.parse(oldSymptoms);
            updateSymptomsDisplay();
            selectedSymptoms.forEach(symptom => {
                const button = Array.from(document.querySelectorAll('.symptom-tag')).find(btn => btn.textContent.trim() === symptom);
                if (button) button.classList.add('selected');
            });
        } catch (e) {
            console.log('Could not parse old symptoms data');
        }
    }

    // Auto-select today if available
    const today = new Date().toISOString().split('T')[0];
    const todayButton = document.querySelector(`[data-date="${today}"]`);
    if (todayButton && !todayButton.classList.contains('disabled')) {
        selectDate(today);
    }

    validateForm();
});
</script>
@endpush
