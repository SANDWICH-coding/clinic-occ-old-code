@extends('layouts.nurse-app')

@section('title', 'Edit Appointment')

@push('styles')
<style>
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
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Appointment</h1>
        
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form id="editAppointmentForm" action="{{ route('nurse.appointments.update', $appointment) }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Patient <span class="text-red-500">*</span>
                </label>
                <select id="user_id" name="user_id" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 form-field focus:outline-none"
                        aria-describedby="user_id_error">
                    <option value="">Choose a patient...</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('user_id', $appointment->user_id) == $student->id ? 'selected' : '' }}>
                            {{ $student->full_name }} ({{ $student->student_id }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p id="user_id_error" class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Appointment Date <span class="text-red-500">*</span>
                </label>
                <input type="date" id="appointment_date" name="appointment_date" required
                       min="{{ date('Y-m-d') }}"
                       value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 form-field focus:outline-none"
                       aria-describedby="appointment_date_error">
                @error('appointment_date')
                    <p id="appointment_date_error" class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">
                    Appointment Time <span class="text-red-500">*</span>
                </label>
                <select id="appointment_time" name="appointment_time" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 form-field focus:outline-none"
                        aria-describedby="appointment_time_error">
                    <option value="">Select a time</option>
                    @foreach($availableSlots as $slot)
                        <option value="{{ $slot['value'] }}" {{ old('appointment_time', $appointment->appointment_time) == $slot['value'] ? 'selected' : '' }}>
                            {{ $slot['label'] }} - {{ ucfirst($slot['period']) }}
                        </option>
                    @endforeach
                </select>
                @error('appointment_time')
                    <p id="appointment_time_error" class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Visit <span class="text-red-500">*</span>
                </label>
                <textarea id="reason" name="reason" rows="3" required
                          class="w-full border border-gray-300 rounded-md px-3 py-2 form-field focus:outline-none"
                          placeholder="Please describe the reason for the visit..."
                          aria-describedby="reason_error">{{ old('reason', $appointment->reason) }}</textarea>
                <p id="reason_counter" class="text-sm text-gray-500 mt-1">500 characters remaining</p>
                @error('reason')
                    <p id="reason_error" class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                    Priority Level
                </label>
                <select id="priority" name="priority"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 form-field focus:outline-none">
                    <option value="1" {{ old('priority', $appointment->priority) == '1' ? 'selected' : '' }}>Low</option>
                    <option value="2" {{ old('priority', $appointment->priority) == '2' ? 'selected' : '' }}>Normal</option>
                    <option value="3" {{ old('priority', $appointment->priority) == '3' ? 'selected' : '' }}>High</option>
                    <option value="4" {{ old('priority', $appointment->priority) == '4' ? 'selected' : '' }}>Urgent</option>
                    <option value="5" {{ old('priority', $appointment->priority) == '5' ? 'selected' : '' }}>Emergency</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Additional Notes
                </label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 form-field focus:outline-none"
                          placeholder="Any additional information...">{{ old('notes', $appointment->notes) }}</textarea>
            </div>

            @if(!auth()->user()->isStudent())
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select id="status" name="status"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 form-field focus:outline-none">
                        <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            @endif

            <div class="flex justify-end space-x-3">
                <a href="{{ auth()->user()->isStudent() ? route('student.appointments.index') : route('nurse.appointments.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" id="submitBtn" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                    Update Appointment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editAppointmentForm');
    const submitBtn = document.getElementById('submitBtn');
    const dateInput = document.getElementById('appointment_date');
    const timeInput = document.getElementById('appointment_time');
    const reasonInput = document.getElementById('reason');

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
        [dateInput, timeInput, reasonInput].forEach(input => input.classList.remove('border-red-500'));
    }

    function validateForm() {
        clearErrors();
        let isValid = true;

        if (!dateInput.value) {
            showError(dateInput, 'Please select an appointment date');
            isValid = false;
        } else {
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                showError(dateInput, 'Appointment date cannot be in the past');
                isValid = false;
            }
        }

        if (!timeInput.value) {
            showError(timeInput, 'Please select an appointment time');
            isValid = false;
        }

        if (!reasonInput.value.trim()) {
            showError(reasonInput, 'Please provide a reason for the visit');
            isValid = false;
        } else if (reasonInput.value.trim().length < 10) {
            showError(reasonInput, 'Reason must be at least 10 characters long');
            isValid = false;
        } else if (reasonInput.value.length > 500) {
            showError(reasonInput, 'Reason must be 500 characters or less');
            isValid = false;
        }

        submitBtn.disabled = !isValid;
        return isValid;
    }

    reasonInput.addEventListener('input', function() {
        const maxReasonLength = 500;
        const remaining = maxReasonLength - this.value.length;
        const counterDiv = document.getElementById('reason_counter');
        counterDiv.textContent = `${remaining} characters remaining`;
        counterDiv.classList.toggle('text-red-500', remaining < 0);
        counterDiv.classList.toggle('text-gray-500', remaining >= 0);
        validateForm();
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!validateForm()) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Updating...';
        form.submit();
    });

    validateForm();
});
</script>
@endpush