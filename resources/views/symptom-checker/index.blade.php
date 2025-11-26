@extends('layouts.app')

@section('title', 'Symptom Checker')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Symptom Checker</h1>
        <p class="text-gray-600">Select the symptoms you're experiencing to get possible condition insights and request an appointment</p>
    </div>

    <!-- Symptom Selection Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form action="{{ route('student.symptom-checker.check') }}" method="POST" id="symptomForm">
            @csrf
            
            <!-- Search Box -->
            <div class="mb-6">
                <label for="symptomSearch" class="block text-sm font-medium text-gray-700 mb-2">
                    Search Symptoms:
                </label>
                <div class="relative">
                    <input type="text" id="symptomSearch" placeholder="Type to search symptoms..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Symptoms Grid -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Select your symptoms (choose at least one):
                </label>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="symptomsContainer">
                    @foreach($symptoms->chunk(ceil($symptoms->count() / 2)) as $chunk)
                    <div class="space-y-3">
                        @foreach($chunk as $symptom)
                        <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors duration-200 symptom-item">
                            <input type="checkbox" name="symptoms[]" value="{{ $symptom->id }}" 
                                   class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500 symptom-checkbox">
                            <span class="ml-3 text-gray-700 symptom-name">{{ $symptom->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                
                @error('symptoms')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Selected Symptoms Preview -->
            <div id="selectedSymptoms" class="mb-6 hidden">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Selected Symptoms:</h3>
                <div id="selectedList" class="flex flex-wrap gap-2"></div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submitBtn" 
                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                <span class="flex items-center justify-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Complete Analysis & Request Appointment
                </span>
            </button>
        </form>
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Disclaimer -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <svg class="h-5 w-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="font-semibold text-yellow-800">Important Notice</h3>
            </div>
            <p class="text-sm text-yellow-700">
                This tool provides general information only and is not a substitute for professional medical advice.
            </p>
        </div>

        <!-- Emergency Info -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="font-semibold text-red-800">Emergency Symptoms</h3>
            </div>
            <p class="text-sm text-red-700">
                If you experience chest pain, difficulty breathing, or severe bleeding, seek immediate medical attention.
            </p>
        </div>
    </div>

    <!-- Workflow Explanation -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-800 mb-3">How It Works</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2">
                    <span class="text-blue-600 font-bold">1</span>
                </div>
                <p class="text-sm text-blue-700">Select your symptoms from the list above</p>
            </div>
            <div class="text-center">
                <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2">
                    <span class="text-blue-600 font-bold">2</span>
                </div>
                <p class="text-sm text-blue-700">Complete analysis to get possible condition insights</p>
            </div>
            <div class="text-center">
                <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2">
                    <span class="text-blue-600 font-bold">3</span>
                </div>
                <p class="text-sm text-blue-700">Automatically proceed to request an appointment</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('symptomSearch');
    const symptomItems = document.querySelectorAll('.symptom-item');
    const checkboxes = document.querySelectorAll('.symptom-checkbox');
    const selectedDiv = document.getElementById('selectedSymptoms');
    const selectedList = document.getElementById('selectedList');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('symptomForm');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        symptomItems.forEach(item => {
            const symptomName = item.querySelector('.symptom-name').textContent.toLowerCase();
            if (symptomName.includes(searchTerm)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    });

    // Track selected symptoms
    let selectedSymptoms = [];

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const symptomId = this.value;
            const symptomName = this.parentElement.querySelector('.symptom-name').textContent;

            if (this.checked) {
                selectedSymptoms.push({ id: symptomId, name: symptomName });
            } else {
                selectedSymptoms = selectedSymptoms.filter(s => s.id !== symptomId);
            }

            updateSelectedList();
            updateSubmitButton();
        });
    });

    function updateSelectedList() {
        selectedList.innerHTML = '';
        selectedSymptoms.forEach(symptom => {
            const badge = document.createElement('span');
            badge.className = 'bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm';
            badge.textContent = symptom.name;
            selectedList.appendChild(badge);
        });

        // Show/hide selected symptoms section
        if (selectedSymptoms.length > 0) {
            selectedDiv.classList.remove('hidden');
        } else {
            selectedDiv.classList.add('hidden');
        }
    }

    function updateSubmitButton() {
        submitBtn.disabled = selectedSymptoms.length === 0;
        
        // Update button text based on selection count
        const buttonText = submitBtn.querySelector('span');
        if (selectedSymptoms.length > 0) {
            buttonText.innerHTML = `
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
               Request Appointment (${selectedSymptoms.length} selected)
            `;
        } else {
            buttonText.innerHTML = `
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
               Request Appointment
            `;
        }
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        if (selectedSymptoms.length === 0) {
            e.preventDefault();
            showNotification('Please select at least one symptom.', 'error');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = `
            <span class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 1010 10A10 10 0 0012 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/>
                </svg>
                Analyzing Symptoms...
            </span>
        `;

        // Allow form to submit normally
    });

    // Notification function
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg border transform transition-transform duration-300 ${
            type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 
            type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 
            'bg-blue-100 border-blue-400 text-blue-700'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${
                        type === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"' :
                        type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"' :
                        'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"'
                    }"/>
                </svg>
                <span>${message}</span>
                <button class="ml-4 text-gray-500 hover:text-gray-700" onclick="this.parentElement.parentElement.remove()">
                    &times;
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Initialize button state
    updateSubmitButton();
});
</script>
@endpush
@endsection